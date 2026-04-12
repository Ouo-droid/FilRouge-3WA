import UserService from "../services/UserService";
import { escapeHtml, formatDateForInput } from "../utils/helpers";
import { getCsrfToken } from "../services/Api";

interface ProjectData {
    id: number;
    name: string;
    description: string;
    beginDate: string;
    theoreticalDeadline: string;
    theoricalDeadLine?: string; // alias rétrocompat
    realDeadline: string;
    realDeadLine?: string; // alias rétrocompat
    clientId: string;
    projectManagerId: number;
    stateId: string;
}

interface ClientData {
    siret: string;
    companyname: string;
    companyName?: string;
    workfield?: string;
    contactfirstname?: string;
    contactlastname?: string;
}

interface StateData {
    id: string;
    name: string;
}

document.addEventListener('DOMContentLoaded', function () {
    // Only run if we are on the projects page
    if (!document.querySelector('.projects-page')) {
        return;
    }

    // --- Filter tabs ---
    const filterBtns = document.querySelectorAll<HTMLButtonElement>('.task-filters .filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            filterBtns.forEach(b => { b.classList.remove('active'); b.setAttribute('aria-pressed', 'false'); });
            this.classList.add('active');
            this.setAttribute('aria-pressed', 'true');

            const filter = this.getAttribute('data-filter');
            document.querySelectorAll<HTMLElement>('#project-list .project-card').forEach(card => {
                card.style.display = (filter === 'all' || card.getAttribute('data-state-id') === filter) ? '' : 'none';
            });
        });
    });

    // --- Search ---
    const searchInput = document.getElementById('project-search') as HTMLInputElement | null;
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const query = this.value.trim().toLowerCase();
            document.querySelectorAll<HTMLElement>('#project-list .project-card').forEach(card => {
                const name = card.getAttribute('data-project-name') ?? '';
                card.style.display = (query === '' || name.includes(query)) ? '' : 'none';
            });
        });
    }

    // Delegation pour les boutons dynamiques
    document.addEventListener('click', function (e) {
        const target = e.target as HTMLElement;

        // Trigger nouveau projet
        if (target.matches('.create-project-trigger')) {
            const btn = document.getElementById('create-project-btn');
            if (btn) btn.click();
        }

        // Toggle dropdown
        const toggleBtn = target.closest('.toggle-dropdown-btn');
        if (toggleBtn) {
            toggleDropdown(e, toggleBtn as HTMLElement);
        }

        // Edit project
        const editBtn = target.closest('.edit-project-btn');
        if (editBtn) {
            e.preventDefault();
            const projectId = editBtn.getAttribute('data-project-id');
            if (projectId) editProject(projectId);
        }

        // View project (Details)
        const viewBtn = target.closest('.view-project-btn');
        if (viewBtn) {
            // Let the link work normally if it's an <a> tag navigating to /project/ID
            // But if we want to use the modal:
            // e.preventDefault();
            // const href = viewBtn.getAttribute('href');
            // const projectId = href?.split('/').pop();
            // if (projectId) Api.loadProjectFromApi(projectId);
        }
        // Note: The HTML has <a href="/project/id"> which works without JS.
        // But the inline script had a viewProject function. It wasn't attached to the 'See Project' button in the HTML provided,
        // but let's check if there is a button calling viewProject.
        // In the HTML: <a href="/project/<?= $project['id'] ?>">...</a>
        // So viewProject might be dead code or for a different view mode.
        // However, the inline script defined `viewProject` and checked `Api`.

        // Delete project
        const deleteBtn = target.closest('.delete-project-btn');
        if (deleteBtn) {
            e.preventDefault();
            const projectId = deleteBtn.getAttribute('data-project-id');
            const projectName = deleteBtn.getAttribute('data-project-name')
                ?? deleteBtn.closest('.project-card')?.getAttribute('data-project-name')
                ?? 'ce projet';
            if (projectId) {
                confirmDeleteProject(projectId, projectName);
            }
        }
    });

    // Fermeture des modaux
    const closeButtons = document.querySelectorAll('.close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function (this: HTMLElement) {
            const modal = this.closest('.modal') as HTMLElement;
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });

    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function (this: HTMLElement, e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });

    // Bouton créer projet
    const createBtn = document.getElementById('create-project-btn');
    if (createBtn) {
        createBtn.addEventListener('click', function () {
            createNewProject();
        });
    }

    // Close dropdowns when clicking outside
    document.addEventListener('click', function (event) {
        const target = event.target as HTMLElement;
        if (!target.closest('.project-dropdown')) {
            document.querySelectorAll('.dropdown-menu-custom').forEach(d => {
                d.classList.remove('show');
            });
        }
    });
});

