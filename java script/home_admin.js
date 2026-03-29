// ========================== home_admin.js ==========================
// Handles UI, menu toggle, preloader, and admin login state

document.addEventListener("DOMContentLoaded", () => {
  // ========================== Animations ==========================
  const welcome = document.querySelector(".welcome");
  const signUp = document.querySelector(".sign_up");
  if (welcome) welcome.classList.add("show");
  if (signUp) signUp.classList.add("show");

  // ========================== Hamburger Menu ==========================
  const menuToggle = document.getElementById("hamburger");
  const links = document.querySelector(".links");

  if (menuToggle && links) {
    let isOpen = false;

    menuToggle.style.cursor = "pointer";
    menuToggle.style.fontSize = "26px";
    menuToggle.style.transition = "transform 0.3s ease";

    menuToggle.addEventListener("click", (e) => {
      e.preventDefault();
      links.classList.toggle("active");
      isOpen = !isOpen;
      menuToggle.textContent = isOpen ? "✖" : "☰";
      menuToggle.style.transform = isOpen ? "rotate(180deg)" : "rotate(0deg)";
    });

    document.addEventListener("click", (e) => {
      if (!menuToggle.contains(e.target) && !links.contains(e.target)) {
        links.classList.remove("active");
        isOpen = false;
        menuToggle.textContent = "☰";
        menuToggle.style.transform = "rotate(0deg)";
      }
    });
  }

  // ========================== Preloader ==========================
  const preloader = document.getElementById("preloader");
  if (preloader) {
    window.addEventListener("load", () => {
      setTimeout(() => {
        preloader.style.opacity = "0";
        preloader.style.transition = "opacity 0.5s ease";
        setTimeout(() => {
          preloader.style.display = "none";
        }, 500);
      }, 500);
    });
  }

  // ========================== Login State Management ==========================

  async function fetchLoginState() {
    try {
      const res = await fetch("check_login_admin.php", { method: "POST" });
      const data = await res.json();
      return data;
    } catch (err) {
      console.error("Error checking login state:", err);
      return null;
    }
  }

  function updateLoginUI(isLoggedIn) {
    // Update Login / Logout buttons
    document.querySelectorAll(".link_login, .authLink").forEach((link) => {
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

    // Toggle visibility of elements
    const accountDiv = document.querySelector(".account");
    const saleDiv = document.querySelector(".sale");
    const navProfileBtn = document.getElementById("navProfileBtn");
    const footerProfileBtn = document.getElementById("footerProfileBtn");

    if (accountDiv) accountDiv.style.display = isLoggedIn ? "none" : "block";
    if (saleDiv) saleDiv.style.display = isLoggedIn ? "block" : "none";
    if (navProfileBtn) navProfileBtn.style.display = isLoggedIn ? "block" : "none";
    if (footerProfileBtn) footerProfileBtn.style.display = isLoggedIn ? "block" : "none";
  }

  async function initializeLoginState() {
    const data = await fetchLoginState();
    const isLoggedIn = data && data.status === "loggedin";
    updateLoginUI(isLoggedIn);
  }

  initializeLoginState();

  // ========================== Logout Confirmation ==========================
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
              updateLoginUI(false);
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
});

