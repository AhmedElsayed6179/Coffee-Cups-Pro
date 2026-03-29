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

    // ✅ Remove comingSoon part safely (skip if doesn't exist)
    if (typeof comingSoon !== "undefined" && comingSoon) {
      comingSoon.style.opacity = 0;
      setTimeout(() => {
        comingSoon.style.display = "none";
      }, 500);
    }

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
    const res = await fetch("check_login_admin.php", { method: "POST" });
    const data = await res.json();

    if (data.status === "loggedin") {
      loggedIn = true;
      document.getElementById("user-sections").classList.remove("hidden");
    }
  } catch (err) {
    console.error("Login check failed:", err);
  }
}


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
    const res = await fetch("check_login_admin.php", { method: "POST" });
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
    const res = await fetch("check_login_admin.php", { method: "POST" });
    const data = await res.json();
      
    const intro = document.querySelector(".intro");

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