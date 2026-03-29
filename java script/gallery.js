// This file is linked to the HTML page and contains the page’s JavaScript code
// When the page loads, fade in the welcome section
document.addEventListener("DOMContentLoaded", () => {
  document.querySelector(".welcome").style.opacity = "1";
  document.querySelector(".welcome").style.transform = "translateY(0)";
});

// Select the toggle button, all product items, extra items, and the "coming soon" text
const toggleBtn = document.getElementById("toggleBtn");
const allItems = document.querySelectorAll(".item");
const moreItems = document.querySelectorAll(".more-item");
const comingSoon = document.querySelector(".coming-soon");

let isShown = false; // Track if "more items" are currently shown

// On page load, animate the first 3 items
window.addEventListener("DOMContentLoaded", () => {
  allItems.forEach((item, index) => {
    if (index < 3) {
      setTimeout(() => {
        item.classList.add("show"); // Add show animation one by one
      }, index * 150); // Delay for each item
    }
  });
});

// Toggle button click event (Show More / Show Less)
toggleBtn.addEventListener("click", () => {
  if (!isShown) {
    // Show the extra items with animation
    moreItems.forEach((item, index) => {
      item.style.display = "block";
      setTimeout(() => {
        item.classList.add("show");
      }, index * 150);
    });

    // Show "coming soon" text with fade effect
    comingSoon.style.display = "block";
    setTimeout(() => {
      comingSoon.style.opacity = 1;
    }, 50);

    // Change button text to "Show Less"
    toggleBtn.textContent = "▲ Show Less";
    isShown = true;
  } else {
    // Hide the extra items with fade effect
    moreItems.forEach((item) => {
      item.classList.remove("show");
      setTimeout(() => {
        item.style.display = "none";
      }, 500);
    });

    // Hide "coming soon" text with fade effect
    comingSoon.style.opacity = 0;
    setTimeout(() => {
      comingSoon.style.display = "none";
    }, 500);

    // Change button text to "Show More"
    toggleBtn.textContent = "▼ Show More";
    isShown = false;
  }
});

// ===========================================
// 🛒 Cart System + Reservation + Login Check
// ===========================================

let cart = [];
let loggedIn = false;

// ✅ التحقق من حالة تسجيل الدخول من قاعدة البيانات
async function checkLoginStatus() {
  try {
    const res = await fetch("check_login_db.php", { method: "POST" });
    const data = await res.json();

    if (data.status === "loggedin") {
      loggedIn = true;
      document.getElementById("user-sections").classList.remove("hidden");
    }
  } catch (err) {
    console.error("Login check failed:", err);
  }
}

// ✅ تحديث عرض السلة في الصفحة
function updateCartDisplay() {
  const cartContainer = document.getElementById("cart-items");
  cartContainer.innerHTML = "";

  if (cart.length === 0) {
    cartContainer.innerHTML = '<p class="empty-cart">Your cart is empty.</p>';
    updateTotalPrice();
    return;
  }

  cart.forEach((item, index) => {
    const div = document.createElement("div");
    div.classList.add("cart-item");
    div.innerHTML = `
      <div class="cart-item-info">
        <span class="cart-item-name">${item.name}</span>
        <span class="cart-item-qty">× ${item.qty}</span>
      </div>
      <div class="cart-item-actions">
        <span class="cart-item-price">$${(item.price * item.qty).toFixed(
          2
        )}</span>
        <button class="remove-btn" data-index="${index}">✖</button>
      </div>
    `;
    cartContainer.appendChild(div);
  });

  // ✅ عند الضغط على زر الإزالة
  document.querySelectorAll(".remove-btn").forEach((btn) => {
    btn.addEventListener("click", () => {
      const index = btn.dataset.index;
      cart.splice(index, 1);
      updateCartDisplay();
    });
  });

  updateTotalPrice();
}