function showProjectToast(message: string, type: 'success' | 'error' = 'success') {
    const toast = document.createElement('div');
    toast.className = `project-toast project-toast--${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'folder-open' : 'exclamation-triangle'}"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('project-toast--visible'));

    setTimeout(() => {
        toast.classList.remove('project-toast--visible');
        toast.addEventListener('transitionend', () => toast.remove());
    }, 3500);
}

function confirmDeleteProject(projectId: string, projectName: string) {
    // Supprimer une éventuelle modale déjà ouverte
    document.getElementById('project-confirm-modal')?.remove();

    const modal = document.createElement('div');
    modal.id = 'project-confirm-modal';
    modal.className = 'project-confirm-overlay';
    modal.innerHTML = `
        <div class="project-confirm-box">
            <div class="project-confirm-icon">
                <i class="fas fa-trash-alt"></i>
            </div>
            <h3>Supprimer le projet</h3>
            <p>Êtes-vous sûr de vouloir supprimer <strong>${projectName.trim()}</strong> ? Cette action est irréversible.</p>
            <div class="project-confirm-actions">
                <button class="project-confirm-cancel">Annuler</button>
                <button class="project-confirm-delete">Oui, supprimer</button>
            </div>
        </div>
    `;

    document.body.appendChild(modal);
    requestAnimationFrame(() => modal.classList.add('project-confirm-overlay--visible'));

    const closeModal = () => {
        modal.classList.remove('project-confirm-overlay--visible');
        modal.addEventListener('transitionend', () => modal.remove(), { once: true });
    };

    modal.querySelector('.project-confirm-cancel')!.addEventListener('click', closeModal);
    modal.addEventListener('click', (e) => { if (e.target === modal) closeModal(); });
    modal.querySelector('.project-confirm-delete')!.addEventListener('click', () => {
        closeModal();
        deleteProject(projectId);
    });
}

async function deleteProject(projectId: string) {
    try {
        const response = await fetch(`/api/delete/project/${projectId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-Token': getCsrfToken() },
        });

        const result = await response.json();

        if (result.success || result.delete) {
            showProjectToast('Projet supprimé avec succès !', 'success');
            location.reload();
        } else {
            showProjectToast(result.error || 'Erreur lors de la suppression du projet', 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showProjectToast('Erreur lors de la suppression du projet', 'error');
    }
}

