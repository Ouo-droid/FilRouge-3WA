import ProjectService from "./services/ProjectService";
import UserService from "./services/UserService";

interface Project {
    id: number;
    name: string;
    description?: string;
    beginDate?: string;
    theoricalDeadLine?: string;
    realDeadLine?: string;
    userId?: number;
    numSIRET?: string;
    projectManagerId?: number;
    user_firstname?: string;
    user_lastname?: string;
    user_email?: string;
    manager_firstname?: string;
    manager_lastname?: string;
    manager_email?: string;
}

interface User {
    id: number;
    firstname: string;
    lastname: string;
    email: string;
    role: string;
}

interface ApiResponse {
    success?: boolean;
    delete?: string | boolean;
    message?: string;
    error?: string;
}

// Sélection des éléments HTML
const projectList = document.getElementById("project-list") as HTMLElement;
const loadingElement = document.getElementById("loading") as HTMLElement;
const errorElement = document.getElementById("error") as HTMLElement;

async function loadProjects() {
    try {
        showLoading(true);
        hideError();

        const data = await ProjectService.loadProjectsFromApi();
        const projects: Project[] = data?.projects || [];

        if (projects.length === 0) {
            projectList.innerHTML = "<p class='no-projects'>Aucun projet trouvé</p>";
            return;
        }

        renderProjects(projects);

    } catch (error) {
        console.error("Erreur lors du chargement des projets :", error);
        showError("Erreur lors du chargement des projets");
    } finally {
        showLoading(false);
    }
}

function renderProjects(projects: Project[]) {
    projectList.innerHTML = "";

    projects.forEach((project) => {
        const projectItem = document.createElement("div");
        projectItem.classList.add("item-card", "project-card");
        projectItem.innerHTML = `
            <div class="card-header">
                <h3>${escapeHtml(project.name)}</h3>
                <div class="project-actions">
                    <button class="btn-sm btn-info btn-view" data-id="${project.id}">Voir détails</button>
                    <button class="btn-sm btn-warning btn-edit" data-id="${project.id}">Modifier</button>
                    <button class="btn-sm btn-danger btn-delete" data-id="${project.id}">Supprimer</button>
                </div>
            </div>
            <div class="project-info">
                <p class="description">${escapeHtml(project.description || "Pas de description")}</p>
                ${project.theoricalDeadLine ? `<p class="deadline">Échéance théorique: ${formatDate(project.theoricalDeadLine)}</p>` : ''}
                ${project.realDeadLine ? `<p class="real-deadline">Échéance réelle: ${formatDate(project.realDeadLine)}</p>` : ''}
                ${project.user_firstname ? `<p class="assigned-user">Utilisateur assigné: ${project.user_firstname} ${project.user_lastname}</p>` : ''}
                ${project.manager_firstname ? `<p class="project-manager">Chef de projet: ${project.manager_firstname} ${project.manager_lastname}</p>` : ''}
            </div>
        `;
        projectList.appendChild(projectItem);

        // Ajout des event listeners
        const viewBtn = projectItem.querySelector(".btn-view") as HTMLButtonElement;
        const editBtn = projectItem.querySelector(".btn-edit") as HTMLButtonElement;
        const deleteBtn = projectItem.querySelector(".btn-delete") as HTMLButtonElement;

        viewBtn.addEventListener("click", () => viewProjectDetails(project.id));
        editBtn.addEventListener("click", () => editProject(project));
        deleteBtn.addEventListener("click", () => deleteProject(project.id, project.name));
    });
}

async function viewProjectDetails(projectId: number) {
    try {
        showLoading(true);
        const data = await ProjectService.loadProjectFromApi(String(projectId));

        if (!data || !data.project) {
            showError("Impossible de récupérer les détails du projet");
            return;
        }

        // La méthode showModalProject est maintenant appelée dans ProjectService.loadProjectFromApi

    } catch (error) {
        console.error("Erreur lors de la récupération du projet :", error);
        showError("Erreur lors de la récupération du projet");
    } finally {
        showLoading(false);
    }
}

