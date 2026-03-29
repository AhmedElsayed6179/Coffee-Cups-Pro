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

//////////// Delete Account
document.addEventListener("DOMContentLoaded", function () {
  const deleteBtn = document.getElementById("deleteBtn");

  deleteBtn.addEventListener("click", async function () {
    try {
      // طلب كلمة السر
      const { value: password, isConfirmed } = await Swal.fire({
        title: "Confirm Account Deletion",
        html: `<style> 
        .pw-note {
            font-size: 12.5px;
            color: #555;
            margin-top: 6px;
            text-align: left;
            line-height: 1.6;
        }
        .pw-note a {
            color: #8B4513;
            text-decoration: none;
            font-weight: 500;
        }
            </style> 
    <div style="text-align: left;">
      <p style="margin-bottom: 10px;">Please confirm your password to delete your account permanently.</p>
      <div style="
        position: relative;
        display: flex;
        align-items: center;
        border: 1px solid #ccc;
        border-radius: 8px;
        padding: 5px 10px;
        background: #fff;
      ">
        <input id="delete-account-pass"
               type="password"
               placeholder="Enter your password"
               style="flex: 1; border: none; outline: none; font-size: 15px; padding: 8px;">
        <button id="toggleDelAccPass" type="button" style="background:none;border:none;cursor:pointer;color:#555;font-size:1.3rem;">
          <i class='fas fa-eye'></i>
        </button>
      </div>
      <p class="pw-note">
            If you forgot your password,<br>
            <a href="forgetpass.html">click here.</a>
        </p>
    </div>
  `,
        showCancelButton: true,
        confirmButtonText: "Delete Account",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#c0392b",
        focusConfirm: false,
        didOpen: () => {
          const input = document.getElementById("delete-account-pass");
          const toggleBtn = document.getElementById("toggleDelAccPass");
          const icon = toggleBtn.querySelector("i");

          toggleBtn.addEventListener("click", () => {
            if (input.type === "password") {
              input.type = "text";
              icon.classList.remove("fa-eye");
              icon.classList.add("fa-eye-slash");
            } else {
              input.type = "password";
              icon.classList.remove("fa-eye-slash");
              icon.classList.add("fa-eye");
            }
          });

          input.focus();
        },
        preConfirm: () => {
          const input = document.getElementById("delete-account-pass");
          const pwd = input.value.trim();
          if (!pwd) {
            Swal.showValidationMessage("Password is required!");
            return false;
          }
          return pwd;
        },
      });

      if (!isConfirmed || !password) return;

      // تأكيد نهائي قبل الحذف
      const { isConfirmed: finalConfirm } = await Swal.fire({
        title: "Are you sure?",
        text: "This action cannot be undone!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
        confirmButtonColor: "#c0392b",
        cancelButtonColor: "#ccc",
      });

      if (!finalConfirm) return;

      // إرسال الطلب للـ PHP
      const response = await fetch("delete_account_admin.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: `password=${encodeURIComponent(password)}`,
      });

      const data = await response.json();

      if (data.status === "success") {
        await Swal.fire({
          icon: "success",
          title: "Account Deleted",
          text: data.message,
          confirmButtonColor: "#8B4513",
        });
        window.location.href = "home_admin.html";
      } else {
        await Swal.fire({
          icon: "error",
          title: "Error",
          text: data.message,
          confirmButtonColor: "#8B4513",
        });
      }
    } catch (error) {
      await Swal.fire({
        icon: "error",
        title: "Network Error",
        text: "Please try again later.",
        confirmButtonColor: "#8B4513",
      });
    }
  });
});

