document.addEventListener("DOMContentLoaded", loadStaffList);

async function loadStaffList() {
  try {
    const response = await fetch("core/api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ action: "get_staff" }),
    });

    const result = await response.json();

    if (result.success) {
      displayStaffList(result.staff);
    } else {
      showError(result.message);
    }
  } catch (error) {
    showError("Error loading staff list");
  }
}

function displayStaffList(staffList) {
  const staffListElement = document.getElementById("staffList");
  staffListElement.innerHTML = "";

  staffList.forEach((staff) => {
    const row = document.createElement("tr");

    const isActive = staff.status === "active";

    row.innerHTML = `
    <td class="px-6 py-4 whitespace-nowrap text-center">
      <div class="text-sm font-medium text-gray-900">${staff.username}</div>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-center">
      <div class="text-sm text-gray-900">${staff.first_name} ${
      staff.last_name
    }</div>

    </td>
    <td class="px-6 py-4 whitespace-nowrap text-center space-x-2">
      <button class="status-btn px-3 py-1 text-xs font-semibold rounded-full transition ${
        isActive
          ? "bg-green-100 text-green-800 cursor-not-allowed opacity-70"
          : "bg-gray-200 text-gray-600 hover:bg-green-50 hover:text-green-700"
      }" data-user-id="${staff.user_id}" data-status="active"${
      isActive ? "disabled" : ""
    }>Active</button>
        <button class="status-btn px-3 py-1 text-xs font-semibold rounded-full transition ${
          !isActive
            ? "bg-red-100 text-red-800 cursor-not-allowed opacity-70"
            : "bg-gray-200 text-gray-600 hover:bg-red-50 hover:text-red-700"
        }" data-user-id="${staff.user_id}" data-status="suspended" ${
      !isActive ? "disabled" : ""
    }>Suspended</button>
    </td>
    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-center">${new Date(
      staff.date_added
    ).toLocaleDateString()}
    </td>

    `;
    staffListElement.appendChild(row);
  });

  document.querySelectorAll(".status-btn:not([disabled])").forEach((btn) => {
    btn.addEventListener("click", async () => {
      const userId = btn.dataset.userId;
      const newStatus = btn.dataset.status;

      try {
        const success = await updateStaffStatus(userId, newStatus);
        if (success) {
          showSuccess(
            `Staff status ${
              newStatus === "active" ? "activated" : "suspended"
            } successfully`
          );
          loadStaffList();
        } else {
          showError("Failed to update staff status");
        }
      } catch (error) {
        showError("Error updating staff status");
      }
    });
  });
}

async function addStaff(event) {
  event.preventDefault();

  const formData = new FormData(event.target);
  const data = Object.fromEntries(formData.entries());

  try {
    const response = await fetch("core/api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "add_staff",
        username: data.username,
        first_name: data.first_name,
        last_name: data.last_name,
        password: data.password,
        confirm_password: data.confirm_password,
        role: "admin",
      }),
    });

    const result = await response.json();

    if (result.success) {
      showSuccess("Staff added successfully");

      event.target.reset();
      loadStaffList();
    } else {
      showError(`Adding staff failed: ${result.message}`);
    }
  } catch (error) {
    showError("Error adding staff");
  }
}

async function updateStaffStatus(userId, newStatus) {
  try {
    const response = await fetch("core/api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        action: "update_staff_status",
        user_id: userId,
        status: newStatus,
      }),
    });

    const result = await response.json();
    return result.success;
  } catch (error) {
    showError("Error updating staff status");
  }
}
