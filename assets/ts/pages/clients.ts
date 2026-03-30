import { escapeHtml } from "../utils/helpers";
import { getCsrfToken } from "../services/Api";

interface ClientData {
    siret: string;
    companyName: string;
    workfield: string;
    contactFirstname: string;
    contactLastname: string;
    contactEmail: string;
    contactPhone: string;
    address?: {
        id?: string;
        streetNumber: string;
        streetLetter: string;
        streetName: string;
        postCode: string;
        state: string;
        city: string;
        country: string;
    };
}

document.addEventListener('DOMContentLoaded', function() {
    // Only run if we are on the clients page
    if (!document.querySelector('.clients-page')) {
        return;
    }

    // Gestion des clics délégués pour les actions dynamiques (édition, suppression)
    document.addEventListener('click', function(e) {
        const target = e.target as HTMLElement;

        // View button
        const viewBtn = target.closest('.view-client-btn');
        if (viewBtn) {
            e.preventDefault();
            const siret = viewBtn.getAttribute('data-siret');
            if (siret) viewClient(siret);
        }

        // Edit button
        const editBtn = target.closest('.edit-client-btn');
        if (editBtn) {
            e.preventDefault();
            const siret = editBtn.getAttribute('data-siret');
            if (siret) editClient(siret);
        }

        // Delete button
        const deleteBtn = target.closest('.delete-client-btn');
        if (deleteBtn) {
            e.preventDefault();
            const siret = deleteBtn.getAttribute('data-siret');
            const name = deleteBtn.getAttribute('data-name');
            if (siret) deleteClient(siret, name || '');
        }
    });

    // Recherche en temps réel
    const searchInput = document.getElementById('client-search') as HTMLInputElement;
    const clientRows = document.querySelectorAll('.client-row') as NodeListOf<HTMLElement>;

    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const target = e.target as HTMLInputElement;
            const searchTerm = target.value.toLowerCase();

            clientRows.forEach(row => {
                const rowText = row.getAttribute('data-search-term');
                if (rowText && rowText.includes(searchTerm)) {
                    row.style.display = 'grid'; // Ou flex selon le CSS
                } else {
                    row.style.display = 'none';
                }
            });
        });
    }

    // Bouton créer client
    const createBtn = document.getElementById('create-client-btn');
    if (createBtn) {
        createBtn.addEventListener('click', function() {
            createNewClient();
        });
    }
});

function showClientToast(message: string, type: 'success' | 'error' = 'success') {
    const toast = document.createElement('div');
    toast.className = `client-toast client-toast--${type}`;
    toast.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'building' : 'exclamation-triangle'}"></i>
        <span>${message}</span>
    `;
    document.body.appendChild(toast);

    requestAnimationFrame(() => toast.classList.add('client-toast--visible'));

    setTimeout(() => {
        toast.classList.remove('client-toast--visible');
        toast.addEventListener('transitionend', () => toast.remove());
    }, 3500);
}

async function deleteClient(numSIRET: string, clientName: string) {
    if (confirm(`Êtes-vous sûr de vouloir supprimer le client "${clientName}" ?`)) {
        try {
            const response = await fetch(`/api/delete/client/${numSIRET}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-Token': getCsrfToken() },
            });

            const result = await response.json();

            if (result.success || result.delete) {
                showClientToast('Client supprimé avec succès !', 'success');
                setTimeout(() => location.reload(), 1200);
            } else {
                showClientToast(result.error || 'Erreur lors de la suppression du client', 'error');
            }
        } catch (error) {
            console.error('Erreur:', error);
            showClientToast('Erreur lors de la suppression du client', 'error');
        }
    }
}

async function createNewClient() {
    showFormModal('Créer un nouveau client', null);
}

function editClient(numSIRET: string) {
    fetch(`/api/client/${numSIRET}`)
        .then(response => response.json())
        .then(data => {
            const client = data.data ?? data.client ?? null;
            if (client) {
                showFormModal('Modifier le client', client);
            } else {
                showClientToast('Erreur lors de la récupération du client', 'error');
            }
        })
        .catch(error => {
            console.error('Erreur:', error);
            showClientToast('Erreur lors de la récupération du client', 'error');
        });
}