//////////// Change Password
async function updatePassword() {
  const { value: formValues } = await Swal.fire({
    title: "Change Password",
    html: `
      <style>
        .swal2-popup.custom-password-popup {
          border-radius: 20px !important;
          padding: 20px !important;
          width: 100% !important;
          max-width: 420px !important;
          box-sizing: border-box;
        }
        .pw-field {
          position: relative;
          margin-bottom: 15px;
        }
        .pw-field input {
          width: 100%;
          height: 42px;
          border: 1px solid #ccc;
          border-radius: 10px;
          padding: 0 40px 0 12px;
          font-size: 14px;
          outline: none;
          transition: 0.3s;
        }
        .pw-field input:focus {
          border-color: #8B4513;
          box-shadow: 0 0 0 2px rgba(139, 69, 19, 0.15);
        }
        .pw-field i {
          position: absolute;
          right: 12px;
          top: 50%;
          transform: translateY(-50%);
          color: #666;
          cursor: pointer;
          font-size: 16px;
          transition: 0.3s;
        }
        .pw-field i:hover {
          color: #8B4513;
        }
        .pw-label {
          font-weight: 600;
          font-size: 14px;
          margin-bottom: 5px;
          display: block;
          color: #333;
        }
        @media (max-width: 480px) {
          .swal2-popup.custom-password-popup {
            width: 100vw !important;
            height: 100vh !important;
            margin: 0 !important;
            border-radius: 0 !important;
            display: flex;
            align-items: center;
            justify-content: center;
          }
        }
      </style>

      <div style="font-family:'Poppins',sans-serif;max-width:360px;margin:auto;text-align:left;">
        <label class="pw-label">Old Password</label>
        <div class="pw-field">
          <input id="old-pass" type="password" placeholder="Enter old password">
          <i id="toggleOld" class="fas fa-eye"></i>
        </div>

        <label class="pw-label">New Password</label>
        <div class="pw-field">
          <input id="new-pass" type="password" placeholder="Enter new password">
          <i id="toggleNew" class="fas fa-eye"></i>
        </div>

        <label class="pw-label">Confirm New Password</label>
        <div class="pw-field">
          <input id="confirm-pass" type="password" placeholder="Confirm new password">
          <i id="toggleConfirm" class="fas fa-eye"></i>
        </div>
      </div>
    `,
    confirmButtonText: "Update Password",
    cancelButtonText: "Cancel",
    confirmButtonColor: "#8B4513",
    showCancelButton: true,
    background: "#fff",
    focusConfirm: false,
    customClass: { popup: "custom-password-popup" },

    didOpen: () => {
      const inputs = [
        document.getElementById("old-pass"),
        document.getElementById("new-pass"),
        document.getElementById("confirm-pass"),
      ];
      const toggles = [
        document.getElementById("toggleOld"),
        document.getElementById("toggleNew"),
        document.getElementById("toggleConfirm"),
      ];

      toggles.forEach((toggle, i) => {
        toggle.addEventListener("click", () => {
          const input = inputs[i];
          const isHidden = input.type === "password";
          input.type = isHidden ? "text" : "password";
          toggle.className = isHidden ? "fas fa-eye-slash" : "fas fa-eye";
        });
      });
    },

    preConfirm: () => {
      const oldPass = document.getElementById("old-pass").value.trim();
      const newPass = document.getElementById("new-pass").value.trim();
      const confirmPass = document.getElementById("confirm-pass").value.trim();

      if (!oldPass || !newPass || !confirmPass) {
        Swal.showValidationMessage("All fields are required!");
        return false;
      }

      if (newPass === oldPass) {
        Swal.showValidationMessage(
          "New password can’t be the same as the old one!"
        );
        return false;
      }

      // ✅ Regex مضبوط تمامًا بدون escape زيادة
      const strongRegex =
        /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;

      if (!strongRegex.test(newPass)) {
        Swal.showValidationMessage(
          "Password must include upper, lower, number & symbol (8+ chars)."
        );
        return false;
      }

      if (newPass !== confirmPass) {
        Swal.showValidationMessage("Passwords do not match!");
        return false;
      }

      return { oldPass, newPass };
    },
  });

  if (!formValues) return;

  Swal.fire({
    title: "Updating Password...",
    allowOutsideClick: false,
    didOpen: () => Swal.showLoading(),
  });

  try {
    const response = await fetch("update_password_admin.php", {
      method: "POST",
      headers: { "Content-Type": "application/x-www-form-urlencoded" },
      body: `old_password=${encodeURIComponent(
        formValues.oldPass
      )}&new_password=${encodeURIComponent(formValues.newPass)}`,
    });

    const data = await response.json();
    Swal.close();

    if (data.status === "success") {
      Swal.fire({
        icon: "success",
        title: "Password Updated!",
        text: data.message,
        confirmButtonColor: "#8B4513",
        showConfirmButton: false,
        timer: 2000,
      }).then(() => (window.location.href = "login.html"));
    } else if (data.message.toLowerCase().includes("incorrect")) {
      Swal.fire({
        icon: "error",
        title: "Incorrect Password",
        text: "Your old password is incorrect. Please try again.",
        confirmButtonColor: "#8B4513",
      });
    } else {
      Swal.fire({
        icon: "error",
        title: "Error",
        text: data.message || "Something went wrong. Please try again.",
        confirmButtonColor: "#8B4513",
      });
    }
  } catch (error) {
    Swal.close();
    Swal.fire({
      icon: "error",
      title: "Network Error",
      text: "Please try again later.",
      confirmButtonColor: "#8B4513",
    });
  }
}

