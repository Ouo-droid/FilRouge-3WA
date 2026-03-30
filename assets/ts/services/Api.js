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
var Api = /** @class */ (function () {
    function Api() {
    }
    Api.loadUsersFromApi = function () {
        return __awaiter(this, void 0, void 0, function () {
            var response, data, error_1;
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
                    case 2:
                        data = _a.sent();
                        console.log('data ', data);
                        return [2 /*return*/, data];
                    case 3:
                        error_1 = _a.sent();
                        console.error("Erreur attrap\u00E9e : ".concat(error_1));
                        return [3 /*break*/, 4];
                    case 4: return [2 /*return*/];
                }
            });
        });
    };
    Api.loadUserFromApi = function (userId) {
        console.log("dans loadUserFromApi");
        return fetch("/api/user/".concat(userId), {
            method: "GET",
        })
            .then(function (response) {
            console.log(response);
            if (response.status == 200) {
                return response.json();
            }
        })
            .then(function (data) {
            console.log("data :", data);
            if (data) {
                showModal({
                    name: data.user.name,
                    email: data.user.email,
                    roles: data.user.roles,
                });
                return data;
            }
            else {
                showModal("User non récupéré");
            }
        })
            .catch(function (error) {
            console.error("Erreur catch :", error);
        });
    };
    Api.addUserFromApi = function (user) {
        console.log("dans addUserFromApi");
        if (!user.firstname || !user.lastname || !user.email || !user.password) {
            return Promise.reject(new Error("Tous les champs obligatoires doivent être remplis"));
        }
        return fetch("api/add/user", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                firstname: user.firstname,
                lastname: user.lastname,
                email: user.email,
                password: user.password,
            }),
        })
            .then(function (response) {
            console.log("Status de la réponse ", response.status);
            if (!response.ok) {
                throw new Error("Erreur HTTP ".concat(response.status));
            }
            return response.json();
        })
        .then(function (data) {
            console.log("Utilisateur cr\u00E9e :", data);
            if (data.success) {
                showModal("Utilisateur ajouté avec succès ! ");
                return Promise.resolve();
            }
            else {
                var error = new Error(data.message || "Erreur lors de la création de l'utilisateur");
                showModal("Erreur : ".concat(data.message));
                return Promise.reject(error);
            }
        })
        .catch(function (e) {
            console.error(e);
            showModal("Erreur : ".concat(e.message || "Une erreur est survenue"));
            return Promise.reject(e);
        });
    };
    Api.deleteUserFromApi = function (userId) {
        console.log("dans deleteUserFromApi", userId);
        return fetch("/api/delete/user/".concat(userId), {
            method: "DELETE",
        })
            .then(function (response) {
            console.log("statut de la r\u00E9ponse", response.status);
            return response.json();
        })
            .then(function (data) {
            console.log("data", data.delete);
            return data;
        });
    };
    Api.editUserFromApi = function (user) {
        console.log("dans editUserFromApi", user);
        if (!user) {
            return Promise.reject(new Error("Utilisateur inconnu"));
        }
        var id = user.id, firstname = user.firstname, lastname = user.lastname, email = user.email;
        return fetch("api/edit/user/".concat(id), {
            method: "PATCH",
            body: JSON.stringify({
                firstname: firstname,
                lastname: lastname,
                email: email,
            }),
            headers: {
                "Content-Type": "application/json",
            },
        })
            .then(function (response) {
            if (response.status == 200) {
                console.log(response.ok);
                return response.json();
            }
            throw new Error("Erreur HTTP ".concat(response.status));
        })
            .then(function (data) {
            console.log("Utilisateur crée", data);
            if (data.success) {
                console.log('ici, ', data.success);
                showModal("Utilisateur modifié avec succès ! ");
                return data;
            }
            else {
                showModal("Erreur : ".concat(data.message));
            }
        })
            .catch(function (error) {
            console.error("Erreur attrap\u00E9e ".concat(error));
        });
    };
    Api.loadProjectsFromApi = function () {
        return __awaiter(this, void 0, void 0, function () {
            var response, data, error_2;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        _a.trys.push([0, 3, , 4]);
                        return [4 /*yield*/, fetch("api/projects")];
                    case 1:
                        response = _a.sent();
                        if (!response.ok) {
                            throw new Error("Erreur lors de la r\u00E9cup\u00E9ration des projets");
                        }
                        return [4 /*yield*/, response.json()];
                    case 2:
                        data = _a.sent();
                        console.log('data ', data);
                        return [2 /*return*/, data];
                    case 3:
                        error_2 = _a.sent();
                        console.error("Erreur attrap\u00E9e : ".concat(error_2));
                        return [3 /*break*/, 4];
                    case 4: return [2 /*return*/];
                }
            });
        });
    };
    Api.loadProjectFromApi = function (projectId) {
        return __awaiter(this, void 0, void 0, function () {
            var response, data, error_3;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        console.log("dans loadProjectFromApi");
                        _a.label = 1;
                    case 1:
                        _a.trys.push([1, 6, , 7]);
                        return [4 /*yield*/, fetch("/api/project/".concat(projectId), {
                                method: "GET",
                            })];
                    case 2:
                        response = _a.sent();
                        if (!(response.status === 200)) return [3 /*break*/, 4];
                        return [4 /*yield*/, response.json()];
                    case 3:
                        data = _a.sent();
                        console.log("data :", data);
                        if (data) {
                            Api.showModalProject({
                                name: data.project.name,
                                description: data.project.description,
                                theoricalDeadLine: data.project.theoricalDeadLine,
                                realDeadLine: data.project.realDeadLine,
                            });
                            return [2 /*return*/, data];
                        }
                        else {
                            Api.showModalProject("Projet non récupéré");
                        }
                        return [3 /*break*/, 5];
                    case 4:
                        Api.showModalProject("Erreur lors de la récupération du projet");
                        _a.label = 5;
                    case 5: return [3 /*break*/, 7];
                    case 6:
                        error_3 = _a.sent();
                        console.error("Erreur catch :", error_3);
                        Api.showModalProject("Erreur lors de la récupération du projet");
                        return [3 /*break*/, 7];
                    case 7: return [2 /*return*/];
                }
            });
        });
    };
    Api.addProjectFromApi = function (project) {
        return __awaiter(this, void 0, void 0, function () {
            var response, data, error_4;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        console.log("dans addProjectFromApi");
                        if (!project.name) {
                            return [2 /*return*/, Promise.reject(new Error("Le nom du projet est obligatoire"))];
                        }
                        _a.label = 1;
                    case 1:
                        _a.trys.push([1, 4, , 5]);
                        return [4 /*yield*/, fetch("api/add/project", {
                                method: "POST",
                                headers: {
                                    "Content-Type": "application/json",
                                },
                                body: JSON.stringify(project),
                            })];
                    case 2:
                        response = _a.sent();
                        console.log("Status de la réponse ", response.status);
                        if (!response.ok) {
                            throw new Error("Erreur HTTP ".concat(response.status));
                        }
                        return [4 /*yield*/, response.json()];
                    case 3:
                        data = _a.sent();
                        console.log("Projet cr\u00E9\u00E9 :", data);
                        if (data.success) {
                            Api.showModalProject("Projet ajouté avec succès ! ");
                        }
                        else {
                            Api.showModalProject("Erreur : ".concat(data.message));
                        }
                        return [3 /*break*/, 5];
                    case 4:
                        error_4 = _a.sent();
                        console.error(error_4);
                        Api.showModalProject("Erreur lors de la création du projet");
                        return [3 /*break*/, 5];
                    case 5: return [2 /*return*/];
                }
            });
        });
    };
    Api.editProjectFromApi = function (project) {
        return __awaiter(this, void 0, void 0, function () {
            var response, data, error_5;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        console.log("dans editProjectFromApi", project);
                        if (!project || !project.id) {
                            return [2 /*return*/, Promise.reject(new Error("Projet inconnu"))];
                        }
                        _a.label = 1;
                    case 1:
                        _a.trys.push([1, 6, , 7]);
                        return [4 /*yield*/, fetch("api/edit/project/".concat(project.id), {
                                method: "PUT",
                                body: JSON.stringify(project),
                                headers: {
                                    "Content-Type": "application/json",
                                },
                            })];
                    case 2:
                        response = _a.sent();
                        if (!(response.status === 200)) return [3 /*break*/, 4];
                        console.log(response.ok);
                        return [4 /*yield*/, response.json()];
                    case 3:
                        data = _a.sent();
                        console.log("Projet modifié", data);
                        if (data.success) {
                            console.log('ici, ', data.success);
                            Api.showModalProject("Projet modifié avec succès ! ");
                            return [2 /*return*/, data];
                        }
                        else {
                            Api.showModalProject("Erreur : ".concat(data.message));
                        }
                        return [3 /*break*/, 5];
                    case 4: throw new Error("Erreur HTTP ".concat(response.status));
                    case 5: return [3 /*break*/, 7];
                    case 6:
                        error_5 = _a.sent();
                        console.error("Erreur attrap\u00E9e ".concat(error_5));
                        Api.showModalProject("Erreur lors de la modification du projet");
                        return [3 /*break*/, 7];
                    case 7: return [2 /*return*/];
                }
            });
        });
    };
    Api.deleteProjectFromApi = function (projectId) {
        return __awaiter(this, void 0, void 0, function () {
            var response, data, error_6;
            return __generator(this, function (_a) {
                switch (_a.label) {
                    case 0:
                        console.log("dans deleteProjectFromApi", projectId);
                        _a.label = 1;
                    case 1:
                        _a.trys.push([1, 4, , 5]);
                        return [4 /*yield*/, fetch("/api/delete/project/".concat(projectId), {
                                method: "DELETE",
                            })];
                    case 2:
                        response = _a.sent();
                        console.log("statut de la r\u00E9ponse", response.status);
                        return [4 /*yield*/, response.json()];
                    case 3:
                        data = _a.sent();
                        console.log("data", data.delete);
                        return [2 /*return*/, data];
                    case 4:
                        error_6 = _a.sent();
                        console.error("Erreur lors de la suppression :", error_6);
                        throw error_6;
                    case 5: return [2 /*return*/];
                }
            });
        });
    };
    // MODAL METHODS (made static)
    Api.showModal = function (content) {
        var modal = document.getElementById("notificationModal");
        var modalMessage = document.getElementById("modal-message");
        var modalUserDetails = document.getElementById("modal-user-details");
        var modalName = document.getElementById("modal-name");
        var modalEmail = document.getElementById("modal-email");
        var modalRoles = document.getElementById("modal-roles");
        var closeModal = document.getElementById("close-modal");
        modalMessage.style.display = "none";
        modalUserDetails.style.display = "none";
        if (typeof content === "string") {
            modalMessage.textContent = content;
            modalMessage.style.display = "block";
        }
        else {
            modalName.textContent = content.name || "Non disponible";
            modalEmail.textContent = content.email || "Non disponible";
            modalRoles.textContent = content.roles[0] || "Non disponible";
            modalUserDetails.style.display = "block";
        }
        modal.style.display = "flex";
        closeModal.addEventListener("click", function () {
            modal.style.display = "none";
            location.reload();
        });
    };
    Api.showModalProject = function (content) {
        var modal = document.getElementById("projectModal");
        var modalMessage = document.getElementById("project-modal-message");
        var modalProjectDetails = document.getElementById("project-modal-details");
        var modalName = document.getElementById("project-modal-name");
        var modalDescription = document.getElementById("project-modal-description");
        var modalTheoricalDeadline = document.getElementById("project-modal-theoretical-deadline");
        var modalRealDeadline = document.getElementById("project-modal-real-deadline");
        var closeModal = document.getElementById("close-project-modal");
        modalMessage.style.display = "none";
        modalProjectDetails.style.display = "none";
        if (typeof content === "string") {
            modalMessage.textContent = content;
            modalMessage.style.display = "block";
        }
        else {
            modalName.textContent = content.name || "Non disponible";
            modalDescription.textContent = content.description || "Non disponible";
            modalTheoricalDeadline.textContent = content.theoricalDeadLine || "Non disponible";
            modalRealDeadline.textContent = content.realDeadLine || "Non disponible";
            modalProjectDetails.style.display = "block";
        }
        modal.style.display = "flex";
        closeModal.addEventListener("click", function () {
            modal.style.display = "none";
            location.reload();
        });
    };
    return Api;
}());
exports.default = Api;
function showModal(content) {
    var modal = document.getElementById("notificationModal");
    var modalMessage = document.getElementById("modal-message");
    var modalUserDetails = document.getElementById("modal-user-details");
    var modalName = document.getElementById("modal-name");
    var modalEmail = document.getElementById("modal-email");
    var modalRoles = document.getElementById("modal-roles");
    var closeModal = document.getElementById("close-modal");
    modalMessage.style.display = "none";
    modalUserDetails.style.display = "none";
    if (typeof content === "string") {
        modalMessage.textContent = content;
        modalMessage.style.display = "block";
    }
    else {
        modalName.textContent = content.name || "Non disponible";
        modalEmail.textContent = content.email || "Non disponible";
        modalRoles.textContent = content.roles[0] || "Non disponible";
        modalUserDetails.style.display = "block";
    }
    modal.style.display = "flex";
    closeModal.addEventListener("click", function () {
        modal.style.display = "none";
        location.reload();
    });
}
