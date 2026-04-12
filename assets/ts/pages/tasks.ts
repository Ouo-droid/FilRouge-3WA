import { escapeHtml, formatDateTimeForInput, formatDate } from "../utils/helpers";
import { getCsrfToken } from "../services/Api";

interface TaskData {
    id: string;
    name: string;
    description: string;
    type: string;
    format: string;
    priority: string;
    difficulty: string;
    beginDate: string;
    theoricalEndDate: string;
    realEndDate: string;
    developerId: string;
    projectId: string;
    stateId: string;
    effortrequired?: number;
    effortmade?: number;
}

function formatEffort(hours: number | null | undefined): string {
    if (hours === null || hours === undefined || hours === 0) return '-';

    const hoursPerDay = 8;
    const days = Math.floor(hours / hoursPerDay);
    const remainingHours = hours % hoursPerDay;

    if (days === 0) return `${remainingHours}h`;
    if (remainingHours === 0) return `${days}j`;
    return `${days}j ${remainingHours}h`;
}

function validateEffort(value: any): string | null {
    const effort = parseFloat(value);

    if (isNaN(effort) || value === '' || value === null) {
        return "L'effort estimé est obligatoire.";
    }
    if (effort <= 0) {
        return "L'effort doit être supérieur à 0.";
    }
    if (effort > 99.99) {
        return "L'effort ne peut pas dépasser 99.99 heures (~12.5 jours).";
    }
    return null; // pas d'erreur
}

document.addEventListener('DOMContentLoaded', function () {
    // Only run if we are on the tasks page
    if (!document.querySelector('.tasks-table') && !document.getElementById('task-list')) {
        return;
    }

    // Delegation des événements
    document.addEventListener('click', function (e) {
        const target = e.target as HTMLElement;

        // Trigger nouvelle tâche
        if (target.matches('.create-task-trigger')) {
            const btn = document.getElementById('create-task-btn');
            if (btn) btn.click();
        }

        // Voir détails
        const viewBtn = target.closest('.view-task-btn');
        if (viewBtn) {
            e.preventDefault();
            const taskId = viewBtn.getAttribute('data-task-id');
            if (taskId) viewTask(taskId);
        }

        // Modifier tâche
        const editBtn = target.closest('.edit-task-btn');
        if (editBtn) {
            e.preventDefault();
            const taskId = editBtn.getAttribute('data-task-id');
            if (taskId) editTask(taskId);
        }

        // Supprimer tâche
        const deleteBtn = target.closest('.delete-btn');
        if (deleteBtn) {
            const taskId = deleteBtn.getAttribute('data-task-id');
            const taskName = deleteBtn.getAttribute('data-task-name');

            if (taskId && confirm(`Êtes-vous sûr de vouloir supprimer la tâche "${taskName}" ?`)) {
                deleteTask(taskId);
            }
        }

        // Clôturer tâche (Gérer le temps)
        const manageTimeBtn = target.closest('.manage-time-btn');
        if (manageTimeBtn) {
            e.preventDefault();
            const taskId = manageTimeBtn.getAttribute('data-task-id');
            if (taskId) openCloseTaskModal(taskId);
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

    // Bouton créer tâche
    const createBtn = document.getElementById('create-task-btn');
    if (createBtn) {
        createBtn.addEventListener('click', function () {
            createNewTask();
        });
    }

    // ── Filtre par état (pills) ──────────────────────────────────────────────
    let activeStateFilter = 'all';

    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function (this: HTMLElement) {
            filterBtns.forEach(b => {
                b.classList.remove('active');
                b.setAttribute('aria-pressed', 'false');
            });
            this.classList.add('active');
            this.setAttribute('aria-pressed', 'true');
            activeStateFilter = this.getAttribute('data-filter') || 'all';
            applyAllFilters();
        });
    });

    // ── Filtres projet et utilisateur ────────────────────────────────────────
    const filterProject  = document.getElementById('filter-project')  as HTMLSelectElement | null;
    const filterUser     = document.getElementById('filter-user')     as HTMLSelectElement | null;

    if (filterProject) {
        filterProject.addEventListener('change', applyAllFilters);
    }
    if (filterUser) {
        filterUser.addEventListener('change', applyAllFilters);
    }

    function applyAllFilters(): void {
        const projectVal = filterProject?.value || '';
        const userVal    = filterUser?.value    || '';
        const taskRows   = document.querySelectorAll('.task-card');

        taskRows.forEach(row => {
            const stateId   = row.getAttribute('data-state-id')     || '';
            const projectId = row.getAttribute('data-project-id')   || '';
            const devId     = row.getAttribute('data-developer-id') || '';

            const stateOk   = activeStateFilter === 'all' || stateId === activeStateFilter;
            const projectOk = !projectVal || projectId === projectVal;
            const userOk    = !userVal    || devId === userVal;

            (row as HTMLElement).style.display = (stateOk && projectOk && userOk) ? '' : 'none';
        });
    }
});

