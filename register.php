<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
  <link rel="stylesheet" href="styles.css" />
  <link rel="icon" type="image/x-icon" href="images/logo.png" />
</head>
<body class="flex flex-col min-h-screen bg-[#f1f3f4] text-[#324149]">
  <header class="sticky top-0 z-50 bg-white shadow-md border-b border-gray-200">
    <nav class="container mx-auto flex items-center justify-between px-6 py-4">
      <div class="flex items-center space-x-3">
        <img src="images/logo.png" class="h-10" alt="Bistro Crafté Logo" />
        <span class="font-extrabold text-2xl tracking-wide">Bistro Crafté</span>
      </div>
      <div class="flex items-center space-x-3">
      <a href="index.php" class="hidden md:inline-block bg-[#324149] text-white px-4 py-2 rounded-lg hover:bg-[#6e7a86] transition cursor-pointer">
        Order Kiosk
      </a>
    </nav>
  </header>
  <main class="flex-grow flex items-center justify-center px-6 py-16">
    <div class="bg-white shadow-lg rounded-2xl p-10 w-full max-w-md border border-gray-100">
      <div class="text-center mb-8">
        <img src="images/logo.png" alt="Bistro Crafté" class="mx-auto h-16 mb-3">
        <h1 class="text-3xl font-extrabold text-[#324149]">Crafté Journey</h1>
        <p class="text-gray-600 mt-2">Register for Crafté access.</p>
      </div>

      <form x-data="registerUser()" @submit.prevent="submit" class="space-y-5">
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Username:</label>
          <input type="text" x-model="username"
            class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#6e7a86] focus:border-transparent transition">
        </div>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">First name:</label>
            <input type="text" x-model="first_name" 
              class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#6e7a86] focus:border-transparent transition">
          </div>
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-1">Last name:</label>
            <input type="text" x-model="last_name" 
              class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#6e7a86] focus:border-transparent transition">
          </div>
        </div>
        
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Verification Code:</label>
          <input type="password" x-model="superadmin_code" 
            class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#6e7a86] focus:border-transparent transition" 
            placeholder="Enter verification code">
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Password:</label>
          <input type="password" x-model="password" 
            class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#6e7a86] focus:border-transparent transition">
        </div>
        <div>
          <label class="block text-sm font-semibold text-gray-700 mb-1">Confirm Password:</label>
          <input type="password" x-model="confirm_password" 
            class="block w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#6e7a86] focus:border-transparent transition">
        </div>

        <button type="submit"
          class="w-full bg-[#324149] text-white font-semibold py-2.5 rounded-lg hover:bg-[#6e7a86] transition">
          Register Admin Account
        </button>

        <p class="text-center text-sm text-gray-600 mt-4">
          Already have an account?
          <a href="index.php" class="text-[#324149] font-semibold hover:underline">Login here</a>
        </p>
      </form>
    </div>
  </main>

  <footer class="border-t border-gray-300 bg-white py-6 text-center text-sm text-gray-600">
    © 2025 Bistro Crafté. All rights reserved.
  </footer>

  <script src="scripts/crafteSwal.js"></script>
  <script>
    function registerUser() {
      return {
        username: '',
        first_name: '',
        last_name: '',
        superadmin_code: '',
        password: '',
        confirm_password: '',

        async submit() {
          try {
            const response = await fetch('core/api.php', {
              method: 'POST',
              headers: {
                'Content-Type': 'application/json',
              },
              body: JSON.stringify({
                action: 'register',
                username: this.username,
                first_name: this.first_name,
                last_name: this.last_name,
                superadmin_code: this.superadmin_code,
                password: this.password,
                confirm_password: this.confirm_password,
                superadmin_code: this.superadmin_code,
                role: 'superadmin',
              }),
            });

            const result = await response.json();

            if (result.success) {
              showSuccess(result.message);
              setTimeout(() => {
                window.location.href = "login.php";
              }, 2000);
            } else {
              showError(`Registration failed: ${result.message}`);
            }
          } catch (error) {
            showError(`registration error: ${error.message}`);
          }
        },
      };
    } 
  </script>
</body>
</html>