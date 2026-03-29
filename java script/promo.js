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
// window.addEventListener("DOMContentLoaded", checkLoginState);

// //// videoplay
// const video = document.getElementById("coffeeVideo");
// const playBtn = document.getElementById("playToggle");
// const volumeBtn = document.getElementById("volumeToggle");
// const volumeRange = document.getElementById("volumeControl");
// const volumeGroup = document.querySelector(".volume-group");
// const fullscreenBtn = document.getElementById("fullscreenToggle");

// // 🎚️ إنشاء progress bar ديناميكي
// const videoContainer = document.querySelector(".video-container");
// const progressContainer = document.createElement("div");
// progressContainer.classList.add("progress-container");

// const progressBar = document.createElement("div");
// progressBar.classList.add("progress-bar");

// progressContainer.appendChild(progressBar);
// videoContainer.appendChild(progressContainer);

// // ▶️ تشغيل / إيقاف الفيديو
// playBtn.addEventListener("click", () => {
//   if (video.paused || video.ended) {
//     video.play();
//     playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
//   } else {
//     video.pause();
//     playBtn.innerHTML = '<i class="fa-solid fa-play"></i>';
//   }
// });

// // 🔁 لما الفيديو يخلص → يتغير الزر إلى "إعادة"
// video.addEventListener("ended", () => {
//   playBtn.innerHTML = '<i class="fa-solid fa-rotate-right"></i>';
//   playBtn.addEventListener(
//     "click",
//     () => {
//       video.currentTime = 0;
//       video.play();
//       playBtn.innerHTML = '<i class="fa-solid fa-pause"></i>';
//     },
//     { once: true } // ← عشان الحدث يشتغل مرة واحدة فقط
//   );
// });

// // 🔊 زر الصوت (إظهار/إخفاء المؤشر)
// volumeBtn.addEventListener("click", () => {
//   volumeGroup.classList.toggle("show");
// });

// // 🎚️ التحكم في مستوى الصوت + تغيير الأيقونة
// volumeRange.addEventListener("input", (e) => {
//   const value = parseFloat(e.target.value);
//   video.volume = value;

//   if (value === 0) {
//     volumeBtn.innerHTML = '<i class="fa-solid fa-volume-xmark"></i>';
//   } else if (value > 0 && value <= 0.5) {
//     volumeBtn.innerHTML = '<i class="fa-solid fa-volume-low"></i>';
//   } else {
//     volumeBtn.innerHTML = '<i class="fa-solid fa-volume-high"></i>';
//   }
// });

// // 🖥️ ملء الشاشة
// fullscreenBtn.addEventListener("click", () => {
//   if (!document.fullscreenElement) {
//     video.requestFullscreen();
//     fullscreenBtn.innerHTML = '<i class="fa-solid fa-compress"></i>';
//   } else {
//     document.exitFullscreen();
//     fullscreenBtn.innerHTML = '<i class="fa-solid fa-expand"></i>';
//   }
// });

// // ⏱️ تحديث progress bar أثناء التشغيل
// video.addEventListener("timeupdate", () => {
//   const progress = (video.currentTime / video.duration) * 100;
//   progressBar.style.width = `${progress}%`;
// });

// // 🖱️ النقر على progress bar لتقديم الفيديو
// progressContainer.addEventListener("click", (e) => {
//   const rect = progressContainer.getBoundingClientRect();
//   const clickX = e.clientX - rect.left;
//   const percent = clickX / rect.width;
//   video.currentTime = percent * video.duration;
// });

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
