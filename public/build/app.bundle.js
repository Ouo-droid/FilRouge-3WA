/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
/******/ 	var __webpack_modules__ = ({

/***/ "./assets/styles/styles.scss"
/*!***********************************!*\
  !*** ./assets/styles/styles.scss ***!
  \***********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ },

/***/ "./assets/ts/pages/clients.ts"
/*!************************************!*\
  !*** ./assets/ts/pages/clients.ts ***!
  \************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _utils_helpers__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../utils/helpers */ "./assets/ts/utils/helpers.ts");
/* harmony import */ var _services_Api__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../services/Api */ "./assets/ts/services/Api.ts");
var __awaiter = (undefined && undefined.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (undefined && undefined.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g = Object.create((typeof Iterator === "function" ? Iterator : Object).prototype);
    return g.next = verb(0), g["throw"] = verb(1), g["return"] = verb(2), typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};


document.addEventListener('DOMContentLoaded', function () {
    // Only run if we are on the clients page
    if (!document.querySelector('.clients-page')) {
        return;
    }
    // Gestion des clics délégués pour les actions dynamiques (édition, suppression)
    document.addEventListener('click', function (e) {
        var target = e.target;
        // View button
        var viewBtn = target.closest('.view-client-btn');
        if (viewBtn) {
            e.preventDefault();
            var siret = viewBtn.getAttribute('data-siret');
            if (siret)
                viewClient(siret);
        }
        // Edit button
        var editBtn = target.closest('.edit-client-btn');
        if (editBtn) {
            e.preventDefault();
            var siret = editBtn.getAttribute('data-siret');
            if (siret)
                editClient(siret);
        }
        // Delete button
        var deleteBtn = target.closest('.delete-client-btn');
        if (deleteBtn) {
            e.preventDefault();
            var siret = deleteBtn.getAttribute('data-siret');
            var name_1 = deleteBtn.getAttribute('data-name');
            if (siret)
                deleteClient(siret, name_1 || '');
        }
    });
    // Recherche en temps réel
    var searchInput = document.getElementById('client-search');
    var clientRows = document.querySelectorAll('.client-row');
    if (searchInput) {
        searchInput.addEventListener('input', function (e) {
            var target = e.target;
            var searchTerm = target.value.toLowerCase();
            clientRows.forEach(function (row) {
                var rowText = row.getAttribute('data-search-term');
                if (rowText && rowText.includes(searchTerm)) {
                    row.style.display = 'grid'; // Ou flex selon le CSS
                }
                else {
                    row.style.display = 'none';
                }
            });
        });
    }
    // Bouton créer client
    var createBtn = document.getElementById('create-client-btn');
    if (createBtn) {
        createBtn.addEventListener('click', function () {
            createNewClient();
        });
    }
});
function showClientToast(message, type) {
    if (type === void 0) { type = 'success'; }
    var toast = document.createElement('div');
    toast.className = "client-toast client-toast--".concat(type);
    toast.innerHTML = "\n        <i class=\"fas fa-".concat(type === 'success' ? 'building' : 'exclamation-triangle', "\"></i>\n        <span>").concat(message, "</span>\n    ");
    document.body.appendChild(toast);
    requestAnimationFrame(function () { return toast.classList.add('client-toast--visible'); });
    setTimeout(function () {
        toast.classList.remove('client-toast--visible');
        toast.addEventListener('transitionend', function () { return toast.remove(); });
    }, 3500);
}
function deleteClient(numSIRET, clientName) {
    return __awaiter(this, void 0, void 0, function () {
        var response, result, error_1;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    if (!confirm("\u00CAtes-vous s\u00FBr de vouloir supprimer le client \"".concat(clientName, "\" ?"))) return [3 /*break*/, 5];
                    _a.label = 1;
                case 1:
                    _a.trys.push([1, 4, , 5]);
                    return [4 /*yield*/, fetch("/api/delete/client/".concat(numSIRET), {
                            method: 'DELETE',
                            headers: { 'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_1__.getCsrfToken)() },
                        })];
                case 2:
                    response = _a.sent();
                    return [4 /*yield*/, response.json()];
                case 3:
                    result = _a.sent();
                    if (result.success || result.delete) {
                        showClientToast('Client supprimé avec succès !', 'success');
                        setTimeout(function () { return location.reload(); }, 1200);
                    }
                    else {
                        showClientToast(result.error || 'Erreur lors de la suppression du client', 'error');
                    }
                    return [3 /*break*/, 5];
                case 4:
                    error_1 = _a.sent();
                    console.error('Erreur:', error_1);
                    showClientToast('Erreur lors de la suppression du client', 'error');
                    return [3 /*break*/, 5];
                case 5: return [2 /*return*/];
            }
        });
    });
}
function createNewClient() {
    return __awaiter(this, void 0, void 0, function () {
        return __generator(this, function (_a) {
            showFormModal('Créer un nouveau client', null);
            return [2 /*return*/];
        });
    });
}
function editClient(numSIRET) {
    fetch("/api/client/".concat(numSIRET))
        .then(function (response) { return response.json(); })
        .then(function (data) {
        var _a, _b;
        var client = (_b = (_a = data.data) !== null && _a !== void 0 ? _a : data.client) !== null && _b !== void 0 ? _b : null;
        if (client) {
            showFormModal('Modifier le client', client);
        }
        else {
            showClientToast('Erreur lors de la récupération du client', 'error');
        }
    })
        .catch(function (error) {
        console.error('Erreur:', error);
        showClientToast('Erreur lors de la récupération du client', 'error');
    });
}
function showFormModal(title, client) {
    var _this = this;
    var isEdit = !!client;
    var formOverlay = document.createElement('div');
    formOverlay.className = 'form-overlay';
    // Note: Styles are now in assets/styles/components/_modals.scss
    var values = {
        siret: client ? client.siret : '',
        companyName: client ? client.companyName : '',
        workfield: client ? client.workfield : '',
        contactFirstname: client ? client.contactFirstname : '',
        contactLastname: client ? client.contactLastname : '',
        contactEmail: client ? client.contactEmail : '',
        contactPhone: client ? client.contactPhone : '',
        streetNumber: (client === null || client === void 0 ? void 0 : client.address) ? client.address.streetNumber : '',
        streetLetter: (client === null || client === void 0 ? void 0 : client.address) ? client.address.streetLetter : '',
        streetName: (client === null || client === void 0 ? void 0 : client.address) ? client.address.streetName : '',
        postCode: (client === null || client === void 0 ? void 0 : client.address) ? client.address.postCode : '',
        state: (client === null || client === void 0 ? void 0 : client.address) ? client.address.state : '',
        city: (client === null || client === void 0 ? void 0 : client.address) ? client.address.city : '',
        country: (client === null || client === void 0 ? void 0 : client.address) ? client.address.country : '',
    };
    formOverlay.innerHTML = "\n        <div class=\"edit-form\">\n            <div class=\"form-header\">\n                <h3>".concat(title, "</h3>\n                <button class=\"btn-close\" type=\"button\">\u00D7</button>\n            </div>\n            <form id=\"client-form\">\n                <div class=\"form-content\">\n\n                    <!-- Section Entreprise -->\n                    <div class=\"form-section form-section--company\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-building\"></i></span>\n                            <span class=\"form-section__label\">Entreprise</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-group\">\n                                <label for=\"siret\">Siren / Siret *</label>\n                                <div class=\"siren-input-group\">\n                                    <input type=\"text\" id=\"siret\" name=\"siret\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(values.siret), "\"\n                                        ").concat(isEdit ? 'readonly disabled' : 'required maxlength="14" pattern="[0-9]{14}" title="14 chiffres SIRET requis (utilisez le bouton loupe pour chercher par SIREN)"', "\n                                        class=\"").concat(isEdit ? 'bg-light' : '', "\">\n                                    ").concat(!isEdit ? '<button type="button" id="siren-lookup-btn" class="btn-siren-lookup" title="Rechercher l\'entreprise par SIREN (9 chiffres) ou SIRET (14 chiffres)"><i class="fas fa-search"></i></button>' : '', "\n                                </div>\n                                ").concat(isEdit ? '<small class="text-muted">Le SIRET ne peut pas être modifié</small>' : '', "\n                            </div>\n                            <div class=\"form-group\">\n                                <label for=\"companyName\">Nom de l'entreprise *</label>\n                                <input type=\"text\" id=\"companyName\" name=\"companyName\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(values.companyName), "\" required>\n                            </div>\n                            <div class=\"form-group\">\n                                <label for=\"workfield\">Domaine d'activit\u00E9</label>\n                                <input type=\"text\" id=\"workfield\" name=\"workfield\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(values.workfield), "\">\n                            </div>\n                        </div>\n                    </div>\n\n                    <!-- Section Contact -->\n                    <div class=\"form-section form-section--contact\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-user\"></i></span>\n                            <span class=\"form-section__label\">Contact</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"contactFirstname\">Pr\u00E9nom *</label>\n                                    <input type=\"text\" id=\"contactFirstname\" name=\"contactFirstname\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(values.contactFirstname), "\" required>\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"contactLastname\">Nom *</label>\n                                    <input type=\"text\" id=\"contactLastname\" name=\"contactLastname\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(values.contactLastname), "\" required>\n                                </div>\n                            </div>\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"contactEmail\">Email *</label>\n                                    <input type=\"email\" id=\"contactEmail\" name=\"contactEmail\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(values.contactEmail), "\" required>\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"contactPhone\">T\u00E9l\u00E9phone</label>\n                                    <input type=\"tel\" id=\"contactPhone\" name=\"contactPhone\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(values.contactPhone), "\">\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n\n                    <!-- Section Adresse -->\n                    <div class=\"form-section form-section--address\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-map-marker-alt\"></i></span>\n                            <span class=\"form-section__label\">Adresse</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-row form-row--address\">\n                                <div class=\"form-group form-group-small\">\n                                    <label for=\"streetNumber\">N\u00B0 *</label>\n                                    <input type=\"number\" id=\"streetNumber\" name=\"streetNumber\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(values.streetNumber), "\" required min=\"1\">\n                                </div>\n                                <div class=\"form-group form-group-small\">\n                                    <label for=\"streetLetter\">Compl\u00E9ment</label>\n                                    <input type=\"text\" id=\"streetLetter\" name=\"streetLetter\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(values.streetLetter), "\" placeholder=\"bis, ter...\">\n                                </div>\n                                <div class=\"form-group form-group-large\">\n                                    <label for=\"streetName\">Rue *</label>\n                                    <input type=\"text\" id=\"streetName\" name=\"streetName\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(values.streetName), "\" required>\n                                </div>\n                            </div>\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"postCode\">Code postal *</label>\n                                    <input type=\"text\" id=\"postCode\" name=\"postCode\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(values.postCode), "\" required>\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"city\">Ville *</label>\n                                    <input type=\"text\" id=\"city\" name=\"city\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(values.city), "\" required>\n                                </div>\n                            </div>\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"state\">R\u00E9gion</label>\n                                    <input type=\"text\" id=\"state\" name=\"state\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(values.state), "\">\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"country\">Pays</label>\n                                    <input type=\"text\" id=\"country\" name=\"country\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(values.country), "\">\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n\n                </div>\n                <div class=\"form-actions\">\n                    <button type=\"button\" class=\"btn-cancel\">Annuler</button>\n                    <button type=\"submit\" class=\"btn-save\">Sauvegarder</button>\n                </div>\n            </form>\n        </div>\n    ");
    document.body.appendChild(formOverlay);
    // Event listeners
    var form = formOverlay.querySelector('#client-form');
    var closeBtn = formOverlay.querySelector('.btn-close');
    var cancelBtn = formOverlay.querySelector('.btn-cancel');
    if (form) {
        form.addEventListener('submit', function (e) { return __awaiter(_this, void 0, void 0, function () {
            var formData, flat, data;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        e.preventDefault();
                        formData = new FormData(form);
                        flat = {};
                        formData.forEach(function (value, key) {
                            flat[key] = value;
                        });
                        data = {
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
                        if (!(isEdit && client)) return [3 /*break*/, 2];
                        data.siret = client.siret;
                        return [4 /*yield*/, handleEditSubmit(client.siret, data, formOverlay)];
                    case 1:
                        _a.sent();
                        return [3 /*break*/, 4];
                    case 2: return [4 /*yield*/, handleCreateSubmit(data, formOverlay)];
                    case 3:
                        _a.sent();
                        _a.label = 4;
                    case 4: return [2 /*return*/];
                }
            });
        }); });
    }
    [closeBtn, cancelBtn].forEach(function (btn) {
        if (btn) {
            btn.addEventListener('click', function () {
                document.body.removeChild(formOverlay);
            });
        }
    });
    formOverlay.addEventListener('click', function (e) {
        if (e.target === formOverlay) {
            document.body.removeChild(formOverlay);
        }
    });
    if (!isEdit) {
        var lookupBtn_1 = formOverlay.querySelector('#siren-lookup-btn');
        var siretInput_1 = formOverlay.querySelector('#siret');
        if (lookupBtn_1 && siretInput_1) {
            lookupBtn_1.addEventListener('click', function () { return __awaiter(_this, void 0, void 0, function () {
                var val, res, result, d, fill, _a;
                return __generator(this, function (_b) {
                    switch (_b.label) {
                        case 0:
                            val = siretInput_1.value.trim();
                            if (val === '' || !/^\d+$/.test(val)) {
                                showClientToast('Veuillez saisir un numéro SIREN (9 chiffres) ou SIRET (14 chiffres)', 'error');
                                return [2 /*return*/];
                            }
                            if (val.length !== 9 && val.length !== 14) {
                                showClientToast('Le numéro doit faire 9 chiffres (SIREN) ou 14 chiffres (SIRET)', 'error');
                                return [2 /*return*/];
                            }
                            lookupBtn_1.disabled = true;
                            lookupBtn_1.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
                            _b.label = 1;
                        case 1:
                            _b.trys.push([1, 4, 5, 6]);
                            return [4 /*yield*/, fetch("/api/siren-lookup?q=".concat(encodeURIComponent(val)))];
                        case 2:
                            res = _b.sent();
                            return [4 /*yield*/, res.json()];
                        case 3:
                            result = _b.sent();
                            if (!res.ok || !result.success) {
                                showClientToast(result.error || 'Entreprise non trouvée', 'error');
                                return [2 /*return*/];
                            }
                            d = result.data;
                            fill = function (id, value) {
                                var el = formOverlay.querySelector("#".concat(id));
                                if (el && value != null && value !== '')
                                    el.value = value;
                            };
                            fill('companyName', d.companyName);
                            fill('streetNumber', d.streetNumber);
                            fill('streetLetter', d.streetLetter);
                            fill('streetName', d.streetName);
                            fill('postCode', d.postCode);
                            fill('city', d.city);
                            fill('country', d.country);
                            showClientToast('Informations récupérées avec succès', 'success');
                            return [3 /*break*/, 6];
                        case 4:
                            _a = _b.sent();
                            showClientToast('Erreur lors de la recherche SIREN/SIRET', 'error');
                            return [3 /*break*/, 6];
                        case 5:
                            lookupBtn_1.disabled = false;
                            lookupBtn_1.innerHTML = '<i class="fas fa-search"></i>';
                            return [7 /*endfinally*/];
                        case 6: return [2 /*return*/];
                    }
                });
            }); });
        }
    }
}
function handleCreateSubmit(data, formOverlay) {
    return __awaiter(this, void 0, void 0, function () {
        var response, result, error_2;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    _a.trys.push([0, 3, , 4]);
                    return [4 /*yield*/, fetch('/api/add/client', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_1__.getCsrfToken)() },
                            body: JSON.stringify(data)
                        })];
                case 1:
                    response = _a.sent();
                    return [4 /*yield*/, response.json()];
                case 2:
                    result = _a.sent();
                    if (result.success) {
                        document.body.removeChild(formOverlay);
                        showClientToast('Client créé avec succès !', 'success');
                        setTimeout(function () { return location.reload(); }, 1200);
                    }
                    else {
                        showClientToast('Erreur : ' + (result.error || result.message), 'error');
                    }
                    return [3 /*break*/, 4];
                case 3:
                    error_2 = _a.sent();
                    console.error('Erreur:', error_2);
                    showClientToast('Erreur lors de la création du client', 'error');
                    return [3 /*break*/, 4];
                case 4: return [2 /*return*/];
            }
        });
    });
}
function handleEditSubmit(numSIRET, data, formOverlay) {
    return __awaiter(this, void 0, void 0, function () {
        var response, result, error_3;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    _a.trys.push([0, 3, , 4]);
                    return [4 /*yield*/, fetch("/api/edit/client/".concat(numSIRET), {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_1__.getCsrfToken)() },
                            body: JSON.stringify(data)
                        })];
                case 1:
                    response = _a.sent();
                    return [4 /*yield*/, response.json()];
                case 2:
                    result = _a.sent();
                    if (result.success) {
                        document.body.removeChild(formOverlay);
                        showClientToast('Client modifié avec succès !', 'success');
                        setTimeout(function () { return location.reload(); }, 1200);
                    }
                    else {
                        showClientToast('Erreur : ' + (result.error || result.message), 'error');
                    }
                    return [3 /*break*/, 4];
                case 3:
                    error_3 = _a.sent();
                    console.error('Erreur:', error_3);
                    showClientToast('Erreur lors de la modification du client', 'error');
                    return [3 /*break*/, 4];
                case 4: return [2 /*return*/];
            }
        });
    });
}
function viewClient(siret) {
    fetch("/api/client/".concat(siret))
        .then(function (res) { return res.json(); })
        .then(function (data) {
        var _a, _b;
        var client = (_b = (_a = data.data) !== null && _a !== void 0 ? _a : data.client) !== null && _b !== void 0 ? _b : null;
        if (client) {
            showClientDetailModal(client);
        }
        else {
            showClientToast('Erreur lors de la récupération du client', 'error');
        }
    })
        .catch(function () { return showClientToast('Erreur lors de la récupération du client', 'error'); });
}
function showClientDetailModal(client) {
    var _a, _b, _c;
    function row(icon, label, value, accent) {
        if (accent === void 0) { accent = false; }
        if (!value || value.trim() === '')
            return '';
        return "\n        <div class=\"cd-row".concat(accent ? ' cd-row--accent' : '', "\">\n            <span class=\"cd-row__label\"><i class=\"fas fa-").concat(icon, "\"></i>").concat(label, "</span>\n            <span class=\"cd-row__value\">").concat(value, "</span>\n        </div>");
    }
    var initials = (((_a = client.companyName) !== null && _a !== void 0 ? _a : '?').substring(0, 2)).toUpperCase();
    var addressParts = client.address ? [
        [client.address.streetNumber, client.address.streetLetter, client.address.streetName].filter(Boolean).join(' '),
        [client.address.postCode, client.address.city].filter(Boolean).join(' '),
        client.address.state,
        client.address.country,
    ].filter(Boolean) : [];
    var addressHtml = addressParts.length
        ? addressParts.map(function (line) { return "<div>".concat(line, "</div>"); }).join('')
        : '';
    var overlay = document.createElement('div');
    overlay.className = 'cd-overlay';
    overlay.innerHTML = "\n        <div class=\"cd-panel\">\n            <div class=\"cd-panel__header\">\n                <div class=\"cd-panel__avatar\">".concat(initials, "</div>\n                <div class=\"cd-panel__title\">\n                    <p class=\"cd-panel__siret\">").concat(client.siret, "</p>\n                    <h2 class=\"cd-panel__name\">").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(client.companyName), "</h2>\n                    ").concat(client.workfield ? "<span class=\"cd-panel__workfield\">".concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(client.workfield), "</span>") : '', "\n                </div>\n                <button class=\"cd-panel__close\" aria-label=\"Fermer\"><i class=\"fas fa-times\"></i></button>\n            </div>\n\n            <div class=\"cd-panel__section-title\">Contact</div>\n            <div class=\"cd-rows\">\n                ").concat(row('user', 'Nom', [client.contactFirstname, client.contactLastname].filter(Boolean).join(' ')), "\n                ").concat(row('envelope', 'Email', (_b = client.contactEmail) !== null && _b !== void 0 ? _b : ''), "\n                ").concat(row('phone', 'Téléphone', (_c = client.contactPhone) !== null && _c !== void 0 ? _c : ''), "\n            </div>\n\n            ").concat(addressParts.length ? "\n            <div class=\"cd-panel__section-title\">Adresse</div>\n            <div class=\"cd-rows\">\n                <div class=\"cd-address\">".concat(addressHtml, "</div>\n            </div>") : '', "\n        </div>");
    document.body.appendChild(overlay);
    requestAnimationFrame(function () { return overlay.classList.add('cd-overlay--visible'); });
    var close = function () {
        overlay.classList.remove('cd-overlay--visible');
        overlay.addEventListener('transitionend', function () { return overlay.remove(); }, { once: true });
    };
    overlay.querySelector('.cd-panel__close').addEventListener('click', close);
    overlay.addEventListener('click', function (e) { if (e.target === overlay)
        close(); });
    document.addEventListener('keydown', function onKey(e) {
        if (e.key === 'Escape') {
            close();
            document.removeEventListener('keydown', onKey);
        }
    });
}


/***/ },

/***/ "./assets/ts/pages/home.ts"
/*!*********************************!*\
  !*** ./assets/ts/pages/home.ts ***!
  \*********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _services_Api__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../services/Api */ "./assets/ts/services/Api.ts");