// ✅ تحديث السعر الإجمالي في السلة
function updateTotalPrice() {
  const total = cart.reduce((sum, item) => sum + item.price * item.qty, 0);
  const totalContainer = document.getElementById("cart-total");
  totalContainer.textContent = `🧾 Total: $${total.toFixed(2)}`;

  // ✅ حفظ الإجمالي ومحتوى السلة في localStorage
  localStorage.setItem("cartTotal", total.toFixed(2));
  localStorage.setItem("cart", JSON.stringify(cart));
}

// ✅ عند الضغط على زر "Add to Cart"
document.querySelectorAll(".buy-btn").forEach((btn) => {
  btn.addEventListener("click", async () => {
    if (!loggedIn) {
      Swal.fire({
        icon: "warning",
        title: "Login Required",
        text: "Please login first before buying!",
        confirmButtonColor: "#f59e0b",
      });
      return;
    }

    const productCard = btn.closest(".item");
    const name = productCard.querySelector("h3").textContent;
    const qty =
      parseInt(productCard.querySelector(".quantity-input").value) || 1;
    const unitPrice = parseFloat(
      productCard.querySelector(".price").dataset.unitPrice
    );

    // ✅ إضافة المنتج إلى السلة
    cart.push({ name, price: unitPrice, qty });
    updateCartDisplay();

    Swal.fire({
      icon: "success",
      title: "Added to Cart!",
      text: `${name} (${qty}) has been added.`,
      confirmButtonColor: "#22c55e",
    });
  });
});

// ✅ زر الدفع بالفيزا
document.querySelector(".visa-btn").addEventListener("click", () => {
  // التحقق إن السلة تحتوي على منتجات
  if (!cart || cart.length === 0) {
    Swal.fire({
      icon: "warning",
      title: "Empty Cart",
      text: "Please add some coffee items to your cart before paying with Visa.",
      confirmButtonColor: "#f59e0b",
    });
    return;
  }

  Swal.fire({
    title: "Redirecting...",
    html: `
    <div style="overflow:hidden;">
      <p>Your order is ready! Redirecting you to the payment page ☕💳</p>
      <div class="loading-spinner" style="
        margin-top: 15px;
        width: 40px;
        height: 40px;
        border: 4px solid #ccc;
        border-top-color: #22c55e;
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-left: auto;
        margin-right: auto;
      "></div>
    </div>
    <style>
      @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
      }
      .swal2-popup {
        overflow: hidden !important;
      }
    </style>
  `,
    showConfirmButton: false,
    allowOutsideClick: false,
    timer: 2500,
    didClose: () => {
      // ✅ بعد انتهاء التحميل، الانتقال إلى صفحة الدفع
      window.location.href = "payment.php";
    },
  });
});

// ✅ عند تحميل الصفحة
window.addEventListener("DOMContentLoaded", () => {
  checkLoginStatus();

  // تحديث الأسعار عند تغيير الكمية
  document.querySelectorAll(".item").forEach((item) => {
    const input = item.querySelector(".quantity-input");
    const priceEl = item.querySelector(".price");
    const unitPrice = parseFloat(priceEl.dataset.unitPrice);

    const updatePrice = () => {
      let qty = parseInt(input.value);
      if (isNaN(qty) || qty < parseInt(input.min)) qty = parseInt(input.min);
      if (qty > parseInt(input.max)) qty = parseInt(input.max);
      input.value = qty;
      priceEl.textContent = `$${(unitPrice * qty).toFixed(2)}`;
    };

    // السماح بالأرقام فقط
    input.addEventListener("input", () => {
      input.value = input.value.replace(/[^0-9]/g, "");
      updatePrice();
    });

    // أزرار زيادة/نقص الكمية
    item.querySelectorAll(".qty-btn").forEach((btn) => {
      btn.addEventListener("click", () => {
        let qty = parseInt(input.value);
        if (btn.dataset.action === "increase") {
          qty = Math.min(qty + 1, parseInt(input.max));
        } else if (btn.dataset.action === "decrease") {
          qty = Math.max(qty - 1, parseInt(input.min));
        }
        input.value = qty;
        updatePrice();
      });
    });

    updatePrice();
  });
});