function viewTask(taskId: string) {
    fetch(`/api/task/${taskId}`)
        .then(response => response.json())
        .then(data => {
            if (data.task) {
                showTaskModal(data.task);
            } else {
                alert('Erreur lors de la récupération de la tâche');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la récupération de la tâche');
        });
}

export function editTask(taskId: string) {
    fetch(`/api/task/${taskId}`)
        .then(response => response.json())
        .then(data => {
            if (data.task) {
                showEditForm(data.task);
            } else {
                alert('Erreur lors de la récupération de la tâche');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            alert('Erreur lors de la récupération de la tâche');
        });
}

function showToast(message: string, type: 'success' | 'error' = 'success') {
    const toast = document.createElement('div');
    toast.className = `task-toast task-toast--${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);

    // Slide in
    requestAnimationFrame(() => toast.classList.add('task-toast--visible'));

    // Auto-remove after 3s
    setTimeout(() => {
        toast.classList.remove('task-toast--visible');
        toast.addEventListener('transitionend', () => toast.remove());
    }, 3000);
}

async function deleteTask(taskId: string) {
    try {
        const response = await fetch(`/api/delete/task/${taskId}`, {
            method: 'DELETE',
            headers: { 'X-CSRF-Token': getCsrfToken() },
        });

        const result = await response.json();

        if (result.success || result.delete) {
            showToast('Tâche supprimée avec succès !', 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast(result.error || 'Erreur lors de la suppression de la tâche', 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showToast('Erreur lors de la suppression de la tâche', 'error');
    }
}

export async function createNewTask(prefill?: { projectId: string; projectName: string }) {
    const formOverlay = document.createElement('div');
    formOverlay.className = 'form-overlay';
    formOverlay.innerHTML = `
        <div class="edit-form">
            <div class="form-header">
                <h3>Créer une nouvelle tâche</h3>
                <button class="btn-close" type="button" aria-label="Fermer">×</button>
            </div>
            <form id="task-create-form">
                <div class="form-content">

                    <!-- Section Identification -->
                    <div class="form-section form-section--identification">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-tag"></i></span>
                            <span class="form-section__label">Identification</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-group">
                                <label for="create-name">Nom de la tâche *</label>
                                <input type="text" id="create-name" name="name" placeholder="Ex: Développer la page d'accueil" required>
                            </div>
                            <div class="form-group">
                                <label for="create-description">Description</label>
                                <textarea id="create-description" name="description" rows="3" placeholder="Décrivez la tâche..."></textarea>
                            </div>
                            <div class="form-group">
                                <label for="create-effortRequired">Effort estimé (heures) *</label>
                                <input type="number" id="create-effortRequired" name="effortRequired" min="0.5" max="99.99" step="0.5" placeholder="Ex: 8" required>
                                <div class="form-hint">8h = 1 journée · 4h = demi-journée · max 99.99h</div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Catégorisation -->
                    <div class="form-section form-section--categorisation">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-layer-group"></i></span>
                            <span class="form-section__label">Catégorisation</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="create-type">Type *</label>
                                    <select id="create-type" name="type" required>
                                        <option value="">Sélectionner un type</option>
                                        <optgroup label="Front-end">
                                            <option value="UI / Intégration">UI / Intégration</option>
                                            <option value="Composant">Composant</option>
                                            <option value="Animation / UX">Animation / UX</option>
                                        </optgroup>
                                        <optgroup label="Back-end">
                                            <option value="API / Endpoint">API / Endpoint</option>
                                            <option value="Base de données">Base de données</option>
                                            <option value="Migration">Migration</option>
                                        </optgroup>
                                        <optgroup label="Transversal">
                                            <option value="Bug fix">Bug fix</option>
                                            <option value="Refactoring">Refactoring</option>
                                            <option value="Tests">Tests</option>
                                            <option value="Documentation">Documentation</option>
                                            <option value="Review / Code review">Review / Code review</option>
                                            <option value="Déploiement / DevOps">Déploiement / DevOps</option>
                                            <option value="Sécurité">Sécurité</option>
                                            <option value="Performance">Performance</option>
                                        </optgroup>
                                        <optgroup label="Gestion">
                                            <option value="Analyse / Spécification">Analyse / Spécification</option>
                                            <option value="Réunion / Point">Réunion / Point</option>
                                            <option value="Recherche / R&D">Recherche / R&D</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="create-format">Format</label>
                                    <input type="text" id="create-format" name="format" placeholder="Ex: Web">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="create-priority">Priorité</label>
                                    <select id="create-priority" name="priority">
                                        <option value="">Sélectionner une priorité</option>
                                        <option value="high">🔴 Haute</option>
                                        <option value="medium">🟡 Moyenne</option>
                                        <option value="low">🟢 Basse</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="create-difficulty">Difficulté</label>
                                    <select id="create-difficulty" name="difficulty">
                                        <option value="">Sélectionner une difficulté</option>
                                        <option value="easy">🟢 Facile</option>
                                        <option value="medium">🟡 Moyenne</option>
                                        <option value="hard">🔴 Difficile</option>
                                    </select>
                                </div>
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
                                    <input type="datetime-local" id="create-begin-date" name="beginDate">
                                </div>
                                <div class="form-group">
                                    <label for="create-theoretical-end-date">Échéance théorique</label>
                                    <input type="datetime-local" id="create-theoretical-end-date" name="theoricalEndDate">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="create-real-end-date">Échéance réelle</label>
                                <input type="datetime-local" id="create-real-end-date" name="realEndDate">
                            </div>
                        </div>
                    </div>

                    <!-- Section Assignation -->
                    <div class="form-section form-section--assignation">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-user-cog"></i></span>
                            <span class="form-section__label">Assignation</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="create-developer">Développeur assigné</label>
                                    <select id="create-developer" name="developerId">
                                        <option value="">Sélectionner un développeur</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="create-project">Projet</label>
                                    <select id="create-project" name="projectId">
                                        <option value="">Sélectionner un projet</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="create-status">Statut</label>
                                <select id="create-status" name="stateId">
                                    <option value="">Sélectionner un statut</option>
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

    await loadUsersAndProjectsIntoSelects(formOverlay);

    if (prefill) {
        const projectSelect = formOverlay.querySelector<HTMLSelectElement>('#create-project');
        if (projectSelect) {
            projectSelect.value = prefill.projectId;
            projectSelect.style.pointerEvents = 'none';
            projectSelect.style.opacity = '0.6';
            // champ caché pour garantir l'envoi même si le select est visuellement verrouillé
            const hidden = document.createElement('input');
            hidden.type = 'hidden';
            hidden.name = 'projectId';
            hidden.value = prefill.projectId;
            projectSelect.parentElement?.appendChild(hidden);
            // désactiver le name du select pour éviter le doublon
            projectSelect.removeAttribute('name');
        }
    }

    const form = formOverlay.querySelector('#task-create-form');
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

    formOverlay.addEventListener('click', (e) => {
        if (e.target === formOverlay) {
            document.body.removeChild(formOverlay);
        }
    });
}

async function showEditForm(task: TaskData) {
    const formOverlay = document.createElement('div');
    formOverlay.className = 'form-overlay';
    formOverlay.innerHTML = `
        <div class="edit-form">
            <div class="form-header">
                <h3>Modifier la tâche</h3>
                <button class="btn-close" type="button" aria-label="Fermer">×</button>
            </div>
            <form id="task-edit-form">
                <div class="form-content">

                    <!-- Section Identification -->
                    <div class="form-section form-section--identification">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-tag"></i></span>
                            <span class="form-section__label">Identification</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-group">
                                <label for="edit-name">Nom de la tâche *</label>
                                <input type="text" id="edit-name" name="name" value="${escapeHtml(task.name)}" required>
                            </div>
                            <div class="form-group">
                                <label for="edit-description">Description</label>
                                <textarea id="edit-description" name="description" rows="3">${escapeHtml(task.description || '')}</textarea>
                            </div>
                            <div class="form-group">
                                <label for="edit-effortRequired">Effort estimé (heures) *</label>
                                <input type="number" id="edit-effortRequired" name="effortRequired" value="${task.effortrequired || ''}" min="0.5" max="99.99" step="0.5" required>
                                <div class="form-hint">8h = 1 journée · 4h = demi-journée · max 99.99h</div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Catégorisation -->
                    <div class="form-section form-section--categorisation">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-layer-group"></i></span>
                            <span class="form-section__label">Catégorisation</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-type">Type *</label>
                                    <select id="edit-type" name="type" required>
                                        <option value="">Sélectionner un type</option>
                                        <optgroup label="Front-end">
                                            <option value="UI / Intégration" ${task.type === 'UI / Intégration' ? 'selected' : ''}>UI / Intégration</option>
                                            <option value="Composant" ${task.type === 'Composant' ? 'selected' : ''}>Composant</option>
                                            <option value="Animation / UX" ${task.type === 'Animation / UX' ? 'selected' : ''}>Animation / UX</option>
                                            <option value="Back-end" ${task.type === 'Back-end' ? 'selected' : ''}>Back-end</option>
                                        </optgroup>
                                        <optgroup label="API / Endpoint">
                                            <option value="API / Endpoint" ${task.type === 'API / Endpoint' ? 'selected' : ''}>API / Endpoint</option>
                                            <option value="Base de données" ${task.type === 'Base de données' ? 'selected' : ''}>Base de données</option>
                                            <option value="Migration" ${task.type === 'Migration' ? 'selected' : ''}>Migration</option>
                                        </optgroup>
                                        <optgroup label="Transversal">
                                            <option value="Bug fix" ${task.type === 'Bug fix' ? 'selected' : ''}>Bug fix</option>
                                            <option value="Refactoring" ${task.type === 'Refactoring' ? 'selected' : ''}>Refactoring</option>
                                            <option value="Tests" ${task.type === 'Tests' ? 'selected' : ''}>Tests</option>
                                            <option value="Documentation" ${task.type === 'Documentation' ? 'selected' : ''}>Documentation</option>
                                            <option value="Review / Code review" ${task.type === 'Review / Code review' ? 'selected' : ''}>Review / Code review</option>
                                            <option value="Déploiement / DevOps" ${task.type === 'Déploiement / DevOps' ? 'selected' : ''}>Déploiement / DevOps</option>
                                            <option value="Sécurité" ${task.type === 'Sécurité' ? 'selected' : ''}>Sécurité</option>
                                            <option value="Performance" ${task.type === 'Performance' ? 'selected' : ''}>Performance</option>
                                        </optgroup>
                                        <optgroup label="Gestion">
                                            <option value="Analyse / Spécification" ${task.type === 'Analyse / Spécification' ? 'selected' : ''}>Analyse / Spécification</option>
                                            <option value="Réunion / Point" ${task.type === 'Réunion / Point' ? 'selected' : ''}>Réunion / Point</option>
                                            <option value="Recherche / R&D" ${task.type === 'Recherche / R&D' ? 'selected' : ''}>Recherche / R&D</option>
                                        </optgroup>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="edit-format">Format</label>
                                    <input type="text" id="edit-format" name="format" value="${escapeHtml(task.format || '')}">
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-priority">Priorité</label>
                                    <select id="edit-priority" name="priority">
                                        <option value="">Sélectionner une priorité</option>
                                        <option value="high" ${task.priority === 'high' ? 'selected' : ''}>🔴 Haute</option>
                                        <option value="medium" ${task.priority === 'medium' ? 'selected' : ''}>🟡 Moyenne</option>
                                        <option value="low" ${task.priority === 'low' ? 'selected' : ''}>🟢 Basse</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="edit-difficulty">Difficulté</label>
                                    <select id="edit-difficulty" name="difficulty">
                                        <option value="">Sélectionner une difficulté</option>
                                        <option value="easy" ${task.difficulty === 'easy' ? 'selected' : ''}>🟢 Facile</option>
                                        <option value="medium" ${task.difficulty === 'medium' ? 'selected' : ''}>🟡 Moyenne</option>
                                        <option value="hard" ${task.difficulty === 'hard' ? 'selected' : ''}>🔴 Difficile</option>
                                    </select>
                                </div>
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
                                    <input type="datetime-local" id="edit-begin-date" name="beginDate" value="${formatDateTimeForInput(task.beginDate)}">
                                </div>
                                <div class="form-group">
                                    <label for="edit-theoretical-end-date">Échéance théorique</label>
                                    <input type="datetime-local" id="edit-theoretical-end-date" name="theoricalEndDate" value="${formatDateTimeForInput(task.theoricalEndDate)}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="edit-real-end-date">Échéance réelle</label>
                                <input type="datetime-local" id="edit-real-end-date" name="realEndDate" value="${formatDateTimeForInput(task.realEndDate)}">
                            </div>
                        </div>
                    </div>

                    <!-- Section Assignation -->
                    <div class="form-section form-section--assignation">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-user-cog"></i></span>
                            <span class="form-section__label">Assignation</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="edit-developer">Développeur assigné</label>
                                    <select id="edit-developer" name="developerId">
                                        <option value="">Sélectionner un développeur</option>
                                    </select>
                                </div>
                                <div class="form-group">
                                    <label for="edit-project">Projet</label>
                                    <select id="edit-project" name="projectId">
                                        <option value="">Sélectionner un projet</option>
                                    </select>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="edit-status">Statut</label>
                                <select id="edit-status" name="stateId">
                                    <option value="">Sélectionner un statut</option>
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

    await loadUsersAndProjectsIntoSelects(formOverlay, task);

    const form = formOverlay.querySelector('#task-edit-form');
    const closeBtn = formOverlay.querySelector('.btn-close');
    const cancelBtn = formOverlay.querySelector('.btn-cancel');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await handleEditSubmit(task.id, formOverlay);
        });
    }

    [closeBtn, cancelBtn].forEach(btn => {
        if (btn) {
            btn.addEventListener('click', () => {
                document.body.removeChild(formOverlay);
            });
        }
    });

    formOverlay.addEventListener('click', (e) => {
        if (e.target === formOverlay) {
            document.body.removeChild(formOverlay);
        }
    });
}

async function handleCreateSubmit(formOverlay: HTMLElement) {
    const form = formOverlay.querySelector('#task-create-form') as HTMLFormElement;
    const formData = new FormData(form);

    const newTask: any = {};
    formData.forEach((value, key) => newTask[key] = value);

    const effortValidation = validateEffort(newTask.effortRequired);
    if (effortValidation) {
        showToast(effortValidation, 'error');
        return;
    }
    newTask.effortRequired = parseFloat(newTask.effortRequired);

    try {
        const response = await fetch('/api/add/task', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
            body: JSON.stringify(newTask)
        });

        const result = await response.json();

        if (result.success) {
            document.body.removeChild(formOverlay);
            showToast('Tâche créée avec succès !', 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast('Erreur : ' + (result.error || result.message), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showToast('Erreur lors de la création de la tâche', 'error');
    }
}

async function handleEditSubmit(taskId: string, formOverlay: HTMLElement) {
    const form = formOverlay.querySelector('#task-edit-form') as HTMLFormElement;
    const formData = new FormData(form);

    const updatedTask: any = { id: taskId };
    formData.forEach((value, key) => updatedTask[key] = value);

    if (updatedTask.effortRequired) {
        const effortValidation = validateEffort(updatedTask.effortRequired);
        if (effortValidation) {
            showToast(effortValidation, 'error');
            return;
        }
        updatedTask.effortRequired = parseFloat(updatedTask.effortRequired);
    }

    try {
        const response = await fetch(`/api/edit/task/${taskId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
            body: JSON.stringify(updatedTask)
        });

        const result = await response.json();

        if (result.success) {
            document.body.removeChild(formOverlay);
            showToast('Tâche modifiée avec succès !', 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast('Erreur : ' + (result.error || result.message), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showToast('Erreur lors de la modification de la tâche', 'error');
    }
}

async function loadUsersAndProjectsIntoSelects(formOverlay: HTMLElement, task: TaskData | null = null) {
    try {
        const [usersRes, projectsRes, statesRes, absencesRes] = await Promise.all([
            fetch('/api/users'),
            fetch('/api/projects'),
            fetch('/api/states'),
            fetch('/api/absences/active'),
        ]);

        const users = (await usersRes.json())?.data || [];
        const projectsData = await projectsRes.json();
        const projects = projectsData?.data?.projects || projectsData?.projects || [];
        console.log('Projets reçus:', JSON.stringify(projects[0]));
        const statesData = await statesRes.json();
        const states = statesData?.data?.states || statesData?.states || [];
        const absencesData = await absencesRes.json().catch(() => ({}));
        const absentIds: Set<string> = new Set(
            (absencesData.absences ?? []).map((a: {user_id: string}) => a.user_id)
        );

        const developerSelect = formOverlay.querySelector<HTMLSelectElement>('#edit-developer, #create-developer');
        if (developerSelect) {
            users.forEach((user: any) => {
                const option = document.createElement('option');
                option.value = user.id;
                const absentLabel = absentIds.has(user.id) ? ' ⚠️ (absent)' : '';
                option.textContent = `${user.firstname} ${user.lastname} (${user.email})${absentLabel}`;
                if (absentIds.has(user.id)) {
                    option.setAttribute('data-absent', 'true');
                }
                if (task && task.developerId == user.id) {
                    option.selected = true;
                }
                developerSelect.appendChild(option);
            });

            // Add warning div after select
            const warnDiv = document.createElement('div');
            warnDiv.id = 'task-absence-warning';
            warnDiv.style.cssText = 'color:#b45309;background:#fef3c7;border:1px solid #fbbf24;border-radius:6px;padding:.4rem .75rem;font-size:.82rem;margin-top:.35rem;display:none;';
            warnDiv.textContent = '⚠️ Ce collaborateur est actuellement en absence.';
            developerSelect.parentElement?.appendChild(warnDiv);

            developerSelect.addEventListener('change', () => {
                const selected = developerSelect.selectedOptions[0];
                const isAbsent = selected?.getAttribute('data-absent') === 'true';
                warnDiv.style.display = isAbsent ? 'block' : 'none';
            });

            // Show warning immediately if pre-selected user is absent
            if (task && task.developerId && absentIds.has(task.developerId)) {
                warnDiv.style.display = 'block';
            }
        }

        const projectSelect = formOverlay.querySelector('#edit-project, #create-project');
        if (projectSelect) {
            console.log('[tasks.ts] Loading projects into select:', projects);
            projects.forEach((project: any) => {
                console.log(`[tasks.ts] Project: ID=${project.id} (type: ${typeof project.id}), Name=${project.name}`);
                const option = document.createElement('option');
                option.value = project.id;
                option.textContent = project.name;
                if (task && task.projectId == project.id) {
                    option.selected = true;
                }
                projectSelect.appendChild(option);
            });
        }

        const statusSelect = formOverlay.querySelector('#edit-status, #create-status');
        if (statusSelect) {
            states.forEach((state: any) => {
                const option = document.createElement('option');
                option.value = state.id;
                option.textContent = state.name;
                if (task && task.stateId == state.id) {
                    option.selected = true;
                }
                statusSelect.appendChild(option);
            });
        }

    } catch (error) {
        console.error('Erreur lors du chargement des données :', error);
        showToast('Erreur lors du chargement des données', 'error');
    }
}


function showTaskModal(task: TaskData) {
    const priorityConfig: Record<string, { label: string; color: string; bg: string; dot: string }> = {
        high:   { label: 'Haute',   color: '#991b1b', bg: '#fee2e2', dot: '#ef4444' },
        medium: { label: 'Moyenne', color: '#92400e', bg: '#fef3c7', dot: '#f59e0b' },
        low:    { label: 'Basse',   color: '#065f46', bg: '#dcfce7', dot: '#10b981' },
    };
    const prio = priorityConfig[(task.priority || '').toLowerCase()] ?? { label: task.priority || '—', color: '#475569', bg: '#f1f5f9', dot: '#94a3b8' };

    function row(icon: string, label: string, value: string, accent = false): string {
        return `
        <div class="td-row${accent ? ' td-row--accent' : ''}">
            <span class="td-row__label"><i class="fas fa-${icon}"></i>${label}</span>
            <span class="td-row__value">${value || '—'}</span>
        </div>`;
    }

    const overlay = document.createElement('div');
    overlay.className = 'td-overlay';
    overlay.innerHTML = `
        <div class="td-panel">
            <div class="td-panel__header">
                <div class="td-panel__icon"><i class="fas fa-tasks"></i></div>
                <div class="td-panel__title">
                    <p class="td-panel__code">TASK-${task.id}</p>
                    <h2 class="td-panel__name">${task.name}</h2>
                </div>
                <button class="td-panel__close" aria-label="Fermer"><i class="fas fa-times"></i></button>
            </div>

            <div class="td-panel__priority-bar">
                <span class="td-panel__badge" style="background:${prio.bg};color:${prio.color};">
                    <span class="td-panel__badge-dot" style="background:${prio.dot};"></span>
                    Priorité ${prio.label}
                </span>
                ${task.effortrequired ? `<span class="td-panel__effort"><i class="fas fa-stopwatch"></i>${formatEffort(task.effortrequired)} estimés</span>` : ''}
            </div>

            ${task.description ? `<p class="td-panel__desc">${task.description}</p>` : ''}

            <div class="td-panel__section-title">Informations</div>
            <div class="td-rows">
                ${row('tag', 'Type', task.type || '—')}
                ${row('th-large', 'Format', task.format || '—')}
                ${row('bolt', 'Difficulté', task.difficulty || '—')}
                ${row('calendar-plus', 'Début', task.beginDate ? formatDate(task.beginDate) : '—')}
                ${row('calendar-check', 'Échéance théorique', task.theoricalEndDate ? formatDate(task.theoricalEndDate) : '—', true)}
                ${row('calendar-times', 'Échéance réelle', task.realEndDate ? formatDate(task.realEndDate) : '—')}
                ${task.effortmade ? row('history', 'Effort réalisé', formatEffort(task.effortmade)) : ''}
            </div>
        </div>`;

    document.body.appendChild(overlay);
    requestAnimationFrame(() => overlay.classList.add('td-overlay--visible'));

    const triggerEl = document.activeElement as HTMLElement;
    const closeBtn = overlay.querySelector('.td-panel__close') as HTMLElement;
    closeBtn?.focus();

    const close = () => {
        overlay.classList.remove('td-overlay--visible');
        overlay.addEventListener('transitionend', () => { overlay.remove(); triggerEl?.focus(); }, { once: true });
    };

    // Focus trap within overlay
    overlay.addEventListener('keydown', (e) => {
        if (e.key !== 'Tab') return;
        const focusable = Array.from(overlay.querySelectorAll<HTMLElement>(
            'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])'
        ));
        if (focusable.length === 0) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (e.shiftKey && document.activeElement === first) { last.focus(); e.preventDefault(); }
        else if (!e.shiftKey && document.activeElement === last) { first.focus(); e.preventDefault(); }
    });

    closeBtn?.addEventListener('click', close);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) close(); });
    document.addEventListener('keydown', function onKey(e) {
        if (e.key === 'Escape') { close(); document.removeEventListener('keydown', onKey); }
    });
}