var __awaiter = (undefined && undefined.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (undefined && undefined.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g = Object.create((typeof Iterator === "function" ? Iterator : Object).prototype);
    return g.next = verb(0), g["throw"] = verb(1), g["return"] = verb(2), typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};

document.addEventListener('DOMContentLoaded', function () {
    var _this = this;
    var monthYearLabel = document.getElementById('calendar-month-year');
    var datepicker = document.getElementById('calendar-datepicker');
    var grid = document.getElementById('calendar-days-grid');
    var prevBtn = document.getElementById('prev-week');
    var nextBtn = document.getElementById('next-week');
    // Only run if the calendar widget is present (i.e. on the home page)
    if (monthYearLabel && datepicker && grid && prevBtn && nextBtn) {
        var currentDate_1 = new Date();
        var selectedDate_1 = new Date();
        var monthNames_1 = ["Janvier", "Février", "Mars", "Avril", "Mai", "Juin", "Juillet", "Août", "Septembre", "Octobre", "Novembre", "Décembre"];
        var dayNames_1 = ["D", "L", "M", "M", "J", "V", "S"];
        var updateCalendar_1 = function () {
            // Update Title (Month Year)
            var span = monthYearLabel === null || monthYearLabel === void 0 ? void 0 : monthYearLabel.querySelector('span');
            if (span) {
                span.textContent = "".concat(monthNames_1[currentDate_1.getMonth()], " ").concat(currentDate_1.getFullYear());
            }
            // Clear Grid
            if (grid) {
                grid.innerHTML = '';
                // Calculate Start of the week (Sunday)
                var startOfWeek = new Date(currentDate_1);
                startOfWeek.setDate(currentDate_1.getDate() - currentDate_1.getDay());
                var _loop_1 = function (i) {
                    var dayDate = new Date(startOfWeek);
                    dayDate.setDate(startOfWeek.getDate() + i);
                    var col = document.createElement('div');
                    col.className = 'calendar-day-col';
                    if (dayDate.toDateString() === selectedDate_1.toDateString()) {
                        col.classList.add('active');
                    }
                    var nameDiv = document.createElement('div');
                    nameDiv.className = 'calendar-day-name';
                    nameDiv.textContent = dayNames_1[dayDate.getDay()];
                    var numDiv = document.createElement('div');
                    numDiv.className = 'calendar-day-num';
                    numDiv.textContent = dayDate.getDate().toString();
                    col.appendChild(nameDiv);
                    col.appendChild(numDiv);
                    col.addEventListener('click', function () {
                        selectedDate_1 = new Date(dayDate);
                        currentDate_1 = new Date(dayDate); // Sync current view with selected day
                        updateCalendar_1();
                    });
                    grid.appendChild(col);
                };
                for (var i = 0; i < 7; i++) {
                    _loop_1(i);
                }
            }
        };
        prevBtn.addEventListener('click', function () {
            currentDate_1.setDate(currentDate_1.getDate() - 7);
            updateCalendar_1();
        });
        nextBtn.addEventListener('click', function () {
            currentDate_1.setDate(currentDate_1.getDate() + 7);
            updateCalendar_1();
        });
        monthYearLabel.addEventListener('click', function () {
            if (datepicker && 'showPicker' in datepicker) {
                datepicker.showPicker();
            }
            else {
                datepicker.focus();
            }
        });
        datepicker.addEventListener('change', function (e) {
            var target = e.target;
            var newDate = new Date(target.value);
            if (!isNaN(newDate.getTime())) {
                currentDate_1 = new Date(newDate);
                selectedDate_1 = new Date(newDate);
                updateCalendar_1();
            }
        });
        // Initialize
        updateCalendar_1();
    }
    // Gestion des clics sur les liens de navigation (Sidebar)
    var sidebarLinks = document.querySelectorAll('.sidebar .nav-link');
    if (sidebarLinks.length > 0) {
        sidebarLinks.forEach(function (link) {
            link.addEventListener('click', function (e) {
                var href = link.getAttribute('href');
                if (href && href.startsWith('/')) {
                    return;
                }
                e.preventDefault();
                sidebarLinks.forEach(function (l) { return l.classList.remove('active'); });
                link.classList.add('active');
            });
        });
    }
    // ── Effort modal ──────────────────────────────────────────────────────────
    var addTaskBtn = document.querySelector('.btn-add-task');
    var effortModal = document.getElementById('effort-modal');
    var effortClose = document.getElementById('effort-modal-close');
    var effortCancel = document.getElementById('effort-modal-cancel');
    var effortSubmit = document.getElementById('effort-modal-submit');
    var effortSelect = document.getElementById('effort-task-select');
    var effortInput = document.getElementById('effort-value');
    var effortError = document.getElementById('effort-error');
    var effortSuccess = document.getElementById('effort-success');
    function openEffortModal() {
        if (!effortModal)
            return;
        if (effortSelect)
            effortSelect.value = '';
        if (effortInput)
            effortInput.value = '';
        hideEffortFeedback();
        effortModal.style.display = 'flex';
        effortSelect === null || effortSelect === void 0 ? void 0 : effortSelect.focus();
    }
    function closeEffortModal() {
        if (effortModal)
            effortModal.style.display = 'none';
    }
    function hideEffortFeedback() {
        if (effortError) {
            effortError.style.display = 'none';
            effortError.textContent = '';
        }
        if (effortSuccess) {
            effortSuccess.style.display = 'none';
            effortSuccess.textContent = '';
        }
    }
    function showEffortError(msg) {
        if (effortError) {
            effortError.textContent = msg;
            effortError.style.display = 'block';
        }
        if (effortSuccess)
            effortSuccess.style.display = 'none';
    }
    function showEffortSuccess(msg) {
        if (effortSuccess) {
            effortSuccess.textContent = msg;
            effortSuccess.style.display = 'block';
        }
        if (effortError)
            effortError.style.display = 'none';
    }
    // Pre-fill effort when a task is selected
    effortSelect === null || effortSelect === void 0 ? void 0 : effortSelect.addEventListener('change', function () {
        var _a;
        if (!effortSelect || !effortInput)
            return;
        var opt = effortSelect.selectedOptions[0];
        var existing = (_a = opt === null || opt === void 0 ? void 0 : opt.getAttribute('data-effort')) !== null && _a !== void 0 ? _a : '';
        effortInput.value = existing;
        hideEffortFeedback();
    });
    addTaskBtn === null || addTaskBtn === void 0 ? void 0 : addTaskBtn.addEventListener('click', openEffortModal);
    effortClose === null || effortClose === void 0 ? void 0 : effortClose.addEventListener('click', closeEffortModal);
    effortCancel === null || effortCancel === void 0 ? void 0 : effortCancel.addEventListener('click', closeEffortModal);
    // Close on overlay click
    effortModal === null || effortModal === void 0 ? void 0 : effortModal.addEventListener('click', function (e) {
        if (e.target === effortModal)
            closeEffortModal();
    });
    effortSubmit === null || effortSubmit === void 0 ? void 0 : effortSubmit.addEventListener('click', function () { return __awaiter(_this, void 0, void 0, function () {
        var taskId, effort, res, data, opt, _a;
        var _b;
        return __generator(this, function (_c) {
            switch (_c.label) {
                case 0:
                    if (!effortSelect || !effortInput)
                        return [2 /*return*/];
                    taskId = effortSelect.value.trim();
                    effort = effortInput.value.trim();
                    if (!taskId) {
                        showEffortError('Veuillez sélectionner une tâche.');
                        return [2 /*return*/];
                    }
                    if (effort === '' || isNaN(parseFloat(effort)) || parseFloat(effort) <= 0) {
                        showEffortError('Veuillez saisir un effort valide (> 0).');
                        return [2 /*return*/];
                    }
                    if (effortSubmit instanceof HTMLButtonElement)
                        effortSubmit.disabled = true;
                    _c.label = 1;
                case 1:
                    _c.trys.push([1, 6, 7, 8]);
                    return [4 /*yield*/, fetch("/api/edit/task/".concat(encodeURIComponent(taskId)), {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_0__.getCsrfToken)() },
                            body: JSON.stringify({ effortMade: parseFloat(effort) }),
                        })];
                case 2:
                    res = _c.sent();
                    if (!!res.ok) return [3 /*break*/, 4];
                    return [4 /*yield*/, res.json().catch(function () { return ({}); })];
                case 3:
                    data = _c.sent();
                    showEffortError((_b = data.message) !== null && _b !== void 0 ? _b : 'Erreur lors de la mise à jour.');
                    return [3 /*break*/, 5];
                case 4:
                    showEffortSuccess('Effort enregistré avec succès !');
                    opt = effortSelect.selectedOptions[0];
                    if (opt)
                        opt.setAttribute('data-effort', effort);
                    setTimeout(closeEffortModal, 1500);
                    _c.label = 5;
                case 5: return [3 /*break*/, 8];
                case 6:
                    _a = _c.sent();
                    showEffortError('Erreur réseau. Veuillez réessayer.');
                    return [3 /*break*/, 8];
                case 7:
                    if (effortSubmit instanceof HTMLButtonElement)
                        effortSubmit.disabled = false;
                    return [7 /*endfinally*/];
                case 8: return [2 /*return*/];
            }
        });
    }); });
    // ── Modal assignation tâche (CDP) ────────────────────────────────────────
    var assignModal = document.getElementById('cdp-assign-modal');
    var assignClose = document.getElementById('cdp-assign-close');
    var assignCancel = document.getElementById('cdp-assign-cancel');
    var assignSubmit = document.getElementById('cdp-assign-submit');
    var assignSearch = document.getElementById('cdp-assign-search');
    var assignResults = document.getElementById('cdp-assign-results');
    var assignSelected = document.getElementById('cdp-assign-selected');
    var assignSelName = document.getElementById('cdp-assign-selected-name');
    var assignClear = document.getElementById('cdp-assign-clear');
    var assignError = document.getElementById('cdp-assign-error');
    var assignTaskId = '';
    var assignUserId = '';
    var allUsers = [];
    function loadUsers() {
        return __awaiter(this, void 0, void 0, function () {
            var res, data, raw, _a;
            var _b;
            return __generator(this, function (_c) {
                switch (_c.label) {
                    case 0:
                        if (allUsers.length)
                            return [2 /*return*/];
                        _c.label = 1;
                    case 1:
                        _c.trys.push([1, 4, , 5]);
                        return [4 /*yield*/, fetch('/api/users')];
                    case 2:
                        res = _c.sent();
                        return [4 /*yield*/, res.json().catch(function () { return ({}); })];
                    case 3:
                        data = _c.sent();
                        raw = (_b = data.data) !== null && _b !== void 0 ? _b : [];
                        allUsers = raw.map(function (u) { return ({ id: u.id, name: "".concat(u.firstname, " ").concat(u.lastname) }); });
                        return [3 /*break*/, 5];
                    case 4:
                        _a = _c.sent();
                        return [3 /*break*/, 5];
                    case 5: return [2 /*return*/];
                }
            });
        });
    }
    function openAssignModal(btn) {
        var _a, _b, _c, _d, _e, _f, _g;
        if (!assignModal)
            return;
        assignTaskId = (_a = btn.getAttribute('data-task-id')) !== null && _a !== void 0 ? _a : '';
        assignUserId = '';
        document.getElementById('cdp-assign-task-name').textContent = (_b = btn.getAttribute('data-task-name')) !== null && _b !== void 0 ? _b : '';
        document.getElementById('cdp-assign-task-project').textContent = (_c = btn.getAttribute('data-task-project')) !== null && _c !== void 0 ? _c : '';
        var descEl = document.getElementById('cdp-assign-task-desc');
        var desc = (_d = btn.getAttribute('data-task-desc')) !== null && _d !== void 0 ? _d : '';
        descEl.textContent = desc || 'Aucune description.';
        var deadlineEl = document.getElementById('cdp-assign-task-deadline');
        var deadline = (_e = btn.getAttribute('data-task-deadline')) !== null && _e !== void 0 ? _e : '';
        if (deadline) {
            deadlineEl.querySelector('.val').textContent = deadline;
            deadlineEl.style.display = '';
        }
        else
            deadlineEl.style.display = 'none';
        var effortEl = document.getElementById('cdp-assign-task-effort');
        var effort = (_f = btn.getAttribute('data-task-effort')) !== null && _f !== void 0 ? _f : '';
        if (effort) {
            effortEl.querySelector('.val').textContent = effort;
            effortEl.style.display = '';
        }
        else
            effortEl.style.display = 'none';
        var prioEl = document.getElementById('cdp-assign-task-priority');
        var priority = (_g = btn.getAttribute('data-task-priority')) !== null && _g !== void 0 ? _g : '';
        if (priority) {
            prioEl.innerHTML = "<span class=\"badge bg-danger bg-opacity-10 text-danger border border-danger\" style=\"font-size:.72rem;\">".concat(priority, "</span>");
            prioEl.style.display = '';
        }
        else
            prioEl.style.display = 'none';
        if (assignSearch)
            assignSearch.value = '';
        if (assignResults) {
            assignResults.innerHTML = '';
            assignResults.style.display = 'none';
        }
        if (assignSelected)
            assignSelected.style.display = 'none';
        if (assignSubmit)
            assignSubmit.disabled = true;
        if (assignError) {
            assignError.textContent = '';
            assignError.style.display = 'none';
        }
        assignModal.style.display = 'flex';
        loadUsers().then(function () { return assignSearch === null || assignSearch === void 0 ? void 0 : assignSearch.focus(); });
    }
    function closeAssignModal() {
        if (assignModal)
            assignModal.style.display = 'none';
    }
    function selectUser(id, name) {
        assignUserId = id;
        if (assignSelName)
            assignSelName.textContent = name;
        if (assignSelected)
            assignSelected.style.display = 'flex';
        if (assignResults) {
            assignResults.innerHTML = '';
            assignResults.style.display = 'none';
        }
        if (assignSearch)
            assignSearch.style.display = 'none';
        if (assignSubmit)
            assignSubmit.disabled = false;
    }
    assignSearch === null || assignSearch === void 0 ? void 0 : assignSearch.addEventListener('input', function () {
        var q = assignSearch.value.trim().toLowerCase();
        if (!assignResults)
            return;
        if (!q) {
            assignResults.style.display = 'none';
            return;
        }
        var filtered = allUsers.filter(function (u) { return u.name.toLowerCase().includes(q); }).slice(0, 8);
        assignResults.innerHTML = filtered.map(function (u) {
            return "<div class=\"cdp-assign-result-item\" data-id=\"".concat(u.id, "\" data-name=\"").concat(u.name, "\">").concat(u.name, "</div>");
        }).join('') || '<div class="cdp-assign-result-item text-muted">Aucun résultat</div>';
        assignResults.style.display = 'block';
    });
    assignResults === null || assignResults === void 0 ? void 0 : assignResults.addEventListener('click', function (e) {
        var _a;
        var item = e.target.closest('.cdp-assign-result-item');
        if (!item || !item.dataset.id)
            return;
        selectUser(item.dataset.id, (_a = item.dataset.name) !== null && _a !== void 0 ? _a : '');
    });
    assignClear === null || assignClear === void 0 ? void 0 : assignClear.addEventListener('click', function () {
        assignUserId = '';
        if (assignSelected)
            assignSelected.style.display = 'none';
        if (assignSearch) {
            assignSearch.style.display = '';
            assignSearch.value = '';
            assignSearch.focus();
        }
        if (assignSubmit)
            assignSubmit.disabled = true;
    });
    assignClose === null || assignClose === void 0 ? void 0 : assignClose.addEventListener('click', closeAssignModal);
    assignCancel === null || assignCancel === void 0 ? void 0 : assignCancel.addEventListener('click', closeAssignModal);
    assignModal === null || assignModal === void 0 ? void 0 : assignModal.addEventListener('click', function (e) { if (e.target === assignModal)
        closeAssignModal(); });
    assignSubmit === null || assignSubmit === void 0 ? void 0 : assignSubmit.addEventListener('click', function () { return __awaiter(_this, void 0, void 0, function () {
        var res, data, _a;
        var _b;
        return __generator(this, function (_c) {
            switch (_c.label) {
                case 0:
                    if (!assignTaskId || !assignUserId)
                        return [2 /*return*/];
                    assignSubmit.disabled = true;
                    _c.label = 1;
                case 1:
                    _c.trys.push([1, 4, , 5]);
                    return [4 /*yield*/, fetch("/api/edit/task/".concat(encodeURIComponent(assignTaskId)), {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_0__.getCsrfToken)() },
                            body: JSON.stringify({ developerId: assignUserId }),
                        })];
                case 2:
                    res = _c.sent();
                    return [4 /*yield*/, res.json().catch(function () { return ({}); })];
                case 3:
                    data = _c.sent();
                    if (!res.ok || !data.success) {
                        if (assignError) {
                            assignError.textContent = (_b = data.error) !== null && _b !== void 0 ? _b : 'Erreur lors de l\'assignation.';
                            assignError.style.display = 'block';
                        }
                        assignSubmit.disabled = false;
                    }
                    else {
                        closeAssignModal();
                        window.location.reload();
                    }
                    return [3 /*break*/, 5];
                case 4:
                    _a = _c.sent();
                    if (assignError) {
                        assignError.textContent = 'Erreur réseau.';
                        assignError.style.display = 'block';
                    }
                    assignSubmit.disabled = false;
                    return [3 /*break*/, 5];
                case 5: return [2 /*return*/];
            }
        });
    }); });
    document.querySelectorAll('.cdp-assign-btn').forEach(function (btn) {
        btn.addEventListener('click', function () { return openAssignModal(btn); });
    });
    // ── Demande d'absence (collaborateur) ────────────────────────────────────
    var FOCUSABLE = 'a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])';
    function trapFocus(modal, e) {
        var items = Array.from(modal.querySelectorAll(FOCUSABLE));
        if (!items.length)
            return;
        var first = items[0];
        var last = items[items.length - 1];
        if (e.shiftKey) {
            if (document.activeElement === first) {
                e.preventDefault();
                last.focus();
            }
        }
        else {
            if (document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        }
    }
    var arBtn = document.getElementById('btn-request-absence');
    var arModal = document.getElementById('absence-request-modal');
    var arClose = document.getElementById('absence-request-close');
    var arCancel = document.getElementById('absence-request-cancel');
    var arSubmit = document.getElementById('absence-request-submit');
    var arStart = document.getElementById('ar-start');
    var arEnd = document.getElementById('ar-end');
    var arReason = document.getElementById('ar-reason');
    var arError = document.getElementById('ar-error');
    var arSuccess = document.getElementById('ar-success');
    function openAbsenceRequestModal() {
        if (!arModal)
            return;
        if (arStart)
            arStart.value = '';
        if (arEnd)
            arEnd.value = '';
        if (arReason)
            arReason.value = '';
        if (arError) {
            arError.style.display = 'none';
            arError.textContent = '';
        }
        if (arSuccess) {
            arSuccess.style.display = 'none';
            arSuccess.textContent = '';
        }
        arModal.style.display = 'flex';
        arStart === null || arStart === void 0 ? void 0 : arStart.focus();
    }
    function closeAbsenceRequestModal() {
        if (arModal)
            arModal.style.display = 'none';
        arBtn === null || arBtn === void 0 ? void 0 : arBtn.focus();
    }
    arBtn === null || arBtn === void 0 ? void 0 : arBtn.addEventListener('click', openAbsenceRequestModal);
    arClose === null || arClose === void 0 ? void 0 : arClose.addEventListener('click', closeAbsenceRequestModal);
    arCancel === null || arCancel === void 0 ? void 0 : arCancel.addEventListener('click', closeAbsenceRequestModal);
    arModal === null || arModal === void 0 ? void 0 : arModal.addEventListener('click', function (e) { if (e.target === arModal)
        closeAbsenceRequestModal(); });
    arModal === null || arModal === void 0 ? void 0 : arModal.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeAbsenceRequestModal();
            return;
        }
        if (e.key === 'Tab' && arModal)
            trapFocus(arModal, e);
    });
    // Sync min date de fin sur changement date de début
    arStart === null || arStart === void 0 ? void 0 : arStart.addEventListener('change', function () {
        if (arEnd && arStart.value)
            arEnd.min = arStart.value;
    });
    arSubmit === null || arSubmit === void 0 ? void 0 : arSubmit.addEventListener('click', function () { return __awaiter(_this, void 0, void 0, function () {
        var start, end, reason, res, data, _a;
        var _b, _c, _d, _e;
        return __generator(this, function (_f) {
            switch (_f.label) {
                case 0:
                    if (arError) {
                        arError.style.display = 'none';
                        arError.textContent = '';
                    }
                    if (arSuccess) {
                        arSuccess.style.display = 'none';
                        arSuccess.textContent = '';
                    }
                    start = (_b = arStart === null || arStart === void 0 ? void 0 : arStart.value) !== null && _b !== void 0 ? _b : '';
                    end = (_c = arEnd === null || arEnd === void 0 ? void 0 : arEnd.value) !== null && _c !== void 0 ? _c : '';
                    reason = (_d = arReason === null || arReason === void 0 ? void 0 : arReason.value.trim()) !== null && _d !== void 0 ? _d : '';
                    if (!start) {
                        if (arError) {
                            arError.textContent = 'La date de début est obligatoire.';
                            arError.style.display = 'block';
                        }
                        return [2 /*return*/];
                    }
                    if (!end) {
                        if (arError) {
                            arError.textContent = 'La date de fin est obligatoire.';
                            arError.style.display = 'block';
                        }
                        return [2 /*return*/];
                    }
                    if (end < start) {
                        if (arError) {
                            arError.textContent = 'La date de fin doit être après la date de début.';
                            arError.style.display = 'block';
                        }
                        return [2 /*return*/];
                    }
                    if (arSubmit)
                        arSubmit.disabled = true;
                    _f.label = 1;
                case 1:
                    _f.trys.push([1, 4, 5, 6]);
                    return [4 /*yield*/, fetch('/api/request/my-absence', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_0__.getCsrfToken)() },
                            body: JSON.stringify({ startDate: start, endDate: end, reason: reason || null }),
                        })];
                case 2:
                    res = _f.sent();
                    return [4 /*yield*/, res.json().catch(function () { return ({}); })];
                case 3:
                    data = _f.sent();
                    if (!res.ok || !data.success) {
                        if (arError) {
                            arError.textContent = (_e = data.error) !== null && _e !== void 0 ? _e : 'Erreur lors de l\'envoi.';
                            arError.style.display = 'block';
                        }
                    }
                    else {
                        if (arSuccess) {
                            arSuccess.textContent = 'Demande envoyée avec succès !';
                            arSuccess.style.display = 'block';
                        }
                        setTimeout(closeAbsenceRequestModal, 1800);
                    }
                    return [3 /*break*/, 6];
                case 4:
                    _a = _f.sent();
                    if (arError) {
                        arError.textContent = 'Erreur réseau. Veuillez réessayer.';
                        arError.style.display = 'block';
                    }
                    return [3 /*break*/, 6];
                case 5:
                    if (arSubmit)
                        arSubmit.disabled = false;
                    return [7 /*endfinally*/];
                case 6: return [2 /*return*/];
            }
        });
    }); });
});


