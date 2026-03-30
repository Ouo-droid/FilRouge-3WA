import { getCsrfToken } from "../services/Api";
import { createNewTask, editTask, openCloseTaskModal } from "./tasks";
import { editProject } from "./projects";

document.addEventListener('DOMContentLoaded', () => {

    // ── Read embedded project data ──────────────────────────────────────────
    const dataEl = document.getElementById('pd-project-data');
    if (!dataEl) return; // Not on the project details page

    let pdData: { projectId: string; projectName: string; canCreate: boolean; canDelete: boolean; states: {id:string;name:string}[]; tasks: {id:string;name:string}[]; users: {id:string;name:string}[] };
    try {
        pdData = JSON.parse(dataEl.textContent ?? '{}');
    } catch {
        return;
    }

    const projectId = pdData.projectId;

    // ── Helpers ─────────────────────────────────────────────────────────────

    function openModal(id: string): void {
        const el = document.getElementById(id);
        if (el) { el.style.display = 'flex'; }
    }

    function closeModal(id: string): void {
        const el = document.getElementById(id);
        if (el) { el.style.display = 'none'; }
    }

    function showError(id: string, msg: string): void {
        const el = document.getElementById(id);
        if (el) { el.textContent = msg; el.style.display = 'block'; }
    }

    function hideError(id: string): void {
        const el = document.getElementById(id);
        if (el) { el.style.display = 'none'; el.textContent = ''; }
    }

    function showSuccess(id: string, msg: string): void {
        const el = document.getElementById(id);
        if (el) { el.textContent = msg; el.style.display = 'block'; }
    }

    // Close on overlay click
    document.querySelectorAll<HTMLElement>('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', (e) => {
            if (e.target === overlay) overlay.style.display = 'none';
        });
    });

    // Generic close buttons
    document.querySelectorAll<HTMLButtonElement>('.pd-modal-close').forEach(btn => {
        btn.addEventListener('click', () => {
            const target = btn.getAttribute('data-target');
            if (target) closeModal(target);
        });
    });

    // ── Task actions (Modifier / Clôturer) ──────────────────────────────────
    document.addEventListener('click', (e) => {
        const editBtn = (e.target as HTMLElement).closest('.pd-edit-task-btn');
        if (editBtn) {
            e.preventDefault();
            const taskId = editBtn.getAttribute('data-task-id');
            if (taskId) editTask(taskId);
        }

        const closeBtn = (e.target as HTMLElement).closest('.pd-close-task-btn');
        if (closeBtn) {
            e.preventDefault();
            const taskId = closeBtn.getAttribute('data-task-id');
            if (taskId) openCloseTaskModal(taskId);
        }
    });

    // ── Task filter pills ────────────────────────────────────────────────────
    const filterBar = document.getElementById('pd-filter-bar');
    const taskCards = document.querySelectorAll<HTMLElement>('.pd-task-card');

    filterBar?.querySelectorAll<HTMLButtonElement>('.pd-filter-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            filterBar.querySelectorAll('.pd-filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filter = btn.getAttribute('data-filter') ?? 'all';
            taskCards.forEach(card => {
                const cat = card.getAttribute('data-state-cat') ?? 'in_progress';
                card.style.display = (filter === 'all' || cat === filter) ? 'flex' : 'none';
            });
        });
    });

    // ── Project options menu (⋮) ─────────────────────────────────────────────
    const optionsBtn  = document.getElementById('pd-options-btn');
    const optionsMenu = document.getElementById('pd-options-menu');

    if (optionsBtn && optionsMenu) {
        optionsBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            const isOpen = optionsMenu.style.display !== 'none';
            optionsMenu.style.display = isOpen ? 'none' : 'block';
            optionsBtn.setAttribute('aria-expanded', String(!isOpen));
        });

        document.addEventListener('click', () => {
            optionsMenu.style.display = 'none';
            optionsBtn.setAttribute('aria-expanded', 'false');
        });
    }

    document.getElementById('pd-menu-edit')?.addEventListener('click', () => {
        if (optionsMenu) optionsMenu.style.display = 'none';
        editProject(projectId);
    });

    document.getElementById('pd-menu-delete')?.addEventListener('click', () => {
        if (optionsMenu) optionsMenu.style.display = 'none';
        openModal('pd-modal-delete-project');
    });

    // ── "Nouvelle tâche" modal ───────────────────────────────────────────────
    document.getElementById('btn-new-task')?.addEventListener('click', () => {
        createNewTask({ projectId, projectName: pdData.projectName });
    });

    document.getElementById('nt-submit')?.addEventListener('click', async () => {
        hideError('nt-error');

        const name    = (document.getElementById('nt-name')        as HTMLInputElement)?.value.trim();
        const desc    = (document.getElementById('nt-description')  as HTMLTextAreaElement)?.value.trim();
        const effort  = (document.getElementById('nt-effort')       as HTMLInputElement)?.value.trim();
        const prio    = (document.getElementById('nt-priority')     as HTMLSelectElement)?.value;
        const stateId = (document.getElementById('nt-state')        as HTMLSelectElement)?.value;
        const devId   = (document.getElementById('nt-developer')    as HTMLSelectElement)?.value;
        const begin   = (document.getElementById('nt-begin')        as HTMLInputElement)?.value;
        const end     = (document.getElementById('nt-end')          as HTMLInputElement)?.value;

        if (!name)   { showError('nt-error', 'Le nom est obligatoire.'); return; }
        if (!effort || parseFloat(effort) <= 0) { showError('nt-error', "L'effort requis doit être > 0."); return; }
        if (!end)    { showError('nt-error', "L'échéance est obligatoire."); return; }

        const btn = document.getElementById('nt-submit') as HTMLButtonElement;
        btn.disabled = true;

        try {
            const body: Record<string, unknown> = {
                name,
                effortRequired : parseFloat(effort),
                projectId,
                theoricalEndDate: end,
            };
            if (desc)    body.description = desc;
            if (prio)    body.priority    = prio;
            if (stateId) body.stateId     = stateId;
            if (devId)   body.developerId = devId;
            if (begin)   body.beginDate   = begin;

            const res = await fetch('/api/add/task', {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
                body   : JSON.stringify(body),
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok || !(data as any).success) {
                showError('nt-error', (data as any).error ?? 'Erreur lors de la création.');
            } else {
                closeModal('pd-modal-new-task');
                window.location.reload();
            }
        } catch {
            showError('nt-error', 'Erreur réseau. Veuillez réessayer.');
        } finally {
            btn.disabled = false;
        }
    });

    // ── Absence check cache ──────────────────────────────────────────────────
    let absentUserIds: Set<string> = new Set();
    async function loadActiveAbsences(): Promise<void> {
        try {
            const res  = await fetch('/api/absences/active');
            const data = await res.json().catch(() => ({}));
            if (data.success && Array.isArray(data.absences)) {
                absentUserIds = new Set(data.absences.map((a: {user_id: string}) => a.user_id));
            }
        } catch { /* non-blocking */ }
    }

    // ── "Ajouter un membre" modal ────────────────────────────────────────────
    document.getElementById('btn-add-member')?.addEventListener('click', async () => {
        hideError('am-error');
        hideError('am-success');
        await loadActiveAbsences();
        openModal('pd-modal-add-member');
    });

    // Absence warning when selecting a user
    (document.getElementById('am-user') as HTMLSelectElement | null)?.addEventListener('change', () => {
        const userId = (document.getElementById('am-user') as HTMLSelectElement)?.value;
        if (userId && absentUserIds.has(userId)) {
            showError('am-error', '⚠️ Ce collaborateur est actuellement en absence. Vous pouvez quand même l\'assigner.');
        } else {
            hideError('am-error');
        }
    });

    // Also check in the new task developer select
    (document.getElementById('nt-developer') as HTMLSelectElement | null)?.addEventListener('change', async () => {
        if (absentUserIds.size === 0) await loadActiveAbsences();
        const userId = (document.getElementById('nt-developer') as HTMLSelectElement)?.value;
        if (userId && absentUserIds.has(userId)) {
            showError('nt-error', '⚠️ Ce collaborateur est actuellement en absence. Vous pouvez quand même l\'assigner.');
        } else {
            hideError('nt-error');
        }
    });

    document.getElementById('am-submit')?.addEventListener('click', async () => {
        hideError('am-error');
        hideError('am-success');

        const taskId = (document.getElementById('am-task') as HTMLSelectElement)?.value;
        const userId = (document.getElementById('am-user') as HTMLSelectElement)?.value;

        if (!taskId) { showError('am-error', 'Veuillez sélectionner une tâche.'); return; }
        if (!userId) { showError('am-error', 'Veuillez sélectionner un utilisateur.'); return; }

        const btn = document.getElementById('am-submit') as HTMLButtonElement;
        btn.disabled = true;

        try {
            const res = await fetch(`/api/edit/task/${encodeURIComponent(taskId)}`, {
                method : 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
                body   : JSON.stringify({ developerId: userId }),
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok || !(data as any).success) {
                showError('am-error', (data as any).error ?? 'Erreur lors de l\'assignation.');
            } else {
                showSuccess('am-success', 'Membre assigné avec succès !');
                setTimeout(() => {
                    closeModal('pd-modal-add-member');
                    window.location.reload();
                }, 1200);
            }
        } catch {
            showError('am-error', 'Erreur réseau. Veuillez réessayer.');
        } finally {
            btn.disabled = false;
        }
    });


    // ── Delete project modal ─────────────────────────────────────────────────
    document.getElementById('dp-submit')?.addEventListener('click', async () => {
        hideError('dp-error');

        const btn = document.getElementById('dp-submit') as HTMLButtonElement;
        btn.disabled = true;

        try {
            const res = await fetch(`/api/delete/project/${encodeURIComponent(projectId)}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-Token': getCsrfToken() },
            });

            const data = await res.json().catch(() => ({}));
            if (!res.ok || !(data as any).success) {
                showError('dp-error', (data as any).error ?? 'Erreur lors de la suppression.');
                btn.disabled = false;
            } else {
                window.location.href = '/projects';
            }
        } catch {
            showError('dp-error', 'Erreur réseau. Veuillez réessayer.');
            btn.disabled = false;
        }
    });
});
