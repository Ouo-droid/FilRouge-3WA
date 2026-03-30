import UserService from "./services/UserService";
import { User } from "./interfaces/UserInterface";

const pageRoot = document.getElementById("dynamical-user");
const isUsersPage = !!pageRoot;

// ── Éléments du panneau droit ────────────────────────────────────────────────
const formAdd        = document.getElementById("form-add-user") as HTMLFormElement;
const panelIcon      = document.getElementById("users-panel-icon") as HTMLElement;
const panelTitleText = document.getElementById("users-panel-title-text") as HTMLElement;
const submitBtn      = document.getElementById("users-form-submit") as HTMLButtonElement;
const cancelBtn      = document.getElementById("users-form-cancel") as HTMLButtonElement;
const passwordGroup  = document.getElementById("password-group") as HTMLElement;
const passwordLabel  = document.getElementById("password-label") as HTMLLabelElement;
const inputFirstname = document.getElementById("firstname") as HTMLInputElement;
const inputLastname  = document.getElementById("lastname") as HTMLInputElement;
const inputEmail     = document.getElementById("email") as HTMLInputElement;
const inputPassword  = document.getElementById("password") as HTMLInputElement;
const inputRoleSelect= document.getElementById("role-select") as HTMLSelectElement;

// ── Chargement des rôles dans le select ──────────────────────────────────────
async function loadRoles(): Promise<void> {
    if (!inputRoleSelect) return;
    try {
        const res  = await fetch('/api/roles');
        const data = await res.json();
        const roles: Array<{ id: string; name: string }> = data?.data ?? [];
        inputRoleSelect.innerHTML = '<option value="">Sélectionner un rôle *</option>';
        roles.forEach(role => {
            const opt = document.createElement('option');
            opt.value = role.id;
            opt.textContent = role.name;
            inputRoleSelect.appendChild(opt);
        });
    } catch {
        inputRoleSelect.innerHTML = '<option value="">Erreur de chargement</option>';
    }
}
if (isUsersPage) loadRoles();

// ── Éléments du modal "Voir" ─────────────────────────────────────────────────
const detailOverlay        = document.getElementById("userDetailOverlay") as HTMLElement;
const detailAvatar         = document.getElementById("detailAvatar") as HTMLElement;
const detailName           = document.getElementById("detailName") as HTMLElement;
const detailEmailText      = document.getElementById("detailEmailText") as HTMLElement;
const detailRoleBadge      = document.getElementById("detailRoleBadge") as HTMLElement;
const detailJobtitleEl     = document.getElementById("detailJobtitle") as HTMLElement;
const detailJobtitleText   = document.getElementById("detailJobtitleText") as HTMLElement;
const detailFieldofworkEl  = document.getElementById("detailFieldofwork") as HTMLElement;
const detailFieldofworkText= document.getElementById("detailFieldofworkText") as HTMLElement;
const detailDegreeEl       = document.getElementById("detailDegree") as HTMLElement;
const detailDegreeList     = document.getElementById("detailDegreeList") as HTMLElement;

// ── Helpers ──────────────────────────────────────────────────────────────────
function roleBadgeClass(role: string): string {
    const r = role.toLowerCase();
    if (r.includes('admin'))                                               return 'role-admin';
    if (r.includes('pdg'))                                                 return 'role-pdg';
    if (r.includes('cdp') || r.includes('chef') || r.includes('project')) return 'role-cdp';
    if (r.includes('dev'))                                                 return 'role-dev';
    if (r.includes('design'))                                              return 'role-designer';
    return 'role-default';
}

// ── Mode du panneau droit ────────────────────────────────────────────────────
function setAddMode() {
    formAdd.dataset.mode = 'add';
    formAdd.dataset.editUserId = '';
    formAdd.reset();
    if (inputRoleSelect) inputRoleSelect.value = '';

    panelIcon.className = 'fas fa-user-plus';
    panelTitleText.textContent = 'Ajouter un utilisateur';

    submitBtn.innerHTML = '<i class="fas fa-plus" aria-hidden="true"></i> Ajouter l\'utilisateur';
    cancelBtn.style.display = 'none';

    inputPassword.required = true;
    passwordLabel.textContent = 'Mot de passe *';
    inputPassword.placeholder = '••••••••';
}

