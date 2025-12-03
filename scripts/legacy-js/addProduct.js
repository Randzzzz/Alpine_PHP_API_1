async function loadMenuItemsManagement() {
  try {
    const response = await fetch("core/api.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ action: "get_products" }),
    });

    const result = await response.json();

    if (result.success) {
      const container = document.querySelector(".menu-container");
      container.innerHTML = "";

      result.products.forEach((prod) => {
        const item = document.createElement("div");
        item.className =
          "item-container bg-white rounded-2xl p-6 shadow-md hover:shadow-xl transition transform hover:-translate-y-1 cursor-pointer";
        item.setAttribute("data-category", "all");
        item.innerHTML = `
          <div class="h-48 overflow-hidden rounded-xl">
            <img src="${prod.product_image}" class="w-full h-full object-cover" />
          </div>
          <div class="mt-4">
            <h3 class="text-2xl font-bold">${prod.product_name}</h3>
            <p class="text-sm text-gray-600 mt-1">${prod.category}</p>
            <p class="text-2xl font-semibold mt-2">&#8369;${prod.price}</p>
            <div class="mt-4 text-sm text-gray-600">Added by: <strong>${prod.added_by_name}</strong></div>
          </div>
        `;
        container.appendChild(item);
      });
    } else {
      showError("Failed to load menu items", result.message);
    }
  } catch (error) {
    console.error("Menu load error:", error);
    showError("An error occurred while loading menu items: " + error.message);
  }
}

function previewImage(event) {
  const file = event.target.files[0];
  const preview = document.getElementById("preview");
  if (!file) {
    preview.src = "";
    preview.classList.add("hidden");
    return;
  }
  const url = URL.createObjectURL(file);
  preview.src = url;
  preview.classList.remove("hidden");
}

async function addProduct(event) {
  event.preventDefault();

  const form = document.getElementById("addProductForm");
  const formData = new FormData(form);
  formData.append("action", "add_product");

  try {
    const response = await fetch("core/api.php", {
      method: "POST",
      body: formData,
    });

    const result = await response.json();
    if (result.success) {
      showSuccess(result.message);

      form.reset();
      document.getElementById("preview").classList.add("hidden");
      loadMenuItemsManagement();
    } else {
      showError(result.message);
    }
  } catch (error) {
    showError("An error occurred while adding the product", error);
  }
}

document.addEventListener("DOMContentLoaded", loadMenuItemsManagement);
