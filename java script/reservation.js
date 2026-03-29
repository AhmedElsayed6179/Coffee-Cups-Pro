// ===========================================
// 📅 نموذج الحجز مع SweetAlert تحميل وتأكيد
// ===========================================
document
  .getElementById("reservationForm")
  .addEventListener("submit", async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);

    Swal.fire({
      title: "Sending your reservation...",
      text: "Please wait a moment ⏳",
      allowOutsideClick: false,
      didOpen: () => {
        Swal.showLoading();
      },
    });

    try {
      const res = await fetch("send_reservation.php", {
        method: "POST",
        body: formData,
      });

      const data = await res.json();

      setTimeout(() => {
        if (data.success) {
          Swal.fire({
            icon: "success",
            title: "Reservation Confirmed!",
            text: "Your table has been reserved successfully. We'll contact you soon.",
            confirmButtonColor: "#22c55e",
            timer: 2500,
            showConfirmButton: false,
          });
          e.target.reset();
        } else if (data.error && data.error.includes("already booked")) {
          Swal.fire({
            icon: "warning",
            title: "Time Slot Unavailable",
            text: "Please choose another time. This slot is already booked.",
            confirmButtonColor: "#f39c12"
          });
        } else {
          Swal.fire({
            icon: "error",
            title: "Error",
            text: "Failed to send reservation. Please try again later.",
            confirmButtonColor: "#ef4444",
          });
        }
      }, 800);
    } catch (err) {
      Swal.fire({
        icon: "error",
        title: "Connection Error",
        text: "Unable to send reservation. Please check your internet connection.",
        confirmButtonColor: "#ef4444",
      });
      console.error("Reservation send failed:", err);
    }
  });

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
    const footerReservationBtn = document.getElementById(
      "footerReservationBtn"
    );
    const Intro = document.getElementById("intro");
    const reservation = document.getElementById("reservation-section");

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

    if (reservation) {
      // ✅ إذا كان المستخدم داخل — نظهره
      if (data.status === "loggedin") {
        reservation.style.display = "block";
      } else {
        // ✅ إذا لم يكن داخل — نخفي الزر
        reservation.style.display = "none";
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

    if (Intro) {
      // ✅ إذا كان المستخدم داخل — نخفي الزر
      if (data.status === "loggedin") {
        Intro.style.display = "none";
      } else {
        // ✅ إذا لم يكن داخل — نظهره
        Intro.style.display = "block";
      }
    }
  } catch (err) {
    console.error("Error checking login state:", err);
  }
}
// ✅ نفذ التحقق عند تحميل الصفحة
document.addEventListener("DOMContentLoaded", checkAndHideReservationButton);

flatpickr("#resDate", {
  dateFormat: "Y-m-d",
  minDate: "today",
  altInput: true,
  altFormat: "F j, Y",
  allowInput: true,
  theme: "light",
  wrap: false,
});

document.querySelector("#resDate").addEventListener("change", function () {
  const selectedDate = new Date(this.value);
  const today = new Date();
  today.setHours(0, 0, 0, 0);

  if (selectedDate < today) {
    Swal.fire({
      icon: "warning",
      title: "Invalid Date",
      text: "You can't select a past date.",
      confirmButtonColor: "#d33",
    });
    this.value = "";
  }
});

flatpickr("#resTime", {
  enableTime: true, // تفعيل الوقت
  noCalendar: true, // منع عرض التاريخ
  dateFormat: "h:i K", // صيغة الوقت (مثال: 02:30 PM)
  time_24hr: false, // false = AM/PM, true = 24 ساعة
  altInput: true, // واجهة أجمل للمستخدم
  altFormat: "h:i K",
  allowInput: true,
});