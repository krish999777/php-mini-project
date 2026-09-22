/**
 * Candidate Dashboard & Profile Management JavaScript
 */

document.addEventListener("DOMContentLoaded", () => {
    // 1. Tab Switching (Editor vs Live Resume Preview)
    const tabButtons = document.querySelectorAll(".cand-tab-btn");
    const tabPanes = document.querySelectorAll(".cand-tab-pane");

    tabButtons.forEach(button => {
        button.addEventListener("click", () => {
            const targetTab = button.getAttribute("data-tab");
            
            tabButtons.forEach(btn => btn.classList.remove("active"));
            tabPanes.forEach(pane => pane.classList.remove("active"));

            button.classList.add("active");
            const activePane = document.getElementById(targetTab);
            if (activePane) {
                activePane.classList.add("active");
            }

            if (targetTab === "preview-tab") {
                updateResumePreview();
            }
        });
    });

    // 2. Interactive Skills Tagging
    const skillInput = document.getElementById("skill-tag-input");
    const skillsContainer = document.getElementById("skills-pills-container");
    const hiddenSkillsInput = document.getElementById("skills-hidden-input");

    let currentSkills = [];
    if (hiddenSkillsInput && hiddenSkillsInput.value.trim() !== "") {
        currentSkills = hiddenSkillsInput.value
            .split(",")
            .map(s => s.trim())
            .filter(s => s.length > 0);
    }

    function renderSkillPills() {
        if (!skillsContainer || !hiddenSkillsInput) return;
        skillsContainer.innerHTML = "";

        currentSkills.forEach((skill, index) => {
            const pill = document.createElement("span");
            pill.className = "skill-tag-pill";
            pill.innerHTML = `
                <span>${escapeHtml(skill)}</span>
                <button type="button" class="remove-pill-btn" data-index="${index}" aria-label="Remove skill">&times;</button>
            `;
            skillsContainer.appendChild(pill);
        });

        hiddenSkillsInput.value = currentSkills.join(", ");
    }

    if (skillsContainer) {
        skillsContainer.addEventListener("click", (e) => {
            if (e.target.classList.contains("remove-pill-btn")) {
                const idx = parseInt(e.target.getAttribute("data-index"), 10);
                if (!isNaN(idx)) {
                    currentSkills.splice(idx, 1);
                    renderSkillPills();
                }
            }
        });
    }

    if (skillInput) {
        skillInput.addEventListener("keydown", (e) => {
            if (e.key === "Enter" || e.key === ",") {
                e.preventDefault();
                const val = skillInput.value.trim().replace(/,/g, "");
                if (val && !currentSkills.includes(val)) {
                    currentSkills.push(val);
                    renderSkillPills();
                }
                skillInput.value = "";
            }
        });

        // Also add on blur if user typed something
        skillInput.addEventListener("blur", () => {
            const val = skillInput.value.trim().replace(/,/g, "");
            if (val && !currentSkills.includes(val)) {
                currentSkills.push(val);
                renderSkillPills();
            }
            skillInput.value = "";
        });
    }

    // Initial render of skills
    renderSkillPills();

    // 3. Update Resume Preview dynamically from form values
    function updateResumePreview() {
        const nameVal = document.getElementById("cand_name")?.value.trim() || "Your Name";
        const headlineVal = document.getElementById("cand_headline")?.value.trim() || "Professional Headline";
        const emailVal = document.getElementById("cand_email_display")?.innerText.trim() || "";
        const phoneVal = document.getElementById("cand_phone")?.value.trim() || "";
        const locationVal = document.getElementById("cand_location")?.value.trim() || "";
        const expVal = document.getElementById("cand_experience_years")?.value.trim() || "0";
        const bioVal = document.getElementById("cand_bio")?.value.trim() || "";
        const educationVal = document.getElementById("cand_education")?.value.trim() || "";

        // Update preview DOM elements
        const pName = document.getElementById("prev-name");
        const pHeadline = document.getElementById("prev-headline");
        const pContact = document.getElementById("prev-contact-bar");
        const pBio = document.getElementById("prev-bio");
        const pExpBadge = document.getElementById("prev-exp-badge");
        const pEducation = document.getElementById("prev-education");
        const pSkillsList = document.getElementById("prev-skills-list");

        if (pName) pName.textContent = nameVal;
        if (pHeadline) pHeadline.textContent = headlineVal;

        if (pContact) {
            let contactItems = [];
            if (emailVal) contactItems.push(`<span>📧 ${escapeHtml(emailVal)}</span>`);
            if (phoneVal) contactItems.push(`<span>📞 ${escapeHtml(phoneVal)}</span>`);
            if (locationVal) contactItems.push(`<span>📍 ${escapeHtml(locationVal)}</span>`);
            pContact.innerHTML = contactItems.join(" &bull; ");
        }

        if (pExpBadge) {
            pExpBadge.textContent = `${expVal} Year${expVal === "1" ? "" : "s"} Experience`;
        }

        if (pBio) {
            pBio.innerHTML = bioVal 
                ? escapeHtml(bioVal).replace(/\n/g, "<br>") 
                : "<em>No professional summary added yet. Use the Profile Editor to describe your background and achievements.</em>";
        }

        if (pEducation) {
            pEducation.innerHTML = educationVal 
                ? escapeHtml(educationVal).replace(/\n/g, "<br>") 
                : "<em>No education details specified.</em>";
        }

        if (pSkillsList) {
            if (currentSkills.length > 0) {
                pSkillsList.innerHTML = currentSkills
                    .map(s => `<span class="resume-skill-badge">${escapeHtml(s)}</span>`)
                    .join("");
            } else {
                pSkillsList.innerHTML = "<em>No skills added yet.</em>";
            }
        }
    }

    // 4. AJAX Save Form Handler
    const profileForm = document.getElementById("candidate-profile-form");
    if (profileForm) {
        profileForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            const submitBtn = profileForm.querySelector("button[type=\"submit\"]");
            const alertBox = document.getElementById("profile-alert");

            if (submitBtn) {
                submitBtn.classList.add("loading");
                submitBtn.disabled = true;
            }
            if (alertBox) {
                alertBox.style.display = "none";
            }

            const formData = new FormData(profileForm);

            try {
                const response = await fetch("api/update_profile.php", {
                    method: "POST",
                    body: formData,
                    headers: {
                        "X-Requested-With": "XMLHttpRequest"
                    }
                });

                const data = await response.json();

                if (data.success) {
                    showProfileAlert("✅ " + data.message, "success");
                    // Sync header name
                    const navUserName = document.getElementById("header-user-name");
                    if (navUserName && data.profile && data.profile.name) {
                        navUserName.textContent = data.profile.name;
                    }
                    updateResumePreview();
                } else {
                    showProfileAlert(data.message || "Failed to save profile.", "error");
                }
            } catch (err) {
                console.error("Profile save error:", err);
                showProfileAlert("Server connection error while saving profile.", "error");
            } finally {
                if (submitBtn) {
                    submitBtn.classList.remove("loading");
                    submitBtn.disabled = false;
                }
            }
        });
    }

    function showProfileAlert(msg, type) {
        const alertBox = document.getElementById("profile-alert");
        if (!alertBox) return;
        alertBox.className = `alert-box alert-${type}`;
        alertBox.textContent = msg;
        alertBox.style.display = "block";
        alertBox.scrollIntoView({ behavior: "smooth", block: "nearest" });
    }

    function escapeHtml(string) {
        return String(string)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // 5. Print Resume Action
    const printBtn = document.getElementById("btn-print-resume");
    if (printBtn) {
        printBtn.addEventListener("click", () => {
            updateResumePreview();
            window.print();
        });
    }
});