/////// ✅ عند الضغط على زر الدفع في المتجر – إرسال محتوى السلة عبر البريد
document.querySelector(".store-btn").addEventListener("click", async () => {
  // 🛒 التحقق من أن السلة ليست فارغة
  if (cart.length === 0) {
    Swal.fire({
      icon: "warning",
      title: "Empty Cart",
      text: "Please add items to your cart before confirming at the store.",
      confirmButtonColor: "#f59e0b",
    });
    return;
  }

  // ⚙️ عرض تحميل مباشرة أثناء الإرسال
  Swal.fire({
    title: "Sending your order...",
    html: `Please wait ⏳<br><b>Name:</b> ${phpFullName} <br><b>Phone:</b> ${php_phone}`,
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });

  try {
    // 📤 إرسال البيانات إلى PHP
    const res = await fetch("send_cart_email.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify({
        cart,
        name: phpFullName,
        phone: php_phone,
      }),
    });

    const data = await res.json();

    // ✅ بعد النجاح
    if (data.success) {
      Swal.fire({
        icon: "success",
        title: "Order Sent Successfully!",
        html: `
          <p>Your order has been sent successfully ✅</p>
          <p><b>Name:</b> ${phpFullName}</p>
          <p><b>Phone:</b> ${php_phone}</p>
          <p>Please complete payment at the store.</p>
        `,
        confirmButtonColor: "#22c55e",
        timer: 4000,
        showConfirmButton: true,
      });

      // 🧹 تفريغ السلة بعد الإرسال
      cart = [];
      updateCartDisplay();
    } else {
      Swal.fire({
        icon: "error",
        title: "Error",
        text:
          data.message || "Failed to send your order. Please try again later.",
        confirmButtonColor: "#ef4444",
      });
    }
  } catch (err) {
    console.error("Error sending cart:", err);
    Swal.fire({
      icon: "error",
      title: "Connection Error",
      text: "Unable to send your order. Please check your connection.",
      confirmButtonColor: "#ef4444",
    });
  }
});

// Toggle links visibility on click
document.addEventListener("DOMContentLoaded", function () {
  const menuToggle = document.getElementById("hamburger");
  const links = document.querySelector(".links");

  menuToggle.addEventListener("click", function (e) {
    e.preventDefault();
    links.classList.toggle("active");
  });

  document.addEventListener("click", function (e) {
    if (!menuToggle.contains(e.target) && !links.contains(e.target)) {
      links.classList.remove("active");
    }
  });

  // ضبط الشكل المبدئي
  menuToggle.style.cursor = "pointer";
  menuToggle.style.fontSize = "26px";
  menuToggle.style.transition = "transform 0.3s ease";

  let isOpen = false;

  menuToggle.addEventListener("click", () => {
    isOpen = !isOpen;

    if (isOpen) {
      menuToggle.textContent = "✖"; // يتحول إلى X
      menuToggle.style.transform = "rotate(180deg)";
    } else {
      menuToggle.textContent = "☰"; // يرجع إلى الهامبرجر
      menuToggle.style.transform = "rotate(0deg)";
    }
  });
});

// Preloader hide after page load
window.addEventListener("load", () => {
  setTimeout(() => {
    const preloader = document.getElementById("preloader");
    preloader.style.opacity = "0";
    preloader.style.transition = "opacity 0.5s ease";
    setTimeout(() => {
      preloader.style.display = "none";
    }, 500);
  }, 500);
});

