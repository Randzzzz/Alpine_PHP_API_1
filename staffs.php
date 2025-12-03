<?php
session_start();

if (!isset($_SESSION['user_id'])) {
  header("Location: login.php");
  exit();
}

if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], ['superadmin'])) {
  header("Location: dashboard.php");
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
<body x-data="staffManager()" x-init="loadStaffList()" class="flex flex-col min-h-screen bg-[#f1f3f4] text-[#324149]">
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
        <h1 class="menu-title text-5xl font-extrabold mb-4">Cashier Accounts</h1>
        <p class="text-xl text-gray-700 max-w-2xl mx-auto">
          Manage your café crew here.
        </p>
      </div>

      <div class="overflow-x-auto bg-white rounded-lg shadow">
        <table class="min-w-full table-auto">
          <thead class="bg-gray-50">
            <tr class="">
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase text-center">Username</th>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase text-center">Name</th>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase text-center">Status</th>
              <th class="px-6 py-3 text-left text-sm font-medium text-gray-500 uppercase text-center">Date Added</th>
            </tr>
          </thead>
          <tbody id="staffList" class="bg-white divide-y divide-gray-200">
            <template x-for="staff in staffList" :key="staff.user_id">
              <tr>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <div class="text-sm font-medium text-gray-900" x-text="staff.username"></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center">
                  <div class="text-sm text-gray-900" x-text="staff.first_name + ' ' + staff.last_name"></div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
                  <button
                    class="status-btn px-3 py-1 text-xs font-semibold rounded-full transition"
                    :class="staff.status === 'active' ? 'bg-green-100 text-green-800 cursor-not-allowed opacity-70' : 'bg-gray-200 text-gray-600 hover:bg-green-50 hover:text-green-700'"
                    :disabled="staff.status === 'active'"
                    @click="updateStaffStatus(staff.user_id, 'active')"
                  >
                    Active
                  </button>

                  <button
                    class="status-btn px-3 py-1 text-xs font-semibold rounded-full transition"
                    :class="staff.status !== 'active' ? 'bg-red-100 text-red-800 cursor-not-allowed opacity-70' : 'bg-gray-200 text-gray-600 hover:bg-red-50 hover:text-red-700'"
                    :disabled="staff.status !== 'active'"
                    @click="updateStaffStatus(staff.user_id, 'suspended')"
                  >
                    Suspended
                  </button>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center" x-text="(new Date(staff.date_added)).toLocaleDateString()"></td>
              </tr>
            </template>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Add Cashier -->
    <aside class="w-full lg:w-80 h-fit self-start bg-white rounded-2xl shadow-md border border-gray-200 p-6 lg:sticky lg:top-24">
      <h2 class="text-2xl font-bold border-b pb-2 mb-4">Add New Cashier</h2>
        <div class="mt-5 space-y-3">
          <form @submit.prevent="addStaff">
            <div class="space-y-3">
              <div>
                <label class="block font-semibold text-sm opacity-75">Username:</label>
                <input type="text" x-model="newStaff.username" class="w-full border rounded px-3 py-2" >
              </div>
              <div>
                <label class="block font-semibold text-sm opacity-75">First name:</label>
                <input type="text" x-model="newStaff.first_name" class="w-full border rounded px-3 py-2" >
              </div>
              <div>
                <label class="block font-semibold text-sm opacity-75">Last name:</label>
                <input type="text"x-model="newStaff.last_name" class="w-full border rounded px-3 py-2" >
              </div>
              <div>
                <label class="block font-semibold text-sm opacity-75">Password:</label>
                <input type="password" x-model="newStaff.password" class="w-full border rounded px-3 py-2" >
              </div>
              <div>
                <label class="block font-semibold text-sm opacity-75">Confirm Password:</label>
                <input type="password"x-model="newStaff.confirm_password" class="w-full border rounded px-3 py-2" >
              </div>
              <button type="submit" class="w-full bg-[#324149] text-white py-2 rounded hover:bg-[#6e7a86] transition">
                Add Cashier
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
    function staffManager() {
      return {
        staffList: [],
        newStaff: {
          username: '',
          first_name: '',
          last_name: '',
          password: '',
          confirm_password: ''
        },

        async loadStaffList() {
          try {
            const response = await fetch('core/api.php', {
              method: 'POST',
              headers: {'Content-Type': 'application/json'},
              body: JSON.stringify({action: 'get_staff'})
            });

            const result = await response.json();

            if (result.success) {
              this.staffList = result.staff || [];
            } else {
              showError(result.message);
            }
          } catch (error) {
            showError('Error loading staff list');
          }
        },

        async updateStaffStatus(userId, newStatus) {
          try {
            const response = await fetch('core/api.php', {
              method: 'POST',
              headers: {'Content-Type': 'application/json'},
              body: JSON.stringify({action: 'update_staff_status', user_id: userId, status: newStatus})
            });

            const result = await response.json();
            if (result.success) {
              showSuccess(`Staff status ${newStatus === 'active' ? 'activated' : 'suspended'} successfully`);
              this.loadStaffList();
              return true;
            } else {
              showError('Failed to update staff status');
            }
          } catch (error) {
            showError('Error updating staff status');
          }
        },

        async addStaff() {
          if (!this.newStaff.username || !this.newStaff.password) {
            showError('Please fill required fields');
            return;
          }
          if (this.newStaff.password !== this.newStaff.confirm_password) {
            showError('Passwords do not match');
            return;
          }

          try {
            const response = await fetch('core/api.php', {
              method: 'POST',
              headers: {'Content-Type': 'application/json'},
              body: JSON.stringify({
                action: 'add_staff',
                username: this.newStaff.username,
                first_name: this.newStaff.first_name,
                last_name: this.newStaff.last_name,
                password: this.newStaff.password,
                confirm_password: this.newStaff.confirm_password,
                role: 'admin'
              })
            });

            const result = await response.json();
            
            if (result.success) {
              showSuccess('Staff added successfully');
              this.newStaff = { username: '', first_name: '', last_name: '', password: '', confirm_password: '' };
              this.loadStaffList();
            } else {
              showError(`Adding staff failed: ${result.message}`);
            }
          } catch (error) {
            showError('Error adding staff');
          }
        }
      };
    }

    document.addEventListener('alpine:init', () => {
      Alpine.data('staffManager', staffManager);
    });
  </script>
</body>
</html>