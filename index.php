<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Bistro Crafté</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="styles.css" />
  <link rel="icon" type="image/x-icon" href="images/logo.png" />
</head>
<body x-data="menuOrder()" x-init="loadMenuItems()" class="flex flex-col min-h-screen bg-[#f1f3f4] text-[#324149]">

  <header class="sticky top-0 z-50 bg-white shadow-md border-b border-gray-200">
    <nav class="container mx-auto flex items-center justify-between px-6 py-4">
      <div class="flex items-center space-x-3">
        <img src="images/logo.png" class="h-10" alt="Bistro Crafté Logo" />
        <span class="font-extrabold text-2xl tracking-wide">Bistro Crafté</span>
      </div>
      <ul class="hidden md:flex space-x-8 font-semibold text-lg">
        <li><button class="hover:text-[#6e7a86]" @click="selectCategory('signatures')">Signatures</button></li>
        <li><button class="hover:text-[#6e7a86]" @click="selectCategory('coffee')">Coffee</button></li>
        <li><button class="hover:text-[#6e7a86]" @click="selectCategory('non-coffee')">Non-Coffee</button></li>
        <li><button class="hover:text-[#6e7a86]" @click="selectCategory('pastries')">Pastries</button></li>
        <li><button class="hover:text-[#6e7a86]" @click="selectCategory('pasta')">Pasta</button></li>
        <li><button class="hover:text-[#6e7a86]" @click="selectCategory('all')">All Menu</button></li>
      </ul>
      <div class="flex items-center space-x-3">
      <a href="login.php" class="hidden md:inline-block bg-[#324149] text-white px-4 py-2 rounded-lg hover:bg-[#6e7a86] transition cursor-pointer">
        User Login
      </a>
      <button class="md:hidden p-2 border rounded text-[#1c0d08]" @click="mobileOpen = !mobileOpen">☰</button>
    </nav>
    <div x-cloak x-show="mobileOpen" class="flex-col bg-white border-t border-gray-200 px-6 py-4 space-y-2 md:hidden">
      <button class="block text-left hover:text-[#6e7a86]" @click="selectCategory('signatures')">Signatures</button>
      <button class="block text-left hover:text-[#6e7a86]" @click="selectCategory('coffee')">Coffee</button>
      <button class="block text-left hover:text-[#6e7a86]" @click="selectCategory('non-coffee')">Non-Coffee</button>
      <button class="block text-left hover:text-[#6e7a86]" @click="selectCategory('pastries')">Pastries</button>
      <button class="block text-left hover:text-[#6e7a86]" @click="selectCategory('pasta')">Pasta</button>
      <button class="block text-left hover:text-[#6e7a86]" @click="selectCategory('all')">All Menu</button>
    </div>
  </header>


  <main class="flex-1 container mx-auto px-6 py-10 flex flex-col lg:flex-row gap-10">
    <section class="menu flex-1">
      <div class="text-center mb-12">
        <h1 class="menu-title text-5xl font-extrabold mb-4">Bistro Crafté Kiosk</h1>
        <p class="text-xl text-gray-700 max-w-2xl mx-auto">
          Explore our handcrafted selections made with passion and the finest ingredients.
        </p>
      </div>

      <div class="menu-container grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <template x-for="product in filteredProducts()" :key="product.product_id">
          <div class="item-container bg-white rounded-2xl p-6 shadow-md hover:shadow-xl transition transform hover:-translate-y-1 cursor-pointer">
            <div class="h-48 overflow-hidden rounded-xl">
              <img :src="product.product_image" class="w-full h-full object-cover" />
            </div>
            <div class="mt-4">
              <h3 class="text-2xl font-bold" x-text="product.product_name"></h3>
              <p class="text-2xl font-semibold mt-2">&#8369;<span x-text="product.price"></span></p>
              <form class="mt-4 space-y-2" @submit.prevent="addToOrder(product.product_id, product.product_name, product.price, quantityTemp[product.product_id])">
                <label :for="'quantity-'+product.product_id" class="block text-sm font-medium text-gray-600">Quantity:</label>
                <input type="number" name="quantity" :id="'quantity-'+product.product_id" min="1" x-model.number="quantityTemp[product.product_id]" class="w-full border border-gray-300 rounded px-3 py-1" />
                <button type="submit" class="w-full bg-[#324149] text-white py-2 rounded hover:bg-[#6e7a86] transition">Add to Order</button>
              </form>
            </div>
          </div>
        </template>
      </div>
    </section>

    <!-- ORDER SUMMARY -->
    <aside class="w-full lg:w-80 h-fit self-start bg-white rounded-2xl shadow-md border border-gray-200 p-6 lg:sticky lg:top-24">
      <h2 class="text-2xl font-bold border-b pb-2 mb-4">Order Summary</h2>
      <div id="order-items" class="space-y-3 text-gray-700">
        <template x-if="cart.length === 0">
          <p class="text-sm text-gray-500">Your ordered items will appear here...</p>
        </template>

        <template x-for="item in cart" :key="item.productId">
          <div class="flex justify-between items-start">
            <div>
              <p class="font-medium" x-text="item.productName"></p>
              <p class="text-sm text-gray-500">₱<span x-text="item.price"></span> × <span x-text="item.quantity"></span></p>
            </div>
            <div class="text-right">
              <p class="font-medium">₱<span x-text="item.subtotal"></span></p>
            </div>
          </div>
        </template>
      </div>
      <div class="border-t mt-4 pt-4 text-[#324149]">
        <div class="flex justify-between text-lg font-semibold">
          <span>Total:</span>
          <span>₱<span x-text="getTotal()"></span></span>
        </div>
        <div class="mt-5 space-y-3">
          <form @submit.prevent="processPayment">
            <input type="number" id="cash-amount" x-model="cashAmount" class="w-full border rounded px-3 py-2 mb-2" placeholder="Input Cash Amount">
            <button id="pay-button" class="w-full bg-[#324149] text-white py-2 rounded hover:bg-[#6e7a86] transition">Pay Now</button>
          </form>
        </div>
      </div>
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


  <button id="scrollToTopBtn" class="hidden fixed bottom-6 right-6 bg-[#324149] text-white p-3 rounded-full shadow-lg hover:bg-[#6e7a86] transition duration-300 z-50">
    ↑
  </button>
  <script src="scripts/scrollButton.js"></script>
  <script src="scripts/crafteSwal.js"></script>
  <script>
    function menuOrder() {
      return {
        products: [],
        selectedCategory: "all",
        mobileOpen: false,
        quantityTemp: {},
        cart: [],
        cashAmount: "",

        async loadMenuItems() {
          try {
            const response = await fetch("core/api.php", {
              method: "POST",
              headers: {"Content-Type": "application/json"},
              body: JSON.stringify({action: "get_products"}),
            });

            const result = await response.json();
            if (result.success) {
              this.products = result.products || [];
              this.products.forEach((p) => {
                this.$nextTick(() => {});
                this.quantityTemp[p.product_id] = 1;
              });
            } else {
              showError("Failed to load menu items");
            }
          } catch (error) {
            showError("An error occurred while loading the menu items");
          }
        },

        filteredProducts() {
          if (!this.products) return [];
          if (this.selectedCategory === "all") return this.products;
          return this.products.filter((p) => p.category === this.selectedCategory);
        },

        selectCategory(cat) {
          this.selectedCategory = cat;
          this.mobileOpen = false;
        },

        addToOrder(productId, productName, price, quantity) {
          quantity = parseInt(quantity) || 1;
          if (quantity < 1) {
            showError("Please enter a valid quantity");
            return;
          }

          const existing = this.cart.find((i) => i.productId === productId);
          if (existing) {
            existing.quantity += quantity;
            existing.subtotal = existing.quantity * price;
          } else {
            this.cart.push({
              productId,
              productName,
              price,
              quantity,
              subtotal: quantity * price,
            });
          }

          this.quantityTemp[productId] = 1;
          showSuccess("Item added to order");
        },

        getTotal() {
          return this.cart.reduce((s, i) => s + i.subtotal, 0);
        },

        async processPayment() {
          if (this.cart.length === 0) {
            showError("Please add items to your order first");
            return;
          }

          const totalAmount = this.getTotal();
          const cash = parseFloat(this.cashAmount);
          if (!cash || cash < totalAmount) {
            showError("Please enter a valid cash amount");
            return;
          }

          const change = cash - totalAmount;
          const confirmResult = await showConfirmPayment(totalAmount, cash, change);
          if (!confirmResult.isConfirmed) return;

          try {
            const response = await fetch("core/api.php", {
              method: "POST",
              headers: {"Content-Type": "application/json"},
              body: JSON.stringify({
                action: "process_transaction",
                order: { items: this.cart, totalAmount, cashAmount: cash },
              }),
            });

            const result = await response.json();
            if (result.success) {
              showPaymentSuccess(result, change);
              this.cart = [];
              this.cashAmount = "";
            } else {
              showError(`Transaction failed: ${result.message}`);
            }
          } catch (err) {
            showError(
              `An error occurred while processing the payment: ${err.message}`
            );
          }
        },
      };
    }

    document.addEventListener("alpine:init", () => {
      Alpine.data("menuOrder", menuOrder);
    });
  </script>
</body>
</html>
