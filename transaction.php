<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

if (isset($_SESSION['status']) && $_SESSION['status'] === 'suspended') {
  session_destroy();
  header("Location: login.php");
  exit();
}

$allowed_roles = ['admin', 'superadmin'];
if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
  header("Location: login.php");
  exit();
}

$username = $_SESSION['username'];
$role = ucfirst($_SESSION['role']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="styles.css" />
  <link rel="icon" type="image/x-icon" href="images/logo.png" />
</head>
<body x-data="transactionManager()" x-init="loadTransactionList()" class="flex flex-col min-h-screen bg-[#f1f3f4] text-[#324149]">
  <header class="sticky top-0 z-50 bg-white shadow-md border-b border-gray-200">
    <nav class="container mx-auto flex items-center justify-between px-6 py-4">
      <div class="flex items-center space-x-3">
        <img src="images/logo.png" class="h-10" alt="Bistro Crafté Logo" />
        <span class="font-extrabold text-2xl tracking-wide">Bistro Crafté</span>
      </div>
      <ul class="hidden md:flex space-x-8 font-semibold text-lg">
        <li><a href="dashboard.php" class="hover:text-[#6e7a86]">Product Catalog</a></li>
        <li><a href="staffs.php" class="hover:text-[#6e7a86]">Staff Management</a></li>
        <li><a href="transaction.php" class="hover:text-[#6e7a86]">Transaction History</a></li>
      </ul>
      <div class="flex items-center space-x-3">
        <div class="text-sm opacity-75">Logged in as: <?php echo $username; ?> (<?php echo $role; ?>)</div>
      <a href="core/logout.php" class="hidden md:inline-block bg-[#324149] text-white px-4 py-2 rounded-lg hover:bg-[#6e7a86] transition cursor-pointer">
        Logout
      </a>
    </nav>
  </header>

  <!-- body -->
  <main class="flex-1 container mx-auto px-6 py-10 flex flex-col lg:flex-row gap-10">
    <section class="menu flex-1">
      <div class="text-center mb-12">
        <h1 class="menu-title text-5xl font-extrabold mb-4">Customer Transactions</h1>
        <p class="text-xl text-gray-700 max-w-2xl mx-auto">
          Review café’s sales and orders.
        </p>
      </div>

      <div class="flex items-center gap-4 mb-4">
        <input type="date" x-model="date_start" class="border rounded-lg px-3 py-2">
        <span class="text-gray-600">to</span>
        <input type="date" x-model="date_end" class="border rounded-lg px-3 py-2">
        <button @click="loadTransactionList(date_start, date_end)" class="bg-[#324149] text-white px-4 py-2 rounded-lg hover:bg-[#6e7a86] transition">Filter</button>
      </div>

      <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full table-auto">
          <thead class="bg-gray-50">
            <tr class="">
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase text-center">Transaction ID</th>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase text-center">Total Amount</th>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase text-center">Date Added</th>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase text-center">Subtotal</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-gray-200 text-center">
            <template x-if="transactions.length === 0">
              <tr><td colspan="4" class="text-center py-4 text-gray-500">No transactions found.</td></tr>
            </template>
            <template x-for="txn in transactions" :key="txn.transaction_id">
              <tr>
                <td class="py-3" x-text="txn.transaction_id"></td>
                <td class="py-3">₱<span x-text="parseFloat(txn.total_amount)"></span></td>
                <td class="py-3" x-text="(new Date(txn.date_added)).toLocaleString()"></td>
                <td class="py-3">
                  <button class="view-btn bg-[#324149] text-white px-3 py-1 rounded hover:bg-[#6e7a86] transition"
                    @click="showTransactionDetails(txn.transaction_id)">
                    View
                  </button>
                </td>
              </tr>
            </template>
          </tbody>
          <tfoot class="bg-gray-100 font-bold text-center">
            <tr>
              <td colspan="3" class="py-3 text-right">Total Sales:</td>
              <td class="py-3 text-green-700 font-bold">₱<span x-text="getTotal()"></span></td>
            </tr>
          </tfoot>
        </table>
      </div>
    </section>

    <aside class="w-full lg:w-80 h-fit self-start bg-white rounded-2xl shadow-md border border-gray-200 p-6 lg:sticky lg:top-24">
      <h2 class="text-2xl font-bold border-b pb-2 mb-4 text-center">PDF Report</h2>
      <button @click="printTable" class="w-full bg-[#324149] text-white py-2 rounded hover:bg-[#6e7a86] transition">Print</button>
    </aside>
  </main>
  
  <footer class="border-t border-gray-300 mt-auto bg-white py-8">
    <div class="container mx-auto px-10 flex flex-col md:flex-row md:justify-between items-center md:items-start gap-8 md:gap-32 lg:gap-40">
      <div class="flex justify-center md:justify-start">
        <img 
          src="images/footer.png" 
          alt="Bistro Crafté Logo" 
          class="w-52 md:w-60 h-auto object-contain" 
        />
      </div>
      <div class="flex flex-col sm:flex-row justify-center md:justify-between items-center md:items-start gap-12 md:gap-20 text-center md:text-left font-medium text-lg text-[#324149] w-full md:w-auto">
        <div class="space-y-2">
          <a href="#" class="block hover:underline">Privacy Notice</a>
          <a href="#" class="block hover:underline">Health Privacy Notice</a>
          <a href="#" class="block hover:underline">Terms of Use</a>
          <a href="#" class="block hover:underline">Cookie Preferences</a>
        </div>

        <div class="hidden sm:block w-px h-20"></div>
        <div class="space-y-2">
          <a href="#" class="block hover:underline">Tiktok</a>
          <a href="#" class="block hover:underline">Facebook</a>
          <a href="#" class="block hover:underline">Instagram</a>
        </div>
      </div>
    </div>
    <div class="border-t mt-8 pt-4 text-center text-sm text-gray-600">
      © 2025 Bistro Crafté. All rights reserved.
    </div>
  </footer>
  <script src="scripts/crafteSwal.js"></script>
  <script>
    function transactionManager() {
      return {
        transactions: [],
        date_start: "",
        date_end: "",

        async loadTransactionList(start = "", end = "") {
          try {
            const response = await fetch("core/api.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
              body: JSON.stringify({
                action: "get_transaction",
                start_date: start,
                end_date: end,
              }),
            });

            const result = await response.json();
            if (result.success) {
              this.transactions = result.transactions || [];
            } else {
              showError(result.message);
            }
          } catch (error) {
            console.error("Fetch error:", error);
            showError("Error loading transaction list");
          }
        },

        getTotal() {
          return this.transactions.reduce(
            (sum, t) => sum + parseFloat(t.total_amount || 0),
            0
          );
        },

        printTable() {
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
        },

        async showTransactionDetails(transactionId) {
          try {
            const response = await fetch("core/api.php", {
              method: "POST",
              headers: { "Content-Type": "application/json" },
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
                  </tr>`
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
        },
      };
    }

    document.addEventListener("alpine:init", () => {
      Alpine.data("transactionManager", transactionManager);
    });
  </script>
</body>
</html>