/////////////// ✅ دالة لتحديث حالة زر تسجيل الدخول
function updateLoginLink(isLoggedIn) {
  document.querySelectorAll(".link_login").forEach((link) => {
    if (isLoggedIn) {
      link.textContent = "Logout";
      link.href = "#"; // منع الانتقال لصفحة login
      link.classList.add("logout-active");
    } else {
      link.textContent = "Login";
      link.href = "login.html";
      link.classList.remove("logout-active");
    }
  });
  document.querySelectorAll(".authLink").forEach((link) => {
    if (isLoggedIn) {
      link.textContent = "Logout";
      link.href = "#";
      link.classList.add("logout-active");
    } else {
      link.textContent = "Login";
      link.href = "login.html";
      link.classList.remove("logout-active");
    }
  });
}

// ✅ فحص حالة تسجيل الدخول عند تحميل الصفحة
async function checkLoginState() {
  try {
    const res = await fetch("check_login_db.php", { method: "POST" });
    const data = await res.json();

    if (data.status === "loggedin") {
      updateLoginLink(true);
    } else {
      updateLoginLink(false);
    }
  } catch (err) {
    console.error("Error checking login state:", err);
  }
}

// ✅ عند الضغط على زر Logout
document.addEventListener("click", async (e) => {
  if (e.target.classList.contains("logout-active")) {
    e.preventDefault();

    const result = await Swal.fire({
      title: "Are you sure?",
      text: "Do you really want to logout?",
      icon: "warning",
      showCancelButton: true,
      confirmButtonColor: "#ef4444",
      cancelButtonColor: "#6b7280",
      confirmButtonText: "Yes, logout",
    });

    if (result.isConfirmed) {
      try {
        const res = await fetch("logout.php", { method: "POST" });
        const data = await res.json();

        if (data.success) {
          Swal.fire({
            icon: "success",
            title: "Logged out!",
            text: "You have been logged out successfully.",
            confirmButtonColor: "#22c55e",
            timer: 2000,
            showConfirmButton: false,
          }).then(() => {
            updateLoginLink(false);
            window.location.href = "index2.html";
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to logout. Please try again.",
            confirmButtonColor: "#ef4444",
          });
        }
      } catch (err) {
        Swal.fire({
          icon: "error",
          title: "Connection Error",
          text: "Unable to connect to the server.",
          confirmButtonColor: "#ef4444",
        });
      }
    }
  }
});

// ✅ استدعاء فحص الحالة عند تحميل الصفحة
window.addEventListener("DOMContentLoaded", checkLoginState);

///////// ✅ إخفاء زر "LOGIN" إذا كان المستخدم مسجل الدخول
async function checkAndHideLoginButton() {
  try {
    const res = await fetch("check_login_db.php", { method: "POST" });
    const data = await res.json();

    const navProfileBtn = document.getElementById("navProfileBtn");
    const footerProfileBtn = document.getElementById("footerProfileBtn");
    const products = document.querySelector(".products");
    const intro = document.querySelector(".intro");

    if (navProfileBtn) {
      // ✅ إذا كان المستخدم داخل — نظهره
      if (data.status === "loggedin") {
        navProfileBtn.style.display = "block";
      } else {
        // ✅ إذا لم يكن داخل — نخفي الزر
        navProfileBtn.style.display = "none";
      }
    }

    if (footerProfileBtn) {
      // ✅ إذا كان المستخدم داخل — نظهره
      if (data.status === "loggedin") {
        footerProfileBtn.style.display = "block";
      } else {
        // ✅ إذا لم يكن داخل — نخفي الزر
        footerProfileBtn.style.display = "none";
      }
    }

    if (products) {
      // ✅ إذا كان المستخدم داخل — نظهره
      if (data.status === "loggedin") {
        products.style.display = "block";
      } else {
        // ✅ إذا لم يكن داخل — نخفي الزر
        products.style.display = "none";
      }
    }

    if (intro) {
      // ✅ إذا كان المستخدم داخل — نخفي الزر
      if (data.status === "loggedin") {
        intro.style.display = "none";
      } else {
        // ✅ إذا لم يكن داخل — نظهره
        intro.style.display = "block";
      }
    }
  } catch (err) {
    console.error("Error checking login state:", err);
  }
}

// ✅ نفذ التحقق عند تحميل الصفحة
document.addEventListener("DOMContentLoaded", checkAndHideLoginButton);

