document.addEventListener("DOMContentLoaded", () => {
  const form = document.getElementById("loginForm_email");

  form.style.opacity = "0";
  form.style.transform = "translateY(-20px)";

  setTimeout(() => {
    form.style.transition = "0.6s ease";
    form.style.opacity = "1";
    form.style.transform = "translateY(0)";
  }, 200);
});

document
  .getElementById("loginForm_email")
  .addEventListener("submit", async (e) => {
    e.preventDefault();

    const form = e.target;
    const formData = new FormData(form);

    Swal.fire({
      title: "Please wait...",
      text: "We’re sending a secure reset link to your email.",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    try {
      const response = await fetch(form.action, {
        method: "POST",
        body: formData,
      });

      const result = await response.json();

      if (result.status === "error") {
        Swal.fire({
          icon: "error",
          title: "Request Failed",
          html: `<p style="font-size:15px;color:#555;">${result.message}</p>`,
          confirmButtonColor: "#d33",
          confirmButtonText: "Try Again",
        });
      } else if (result.status === "unverified") {
        Swal.fire({
          icon: "warning",
          title: "Account Not Verified",
          html: `
          <p style="font-size:15px;color:#444;">
            Your account is not verified yet.<br>
            We’ve sent a new confirmation email — please check your inbox or spam folder.
          </p>
        `,
          confirmButtonColor: "#f59e0b",
          confirmButtonText: "Got it",
        });
      } else if (result.status === "success") {
        Swal.fire({
          icon: "success",
          title: "Email Sent!",
          html: `
          <p style="font-size:15px;color:#444;">
            A password reset link has been sent to your email.<br>
            Please check your inbox and follow the instructions.
          </p>
        `,
          confirmButtonColor: "#1cc88a",
          confirmButtonText: "Okay",
        });
        form.reset();
      } else {
        Swal.fire({
          icon: "info",
          title: "Unexpected Response",
          text: "We received an unexpected response. Please try again.",
          confirmButtonColor: "#3085d6",
        });
      }
    } catch (err) {
      Swal.fire({
        icon: "error",
        title: "Connection Error",
        html: `
        <p style="font-size:15px;color:#555;">
          Something went wrong while connecting to the server.<br>
          Please check your internet connection and try again.
        </p>
      `,
        confirmButtonColor: "#c62828",
      });
    }
  });

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

///////// ✅ إخفاء زر "LOGIN" إذا كان المستخدم مسجل الدخول
async function checkAndHideLoginButton() {
  try {
    const res = await fetch("check_login_db.php", { method: "POST" });
    const data = await res.json();

    const accountDiv = document.querySelector(".account");
    const saleDiv = document.querySelector(".sale");
    const navProfileBtn = document.getElementById("navProfileBtn");
    const footerProfileBtn = document.getElementById("footerProfileBtn");

    if (accountDiv) {
      // ✅ إذا كان المستخدم داخل — نخفي الزر
      if (data.status === "loggedin") {
        accountDiv.style.display = "none";
      } else {
        // ✅ إذا لم يكن داخل — نظهره
        accountDiv.style.display = "block";
      }
    }

    if (saleDiv) {
      // ✅ إذا كان المستخدم داخل — نظهره
      if (data.status === "loggedin") {
        saleDiv.style.display = "block";
      } else {
        // ✅ إذا لم يكن داخل — نخفي الزر
        saleDiv.style.display = "none";
      }
    }

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