document
  .getElementById("changePasswordBtn")
  .addEventListener("click", updatePassword);

/* ============================================================
   upload image
============================================================ */
document.addEventListener("DOMContentLoaded", () => {
  const changeBtn = document.getElementById("changePicBtn");
  const deleteBtn = document.getElementById("deletePicBtn");
  const profileInput = document.getElementById("profileUpload");
  const profileImg = document.querySelector(".user-img");
  const DEFAULT_IMAGE = "uploads_admin/default.webp";

  // =============== إغلاق القوائم عند الضغط على الأزرار ===============
  changeBtn?.addEventListener("click", () => closeAllMenus());
  deleteBtn?.addEventListener("click", () => closeAllMenus());

  // =============== عرض الصورة بجودتها العالية ===============
  if (profileImg) {
    profileImg.addEventListener("click", () => {
      // تحميل الصورة الأصلية بجودتها الكاملة
      const highRes = profileImg.src.replace("thumb_", ""); // لو عندك صور thumbnail
      Swal.fire({
        title: "Profile Picture",
        html: `
                    <div style="overflow:hidden;border-radius:10px;max-width:90vw;">
                        <img src="${highRes}" alt="Profile Picture" 
                        style="width:100%;max-width:450px;border-radius:10px;
                        box-shadow:0 0 20px rgba(0,0,0,0.5);">
                    </div>
                `,
        background: "#1e1e1e",
        color: "#fff",
        showConfirmButton: true,
        confirmButtonText: "Close",
        allowOutsideClick: false,
        allowEscapeKey: false,
        customClass: {
          popup: "animated fadeInDown faster",
        },
      });
    });
  }

  // =============== اختيار وتغيير الصورة مع القص الاحترافي ===============
  if (changeBtn && profileInput) {
    changeBtn.addEventListener("click", () => {
      profileInput.click();
    });
  }

  if (profileInput) {
    profileInput.addEventListener("change", () => {
      if (profileInput.files.length > 0) {
        const file = profileInput.files[0];
        const reader = new FileReader();

        reader.onload = () => {
          Swal.fire({
            title: "Edit your picture",
            html: `
                            <div style="max-width:350px;margin:auto;">
                                <img id="cropImage" src="${reader.result}" 
                                     style="max-width:100%;border-radius:10px;">
                            </div>
                            <div style="margin-top:15px;">
                                <button id="rotateLeft" class="swal2-styled" style="margin-right:5px;">↺ Rotate Left</button>
                                <button id="rotateRight" class="swal2-styled" style="margin-right:5px;">↻ Rotate Right</button>
                                <button id="flipX" class="swal2-styled" style="margin-right:5px;">⇋ Flip</button>
                                <button id="resetCrop" class="swal2-styled">Reset</button>
                            </div>
                        `,
            showCancelButton: true,
            confirmButtonText: "Save",
            cancelButtonText: "Cancel",
            allowOutsideClick: false,
            allowEscapeKey: false,
            didOpen: () => {
              const image = document.getElementById("cropImage");
              const cropper = new Cropper(image, {
                aspectRatio: 1,
                viewMode: 2,
                background: false,
                autoCropArea: 1,
                movable: true,
                zoomable: true,
                rotatable: true,
                scalable: true,
              });

              // أدوات التدوير والقلب
              document
                .getElementById("rotateLeft")
                .addEventListener("click", () => cropper.rotate(-90));
              document
                .getElementById("rotateRight")
                .addEventListener("click", () => cropper.rotate(90));
              document
                .getElementById("flipX")
                .addEventListener("click", () =>
                  cropper.scaleX(-cropper.getData().scaleX || -1)
                );
              document
                .getElementById("resetCrop")
                .addEventListener("click", () => cropper.reset());

              Swal.getConfirmButton().addEventListener("click", () => {
                cropper
                  .getCroppedCanvas({
                    width: 500,
                    height: 500,
                    imageSmoothingQuality: "high",
                  })
                  .toBlob(
                    (blob) => {
                      const formData = new FormData();
                      formData.append("profile_image", blob, "cropped.png");
                      formData.append("upload", "1");

                      fetch("upload_image_admin.php", {
                        method: "POST",
                        body: formData,
                      })
                        .then((res) => res.json())
                        .then((data) => {
                          if (data.success) {
                            Swal.fire({
                              icon: "success",
                              title: "Updated!",
                              text: data.success,
                              timer: 2000,
                              showConfirmButton: false,
                            }).then(() => location.reload());
                          } else {
                            Swal.fire({
                              icon: "error",
                              title: "Error",
                              text: data.error || "Please try again",
                            });
                          }
                        })
                        .catch((err) => {
                          Swal.fire({
                            icon: "error",
                            title: "Error",
                            text: "Please try again",
                          });
                          console.error(err);
                        });
                    },
                    "image/png",
                    1.0
                  ); // حفظ بجودة 100%
              });
            },
          });
        };
        reader.readAsDataURL(file);
      }
    });
  }

  // =============== حذف الصورة ===============
  if (deleteBtn && profileImg) {
    deleteBtn.addEventListener("click", (e) => {
      e.preventDefault();

      if (profileImg.src.includes(DEFAULT_IMAGE)) {
        Swal.fire({
          icon: "info",
          title: "No picture",
          text: "There is no profile picture to delete.",
        });
        return;
      }

      Swal.fire({
        title: "Are you sure?",
        text: "The profile picture will be permanently deleted!",
        icon: "warning",
        showCancelButton: true,
        confirmButtonColor: "#c0392b",
        cancelButtonColor: "#3085d6",
        confirmButtonText: "Yes, delete it!",
        cancelButtonText: "Cancel",
      }).then((result) => {
        if (result.isConfirmed) {
          const formData = new FormData();
          formData.append("delete", "1");

          fetch("upload_image_admin.php", {
            method: "POST",
            body: formData,
          })
            .then((res) => res.json())
            .then((data) => {
              if (data.success) {
                Swal.fire({
                  icon: "success",
                  title: "Deleted!",
                  text: data.success,
                  timer: 2000,
                  showConfirmButton: false,
                }).then(() => location.reload());
              } else {
                Swal.fire({
                  icon: "error",
                  title: "Error",
                  text: data.error || "Please try again",
                });
              }
            })
            .catch((err) => {
              Swal.fire({
                icon: "error",
                title: "Error",
                text: "Please try again",
              });
              console.error(err);
            });
        }
      });
    });
  }
});