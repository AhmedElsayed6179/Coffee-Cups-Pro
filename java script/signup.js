// This file is linked to the HTML page and contains the page’s JavaScript code

function initSignup(formSignup) {
  const fullName = document.getElementById("full_name");
  const username = document.getElementById("user_name");
  const email = document.getElementById("email");
  const password = document.getElementById("pass");
  const confirmPassword = document.getElementById("confpass");
  const togglePassword = document.getElementById("togglePassword");
  const toggleConfirmPassword = document.getElementById("toggleConfirmPassword");
  const phone = document.getElementById("phone");
  const dob = document.getElementById("dob");
  const gender = document.getElementById("gender");
  const agree = document.getElementById("agree");
    
  // ================= Password Toggle =================
  function toggleVisibility(input, icon) {
    const isPassword = input.type === "password";
    input.type = isPassword ? "text" : "password";
    icon.classList.toggle("fa-eye");
    icon.classList.toggle("fa-eye-slash");
  }

  togglePassword.addEventListener("click", () =>
    toggleVisibility(password, togglePassword)
  );
  toggleConfirmPassword.addEventListener("click", () =>
    toggleVisibility(confirmPassword, toggleConfirmPassword)
  );

  // ================= Form Submit =================
  formSignup.addEventListener("submit", async (e) => {
    e.preventDefault();

    const nameVal = fullName.value.trim();
    const userVal = username.value.trim();
    const emailVal = email.value.trim();
    const passVal = password.value.trim();
    const confVal = confirmPassword.value.trim();
    const phoneVal = phone.value.trim();
    const dobVal = dob.value.trim();
    const genderVal = gender.value.trim();
    const agreeVal = agree.checked;

    // Regex rules
    const usernameRegex = /^[a-zA-Z0-9._]{3,20}$/;
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const passwordRegex = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%]).{8,}$/;
    const arabicRegex = /[\u0600-\u06FF]/;

    // ================= Validation =================
    if (!nameVal || !userVal || !emailVal || !passVal || !confVal || !phoneVal || !dobVal || !genderVal || !agreeVal) {
      Swal.fire({
        icon: "warning",
        title: "Missing Information",
        text: "Please fill in all fields before continuing.",
        confirmButtonColor: "#0078ff",
      });
      return;
    }

    if (arabicRegex.test(nameVal + userVal + emailVal + passVal + confVal + !phoneVal )) {
      Swal.fire({
        icon: "warning",
        title: "⚠️ Invalid Input",
        text: "Please avoid using Arabic characters in your input fields.",
        confirmButtonColor: "#3085d6",
      });
      return;
    }

    if (!/^[a-zA-Z\s]+$/.test(nameVal)) {
      Swal.fire({
        icon: "error",
        title: "Invalid Full Name",
        text: "Full name can only contain letters and spaces, no numbers or symbols!",
        confirmButtonColor: "#0078ff",
      });
      return;
    }

    if (nameVal.length < 8) {
      Swal.fire({
        icon: "error",
        title: "Invalid Full Name",
        text: "Full name must be at least 8 characters long.",
        confirmButtonColor: "#0078ff",
      });
      return;
    }

    if (!usernameRegex.test(userVal)) {
      Swal.fire({
        icon: "error",
        title: "Invalid Username",
        text: "Username must be 3–20 characters (letters, numbers, . or _).",
        confirmButtonColor: "#0078ff",
      });
      return;
    }

    if (!emailRegex.test(emailVal)) {
      Swal.fire({
        icon: "error",
        title: "Invalid Email",
        text: "Please enter a valid email address.",
        confirmButtonColor: "#0078ff",
      });
      return;
    }

    if (!passwordRegex.test(passVal)) {
      Swal.fire({
        icon: "error",
        title: "Weak Password",
        text: "Password must include upper, lower, number & symbol (8+ chars).",
        confirmButtonColor: "#0078ff",
      });
      return;
    }

    if (passVal !== confVal) {
      Swal.fire({
        icon: "error",
        title: "Password Mismatch",
        text: "Passwords do not match. Please re-type carefully.",
        confirmButtonColor: "#ef4444",
      });
      confirmPassword.focus();
      return;
    }
   
    if (!/^(010|011|012|015)\d{8}$/.test(phoneVal)) {
      Swal.fire({
        icon: "error",
        title: "Invalid Phone Number",
        text: "Phone number must start with 010, 011, 012, or 015 and be exactly 11 digits long.",
        confirmButtonColor: "#ef4444",
      });
      document.getElementById("phone").focus();
      return;
    }

    // تحقق من تاريخ الميلاد — لا يسمح بالمواليد بعد 2015
    if (dobVal) {
      const userDOB = new Date(dobVal);
      const minYear = 2015;
      if (userDOB.getFullYear() > minYear) {
        Swal.fire({
          icon: "error",
          title: "Invalid Date of Birth",
          text: "Registration is not allowed for users born after 2015.",
          confirmButtonColor: "#ef4444",
        });
        dob.focus();
        return;
      }
    } else {
      Swal.fire({
        icon: "warning",
        title: "Missing Date of Birth",
        text: "Please enter your date of birth.",
        confirmButtonColor: "#f59e0b",
      });
      return;
    }

    // ================= Send to Server =================
    const formData = new FormData(formSignup);

    Swal.fire({
      title: "Creating your account...",
      text: "Please wait while we process your registration.",
      allowOutsideClick: false,
      didOpen: () => Swal.showLoading(),
    });

    try {
      const response = await fetch("signup.php", {
        method: "POST",
        body: formData,
      });

      const result = await response.json();
      Swal.close();

      if (result.status === "pending_verification") {
        Swal.fire({
          icon: "success",
          title: "Verification Email Sent ✅",
          text: result.message,
          confirmButtonColor: "#22c55e",
        }).then(() => formSignup.reset());
        return;
      }

      if (result.status === "error" && result.type === "username_pending") {
        Swal.fire({
          icon: "info",
          title: "Username Pending ⚠️",
          text: result.message,
          confirmButtonColor: "#3085d6",
        });
        return;
      }

      if (result.status === "error" && result.type === "phone") {
        Swal.fire({
          icon: "error",
          title: "Phone Already Registered",
          text: "This phone number is already registered. Please use another one.",
          confirmButtonColor: "#ef4444",
        });
        return;
      }

      if (
        result.status === "error" &&
        (result.message === "Please enter your date of birth." ||
          result.message === "Please select your gender.")
      ) {
        Swal.fire({
          icon: "warning",
          title: "Missing Information ⚠️",
          text: result.message,
          confirmButtonColor: "#f59e0b",
        });
        return;
      }

      if (result.status === "success") {
        Swal.fire({
          icon: "success",
          title: "Account Created ✅",
          text: result.message || "Verification email sent successfully!",
          confirmButtonColor: "#22c55e",
        }).then(() => formSignup.reset());
      } else if (result.status === "pending_verification") {
        Swal.fire({
          icon: "success",
          title: "Verification Email Sent ✅",
          text: result.message,
          confirmButtonColor: "#22c55e",
        }).then(() => formSignup.reset());
      } else if (result.status === "error") {
        let errorMsg = "";
        switch (result.type) {
          case "username":
            errorMsg = "Username already taken! Please choose another.";
            break;
          case "email":
            errorMsg = "Email already registered! Please use another.";
            break;
          case "weak_password":
            errorMsg = "Password must include letters, not numbers only.";
            break;
          case "short_password":
            errorMsg = "Password must be at least 8 characters.";
            break;
          case "both":
            errorMsg = "Account already exists! Please log in.";
            break;
          case "mail_failed":
            errorMsg = "Could not send verification email. Contact support.";
            break;
          default:
            errorMsg = "Unexpected error. Please try again later.";
        }

        Swal.fire({
          icon: "error",
          title: "Signup Failed",
          text: errorMsg,
          confirmButtonColor: "#ef4444",
        });
      }
    } catch (err) {
      Swal.close();
      Swal.fire({
        icon: "error",
        title: "Network Error",
        text: "Please check your internet connection and try again.",
        confirmButtonColor: "#ef4444",
      });
    }
  });
}

