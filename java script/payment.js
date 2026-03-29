// This file is linked to the HTML page and contains the page’s JavaScript code
document.addEventListener("DOMContentLoaded", () => {
  document.querySelector(".welcome").style.opacity = "1";
  document.querySelector(".welcome").style.transform = "translateY(0)";
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

// ===========================================
// 💳 Payment Page – Visa Checkout Integration
// ===========================================

// 🛒 تحميل بيانات السلة من localStorage أو متغير عام
let cart = window.cart || JSON.parse(localStorage.getItem("cart") || "[]");
const totalFromStorage = localStorage.getItem("cartTotal");

// عناصر الصفحة
const summaryItems = document.getElementById("summary-items");
const summaryTotalEl = document.getElementById("summary-total");
const visaForm = document.getElementById("visaForm");

// =================================================
// 🧾 عرض ملخص الطلب
// =================================================
function renderSummary() {
  summaryItems.innerHTML = "";
  if (!cart || cart.length === 0) {
    summaryItems.innerHTML = '<p class="empty">No items in cart.</p>';
    summaryTotalEl.textContent = "$0.00";
    return;
  }

  let total = 0;
  cart.forEach((item) => {
    const line = document.createElement("div");
    line.className = "item";
    const subtotal = item.price * item.qty;
    total += subtotal;
    line.innerHTML = `
      <div class="name">${item.name}</div>
      <div class="meta">${item.qty} × $${item.price.toFixed(2)}</div>
      <div style="margin-left:8px;font-weight:700;">$${subtotal.toFixed(
        2
      )}</div>
    `;
    summaryItems.appendChild(line);
  });

  summaryTotalEl.textContent = `$${total.toFixed(2)}`;
}

// =================================================
// 🧩 دوال التحقق من صحة بيانات البطاقة
// =================================================
function validateCardNumber(num) {
  const numbers = num.replace(/\s+/g, "");
  return /^\d{16}$/.test(numbers);
}
function formatCardNumber(value) {
  return value
    .replace(/\D/g, "")
    .replace(/(.{4})/g, "$1 ")
    .trim();
}
function validateExpiry(ev) {
  if (!/^\d{2}\/\d{2}$/.test(ev)) return false;
  const [mm, yy] = ev.split("/").map((v) => parseInt(v, 10));
  if (mm < 1 || mm > 12) return false;
  const now = new Date();
  const inputDate = new Date(2000 + yy, mm - 1, 1);
  const end = new Date(inputDate.getFullYear(), inputDate.getMonth() + 1, 1);
  return end > now;
}
function validateCVV(c) {
  return /^\d{3,4}$/.test(c);
}
function validateName(n) {
  return n && n.trim().length >= 8;
}

// =================================================
// 🎨 تنسيق الحقول أثناء الكتابة
// =================================================
document.getElementById("cardNumber").addEventListener("input", (e) => {
  e.target.value = formatCardNumber(e.target.value);
});
document.getElementById("expiry").addEventListener("input", (e) => {
  let v = e.target.value.replace(/\D/g, "");
  if (v.length >= 3) v = v.slice(0, 2) + "/" + v.slice(2, 4);
  e.target.value = v;
});
document.getElementById("cvv").addEventListener("input", (e) => {
  e.target.value = e.target.value.replace(/\D/g, "").slice(0, 4);
});

// =================================================
// 🚫 عند الضغط على زر Pay Now
// =================================================
visaForm.addEventListener("submit", async (ev) => {
  ev.preventDefault();

  // ✅ عرض رسالة "هناك تحديثات من خلالنا"
  Swal.fire({
    icon: "info",
    title: "Updates in Progress",
    text: "There are updates in progress, please try again later.",
    confirmButtonColor: "#0078ff",
  });
});

// =================================================
// ❌ زر إلغاء الدفع
// =================================================
document.getElementById("cancelPayment").addEventListener("click", () => {
  Swal.fire({
    title: "Cancel Payment?",
    text: "Do you want to cancel and return to the shop?",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Yes, cancel",
    cancelButtonText: "Keep payment",
    confirmButtonColor: "#ef4444",
  }).then((res) => {
    if (res.isConfirmed) window.location.href = "gallery.php";
  });
});

// =================================================
// 📦 عرض القيم المحفوظة عند تحميل الصفحة
// =================================================
if (totalFromStorage) {
  summaryTotalEl.textContent = `$${parseFloat(totalFromStorage).toFixed(2)}`;
}

// 🚀 تشغيل العرض عند تحميل الصفحة
renderSummary();

///////// ✅ إخفاء زر "LOGIN" إذا كان المستخدم مسجل الدخول
async function checkAndHideLoginButton() {
  try {
    const res = await fetch("check_login_db.php", { method: "POST" });
    const data = await res.json();

    const navProfileBtn = document.getElementById("navProfileBtn");
    const footerProfileBtn = document.getElementById("footerProfileBtn");

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
  } catch (err) {
    console.error("Error checking login state:", err);
  }
}

// ✅ نفذ التحقق عند تحميل الصفحة
document.addEventListener("DOMContentLoaded", checkAndHideLoginButton);

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