/***/ },

/***/ "./assets/ts/pages/login.ts"
/*!**********************************!*\
  !*** ./assets/ts/pages/login.ts ***!
  \**********************************/
() {


document.addEventListener("DOMContentLoaded", function () {
    var toggleBtn = document.querySelector(".login-toggle-password");
    if (!toggleBtn)
        return;
    var input = document.getElementById("pwd");
    var icon = toggleBtn.querySelector("i");
    if (!input || !icon)
        return;
    toggleBtn.addEventListener("click", function () {
        var isPassword = input.type === "password";
        input.type = isPassword ? "text" : "password";
        icon.classList.toggle("fa-eye-slash", !isPassword);
        icon.classList.toggle("fa-eye", isPassword);
    });
});


/***/ },

/***/ "./assets/ts/pages/projects.ts"
/*!*************************************!*\
  !*** ./assets/ts/pages/projects.ts ***!
  \*************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   editProject: () => (/* binding */ editProject)
/* harmony export */ });
/* harmony import */ var _services_UserService__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../services/UserService */ "./assets/ts/services/UserService.ts");
/* harmony import */ var _utils_helpers__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../utils/helpers */ "./assets/ts/utils/helpers.ts");
/* harmony import */ var _services_Api__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ../services/Api */ "./assets/ts/services/Api.ts");
var __awaiter = (undefined && undefined.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (undefined && undefined.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g = Object.create((typeof Iterator === "function" ? Iterator : Object).prototype);
    return g.next = verb(0), g["throw"] = verb(1), g["return"] = verb(2), typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};



document.addEventListener('DOMContentLoaded', function () {
    // Only run if we are on the projects page
    if (!document.querySelector('.projects-page')) {
        return;
    }
    // --- Filter tabs ---
    var filterBtns = document.querySelectorAll('.task-filters .filter-btn');
    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            filterBtns.forEach(function (b) { b.classList.remove('active'); b.setAttribute('aria-pressed', 'false'); });
            this.classList.add('active');
            this.setAttribute('aria-pressed', 'true');
            var filter = this.getAttribute('data-filter');
            document.querySelectorAll('#project-list .project-card').forEach(function (card) {
                card.style.display = (filter === 'all' || card.getAttribute('data-state-id') === filter) ? '' : 'none';
            });
        });
    });
    // --- Search ---
    var searchInput = document.getElementById('project-search');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            var query = this.value.trim().toLowerCase();
            document.querySelectorAll('#project-list .project-card').forEach(function (card) {
                var _a;
                var name = (_a = card.getAttribute('data-project-name')) !== null && _a !== void 0 ? _a : '';
                card.style.display = (query === '' || name.includes(query)) ? '' : 'none';
            });
        });
    }
    // Delegation pour les boutons dynamiques
    document.addEventListener('click', function (e) {
        var _a, _b, _c;
        var target = e.target;
        // Trigger nouveau projet
        if (target.matches('.create-project-trigger')) {
            var btn = document.getElementById('create-project-btn');
            if (btn)
                btn.click();
        }
        // Toggle dropdown
        var toggleBtn = target.closest('.toggle-dropdown-btn');
        if (toggleBtn) {
            toggleDropdown(e, toggleBtn);
        }
        // Edit project
        var editBtn = target.closest('.edit-project-btn');
        if (editBtn) {
            e.preventDefault();
            var projectId = editBtn.getAttribute('data-project-id');
            if (projectId)
                editProject(projectId);
        }
        // View project (Details)
        var viewBtn = target.closest('.view-project-btn');
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
        var deleteBtn = target.closest('.delete-project-btn');
        if (deleteBtn) {
            e.preventDefault();
            var projectId = deleteBtn.getAttribute('data-project-id');
            var projectName = (_c = (_a = deleteBtn.getAttribute('data-project-name')) !== null && _a !== void 0 ? _a : (_b = deleteBtn.closest('.project-card')) === null || _b === void 0 ? void 0 : _b.getAttribute('data-project-name')) !== null && _c !== void 0 ? _c : 'ce projet';
            if (projectId) {
                confirmDeleteProject(projectId, projectName);
            }
        }
    });
    // Fermeture des modaux
    var closeButtons = document.querySelectorAll('.close');
    closeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });
    document.querySelectorAll('.modal').forEach(function (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });
    // Bouton créer projet
    var createBtn = document.getElementById('create-project-btn');
    if (createBtn) {
        createBtn.addEventListener('click', function () {
            createNewProject();
        });
    }
    // Close dropdowns when clicking outside
    document.addEventListener('click', function (event) {
        var target = event.target;
        if (!target.closest('.project-dropdown')) {
            document.querySelectorAll('.dropdown-menu-custom').forEach(function (d) {
                d.classList.remove('show');
            });
        }
    });
});
function showProjectToast(message, type) {
    if (type === void 0) { type = 'success'; }
    var toast = document.createElement('div');
    toast.className = "project-toast project-toast--".concat(type);
    toast.innerHTML = "\n        <i class=\"fas fa-".concat(type === 'success' ? 'folder-open' : 'exclamation-triangle', "\"></i>\n        <span>").concat(message, "</span>\n    ");
    document.body.appendChild(toast);
    requestAnimationFrame(function () { return toast.classList.add('project-toast--visible'); });
    setTimeout(function () {
        toast.classList.remove('project-toast--visible');
        toast.addEventListener('transitionend', function () { return toast.remove(); });
    }, 3500);
}
function confirmDeleteProject(projectId, projectName) {
    var _a;
    // Supprimer une éventuelle modale déjà ouverte
    (_a = document.getElementById('project-confirm-modal')) === null || _a === void 0 ? void 0 : _a.remove();
    var modal = document.createElement('div');
    modal.id = 'project-confirm-modal';
    modal.className = 'project-confirm-overlay';
    modal.innerHTML = "\n        <div class=\"project-confirm-box\">\n            <div class=\"project-confirm-icon\">\n                <i class=\"fas fa-trash-alt\"></i>\n            </div>\n            <h3>Supprimer le projet</h3>\n            <p>\u00CAtes-vous s\u00FBr de vouloir supprimer <strong>".concat(projectName.trim(), "</strong> ? Cette action est irr\u00E9versible.</p>\n            <div class=\"project-confirm-actions\">\n                <button class=\"project-confirm-cancel\">Annuler</button>\n                <button class=\"project-confirm-delete\">Oui, supprimer</button>\n            </div>\n        </div>\n    ");
    document.body.appendChild(modal);
    requestAnimationFrame(function () { return modal.classList.add('project-confirm-overlay--visible'); });
    var closeModal = function () {
        modal.classList.remove('project-confirm-overlay--visible');
        modal.addEventListener('transitionend', function () { return modal.remove(); }, { once: true });
    };
    modal.querySelector('.project-confirm-cancel').addEventListener('click', closeModal);
    modal.addEventListener('click', function (e) { if (e.target === modal)
        closeModal(); });
    modal.querySelector('.project-confirm-delete').addEventListener('click', function () {
        closeModal();
        deleteProject(projectId);
    });
}
function deleteProject(projectId) {
    return __awaiter(this, void 0, void 0, function () {
        var response, result, error_1;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    _a.trys.push([0, 3, , 4]);
                    return [4 /*yield*/, fetch("/api/delete/project/".concat(projectId), {
                            method: 'DELETE',
                            headers: { 'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_2__.getCsrfToken)() },
                        })];
                case 1:
                    response = _a.sent();
                    return [4 /*yield*/, response.json()];
                case 2:
                    result = _a.sent();
                    if (result.success || result.delete) {
                        showProjectToast('Projet supprimé avec succès !', 'success');
                        location.reload();
                    }
                    else {
                        showProjectToast(result.error || 'Erreur lors de la suppression du projet', 'error');
                    }
                    return [3 /*break*/, 4];
                case 3:
                    error_1 = _a.sent();
                    console.error('Erreur:', error_1);
                    showProjectToast('Erreur lors de la suppression du projet', 'error');
                    return [3 /*break*/, 4];
                case 4: return [2 /*return*/];
            }
        });
    });
}
function editProject(projectId) {
    fetch("/api/project/".concat(projectId))
        .then(function (response) { return response.json(); })
        .then(function (data) {
        var _a, _b;
        var project = (_a = data.project) !== null && _a !== void 0 ? _a : (_b = data.data) === null || _b === void 0 ? void 0 : _b.project;
        if (project) {
            showEditForm(project);
        }
        else {
            showProjectToast('Erreur lors de la récupération du projet', 'error');
        }
    })
        .catch(function (error) {
        console.error('Erreur:', error);
        showProjectToast('Erreur lors de la récupération du projet', 'error');
    });
}
function createNewProject() {
    return __awaiter(this, void 0, void 0, function () {
        var formOverlay, form, closeBtn, cancelBtn;
        var _this = this;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    formOverlay = document.createElement('div');
                    formOverlay.className = 'form-overlay';
                    formOverlay.innerHTML = "\n        <div class=\"edit-form\">\n            <div class=\"form-header\">\n                <h3>Cr\u00E9er un nouveau projet</h3>\n                <button class=\"btn-close\" type=\"button\">\u00D7</button>\n            </div>\n            <form id=\"project-create-form\">\n                <div class=\"form-content\">\n\n                    <!-- Section Informations -->\n                    <div class=\"form-section form-section--info\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-folder\"></i></span>\n                            <span class=\"form-section__label\">Informations</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-group\">\n                                <label for=\"create-name\">Nom du projet *</label>\n                                <input type=\"text\" id=\"create-name\" name=\"name\" required>\n                            </div>\n                            <div class=\"form-group\">\n                                <label for=\"create-description\">Description</label>\n                                <textarea id=\"create-description\" name=\"description\" rows=\"3\"></textarea>\n                            </div>\n                        </div>\n                    </div>\n\n                    <!-- Section Planification -->\n                    <div class=\"form-section form-section--planning\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-calendar-alt\"></i></span>\n                            <span class=\"form-section__label\">Planification</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"create-begin-date\">Date de d\u00E9but</label>\n                                    <input type=\"date\" id=\"create-begin-date\" name=\"beginDate\">\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"create-theoretical-deadline\">\u00C9ch\u00E9ance th\u00E9orique *</label>\n                                    <input type=\"date\" id=\"create-theoretical-deadline\" name=\"theoricalDeadLine\" required>\n                                </div>\n                            </div>\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"create-real-deadline\">\u00C9ch\u00E9ance r\u00E9elle</label>\n                                    <input type=\"date\" id=\"create-real-deadline\" name=\"realDeadLine\">\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"create-state\">\u00C9tat du projet</label>\n                                    <select id=\"create-state\" name=\"stateId\">\n                                        <option value=\"\">S\u00E9lectionner un \u00E9tat</option>\n                                    </select>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n\n                    <!-- Section \u00C9quipe -->\n                    <div class=\"form-section form-section--team\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-users\"></i></span>\n                            <span class=\"form-section__label\">\u00C9quipe & Client</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-group\">\n                                <label for=\"create-client-search\">Client</label>\n                                <div class=\"client-search-container\">\n                                    <input type=\"text\" id=\"create-client-search\" placeholder=\"Rechercher un client par nom...\" autocomplete=\"off\">\n                                    <input type=\"hidden\" id=\"create-client-id\" name=\"clientId\">\n                                    <div class=\"client-search-results\" id=\"create-client-results\"></div>\n                                    <div class=\"client-selected\" id=\"create-client-selected\" style=\"display:none;\">\n                                        <span id=\"create-client-selected-name\"></span>\n                                        <button type=\"button\" class=\"btn-remove-client\" title=\"Retirer le client\">&times;</button>\n                                    </div>\n                                </div>\n                            </div>\n                            <div class=\"form-group\">\n                                <label for=\"create-project-manager\">Chef de projet</label>\n                                <select id=\"create-project-manager\" name=\"projectManagerId\">\n                                    <option value=\"\">S\u00E9lectionner un chef de projet</option>\n                                </select>\n                            </div>\n                        </div>\n                    </div>\n\n                </div>\n                <div class=\"form-actions\">\n                    <button type=\"button\" class=\"btn-cancel\">Annuler</button>\n                    <button type=\"submit\" class=\"btn-save\">Cr\u00E9er</button>\n                </div>\n            </form>\n        </div>\n    ";
                    document.body.appendChild(formOverlay);
                    return [4 /*yield*/, loadUsersIntoSelects(formOverlay)];
                case 1:
                    _a.sent();
                    return [4 /*yield*/, loadStatesIntoSelect(formOverlay)];
                case 2:
                    _a.sent();
                    setupClientSearch(formOverlay, 'create');
                    form = formOverlay.querySelector('#project-create-form');
                    closeBtn = formOverlay.querySelector('.btn-close');
                    cancelBtn = formOverlay.querySelector('.btn-cancel');
                    if (form) {
                        form.addEventListener('submit', function (e) { return __awaiter(_this, void 0, void 0, function () {
                            return __generator(this, function (_a) {
                                switch (_a.label) {
                                    case 0:
                                        e.preventDefault();
                                        return [4 /*yield*/, handleCreateSubmit(formOverlay)];
                                    case 1:
                                        _a.sent();
                                        return [2 /*return*/];
                                }
                            });
                        }); });
                    }
                    [closeBtn, cancelBtn].forEach(function (btn) {
                        if (btn) {
                            btn.addEventListener('click', function () {
                                document.body.removeChild(formOverlay);
                            });
                        }
                    });
                    return [2 /*return*/];
            }
        });
    });
}
function showEditForm(project) {
    return __awaiter(this, void 0, void 0, function () {
        var formOverlay, form, closeBtn, cancelBtn;
        var _this = this;
        var _a, _b, _c, _d;
        return __generator(this, function (_e) {
            switch (_e.label) {
                case 0:
                    formOverlay = document.createElement('div');
                    formOverlay.className = 'form-overlay';
                    formOverlay.innerHTML = "\n        <div class=\"edit-form\">\n            <div class=\"form-header\">\n                <h3>Modifier le projet</h3>\n                <button class=\"btn-close\" type=\"button\">\u00D7</button>\n            </div>\n            <form id=\"project-edit-form\">\n                <div class=\"form-content\">\n\n                    <!-- Section Informations -->\n                    <div class=\"form-section form-section--info\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-folder\"></i></span>\n                            <span class=\"form-section__label\">Informations</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-group\">\n                                <label for=\"edit-name\">Nom du projet *</label>\n                                <input type=\"text\" id=\"edit-name\" name=\"name\" value=\"".concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_1__.escapeHtml)(project.name), "\" required>\n                            </div>\n                            <div class=\"form-group\">\n                                <label for=\"edit-description\">Description</label>\n                                <textarea id=\"edit-description\" name=\"description\" rows=\"3\">").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_1__.escapeHtml)(project.description || ''), "</textarea>\n                            </div>\n                        </div>\n                    </div>\n\n                    <!-- Section Planification -->\n                    <div class=\"form-section form-section--planning\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-calendar-alt\"></i></span>\n                            <span class=\"form-section__label\">Planification</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"edit-begin-date\">Date de d\u00E9but</label>\n                                    <input type=\"date\" id=\"edit-begin-date\" name=\"beginDate\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_1__.formatDateForInput)(project.beginDate), "\">\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"edit-theoretical-deadline\">\u00C9ch\u00E9ance th\u00E9orique</label>\n                                    <input type=\"date\" id=\"edit-theoretical-deadline\" name=\"theoricalDeadLine\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_1__.formatDateForInput)((_b = (_a = project.theoreticalDeadline) !== null && _a !== void 0 ? _a : project.theoricalDeadLine) !== null && _b !== void 0 ? _b : ''), "\">\n                                </div>\n                            </div>\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"edit-real-deadline\">\u00C9ch\u00E9ance r\u00E9elle</label>\n                                    <input type=\"date\" id=\"edit-real-deadline\" name=\"realDeadLine\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_1__.formatDateForInput)((_d = (_c = project.realDeadline) !== null && _c !== void 0 ? _c : project.realDeadLine) !== null && _d !== void 0 ? _d : ''), "\">\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"edit-state\">\u00C9tat du projet</label>\n                                    <select id=\"edit-state\" name=\"stateId\">\n                                        <option value=\"\">S\u00E9lectionner un \u00E9tat</option>\n                                    </select>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n\n                    <!-- Section \u00C9quipe -->\n                    <div class=\"form-section form-section--team\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-users\"></i></span>\n                            <span class=\"form-section__label\">\u00C9quipe & Client</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-group\">\n                                <label for=\"edit-client-search\">Client</label>\n                                <div class=\"client-search-container\">\n                                    <input type=\"text\" id=\"edit-client-search\" placeholder=\"Rechercher un client par nom...\" autocomplete=\"off\">\n                                    <input type=\"hidden\" id=\"edit-client-id\" name=\"clientId\" value=\"").concat(project.clientId || '', "\">\n                                    <div class=\"client-search-results\" id=\"edit-client-results\"></div>\n                                    <div class=\"client-selected\" id=\"edit-client-selected\" style=\"display:none;\">\n                                        <span id=\"edit-client-selected-name\"></span>\n                                        <button type=\"button\" class=\"btn-remove-client\" title=\"Retirer le client\">&times;</button>\n                                    </div>\n                                </div>\n                            </div>\n                            <div class=\"form-group\">\n                                <label for=\"edit-project-manager\">Chef de projet</label>\n                                <select id=\"edit-project-manager\" name=\"projectManagerId\">\n                                    <option value=\"\">S\u00E9lectionner un chef de projet</option>\n                                </select>\n                            </div>\n                        </div>\n                    </div>\n\n                </div>\n                <div class=\"form-actions\">\n                    <button type=\"button\" class=\"btn-cancel\">Annuler</button>\n                    <button type=\"submit\" class=\"btn-save\">Sauvegarder</button>\n                </div>\n            </form>\n        </div>\n    ");
                    document.body.appendChild(formOverlay);
                    return [4 /*yield*/, loadUsersIntoSelects(formOverlay, project)];
                case 1:
                    _e.sent();
                    return [4 /*yield*/, loadStatesIntoSelect(formOverlay, project.stateId)];
                case 2:
                    _e.sent();
                    setupClientSearch(formOverlay, 'edit', project.clientId);
                    form = formOverlay.querySelector('#project-edit-form');
                    closeBtn = formOverlay.querySelector('.btn-close');
                    cancelBtn = formOverlay.querySelector('.btn-cancel');
                    if (form) {
                        form.addEventListener('submit', function (e) { return __awaiter(_this, void 0, void 0, function () {
                            return __generator(this, function (_a) {
                                switch (_a.label) {
                                    case 0:
                                        e.preventDefault();
                                        return [4 /*yield*/, handleEditSubmit(project.id, formOverlay)];
                                    case 1:
                                        _a.sent();
                                        return [2 /*return*/];
                                }
                            });
                        }); });
                    }
                    [closeBtn, cancelBtn].forEach(function (btn) {
                        if (btn) {
                            btn.addEventListener('click', function () {
                                document.body.removeChild(formOverlay);
                            });
                        }
                    });
                    return [2 /*return*/];
            }
        });
    });
}
function handleCreateSubmit(formOverlay) {
    return __awaiter(this, void 0, void 0, function () {
        var form, formData, beginDate, theoricalDeadLine, realDeadLine, clientId, projectManagerId, stateId, newProject, response, result, error_2;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    form = formOverlay.querySelector('#project-create-form');
                    formData = new FormData(form);
                    beginDate = formData.get('beginDate') || null;
                    theoricalDeadLine = formData.get('theoricalDeadLine') || null;
                    realDeadLine = formData.get('realDeadLine') || null;
                    clientId = formData.get('clientId') || null;
                    projectManagerId = formData.get('projectManagerId') || null;
                    stateId = formData.get('stateId') || null;
                    newProject = {
                        name: formData.get('name'),
                        description: formData.get('description') || null,
                        beginDate: beginDate,
                        theoreticalDeadline: theoricalDeadLine,
                        realDeadline: realDeadLine,
                        clientId: clientId,
                        projectManagerId: projectManagerId,
                        stateId: stateId
                    };
                    _a.label = 1;
                case 1:
                    _a.trys.push([1, 4, , 5]);
                    return [4 /*yield*/, fetch('/api/add/project', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_2__.getCsrfToken)(),
                            },
                            body: JSON.stringify(newProject)
                        })];
                case 2:
                    response = _a.sent();
                    return [4 /*yield*/, response.json()];
                case 3:
                    result = _a.sent();
                    if (result.success) {
                        document.body.removeChild(formOverlay);
                        showProjectToast('Projet créé avec succès !', 'success');
                        setTimeout(function () { return location.reload(); }, 1200);
                    }
                    else {
                        showProjectToast('Erreur : ' + (result.error || result.message), 'error');
                    }
                    return [3 /*break*/, 5];
                case 4:
                    error_2 = _a.sent();
                    console.error('Erreur:', error_2);
                    showProjectToast('Erreur lors de la création du projet', 'error');
                    return [3 /*break*/, 5];
                case 5: return [2 /*return*/];
            }
        });
    });
}
function handleEditSubmit(projectId, formOverlay) {
    return __awaiter(this, void 0, void 0, function () {
        var form, formData, beginDate, theoricalDeadLine, realDeadLine, clientId, projectManagerId, stateId, updatedProject, response, result, error_3;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    form = formOverlay.querySelector('#project-edit-form');
                    formData = new FormData(form);
                    beginDate = formData.get('beginDate') || null;
                    theoricalDeadLine = formData.get('theoricalDeadLine') || null;
                    realDeadLine = formData.get('realDeadLine') || null;
                    clientId = formData.get('clientId') || null;
                    projectManagerId = formData.get('projectManagerId') || null;
                    stateId = formData.get('stateId') || null;
                    updatedProject = {
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
                    _a.label = 1;
                case 1:
                    _a.trys.push([1, 4, , 5]);
                    return [4 /*yield*/, fetch("/api/edit/project/".concat(projectId), {
                            method: 'PUT',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_2__.getCsrfToken)(),
                            },
                            body: JSON.stringify(updatedProject)
                        })];
                case 2:
                    response = _a.sent();
                    return [4 /*yield*/, response.json()];
                case 3:
                    result = _a.sent();
                    if (result.success) {
                        document.body.removeChild(formOverlay);
                        showProjectToast('Projet modifié avec succès !', 'success');
                        setTimeout(function () { return location.reload(); }, 1200);
                    }
                    else {
                        showProjectToast('Erreur : ' + (result.error || result.message), 'error');
                    }
                    return [3 /*break*/, 5];
                case 4:
                    error_3 = _a.sent();
                    console.error('Erreur:', error_3);
                    showProjectToast('Erreur lors de la modification du projet', 'error');
                    return [3 /*break*/, 5];
                case 5: return [2 /*return*/];
            }
        });
    });
}
function loadUsersIntoSelects(formOverlay_1) {
    return __awaiter(this, arguments, void 0, function (formOverlay, project) {
        var data, users, managerSelect_1, managers, error_4;
        if (project === void 0) { project = null; }
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    _a.trys.push([0, 2, , 3]);
                    return [4 /*yield*/, _services_UserService__WEBPACK_IMPORTED_MODULE_0__["default"].loadUsersFromApi()];
                case 1:
                    data = _a.sent();
                    users = (data === null || data === void 0 ? void 0 : data.data) || [];
                    managerSelect_1 = formOverlay.querySelector('#edit-project-manager, #create-project-manager');
                    if (managerSelect_1) {
                        managers = users.filter(function (user) { return user.roleName === 'CDP'; });
                        managers.forEach(function (user) {
                            var option = document.createElement('option');
                            option.value = user.id;
                            option.textContent = "".concat(user.firstname, " ").concat(user.lastname, " (").concat(user.email, ")");
                            if (project && project.projectManagerId == user.id) {
                                option.selected = true;
                            }
                            managerSelect_1.appendChild(option);
                        });
                    }
                    return [3 /*break*/, 3];
                case 2:
                    error_4 = _a.sent();
                    console.error('Erreur lors du chargement des utilisateurs :', error_4);
                    return [3 /*break*/, 3];
                case 3: return [2 /*return*/];
            }
        });
    });
}
function loadStatesIntoSelect(formOverlay_1) {
    return __awaiter(this, arguments, void 0, function (formOverlay, selectedStateId) {
        var response, data, states, stateSelect_1, error_5;
        if (selectedStateId === void 0) { selectedStateId = null; }
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    _a.trys.push([0, 3, , 4]);
                    return [4 /*yield*/, fetch('/api/states')];
                case 1:
                    response = _a.sent();
                    return [4 /*yield*/, response.json()];
                case 2:
                    data = _a.sent();
                    states = (data === null || data === void 0 ? void 0 : data.states) || [];
                    stateSelect_1 = formOverlay.querySelector('#edit-state, #create-state');
                    if (stateSelect_1) {
                        states.forEach(function (state) {
                            var option = document.createElement('option');
                            option.value = state.id;
                            option.textContent = state.name;
                            if (selectedStateId && selectedStateId == state.id) {
                                option.selected = true;
                            }
                            stateSelect_1.appendChild(option);
                        });
                    }
                    return [3 /*break*/, 4];
                case 3:
                    error_5 = _a.sent();
                    console.error('Erreur lors du chargement des états :', error_5);
                    return [3 /*break*/, 4];
                case 4: return [2 /*return*/];
            }
        });
    });
}
function setupClientSearch(formOverlay, prefix, currentClientId) {
    if (currentClientId === void 0) { currentClientId = null; }
    var searchInput = formOverlay.querySelector("#".concat(prefix, "-client-search"));
    var hiddenInput = formOverlay.querySelector("#".concat(prefix, "-client-id"));
    var resultsDiv = formOverlay.querySelector("#".concat(prefix, "-client-results"));
    var selectedDiv = formOverlay.querySelector("#".concat(prefix, "-client-selected"));
    var selectedName = formOverlay.querySelector("#".concat(prefix, "-client-selected-name"));
    var removeBtn = formOverlay.querySelector("#".concat(prefix, "-client-selected .btn-remove-client"));
    if (!searchInput || !hiddenInput || !resultsDiv)
        return;
    var allClients = [];
    // Load all clients once
    fetch('/api/clients')
        .then(function (res) { return res.json(); })
        .then(function (data) {
        allClients = (data === null || data === void 0 ? void 0 : data.data) || (data === null || data === void 0 ? void 0 : data.clients) || [];
        // If editing with an existing clientId, show the selected client
        if (currentClientId) {
            var current = allClients.find(function (c) { return c.siret === currentClientId; });
            if (current && selectedDiv && selectedName) {
                selectedName.textContent = "".concat(current.companyname || current.companyName, " (").concat(current.siret, ")");
                selectedDiv.style.display = 'flex';
                searchInput.style.display = 'none';
            }
        }
    })
        .catch(function (err) { return console.error('Erreur chargement clients:', err); });
    var debounceTimer;
    searchInput.addEventListener('input', function () {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            var query = searchInput.value.trim().toLowerCase();
            resultsDiv.innerHTML = '';
            if (query.length < 1) {
                resultsDiv.style.display = 'none';
                return;
            }
            var filtered = allClients.filter(function (c) {
                var name = (c.companyname || c.companyName || '').toLowerCase();
                var siret = (c.siret || '').toLowerCase();
                return name.includes(query) || siret.includes(query);
            });
            if (filtered.length === 0) {
                resultsDiv.innerHTML = '<div class="client-search-item no-result">Aucun client trouvé</div>';
                resultsDiv.style.display = 'block';
                return;
            }
            filtered.slice(0, 10).forEach(function (client) {
                var item = document.createElement('div');
                item.className = 'client-search-item';
                item.textContent = "".concat(client.companyname || client.companyName, " (").concat(client.siret, ")");
                item.addEventListener('click', function () {
                    hiddenInput.value = client.siret;
                    if (selectedName)
                        selectedName.textContent = "".concat(client.companyname || client.companyName, " (").concat(client.siret, ")");
                    if (selectedDiv)
                        selectedDiv.style.display = 'flex';
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
    document.addEventListener('click', function (e) {
        if (!searchInput.contains(e.target) && !resultsDiv.contains(e.target)) {
            resultsDiv.style.display = 'none';
        }
    });
    // Remove selected client
    if (removeBtn) {
        removeBtn.addEventListener('click', function () {
            hiddenInput.value = '';
            if (selectedDiv)
                selectedDiv.style.display = 'none';
            searchInput.style.display = '';
            searchInput.value = '';
        });
    }
}
function toggleDropdown(event, btn) {
    var _a;
    event.stopPropagation();
    var dropdown = (_a = btn.closest('.project-dropdown')) === null || _a === void 0 ? void 0 : _a.querySelector('.dropdown-menu-custom');
    var allDropdowns = document.querySelectorAll('.dropdown-menu-custom');
    allDropdowns.forEach(function (d) {
        if (d !== dropdown) {
            d.classList.remove('show');
        }
    });
    if (dropdown) {
        dropdown.classList.toggle('show');
    }
}


/***/ },

/***/ "./assets/ts/pages/tasks.ts"
/*!**********************************!*\
  !*** ./assets/ts/pages/tasks.ts ***!
  \**********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   createNewTask: () => (/* binding */ createNewTask),
/* harmony export */   editTask: () => (/* binding */ editTask),
/* harmony export */   openCloseTaskModal: () => (/* binding */ openCloseTaskModal)
/* harmony export */ });
/* harmony import */ var _utils_helpers__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../utils/helpers */ "./assets/ts/utils/helpers.ts");
/* harmony import */ var _services_Api__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ../services/Api */ "./assets/ts/services/Api.ts");
var __awaiter = (undefined && undefined.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (undefined && undefined.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g = Object.create((typeof Iterator === "function" ? Iterator : Object).prototype);
    return g.next = verb(0), g["throw"] = verb(1), g["return"] = verb(2), typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};


function formatEffort(hours) {
    if (hours === null || hours === undefined || hours === 0)
        return '-';
    var hoursPerDay = 8;
    var days = Math.floor(hours / hoursPerDay);
    var remainingHours = hours % hoursPerDay;
    if (days === 0)
        return "".concat(remainingHours, "h");
    if (remainingHours === 0)
        return "".concat(days, "j");
    return "".concat(days, "j ").concat(remainingHours, "h");
}
function validateEffort(value) {
    var effort = parseFloat(value);
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
        var target = e.target;
        // Trigger nouvelle tâche
        if (target.matches('.create-task-trigger')) {
            var btn = document.getElementById('create-task-btn');
            if (btn)
                btn.click();
        }
        // Voir détails
        var viewBtn = target.closest('.view-task-btn');
        if (viewBtn) {
            e.preventDefault();
            var taskId = viewBtn.getAttribute('data-task-id');
            if (taskId)
                viewTask(taskId);
        }
        // Modifier tâche
        var editBtn = target.closest('.edit-task-btn');
        if (editBtn) {
            e.preventDefault();
            var taskId = editBtn.getAttribute('data-task-id');
            if (taskId)
                editTask(taskId);
        }
        // Supprimer tâche
        var deleteBtn = target.closest('.delete-btn');
        if (deleteBtn) {
            var taskId = deleteBtn.getAttribute('data-task-id');
            var taskName = deleteBtn.getAttribute('data-task-name');
            if (taskId && confirm("\u00CAtes-vous s\u00FBr de vouloir supprimer la t\u00E2che \"".concat(taskName, "\" ?"))) {
                deleteTask(taskId);
            }
        }
        // Clôturer tâche (Gérer le temps)
        var manageTimeBtn = target.closest('.manage-time-btn');
        if (manageTimeBtn) {
            e.preventDefault();
            var taskId = manageTimeBtn.getAttribute('data-task-id');
            if (taskId)
                openCloseTaskModal(taskId);
        }
    });
    // Fermeture des modaux
    var closeButtons = document.querySelectorAll('.close');
    closeButtons.forEach(function (button) {
        button.addEventListener('click', function () {
            var modal = this.closest('.modal');
            if (modal) {
                modal.style.display = 'none';
            }
        });
    });
    document.querySelectorAll('.modal').forEach(function (modal) {
        modal.addEventListener('click', function (e) {
            if (e.target === this) {
                this.style.display = 'none';
            }
        });
    });
    // Bouton créer tâche
    var createBtn = document.getElementById('create-task-btn');
    if (createBtn) {
        createBtn.addEventListener('click', function () {
            createNewTask();
        });
    }
    // ── Filtre par état (pills) ──────────────────────────────────────────────
    var activeStateFilter = 'all';
    var filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(function (btn) {
        btn.addEventListener('click', function () {
            filterBtns.forEach(function (b) {
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
    var filterProject = document.getElementById('filter-project');
    var filterUser = document.getElementById('filter-user');
    if (filterProject) {
        filterProject.addEventListener('change', applyAllFilters);
    }
    if (filterUser) {
        filterUser.addEventListener('change', applyAllFilters);
    }
    function applyAllFilters() {
        var projectVal = (filterProject === null || filterProject === void 0 ? void 0 : filterProject.value) || '';
        var userVal = (filterUser === null || filterUser === void 0 ? void 0 : filterUser.value) || '';
        var taskRows = document.querySelectorAll('.task-card');
        taskRows.forEach(function (row) {
            var stateId = row.getAttribute('data-state-id') || '';
            var projectId = row.getAttribute('data-project-id') || '';
            var devId = row.getAttribute('data-developer-id') || '';
            var stateOk = activeStateFilter === 'all' || stateId === activeStateFilter;
            var projectOk = !projectVal || projectId === projectVal;
            var userOk = !userVal || devId === userVal;
            row.style.display = (stateOk && projectOk && userOk) ? '' : 'none';
        });
    }
});
function viewTask(taskId) {
    fetch("/api/task/".concat(taskId))
        .then(function (response) { return response.json(); })
        .then(function (data) {
        if (data.task) {
            showTaskModal(data.task);
        }
        else {
            alert('Erreur lors de la récupération de la tâche');
        }
    })
        .catch(function (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la récupération de la tâche');
    });
}
function editTask(taskId) {
    fetch("/api/task/".concat(taskId))
        .then(function (response) { return response.json(); })
        .then(function (data) {
        if (data.task) {
            showEditForm(data.task);
        }
        else {
            alert('Erreur lors de la récupération de la tâche');
        }
    })
        .catch(function (error) {
        console.error('Erreur:', error);
        alert('Erreur lors de la récupération de la tâche');
    });
}
function showToast(message, type) {
    if (type === void 0) { type = 'success'; }
    var toast = document.createElement('div');
    toast.className = "task-toast task-toast--".concat(type);
    toast.innerHTML = "\n        <i class=\"fas fa-".concat(type === 'success' ? 'check-circle' : 'exclamation-circle', "\"></i>\n        <span>").concat(message, "</span>\n    ");
    document.body.appendChild(toast);
    // Slide in
    requestAnimationFrame(function () { return toast.classList.add('task-toast--visible'); });
    // Auto-remove after 3s
    setTimeout(function () {
        toast.classList.remove('task-toast--visible');
        toast.addEventListener('transitionend', function () { return toast.remove(); });
    }, 3000);
}
function deleteTask(taskId) {
    return __awaiter(this, void 0, void 0, function () {
        var response, result, error_1;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    _a.trys.push([0, 3, , 4]);
                    return [4 /*yield*/, fetch("/api/delete/task/".concat(taskId), {
                            method: 'DELETE',
                            headers: { 'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_1__.getCsrfToken)() },
                        })];
                case 1:
                    response = _a.sent();
                    return [4 /*yield*/, response.json()];
                case 2:
                    result = _a.sent();
                    if (result.success || result.delete) {
                        showToast('Tâche supprimée avec succès !', 'success');
                        setTimeout(function () { return location.reload(); }, 1200);
                    }
                    else {
                        showToast(result.error || 'Erreur lors de la suppression de la tâche', 'error');
                    }
                    return [3 /*break*/, 4];
                case 3:
                    error_1 = _a.sent();
                    console.error('Erreur:', error_1);
                    showToast('Erreur lors de la suppression de la tâche', 'error');
                    return [3 /*break*/, 4];
                case 4: return [2 /*return*/];
            }
        });
    });
}
function createNewTask(prefill) {
    return __awaiter(this, void 0, void 0, function () {
        var formOverlay, projectSelect, hidden, form, closeBtn, cancelBtn;
        var _this = this;
        var _a;
        return __generator(this, function (_b) {
            switch (_b.label) {
                case 0:
                    formOverlay = document.createElement('div');
                    formOverlay.className = 'form-overlay';
                    formOverlay.innerHTML = "\n        <div class=\"edit-form\">\n            <div class=\"form-header\">\n                <h3>Cr\u00E9er une nouvelle t\u00E2che</h3>\n                <button class=\"btn-close\" type=\"button\" aria-label=\"Fermer\">\u00D7</button>\n            </div>\n            <form id=\"task-create-form\">\n                <div class=\"form-content\">\n\n                    <!-- Section Identification -->\n                    <div class=\"form-section form-section--identification\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-tag\"></i></span>\n                            <span class=\"form-section__label\">Identification</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-group\">\n                                <label for=\"create-name\">Nom de la t\u00E2che *</label>\n                                <input type=\"text\" id=\"create-name\" name=\"name\" placeholder=\"Ex: D\u00E9velopper la page d'accueil\" required>\n                            </div>\n                            <div class=\"form-group\">\n                                <label for=\"create-description\">Description</label>\n                                <textarea id=\"create-description\" name=\"description\" rows=\"3\" placeholder=\"D\u00E9crivez la t\u00E2che...\"></textarea>\n                            </div>\n                            <div class=\"form-group\">\n                                <label for=\"create-effortRequired\">Effort estim\u00E9 (heures) *</label>\n                                <input type=\"number\" id=\"create-effortRequired\" name=\"effortRequired\" min=\"0.5\" max=\"99.99\" step=\"0.5\" placeholder=\"Ex: 8\" required>\n                                <div class=\"form-hint\">8h = 1 journ\u00E9e \u00B7 4h = demi-journ\u00E9e \u00B7 max 99.99h</div>\n                            </div>\n                        </div>\n                    </div>\n\n                    <!-- Section Cat\u00E9gorisation -->\n                    <div class=\"form-section form-section--categorisation\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-layer-group\"></i></span>\n                            <span class=\"form-section__label\">Cat\u00E9gorisation</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"create-type\">Type *</label>\n                                    <select id=\"create-type\" name=\"type\" required>\n                                        <option value=\"\">S\u00E9lectionner un type</option>\n                                        <optgroup label=\"Front-end\">\n                                            <option value=\"UI / Int\u00E9gration\">UI / Int\u00E9gration</option>\n                                            <option value=\"Composant\">Composant</option>\n                                            <option value=\"Animation / UX\">Animation / UX</option>\n                                        </optgroup>\n                                        <optgroup label=\"Back-end\">\n                                            <option value=\"API / Endpoint\">API / Endpoint</option>\n                                            <option value=\"Base de donn\u00E9es\">Base de donn\u00E9es</option>\n                                            <option value=\"Migration\">Migration</option>\n                                        </optgroup>\n                                        <optgroup label=\"Transversal\">\n                                            <option value=\"Bug fix\">Bug fix</option>\n                                            <option value=\"Refactoring\">Refactoring</option>\n                                            <option value=\"Tests\">Tests</option>\n                                            <option value=\"Documentation\">Documentation</option>\n                                            <option value=\"Review / Code review\">Review / Code review</option>\n                                            <option value=\"D\u00E9ploiement / DevOps\">D\u00E9ploiement / DevOps</option>\n                                            <option value=\"S\u00E9curit\u00E9\">S\u00E9curit\u00E9</option>\n                                            <option value=\"Performance\">Performance</option>\n                                        </optgroup>\n                                        <optgroup label=\"Gestion\">\n                                            <option value=\"Analyse / Sp\u00E9cification\">Analyse / Sp\u00E9cification</option>\n                                            <option value=\"R\u00E9union / Point\">R\u00E9union / Point</option>\n                                            <option value=\"Recherche / R&D\">Recherche / R&D</option>\n                                        </optgroup>\n                                    </select>\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"create-format\">Format</label>\n                                    <input type=\"text\" id=\"create-format\" name=\"format\" placeholder=\"Ex: Web\">\n                                </div>\n                            </div>\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"create-priority\">Priorit\u00E9</label>\n                                    <select id=\"create-priority\" name=\"priority\">\n                                        <option value=\"\">S\u00E9lectionner une priorit\u00E9</option>\n                                        <option value=\"high\">\uD83D\uDD34 Haute</option>\n                                        <option value=\"medium\">\uD83D\uDFE1 Moyenne</option>\n                                        <option value=\"low\">\uD83D\uDFE2 Basse</option>\n                                    </select>\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"create-difficulty\">Difficult\u00E9</label>\n                                    <select id=\"create-difficulty\" name=\"difficulty\">\n                                        <option value=\"\">S\u00E9lectionner une difficult\u00E9</option>\n                                        <option value=\"easy\">\uD83D\uDFE2 Facile</option>\n                                        <option value=\"medium\">\uD83D\uDFE1 Moyenne</option>\n                                        <option value=\"hard\">\uD83D\uDD34 Difficile</option>\n                                    </select>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n\n                    <!-- Section Planification -->\n                    <div class=\"form-section form-section--planning\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-calendar-alt\"></i></span>\n                            <span class=\"form-section__label\">Planification</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"create-begin-date\">Date de d\u00E9but</label>\n                                    <input type=\"datetime-local\" id=\"create-begin-date\" name=\"beginDate\">\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"create-theoretical-end-date\">\u00C9ch\u00E9ance th\u00E9orique</label>\n                                    <input type=\"datetime-local\" id=\"create-theoretical-end-date\" name=\"theoricalEndDate\">\n                                </div>\n                            </div>\n                            <div class=\"form-group\">\n                                <label for=\"create-real-end-date\">\u00C9ch\u00E9ance r\u00E9elle</label>\n                                <input type=\"datetime-local\" id=\"create-real-end-date\" name=\"realEndDate\">\n                            </div>\n                        </div>\n                    </div>\n\n                    <!-- Section Assignation -->\n                    <div class=\"form-section form-section--assignation\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-user-cog\"></i></span>\n                            <span class=\"form-section__label\">Assignation</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"create-developer\">D\u00E9veloppeur assign\u00E9</label>\n                                    <select id=\"create-developer\" name=\"developerId\">\n                                        <option value=\"\">S\u00E9lectionner un d\u00E9veloppeur</option>\n                                    </select>\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"create-project\">Projet</label>\n                                    <select id=\"create-project\" name=\"projectId\">\n                                        <option value=\"\">S\u00E9lectionner un projet</option>\n                                    </select>\n                                </div>\n                            </div>\n                            <div class=\"form-group\">\n                                <label for=\"create-status\">Statut</label>\n                                <select id=\"create-status\" name=\"stateId\">\n                                    <option value=\"\">S\u00E9lectionner un statut</option>\n                                </select>\n                            </div>\n                        </div>\n                    </div>\n\n                </div>\n                <div class=\"form-actions\">\n                    <button type=\"button\" class=\"btn-cancel\">Annuler</button>\n                    <button type=\"submit\" class=\"btn-save\">Cr\u00E9er</button>\n                </div>\n            </form>\n        </div>\n    ";
                    document.body.appendChild(formOverlay);
                    return [4 /*yield*/, loadUsersAndProjectsIntoSelects(formOverlay)];
                case 1:
                    _b.sent();
                    if (prefill) {
                        projectSelect = formOverlay.querySelector('#create-project');
                        if (projectSelect) {
                            projectSelect.value = prefill.projectId;
                            projectSelect.style.pointerEvents = 'none';
                            projectSelect.style.opacity = '0.6';
                            hidden = document.createElement('input');
                            hidden.type = 'hidden';
                            hidden.name = 'projectId';
                            hidden.value = prefill.projectId;
                            (_a = projectSelect.parentElement) === null || _a === void 0 ? void 0 : _a.appendChild(hidden);
                            // désactiver le name du select pour éviter le doublon
                            projectSelect.removeAttribute('name');
                        }
                    }
                    form = formOverlay.querySelector('#task-create-form');
                    closeBtn = formOverlay.querySelector('.btn-close');
                    cancelBtn = formOverlay.querySelector('.btn-cancel');
                    if (form) {
                        form.addEventListener('submit', function (e) { return __awaiter(_this, void 0, void 0, function () {
                            return __generator(this, function (_a) {
                                switch (_a.label) {
                                    case 0:
                                        e.preventDefault();
                                        return [4 /*yield*/, handleCreateSubmit(formOverlay)];
                                    case 1:
                                        _a.sent();
                                        return [2 /*return*/];
                                }
                            });
                        }); });
                    }
                    [closeBtn, cancelBtn].forEach(function (btn) {
                        if (btn) {
                            btn.addEventListener('click', function () {
                                document.body.removeChild(formOverlay);
                            });
                        }
                    });
                    formOverlay.addEventListener('click', function (e) {
                        if (e.target === formOverlay) {
                            document.body.removeChild(formOverlay);
                        }
                    });
                    return [2 /*return*/];
            }
        });
    });
}
function showEditForm(task) {
    return __awaiter(this, void 0, void 0, function () {
        var formOverlay, form, closeBtn, cancelBtn;
        var _this = this;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    formOverlay = document.createElement('div');
                    formOverlay.className = 'form-overlay';
                    formOverlay.innerHTML = "\n        <div class=\"edit-form\">\n            <div class=\"form-header\">\n                <h3>Modifier la t\u00E2che</h3>\n                <button class=\"btn-close\" type=\"button\" aria-label=\"Fermer\">\u00D7</button>\n            </div>\n            <form id=\"task-edit-form\">\n                <div class=\"form-content\">\n\n                    <!-- Section Identification -->\n                    <div class=\"form-section form-section--identification\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-tag\"></i></span>\n                            <span class=\"form-section__label\">Identification</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-group\">\n                                <label for=\"edit-name\">Nom de la t\u00E2che *</label>\n                                <input type=\"text\" id=\"edit-name\" name=\"name\" value=\"".concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(task.name), "\" required>\n                            </div>\n                            <div class=\"form-group\">\n                                <label for=\"edit-description\">Description</label>\n                                <textarea id=\"edit-description\" name=\"description\" rows=\"3\">").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(task.description || ''), "</textarea>\n                            </div>\n                            <div class=\"form-group\">\n                                <label for=\"edit-effortRequired\">Effort estim\u00E9 (heures) *</label>\n                                <input type=\"number\" id=\"edit-effortRequired\" name=\"effortRequired\" value=\"").concat(task.effortrequired || '', "\" min=\"0.5\" max=\"99.99\" step=\"0.5\" required>\n                                <div class=\"form-hint\">8h = 1 journ\u00E9e \u00B7 4h = demi-journ\u00E9e \u00B7 max 99.99h</div>\n                            </div>\n                        </div>\n                    </div>\n\n                    <!-- Section Cat\u00E9gorisation -->\n                    <div class=\"form-section form-section--categorisation\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-layer-group\"></i></span>\n                            <span class=\"form-section__label\">Cat\u00E9gorisation</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"edit-type\">Type *</label>\n                                    <select id=\"edit-type\" name=\"type\" required>\n                                        <option value=\"\">S\u00E9lectionner un type</option>\n                                        <optgroup label=\"Front-end\">\n                                            <option value=\"UI / Int\u00E9gration\" ").concat(task.type === 'UI / Intégration' ? 'selected' : '', ">UI / Int\u00E9gration</option>\n                                            <option value=\"Composant\" ").concat(task.type === 'Composant' ? 'selected' : '', ">Composant</option>\n                                            <option value=\"Animation / UX\" ").concat(task.type === 'Animation / UX' ? 'selected' : '', ">Animation / UX</option>\n                                            <option value=\"Back-end\" ").concat(task.type === 'Back-end' ? 'selected' : '', ">Back-end</option>\n                                        </optgroup>\n                                        <optgroup label=\"API / Endpoint\">\n                                            <option value=\"API / Endpoint\" ").concat(task.type === 'API / Endpoint' ? 'selected' : '', ">API / Endpoint</option>\n                                            <option value=\"Base de donn\u00E9es\" ").concat(task.type === 'Base de données' ? 'selected' : '', ">Base de donn\u00E9es</option>\n                                            <option value=\"Migration\" ").concat(task.type === 'Migration' ? 'selected' : '', ">Migration</option>\n                                        </optgroup>\n                                        <optgroup label=\"Transversal\">\n                                            <option value=\"Bug fix\" ").concat(task.type === 'Bug fix' ? 'selected' : '', ">Bug fix</option>\n                                            <option value=\"Refactoring\" ").concat(task.type === 'Refactoring' ? 'selected' : '', ">Refactoring</option>\n                                            <option value=\"Tests\" ").concat(task.type === 'Tests' ? 'selected' : '', ">Tests</option>\n                                            <option value=\"Documentation\" ").concat(task.type === 'Documentation' ? 'selected' : '', ">Documentation</option>\n                                            <option value=\"Review / Code review\" ").concat(task.type === 'Review / Code review' ? 'selected' : '', ">Review / Code review</option>\n                                            <option value=\"D\u00E9ploiement / DevOps\" ").concat(task.type === 'Déploiement / DevOps' ? 'selected' : '', ">D\u00E9ploiement / DevOps</option>\n                                            <option value=\"S\u00E9curit\u00E9\" ").concat(task.type === 'Sécurité' ? 'selected' : '', ">S\u00E9curit\u00E9</option>\n                                            <option value=\"Performance\" ").concat(task.type === 'Performance' ? 'selected' : '', ">Performance</option>\n                                        </optgroup>\n                                        <optgroup label=\"Gestion\">\n                                            <option value=\"Analyse / Sp\u00E9cification\" ").concat(task.type === 'Analyse / Spécification' ? 'selected' : '', ">Analyse / Sp\u00E9cification</option>\n                                            <option value=\"R\u00E9union / Point\" ").concat(task.type === 'Réunion / Point' ? 'selected' : '', ">R\u00E9union / Point</option>\n                                            <option value=\"Recherche / R&D\" ").concat(task.type === 'Recherche / R&D' ? 'selected' : '', ">Recherche / R&D</option>\n                                        </optgroup>\n                                    </select>\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"edit-format\">Format</label>\n                                    <input type=\"text\" id=\"edit-format\" name=\"format\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.escapeHtml)(task.format || ''), "\">\n                                </div>\n                            </div>\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"edit-priority\">Priorit\u00E9</label>\n                                    <select id=\"edit-priority\" name=\"priority\">\n                                        <option value=\"\">S\u00E9lectionner une priorit\u00E9</option>\n                                        <option value=\"high\" ").concat(task.priority === 'high' ? 'selected' : '', ">\uD83D\uDD34 Haute</option>\n                                        <option value=\"medium\" ").concat(task.priority === 'medium' ? 'selected' : '', ">\uD83D\uDFE1 Moyenne</option>\n                                        <option value=\"low\" ").concat(task.priority === 'low' ? 'selected' : '', ">\uD83D\uDFE2 Basse</option>\n                                    </select>\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"edit-difficulty\">Difficult\u00E9</label>\n                                    <select id=\"edit-difficulty\" name=\"difficulty\">\n                                        <option value=\"\">S\u00E9lectionner une difficult\u00E9</option>\n                                        <option value=\"easy\" ").concat(task.difficulty === 'easy' ? 'selected' : '', ">\uD83D\uDFE2 Facile</option>\n                                        <option value=\"medium\" ").concat(task.difficulty === 'medium' ? 'selected' : '', ">\uD83D\uDFE1 Moyenne</option>\n                                        <option value=\"hard\" ").concat(task.difficulty === 'hard' ? 'selected' : '', ">\uD83D\uDD34 Difficile</option>\n                                    </select>\n                                </div>\n                            </div>\n                        </div>\n                    </div>\n\n                    <!-- Section Planification -->\n                    <div class=\"form-section form-section--planning\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-calendar-alt\"></i></span>\n                            <span class=\"form-section__label\">Planification</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"edit-begin-date\">Date de d\u00E9but</label>\n                                    <input type=\"datetime-local\" id=\"edit-begin-date\" name=\"beginDate\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.formatDateTimeForInput)(task.beginDate), "\">\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"edit-theoretical-end-date\">\u00C9ch\u00E9ance th\u00E9orique</label>\n                                    <input type=\"datetime-local\" id=\"edit-theoretical-end-date\" name=\"theoricalEndDate\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.formatDateTimeForInput)(task.theoricalEndDate), "\">\n                                </div>\n                            </div>\n                            <div class=\"form-group\">\n                                <label for=\"edit-real-end-date\">\u00C9ch\u00E9ance r\u00E9elle</label>\n                                <input type=\"datetime-local\" id=\"edit-real-end-date\" name=\"realEndDate\" value=\"").concat((0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.formatDateTimeForInput)(task.realEndDate), "\">\n                            </div>\n                        </div>\n                    </div>\n\n                    <!-- Section Assignation -->\n                    <div class=\"form-section form-section--assignation\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-user-cog\"></i></span>\n                            <span class=\"form-section__label\">Assignation</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-row\">\n                                <div class=\"form-group\">\n                                    <label for=\"edit-developer\">D\u00E9veloppeur assign\u00E9</label>\n                                    <select id=\"edit-developer\" name=\"developerId\">\n                                        <option value=\"\">S\u00E9lectionner un d\u00E9veloppeur</option>\n                                    </select>\n                                </div>\n                                <div class=\"form-group\">\n                                    <label for=\"edit-project\">Projet</label>\n                                    <select id=\"edit-project\" name=\"projectId\">\n                                        <option value=\"\">S\u00E9lectionner un projet</option>\n                                    </select>\n                                </div>\n                            </div>\n                            <div class=\"form-group\">\n                                <label for=\"edit-status\">Statut</label>\n                                <select id=\"edit-status\" name=\"stateId\">\n                                    <option value=\"\">S\u00E9lectionner un statut</option>\n                                </select>\n                            </div>\n                        </div>\n                    </div>\n\n                </div>\n                <div class=\"form-actions\">\n                    <button type=\"button\" class=\"btn-cancel\">Annuler</button>\n                    <button type=\"submit\" class=\"btn-save\">Sauvegarder</button>\n                </div>\n            </form>\n        </div>\n    ");
                    document.body.appendChild(formOverlay);
                    return [4 /*yield*/, loadUsersAndProjectsIntoSelects(formOverlay, task)];
                case 1:
                    _a.sent();
                    form = formOverlay.querySelector('#task-edit-form');
                    closeBtn = formOverlay.querySelector('.btn-close');
                    cancelBtn = formOverlay.querySelector('.btn-cancel');
                    if (form) {
                        form.addEventListener('submit', function (e) { return __awaiter(_this, void 0, void 0, function () {
                            return __generator(this, function (_a) {
                                switch (_a.label) {
                                    case 0:
                                        e.preventDefault();
                                        return [4 /*yield*/, handleEditSubmit(task.id, formOverlay)];
                                    case 1:
                                        _a.sent();
                                        return [2 /*return*/];
                                }
                            });
                        }); });
                    }
                    [closeBtn, cancelBtn].forEach(function (btn) {
                        if (btn) {
                            btn.addEventListener('click', function () {
                                document.body.removeChild(formOverlay);
                            });
                        }
                    });
                    formOverlay.addEventListener('click', function (e) {
                        if (e.target === formOverlay) {
                            document.body.removeChild(formOverlay);
                        }
                    });
                    return [2 /*return*/];
            }
        });
    });
}
function handleCreateSubmit(formOverlay) {
    return __awaiter(this, void 0, void 0, function () {
        var form, formData, newTask, effortValidation, response, result, error_2;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    form = formOverlay.querySelector('#task-create-form');
                    formData = new FormData(form);
                    newTask = {};
                    formData.forEach(function (value, key) { return newTask[key] = value; });
                    effortValidation = validateEffort(newTask.effortRequired);
                    if (effortValidation) {
                        showToast(effortValidation, 'error');
                        return [2 /*return*/];
                    }
                    newTask.effortRequired = parseFloat(newTask.effortRequired);
                    _a.label = 1;
                case 1:
                    _a.trys.push([1, 4, , 5]);
                    return [4 /*yield*/, fetch('/api/add/task', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_1__.getCsrfToken)() },
                            body: JSON.stringify(newTask)
                        })];
                case 2:
                    response = _a.sent();
                    return [4 /*yield*/, response.json()];
                case 3:
                    result = _a.sent();
                    if (result.success) {
                        document.body.removeChild(formOverlay);
                        showToast('Tâche créée avec succès !', 'success');
                        setTimeout(function () { return location.reload(); }, 1200);
                    }
                    else {
                        showToast('Erreur : ' + (result.error || result.message), 'error');
                    }
                    return [3 /*break*/, 5];
                case 4:
                    error_2 = _a.sent();
                    console.error('Erreur:', error_2);
                    showToast('Erreur lors de la création de la tâche', 'error');
                    return [3 /*break*/, 5];
                case 5: return [2 /*return*/];
            }
        });
    });
}
function handleEditSubmit(taskId, formOverlay) {
    return __awaiter(this, void 0, void 0, function () {
        var form, formData, updatedTask, effortValidation, response, result, error_3;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    form = formOverlay.querySelector('#task-edit-form');
                    formData = new FormData(form);
                    updatedTask = { id: taskId };
                    formData.forEach(function (value, key) { return updatedTask[key] = value; });
                    if (updatedTask.effortRequired) {
                        effortValidation = validateEffort(updatedTask.effortRequired);
                        if (effortValidation) {
                            showToast(effortValidation, 'error');
                            return [2 /*return*/];
                        }
                        updatedTask.effortRequired = parseFloat(updatedTask.effortRequired);
                    }
                    _a.label = 1;
                case 1:
                    _a.trys.push([1, 4, , 5]);
                    return [4 /*yield*/, fetch("/api/edit/task/".concat(taskId), {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_1__.getCsrfToken)() },
                            body: JSON.stringify(updatedTask)
                        })];
                case 2:
                    response = _a.sent();
                    return [4 /*yield*/, response.json()];
                case 3:
                    result = _a.sent();
                    if (result.success) {
                        document.body.removeChild(formOverlay);
                        showToast('Tâche modifiée avec succès !', 'success');
                        setTimeout(function () { return location.reload(); }, 1200);
                    }
                    else {
                        showToast('Erreur : ' + (result.error || result.message), 'error');
                    }
                    return [3 /*break*/, 5];
                case 4:
                    error_3 = _a.sent();
                    console.error('Erreur:', error_3);
                    showToast('Erreur lors de la modification de la tâche', 'error');
                    return [3 /*break*/, 5];
                case 5: return [2 /*return*/];
            }
        });
    });
}
function loadUsersAndProjectsIntoSelects(formOverlay_1) {
    return __awaiter(this, arguments, void 0, function (formOverlay, task) {
        var _a, usersRes, projectsRes, statesRes, absencesRes, users, projectsData, projects, states, absencesData, absentIds_1, developerSelect_1, warnDiv_1, projectSelect_1, statusSelect_1, error_4;
        var _b, _c, _d, _e, _f;
        if (task === void 0) { task = null; }
        return __generator(this, function (_g) {
            switch (_g.label) {
                case 0:
                    _g.trys.push([0, 6, , 7]);
                    return [4 /*yield*/, Promise.all([
                            fetch('/api/users'),
                            fetch('/api/projects'),
                            fetch('/api/states'),
                            fetch('/api/absences/active'),
                        ])];
                case 1:
                    _a = _g.sent(), usersRes = _a[0], projectsRes = _a[1], statesRes = _a[2], absencesRes = _a[3];
                    return [4 /*yield*/, usersRes.json()];
                case 2:
                    users = ((_b = (_g.sent())) === null || _b === void 0 ? void 0 : _b.data) || [];
                    return [4 /*yield*/, projectsRes.json()];
                case 3:
                    projectsData = _g.sent();
                    projects = ((_c = projectsData === null || projectsData === void 0 ? void 0 : projectsData.data) === null || _c === void 0 ? void 0 : _c.projects) || (projectsData === null || projectsData === void 0 ? void 0 : projectsData.projects) || [];
                    console.log('Projets reçus:', JSON.stringify(projects[0]));
                    return [4 /*yield*/, statesRes.json()];
                case 4:
                    states = ((_d = (_g.sent())) === null || _d === void 0 ? void 0 : _d.states) || [];
                    return [4 /*yield*/, absencesRes.json().catch(function () { return ({}); })];
                case 5:
                    absencesData = _g.sent();
                    absentIds_1 = new Set(((_e = absencesData.absences) !== null && _e !== void 0 ? _e : []).map(function (a) { return a.user_id; }));
                    developerSelect_1 = formOverlay.querySelector('#edit-developer, #create-developer');
                    if (developerSelect_1) {
                        users.forEach(function (user) {
                            var option = document.createElement('option');
                            option.value = user.id;
                            var absentLabel = absentIds_1.has(user.id) ? ' ⚠️ (absent)' : '';
                            option.textContent = "".concat(user.firstname, " ").concat(user.lastname, " (").concat(user.email, ")").concat(absentLabel);
                            if (absentIds_1.has(user.id)) {
                                option.setAttribute('data-absent', 'true');
                            }
                            if (task && task.developerId == user.id) {
                                option.selected = true;
                            }
                            developerSelect_1.appendChild(option);
                        });
                        warnDiv_1 = document.createElement('div');
                        warnDiv_1.id = 'task-absence-warning';
                        warnDiv_1.style.cssText = 'color:#b45309;background:#fef3c7;border:1px solid #fbbf24;border-radius:6px;padding:.4rem .75rem;font-size:.82rem;margin-top:.35rem;display:none;';
                        warnDiv_1.textContent = '⚠️ Ce collaborateur est actuellement en absence.';
                        (_f = developerSelect_1.parentElement) === null || _f === void 0 ? void 0 : _f.appendChild(warnDiv_1);
                        developerSelect_1.addEventListener('change', function () {
                            var selected = developerSelect_1.selectedOptions[0];
                            var isAbsent = (selected === null || selected === void 0 ? void 0 : selected.getAttribute('data-absent')) === 'true';
                            warnDiv_1.style.display = isAbsent ? 'block' : 'none';
                        });
                        // Show warning immediately if pre-selected user is absent
                        if (task && task.developerId && absentIds_1.has(task.developerId)) {
                            warnDiv_1.style.display = 'block';
                        }
                    }
                    projectSelect_1 = formOverlay.querySelector('#edit-project, #create-project');
                    if (projectSelect_1) {
                        console.log('[tasks.ts] Loading projects into select:', projects);
                        projects.forEach(function (project) {
                            console.log("[tasks.ts] Project: ID=".concat(project.id, " (type: ").concat(typeof project.id, "), Name=").concat(project.name));
                            var option = document.createElement('option');
                            option.value = project.id;
                            option.textContent = project.name;
                            if (task && task.projectId == project.id) {
                                option.selected = true;
                            }
                            projectSelect_1.appendChild(option);
                        });
                    }
                    statusSelect_1 = formOverlay.querySelector('#edit-status, #create-status');
                    if (statusSelect_1) {
                        states.forEach(function (state) {
                            var option = document.createElement('option');
                            option.value = state.id;
                            option.textContent = state.name;
                            if (task && task.stateId == state.id) {
                                option.selected = true;
                            }
                            statusSelect_1.appendChild(option);
                        });
                    }
                    return [3 /*break*/, 7];
                case 6:
                    error_4 = _g.sent();
                    console.error('Erreur lors du chargement des données :', error_4);
                    showToast('Erreur lors du chargement des données', 'error');
                    return [3 /*break*/, 7];
                case 7: return [2 /*return*/];
            }
        });
    });
}
function showTaskModal(task) {
    var _a;
    var priorityConfig = {
        high: { label: 'Haute', color: '#991b1b', bg: '#fee2e2', dot: '#ef4444' },
        medium: { label: 'Moyenne', color: '#92400e', bg: '#fef3c7', dot: '#f59e0b' },
        low: { label: 'Basse', color: '#065f46', bg: '#dcfce7', dot: '#10b981' },
    };
    var prio = (_a = priorityConfig[(task.priority || '').toLowerCase()]) !== null && _a !== void 0 ? _a : { label: task.priority || '—', color: '#475569', bg: '#f1f5f9', dot: '#94a3b8' };
    function row(icon, label, value, accent) {
        if (accent === void 0) { accent = false; }
        return "\n        <div class=\"td-row".concat(accent ? ' td-row--accent' : '', "\">\n            <span class=\"td-row__label\"><i class=\"fas fa-").concat(icon, "\"></i>").concat(label, "</span>\n            <span class=\"td-row__value\">").concat(value || '—', "</span>\n        </div>");
    }
    var overlay = document.createElement('div');
    overlay.className = 'td-overlay';
    overlay.innerHTML = "\n        <div class=\"td-panel\">\n            <div class=\"td-panel__header\">\n                <div class=\"td-panel__icon\"><i class=\"fas fa-tasks\"></i></div>\n                <div class=\"td-panel__title\">\n                    <p class=\"td-panel__code\">TASK-".concat(task.id, "</p>\n                    <h2 class=\"td-panel__name\">").concat(task.name, "</h2>\n                </div>\n                <button class=\"td-panel__close\" aria-label=\"Fermer\"><i class=\"fas fa-times\"></i></button>\n            </div>\n\n            <div class=\"td-panel__priority-bar\">\n                <span class=\"td-panel__badge\" style=\"background:").concat(prio.bg, ";color:").concat(prio.color, ";\">\n                    <span class=\"td-panel__badge-dot\" style=\"background:").concat(prio.dot, ";\"></span>\n                    Priorit\u00E9 ").concat(prio.label, "\n                </span>\n                ").concat(task.effortrequired ? "<span class=\"td-panel__effort\"><i class=\"fas fa-stopwatch\"></i>".concat(formatEffort(task.effortrequired), " estim\u00E9s</span>") : '', "\n            </div>\n\n            ").concat(task.description ? "<p class=\"td-panel__desc\">".concat(task.description, "</p>") : '', "\n\n            <div class=\"td-panel__section-title\">Informations</div>\n            <div class=\"td-rows\">\n                ").concat(row('tag', 'Type', task.type || '—'), "\n                ").concat(row('th-large', 'Format', task.format || '—'), "\n                ").concat(row('bolt', 'Difficulté', task.difficulty || '—'), "\n                ").concat(row('calendar-plus', 'Début', task.beginDate ? (0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.formatDate)(task.beginDate) : '—'), "\n                ").concat(row('calendar-check', 'Échéance théorique', task.theoricalEndDate ? (0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.formatDate)(task.theoricalEndDate) : '—', true), "\n                ").concat(row('calendar-times', 'Échéance réelle', task.realEndDate ? (0,_utils_helpers__WEBPACK_IMPORTED_MODULE_0__.formatDate)(task.realEndDate) : '—'), "\n                ").concat(task.effortmade ? row('history', 'Effort réalisé', formatEffort(task.effortmade)) : '', "\n            </div>\n        </div>");
    document.body.appendChild(overlay);
    requestAnimationFrame(function () { return overlay.classList.add('td-overlay--visible'); });
    var triggerEl = document.activeElement;
    var closeBtn = overlay.querySelector('.td-panel__close');
    closeBtn === null || closeBtn === void 0 ? void 0 : closeBtn.focus();
    var close = function () {
        overlay.classList.remove('td-overlay--visible');
        overlay.addEventListener('transitionend', function () { overlay.remove(); triggerEl === null || triggerEl === void 0 ? void 0 : triggerEl.focus(); }, { once: true });
    };
    // Focus trap within overlay
    overlay.addEventListener('keydown', function (e) {
        if (e.key !== 'Tab')
            return;
        var focusable = Array.from(overlay.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])'));
        if (focusable.length === 0)
            return;
        var first = focusable[0];
        var last = focusable[focusable.length - 1];
        if (e.shiftKey && document.activeElement === first) {
            last.focus();
            e.preventDefault();
        }
        else if (!e.shiftKey && document.activeElement === last) {
            first.focus();
            e.preventDefault();
        }
    });
    closeBtn === null || closeBtn === void 0 ? void 0 : closeBtn.addEventListener('click', close);
    overlay.addEventListener('click', function (e) { if (e.target === overlay)
        close(); });
    document.addEventListener('keydown', function onKey(e) {
        if (e.key === 'Escape') {
            close();
            document.removeEventListener('keydown', onKey);
        }
    });
}
function openCloseTaskModal(taskId) {
    return __awaiter(this, void 0, void 0, function () {
        var task, res, data, e_1, now, defaultDate, overlay, triggerCloseEl, form, closeBtn, cancelBtn, firstInput, removeOverlay;
        var _this = this;
        var _a;
        return __generator(this, function (_b) {
            switch (_b.label) {
                case 0:
                    task = null;
                    _b.label = 1;
                case 1:
                    _b.trys.push([1, 4, , 5]);
                    return [4 /*yield*/, fetch("/api/task/".concat(taskId))];
                case 2:
                    res = _b.sent();
                    return [4 /*yield*/, res.json()];
                case 3:
                    data = _b.sent();
                    task = (_a = data.task) !== null && _a !== void 0 ? _a : null;
                    return [3 /*break*/, 5];
                case 4:
                    e_1 = _b.sent();
                    console.error('Erreur récupération tâche:', e_1);
                    return [3 /*break*/, 5];
                case 5:
                    now = new Date();
                    defaultDate = now.toISOString().slice(0, 16);
                    overlay = document.createElement('div');
                    overlay.className = 'form-overlay';
                    overlay.innerHTML = "\n        <div class=\"edit-form\">\n            <div class=\"form-header\">\n                <h3>Cl\u00F4turer la t\u00E2che</h3>\n                <button class=\"btn-close\" type=\"button\">\u00D7</button>\n            </div>\n            <form id=\"close-task-form\">\n                <div class=\"form-content\">\n\n                    <div class=\"form-section form-section--closure\">\n                        <div class=\"form-section__header\">\n                            <span class=\"form-section__icon\"><i class=\"fas fa-flag-checkered\"></i></span>\n                            <span class=\"form-section__label\">Finalisation</span>\n                        </div>\n                        <div class=\"form-section__body\">\n                            <div class=\"form-group\">\n                                <label for=\"close-real-end-date\">Date de fin r\u00E9elle *</label>\n                                <input type=\"datetime-local\" id=\"close-real-end-date\" name=\"realEndDate\" value=\"".concat((task === null || task === void 0 ? void 0 : task.realEndDate) ? task.realEndDate.slice(0, 16) : defaultDate, "\" required>\n                            </div>\n                            <div class=\"form-group\">\n                                <label for=\"close-effort-made\">Effort r\u00E9el consomm\u00E9 (heures) *</label>\n                                <input type=\"number\" id=\"close-effort-made\" name=\"effortMade\" min=\"0.5\" max=\"999.99\" step=\"0.5\" value=\"").concat((task === null || task === void 0 ? void 0 : task.effortmade) || '', "\" placeholder=\"Ex: 12\" required>\n                                <div class=\"form-hint\">8h = 1 journ\u00E9e \u00B7 4h = demi-journ\u00E9e</div>\n                            </div>\n                        </div>\n                    </div>\n\n                </div>\n                <div class=\"form-actions\">\n                    <button type=\"button\" class=\"btn-cancel\">Annuler</button>\n                    <button type=\"submit\" class=\"btn-save\">Confirmer la cl\u00F4ture</button>\n                </div>\n            </form>\n        </div>\n    ");
                    document.body.appendChild(overlay);
                    triggerCloseEl = document.activeElement;
                    form = overlay.querySelector('#close-task-form');
                    closeBtn = overlay.querySelector('.btn-close');
                    cancelBtn = overlay.querySelector('.btn-cancel');
                    firstInput = overlay.querySelector('input,select,textarea,button');
                    firstInput === null || firstInput === void 0 ? void 0 : firstInput.focus();
                    removeOverlay = function () { document.body.removeChild(overlay); triggerCloseEl === null || triggerCloseEl === void 0 ? void 0 : triggerCloseEl.focus(); };
                    // Focus trap
                    overlay.addEventListener('keydown', function (e) {
                        if (e.key !== 'Tab')
                            return;
                        var focusable = Array.from(overlay.querySelectorAll('a[href],button:not([disabled]),input:not([disabled]),select:not([disabled]),textarea:not([disabled]),[tabindex]:not([tabindex="-1"])'));
                        if (focusable.length === 0)
                            return;
                        var first = focusable[0];
                        var last = focusable[focusable.length - 1];
                        if (e.shiftKey && document.activeElement === first) {
                            last.focus();
                            e.preventDefault();
                        }
                        else if (!e.shiftKey && document.activeElement === last) {
                            first.focus();
                            e.preventDefault();
                        }
                    });
                    [closeBtn, cancelBtn].forEach(function (btn) { return btn === null || btn === void 0 ? void 0 : btn.addEventListener('click', removeOverlay); });
                    overlay.addEventListener('click', function (e) { if (e.target === overlay)
                        removeOverlay(); });
                    if (form) {
                        form.addEventListener('submit', function (e) { return __awaiter(_this, void 0, void 0, function () {
                            return __generator(this, function (_a) {
                                switch (_a.label) {
                                    case 0:
                                        e.preventDefault();
                                        return [4 /*yield*/, handleCloseTaskSubmit(taskId, overlay)];
                                    case 1:
                                        _a.sent();
                                        return [2 /*return*/];
                                }
                            });
                        }); });
                    }
                    return [2 /*return*/];
            }
        });
    });
}
function handleCloseTaskSubmit(taskId, overlay) {
    return __awaiter(this, void 0, void 0, function () {
        var form, formData, realEndDate, effortMade, termineeStateId, statesRes, statesData, states, termineeState, e_2, payload, response, result, error_5;
        var _a;
        return __generator(this, function (_b) {
            switch (_b.label) {
                case 0:
                    form = overlay.querySelector('#close-task-form');
                    formData = new FormData(form);
                    realEndDate = formData.get('realEndDate');
                    effortMade = parseFloat(formData.get('effortMade'));
                    if (!realEndDate) {
                        showToast('La date de fin réelle est obligatoire.', 'error');
                        return [2 /*return*/];
                    }
                    if (isNaN(effortMade) || effortMade <= 0) {
                        showToast('L\'effort réel doit être supérieur à 0.', 'error');
                        return [2 /*return*/];
                    }
                    termineeStateId = null;
                    _b.label = 1;
                case 1:
                    _b.trys.push([1, 4, , 5]);
                    return [4 /*yield*/, fetch('/api/states')];
                case 2:
                    statesRes = _b.sent();
                    return [4 /*yield*/, statesRes.json()];
                case 3:
                    statesData = _b.sent();
                    states = statesData.states || [];
                    termineeState = states.find(function (s) {
                        return ['terminé', 'terminée', 'terminées', 'done', 'completed', 'fini', 'finie', 'closed'].includes(s.name.toLowerCase().trim());
                    });
                    termineeStateId = (_a = termineeState === null || termineeState === void 0 ? void 0 : termineeState.id) !== null && _a !== void 0 ? _a : null;
                    return [3 /*break*/, 5];
                case 4:
                    e_2 = _b.sent();
                    console.error('Erreur récupération états:', e_2);
                    return [3 /*break*/, 5];
                case 5:
                    payload = {
                        realEndDate: realEndDate,
                        effortMade: effortMade,
                    };
                    if (termineeStateId) {
                        payload.stateId = termineeStateId;
                    }
                    _b.label = 6;
                case 6:
                    _b.trys.push([6, 9, , 10]);
                    return [4 /*yield*/, fetch("/api/edit/task/".concat(taskId), {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_1__.getCsrfToken)() },
                            body: JSON.stringify(payload)
                        })];
                case 7:
                    response = _b.sent();
                    return [4 /*yield*/, response.json()];
                case 8:
                    result = _b.sent();
                    if (result.success) {
                        document.body.removeChild(overlay);
                        showToast('Tâche clôturée avec succès !', 'success');
                        setTimeout(function () { return location.reload(); }, 1200);
                    }
                    else {
                        showToast('Erreur : ' + (result.error || result.message), 'error');
                    }
                    return [3 /*break*/, 10];
                case 9:
                    error_5 = _b.sent();
                    console.error('Erreur:', error_5);
                    showToast('Erreur lors de la clôture de la tâche', 'error');
                    return [3 /*break*/, 10];
                case 10: return [2 /*return*/];
            }
        });
    });
}


