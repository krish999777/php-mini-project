/**
 * ResumeHub / Candidate Recruitment Portal - Client-Side Authentication Scripts
 */

document.addEventListener("DOMContentLoaded", () => {
    // Role selection tabs (Candidate / Recruiter)
    const roleButtons = document.querySelectorAll(".role-toggle-btn");
    roleButtons.forEach(button => {
        button.addEventListener("click", () => {
            roleButtons.forEach(btn => btn.classList.remove("active"));
            button.classList.add("active");
            const radio = button.querySelector("input[type=\"radio\"]");
            if (radio) {
                radio.checked = true;
            }
        });
    });

    // Password visibility toggle
    const togglePasswordButtons = document.querySelectorAll(".toggle-password, .toggle-pwd");
    togglePasswordButtons.forEach(button => {
        button.addEventListener("click", () => {
            const input = button.previousElementSibling;
            if (!input) return;
            
            if (input.type === "password") {
                input.type = "text";
                button.innerHTML = `
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                `;
            } else {
                input.type = "password";
                button.innerHTML = `
                    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                `;
            }
        });
    });

    // Generic AJAX Form Handler
    const forms = document.querySelectorAll("form[data-ajax=\"true\"]");
    forms.forEach(form => {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();

            const submitBtn = form.querySelector("button[type=\"submit\"]");
            const alertBox = form.querySelector(".alert-box") || document.querySelector(".alert-box");
            
            // Client-side confirmation check if present
            const pwd = form.querySelector("#password");
            const confirmPwd = form.querySelector("#confirm_password");
            if (pwd && confirmPwd && confirmPwd.value.length > 0 && pwd.value !== confirmPwd.value) {
                showAlert(alertBox, "Passwords do not match.", "error");
                return;
            }

            // Set loading state
            if (submitBtn) {
                submitBtn.classList.add("loading");
                submitBtn.disabled = true;
            }
            if (alertBox) {
                alertBox.style.display = "none";
            }

            const formData = new FormData(form);
            const actionUrl = form.getAttribute("action");

            try {
                const response = await fetch(actionUrl, {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showAlert(alertBox, data.message || "Success! Redirecting...", "success");
                    setTimeout(() => {
                        window.location.href = data.redirect || "dashboard.php";
                    }, 800);
                } else {
                    showAlert(alertBox, data.message || "An error occurred. Please try again.", "error");
                    if (submitBtn) {
                        submitBtn.classList.remove("loading");
                        submitBtn.disabled = false;
                    }
                }
            } catch (err) {
                console.error("Fetch error:", err);
                showAlert(alertBox, "Unable to connect to server. Ensure MySQL and Apache are running in XAMPP.", "error");
                if (submitBtn) {
                    submitBtn.classList.remove("loading");
                    submitBtn.disabled = false;
                }
            }
        });
    });
});

function showAlert(element, message, type) {
    if (!element) return;
    element.className = `alert-box alert-${type}`;
    element.textContent = message;
    element.style.display = "block";
}