// عرض المنتجات إذا اختار العميل "Products"
document.getElementById("type").addEventListener("change", function () {
  const products = document.getElementById("product-options");
  if (this.value === "products") {
    products.classList.remove("hidden");
  } else {
    products.classList.add("hidden");
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("feedbackForm");

  if (!form) return;
    
      // التحكم في إظهار أو إزالة تحديد المنتجات حسب نوع الفيدباك
  const typeSelect = document.getElementById("type");
  const productSection = document.getElementById("product-options");

  if (typeSelect && productSection) {
    typeSelect.addEventListener("change", () => {
      const selectedType = typeSelect.value;

      // لو النوع مش "Products" شيل التحديد فقط
      if (selectedType !== "products") {
        const checkedProducts = productSection.querySelectorAll(
          'input[name="products[]"]:checked'
        );
        checkedProducts.forEach((cb) => (cb.checked = false));
      }
    });
  }

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    // التحقق من اختيار نجمة واحدة على الأقل
    const ratingChecked = form.querySelector('input[name="rating"]:checked');
    if (!ratingChecked) {
      Swal.fire({
        icon: "warning",
        title: "Rating Required ⭐",
        text: "Kindly select at least one star to rate your experience.",
        confirmButtonColor: "#8B4513",
      });
      return;
    }

    // التحقق من اختيار منتج واحد على الأقل (لو القسم ظاهر)
    const productSection = document.getElementById("product-options");
    if (productSection && !productSection.classList.contains("hidden")) {
      const checkedProducts = form.querySelectorAll(
        'input[name="products[]"]:checked'
      );
      if (checkedProducts.length === 0) {
        Swal.fire({
          icon: "info",
          title: "Select Products ☕",
          text: "Please choose at least one product you’ve tried before submitting.",
          confirmButtonColor: "#8B4513",
        });
        return;
      }
    }

    // إرسال النموذج بعد التحقق
    const formData = new FormData(form);

    Swal.fire({
      title: "Sending...",
      text: "Please wait while we send your feedback.",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    try {
      const response = await fetch("send-review.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();
      Swal.close();

      if (result.status === "success") {
        Swal.fire({
          icon: "success",
          title: "Feedback Sent! ✅",
          text: result.message,
          confirmButtonColor: "#8B4513",
        }).then(() => form.reset());
      } else {
        Swal.fire({
          icon: "error",
          title: "Error ❌",
          text: result.message,
          confirmButtonColor: "#a63c3c",
        });
      }
    } catch (error) {
      Swal.close();
      Swal.fire({
        icon: "error",
        title: "Network Error 🌐",
        text: "Something went wrong. Please try again later.",
        confirmButtonColor: "#a63c3c",
      });
    }
  });
});

///Reservation hidden when user dont login
async function checkAndHideReservationButton() {
  try {
    const res = await fetch("check_login_db.php", { method: "POST" });
    const data = await res.json();
    
    const navReservationBtn = document.getElementById("navReservationBtn");
    const footerReservationBtn = document.getElementById("footerReservationBtn");

    if (navReservationBtn) {
      // ✅ إذا كان المستخدم داخل — نظهره
      if (data.status === "loggedin") {
        navReservationBtn.style.display = "block";
      } else {
        // ✅ إذا لم يكن داخل — نخفي الزر
        navReservationBtn.style.display = "none";
      }
    }

    if (footerReservationBtn) {
      // ✅ إذا كان المستخدم داخل — نظهره
      if (data.status === "loggedin") {
        footerReservationBtn.style.display = "block";
      } else {
        // ✅ إذا لم يكن داخل — نخفي الزر
        footerReservationBtn.style.display = "none";
      }
    }
  } catch (err) {
    console.error("Error checking login state:", err);
  }
}
// ✅ نفذ التحقق عند تحميل الصفحة
document.addEventListener("DOMContentLoaded", checkAndHideReservationButton);