/***/ },

/***/ "./assets/ts/pages/viewProject.ts"
/*!****************************************!*\
  !*** ./assets/ts/pages/viewProject.ts ***!
  \****************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _services_Api__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ../services/Api */ "./assets/ts/services/Api.ts");
/* harmony import */ var _tasks__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./tasks */ "./assets/ts/pages/tasks.ts");
/* harmony import */ var _projects__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./projects */ "./assets/ts/pages/projects.ts");
var __awaiter = (undefined && undefined.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (undefined && undefined.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g = Object.create((typeof Iterator === "function" ? Iterator : Object).prototype);
    return g.next = verb(0), g["throw"] = verb(1), g["return"] = verb(2), typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};



document.addEventListener('DOMContentLoaded', function () {
    var _a, _b, _c, _d, _e, _f, _g, _h, _j, _k;
    // ── Read embedded project data ──────────────────────────────────────────
    var dataEl = document.getElementById('pd-project-data');
    if (!dataEl)
        return; // Not on the project details page
    var pdData;
    try {
        pdData = JSON.parse((_a = dataEl.textContent) !== null && _a !== void 0 ? _a : '{}');
    }
    catch (_l) {
        return;
    }
    var projectId = pdData.projectId;
    // ── Helpers ─────────────────────────────────────────────────────────────
    function openModal(id) {
        var el = document.getElementById(id);
        if (el) {
            el.style.display = 'flex';
        }
    }
    function closeModal(id) {
        var el = document.getElementById(id);
        if (el) {
            el.style.display = 'none';
        }
    }
    function showError(id, msg) {
        var el = document.getElementById(id);
        if (el) {
            el.textContent = msg;
            el.style.display = 'block';
        }
    }
    function hideError(id) {
        var el = document.getElementById(id);
        if (el) {
            el.style.display = 'none';
            el.textContent = '';
        }
    }
    function showSuccess(id, msg) {
        var el = document.getElementById(id);
        if (el) {
            el.textContent = msg;
            el.style.display = 'block';
        }
    }
    // Close on overlay click
    document.querySelectorAll('.modal-overlay').forEach(function (overlay) {
        overlay.addEventListener('click', function (e) {
            if (e.target === overlay)
                overlay.style.display = 'none';
        });
    });
    // Generic close buttons
    document.querySelectorAll('.pd-modal-close').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var target = btn.getAttribute('data-target');
            if (target)
                closeModal(target);
        });
    });
    // ── Task actions (Modifier / Clôturer) ──────────────────────────────────
    document.addEventListener('click', function (e) {
        var editBtn = e.target.closest('.pd-edit-task-btn');
        if (editBtn) {
            e.preventDefault();
            var taskId = editBtn.getAttribute('data-task-id');
            if (taskId)
                (0,_tasks__WEBPACK_IMPORTED_MODULE_1__.editTask)(taskId);
        }
        var closeBtn = e.target.closest('.pd-close-task-btn');
        if (closeBtn) {
            e.preventDefault();
            var taskId = closeBtn.getAttribute('data-task-id');
            if (taskId)
                (0,_tasks__WEBPACK_IMPORTED_MODULE_1__.openCloseTaskModal)(taskId);
        }
    });
    // ── Task filter pills ────────────────────────────────────────────────────
    var filterBar = document.getElementById('pd-filter-bar');
    var taskCards = document.querySelectorAll('.pd-task-card');
    filterBar === null || filterBar === void 0 ? void 0 : filterBar.querySelectorAll('.pd-filter-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var _a;
            filterBar.querySelectorAll('.pd-filter-btn').forEach(function (b) { return b.classList.remove('active'); });
            btn.classList.add('active');
            var filter = (_a = btn.getAttribute('data-filter')) !== null && _a !== void 0 ? _a : 'all';
            taskCards.forEach(function (card) {
                var _a;
                var cat = (_a = card.getAttribute('data-state-cat')) !== null && _a !== void 0 ? _a : 'in_progress';
                card.style.display = (filter === 'all' || cat === filter) ? 'flex' : 'none';
            });
        });
    });
    // ── Project options menu (⋮) ─────────────────────────────────────────────
    var optionsBtn = document.getElementById('pd-options-btn');
    var optionsMenu = document.getElementById('pd-options-menu');
    if (optionsBtn && optionsMenu) {
        optionsBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            var isOpen = optionsMenu.style.display !== 'none';
            optionsMenu.style.display = isOpen ? 'none' : 'block';
            optionsBtn.setAttribute('aria-expanded', String(!isOpen));
        });
        document.addEventListener('click', function () {
            optionsMenu.style.display = 'none';
            optionsBtn.setAttribute('aria-expanded', 'false');
        });
    }
    (_b = document.getElementById('pd-menu-edit')) === null || _b === void 0 ? void 0 : _b.addEventListener('click', function () {
        if (optionsMenu)
            optionsMenu.style.display = 'none';
        (0,_projects__WEBPACK_IMPORTED_MODULE_2__.editProject)(projectId);
    });
    (_c = document.getElementById('pd-menu-delete')) === null || _c === void 0 ? void 0 : _c.addEventListener('click', function () {
        if (optionsMenu)
            optionsMenu.style.display = 'none';
        openModal('pd-modal-delete-project');
    });
    // ── "Nouvelle tâche" modal ───────────────────────────────────────────────
    (_d = document.getElementById('btn-new-task')) === null || _d === void 0 ? void 0 : _d.addEventListener('click', function () {
        (0,_tasks__WEBPACK_IMPORTED_MODULE_1__.createNewTask)({ projectId: projectId, projectName: pdData.projectName });
    });
    (_e = document.getElementById('nt-submit')) === null || _e === void 0 ? void 0 : _e.addEventListener('click', function () { return __awaiter(void 0, void 0, void 0, function () {
        var name, desc, effort, prio, stateId, devId, begin, end, btn, body, res, data, _a;
        var _b, _c, _d, _e, _f, _g, _h, _j, _k;
        return __generator(this, function (_l) {
            switch (_l.label) {
                case 0:
                    hideError('nt-error');
                    name = (_b = document.getElementById('nt-name')) === null || _b === void 0 ? void 0 : _b.value.trim();
                    desc = (_c = document.getElementById('nt-description')) === null || _c === void 0 ? void 0 : _c.value.trim();
                    effort = (_d = document.getElementById('nt-effort')) === null || _d === void 0 ? void 0 : _d.value.trim();
                    prio = (_e = document.getElementById('nt-priority')) === null || _e === void 0 ? void 0 : _e.value;
                    stateId = (_f = document.getElementById('nt-state')) === null || _f === void 0 ? void 0 : _f.value;
                    devId = (_g = document.getElementById('nt-developer')) === null || _g === void 0 ? void 0 : _g.value;
                    begin = (_h = document.getElementById('nt-begin')) === null || _h === void 0 ? void 0 : _h.value;
                    end = (_j = document.getElementById('nt-end')) === null || _j === void 0 ? void 0 : _j.value;
                    if (!name) {
                        showError('nt-error', 'Le nom est obligatoire.');
                        return [2 /*return*/];
                    }
                    if (!effort || parseFloat(effort) <= 0) {
                        showError('nt-error', "L'effort requis doit être > 0.");
                        return [2 /*return*/];
                    }
                    if (!end) {
                        showError('nt-error', "L'échéance est obligatoire.");
                        return [2 /*return*/];
                    }
                    btn = document.getElementById('nt-submit');
                    btn.disabled = true;
                    _l.label = 1;
                case 1:
                    _l.trys.push([1, 4, 5, 6]);
                    body = {
                        name: name,
                        effortRequired: parseFloat(effort),
                        projectId: projectId,
                        theoricalEndDate: end,
                    };
                    if (desc)
                        body.description = desc;
                    if (prio)
                        body.priority = prio;
                    if (stateId)
                        body.stateId = stateId;
                    if (devId)
                        body.developerId = devId;
                    if (begin)
                        body.beginDate = begin;
                    return [4 /*yield*/, fetch('/api/add/task', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_0__.getCsrfToken)() },
                            body: JSON.stringify(body),
                        })];
                case 2:
                    res = _l.sent();
                    return [4 /*yield*/, res.json().catch(function () { return ({}); })];
                case 3:
                    data = _l.sent();
                    if (!res.ok || !data.success) {
                        showError('nt-error', (_k = data.error) !== null && _k !== void 0 ? _k : 'Erreur lors de la création.');
                    }
                    else {
                        closeModal('pd-modal-new-task');
                        window.location.reload();
                    }
                    return [3 /*break*/, 6];
                case 4:
                    _a = _l.sent();
                    showError('nt-error', 'Erreur réseau. Veuillez réessayer.');
                    return [3 /*break*/, 6];
                case 5:
                    btn.disabled = false;
                    return [7 /*endfinally*/];
                case 6: return [2 /*return*/];
            }
        });
    }); });
    // ── Absence check cache ──────────────────────────────────────────────────
    var absentUserIds = new Set();
    function loadActiveAbsences() {
        return __awaiter(this, void 0, void 0, function () {
            var res, data, _a;
            return __generator(this, function (_b) {
                switch (_b.label) {
                    case 0:
                        _b.trys.push([0, 3, , 4]);
                        return [4 /*yield*/, fetch('/api/absences/active')];
                    case 1:
                        res = _b.sent();
                        return [4 /*yield*/, res.json().catch(function () { return ({}); })];
                    case 2:
                        data = _b.sent();
                        if (data.success && Array.isArray(data.absences)) {
                            absentUserIds = new Set(data.absences.map(function (a) { return a.user_id; }));
                        }
                        return [3 /*break*/, 4];
                    case 3:
                        _a = _b.sent();
                        return [3 /*break*/, 4];
                    case 4: return [2 /*return*/];
                }
            });
        });
    }
    // ── "Ajouter un membre" modal ────────────────────────────────────────────
    (_f = document.getElementById('btn-add-member')) === null || _f === void 0 ? void 0 : _f.addEventListener('click', function () { return __awaiter(void 0, void 0, void 0, function () {
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    hideError('am-error');
                    hideError('am-success');
                    return [4 /*yield*/, loadActiveAbsences()];
                case 1:
                    _a.sent();
                    openModal('pd-modal-add-member');
                    return [2 /*return*/];
            }
        });
    }); });
    // Absence warning when selecting a user
    (_g = document.getElementById('am-user')) === null || _g === void 0 ? void 0 : _g.addEventListener('change', function () {
        var _a;
        var userId = (_a = document.getElementById('am-user')) === null || _a === void 0 ? void 0 : _a.value;
        if (userId && absentUserIds.has(userId)) {
            showError('am-error', '⚠️ Ce collaborateur est actuellement en absence. Vous pouvez quand même l\'assigner.');
        }
        else {
            hideError('am-error');
        }
    });
    // Also check in the new task developer select
    (_h = document.getElementById('nt-developer')) === null || _h === void 0 ? void 0 : _h.addEventListener('change', function () { return __awaiter(void 0, void 0, void 0, function () {
        var userId;
        var _a;
        return __generator(this, function (_b) {
            switch (_b.label) {
                case 0:
                    if (!(absentUserIds.size === 0)) return [3 /*break*/, 2];
                    return [4 /*yield*/, loadActiveAbsences()];
                case 1:
                    _b.sent();
                    _b.label = 2;
                case 2:
                    userId = (_a = document.getElementById('nt-developer')) === null || _a === void 0 ? void 0 : _a.value;
                    if (userId && absentUserIds.has(userId)) {
                        showError('nt-error', '⚠️ Ce collaborateur est actuellement en absence. Vous pouvez quand même l\'assigner.');
                    }
                    else {
                        hideError('nt-error');
                    }
                    return [2 /*return*/];
            }
        });
    }); });
    (_j = document.getElementById('am-submit')) === null || _j === void 0 ? void 0 : _j.addEventListener('click', function () { return __awaiter(void 0, void 0, void 0, function () {
        var taskId, userId, btn, res, data, _a;
        var _b, _c, _d;
        return __generator(this, function (_e) {
            switch (_e.label) {
                case 0:
                    hideError('am-error');
                    hideError('am-success');
                    taskId = (_b = document.getElementById('am-task')) === null || _b === void 0 ? void 0 : _b.value;
                    userId = (_c = document.getElementById('am-user')) === null || _c === void 0 ? void 0 : _c.value;
                    if (!taskId) {
                        showError('am-error', 'Veuillez sélectionner une tâche.');
                        return [2 /*return*/];
                    }
                    if (!userId) {
                        showError('am-error', 'Veuillez sélectionner un utilisateur.');
                        return [2 /*return*/];
                    }
                    btn = document.getElementById('am-submit');
                    btn.disabled = true;
                    _e.label = 1;
                case 1:
                    _e.trys.push([1, 4, 5, 6]);
                    return [4 /*yield*/, fetch("/api/edit/task/".concat(encodeURIComponent(taskId)), {
                            method: 'PUT',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_0__.getCsrfToken)() },
                            body: JSON.stringify({ developerId: userId }),
                        })];
                case 2:
                    res = _e.sent();
                    return [4 /*yield*/, res.json().catch(function () { return ({}); })];
                case 3:
                    data = _e.sent();
                    if (!res.ok || !data.success) {
                        showError('am-error', (_d = data.error) !== null && _d !== void 0 ? _d : 'Erreur lors de l\'assignation.');
                    }
                    else {
                        showSuccess('am-success', 'Membre assigné avec succès !');
                        setTimeout(function () {
                            closeModal('pd-modal-add-member');
                            window.location.reload();
                        }, 1200);
                    }
                    return [3 /*break*/, 6];
                case 4:
                    _a = _e.sent();
                    showError('am-error', 'Erreur réseau. Veuillez réessayer.');
                    return [3 /*break*/, 6];
                case 5:
                    btn.disabled = false;
                    return [7 /*endfinally*/];
                case 6: return [2 /*return*/];
            }
        });
    }); });
    // ── Delete project modal ─────────────────────────────────────────────────
    (_k = document.getElementById('dp-submit')) === null || _k === void 0 ? void 0 : _k.addEventListener('click', function () { return __awaiter(void 0, void 0, void 0, function () {
        var btn, res, data, _a;
        var _b;
        return __generator(this, function (_c) {
            switch (_c.label) {
                case 0:
                    hideError('dp-error');
                    btn = document.getElementById('dp-submit');
                    btn.disabled = true;
                    _c.label = 1;
                case 1:
                    _c.trys.push([1, 4, , 5]);
                    return [4 /*yield*/, fetch("/api/delete/project/".concat(encodeURIComponent(projectId)), {
                            method: 'DELETE',
                            headers: { 'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_0__.getCsrfToken)() },
                        })];
                case 2:
                    res = _c.sent();
                    return [4 /*yield*/, res.json().catch(function () { return ({}); })];
                case 3:
                    data = _c.sent();
                    if (!res.ok || !data.success) {
                        showError('dp-error', (_b = data.error) !== null && _b !== void 0 ? _b : 'Erreur lors de la suppression.');
                        btn.disabled = false;
                    }
                    else {
                        window.location.href = '/projects';
                    }
                    return [3 /*break*/, 5];
                case 4:
                    _a = _c.sent();
                    showError('dp-error', 'Erreur réseau. Veuillez réessayer.');
                    btn.disabled = false;
                    return [3 /*break*/, 5];
                case 5: return [2 /*return*/];
            }
        });
    }); });
});


