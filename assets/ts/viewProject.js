"use strict";
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
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
Object.defineProperty(exports, "__esModule", { value: true });
exports.loadProjects = loadProjects;
exports.viewProjectDetails = viewProjectDetails;
exports.deleteProject = deleteProject;
exports.editProject = editProject;
exports.createNewProject = createNewProject;
var Api_1 = require("./services/Api");
console.log("Dans viewProject.ts");
// Sélection des éléments HTML
var projectList = document.getElementById("project-list");
var loadingElement = document.getElementById("loading");
var errorElement = document.getElementById("error");
function loadProjects() {
    return __awaiter(this, void 0, void 0, function () {
        var data, projects, error_1;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    _a.trys.push([0, 2, 3, 4]);
                    showLoading(true);
                    hideError();
                    return [4 /*yield*/, Api_1.default.loadProjectsFromApi()];
                case 1:
                    data = _a.sent();
                    projects = (data === null || data === void 0 ? void 0 : data.projects) || [];
                    if (projects.length === 0) {
                        projectList.innerHTML = "<p class='no-projects'>Aucun projet trouvé</p>";
                        return [2 /*return*/];
                    }
                    renderProjects(projects);
                    return [3 /*break*/, 4];
                case 2:
                    error_1 = _a.sent();
                    console.error("Erreur lors du chargement des projets :", error_1);
                    showError("Erreur lors du chargement des projets");
                    return [3 /*break*/, 4];
                case 3:
                    showLoading(false);
                    return [7 /*endfinally*/];
                case 4: return [2 /*return*/];
            }
        });
    });
}
function renderProjects(projects) {
    projectList.innerHTML = "";
    projects.forEach(function (project) {
        var projectItem = document.createElement("div");
        projectItem.classList.add("project-item");
        projectItem.innerHTML = "\n            <div class=\"project-header\">\n                <h3>".concat(escapeHtml(project.name), "</h3>\n                <div class=\"project-actions\">\n                    <button class=\"btn-view\" data-id=\"").concat(project.id, "\">Voir d\u00E9tails</button>\n                    <button class=\"btn-edit\" data-id=\"").concat(project.id, "\">Modifier</button>\n                    <button class=\"btn-delete\" data-id=\"").concat(project.id, "\">Supprimer</button>\n                </div>\n            </div>\n            <div class=\"project-info\">\n                <p class=\"description\">").concat(escapeHtml(project.description || "Pas de description"), "</p>\n                ").concat(project.theoricalDeadLine ? "<p class=\"deadline\">\u00C9ch\u00E9ance th\u00E9orique: ".concat(formatDate(project.theoricalDeadLine), "</p>") : '', "\n                ").concat(project.realDeadLine ? "<p class=\"real-deadline\">\u00C9ch\u00E9ance r\u00E9elle: ".concat(formatDate(project.realDeadLine), "</p>") : '', "\n            </div>\n        ");
        projectList.appendChild(projectItem);
        // Ajout des event listeners
        var viewBtn = projectItem.querySelector(".btn-view");
        var editBtn = projectItem.querySelector(".btn-edit");
        var deleteBtn = projectItem.querySelector(".btn-delete");
        viewBtn.addEventListener("click", function () { return viewProjectDetails(project.id); });
        editBtn.addEventListener("click", function () { return editProject(project); });
        deleteBtn.addEventListener("click", function () { return deleteProject(project.id, project.name); });
    });
}
function viewProjectDetails(projectId) {
    return __awaiter(this, void 0, void 0, function () {
        var data, error_2;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    _a.trys.push([0, 2, 3, 4]);
                    showLoading(true);
                    return [4 /*yield*/, Api_1.default.loadProjectFromApi(String(projectId))];
                case 1:
                    data = _a.sent();
                    if (!data || !data.project) {
                        showError("Impossible de récupérer les détails du projet");
                        return [2 /*return*/];
                    }
                    return [3 /*break*/, 4];
                case 2:
                    error_2 = _a.sent();
                    console.error("Erreur lors de la récupération du projet :", error_2);
                    showError("Erreur lors de la récupération du projet");
                    return [3 /*break*/, 4];
                case 3:
                    showLoading(false);
                    return [7 /*endfinally*/];
                case 4: return [2 /*return*/];
            }
        });
    });
}
function deleteProject(projectId, projectName) {
    return __awaiter(this, void 0, void 0, function () {
        var result, errorMessage, error_3;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    console.log("🔥 deleteProject appelée avec:", { projectId: projectId, projectName: projectName });
                    if (!confirm("\u00CAtes-vous s\u00FBr de vouloir supprimer le projet \"".concat(projectName, "\" ?"))) {
                        console.log("❌ Suppression annulée par l'utilisateur");
                        return [2 /*return*/];
                    }
                    _a.label = 1;
                case 1:
                    _a.trys.push([1, 8, 9, 10]);
                    showLoading(true);
                    console.log("🌐 Appel API de suppression...");
                    return [4 /*yield*/, Api_1.default.deleteProjectFromApi(String(projectId))];
                case 2:
                    result = _a.sent();
                    console.log("📦 Résultat API:", result);
                    if (!(result && (result.success === true || result.delete === true))) return [3 /*break*/, 6];
                    console.log("✅ Suppression réussie");
                    alert("Projet supprimé avec succès");
                    if (!(typeof loadProjects === 'function')) return [3 /*break*/, 4];
                    return [4 /*yield*/, loadProjects()];
                case 3:
                    _a.sent();
                    return [3 /*break*/, 5];
                case 4:
                    // Fallback: rechargement de la page
                    window.location.reload();
                    _a.label = 5;
                case 5: return [3 /*break*/, 7];
                case 6:
                    errorMessage = (result === null || result === void 0 ? void 0 : result.error) || (result === null || result === void 0 ? void 0 : result.message) || "Erreur lors de la suppression du projet";
                    console.error("❌ Échec de suppression:", errorMessage);
                    showError(errorMessage);
                    alert("Erreur: ".concat(errorMessage));
                    _a.label = 7;
                case 7: return [3 /*break*/, 10];
                case 8:
                    error_3 = _a.sent();
                    console.error("💥 Erreur lors de la suppression:", error_3);
                    showError("Erreur lors de la suppression du projet");
                    alert("Erreur lors de la suppression du projet");
                    return [3 /*break*/, 10];
                case 9:
                    showLoading(false);
                    return [7 /*endfinally*/];
                case 10: return [2 /*return*/];
            }
        });
    });
}
function editProject(project) {
    // Créer un formulaire de modification
    var editForm = createEditForm(project);
    document.body.appendChild(editForm);
}
function createEditForm(project) {
    var _this = this;
    var formOverlay = document.createElement("div");
    formOverlay.className = "form-overlay";
    formOverlay.innerHTML = "\n        <div class=\"edit-form\">\n            <div class=\"form-header\">\n                <h3>Modifier le projet</h3>\n                <button class=\"btn-close\" type=\"button\">\u00D7</button>\n            </div>\n            <form id=\"project-edit-form\">\n                <div class=\"form-group\">\n                    <label for=\"edit-name\">Nom du projet *</label>\n                    <input type=\"text\" id=\"edit-name\" name=\"name\" value=\"".concat(escapeHtml(project.name), "\" required>\n                </div>\n                <div class=\"form-group\">\n                    <label for=\"edit-description\">Description</label>\n                    <textarea id=\"edit-description\" name=\"description\" rows=\"3\">").concat(escapeHtml(project.description || ""), "</textarea>\n                </div>\n                <div class=\"form-row\">\n                    <div class=\"form-group\">\n                        <label for=\"edit-begin-date\">Date de d\u00E9but</label>\n                        <input type=\"date\" id=\"edit-begin-date\" name=\"beginDate\" value=\"").concat(formatDateForInput(project.beginDate), "\">\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"edit-theoretical-deadline\">\u00C9ch\u00E9ance th\u00E9orique</label>\n                        <input type=\"date\" id=\"edit-theoretical-deadline\" name=\"theoricalDeadLine\" value=\"").concat(formatDateForInput(project.theoricalDeadLine), "\">\n                    </div>\n                </div>\n                <div class=\"form-row\">\n                    <div class=\"form-group\">\n                        <label for=\"edit-real-deadline\">\u00C9ch\u00E9ance r\u00E9elle</label>\n                        <input type=\"date\" id=\"edit-real-deadline\" name=\"realDeadLine\" value=\"").concat(formatDateForInput(project.realDeadLine), "\">\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"edit-siret\">Num\u00E9ro SIRET</label>\n                        <input type=\"text\" id=\"edit-siret\" name=\"numSIRET\" value=\"").concat(project.numSIRET || "", "\" pattern=\"[0-9]{14}\">\n                    </div>\n                </div>\n                <div class=\"form-actions\">\n                    <button type=\"button\" class=\"btn-cancel\">Annuler</button>\n                    <button type=\"submit\" class=\"btn-save\">Sauvegarder</button>\n                </div>\n            </form>\n        </div>\n    ");
    var form = formOverlay.querySelector("#project-edit-form");
    var closeBtn = formOverlay.querySelector(".btn-close");
    var cancelBtn = formOverlay.querySelector(".btn-cancel");
    form.addEventListener("submit", function (e) { return __awaiter(_this, void 0, void 0, function () {
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
    [closeBtn, cancelBtn].forEach(function (btn) {
        btn.addEventListener("click", function () {
            document.body.removeChild(formOverlay);
        });
    });
    // Fermer en cliquant sur l'overlay
    formOverlay.addEventListener("click", function (e) {
        if (e.target === formOverlay) {
            document.body.removeChild(formOverlay);
        }
    });
    return formOverlay;
}
function handleEditSubmit(projectId, formOverlay) {
    return __awaiter(this, void 0, void 0, function () {
        var form, formData, updatedProject, result, errorMessage, error_4;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    form = formOverlay.querySelector("#project-edit-form");
                    formData = new FormData(form);
                    updatedProject = {
                        id: projectId,
                        name: formData.get("name"),
                        description: formData.get("description") || undefined,
                        beginDate: formData.get("beginDate") || undefined,
                        theoricalDeadLine: formData.get("theoricalDeadLine") || undefined,
                        realDeadLine: formData.get("realDeadLine") || undefined,
                        numSIRET: formData.get("numSIRET") || undefined,
                    };
                    _a.label = 1;
                case 1:
                    _a.trys.push([1, 3, 4, 5]);
                    showLoading(true);
                    return [4 /*yield*/, Api_1.default.editProjectFromApi(updatedProject)];
                case 2:
                    result = _a.sent();
                    if (result && result.success) {
                        document.body.removeChild(formOverlay);
                        loadProjects(); // Recharger la liste
                    }
                    else {
                        errorMessage = (result === null || result === void 0 ? void 0 : result.error) || "Erreur lors de la modification du projet";
                        showError(errorMessage);
                    }
                    return [3 /*break*/, 5];
                case 3:
                    error_4 = _a.sent();
                    console.error("Erreur lors de la modification :", error_4);
                    showError("Erreur lors de la modification du projet");
                    return [3 /*break*/, 5];
                case 4:
                    showLoading(false);
                    return [7 /*endfinally*/];
                case 5: return [2 /*return*/];
            }
        });
    });
}
// Fonctions utilitaires
function showLoading(show) {
    if (loadingElement) {
        loadingElement.style.display = show ? "block" : "none";
    }
}
function showError(message) {
    if (errorElement) {
        errorElement.textContent = message;
        errorElement.style.display = "block";
        setTimeout(function () { return hideError(); }, 5000);
    }
}
function hideError() {
    if (errorElement) {
        errorElement.style.display = "none";
    }
}
function escapeHtml(text) {
    if (!text)
        return "";
    var div = document.createElement("div");
    div.textContent = text;
    return div.innerHTML;
}
function formatDate(dateString) {
    try {
        var date = new Date(dateString);
        return date.toLocaleDateString("fr-FR");
    }
    catch (_a) {
        return dateString;
    }
}
function formatDateForInput(dateString) {
    if (!dateString)
        return "";
    try {
        var date = new Date(dateString);
        return date.toISOString().split('T')[0];
    }
    catch (_a) {
        return "";
    }
}
function createNewProject() {
    var createForm = createCreateForm();
    document.body.appendChild(createForm);
}
function createCreateForm() {
    var _this = this;
    var formOverlay = document.createElement("div");
    formOverlay.className = "form-overlay";
    formOverlay.innerHTML = "\n        <div class=\"edit-form\">\n            <div class=\"form-header\">\n                <h3>Cr\u00E9er un nouveau projet</h3>\n                <button class=\"btn-close\" type=\"button\">\u00D7</button>\n            </div>\n            <form id=\"project-create-form\">\n                <div class=\"form-group\">\n                    <label for=\"create-name\">Nom du projet *</label>\n                    <input type=\"text\" id=\"create-name\" name=\"name\" required>\n                </div>\n                <div class=\"form-group\">\n                    <label for=\"create-description\">Description</label>\n                    <textarea id=\"create-description\" name=\"description\" rows=\"3\"></textarea>\n                </div>\n                <div class=\"form-row\">\n                    <div class=\"form-group\">\n                        <label for=\"create-begin-date\">Date de d\u00E9but</label>\n                        <input type=\"date\" id=\"create-begin-date\" name=\"beginDate\">\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"create-theoretical-deadline\">\u00C9ch\u00E9ance th\u00E9orique</label>\n                        <input type=\"date\" id=\"create-theoretical-deadline\" name=\"theoricalDeadLine\">\n                    </div>\n                </div>\n                <div class=\"form-row\">\n                    <div class=\"form-group\">\n                        <label for=\"create-real-deadline\">\u00C9ch\u00E9ance r\u00E9elle</label>\n                        <input type=\"date\" id=\"create-real-deadline\" name=\"realDeadLine\">\n                    </div>\n                    <div class=\"form-group\">\n                        <label for=\"create-siret\">Num\u00E9ro SIRET</label>\n                        <input type=\"text\" id=\"create-siret\" name=\"numSIRET\" pattern=\"[0-9]{14}\">\n                    </div>\n                </div>\n                <div class=\"form-actions\">\n                    <button type=\"button\" class=\"btn-cancel\">Annuler</button>\n                    <button type=\"submit\" class=\"btn-save\">Cr\u00E9er</button>\n                </div>\n            </form>\n        </div>\n    ";
    var form = formOverlay.querySelector("#project-create-form");
    var closeBtn = formOverlay.querySelector(".btn-close");
    var cancelBtn = formOverlay.querySelector(".btn-cancel");
    form.addEventListener("submit", function (e) { return __awaiter(_this, void 0, void 0, function () {
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
    [closeBtn, cancelBtn].forEach(function (btn) {
        btn.addEventListener("click", function () {
            document.body.removeChild(formOverlay);
        });
    });
    // Fermer en cliquant sur l'overlay
    formOverlay.addEventListener("click", function (e) {
        if (e.target === formOverlay) {
            document.body.removeChild(formOverlay);
        }
    });
    return formOverlay;
}
function handleCreateSubmit(formOverlay) {
    return __awaiter(this, void 0, void 0, function () {
        var form, formData, newProject, result, error_5;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    form = formOverlay.querySelector("#project-create-form");
                    formData = new FormData(form);
                    newProject = {
                        name: formData.get("name"),
                        description: formData.get("description") || undefined,
                        beginDate: formData.get("beginDate") || undefined,
                        theoricalDeadLine: formData.get("theoricalDeadLine") || undefined,
                        realDeadLine: formData.get("realDeadLine") || undefined,
                        numSIRET: formData.get("numSIRET") || undefined,
                    };
                    _a.label = 1;
                case 1:
                    _a.trys.push([1, 3, 4, 5]);
                    showLoading(true);
                    return [4 /*yield*/, Api_1.default.addProjectFromApi(newProject)];
                case 2:
                    result = _a.sent();
                    if (newProject) {
                        document.body.removeChild(formOverlay);
                        loadProjects(); // Recharger la liste
                    }
                    return [3 /*break*/, 5];
                case 3:
                    error_5 = _a.sent();
                    console.error("Erreur lors de la création :", error_5);
                    showError("Erreur lors de la création du projet");
                    return [3 /*break*/, 5];
                case 4:
                    showLoading(false);
                    return [7 /*endfinally*/];
                case 5: return [2 /*return*/];
            }
        });
    });
}
document.addEventListener("DOMContentLoaded", function () {
    var createBtn = document.getElementById("create-project-btn");
    if (createBtn) {
        createBtn.addEventListener("click", createNewProject);
    }
    loadProjects();
});