export async function openCloseTaskModal(taskId: string) {
    // Récupère la tâche pour pré-remplir les champs
    let task: TaskData | null = null;
    try {
        const res = await fetch(`/api/task/${taskId}`);
        const data = await res.json();
        task = data.task ?? null;
    } catch (e) {
        console.error('Erreur récupération tâche:', e);
    }

    const now = new Date();
    const defaultDate = now.toISOString().slice(0, 16); // Format datetime-local

    const overlay = document.createElement('div');
    overlay.className = 'form-overlay';
    overlay.innerHTML = `
        <div class="edit-form">
            <div class="form-header">
                <h3>Clôturer la tâche</h3>
                <button class="btn-close" type="button">×</button>
            </div>
            <form id="close-task-form">
                <div class="form-content">

                    <div class="form-section form-section--closure">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-flag-checkered"></i></span>
                            <span class="form-section__label">Finalisation</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-group">
                                <label for="close-real-end-date">Date de fin réelle *</label>
                                <input type="datetime-local" id="close-real-end-date" name="realEndDate" value="${task?.realEndDate ? task.realEndDate.slice(0, 16) : defaultDate}" required>
                            </div>
                            <div class="form-group">
                                <label for="close-effort-made">Effort réel consommé (heures) *</label>
                                <input type="number" id="close-effort-made" name="effortMade" min="0.5" max="999.99" step="0.5" value="${task?.effortmade || ''}" placeholder="Ex: 12" required>
                                <div class="form-hint">8h = 1 journée · 4h = demi-journée</div>
                            </div>
                        </div>
                    </div>

                </div>
                <div class="form-actions">
                    <button type="button" class="btn-cancel">Annuler</button>
                    <button type="submit" class="btn-save">Confirmer la clôture</button>
                </div>
            </form>
        </div>
    `;

    document.body.appendChild(overlay);

    const triggerCloseEl = document.activeElement as HTMLElement;
    const form = overlay.querySelector('#close-task-form');
    const closeBtn = overlay.querySelector('.btn-close') as HTMLElement;
    const cancelBtn = overlay.querySelector('.btn-cancel');

    // Move focus to first field
    const firstInput = overlay.querySelector<HTMLElement>('input,select,textarea,button');
    firstInput?.focus();

    const removeOverlay = () => { document.body.removeChild(overlay); triggerCloseEl?.focus(); };

    // Focus trap
    overlay.addEventListener('keydown', (e) => {
        if (e.key !== 'Tab') return;
        const focusable = Array.from(overlay.querySelectorAll<HTMLElement>(
            'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])'
        ));
        if (focusable.length === 0) return;
        const first = focusable[0];
        const last = focusable[focusable.length - 1];
        if (e.shiftKey && document.activeElement === first) { last.focus(); e.preventDefault(); }
        else if (!e.shiftKey && document.activeElement === last) { first.focus(); e.preventDefault(); }
    });

    [closeBtn, cancelBtn].forEach(btn => btn?.addEventListener('click', removeOverlay));
    overlay.addEventListener('click', (e) => { if (e.target === overlay) removeOverlay(); });

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            await handleCloseTaskSubmit(taskId, overlay);
        });
    }
}