/***/ },

/***/ "./assets/ts/services/Api.ts"
/*!***********************************!*\
  !*** ./assets/ts/services/Api.ts ***!
  \***********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   getCsrfToken: () => (/* binding */ getCsrfToken)
/* harmony export */ });
/**
 * Lit le token CSRF depuis la meta tag injectée par le serveur.
 * À utiliser dans tous les appels fetch mutants (POST, PUT, PATCH, DELETE).
 */
function getCsrfToken() {
    var _a, _b;
    return (_b = (_a = document.querySelector('meta[name="csrf-token"]')) === null || _a === void 0 ? void 0 : _a.content) !== null && _b !== void 0 ? _b : '';
}


/***/ },

/***/ "./assets/ts/services/UserService.ts"
/*!*******************************************!*\
  !*** ./assets/ts/services/UserService.ts ***!
  \*******************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   "default": () => (__WEBPACK_DEFAULT_EXPORT__)
/* harmony export */ });
/* harmony import */ var _Api__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./Api */ "./assets/ts/services/Api.ts");
var __awaiter = (undefined && undefined.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (undefined && undefined.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g = Object.create((typeof Iterator === "function" ? Iterator : Object).prototype);
    return g.next = verb(0), g["throw"] = verb(1), g["return"] = verb(2), typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};

var UserService = /** @class */ (function () {
    function UserService() {
    }
    UserService.loadUsersFromApi = function () {
        return __awaiter(this, void 0, void 0, function () {
            var response, error_1;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        _a.trys.push([0, 3, , 4]);
                        return [4 /*yield*/, fetch("api/users")];
                    case 1:
                        response = _a.sent();
                        if (!response.ok) {
                            throw new Error("Erreur lors de la r\u00E9cup\u00E9ration des utilisateurs");
                        }
                        return [4 /*yield*/, response.json()];
                    case 2: return [2 /*return*/, _a.sent()];
                    case 3:
                        error_1 = _a.sent();
                        console.error("Erreur attrap\u00E9e : ".concat(error_1));
                        return [3 /*break*/, 4];
                    case 4: return [2 /*return*/];
                }
            });
        });
    };
    UserService.loadUserFromApi = function (userId) {
        return fetch("/api/user/".concat(userId), { method: "GET" })
            .then(function (response) {
            if (response.status === 200)
                return response.json();
        })
            .then(function (data) {
            if (data) {
                UserService.showModal({
                    name: data.user.name,
                    email: data.user.email,
                    roles: data.user.roles,
                });
                return data;
            }
            else {
                UserService.showModal("User non récupéré");
            }
        })
            .catch(function (error) {
            console.error("Erreur catch :", error);
        });
    };
    UserService.addUserFromApi = function (user) {
        if (!user.firstname || !user.lastname || !user.email || !user.password || !user.roleId) {
            return Promise.reject(new Error("Tous les champs obligatoires doivent être remplis"));
        }
        return fetch("api/add/user", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": (0,_Api__WEBPACK_IMPORTED_MODULE_0__.getCsrfToken)(),
            },
            body: JSON.stringify({
                firstname: user.firstname,
                lastname: user.lastname,
                email: user.email,
                password: user.password,
                roleId: user.roleId,
            }),
        })
            .then(function (response) {
            if (!response.ok)
                throw new Error("Erreur HTTP ".concat(response.status));
            return response.json();
        })
            .then(function (data) {
            if (data.success) {
                UserService.showModal("Utilisateur ajouté avec succès !");
                return Promise.resolve();
            }
            else {
                var error = new Error(data.message || "Erreur lors de la création de l'utilisateur");
                UserService.showModal("Erreur : ".concat(data.message));
                return Promise.reject(error);
            }
        })
            .catch(function (e) {
            console.error(e);
            UserService.showModal("Erreur : ".concat(e.message || "Une erreur est survenue"));
            return Promise.reject(e);
        });
    };
    UserService.deleteUserFromApi = function (userId) {
        return fetch("/api/delete/user/".concat(userId), {
            method: "DELETE",
            headers: { "X-CSRF-Token": (0,_Api__WEBPACK_IMPORTED_MODULE_0__.getCsrfToken)() },
        })
            .then(function (response) { return response.json(); })
            .then(function (data) { return data; });
    };
    UserService.editUserFromApi = function (user) {
        if (!user)
            return Promise.reject(new Error("Utilisateur inconnu"));
        var id = user.id, firstname = user.firstname, lastname = user.lastname, email = user.email, roleId = user.roleId, jobtitle = user.jobtitle, fieldofwork = user.fieldofwork, degree = user.degree;
        var body = { firstname: firstname, lastname: lastname, email: email };
        if (roleId)
            body.roleId = roleId;
        if (jobtitle !== undefined)
            body.jobtitle = jobtitle;
        if (fieldofwork !== undefined)
            body.fieldofwork = fieldofwork;
        if (degree !== undefined)
            body.degree = degree;
        return fetch("api/edit/user/".concat(id), {
            method: "PATCH",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": (0,_Api__WEBPACK_IMPORTED_MODULE_0__.getCsrfToken)(),
            },
            body: JSON.stringify(body),
        })
            .then(function (response) {
            if (response.status === 200)
                return response.json();
            throw new Error("Erreur HTTP ".concat(response.status));
        })
            .then(function (data) {
            if (data.success) {
                UserService.showModal("Utilisateur modifié avec succès !");
                return data;
            }
            else {
                UserService.showModal("Erreur : ".concat(data.message));
            }
        })
            .catch(function (error) {
            console.error("Erreur attrap\u00E9e ".concat(error));
        });
    };
    UserService.showToast = function (message, type) {
        if (type === void 0) { type = 'success'; }
        var toast = document.createElement('div');
        toast.className = "task-toast task-toast--".concat(type);
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
        toast.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML = "\n            <i class=\"fas fa-".concat(type === 'success' ? 'check-circle' : 'exclamation-circle', "\" aria-hidden=\"true\"></i>\n            <span>").concat(message, "</span>\n        ");
        document.body.appendChild(toast);
        requestAnimationFrame(function () { return toast.classList.add('task-toast--visible'); });
        setTimeout(function () {
            toast.classList.remove('task-toast--visible');
            toast.addEventListener('transitionend', function () { return toast.remove(); }, { once: true });
            if (type === 'success')
                location.reload();
        }, 2500);
    };
    /** @deprecated use showToast */
    UserService.showModal = function (content) {
        if (typeof content === 'string') {
            var isError = content.toLowerCase().startsWith('erreur');
            UserService.showToast(content, isError ? 'error' : 'success');
        }
    };
    return UserService;
}());
/* harmony default export */ const __WEBPACK_DEFAULT_EXPORT__ = (UserService);


