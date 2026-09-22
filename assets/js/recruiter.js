/**
 * Recruiter Portal - Candidate Discovery & Modal JavaScript
 */

document.addEventListener("DOMContentLoaded", () => {
    const searchInput = document.getElementById("recruiter-search-input");
    const clearSearchBtn = document.getElementById("btn-clear-search");
    const candidateGrid = document.getElementById("candidates-grid");
    const candidateCountEl = document.getElementById("candidate-count-number");
    const emptyStateEl = document.getElementById("candidates-empty-state");

    // Modal elements
    const modal = document.getElementById("candidate-detail-modal");
    const modalBackdrop = document.getElementById("modal-backdrop");
    const modalCloseBtn = document.getElementById("modal-close-btn");
    const modalPrintBtn = document.getElementById("modal-print-btn");

    let debounceTimer = null;

    // 1. Debounced Search Handler
    if (searchInput) {
        searchInput.addEventListener("input", () => {
            const query = searchInput.value.trim();
            if (clearSearchBtn) {
                clearSearchBtn.style.display = query.length > 0 ? "inline-flex" : "none";
            }
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                fetchCandidates(query);
            }, 300);
        });
    }

    if (clearSearchBtn) {
        clearSearchBtn.addEventListener("click", () => {
            if (searchInput) {
                searchInput.value = "";
                clearSearchBtn.style.display = "none";
                fetchCandidates("");
                searchInput.focus();
            }
        });
    }

    // 2. Fetch Candidates API
    async function fetchCandidates(searchQuery) {
        try {
            if (candidateGrid) {
                candidateGrid.style.opacity = "0.5";
            }

            const url = `api/get_candidates.php?search=${encodeURIComponent(searchQuery)}`;
            const response = await fetch(url, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });
            const data = await response.json();

            if (data.success) {
                renderCandidateCards(data.candidates || []);
                if (candidateCountEl) {
                    candidateCountEl.textContent = data.count;
                }
            }
        } catch (err) {
            console.error("Error fetching candidates:", err);
        } finally {
            if (candidateGrid) {
                candidateGrid.style.opacity = "1";
            }
        }
    }

    // 3. Render Candidate Cards
    function renderCandidateCards(candidates) {
        if (!candidateGrid) return;
        candidateGrid.innerHTML = "";

        if (candidates.length === 0) {
            if (emptyStateEl) emptyStateEl.style.display = "block";
            return;
        }

        if (emptyStateEl) emptyStateEl.style.display = "none";

        candidates.forEach(cand => {
            const card = document.createElement("div");
            card.className = "candidate-card";

            const name = escapeHtml(cand.name || "Unnamed Candidate");
            const headline = escapeHtml(cand.headline || "Candidate / Job Seeker");
            const exp = parseInt(cand.experience_years, 10) || 0;
            const expLabel = `${exp} yr${exp === 1 ? "" : "s"} exp`;
            const location = cand.location ? `📍 ${escapeHtml(cand.location)}` : "";
            const avatarLetter = name.charAt(0).toUpperCase() || "C";

            // Process skills
            let skillsHtml = "";
            if (cand.skills && cand.skills.trim().length > 0) {
                const skillsList = cand.skills.split(",").map(s => s.trim()).filter(s => s.length > 0);
                const displaySkills = skillsList.slice(0, 4);
                skillsHtml = displaySkills.map(s => `<span class="cand-skill-chip">${escapeHtml(s)}</span>`).join("");
                if (skillsList.length > 4) {
                    skillsHtml += `<span class="cand-skill-chip-more">+${skillsList.length - 4}</span>`;
                }
            } else {
                skillsHtml = `<span style="font-size: 0.8rem; color: #9CA3AF; font-style: italic;">No skills specified</span>`;
            }

            card.innerHTML = `
                <div class="cand-card-top">
                    <div class="cand-avatar">${avatarLetter}</div>
                    <div class="cand-card-main-info">
                        <h3 class="cand-card-name">${name}</h3>
                        <div class="cand-card-headline">${headline}</div>
                    </div>
                </div>

                <div class="cand-card-meta">
                    <span class="cand-card-exp-tag">${expLabel}</span>
                    ${location ? `<span class="cand-card-location">${location}</span>` : ""}
                </div>

                <div class="cand-card-skills">
                    ${skillsHtml}
                </div>

                <div class="cand-card-footer">
                    <button type="button" class="btn-view-profile" data-id="${cand.id}">
                        View Full Resume
                    </button>
                </div>
            `;

            candidateGrid.appendChild(card);
        });
    }

    // 4. Modal Event Listeners
    if (candidateGrid) {
        candidateGrid.addEventListener("click", (e) => {
            const btn = e.target.closest(".btn-view-profile");
            if (btn) {
                const candidateId = btn.getAttribute("data-id");
                openCandidateModal(candidateId);
            }
        });
    }

    async function openCandidateModal(candidateId) {
        if (!modal) return;

        // Reset modal content
        document.getElementById("modal-cand-name").textContent = "Loading candidate...";
        document.getElementById("modal-cand-headline").textContent = "";
        document.getElementById("modal-cand-contacts").innerHTML = "";
        document.getElementById("modal-cand-exp-badge").textContent = "";
        document.getElementById("modal-cand-bio").innerHTML = "<div class='modal-loading-spinner'></div>";
        document.getElementById("modal-cand-skills").innerHTML = "";
        document.getElementById("modal-cand-education").innerHTML = "";

        modal.classList.add("active");
        document.body.style.overflow = "hidden";

        try {
            const response = await fetch(`api/get_candidate_detail.php?id=${encodeURIComponent(candidateId)}`, {
                headers: { "X-Requested-With": "XMLHttpRequest" }
            });
            const data = await response.json();

            if (data.success && data.candidate) {
                populateModal(data.candidate);
            } else {
                document.getElementById("modal-cand-name").textContent = "Profile Not Found";
                document.getElementById("modal-cand-bio").textContent = data.message || "Failed to load candidate details.";
            }
        } catch (err) {
            console.error("Modal fetch error:", err);
            document.getElementById("modal-cand-name").textContent = "Error";
            document.getElementById("modal-cand-bio").textContent = "Unable to connect to server.";
        }
    }

    function populateModal(c) {
        const name = escapeHtml(c.name || "Candidate");
        const headline = escapeHtml(c.headline || "Professional Headline");
        const email = escapeHtml(c.email || "");
        const phone = escapeHtml(c.phone || "");
        const location = escapeHtml(c.location || "");
        const expYears = parseInt(c.experience_years, 10) || 0;
        const bio = c.bio ? escapeHtml(c.bio).replace(/\n/g, "<br>") : "<em>No professional summary provided.</em>";
        const education = c.education ? escapeHtml(c.education).replace(/\n/g, "<br>") : "<em>No education details specified.</em>";

        document.getElementById("modal-cand-name").textContent = name;
        document.getElementById("modal-cand-headline").textContent = headline;
        document.getElementById("modal-cand-exp-badge").textContent = `${expYears} Year${expYears === 1 ? "" : "s"} Experience`;

        // Contact info
        let contacts = [];
        if (email) contacts.push(`<span>📧 <a href="mailto:${email}" style="color: inherit; text-decoration: underline;">${email}</a></span>`);
        if (phone) contacts.push(`<span>📞 <a href="tel:${phone}" style="color: inherit; text-decoration: underline;">${phone}</a></span>`);
        if (location) contacts.push(`<span>📍 ${location}</span>`);
        document.getElementById("modal-cand-contacts").innerHTML = contacts.join(" &bull; ");

        document.getElementById("modal-cand-bio").innerHTML = bio;
        document.getElementById("modal-cand-education").innerHTML = education;

        // Skills
        const skillsContainer = document.getElementById("modal-cand-skills");
        if (c.skills && c.skills.trim().length > 0) {
            const skillsArr = c.skills.split(",").map(s => s.trim()).filter(s => s.length > 0);
            skillsContainer.innerHTML = skillsArr.map(s => `<span class="resume-skill-badge">${escapeHtml(s)}</span>`).join("");
        } else {
            skillsContainer.innerHTML = "<em>No skills specified.</em>";
        }
    }

    function closeModal() {
        if (modal) {
            modal.classList.remove("active");
            document.body.style.overflow = "";
        }
    }

    if (modalCloseBtn) {
        modalCloseBtn.addEventListener("click", closeModal);
    }

    if (modalBackdrop) {
        modalBackdrop.addEventListener("click", closeModal);
    }

    document.addEventListener("keydown", (e) => {
        if (e.key === "Escape" && modal && modal.classList.contains("active")) {
            closeModal();
        }
    });

    if (modalPrintBtn) {
        modalPrintBtn.addEventListener("click", () => {
            window.print();
        });
    }

    function escapeHtml(string) {
        return String(string)
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }
});