function showFormModal(title: string, client: ClientData | null) {
    const isEdit = !!client;
    const formOverlay = document.createElement('div');
    formOverlay.className = 'form-overlay';

    // Note: Styles are now in assets/styles/components/_modals.scss

    const values = {
        siret: client ? client.siret : '',
        companyName: client ? client.companyName : '',
        workfield: client ? client.workfield : '',
        contactFirstname: client ? client.contactFirstname : '',
        contactLastname: client ? client.contactLastname : '',
        contactEmail: client ? client.contactEmail : '',
        contactPhone: client ? client.contactPhone : '',
        streetNumber: client?.address ? client.address.streetNumber : '',
        streetLetter: client?.address ? client.address.streetLetter : '',
        streetName: client?.address ? client.address.streetName : '',
        postCode: client?.address ? client.address.postCode : '',
        state: client?.address ? client.address.state : '',
        city: client?.address ? client.address.city : '',
        country: client?.address ? client.address.country : '',
    };

    formOverlay.innerHTML = `
        <div class="edit-form">
            <div class="form-header">
                <h3>${title}</h3>
                <button class="btn-close" type="button">×</button>
            </div>
            <form id="client-form">
                <div class="form-content">

                    <!-- Section Entreprise -->
                    <div class="form-section form-section--company">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-building"></i></span>
                            <span class="form-section__label">Entreprise</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-group">
                                <label for="siret">Siren / Siret *</label>
                                <div class="siren-input-group">
                                    <input type="text" id="siret" name="siret" value="${escapeHtml(values.siret)}"
                                        ${isEdit ? 'readonly disabled' : 'required maxlength="14" pattern="[0-9]{14}" title="14 chiffres SIRET requis (utilisez le bouton loupe pour chercher par SIREN)"'}
                                        class="${isEdit ? 'bg-light' : ''}">
                                    ${!isEdit ? '<button type="button" id="siren-lookup-btn" class="btn-siren-lookup" title="Rechercher l\'entreprise par SIREN (9 chiffres) ou SIRET (14 chiffres)"><i class="fas fa-search"></i></button>' : ''}
                                </div>
                                ${isEdit ? '<small class="text-muted">Le SIRET ne peut pas être modifié</small>' : ''}
                            </div>
                            <div class="form-group">
                                <label for="companyName">Nom de l'entreprise *</label>
                                <input type="text" id="companyName" name="companyName" value="${escapeHtml(values.companyName)}" required>
                            </div>
                            <div class="form-group">
                                <label for="workfield">Domaine d'activité</label>
                                <input type="text" id="workfield" name="workfield" value="${escapeHtml(values.workfield)}">
                            </div>
                        </div>
                    </div>

                    <!-- Section Contact -->
                    <div class="form-section form-section--contact">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-user"></i></span>
                            <span class="form-section__label">Contact</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contactFirstname">Prénom *</label>
                                    <input type="text" id="contactFirstname" name="contactFirstname" value="${escapeHtml(values.contactFirstname)}" required>
                                </div>
                                <div class="form-group">
                                    <label for="contactLastname">Nom *</label>
                                    <input type="text" id="contactLastname" name="contactLastname" value="${escapeHtml(values.contactLastname)}" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="contactEmail">Email *</label>
                                    <input type="email" id="contactEmail" name="contactEmail" value="${escapeHtml(values.contactEmail)}" required>
                                </div>
                                <div class="form-group">
                                    <label for="contactPhone">Téléphone</label>
                                    <input type="tel" id="contactPhone" name="contactPhone" value="${escapeHtml(values.contactPhone)}">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section Adresse -->
                    <div class="form-section form-section--address">
                        <div class="form-section__header">
                            <span class="form-section__icon"><i class="fas fa-map-marker-alt"></i></span>
                            <span class="form-section__label">Adresse</span>
                        </div>
                        <div class="form-section__body">
                            <div class="form-row form-row--address">
                                <div class="form-group form-group-small">
                                    <label for="streetNumber">N° *</label>
                                    <input type="number" id="streetNumber" name="streetNumber" value="${escapeHtml(values.streetNumber)}" required min="1">
                                </div>
                                <div class="form-group form-group-small">
                                    <label for="streetLetter">Complément</label>
                                    <input type="text" id="streetLetter" name="streetLetter" value="${escapeHtml(values.streetLetter)}" placeholder="bis, ter...">
                                </div>
                                <div class="form-group form-group-large">
                                    <label for="streetName">Rue *</label>
                                    <input type="text" id="streetName" name="streetName" value="${escapeHtml(values.streetName)}" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="postCode">Code postal *</label>
                                    <input type="text" id="postCode" name="postCode" value="${escapeHtml(values.postCode)}" required>
                                </div>
                                <div class="form-group">
                                    <label for="city">Ville *</label>
                                    <input type="text" id="city" name="city" value="${escapeHtml(values.city)}" required>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="state">Région</label>
                                    <input type="text" id="state" name="state" value="${escapeHtml(values.state)}">
                                </div>
                                <div class="form-group">
                                    <label for="country">Pays</label>
                                    <input type="text" id="country" name="country" value="${escapeHtml(values.country)}">
                                </div>
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

    // Event listeners
    const form = formOverlay.querySelector('#client-form') as HTMLFormElement;
    const closeBtn = formOverlay.querySelector('.btn-close');
    const cancelBtn = formOverlay.querySelector('.btn-cancel');

    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();
            const formData = new FormData(form);
            const flat: any = {};
            formData.forEach((value, key) => {
                flat[key] = value;
            });

            // Structurer les données : client + adresse imbriquée
            const data: any = {
                siret: flat.siret,
                companyName: flat.companyName,
                workfield: flat.workfield,
                contactFirstname: flat.contactFirstname,
                contactLastname: flat.contactLastname,
                contactEmail: flat.contactEmail,
                contactPhone: flat.contactPhone,
                address: {
                    streetNumber: flat.streetNumber,
                    streetLetter: flat.streetLetter,
                    streetName: flat.streetName,
                    postCode: flat.postCode,
                    state: flat.state,
                    city: flat.city,
                    country: flat.country,
                }
            };

            if (isEdit && client) {
                data.siret = client.siret;
                await handleEditSubmit(client.siret, data, formOverlay);
            } else {
                await handleCreateSubmit(data, formOverlay);
            }
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

    if (!isEdit) {
        const lookupBtn = formOverlay.querySelector('#siren-lookup-btn') as HTMLButtonElement;
        const siretInput = formOverlay.querySelector('#siret') as HTMLInputElement;
        if (lookupBtn && siretInput) {
            lookupBtn.addEventListener('click', async () => {
                const val = siretInput.value.trim();
                if (val === '' || !/^\d+$/.test(val)) {
                    showClientToast('Veuillez saisir un numéro SIREN (9 chiffres) ou SIRET (14 chiffres)', 'error');
                    return;
                }
                if (val.length !== 9 && val.length !== 14) {
                    showClientToast('Le numéro doit faire 9 chiffres (SIREN) ou 14 chiffres (SIRET)', 'error');
                    return;
                }

                lookupBtn.disabled = true;
                lookupBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';

                try {
                    const res = await fetch(`/api/siren-lookup?q=${encodeURIComponent(val)}`);
                    const result = await res.json();

                    if (!res.ok || !result.success) {
                        showClientToast(result.error || 'Entreprise non trouvée', 'error');
                        return;
                    }

                    const d = result.data;
                    const fill = (id: string, value: string | null | undefined) => {
                        const el = formOverlay.querySelector(`#${id}`) as HTMLInputElement | null;
                        if (el && value != null && value !== '') el.value = value;
                    };

                    fill('companyName', d.companyName);
                    fill('streetNumber', d.streetNumber);
                    fill('streetLetter', d.streetLetter);
                    fill('streetName', d.streetName);
                    fill('postCode', d.postCode);
                    fill('city', d.city);
                    fill('country', d.country);

                    showClientToast('Informations récupérées avec succès', 'success');
                } catch {
                    showClientToast('Erreur lors de la recherche SIREN/SIRET', 'error');
                } finally {
                    lookupBtn.disabled = false;
                    lookupBtn.innerHTML = '<i class="fas fa-search"></i>';
                }
            });
        }
    }
}

