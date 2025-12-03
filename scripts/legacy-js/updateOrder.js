let currentOrder = [];

function addToOrder(event) {
  event.preventDefault();

  const formData = new FormData(event.target);
  const data = Object.fromEntries(formData.entries());

  const productId = parseInt(data.productId);
  const productName = data.productName;
  const price = parseInt(data.price);
  const quantity = parseInt(data.quantity);

  if (quantity < 1) {
    showError("Please enter a valid quantity");
    return;
  }

  const existingItemIndex = currentOrder.findIndex(
    (item) => item.productId === productId
  );
  if (existingItemIndex !== -1) {
    currentOrder[existingItemIndex].quantity += quantity;
    currentOrder[existingItemIndex].subtotal =
      currentOrder[existingItemIndex].quantity * price;
  } else {
    currentOrder.push({
      productId,
      productName,
      price,
      quantity,
      subtotal: quantity * price,
    });
  }

  event.target.quantity.value = 1;

  updateOrderDisplay();
  showSuccess("Item added to order");
}

function updateOrderDisplay() {
  const orderItemsDiv = document.getElementById("order-items");
  const totalAmountSpan = document.getElementById("total-amount");

  if (currentOrder.length === 0) {
    orderItemsDiv.innerHTML =
      '<p class="text-sm text-gray-500">Your ordered items will appear here...</p>';
    totalAmountSpan.textContent = "₱0.00";
    return;
  }

  let html = "";
  let total = 0;

  currentOrder.forEach((item) => {
    total += item.subtotal;
    html += `
        <div class="flex justify-between items-start">
            <div>
                <p class="font-medium">${item.productName}</p>
                <p class="text-sm text-gray-500">₱${item.price} × ${item.quantity}</p>
            </div>
            <div class="text-right">
                <p class="font-medium">₱${item.subtotal}</p>
            </div>
        </div>`;
  });

  orderItemsDiv.innerHTML = html;
  totalAmountSpan.textContent = `₱${total}`;
}

async function processPayment(event) {
  event.preventDefault();

  if (currentOrder.length === 0) {
    showError("Please add items to your order first");
    return;
  }

  const cashAmount = parseFloat(document.getElementById("cash-amount").value);
  const totalAmount = currentOrder.reduce(
    (sum, item) => sum + item.subtotal,
    0
  );

  if (!cashAmount || cashAmount < totalAmount) {
    showError("Please enter a valid cash amount");
    return;
  }

  const change = cashAmount - totalAmount;

  const confirmResult = await showConfirmPayment(
    totalAmount,
    cashAmount,
    change
  );

  if (!confirmResult.isConfirmed) return;

  try {
    const response = await fetch("core/api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "process_transaction",
        order: {
          items: currentOrder,
          totalAmount,
          cashAmount,
        },
      }),
    });

    const result = await response.json();

    if (result.success) {
      showPaymentSuccess(result, change);

      currentOrder = [];
      updateOrderDisplay();
      document.getElementById("cash-amount").value = "";
    } else {
      showError(`Transaction failed: ${result.message}`);
    }
  } catch (error) {
    showError(
      `An error occurred while processing the payment: ${error.message}`
    );
  }
}
