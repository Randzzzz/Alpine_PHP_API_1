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
<body x-data="menuManager()" x-init="loadMenuItemsManagement()" class="flex flex-col min-h-screen bg-[#f1f3f4] text-[#324149]">
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
        <h1 class="menu-title text-5xl font-extrabold mb-4">Menu Manager</h1>
        <p class="text-xl text-gray-700 max-w-2xl mx-auto">
          Bistro Crafté Menu Management
        </p>
      </div>

      <div class="menu-container grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <template x-for="prod in products" :key="prod.product_id">
          <div class="item-container bg-white rounded-2xl p-6 shadow-md hover:shadow-xl transition transform hover:-translate-y-1 cursor-pointer">
            <div class="h-48 overflow-hidden rounded-xl">
              <img :src="prod.product_image" class="w-full h-full object-cover" />
            </div>
            <div class="mt-4">
              <h3 class="text-2xl font-bold" x-text="prod.product_name"></h3>
              <p class="text-sm text-gray-600 mt-1" x-text="prod.category"></p>
              <p class="text-2xl font-semibold mt-2">&#8369;<span x-text="prod.price"></span></p>
              <div class="mt-4 text-sm text-gray-600">Added by: <strong x-text="prod.added_by_name"></strong></div>
            </div>
          </div>
        </template>
      </div>
    </section>

    <!-- Add Product -->
    <aside class="w-full lg:w-80 h-fit self-start bg-white rounded-2xl shadow-md border border-gray-200 p-6 lg:sticky lg:top-24">
      <h2 class="text-2xl font-bold border-b pb-2 mb-4">Add New Product</h2>
        <div class="mt-5 space-y-3">
          <form enctype="multipart/form-data" @submit.prevent="addProduct">
            <div class="space-y-3">
              <div>
                <label class="block font-semibold text-sm opacity-75">Product Name:</label>
                <input type="text"class="w-full border rounded px-3 py-2" x-model="newProduct.product_name" required>
              </div>

              <div>
                <label class="block font-semibold text-sm opacity-75">Price:</label>
                <input type="number"class="w-full border rounded px-3 py-2" x-model="newProduct.price" required>
              </div>
              <div>
                <label class="block font-semibold text-sm opacity-75">Category:</label>
                <select name="category" class="w-full border rounded px-3 py-2" x-model="newProduct.category">
                  <option value="signatures">Signatures</option>
                  <option value="coffee">Coffee</option>
                  <option value="non-coffee">Non-Coffee</option>
                  <option value="pastries">Pastries</option>
                  <option value="pasta">Pasta</option>
                </select>
              </div>
              <div>
                <label class="block font-semibold text-sm opacity-75">Product Image:</label>
                <input type="file" accept="image/*" class="w-full border rounded px-3 py-2" @change="previewImage($event)" required>
                <div id="imagePreview" class="mt-3">
                  <img id="preview" :src="previewUrl" alt="" x-show="previewUrl" class="w-32 h-32 object-cover rounded-lg border">
                </div>
              </div>

              <button type="submit" class="w-full bg-[#324149]  text-white py-2 rounded hover:bg-[#6e7a86] transition">
                Add Product
              </button>
            </div>
          </form>
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
  <script src="scripts/crafteSwal.js"></script>
  <script>
    function menuManager() {
      return {
        products: [],
        previewUrl: '',
        newProduct: {
          product_name: '',
          price: '',
          category: '',
          product_image: null,
        },

        async loadMenuItemsManagement() {
          try {
            const response = await fetch('core/api.php', {
              method: 'POST',
              headers: {'Content-Type': 'application/json'},
              body: JSON.stringify({action: 'get_products'}),
            });

            const result = await response.json();
            if (result.success) {
              this.products = result.products || [];
            } else {
              showError('Failed to load menu items', result.message);
            }
          } catch (error) {
            console.error('Menu load error:', error);
            showError('An error occurred while loading menu items: ' + error.message);
          }
        },

        previewImage(event) {
          const file = event.target.files[0];
          this.newProduct.product_image = file;
          this.previewUrl = URL.createObjectURL(file);
        },

        async addProduct() {
          const formData = new FormData();
          formData.append('action', 'add_product');
          formData.append('product_name', this.newProduct.product_name);
          formData.append('price', this.newProduct.price);
          formData.append('category', this.newProduct.category);
          formData.append('product_image', this.newProduct.product_image);

          try {
            const response = await fetch('core/api.php', {
              method: 'POST',
              body: formData,
            });

            const result = await response.json();
            if (result.success) {
              showSuccess(result.message);
              this.newProduct = { product_name: '', price: '', category: 'signatures', product_image: null };
              this.previewUrl = '';
              this.loadMenuItemsManagement();
            } else {
              showError(result.message);
            }
          } catch (error) {
            showError('An error occurred while adding the product', error);
          }
        },
      };
    }

    document.addEventListener('alpine:init', () => {
      Alpine.data('menuManager', menuManager);
    });
  </script>
</body>
</html>