async function deleteProject(projectId: number, projectName: string) {
    if (!confirm(`Êtes-vous sûr de vouloir supprimer le projet "${projectName}" ?`)) {
        return;
    }

    try {
        showLoading(true);
        const result = await ProjectService.deleteProjectFromApi(String(projectId)) as ApiResponse;

        if (result && (result.success === true || result.delete === true)) {
            alert("Projet supprimé avec succès");

            if (typeof loadProjects === 'function') {
                await loadProjects();
            } else {
                // Fallback: rechargement de la page
                window.location.reload();
            }
        } else {
            const errorMessage = result?.error || result?.message || "Erreur lors de la suppression du projet";
            console.error("❌ Échec de suppression:", errorMessage);
            showError(errorMessage);
            alert(`Erreur: ${errorMessage}`);
        }

    } catch (error) {
        console.error("💥 Erreur lors de la suppression:", error);
        showError("Erreur lors de la suppression du projet");
        alert("Erreur lors de la suppression du projet");
    } finally {
        showLoading(false);
    }
}



async function editProject(project: Project) {
    // Créer un formulaire de modification
    const editForm = await createEditForm(project);
    document.body.appendChild(editForm);
}

async function createEditForm(project: Project): Promise<HTMLElement> {
    const formOverlay = document.createElement("div");
    formOverlay.className = "form-overlay";
    formOverlay.innerHTML = `
        <div class="edit-form">
            <div class="form-header">
                <h3>Modifier le projet</h3>
                <button class="btn-close" type="button">×</button>
            </div>
            <form id="project-edit-form">
                <div class="form-group">
                    <label for="edit-name">Nom du projet *</label>
                    <input type="text" id="edit-name" name="name" value="${escapeHtml(project.name)}" required>
                </div>
                <div class="form-group">
                    <label for="edit-description">Description</label>
                    <textarea id="edit-description" name="description" rows="3">${escapeHtml(project.description || "")}</textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-begin-date">Date de début</label>
                        <input type="date" id="edit-begin-date" name="beginDate" value="${formatDateForInput(project.beginDate)}">
                    </div>
                    <div class="form-group">
                        <label for="edit-theoretical-deadline">Échéance théorique</label>
                        <input type="date" id="edit-theoretical-deadline" name="theoricalDeadLine" value="${formatDateForInput(project.theoricalDeadLine)}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-real-deadline">Échéance réelle</label>
                        <input type="date" id="edit-real-deadline" name="realDeadLine" value="${formatDateForInput(project.realDeadLine)}">
                    </div>
                    <div class="form-group">
                        <label for="edit-siret">Numéro SIRET</label>
                        <input type="text" id="edit-siret" name="numSIRET" value="${project.numSIRET || ""}" pattern="[0-9]{14}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="edit-user">Utilisateur assigné</label>
                        <select id="edit-user" name="userId">
                            <option value="">Sélectionner un utilisateur</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="edit-project-manager">Chef de projet</label>
                        <select id="edit-project-manager" name="projectManagerId">
                            <option value="">Sélectionner un chef de projet</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel">Annuler</button>
                    <button type="submit" class="btn-save">Sauvegarder</button>
                </div>
            </form>
        </div>
    `;

    // Charger les utilisateurs et remplir les listes déroulantes
    await loadUsersIntoSelects(formOverlay, project);

    const form = formOverlay.querySelector("#project-edit-form") as HTMLFormElement;
    const closeBtn = formOverlay.querySelector(".btn-close") as HTMLButtonElement;
    const cancelBtn = formOverlay.querySelector(".btn-cancel") as HTMLButtonElement;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        await handleEditSubmit(project.id, formOverlay);
    });

    [closeBtn, cancelBtn].forEach(btn => {
        btn.addEventListener("click", () => {
            document.body.removeChild(formOverlay);
        });
    });

    // Fermer en cliquant sur l'overlay
    formOverlay.addEventListener("click", (e) => {
        if (e.target === formOverlay) {
            document.body.removeChild(formOverlay);
        }
    });

    return formOverlay;
}