export function editProject(projectId: string) {
    fetch(`/api/project/${projectId}`)
        .then(response => response.json())
        .then(data => {
            const project = data.project ?? data.data?.project;
            if (project) {
                showEditForm(project);
            } else {
                showProjectToast('Erreur lors de la récupération du projet', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showProjectToast('Erreur lors de la récupération du projet', 'error');
        });
}

async function createNewProject() {
    const formOverlay = document.createElement('div');
    formOverlay.className = 'form-overlay';
    formOverlay.innerHTML = `
        <div class="edit-form">
            <div class="form-header">
                <h3>Créer un nouveau projet</h3>
                <button class="btn-close" type="button">×</button>
            </div>
            <form id="project-create-form">
                <div class="form-content">

                    <!-- Section Informations -->
                    <div class="form-section form-section--info">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-folder"></i></span>
                            <span class="form-section__label">Informations</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-group">
                                <label for="create-name">Nom du projet *</label>
                                <input type="text" id="create-name" name="name" required>
                            </div>
                            <div class="form-group">
                                <label for="create-description">Description</label>
                                <textarea id="create-description" name="description" rows="3"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Section Planification -->
                    <div class="form-section form-section--planning">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-calendar-alt"></i></span>
                            <span class="form-section__label">Planification</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="create-begin-date">Date de début</label>
                                    <input type="date" id="create-begin-date" name="beginDate">
                                </div>
                                <div class="form-group">
                                    <label for="create-theoretical-deadline">Échéance théorique *</label>
                                    <input type="date" id="create-theoretical-deadline" name="theoricalDeadLine" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="create-real-deadline">Échéance réelle</label>
                                    <input type="date" id="create-real-deadline" name="realDeadLine">
                                </div>
                                <div class="form-group">
                                    <label for="create-state">État du projet</label>
                                    <select id="create-state" name="stateId">
                                        <option value="">Sélectionner un état</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Équipe -->
                    <div class="form-section form-section--team">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-users"></i></span>
                            <span class="form-section__label">Équipe & Client</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-group">
                                <label for="create-client-search">Client</label>
                                <div class="client-search-container">
                                    <input type="text" id="create-client-search" placeholder="Rechercher un client par nom..." autocomplete="off">
                                    <input type="hidden" id="create-client-id" name="clientId">
                                    <div class="client-search-results" id="create-client-results"></div>
                                    <div class="client-selected" id="create-client-selected" style="display:none;">
                                        <span id="create-client-selected-name"></span>
                                        <button type="button" class="btn-remove-client" title="Retirer le client">&times;</button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="create-project-manager">Chef de projet</label>
                                <select id="create-project-manager" name="projectManagerId">
                                    <option value="">Sélectionner un chef de projet</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel">Annuler</button>
                    <button type="submit" class="btn-save">Créer</button>
                </div>
            </form>
        </div>
    `;

    document.body.appendChild(formOverlay);

    await loadUsersIntoSelects(formOverlay);
    await loadStatesIntoSelect(formOverlay);
    setupClientSearch(formOverlay, 'create');

    const form = formOverlay.querySelector('#project-create-form');
    const closeBtn = formOverlay.querySelector('.btn-close');
    const cancelBtn = formOverlay.querySelector('.btn-cancel');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await handleCreateSubmit(formOverlay);
        });
    }

    [closeBtn, cancelBtn].forEach(btn => {
        if (btn) {
            btn.addEventListener('click', () => {
                document.body.removeChild(formOverlay);
            });
        }
    });

}

async function showEditForm(project: ProjectData) {
    const formOverlay = document.createElement('div');
    formOverlay.className = 'form-overlay';
    formOverlay.innerHTML = `
        <div class="edit-form">
            <div class="form-header">
                <h3>Modifier le projet</h3>
                <button class="btn-close" type="button">×</button>
            </div>
            <form id="project-edit-form">
                <div class="form-content">

                    <!-- Section Informations -->
                    <div class="form-section form-section--info">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-folder"></i></span>
                            <span class="form-section__label">Informations</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-group">
                                <label for="edit-name">Nom du projet *</label>
                                <input type="text" id="edit-name" name="name" value="${escapeHtml(project.name)}" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-description">Description</label>
                                <textarea id="edit-description" name="description" rows="3">${escapeHtml(project.description || '')}</textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Section Planification -->
                    <div class="form-section form-section--planning">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-calendar-alt"></i></span>
                            <span class="form-section__label">Planification</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-begin-date">Date de début</label>
                                    <input type="date" id="edit-begin-date" name="beginDate" value="${formatDateForInput(project.beginDate)}">
                                </div>
                                <div class="form-group">
                                    <label for="edit-theoretical-deadline">Échéance théorique</label>
                                    <input type="date" id="edit-theoretical-deadline" name="theoricalDeadLine" value="${formatDateForInput(project.theoreticalDeadline ?? project.theoricalDeadLine ?? '')}">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-real-deadline">Échéance réelle</label>
                                    <input type="date" id="edit-real-deadline" name="realDeadLine" value="${formatDateForInput(project.realDeadline ?? project.realDeadLine ?? '')}">
                                </div>
                                <div class="form-group">
                                    <label for="edit-state">État du projet</label>
                                    <select id="edit-state" name="stateId">
                                        <option value="">Sélectionner un état</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Équipe -->
                    <div class="form-section form-section--team">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-users"></i></span>
                            <span class="form-section__label">Équipe & Client</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-group">
                                <label for="edit-client-search">Client</label>
                                <div class="client-search-container">
                                    <input type="text" id="edit-client-search" placeholder="Rechercher un client par nom..." autocomplete="off">
                                    <input type="hidden" id="edit-client-id" name="clientId" value="${project.clientId || ''}">
                                    <div class="client-search-results" id="edit-client-results"></div>
                                    <div class="client-selected" id="edit-client-selected" style="display:none;">
                                        <span id="edit-client-selected-name"></span>
                                        <button type="button" class="btn-remove-client" title="Retirer le client">&times;</button>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="edit-project-manager">Chef de projet</label>
                                <select id="edit-project-manager" name="projectManagerId">
                                    <option value="">Sélectionner un chef de projet</option>
                                </select>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel">Annuler</button>
                    <button type="submit" class="btn-save">Sauvegarder</button>
                </div>
            </form>
        </div>
    `;

    document.body.appendChild(formOverlay);

    await loadUsersIntoSelects(formOverlay, project);
    await loadStatesIntoSelect(formOverlay, project.stateId);
    setupClientSearch(formOverlay, 'edit', project.clientId);

    const form = formOverlay.querySelector('#project-edit-form');
    const closeBtn = formOverlay.querySelector('.btn-close');
    const cancelBtn = formOverlay.querySelector('.btn-cancel');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await handleEditSubmit(project.id, formOverlay);
        });
    }

    [closeBtn, cancelBtn].forEach(btn => {
        if (btn) {
            btn.addEventListener('click', () => {
                document.body.removeChild(formOverlay);
            });
        }
    });

}