function setEditMode(userId: string, firstname: string, lastname: string, email: string, roleId?: string) {
    formAdd.dataset.mode = 'edit';
    formAdd.dataset.editUserId = userId;

    panelIcon.className = 'fas fa-user-edit';
    panelTitleText.textContent = 'Modifier l\'utilisateur';

    inputFirstname.value = firstname;
    inputLastname.value  = lastname;
    inputEmail.value     = email;
    inputPassword.value  = '';
    if (inputRoleSelect && roleId) inputRoleSelect.value = roleId;

    inputPassword.required = false;
    passwordLabel.textContent = 'Nouveau mot de passe (optionnel)';
    inputPassword.placeholder = 'Laisser vide pour ne pas changer';

    submitBtn.innerHTML = '<i class="fas fa-save" aria-hidden="true"></i> Enregistrer les modifications';
    cancelBtn.style.display = 'block';

    document.getElementById('users-form-panel')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    inputFirstname.focus();
}

// ── Bouton Annuler ───────────────────────────────────────────────────────────
if (isUsersPage && cancelBtn) {
    cancelBtn.addEventListener('click', () => setAddMode());
}

// ── Soumission du formulaire (ajout OU modification) ─────────────────────────
let isSubmitting = false;
const FORM_LISTENER_ATTACHED = Symbol('formListenerAttached');

function manageAdd(form: HTMLFormElement) {
    if ((form as any)[FORM_LISTENER_ATTACHED]) return;
    (form as any)[FORM_LISTENER_ATTACHED] = true;

    const originalContent = submitBtn ? submitBtn.innerHTML : '';

    form.addEventListener("submit", (event: Event) => {
        event.preventDefault();
        if (isSubmitting) return;
        isSubmitting = true;

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
        }

        const mode   = form.dataset.mode;
        const userId = form.dataset.editUserId;

        const firstname = inputFirstname.value;
        const lastname  = inputLastname.value;
        const email     = inputEmail.value;
        const password  = inputPassword.value;
        const roleId    = inputRoleSelect?.value || undefined;

        const finish = () => {
            isSubmitting = false;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = mode === 'edit'
                    ? '<i class="fas fa-save" aria-hidden="true"></i> Enregistrer les modifications'
                    : originalContent;
            }
        };

        if (mode === 'edit' && userId) {
            UserService.editUserFromApi({ id: userId, firstname, lastname, email, roleId })
                .then(() => setAddMode())
                .catch((e) => console.error("Erreur modification:", e))
                .finally(finish);
        } else {
            UserService.addUserFromApi({ firstname, lastname, email, password, roleId })
                .then(() => form.reset())
                .catch((e) => console.error("Erreur ajout:", e))
                .finally(finish);
        }
    });
}

// ── Construction des cartes ──────────────────────────────────────────────────
function buildCard(user: User): HTMLElement {
    const card = document.createElement("div");
    card.className = 'user-card';
    card.setAttribute("data-userid",        String(user.id));
    card.setAttribute("data-userfirstname", user.firstname);
    card.setAttribute("data-userlastname",  user.lastname);
    card.setAttribute("data-email",         user.email);
    card.setAttribute("data-roleid",        user.roleId ?? '');
    card.setAttribute("data-name",          `${user.firstname} ${user.lastname}`.toLowerCase());

    const initials  = (user.firstname.charAt(0) + user.lastname.charAt(0)).toUpperCase();
    const fullName  = `${user.firstname} ${user.lastname}`;
    const roleName  = (user as any).role_name ?? '';
    const roleClass = roleName ? roleBadgeClass(roleName) : '';

    card.innerHTML = `
        <div class="user-card-avatar" aria-hidden="true">${initials}</div>
        <div class="user-card-name" title="${fullName}">${fullName}</div>
        ${roleName ? `<span class="member-role-badge ${roleClass}">${roleName}</span>` : ''}
        <div class="user-card-email" title="${user.email}">
            <i class="fas fa-envelope" aria-hidden="true"></i>${user.email}
        </div>
        <div class="user-card-actions">
            <button class="user-icon-btn btn-view show-btn"
                    aria-label="Voir ${fullName}">
                <i class="fas fa-eye" aria-hidden="true"></i>
            </button>
            <button class="user-icon-btn btn-edit edit-btn"
                    aria-label="Modifier ${fullName}">
                <i class="fas fa-edit" aria-hidden="true"></i>
            </button>
            <button class="user-icon-btn btn-delete delete-btn"
                    aria-label="Supprimer ${fullName}">
                <i class="fas fa-trash" aria-hidden="true"></i>
            </button>
        </div>
    `;
    return card;
}

