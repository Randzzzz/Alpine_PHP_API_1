function toggleMenu() {
  const menu = document.getElementById("mobileMenu");
  menu.classList.toggle("hidden");
}

function showMenu(category) {
  const items = document.querySelectorAll(".item-container");

  items.forEach((item) => {
    const itemCategory = item.getAttribute("data-category");
    if (category === "all" || itemCategory === category) {
      item.classList.remove("hidden");
      item.style.display = "block";
    } else {
      item.classList.add("hidden");
      item.style.display = "none";
    }
  });
}

async function loadMenuItems() {
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
        item.setAttribute("data-category", prod.category || "all");

        item.innerHTML = `
          <div class="h-48 overflow-hidden rounded-xl">
            <img src="${prod.product_image}" class="w-full h-full object-cover" />
          </div>
          <div class="mt-4">
            <h3 class="text-2xl font-bold">${prod.product_name}</h3>
            <p class="text-2xl font-semibold mt-2">&#8369;${prod.price}</p>
            <form class="mt-4 space-y-2" onsubmit="addToOrder(event)">
              <input type="hidden" name="productId" value="${prod.product_id}">
              <input type="hidden" name="productName" value="${prod.product_name}">
              <input type="hidden" name="price" value="${prod.price}">
              <label for="quantity-${prod.product_id}" class="block text-sm font-medium text-gray-600">Quantity:</label>
              <input type="number" name="quantity" id="quantity-${prod.product_id}" min="1" value="1" 
                    class="w-full border border-gray-300 rounded px-3 py-1" />
              <button type="submit" class="w-full bg-[#324149] text-white py-2 rounded hover:bg-[#6e7a86] transition">
                Add to Order
              </button>
            </form>
          </div>
        `;
        container.appendChild(item);
      });

      showMenu("all");
    } else {
      showError("Failed to load menu items", result.message);
    }
  } catch (error) {
    showError("An error occurred while loading the menu items", error);
  }
}

document.addEventListener("DOMContentLoaded", loadMenuItems);