async function handleCreateSubmit(formOverlay: HTMLElement) {
    const form = formOverlay.querySelector('#project-create-form') as HTMLFormElement;
    const formData = new FormData(form);

    const beginDate = formData.get('beginDate') || null;
    const theoricalDeadLine = formData.get('theoricalDeadLine') || null;
    const realDeadLine = formData.get('realDeadLine') || null;
    const clientId = formData.get('clientId') || null;
    const projectManagerId = formData.get('projectManagerId') || null;
    const stateId = formData.get('stateId') || null;

    const newProject = {
        name: formData.get('name'),
        description: formData.get('description') || null,
        beginDate: beginDate,
        theoreticalDeadline: theoricalDeadLine,
        realDeadline: realDeadLine,
        clientId: clientId,
        projectManagerId: projectManagerId,
        stateId: stateId
    };

    try {
        const response = await fetch('/api/add/project', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrfToken(),
            },
            body: JSON.stringify(newProject)
        });

        const result = await response.json();

        if (result.success) {
            document.body.removeChild(formOverlay);
            showProjectToast('Projet créé avec succès !', 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            showProjectToast('Erreur : ' + (result.error || result.message), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showProjectToast('Erreur lors de la création du projet', 'error');
    }
}

async function handleEditSubmit(projectId: number, formOverlay: HTMLElement) {
    const form = formOverlay.querySelector('#project-edit-form') as HTMLFormElement;
    const formData = new FormData(form);

    const beginDate = formData.get('beginDate') || null;
    const theoricalDeadLine = formData.get('theoricalDeadLine') || null;
    const realDeadLine = formData.get('realDeadLine') || null;
    const clientId = formData.get('clientId') || null;
    const projectManagerId = formData.get('projectManagerId') || null;
    const stateId = formData.get('stateId') || null;

    const updatedProject = {
        id: projectId,
        name: formData.get('name'),
        description: formData.get('description') || null,
        beginDate: beginDate,
        theoreticalDeadline: theoricalDeadLine,
        realDeadline: realDeadLine,
        clientId: clientId,
        projectManagerId: projectManagerId,
        stateId: stateId
    };

    try {
        const response = await fetch(`/api/edit/project/${projectId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-Token': getCsrfToken(),
            },
            body: JSON.stringify(updatedProject)
        });

        const result = await response.json();

        if (result.success) {
            document.body.removeChild(formOverlay);
            showProjectToast('Projet modifié avec succès !', 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            showProjectToast('Erreur : ' + (result.error || result.message), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showProjectToast('Erreur lors de la modification du projet', 'error');
    }
}

async function loadUsersIntoSelects(formOverlay: HTMLElement, project: ProjectData | null = null) {
    try {
        const data = await UserService.loadUsersFromApi();
        const users = data?.data || [];

        const managerSelect = formOverlay.querySelector('#edit-project-manager, #create-project-manager') as HTMLSelectElement;
        if (managerSelect) {
            const managers = users.filter((user: any) => user.roleName === 'CDP');
            managers.forEach((user: any) => {
                const option = document.createElement('option');
                option.value = user.id;
                option.textContent = `${user.firstname} ${user.lastname} (${user.email})`;
                if (project && project.projectManagerId == user.id) {
                    option.selected = true;
                }
                managerSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erreur lors du chargement des utilisateurs :', error);
    }
}

async function loadStatesIntoSelect(formOverlay: HTMLElement, selectedStateId: string | null = null) {
    try {
        const response = await fetch('/api/states');
        const data = await response.json();
        const states: StateData[] = data?.data?.states || data?.states || [];

        const stateSelect = formOverlay.querySelector('#edit-state, #create-state') as HTMLSelectElement;
        if (stateSelect) {
            states.forEach((state: StateData) => {
                const option = document.createElement('option');
                option.value = state.id;
                option.textContent = state.name;
                if (selectedStateId && selectedStateId == state.id) {
                    option.selected = true;
                }
                stateSelect.appendChild(option);
            });
        }
    } catch (error) {
        console.error('Erreur lors du chargement des états :', error);
    }
}

function setupClientSearch(formOverlay: HTMLElement, prefix: string, currentClientId: string | null = null) {
    const searchInput = formOverlay.querySelector(`#${prefix}-client-search`) as HTMLInputElement;
    const hiddenInput = formOverlay.querySelector(`#${prefix}-client-id`) as HTMLInputElement;
    const resultsDiv = formOverlay.querySelector(`#${prefix}-client-results`) as HTMLElement;
    const selectedDiv = formOverlay.querySelector(`#${prefix}-client-selected`) as HTMLElement;
    const selectedName = formOverlay.querySelector(`#${prefix}-client-selected-name`) as HTMLElement;
    const removeBtn = formOverlay.querySelector(`#${prefix}-client-selected .btn-remove-client`) as HTMLElement;

    if (!searchInput || !hiddenInput || !resultsDiv) return;

    let allClients: ClientData[] = [];

    // Load all clients once
    fetch('/api/clients')
        .then(res => res.json())
        .then(data => {
            allClients = data?.data || data?.clients || [];

            // If editing with an existing clientId, show the selected client
            if (currentClientId) {
                const current = allClients.find(c => c.siret === currentClientId);
                if (current && selectedDiv && selectedName) {
                    selectedName.textContent = `${current.companyname || current.companyName} (${current.siret})`;
                    selectedDiv.style.display = 'flex';
                    searchInput.style.display = 'none';
                }
            }
        })
        .catch(err => console.error('Erreur chargement clients:', err));

    let debounceTimer: ReturnType<typeof setTimeout>;

    searchInput.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => {
            const query = searchInput.value.trim().toLowerCase();
            resultsDiv.innerHTML = '';

            if (query.length < 1) {
                resultsDiv.style.display = 'none';
                return;
            }

            const filtered = allClients.filter(c => {
                const name = (c.companyname || c.companyName || '').toLowerCase();
                const siret = (c.siret || '').toLowerCase();
                return name.includes(query) || siret.includes(query);
            });

            if (filtered.length === 0) {
                resultsDiv.innerHTML = '<div class="client-search-item no-result">Aucun client trouvé</div>';
                resultsDiv.style.display = 'block';
                return;
            }

            filtered.slice(0, 10).forEach(client => {
                const item = document.createElement('div');
                item.className = 'client-search-item';
                item.textContent = `${client.companyname || client.companyName} (${client.siret})`;
                item.addEventListener('click', () => {
                    hiddenInput.value = client.siret;
                    if (selectedName) selectedName.textContent = `${client.companyname || client.companyName} (${client.siret})`;
                    if (selectedDiv) selectedDiv.style.display = 'flex';
                    searchInput.value = '';
                    searchInput.style.display = 'none';
                    resultsDiv.style.display = 'none';
                });
                resultsDiv.appendChild(item);
            });

            resultsDiv.style.display = 'block';
        }, 200);
    });

    // Close results when clicking outside
    document.addEventListener('click', (e) => {
        if (!searchInput.contains(e.target as Node) && !resultsDiv.contains(e.target as Node)) {
            resultsDiv.style.display = 'none';
        }
    });

    // Remove selected client
    if (removeBtn) {
        removeBtn.addEventListener('click', () => {
            hiddenInput.value = '';
            if (selectedDiv) selectedDiv.style.display = 'none';
            searchInput.style.display = '';
            searchInput.value = '';
        });
    }
}


function toggleDropdown(event: Event, btn: HTMLElement) {
    event.stopPropagation();
    const dropdown = btn.closest('.project-dropdown')?.querySelector('.dropdown-menu-custom');
    const allDropdowns = document.querySelectorAll('.dropdown-menu-custom');

    allDropdowns.forEach(d => {
        if (d !== dropdown) {
            d.classList.remove('show');
        }
    });

    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}