async function handleEditSubmit(projectId: number, formOverlay: HTMLElement) {
    const form = formOverlay.querySelector("#project-edit-form") as HTMLFormElement;
    const formData = new FormData(form);

    const updatedProject: Project = {
        id: projectId,
        name: formData.get("name") as string,
        description: (formData.get("description") as string) || undefined,
        beginDate: (formData.get("beginDate") as string) || undefined,
        theoricalDeadLine: (formData.get("theoricalDeadLine") as string) || undefined,
        realDeadLine: (formData.get("realDeadLine") as string) || undefined,
        numSIRET: (formData.get("numSIRET") as string) || undefined,
        userId: (formData.get("userId") as string) ? parseInt(formData.get("userId") as string) : undefined,
        projectManagerId: (formData.get("projectManagerId") as string) ? parseInt(formData.get("projectManagerId") as string) : undefined,
    };

    try {
        showLoading(true);
        const result = await ProjectService.editProjectFromApi(updatedProject);

        if (result && result.success) {
            document.body.removeChild(formOverlay);
            loadProjects(); // Recharger la liste
        } else {
            const errorMessage = result?.error || "Erreur lors de la modification du projet";
            showError(errorMessage);
        }
    } catch (error) {
        console.error("Erreur lors de la modification :", error);
        showError("Erreur lors de la modification du projet");
    } finally {
        showLoading(false);
    }
}

// Fonctions utilitaires
function showLoading(show: boolean) {
    if (loadingElement) {
        loadingElement.style.display = show ? "block" : "none";
    }
}

function showError(message: string) {
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = "block";
        setTimeout(() => hideError(), 5000);
    }
}

function hideError() {
    if (errorElement) {
        errorElement.style.display = "none";
    }
}

function escapeHtml(text: string): string {
    if (!text) return "";
    const div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}

function formatDate(dateString: string): string {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString("fr-FR");
    } catch {
        return dateString;
    }
}

function formatDateForInput(dateString?: string): string {
    if (!dateString) return "";
    try {
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    } catch {
        return "";
    }
}

async function createNewProject() {
    const createForm = await createCreateForm();
    document.body.appendChild(createForm);
}

async function createCreateForm(): Promise<HTMLElement> {
    const formOverlay = document.createElement("div");
    formOverlay.className = "form-overlay";
    formOverlay.innerHTML = `
        <div class="edit-form">
            <div class="form-header">
                <h3>Créer un nouveau projet</h3>
                <button class="btn-close" type="button">×</button>
            </div>
            <form id="project-create-form">
                <div class="form-group">
                    <label for="create-name">Nom du projet *</label>
                    <input type="text" id="create-name" name="name" required>
                </div>
                <div class="form-group">
                    <label for="create-description">Description</label>
                    <textarea id="create-description" name="description" rows="3"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="create-begin-date">Date de début</label>
                        <input type="date" id="create-begin-date" name="beginDate">
                    </div>
                    <div class="form-group">
                        <label for="create-theoretical-deadline">Échéance théorique</label>
                        <input type="date" id="create-theoretical-deadline" name="theoricalDeadLine">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="create-real-deadline">Échéance réelle</label>
                        <input type="date" id="create-real-deadline" name="realDeadLine">
                    </div>
                    <div class="form-group">
                        <label for="create-siret">Numéro SIRET</label>
                        <input type="text" id="create-siret" name="numSIRET" pattern="[0-9]{14}">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="create-user">Utilisateur assigné</label>
                        <select id="create-user" name="userId">
                            <option value="">Sélectionner un utilisateur</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="create-project-manager">Chef de projet</label>
                        <select id="create-project-manager" name="projectManagerId">
                            <option value="">Sélectionner un chef de projet</option>
                        </select>
                    </div>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel">Annuler</button>
                    <button type="submit" class="btn-save">Créer</button>
                </div>
            </form>
        </div>
    `;

    // Charger les utilisateurs et remplir les listes déroulantes
    await loadUsersIntoSelects(formOverlay);

    const form = formOverlay.querySelector("#project-create-form") as HTMLFormElement;
    const closeBtn = formOverlay.querySelector(".btn-close") as HTMLButtonElement;
    const cancelBtn = formOverlay.querySelector(".btn-cancel") as HTMLButtonElement;

    form.addEventListener("submit", async (e) => {
        e.preventDefault();
        await handleCreateSubmit(formOverlay);
    });

    [closeBtn, cancelBtn].forEach(btn => {
        btn.addEventListener("click", () => {
            document.body.removeChild(formOverlay);
        });
    });

    // Fermer en cliquant sur l'overlay
    formOverlay.addEventListener("click", (e) => {
        if (e.target === formOverlay) {
            document.body.removeChild(formOverlay);
        }
    });

    return formOverlay;
}

