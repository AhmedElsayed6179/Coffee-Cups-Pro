// ===================== Login Form Logic =====================
const formLogin = document.getElementById("loginForm");
const loginPassword = document.getElementById("pass");
const arabicRegex = /[\u0600-\u06FF]/;

// ألوان القهوة ☕
const bgColor = "#3e2723";
const textColor = "#fbe9e7";
const btnColor = "#6d4c41";

if (formLogin) {
  // تحميل بيانات Remember Me (إن وُجدت)
  const savedUsername = localStorage.getItem("savedUsername");
  const savedPassword = localStorage.getItem("savedPassword");
  const rememberCheckbox = document.querySelector(".remember-label input");

  if (savedUsername && savedPassword) {
    document.getElementById("user_name").value = savedUsername;
    document.getElementById("pass").value = savedPassword;
    rememberCheckbox.checked = true;
  }

  formLogin.addEventListener("submit", async (e) => {
    e.preventDefault();

    const username = document.getElementById("user_name").value.trim();
    const passwordVal = loginPassword.value.trim();

    if (!username || !passwordVal) {
      Swal.fire({
        icon: "warning",
        title: "Missing Information",
        text: "Please fill in all fields before continuing.",
        confirmButtonColor: "#0078ff",
      });
      return;
    }

    // منع الحروف العربية
    if (arabicRegex.test(username) || arabicRegex.test(passwordVal)) {
      Swal.fire({
        icon: "warning",
        title: "⚠️ Invalid Input",
        text: "Username and Password must not contain Arabic characters!",
        background: bgColor,
        color: textColor,
        confirmButtonColor: btnColor,
      });
      return;
    }

    const formData = new FormData(formLogin);

    try {
      // عرض SweetAlert تحميل
      Swal.fire({
        title: "Processing...",
        html: "Please wait while we check your account",
        allowOutsideClick: false,
        background: bgColor,
        color: textColor,
        didOpen: () => {
          Swal.showLoading();
        },
      });

      const response = await fetch("login.php", {
        method: "POST",
        body: formData,
      });

      if (!response.ok) throw new Error("Network response was not ok");

      const result = await response.json();
      Swal.close();

      // Remember Me logic
      if (rememberCheckbox.checked) {
        localStorage.setItem("savedUsername", username);
        localStorage.setItem("savedPassword", passwordVal);
      } else {
        localStorage.removeItem("savedUsername");
        localStorage.removeItem("savedPassword");
      }

      switch (result.status) {
        case "notfound":
          Swal.fire({
            icon: "info",
            title: "Account Not Found",
            text: "No account found! Please create an account first.",
            background: bgColor,
            color: textColor,
            confirmButtonColor: btnColor,
          });
          break;

        case "invalid":
          Swal.fire({
            icon: "error",
            title: "Incorrect Credentials",
            text: "Incorrect username/email or password! Please try again.",
            background: bgColor,
            color: textColor,
            confirmButtonColor: btnColor,
          });
          break;

        case "unverified":
          Swal.fire({
            icon: "warning",
            title: "Email Not Verified",
            text:
              result.message ||
              "A confirmation email has been resent to your email.",
            background: bgColor,
            color: textColor,
            confirmButtonColor: btnColor,
          });
          break;

        case "admin": // ← إضافة حالة الأدمن
          Swal.fire({
            icon: "success",
            title: `Welcome back, ${result.username}!`,
            text: "Redirecting to the admin dashboard...",
            background: bgColor,
            color: textColor,
            showConfirmButton: false,
            timer: 2000,
            timerProgressBar: true,
          });

          setTimeout(() => {
            window.location.href = result.redirect || "dashboard.php";
          }, 2000);
          break;

        case "success":
          Swal.fire({
            icon: "success",
            title: `Welcome back, ${result.username}! ☕`,
            text: "Redirecting to the homepage...",
            background: bgColor,
            color: textColor,
            showConfirmButton: false,
            timer: 2500,
            timerProgressBar: true,
          });

          setTimeout(() => {
            window.location.href = "index2.html";
          }, 2500);
          break;

        default:
          Swal.fire({
            icon: "error",
            title: "Unexpected Error",
            text: "Something went wrong! Please try again later.",
            background: bgColor,
            color: textColor,
            confirmButtonColor: btnColor,
          });
      }
    } catch (err) {
      console.error(err);
      Swal.fire({
        icon: "error",
        title: "Network Error",
        text: "Please check your internet connection and try again.",
        background: bgColor,
        color: textColor,
        confirmButtonColor: btnColor,
      });
    }
  });
}

// ===================== Toggle Password Visibility =====================
const togglePassword = document.querySelector("#togglePassword");
const password = document.querySelector("#pass");

togglePassword.addEventListener("click", function () {
  const type =
    password.getAttribute("type") === "password" ? "text" : "password";
  password.setAttribute("type", type);
  this.classList.toggle("fa-eye");
  this.classList.toggle("fa-eye-slash");
});

// ===================== Fade In Animation =====================
document.addEventListener("DOMContentLoaded", () => {
  document.querySelector(".first").classList.add("show");

  const savedUsername = localStorage.getItem("savedUsername");
  const savedPassword = localStorage.getItem("savedPassword");

  if (savedUsername && savedPassword) {
    document.getElementById("user_name").value = savedUsername;
    document.getElementById("pass").value = savedPassword;
    document.querySelector(".remember-label input").checked = true;
  }

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