async function handleCreateSubmit(data: any, formOverlay: HTMLElement) {
    try {
        const response = await fetch('/api/add/client', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            document.body.removeChild(formOverlay);
            showClientToast('Client créé avec succès !', 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            showClientToast('Erreur : ' + (result.error || result.message), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showClientToast('Erreur lors de la création du client', 'error');
    }
}

async function handleEditSubmit(numSIRET: string, data: any, formOverlay: HTMLElement) {
    try {
        const response = await fetch(`/api/edit/client/${numSIRET}`, {
            method: 'PUT',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': getCsrfToken() },
            body: JSON.stringify(data)
        });

        const result = await response.json();

        if (result.success) {
            document.body.removeChild(formOverlay);
            showClientToast('Client modifié avec succès !', 'success');
            setTimeout(() => location.reload(), 1200);
        } else {
            showClientToast('Erreur : ' + (result.error || result.message), 'error');
        }
    } catch (error) {
        console.error('Erreur:', error);
        showClientToast('Erreur lors de la modification du client', 'error');
    }
}

function viewClient(siret: string) {
    fetch(`/api/client/${siret}`)
        .then(res => res.json())
        .then(data => {
            const client = data.data ?? data.client ?? null;
            if (client) {
                showClientDetailModal(client);
            } else {
                showClientToast('Erreur lors de la récupération du client', 'error');
            }
        })
        .catch(() => showClientToast('Erreur lors de la récupération du client', 'error'));
}

function showClientDetailModal(client: ClientData) {
    function row(icon: string, label: string, value: string, accent = false): string {
        if (!value || value.trim() === '') return '';
        return `
        <div class="cd-row${accent ? ' cd-row--accent' : ''}">
            <span class="cd-row__label"><i class="fas fa-${icon}"></i>${label}</span>
            <span class="cd-row__value">${value}</span>
        </div>`;
    }

    const initials = ((client.companyName ?? '?').substring(0, 2)).toUpperCase();

    const addressParts = client.address ? [
        [client.address.streetNumber, client.address.streetLetter, client.address.streetName].filter(Boolean).join(' '),
        [client.address.postCode, client.address.city].filter(Boolean).join(' '),
        client.address.state,
        client.address.country,
    ].filter(Boolean) : [];
    const addressHtml = addressParts.length
        ? addressParts.map(line => `<div>${line}</div>`).join('')
        : '';

    const overlay = document.createElement('div');
    overlay.className = 'cd-overlay';
    overlay.innerHTML = `
        <div class="cd-panel">
            <div class="cd-panel__header">
                <div class="cd-panel__avatar">${initials}</div>
                <div class="cd-panel__title">
                    <p class="cd-panel__siret">${client.siret}</p>
                    <h2 class="cd-panel__name">${escapeHtml(client.companyName)}</h2>
                    ${client.workfield ? `<span class="cd-panel__workfield">${escapeHtml(client.workfield)}</span>` : ''}
                </div>
                <button class="cd-panel__close" aria-label="Fermer"><i class="fas fa-times"></i></button>
            </div>

            <div class="cd-panel__section-title">Contact</div>
            <div class="cd-rows">
                ${row('user', 'Nom', [client.contactFirstname, client.contactLastname].filter(Boolean).join(' '))}
                ${row('envelope', 'Email', client.contactEmail ?? '')}
                ${row('phone', 'Téléphone', client.contactPhone ?? '')}
            </div>

            ${addressParts.length ? `
            <div class="cd-panel__section-title">Adresse</div>
            <div class="cd-rows">
                <div class="cd-address">${addressHtml}</div>
            </div>` : ''}
        </div>`;

    document.body.appendChild(overlay);
    requestAnimationFrame(() => overlay.classList.add('cd-overlay--visible'));

    const close = () => {
        overlay.classList.remove('cd-overlay--visible');
        overlay.addEventListener('transitionend', () => overlay.remove(), { once: true });
    };

    overlay.querySelector('.cd-panel__close')!.addEventListener('click', close);
    overlay.addEventListener('click', (e) => { if (e.target === overlay) close(); });
    document.addEventListener('keydown', function onKey(e) {
        if (e.key === 'Escape') { close(); document.removeEventListener('keydown', onKey); }
    });
}