async function handleCloseTaskSubmit(taskId: string, overlay: HTMLElement) {
    const form = overlay.querySelector('#close-task-form') as HTMLFormElement;
    const formData = new FormData(form);

    const realEndDate = formData.get('realEndDate') as string;
    const effortMade = parseFloat(formData.get('effortMade') as string);

    if (!realEndDate) {
        showToast('La date de fin réelle est obligatoire.', 'error');
        return;
    }
    if (isNaN(effortMade) || effortMade <= 0) {
        showToast('L\'effort réel doit être supérieur à 0.', 'error');
        return;
    }

    // Trouver l'état "Terminée" dynamiquement
    let termineeStateId: string | null = null;
    try {
        const statesRes = await fetch('/api/states');
        const statesData = await statesRes.json();
        const states: Array<{ id: string, name: string }> = statesData?.data?.states || statesData?.states || [];
        const termineeState = states.find(s =>
            ['terminé', 'terminée', 'terminées', 'done', 'completed', 'fini', 'finie', 'closed'].includes(s.name.toLowerCase().trim())
        );
        termineeStateId = termineeState?.id ?? null;
    } catch (e) {
        console.error('Erreur récupération états:', e);
    }

    const payload: Record<string, any> = {
        realEndDate,
        effortMade,
    };
    if (termineeStateId) {
        payload.stateId = termineeStateId;
    }

    try {
        const response = await fetch(`/api/edit/task/${taskId}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
            body: JSON.stringify(payload)
        });

        const result = await response.json();

        if (result.success) {
            document.body.removeChild(overlay);
            showToast('Tâche clôturée avec succès !', 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            showToast('Erreur : ' + (result.error || result.message), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showToast('Erreur lors de la clôture de la tâche', 'error');
    }
}