document.addEventListener("DOMContentLoaded", function () {
  const phone = document.getElementById("phone");

  if (phone) {
    phone.addEventListener("input", function () {
      this.value = this.value.replace(/[^0-9]/g, "");
    });

    phone.addEventListener("paste", function (e) {
      const pasteData = e.clipboardData.getData("text");
      if (/[^0-9]/.test(pasteData)) {
        e.preventDefault();
      }
    });
  }
});

document.addEventListener("DOMContentLoaded", () => {
  const firstSection = document.querySelector(".first");
  if (firstSection) {
    setTimeout(() => {
      firstSection.classList.add("show");
    }, 200);
  }
});

// ================= Init =================
document.addEventListener("DOMContentLoaded", function () {
  const formSignup = document.getElementById("createForm");
  if (formSignup) {
    initSignup(formSignup);
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

document.addEventListener("DOMContentLoaded", () => {
  document.querySelector(".welcome").style.opacity = "1";
  document.querySelector(".welcome").style.transform = "translateY(0)";
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

flatpickr("#dob", {
  dateFormat: "Y-m-d",
  maxDate: "today",
  altInput: true,
  altFormat: "F j, Y",
  allowInput: true,
  theme: "light",
  wrap: false,
});

$(document).ready(function () {
  $("#gender").select2({
    placeholder: "Select your gender",
    minimumResultsForSearch: Infinity,
    width: "100%",
  });

  // تعديل الصندوق الأساسي
  $(".select2-selection--single").css({
    width: "100%",
    padding: "0 40px 0 12px",
    "border-radius": "5px",
    outline: "none",
    background: "#fafafa",
    color: "#333",
    transition: "all 0.3s ease",
    "box-shadow": "0 1px 2px rgba(0, 0, 0, 0.05)",
    height: "40px",
    "box-sizing": "border-box",
    "font-size": "14px",
    border: "1.8px solid var(--navbar-color)",
    display: "flex",
    "align-items": "center",
  });

  // placeholder
  function updateSelect2Placeholder() {
    $("#gender").each(function () {
      var $select = $(this);
      var $rendered = $select
        .next(".select2-container")
        .find(".select2-selection__rendered");
      if ($select.val() === "" || $select.val() === null) {
        $rendered.css({
          color: "#bbbbbb",
          "line-height": "50px",
        });
      } else {
        $rendered.css({
          color: "#222",
          "line-height": "50px",
        });
      }
    });
  }

  updateSelect2Placeholder();

  $("#gender").on("change", function () {
    updateSelect2Placeholder();
  });

  // السهم
  $(".select2-selection__arrow").css({
    height: "100%",
    display: "flex",
    "align-items": "center",
    "justify-content": "center",
    right: "12px",
    top: "0",
  });

  $(".select2-selection__arrow b").css({
    "border-color": "#8B4513 transparent transparent transparent",
    "border-width": "6px 6px 0 6px",
  });

  // جعل dropdown بنفس العرض
  $(".select2-dropdown").css({
    width: "100% !important",
  });
});
