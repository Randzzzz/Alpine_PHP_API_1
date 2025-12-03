document.addEventListener("DOMContentLoaded", () => {
  loadTransactionList();

  document.getElementById("filterBtn").addEventListener("click", () => {
    const start = document.getElementById("date_start").value;
    const end = document.getElementById("date_end").value;
    loadTransactionList(start, end);
  });
});

document.getElementById("printTable").addEventListener("click", function () {
  const table = document.querySelector(".menu").innerHTML;
  const printWindow = window.open("", "", "width=900,height=650");
  printWindow.document.write(`
    <html>
      <head>
        <title>Transaction Report</title>
        <style>
          body { font-family: Arial, sans-serif; padding: 20px; color: #324149; }
          table { width: 100%; border-collapse: collapse; }
          th, td { border: 1px solid #ccc; padding: 8px; text-align: center; }
          th { background: #f9f9f9; }
          h1 { text-align: center; margin-bottom: 20px; }
        </style>
      </head>
      <body>
        <h1>Bistro Crafté — </h1>
        ${table}
      </body>
    </html>
  `);
  printWindow.document.close();
  printWindow.print();
});

async function loadTransactionList(start = "", end = "") {
  try {
    const response = await fetch("core/api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "get_transaction",
        start_date: start,
        end_date: end,
      }),
    });

    const result = await response.json();
    console.log("Transaction fetch result:", result);

    if (result.success) {
      displayTransactionList(result.transactions);
    } else {
      showError(result.message);
    }
  } catch (error) {
    console.error("Fetch error:", error);
    showError("Error loading transaction list");
  }
}

function displayTransactionList(transactions) {
  const tbody = document.getElementById("transactionList");
  const tfoot = document.getElementById("transactionTotal");
  tbody.innerHTML = "";
  tfoot.innerHTML = "";
  let totalSum = 0;

  if (!transactions.length) {
    tbody.innerHTML = `<tr><td colspan="4" class="text-center py-4 text-gray-500">No transactions found.</td></tr>`;
    return;
  }

  transactions.forEach((txn) => {
    totalSum += parseFloat(txn.total_amount);

    const row = document.createElement("tr");
    row.innerHTML = `
      <td class="py-3">${txn.transaction_id}</td>
      <td class="py-3">₱${parseFloat(txn.total_amount)}</td>
      <td class="py-3">${new Date(txn.date_added).toLocaleString()}</td>
      <td class="py-3">
        <button class="view-btn bg-[#324149] text-white px-3 py-1 rounded hover:bg-[#6e7a86] transition"
          data-id="${txn.transaction_id}">
          View
        </button>
      </td>
    `;
    tbody.appendChild(row);
  });

  tfoot.innerHTML = `
    <tr>
      <td colspan="3" class="py-3 text-right">Total Sales:</td>
      <td class="py-3 text-green-700 font-bold">₱${totalSum.toFixed(2)}</td>
    </tr>
  `;

  document.querySelectorAll(".view-btn").forEach((btn) => {
    btn.addEventListener("click", () => showTransactionDetails(btn.dataset.id));
  });
}

async function showTransactionDetails(transactionId) {
  try {
    const response = await fetch("core/api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "get_transaction_details",
        transaction_id: transactionId,
      }),
    });

    const result = await response.json();

    if (result.success) {
      const itemsHtml = result.items
        .map(
          (item) => `
        <tr>
          <td class="py-2">${item.product_name}</td>
          <td class="py-2 text-center">${item.quantity}</td>
          <td class="py-2 text-right">₱${parseFloat(item.subtotal)}</td>
        </tr>
      `
        )
        .join("");

      return crafteSwal.fire({
        icon: "info",
        title: "Transaction Details",
        html: `
      <table class="w-full text-left border-collapse mt-2">
        <thead class="border-b border-gray-300">
          <tr class="text-left">
            <th class="py-1">Product</th>
            <th class="py-1 text-center">Qty</th>
            <th class="py-1 text-right">Subtotal</th>
          </tr>
        </thead>
        <tbody>${itemsHtml}</tbody>
      </table>
    `,
        width: "600px",
        showCloseButton: true,
        showConfirmButton: false,
      });
    } else {
      showError(result.message);
    }
  } catch (error) {
    console.error("Fetch error:", error);
    showError("Error loading transaction details");
  }
}