/***/ },

/***/ "./assets/ts/settings.ts"
/*!*******************************!*\
  !*** ./assets/ts/settings.ts ***!
  \*******************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _services_UserService__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./services/UserService */ "./assets/ts/services/UserService.ts");
/* harmony import */ var _services_Api__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./services/Api */ "./assets/ts/services/Api.ts");
var __awaiter = (undefined && undefined.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (undefined && undefined.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g = Object.create((typeof Iterator === "function" ? Iterator : Object).prototype);
    return g.next = verb(0), g["throw"] = verb(1), g["return"] = verb(2), typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};


document.addEventListener('DOMContentLoaded', function () {
    var settingsForm = document.getElementById("settings-form");
    // ── Degree management ──────────────────────────────────────────────
    var degreeListEl = document.getElementById('degree-list');
    var degreeInputEl = document.getElementById('degree-input');
    var degreeAddBtn = document.getElementById('degree-add-btn');
    // Init from server-rendered DOM
    var degrees = degreeListEl
        ? Array.from(degreeListEl.querySelectorAll('.degree-item'))
            .map(function (li) { var _a; return (_a = li.dataset.value) !== null && _a !== void 0 ? _a : ''; })
            .filter(Boolean)
        : [];
    function renderDegrees() {
        if (!degreeListEl)
            return;
        degreeListEl.innerHTML = '';
        degrees.forEach(function (d, index) {
            var li = document.createElement('li');
            li.className = 'degree-item';
            li.dataset.value = d;
            li.innerHTML = "\n                <span class=\"degree-text\">".concat(d, "</span>\n                <div class=\"degree-actions\">\n                    <button type=\"button\" class=\"degree-btn-edit\" data-index=\"").concat(index, "\" title=\"Modifier\">\n                        <i class=\"fas fa-pen\"></i>\n                    </button>\n                    <button type=\"button\" class=\"degree-btn-delete\" data-index=\"").concat(index, "\" title=\"Supprimer\">\n                        <i class=\"fas fa-times\"></i>\n                    </button>\n                </div>");
            degreeListEl.appendChild(li);
        });
        degreeListEl.querySelectorAll('.degree-btn-edit').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var _a;
                var idx = parseInt((_a = btn.dataset.index) !== null && _a !== void 0 ? _a : '0', 10);
                if (degreeInputEl)
                    degreeInputEl.value = degrees[idx];
                degrees.splice(idx, 1);
                renderDegrees();
                degreeInputEl === null || degreeInputEl === void 0 ? void 0 : degreeInputEl.focus();
            });
        });
        degreeListEl.querySelectorAll('.degree-btn-delete').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var _a;
                var idx = parseInt((_a = btn.dataset.index) !== null && _a !== void 0 ? _a : '0', 10);
                degrees.splice(idx, 1);
                renderDegrees();
            });
        });
    }
    function addDegree() {
        if (!degreeInputEl)
            return;
        var val = degreeInputEl.value.trim();
        if (val && !degrees.includes(val)) {
            degrees.push(val);
            degreeInputEl.value = '';
            renderDegrees();
        }
    }
    degreeAddBtn === null || degreeAddBtn === void 0 ? void 0 : degreeAddBtn.addEventListener('click', addDegree);
    degreeInputEl === null || degreeInputEl === void 0 ? void 0 : degreeInputEl.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            addDegree();
        }
    });
    // ── Profile form submit ─────────────────────────────────────────────
    if (settingsForm) {
        settingsForm.addEventListener("submit", function (event) { return __awaiter(void 0, void 0, void 0, function () {
            var userId, firstnameInput, lastnameInput, emailInput, jobtitleInput, fieldofworkInput, saveButton, originalButtonContent, error_1;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        event.preventDefault();
                        userId = settingsForm.dataset.userId;
                        firstnameInput = document.getElementById("firstname");
                        lastnameInput = document.getElementById("lastname");
                        emailInput = document.getElementById("email");
                        jobtitleInput = document.getElementById("jobtitle");
                        fieldofworkInput = document.getElementById("fieldofwork");
                        saveButton = settingsForm.querySelector(".btn-save");
                        if (!(userId && firstnameInput && lastnameInput && emailInput)) return [3 /*break*/, 6];
                        originalButtonContent = saveButton.innerHTML;
                        saveButton.disabled = true;
                        saveButton.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Enregistrement...';
                        _a.label = 1;
                    case 1:
                        _a.trys.push([1, 3, 4, 5]);
                        return [4 /*yield*/, _services_UserService__WEBPACK_IMPORTED_MODULE_0__["default"].editUserFromApi({
                                id: userId,
                                firstname: firstnameInput.value,
                                lastname: lastnameInput.value,
                                email: emailInput.value,
                                jobtitle: (jobtitleInput === null || jobtitleInput === void 0 ? void 0 : jobtitleInput.value) || '',
                                fieldofwork: (fieldofworkInput === null || fieldofworkInput === void 0 ? void 0 : fieldofworkInput.value) || '',
                                degree: degrees,
                            })];
                    case 2:
                        _a.sent();
                        return [3 /*break*/, 5];
                    case 3:
                        error_1 = _a.sent();
                        console.error("Mise à jour du profil échouée :", error_1);
                        return [3 /*break*/, 5];
                    case 4:
                        saveButton.disabled = false;
                        saveButton.innerHTML = originalButtonContent;
                        return [7 /*endfinally*/];
                    case 5: return [3 /*break*/, 7];
                    case 6:
                        console.error("Champs ou ID utilisateur manquants");
                        _a.label = 7;
                    case 7: return [2 /*return*/];
                }
            });
        }); });
    }
    // Password change handling
    var passwordChangeForm = document.getElementById("password-change-form");
    if (passwordChangeForm) {
        passwordChangeForm.addEventListener("submit", function (event) { return __awaiter(void 0, void 0, void 0, function () {
            var currentPwd, newPwd, confirmPwd, errorEl, successEl, saveBtn, originalContent, response, data, _a;
            return __generator(this, function (_b) {
                switch (_b.label) {
                    case 0:
                        event.preventDefault();
                        currentPwd = document.getElementById("current-password").value;
                        newPwd = document.getElementById("new-password").value;
                        confirmPwd = document.getElementById("confirm-password").value;
                        errorEl = document.getElementById("password-change-error");
                        successEl = document.getElementById("password-change-success");
                        saveBtn = passwordChangeForm.querySelector(".btn-save-password");
                        errorEl.style.display = 'none';
                        successEl.style.display = 'none';
                        if (newPwd !== confirmPwd) {
                            errorEl.textContent = 'Les nouveaux mots de passe ne correspondent pas.';
                            errorEl.style.display = 'block';
                            return [2 /*return*/];
                        }
                        if (newPwd.length < 8) {
                            errorEl.textContent = 'Le nouveau mot de passe doit contenir au moins 8 caractères.';
                            errorEl.style.display = 'block';
                            return [2 /*return*/];
                        }
                        originalContent = saveBtn.innerHTML;
                        saveBtn.disabled = true;
                        saveBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Mise à jour...';
                        _b.label = 1;
                    case 1:
                        _b.trys.push([1, 4, 5, 6]);
                        return [4 /*yield*/, fetch('/api/change-password', {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_1__.getCsrfToken)() },
                                body: JSON.stringify({ currentPassword: currentPwd, newPassword: newPwd }),
                            })];
                    case 2:
                        response = _b.sent();
                        return [4 /*yield*/, response.json()];
                    case 3:
                        data = _b.sent();
                        if (response.ok && data.success) {
                            successEl.textContent = 'Mot de passe modifié avec succès !';
                            successEl.style.display = 'block';
                            passwordChangeForm.reset();
                        }
                        else {
                            errorEl.textContent = data.error || data.message || 'Une erreur est survenue.';
                            errorEl.style.display = 'block';
                        }
                        return [3 /*break*/, 6];
                    case 4:
                        _a = _b.sent();
                        errorEl.textContent = 'Impossible de joindre le serveur.';
                        errorEl.style.display = 'block';
                        return [3 /*break*/, 6];
                    case 5:
                        saveBtn.disabled = false;
                        saveBtn.innerHTML = originalContent;
                        return [7 /*endfinally*/];
                    case 6: return [2 /*return*/];
                }
            });
        }); });
    }
    // Delete account handling
    var deleteAccountBtn = document.getElementById("btn-delete-account");
    if (deleteAccountBtn) {
        deleteAccountBtn.addEventListener("click", function () { return __awaiter(void 0, void 0, void 0, function () {
            var confirmed, originalContent, response, data, error_2;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        confirmed = confirm("Êtes-vous sûr de vouloir supprimer votre compte ? Cette action est irréversible et supprimera toutes vos données.");
                        if (!confirmed) return [3 /*break*/, 5];
                        originalContent = deleteAccountBtn.innerHTML;
                        deleteAccountBtn.disabled = true;
                        deleteAccountBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i> Suppression...';
                        _a.label = 1;
                    case 1:
                        _a.trys.push([1, 4, , 5]);
                        return [4 /*yield*/, fetch('/api/delete/my-account', {
                                method: 'DELETE',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-Token': (0,_services_Api__WEBPACK_IMPORTED_MODULE_1__.getCsrfToken)(),
                                }
                            })];
                    case 2:
                        response = _a.sent();
                        return [4 /*yield*/, response.json()];
                    case 3:
                        data = _a.sent();
                        if (response.ok && data.success) {
                            // Redirection vers logout qui gérera le nettoyage final et la redirection vers login
                            window.location.href = '/logout';
                        }
                        else {
                            console.error("Erreur de suppression du compte", data);
                            alert(data.message || data.error || "Une erreur est survenue lors de la suppression de votre compte.");
                            deleteAccountBtn.disabled = false;
                            deleteAccountBtn.innerHTML = originalContent;
                        }
                        return [3 /*break*/, 5];
                    case 4:
                        error_2 = _a.sent();
                        console.error("Erreur:", error_2);
                        alert("Impossible de joindre le serveur pour supprimer le compte.");
                        deleteAccountBtn.disabled = false;
                        deleteAccountBtn.innerHTML = originalContent;
                        return [3 /*break*/, 5];
                    case 5: return [2 /*return*/];
                }
            });
        }); });
    }
    // Tab handling
    var tabs = document.querySelectorAll('.settings-tabs .nav-link');
    var contents = document.querySelectorAll('.settings-content');
    if (tabs.length > 0 && contents.length > 0) {
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function (e) {
                e.preventDefault();
                // Remove active class from all tabs
                tabs.forEach(function (t) { return t.classList.remove('active'); });
                // Add active class to clicked tab
                tab.classList.add('active');
                // Hide all contents
                contents.forEach(function (c) { return c.style.display = 'none'; });
                // Show target content
                var targetId = tab.getAttribute('data-target');
                if (targetId) {
                    var targetContent = document.getElementById(targetId);
                    if (targetContent) {
                        targetContent.style.display = 'block';
                    }
                }
            });
        });
    }
    // Modal handling
    var closeModal = document.getElementById('close-modal');
    var closeModalBtn = document.getElementById('close-modal-btn');
    var modal = document.getElementById('notificationModal');
    if (closeModal) {
        closeModal.addEventListener('click', function () {
            if (modal)
                modal.style.display = 'none';
        });
    }
    if (closeModalBtn) {
        closeModalBtn.addEventListener('click', function () {
            if (modal)
                modal.style.display = 'none';
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


/***/ },

/***/ "./assets/ts/utils/helpers.ts"
/*!************************************!*\
  !*** ./assets/ts/utils/helpers.ts ***!
  \************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony export */ __webpack_require__.d(__webpack_exports__, {
/* harmony export */   escapeHtml: () => (/* binding */ escapeHtml),
/* harmony export */   formatDate: () => (/* binding */ formatDate),
/* harmony export */   formatDateForInput: () => (/* binding */ formatDateForInput),
/* harmony export */   formatDateTimeForInput: () => (/* binding */ formatDateTimeForInput)
/* harmony export */ });
function escapeHtml(text) {
    if (!text)
        return '';
    var div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}
function formatDate(dateString) {
    try {
        var date = new Date(dateString);
        return date.toLocaleDateString('fr-FR') + ' ' + date.toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' });
    }
    catch (error) {
        return dateString;
    }
}
function formatDateForInput(dateString) {
    if (!dateString)
        return '';
    try {
        var date = new Date(dateString);
        return date.toISOString().split('T')[0];
    }
    catch (_a) {
        return '';
    }
}
function formatDateTimeForInput(dateString) {
    if (!dateString)
        return '';
    try {
        var date = new Date(dateString);
        var year = date.getFullYear();
        var month = String(date.getMonth() + 1).padStart(2, '0');
        var day = String(date.getDate()).padStart(2, '0');
        var hours = String(date.getHours()).padStart(2, '0');
        var minutes = String(date.getMinutes()).padStart(2, '0');
        return "".concat(year, "-").concat(month, "-").concat(day, "T").concat(hours, ":").concat(minutes);
    }
    catch (_a) {
        return '';
    }
}


/***/ },

/***/ "./assets/ts/viewUsers.ts"
/*!********************************!*\
  !*** ./assets/ts/viewUsers.ts ***!
  \********************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
/* harmony import */ var _services_UserService__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! ./services/UserService */ "./assets/ts/services/UserService.ts");
var __awaiter = (undefined && undefined.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (undefined && undefined.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g = Object.create((typeof Iterator === "function" ? Iterator : Object).prototype);
    return g.next = verb(0), g["throw"] = verb(1), g["return"] = verb(2), typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};

var pageRoot = document.getElementById("dynamical-user");
var isUsersPage = !!pageRoot;
// ── Éléments du panneau droit ────────────────────────────────────────────────
var formAdd = document.getElementById("form-add-user");
var panelIcon = document.getElementById("users-panel-icon");
var panelTitleText = document.getElementById("users-panel-title-text");
var submitBtn = document.getElementById("users-form-submit");
var cancelBtn = document.getElementById("users-form-cancel");
var passwordGroup = document.getElementById("password-group");
var passwordLabel = document.getElementById("password-label");
var inputFirstname = document.getElementById("firstname");
var inputLastname = document.getElementById("lastname");
var inputEmail = document.getElementById("email");
var inputPassword = document.getElementById("password");
var inputRoleSelect = document.getElementById("role-select");
// ── Chargement des rôles dans le select ──────────────────────────────────────
function loadRoles() {
    return __awaiter(this, void 0, void 0, function () {
        var res, data, roles, _a;
        var _b;
        return __generator(this, function (_c) {
            switch (_c.label) {
                case 0:
                    if (!inputRoleSelect)
                        return [2 /*return*/];
                    _c.label = 1;
                case 1:
                    _c.trys.push([1, 4, , 5]);
                    return [4 /*yield*/, fetch('/api/roles')];
                case 2:
                    res = _c.sent();
                    return [4 /*yield*/, res.json()];
                case 3:
                    data = _c.sent();
                    roles = (_b = data === null || data === void 0 ? void 0 : data.data) !== null && _b !== void 0 ? _b : [];
                    inputRoleSelect.innerHTML = '<option value="">Sélectionner un rôle *</option>';
                    roles.forEach(function (role) {
                        var opt = document.createElement('option');
                        opt.value = role.id;
                        opt.textContent = role.name;
                        inputRoleSelect.appendChild(opt);
                    });
                    return [3 /*break*/, 5];
                case 4:
                    _a = _c.sent();
                    inputRoleSelect.innerHTML = '<option value="">Erreur de chargement</option>';
                    return [3 /*break*/, 5];
                case 5: return [2 /*return*/];
            }
        });
    });
}
if (isUsersPage)
    loadRoles();
// ── Éléments du modal "Voir" ─────────────────────────────────────────────────
var detailOverlay = document.getElementById("userDetailOverlay");
var detailAvatar = document.getElementById("detailAvatar");
var detailName = document.getElementById("detailName");
var detailEmailText = document.getElementById("detailEmailText");
var detailRoleBadge = document.getElementById("detailRoleBadge");
var detailJobtitleEl = document.getElementById("detailJobtitle");
var detailJobtitleText = document.getElementById("detailJobtitleText");
var detailFieldofworkEl = document.getElementById("detailFieldofwork");
var detailFieldofworkText = document.getElementById("detailFieldofworkText");
var detailDegreeEl = document.getElementById("detailDegree");
var detailDegreeList = document.getElementById("detailDegreeList");
// ── Helpers ──────────────────────────────────────────────────────────────────
function roleBadgeClass(role) {
    var r = role.toLowerCase();
    if (r.includes('admin'))
        return 'role-admin';
    if (r.includes('pdg'))
        return 'role-pdg';
    if (r.includes('cdp') || r.includes('chef') || r.includes('project'))
        return 'role-cdp';
    if (r.includes('dev'))
        return 'role-dev';
    if (r.includes('design'))
        return 'role-designer';
    return 'role-default';
}
// ── Mode du panneau droit ────────────────────────────────────────────────────
function setAddMode() {
    formAdd.dataset.mode = 'add';
    formAdd.dataset.editUserId = '';
    formAdd.reset();
    if (inputRoleSelect)
        inputRoleSelect.value = '';
    panelIcon.className = 'fas fa-user-plus';
    panelTitleText.textContent = 'Ajouter un utilisateur';
    submitBtn.innerHTML = '<i class="fas fa-plus" aria-hidden="true"></i> Ajouter l\'utilisateur';
    cancelBtn.style.display = 'none';
    inputPassword.required = true;
    passwordLabel.textContent = 'Mot de passe *';
    inputPassword.placeholder = '••••••••';
}
function setEditMode(userId, firstname, lastname, email, roleId) {
    var _a;
    formAdd.dataset.mode = 'edit';
    formAdd.dataset.editUserId = userId;
    panelIcon.className = 'fas fa-user-edit';
    panelTitleText.textContent = 'Modifier l\'utilisateur';
    inputFirstname.value = firstname;
    inputLastname.value = lastname;
    inputEmail.value = email;
    inputPassword.value = '';
    if (inputRoleSelect && roleId)
        inputRoleSelect.value = roleId;
    inputPassword.required = false;
    passwordLabel.textContent = 'Nouveau mot de passe (optionnel)';
    inputPassword.placeholder = 'Laisser vide pour ne pas changer';
    submitBtn.innerHTML = '<i class="fas fa-save" aria-hidden="true"></i> Enregistrer les modifications';
    cancelBtn.style.display = 'block';
    (_a = document.getElementById('users-form-panel')) === null || _a === void 0 ? void 0 : _a.scrollIntoView({ behavior: 'smooth', block: 'start' });
    inputFirstname.focus();
}
// ── Bouton Annuler ───────────────────────────────────────────────────────────
if (isUsersPage && cancelBtn) {
    cancelBtn.addEventListener('click', function () { return setAddMode(); });
}
// ── Soumission du formulaire (ajout OU modification) ─────────────────────────
var isSubmitting = false;
var FORM_LISTENER_ATTACHED = Symbol('formListenerAttached');
function manageAdd(form) {
    if (form[FORM_LISTENER_ATTACHED])
        return;
    form[FORM_LISTENER_ATTACHED] = true;
    var originalContent = submitBtn ? submitBtn.innerHTML : '';
    form.addEventListener("submit", function (event) {
        event.preventDefault();
        if (isSubmitting)
            return;
        isSubmitting = true;
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Envoi en cours...';
        }
        var mode = form.dataset.mode;
        var userId = form.dataset.editUserId;
        var firstname = inputFirstname.value;
        var lastname = inputLastname.value;
        var email = inputEmail.value;
        var password = inputPassword.value;
        var roleId = (inputRoleSelect === null || inputRoleSelect === void 0 ? void 0 : inputRoleSelect.value) || undefined;
        var finish = function () {
            isSubmitting = false;
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = mode === 'edit'
                    ? '<i class="fas fa-save" aria-hidden="true"></i> Enregistrer les modifications'
                    : originalContent;
            }
        };
        if (mode === 'edit' && userId) {
            _services_UserService__WEBPACK_IMPORTED_MODULE_0__["default"].editUserFromApi({ id: userId, firstname: firstname, lastname: lastname, email: email, roleId: roleId })
                .then(function () { return setAddMode(); })
                .catch(function (e) { return console.error("Erreur modification:", e); })
                .finally(finish);
        }
        else {
            _services_UserService__WEBPACK_IMPORTED_MODULE_0__["default"].addUserFromApi({ firstname: firstname, lastname: lastname, email: email, password: password, roleId: roleId })
                .then(function () { return form.reset(); })
                .catch(function (e) { return console.error("Erreur ajout:", e); })
                .finally(finish);
        }
    });
}
// ── Construction des cartes ──────────────────────────────────────────────────
function buildCard(user) {
    var _a, _b;
    var card = document.createElement("div");
    card.className = 'user-card';
    card.setAttribute("data-userid", String(user.id));
    card.setAttribute("data-userfirstname", user.firstname);
    card.setAttribute("data-userlastname", user.lastname);
    card.setAttribute("data-email", user.email);
    card.setAttribute("data-roleid", (_a = user.roleId) !== null && _a !== void 0 ? _a : '');
    card.setAttribute("data-name", "".concat(user.firstname, " ").concat(user.lastname).toLowerCase());
    var initials = (user.firstname.charAt(0) + user.lastname.charAt(0)).toUpperCase();
    var fullName = "".concat(user.firstname, " ").concat(user.lastname);
    var roleName = (_b = user.role_name) !== null && _b !== void 0 ? _b : '';
    var roleClass = roleName ? roleBadgeClass(roleName) : '';
    card.innerHTML = "\n        <div class=\"user-card-avatar\" aria-hidden=\"true\">".concat(initials, "</div>\n        <div class=\"user-card-name\" title=\"").concat(fullName, "\">").concat(fullName, "</div>\n        ").concat(roleName ? "<span class=\"member-role-badge ".concat(roleClass, "\">").concat(roleName, "</span>") : '', "\n        <div class=\"user-card-email\" title=\"").concat(user.email, "\">\n            <i class=\"fas fa-envelope\" aria-hidden=\"true\"></i>").concat(user.email, "\n        </div>\n        <div class=\"user-card-actions\">\n            <button class=\"user-icon-btn btn-view show-btn\"\n                    aria-label=\"Voir ").concat(fullName, "\">\n                <i class=\"fas fa-eye\" aria-hidden=\"true\"></i>\n            </button>\n            <button class=\"user-icon-btn btn-edit edit-btn\"\n                    aria-label=\"Modifier ").concat(fullName, "\">\n                <i class=\"fas fa-edit\" aria-hidden=\"true\"></i>\n            </button>\n            <button class=\"user-icon-btn btn-delete delete-btn\"\n                    aria-label=\"Supprimer ").concat(fullName, "\">\n                <i class=\"fas fa-trash\" aria-hidden=\"true\"></i>\n            </button>\n        </div>\n    ");
    return card;
}
// ── Listeners ────────────────────────────────────────────────────────────────
function manageDelete(btns) {
    btns.forEach(function (btn) {
        btn.addEventListener("click", function (event) {
            var card = event.target.closest('.user-card');
            if (!card)
                return;
            var userId = card.getAttribute("data-userid");
            if (!userId)
                return;
            card.style.opacity = "0.4";
            card.style.pointerEvents = "none";
            _services_UserService__WEBPACK_IMPORTED_MODULE_0__["default"].deleteUserFromApi(userId)
                .then(function (data) {
                if ("delete" in data && data.delete == "true") {
                    card.remove();
                }
                else {
                    card.style.opacity = "";
                    card.style.pointerEvents = "";
                }
            })
                .catch(function (error) {
                console.error("Erreur suppression:", error);
                card.style.opacity = "";
                card.style.pointerEvents = "";
            });
        });
    });
}
function manageEdit(btns) {
    btns.forEach(function (btn) {
        btn.addEventListener("click", function (event) {
            var card = event.target.closest('.user-card');
            if (!card)
                return;
            var userId = card.getAttribute("data-userid") || '';
            var firstname = card.getAttribute("data-userfirstname") || '';
            var lastname = card.getAttribute("data-userlastname") || '';
            var email = card.getAttribute("data-email") || '';
            var roleId = card.getAttribute("data-roleid") || undefined;
            setEditMode(userId, firstname, lastname, email, roleId);
        });
    });
}
function manageShow(btns) {
    btns.forEach(function (btn) {
        btn.addEventListener('click', function (event) {
            var card = event.target.closest('.user-card');
            if (!card)
                return;
            var firstname = card.getAttribute("data-userfirstname") || '';
            var lastname = card.getAttribute("data-userlastname") || '';
            var email = card.getAttribute("data-email") || '';
            var initials = (firstname.charAt(0) + lastname.charAt(0)).toUpperCase();
            detailAvatar.textContent = initials;
            detailName.textContent = "".concat(firstname, " ").concat(lastname);
            detailEmailText.textContent = email;
            detailRoleBadge.innerHTML = '';
            if (detailJobtitleEl)
                detailJobtitleEl.style.display = 'none';
            if (detailFieldofworkEl)
                detailFieldofworkEl.style.display = 'none';
            if (detailDegreeEl)
                detailDegreeEl.style.display = 'none';
            // Charger les infos complètes depuis l'API
            var userId = card.getAttribute("data-userid");
            if (userId) {
                fetch("/api/user/".concat(userId), { method: "GET" })
                    .then(function (r) { return r.ok ? r.json() : null; })
                    .then(function (data) {
                    var _a, _b, _c, _d, _e;
                    if (!(data === null || data === void 0 ? void 0 : data.user))
                        return;
                    var u = data.user;
                    // Rôle
                    var roleName = (_a = u.roleName) !== null && _a !== void 0 ? _a : ((_d = (_c = (_b = u.roles) === null || _b === void 0 ? void 0 : _b[0]) === null || _c === void 0 ? void 0 : _c.name) !== null && _d !== void 0 ? _d : '');
                    if (roleName) {
                        detailRoleBadge.innerHTML =
                            "<span class=\"member-role-badge ".concat(roleBadgeClass(roleName), "\">").concat(roleName, "</span>");
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
                    if (((_e = u.degree) === null || _e === void 0 ? void 0 : _e.length) && detailDegreeEl) {
                        detailDegreeList.innerHTML = u.degree
                            .map(function (d) { return "<span class=\"badge bg-light text-dark border\">".concat(d, "</span>"); })
                            .join('');
                        detailDegreeEl.style.display = 'block';
                    }
                })
                    .catch(function () { });
            }
            detailOverlay.style.display = 'flex';
        });
    });
}
// ── Chargement initial de la liste ───────────────────────────────────────────
var usersList = document.querySelector(".users-list");
if (isUsersPage && usersList) {
    _services_UserService__WEBPACK_IMPORTED_MODULE_0__["default"].loadUsersFromApi()
        .then(function (data) {
        var _a;
        var users = (_a = data === null || data === void 0 ? void 0 : data.data) !== null && _a !== void 0 ? _a : [];
        if (users.length > 0) {
            usersList.innerHTML = '';
            users.forEach(function (user) { return usersList.appendChild(buildCard(user)); });
            manageDelete(usersList.querySelectorAll(".delete-btn"));
            manageEdit(usersList.querySelectorAll(".edit-btn"));
            manageShow(usersList.querySelectorAll(".show-btn"));
        }
        else {
            usersList.innerHTML = "\n                    <div class=\"users-empty\">\n                        <i class=\"fas fa-users-slash\" aria-hidden=\"true\"></i>\n                        <p>Aucun utilisateur trouv\u00E9. Ajoutez-en un pour commencer !</p>\n                    </div>\n                ";
        }
    })
        .catch(function (e) { return console.error("Erreur chargement utilisateurs:", e); });
}
if (isUsersPage && formAdd) {
    manageAdd(formAdd);
}


/***/ },

