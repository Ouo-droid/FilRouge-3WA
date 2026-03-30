import { getCsrfToken } from "../services/Api";

document.addEventListener('DOMContentLoaded', function() {
    const monthYearLabel = document.getElementById('calendar-month-year');
    const datepicker = document.getElementById('calendar-datepicker') as HTMLInputElement;
    const grid = document.getElementById('calendar-days-grid');
    const prevBtn = document.getElementById('prev-week');
    const nextBtn = document.getElementById('next-week');

    // Only run if the calendar widget is present (i.e. on the home page)
    if (monthYearLabel && datepicker && grid && prevBtn && nextBtn) {

        let currentDate = new Date();
        let selectedDate = new Date();

        const monthNames = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        const dayNames = ["D", "L", "M", "M", "J", "V", "S"];

        const updateCalendar = function() {
            // Update Title (Month Year)
            const span = monthYearLabel?.querySelector('span');
            if (span) {
                span.textContent = `${monthNames[currentDate.getMonth()]} ${currentDate.getFullYear()}`;
            }

            // Clear Grid
            if (grid) {
                grid.innerHTML = '';

                // Calculate Start of the week (Sunday)
                const startOfWeek = new Date(currentDate);
                startOfWeek.setDate(currentDate.getDate() - currentDate.getDay());

                for (let i = 0; i < 7; i++) {
                    const dayDate = new Date(startOfWeek);
                    dayDate.setDate(startOfWeek.getDate() + i);

                    const col = document.createElement('div');
                    col.className = 'calendar-day-col';
                    if (dayDate.toDateString() === selectedDate.toDateString()) {
                        col.classList.add('active');
                    }

                    const nameDiv = document.createElement('div');
                    nameDiv.className = 'calendar-day-name';
                    nameDiv.textContent = dayNames[dayDate.getDay()];

                    const numDiv = document.createElement('div');
                    numDiv.className = 'calendar-day-num';
                    numDiv.textContent = dayDate.getDate().toString();

                    col.appendChild(nameDiv);
                    col.appendChild(numDiv);

                    col.addEventListener('click', () => {
                        selectedDate = new Date(dayDate);
                        currentDate = new Date(dayDate); // Sync current view with selected day
                        updateCalendar();
                    });

                    grid.appendChild(col);
                }
            }
        }

        prevBtn.addEventListener('click', () => {
            currentDate.setDate(currentDate.getDate() - 7);
            updateCalendar();
        });

        nextBtn.addEventListener('click', () => {
            currentDate.setDate(currentDate.getDate() + 7);
            updateCalendar();
        });

        monthYearLabel.addEventListener('click', () => {
            if (datepicker && 'showPicker' in datepicker) {
                (datepicker as any).showPicker();
            } else {
                (datepicker as HTMLInputElement).focus();
            }
        });

        datepicker.addEventListener('change', (e) => {
            const target = e.target as HTMLInputElement;
            const newDate = new Date(target.value);
            if (!isNaN(newDate.getTime())) {
                currentDate = new Date(newDate);
                selectedDate = new Date(newDate);
                updateCalendar();
            }
        });

        // Initialize
        updateCalendar();
    }

    // Gestion des clics sur les liens de navigation (Sidebar)
    const sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
    if (sidebarLinks.length > 0) {
        sidebarLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                const href = link.getAttribute('href');
                if (href && href.startsWith('/')) {
                    return;
                }
                e.preventDefault();
                sidebarLinks.forEach(l => l.classList.remove('active'));
                link.classList.add('active');
            });
        });
    }

    // ── Effort modal ──────────────────────────────────────────────────────────
    const addTaskBtn      = document.querySelector('.btn-add-task');
    const effortModal     = document.getElementById('effort-modal');
    const effortClose     = document.getElementById('effort-modal-close');
    const effortCancel    = document.getElementById('effort-modal-cancel');
    const effortSubmit    = document.getElementById('effort-modal-submit');
    const effortSelect    = document.getElementById('effort-task-select') as HTMLSelectElement | null;
    const effortInput     = document.getElementById('effort-value')       as HTMLInputElement  | null;
    const effortError     = document.getElementById('effort-error');
    const effortSuccess   = document.getElementById('effort-success');

    function openEffortModal(): void {
        if (!effortModal) return;
        if (effortSelect) effortSelect.value = '';
        if (effortInput)  effortInput.value  = '';
        hideEffortFeedback();
        effortModal.style.display = 'flex';
        effortSelect?.focus();
    }

    function closeEffortModal(): void {
        if (effortModal) effortModal.style.display = 'none';
    }

    function hideEffortFeedback(): void {
        if (effortError)   { effortError.style.display   = 'none'; effortError.textContent   = ''; }
        if (effortSuccess) { effortSuccess.style.display = 'none'; effortSuccess.textContent = ''; }
    }

    function showEffortError(msg: string): void {
        if (effortError) { effortError.textContent = msg; effortError.style.display = 'block'; }
        if (effortSuccess) effortSuccess.style.display = 'none';
    }

    function showEffortSuccess(msg: string): void {
        if (effortSuccess) { effortSuccess.textContent = msg; effortSuccess.style.display = 'block'; }
        if (effortError) effortError.style.display = 'none';
    }

    // Pre-fill effort when a task is selected
    effortSelect?.addEventListener('change', () => {
        if (!effortSelect || !effortInput) return;
        const opt = effortSelect.selectedOptions[0];
        const existing = opt?.getAttribute('data-effort') ?? '';
        effortInput.value = existing;
        hideEffortFeedback();
    });

    addTaskBtn?.addEventListener('click', openEffortModal);
    effortClose?.addEventListener('click', closeEffortModal);
    effortCancel?.addEventListener('click', closeEffortModal);

    // Close on overlay click
    effortModal?.addEventListener('click', (e) => {
        if (e.target === effortModal) closeEffortModal();
    });

    effortSubmit?.addEventListener('click', async () => {
        if (!effortSelect || !effortInput) return;

        const taskId = effortSelect.value.trim();
        const effort = effortInput.value.trim();

        if (!taskId) { showEffortError('Veuillez sélectionner une tâche.'); return; }
        if (effort === '' || isNaN(parseFloat(effort)) || parseFloat(effort) <= 0) {
            showEffortError('Veuillez saisir un effort valide (> 0).'); return;
        }

        if (effortSubmit instanceof HTMLButtonElement) effortSubmit.disabled = true;

        try {
            const res = await fetch(`/api/edit/task/${encodeURIComponent(taskId)}`, {
                method : 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
                body   : JSON.stringify({ effortMade: parseFloat(effort) }),
            });

            if (!res.ok) {
                const data = await res.json().catch(() => ({}));
                showEffortError((data as any).message ?? 'Erreur lors de la mise à jour.');
            } else {
                showEffortSuccess('Effort enregistré avec succès !');
                // Update data-effort attribute on option so re-open pre-fills correctly
                const opt = effortSelect.selectedOptions[0];
                if (opt) opt.setAttribute('data-effort', effort);
                setTimeout(closeEffortModal, 1500);
            }
        } catch {
            showEffortError('Erreur réseau. Veuillez réessayer.');
        } finally {
            if (effortSubmit instanceof HTMLButtonElement) effortSubmit.disabled = false;
        }
    });

    // ── Modal assignation tâche (CDP) ────────────────────────────────────────
    const assignModal    = document.getElementById('cdp-assign-modal');
    const assignClose    = document.getElementById('cdp-assign-close');
    const assignCancel   = document.getElementById('cdp-assign-cancel');
    const assignSubmit   = document.getElementById('cdp-assign-submit') as HTMLButtonElement | null;
    const assignSearch   = document.getElementById('cdp-assign-search') as HTMLInputElement | null;
    const assignResults  = document.getElementById('cdp-assign-results');
    const assignSelected = document.getElementById('cdp-assign-selected');
    const assignSelName  = document.getElementById('cdp-assign-selected-name');
    const assignClear    = document.getElementById('cdp-assign-clear');
    const assignError    = document.getElementById('cdp-assign-error');

    let assignTaskId    = '';
    let assignUserId    = '';
    let allUsers: {id: string; name: string}[] = [];

    async function loadUsers(): Promise<void> {
        if (allUsers.length) return;
        try {
            const res  = await fetch('/api/users');
            const data = await res.json().catch(() => ({}));
            const raw  = (data as any).data ?? [];
            allUsers   = raw.map((u: any) => ({ id: u.id, name: `${u.firstname} ${u.lastname}` }));
        } catch { /* non-bloquant */ }
    }

    function openAssignModal(btn: HTMLElement): void {
        if (!assignModal) return;
        assignTaskId = btn.getAttribute('data-task-id') ?? '';
        assignUserId = '';

        (document.getElementById('cdp-assign-task-name') as HTMLElement).textContent  = btn.getAttribute('data-task-name') ?? '';
        (document.getElementById('cdp-assign-task-project') as HTMLElement).textContent = btn.getAttribute('data-task-project') ?? '';
        const descEl = document.getElementById('cdp-assign-task-desc') as HTMLElement;
        const desc = btn.getAttribute('data-task-desc') ?? '';
        descEl.textContent = desc || 'Aucune description.';

        const deadlineEl = document.getElementById('cdp-assign-task-deadline') as HTMLElement;
        const deadline   = btn.getAttribute('data-task-deadline') ?? '';
        if (deadline) { deadlineEl.querySelector('.val')!.textContent = deadline; deadlineEl.style.display = ''; }
        else deadlineEl.style.display = 'none';

        const effortEl = document.getElementById('cdp-assign-task-effort') as HTMLElement;
        const effort   = btn.getAttribute('data-task-effort') ?? '';
        if (effort) { effortEl.querySelector('.val')!.textContent = effort; effortEl.style.display = ''; }
        else effortEl.style.display = 'none';

        const prioEl  = document.getElementById('cdp-assign-task-priority') as HTMLElement;
        const priority = btn.getAttribute('data-task-priority') ?? '';
        if (priority) {
            prioEl.innerHTML = `<span class="badge bg-danger bg-opacity-10 text-danger border border-danger" style="font-size:.72rem;">${priority}</span>`;
            prioEl.style.display = '';
        } else prioEl.style.display = 'none';

        if (assignSearch)   assignSearch.value = '';
        if (assignResults)  { assignResults.innerHTML = ''; assignResults.style.display = 'none'; }
        if (assignSelected) assignSelected.style.display = 'none';
        if (assignSubmit)   assignSubmit.disabled = true;
        if (assignError)    { assignError.textContent = ''; assignError.style.display = 'none'; }

        assignModal.style.display = 'flex';
        loadUsers().then(() => assignSearch?.focus());
    }

    function closeAssignModal(): void {
        if (assignModal) assignModal.style.display = 'none';
    }

    function selectUser(id: string, name: string): void {
        assignUserId = id;
        if (assignSelName)  assignSelName.textContent = name;
        if (assignSelected) assignSelected.style.display = 'flex';
        if (assignResults)  { assignResults.innerHTML = ''; assignResults.style.display = 'none'; }
        if (assignSearch)   assignSearch.style.display = 'none';
        if (assignSubmit)   assignSubmit.disabled = false;
    }

    assignSearch?.addEventListener('input', () => {
        const q = assignSearch!.value.trim().toLowerCase();
        if (!assignResults) return;
        if (!q) { assignResults.style.display = 'none'; return; }
        const filtered = allUsers.filter(u => u.name.toLowerCase().includes(q)).slice(0, 8);
        assignResults.innerHTML = filtered.map(u =>
            `<div class="cdp-assign-result-item" data-id="${u.id}" data-name="${u.name}">${u.name}</div>`
        ).join('') || '<div class="cdp-assign-result-item text-muted">Aucun résultat</div>';
        assignResults.style.display = 'block';
    });

    assignResults?.addEventListener('click', (e) => {
        const item = (e.target as HTMLElement).closest<HTMLElement>('.cdp-assign-result-item');
        if (!item || !item.dataset.id) return;
        selectUser(item.dataset.id, item.dataset.name ?? '');
    });

    assignClear?.addEventListener('click', () => {
        assignUserId = '';
        if (assignSelected) assignSelected.style.display = 'none';
        if (assignSearch)   { assignSearch.style.display = ''; assignSearch.value = ''; assignSearch.focus(); }
        if (assignSubmit)   assignSubmit.disabled = true;
    });

    assignClose?.addEventListener('click',  closeAssignModal);
    assignCancel?.addEventListener('click', closeAssignModal);
    assignModal?.addEventListener('click', (e) => { if (e.target === assignModal) closeAssignModal(); });

    assignSubmit?.addEventListener('click', async () => {
        if (!assignTaskId || !assignUserId) return;
        assignSubmit.disabled = true;

        try {
            const res  = await fetch(`/api/edit/task/${encodeURIComponent(assignTaskId)}`, {
                method : 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
                body   : JSON.stringify({ developerId: assignUserId }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !(data as any).success) {
                if (assignError) { assignError.textContent = (data as any).error ?? 'Erreur lors de l\'assignation.'; assignError.style.display = 'block'; }
                assignSubmit.disabled = false;
            } else {
                closeAssignModal();
                window.location.reload();
            }
        } catch {
            if (assignError) { assignError.textContent = 'Erreur réseau.'; assignError.style.display = 'block'; }
            assignSubmit.disabled = false;
        }
    });

    document.querySelectorAll<HTMLElement>('.cdp-assign-btn').forEach(btn => {
        btn.addEventListener('click', () => openAssignModal(btn));
    });

    // ── Demande d'absence (collaborateur) ────────────────────────────────────
    const FOCUSABLE = 'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])';

    function trapFocus(modal: HTMLElement, e: KeyboardEvent): void {
        const items = Array.from(modal.querySelectorAll<HTMLElement>(FOCUSABLE));
        if (!items.length) return;
        const first = items[0];
        const last  = items[items.length - 1];
        if (e.shiftKey) {
            if (document.activeElement === first) { e.preventDefault(); last.focus(); }
        } else {
            if (document.activeElement === last)  { e.preventDefault(); first.focus(); }
        }
    }

    const arBtn    = document.getElementById('btn-request-absence');
    const arModal  = document.getElementById('absence-request-modal');
    const arClose  = document.getElementById('absence-request-close');
    const arCancel = document.getElementById('absence-request-cancel');
    const arSubmit = document.getElementById('absence-request-submit') as HTMLButtonElement | null;
    const arStart  = document.getElementById('ar-start')   as HTMLInputElement | null;
    const arEnd    = document.getElementById('ar-end')     as HTMLInputElement | null;
    const arReason = document.getElementById('ar-reason')  as HTMLInputElement | null;
    const arError  = document.getElementById('ar-error');
    const arSuccess= document.getElementById('ar-success');

    function openAbsenceRequestModal(): void {
        if (!arModal) return;
        if (arStart)  arStart.value  = '';
        if (arEnd)    arEnd.value    = '';
        if (arReason) arReason.value = '';
        if (arError)  { arError.style.display  = 'none'; arError.textContent  = ''; }
        if (arSuccess){ arSuccess.style.display = 'none'; arSuccess.textContent= ''; }
        arModal.style.display = 'flex';
        arStart?.focus();
    }

    function closeAbsenceRequestModal(): void {
        if (arModal) arModal.style.display = 'none';
        (arBtn as HTMLElement | null)?.focus();
    }

    arBtn?.addEventListener('click', openAbsenceRequestModal);
    arClose?.addEventListener('click', closeAbsenceRequestModal);
    arCancel?.addEventListener('click', closeAbsenceRequestModal);
    arModal?.addEventListener('click', (e) => { if (e.target === arModal) closeAbsenceRequestModal(); });
    arModal?.addEventListener('keydown', (e: KeyboardEvent) => {
        if (e.key === 'Escape') { closeAbsenceRequestModal(); return; }
        if (e.key === 'Tab' && arModal)    trapFocus(arModal as HTMLElement, e);
    });

    // Sync min date de fin sur changement date de début
    arStart?.addEventListener('change', () => {
        if (arEnd && arStart.value) arEnd.min = arStart.value;
    });

    arSubmit?.addEventListener('click', async () => {
        if (arError)  { arError.style.display  = 'none'; arError.textContent  = ''; }
        if (arSuccess){ arSuccess.style.display = 'none'; arSuccess.textContent= ''; }

        const start  = arStart?.value  ?? '';
        const end    = arEnd?.value    ?? '';
        const reason = arReason?.value.trim() ?? '';

        if (!start) { if (arError) { arError.textContent = 'La date de début est obligatoire.'; arError.style.display = 'block'; } return; }
        if (!end)   { if (arError) { arError.textContent = 'La date de fin est obligatoire.';   arError.style.display = 'block'; } return; }
        if (end < start) { if (arError) { arError.textContent = 'La date de fin doit être après la date de début.'; arError.style.display = 'block'; } return; }

        if (arSubmit) arSubmit.disabled = true;

        try {
            const res  = await fetch('/api/request/my-absence', {
                method : 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
                body   : JSON.stringify({ startDate: start, endDate: end, reason: reason || null }),
            });
            const data = await res.json().catch(() => ({}));
            if (!res.ok || !(data as any).success) {
                if (arError) { arError.textContent = (data as any).error ?? 'Erreur lors de l\'envoi.'; arError.style.display = 'block'; }
            } else {
                if (arSuccess) { arSuccess.textContent = 'Demande envoyée avec succès !'; arSuccess.style.display = 'block'; }
                setTimeout(closeAbsenceRequestModal, 1800);
            }
        } catch {
            if (arError) { arError.textContent = 'Erreur réseau. Veuillez réessayer.'; arError.style.display = 'block'; }
        } finally {
            if (arSubmit) arSubmit.disabled = false;
        }
    });
});