// ── Listeners ────────────────────────────────────────────────────────────────
function manageDelete(btns: NodeListOf<Element>) {
    btns.forEach((btn) => {
        btn.addEventListener("click", (event: Event) => {
            const card = (event.target as HTMLElement).closest('.user-card') as HTMLElement;
            if (!card) return;
            const userId = card.getAttribute("data-userid");
            if (!userId) return;

            card.style.opacity = "0.4";
            card.style.pointerEvents = "none";

            UserService.deleteUserFromApi(userId)
                .then((data) => {
                    if ("delete" in data && (data as any).delete == "true") {
                        card.remove();
                    } else {
                        card.style.opacity = "";
                        card.style.pointerEvents = "";
                    }
                })
                .catch((error) => {
                    console.error("Erreur suppression:", error);
                    card.style.opacity = "";
                    card.style.pointerEvents = "";
                });
        });
    });
}

function manageEdit(btns: NodeListOf<Element>) {
    btns.forEach((btn) => {
        btn.addEventListener("click", (event: Event) => {
            const card = (event.target as HTMLElement).closest('.user-card') as HTMLElement;
            if (!card) return;

            const userId    = card.getAttribute("data-userid")        || '';
            const firstname = card.getAttribute("data-userfirstname") || '';
            const lastname  = card.getAttribute("data-userlastname")  || '';
            const email     = card.getAttribute("data-email")         || '';
            const roleId    = card.getAttribute("data-roleid")        || undefined;

            setEditMode(userId, firstname, lastname, email, roleId);
        });
    });
}

function manageShow(btns: NodeListOf<Element>) {
    btns.forEach((btn) => {
        btn.addEventListener('click', (event: Event) => {
            const card = (event.target as HTMLElement).closest('.user-card') as HTMLElement;
            if (!card) return;

            const firstname = card.getAttribute("data-userfirstname") || '';
            const lastname  = card.getAttribute("data-userlastname")  || '';
            const email     = card.getAttribute("data-email")         || '';

            const initials = (firstname.charAt(0) + lastname.charAt(0)).toUpperCase();

            detailAvatar.textContent    = initials;
            detailName.textContent      = `${firstname} ${lastname}`;
            detailEmailText.textContent = email;
            detailRoleBadge.innerHTML   = '';
            if (detailJobtitleEl)    detailJobtitleEl.style.display   = 'none';
            if (detailFieldofworkEl) detailFieldofworkEl.style.display = 'none';
            if (detailDegreeEl)      detailDegreeEl.style.display      = 'none';

            // Charger les infos complètes depuis l'API
            const userId = card.getAttribute("data-userid");
            if (userId) {
                fetch(`/api/user/${userId}`, { method: "GET" })
                    .then(r => r.ok ? r.json() : null)
                    .then(data => {
                        if (!data?.user) return;
                        const u = data.user;

                        // Rôle
                        const roleName = u.roleName ?? (u.roles?.[0]?.name ?? '');
                        if (roleName) {
                            detailRoleBadge.innerHTML =
                                `<span class="member-role-badge ${roleBadgeClass(roleName)}">${roleName}</span>`;
                        }

                        // Jobtitle
                        if (u.jobtitle && detailJobtitleEl) {
                            detailJobtitleText.textContent = u.jobtitle;
                            detailJobtitleEl.style.display = 'block';
                        }
                        // Fieldofwork
                        if (u.fieldofwork && detailFieldofworkEl) {
                            detailFieldofworkText.textContent = u.fieldofwork;
                            detailFieldofworkEl.style.display = 'block';
                        }
                        // Degrees
                        if (u.degree?.length && detailDegreeEl) {
                            detailDegreeList.innerHTML = u.degree
                                .map((d: string) => `<span class="badge bg-light text-dark border">${d}</span>`)
                                .join('');
                            detailDegreeEl.style.display = 'block';
                        }
                    })
                    .catch(() => {});
            }

            detailOverlay.style.display = 'flex';
        });
    });
}

// ── Chargement initial de la liste ───────────────────────────────────────────
const usersList = document.querySelector(".users-list") as HTMLElement;

if (isUsersPage && usersList) {
    UserService.loadUsersFromApi()
        .then((data) => {
            const users: User[] = data?.data ?? [];

            if (users.length > 0) {
                usersList.innerHTML = '';
                users.forEach(user => usersList.appendChild(buildCard(user)));

                manageDelete(usersList.querySelectorAll(".delete-btn"));
                manageEdit(usersList.querySelectorAll(".edit-btn"));
                manageShow(usersList.querySelectorAll(".show-btn"));
            } else {
                usersList.innerHTML = `
                    <div class="users-empty">
                        <i class="fas fa-users-slash" aria-hidden="true"></i>
                        <p>Aucun utilisateur trouvé. Ajoutez-en un pour commencer !</p>
                    </div>
                `;
            }
        })
        .catch((e) => console.error("Erreur chargement utilisateurs:", e));
}

if (isUsersPage && formAdd) {
    manageAdd(formAdd);
}