/***/ "./node_modules/bootstrap/scss/bootstrap.scss"
/*!****************************************************!*\
  !*** ./node_modules/bootstrap/scss/bootstrap.scss ***!
  \****************************************************/
(__unused_webpack_module, __webpack_exports__, __webpack_require__) {

__webpack_require__.r(__webpack_exports__);
// extracted by mini-css-extract-plugin


/***/ }

/******/ 	});
/************************************************************************/
/******/ 	// The module cache
/******/ 	var __webpack_module_cache__ = {};
/******/ 	
/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {
/******/ 		// Check if module is in cache
/******/ 		var cachedModule = __webpack_module_cache__[moduleId];
/******/ 		if (cachedModule !== undefined) {
/******/ 			return cachedModule.exports;
/******/ 		}
/******/ 		// Check if module exists (development only)
/******/ 		if (__webpack_modules__[moduleId] === undefined) {
/******/ 			var e = new Error("Cannot find module '" + moduleId + "'");
/******/ 			e.code = 'MODULE_NOT_FOUND';
/******/ 			throw e;
/******/ 		}
/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = __webpack_module_cache__[moduleId] = {
/******/ 			// no module.id needed
/******/ 			// no module.loaded needed
/******/ 			exports: {}
/******/ 		};
/******/ 	
/******/ 		// Execute the module function
/******/ 		__webpack_modules__[moduleId](module, module.exports, __webpack_require__);
/******/ 	
/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}
/******/ 	
/************************************************************************/
/******/ 	/* webpack/runtime/compat get default export */
/******/ 	(() => {
/******/ 		// getDefaultExport function for compatibility with non-harmony modules
/******/ 		__webpack_require__.n = (module) => {
/******/ 			var getter = module && module.__esModule ?
/******/ 				() => (module['default']) :
/******/ 				() => (module);
/******/ 			__webpack_require__.d(getter, { a: getter });
/******/ 			return getter;
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/define property getters */
/******/ 	(() => {
/******/ 		// define getter functions for harmony exports
/******/ 		__webpack_require__.d = (exports, definition) => {
/******/ 			for(var key in definition) {
/******/ 				if(__webpack_require__.o(definition, key) && !__webpack_require__.o(exports, key)) {
/******/ 					Object.defineProperty(exports, key, { enumerable: true, get: definition[key] });
/******/ 				}
/******/ 			}
/******/ 		};
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/hasOwnProperty shorthand */
/******/ 	(() => {
/******/ 		__webpack_require__.o = (obj, prop) => (Object.prototype.hasOwnProperty.call(obj, prop))
/******/ 	})();
/******/ 	
/******/ 	/* webpack/runtime/make namespace object */
/******/ 	(() => {
/******/ 		// define __esModule on exports
/******/ 		__webpack_require__.r = (exports) => {
/******/ 			if(typeof Symbol !== 'undefined' && Symbol.toStringTag) {
/******/ 				Object.defineProperty(exports, Symbol.toStringTag, { value: 'Module' });
/******/ 			}
/******/ 			Object.defineProperty(exports, '__esModule', { value: true });
/******/ 		};
/******/ 	})();
/******/ 	
/************************************************************************/
var __webpack_exports__ = {};
// This entry needs to be wrapped in an IIFE because it needs to be isolated against other modules in the chunk.
(() => {
/*!***********************!*\
  !*** ./assets/app.ts ***!
  \***********************/
__webpack_require__.r(__webpack_exports__);
/* harmony import */ var bootstrap_scss_bootstrap_scss__WEBPACK_IMPORTED_MODULE_0__ = __webpack_require__(/*! bootstrap/scss/bootstrap.scss */ "./node_modules/bootstrap/scss/bootstrap.scss");
/* harmony import */ var _styles_styles_scss__WEBPACK_IMPORTED_MODULE_1__ = __webpack_require__(/*! ./styles/styles.scss */ "./assets/styles/styles.scss");
/* harmony import */ var _ts_viewUsers__WEBPACK_IMPORTED_MODULE_2__ = __webpack_require__(/*! ./ts/viewUsers */ "./assets/ts/viewUsers.ts");
/* harmony import */ var _ts_settings__WEBPACK_IMPORTED_MODULE_3__ = __webpack_require__(/*! ./ts/settings */ "./assets/ts/settings.ts");
/* harmony import */ var _ts_pages_home__WEBPACK_IMPORTED_MODULE_4__ = __webpack_require__(/*! ./ts/pages/home */ "./assets/ts/pages/home.ts");
/* harmony import */ var _ts_pages_clients__WEBPACK_IMPORTED_MODULE_5__ = __webpack_require__(/*! ./ts/pages/clients */ "./assets/ts/pages/clients.ts");
/* harmony import */ var _ts_pages_projects__WEBPACK_IMPORTED_MODULE_6__ = __webpack_require__(/*! ./ts/pages/projects */ "./assets/ts/pages/projects.ts");
/* harmony import */ var _ts_pages_tasks__WEBPACK_IMPORTED_MODULE_7__ = __webpack_require__(/*! ./ts/pages/tasks */ "./assets/ts/pages/tasks.ts");
/* harmony import */ var _ts_pages_viewProject__WEBPACK_IMPORTED_MODULE_8__ = __webpack_require__(/*! ./ts/pages/viewProject */ "./assets/ts/pages/viewProject.ts");
/* harmony import */ var _ts_pages_login__WEBPACK_IMPORTED_MODULE_9__ = __webpack_require__(/*! ./ts/pages/login */ "./assets/ts/pages/login.ts");
/* harmony import */ var _ts_pages_login__WEBPACK_IMPORTED_MODULE_9___default = /*#__PURE__*/__webpack_require__.n(_ts_pages_login__WEBPACK_IMPORTED_MODULE_9__);











})();

/******/ })()
;
//# sourceMappingURL=app.bundle.js.map