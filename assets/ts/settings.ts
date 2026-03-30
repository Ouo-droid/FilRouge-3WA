import UserService from "./services/UserService";
import { getCsrfToken } from "./services/Api";

document.addEventListener('DOMContentLoaded', () => {
    const settingsForm = document.getElementById("settings-form") as HTMLFormElement;

    // ── Degree management ──────────────────────────────────────────────
    const degreeListEl  = document.getElementById('degree-list')   as HTMLUListElement | null;
    const degreeInputEl = document.getElementById('degree-input')  as HTMLInputElement | null;
    const degreeAddBtn  = document.getElementById('degree-add-btn') as HTMLButtonElement | null;

    // Init from server-rendered DOM
    let degrees: string[] = degreeListEl
        ? Array.from(degreeListEl.querySelectorAll<HTMLElement>('.degree-item'))
              .map(li => li.dataset.value ?? '')
              .filter(Boolean)
        : [];

    function renderDegrees() {
        if (!degreeListEl) return;
        degreeListEl.innerHTML = '';
        degrees.forEach((d, index) => {
            const li = document.createElement('li');
            li.className = 'degree-item';
            li.dataset.value = d;
            li.innerHTML = `
                <span class="degree-text">${d}</span>
                <div class="degree-actions">
                    <button type="button" class="degree-btn-edit" data-index="${index}" title="Modifier">
                        <i class="fas fa-pen"></i>
                    </button>
                    <button type="button" class="degree-btn-delete" data-index="${index}" title="Supprimer">
                        <i class="fas fa-times"></i>
                    </button>
                </div>`;
            degreeListEl.appendChild(li);
        });

        degreeListEl.querySelectorAll<HTMLElement>('.degree-btn-edit').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.dataset.index ?? '0', 10);
                if (degreeInputEl) degreeInputEl.value = degrees[idx];
                degrees.splice(idx, 1);
                renderDegrees();
                degreeInputEl?.focus();
            });
        });

        degreeListEl.querySelectorAll<HTMLElement>('.degree-btn-delete').forEach(btn => {
            btn.addEventListener('click', () => {
                const idx = parseInt(btn.dataset.index ?? '0', 10);
                degrees.splice(idx, 1);
                renderDegrees();
            });
        });
    }

    function addDegree() {
        if (!degreeInputEl) return;
        const val = degreeInputEl.value.trim();
        if (val && !degrees.includes(val)) {
            degrees.push(val);
            degreeInputEl.value = '';
            renderDegrees();
        }
    }

    degreeAddBtn?.addEventListener('click', addDegree);
    degreeInputEl?.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') { e.preventDefault(); addDegree(); }
    });

    // ── Profile form submit ─────────────────────────────────────────────
    if (settingsForm) {
        settingsForm.addEventListener("submit", async (event) => {
            event.preventDefault();

            const userId          = settingsForm.dataset.userId;
            const firstnameInput  = document.getElementById("firstname")    as HTMLInputElement;
            const lastnameInput   = document.getElementById("lastname")     as HTMLInputElement;
            const emailInput      = document.getElementById("email")        as HTMLInputElement;
            const jobtitleInput   = document.getElementById("jobtitle")     as HTMLInputElement;
            const fieldofworkInput= document.getElementById("fieldofwork")  as HTMLInputElement;
            const saveButton      = settingsForm.querySelector(".btn-save") as HTMLButtonElement;

            if (userId && firstnameInput && lastnameInput && emailInput) {
                const originalButtonContent = saveButton.innerHTML;
                saveButton.disabled = true;
                saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Enregistrement...';

                try {
                    await UserService.editUserFromApi({
                        id: userId,
                        firstname: firstnameInput.value,
                        lastname: lastnameInput.value,
                        email: emailInput.value,
                        jobtitle: jobtitleInput?.value || '',
                        fieldofwork: fieldofworkInput?.value || '',
                        degree: degrees,
                    });
                } catch (error) {
                    console.error("Mise à jour du profil échouée :", error);
                } finally {
                    saveButton.disabled = false;
                    saveButton.innerHTML = originalButtonContent;
                }
            } else {
                console.error("Champs ou ID utilisateur manquants");
            }
        });
    }

    // Password change handling
    const passwordChangeForm = document.getElementById("password-change-form") as HTMLFormElement;
    if (passwordChangeForm) {
        passwordChangeForm.addEventListener("submit", async (event) => {
            event.preventDefault();
            const currentPwd  = (document.getElementById("current-password") as HTMLInputElement).value;
            const newPwd      = (document.getElementById("new-password") as HTMLInputElement).value;
            const confirmPwd  = (document.getElementById("confirm-password") as HTMLInputElement).value;
            const errorEl     = document.getElementById("password-change-error") as HTMLElement;
            const successEl   = document.getElementById("password-change-success") as HTMLElement;
            const saveBtn     = passwordChangeForm.querySelector(".btn-save-password") as HTMLButtonElement;

            errorEl.style.display = 'none';
            successEl.style.display = 'none';

            if (newPwd !== confirmPwd) {
                errorEl.textContent = 'Les nouveaux mots de passe ne correspondent pas.';
                errorEl.style.display = 'block';
                return;
            }
            if (newPwd.length < 8) {
                errorEl.textContent = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
                errorEl.style.display = 'block';
                return;
            }

            const originalContent = saveBtn.innerHTML;
            saveBtn.disabled = true;
            saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Mise à jour...';

            try {
                const response = await fetch('/api/change-password', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
                    body: JSON.stringify({ currentPassword: currentPwd, newPassword: newPwd }),
                });
                const data = await response.json();
                if (response.ok && data.success) {
                    successEl.textContent = 'Mot de passe modifié avec succès !';
                    successEl.style.display = 'block';
                    passwordChangeForm.reset();
                } else {
                    errorEl.textContent = data.error || data.message || 'Une erreur est survenue.';
                    errorEl.style.display = 'block';
                }
            } catch {
                errorEl.textContent = 'Impossible de joindre le serveur.';
                errorEl.style.display = 'block';
            } finally {
                saveBtn.disabled = false;
                saveBtn.innerHTML = originalContent;
            }
        });
    }

    // Delete account handling
    const deleteAccountBtn = document.getElementById("btn-delete-account") as HTMLButtonElement;
    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener("click", async () => {
            const confirmed = confirm("Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible et supprimera toutes vos données.");
            if (confirmed) {
                const originalContent = deleteAccountBtn.innerHTML;
                deleteAccountBtn.disabled = true;
                deleteAccountBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Suppression...';

                try {
                    const response = await fetch('/api/delete/my-account', {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-Token': getCsrfToken(),
                        }
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Redirection vers logout qui gérera le nettoyage final et la redirection vers login
                        window.location.href = '/logout';
                    } else {
                        console.error("Erreur de suppression du compte", data);
                        alert(data.message || data.error || "Une erreur est survenue lors de la suppression de votre compte.");
                        deleteAccountBtn.disabled = false;
                        deleteAccountBtn.innerHTML = originalContent;
                    }
                } catch (error) {
                    console.error("Erreur:", error);
                    alert("Impossible de joindre le serveur pour supprimer le compte.");
                    deleteAccountBtn.disabled = false;
                    deleteAccountBtn.innerHTML = originalContent;
                }
            }
        });
    }

    // Tab handling
    const tabs = document.querySelectorAll('.settings-tabs .nav-link');
    const contents = document.querySelectorAll('.settings-content');

    if (tabs.length > 0 && contents.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener('click', (e) => {
                e.preventDefault();

                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                tab.classList.add('active');

                // Hide all contents
                contents.forEach(c => (c as HTMLElement).style.display = 'none');

                // Show target content
                const targetId = tab.getAttribute('data-target');
                if (targetId) {
                    const targetContent = document.getElementById(targetId);
                    if (targetContent) {
                        targetContent.style.display = 'block';
                    }
                }
            });
        });
    }

    // Modal handling
    const closeModal = document.getElementById('close-modal');
    const closeModalBtn = document.getElementById('close-modal-btn');
    const modal = document.getElementById('notificationModal');

    if (closeModal) {
        closeModal.addEventListener('click', function () {
            if (modal) modal.style.display = 'none';
        });
    }

    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function () {
            if (modal) modal.style.display = 'none';
        });
    }

    if (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === modal) {
                modal.style.display = 'none';
            }
        });
    }
});