async function handleCreateSubmit(formOverlay: HTMLElement) {
    const form = formOverlay.querySelector("#project-create-form") as HTMLFormElement;
    const formData = new FormData(form);

    const newProject = {
        name: formData.get("name") as string,
        description: (formData.get("description") as string) || undefined,
        beginDate: (formData.get("beginDate") as string) || undefined,
        theoricalDeadLine: (formData.get("theoricalDeadLine") as string) || undefined,
        realDeadLine: (formData.get("realDeadLine") as string) || undefined,
        numSIRET: (formData.get("numSIRET") as string) || undefined,
        userId: (formData.get("userId") as string) ? parseInt(formData.get("userId") as string) : undefined,
        projectManagerId: (formData.get("projectManagerId") as string) ? parseInt(formData.get("projectManagerId") as string) : undefined,
    };

    try {
        showLoading(true);
        await ProjectService.addProjectFromApi(newProject);

        // Si on arrive ici, c'est que l'ajout s'est bien passé
        document.body.removeChild(formOverlay);
        loadProjects(); // Recharger la liste
    } catch (error) {
        console.error("Erreur lors de la création :", error);
        showError("Erreur lors de la création du projet");
    } finally {
        showLoading(false);
    }
}

document.addEventListener("DOMContentLoaded", () => {
    const createBtn = document.getElementById("create-project-btn");
    if (createBtn) {
        createBtn.addEventListener("click", createNewProject);
    }

    loadProjects();
});

// Fonction pour charger les utilisateurs dans les listes déroulantes
async function loadUsersIntoSelects(formOverlay: HTMLElement, project?: Project): Promise<void> {
    try {
        const data = await UserService.loadUsersFromApi();
        const users: User[] = data?.users || [];

        // Remplir la liste des utilisateurs assignés
        const userSelect = formOverlay.querySelector('#edit-user, #create-user') as HTMLSelectElement;
        if (userSelect) {
            users.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id.toString();
                option.textContent = `${user.firstname} ${user.lastname} (${user.email})`;
                if (project && project.userId === user.id) {
                    option.selected = true;
                }
                userSelect.appendChild(option);
            });
        }

        // Remplir la liste des chefs de projet
        const managerSelect = formOverlay.querySelector('#edit-project-manager, #create-project-manager') as HTMLSelectElement;
        if (managerSelect) {
            users.forEach(user => {
                const option = document.createElement('option');
                option.value = user.id.toString();
                option.textContent = `${user.firstname} ${user.lastname} (${user.email})`;
                if (project && project.projectManagerId === user.id) {
                    option.selected = true;
                }
                managerSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error("Erreur lors du chargement des utilisateurs :", error);
        showError("Erreur lors du chargement des utilisateurs");
    }
}

export { loadProjects, viewProjectDetails, deleteProject, editProject, createNewProject };