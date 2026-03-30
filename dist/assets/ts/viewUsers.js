import Api from "./services/Api";
console.log("Dans viewUser.ts");
// Si la page a l'id dynamical-user alors je continue le script
var id = document.getElementById("dynamical-user");
if (id) {
    console.log("Dans la page qui contient l'\u00E9l\u00E9ment qui a pour id dynamical-user");
    Api.loadUsersFromApi()
        .then(function (data) {
        console.log("Donnés utilisateurs", data);
        var usersList = document.querySelector(".users-list");
        if (!usersList) {
            console.error("Impossible de trouver l'élément .user-list");
            return;
        }
        usersList.innerHTML = "<h2>Liste des utilisateurs</h2>";
        var users = data.users;
        if (users && users.length > 0) {
            users.forEach(function (user) {
                var userSection = document.createElement("section");
                userSection.classList.add("d-flex", "gap-2", "border-1", "my-3", "align-items-center");
                userSection.setAttribute("data-userid", String(user.id));
                userSection.setAttribute("data-firstname", user.firstname);
                userSection.setAttribute("data-lastname", user.lastname);
                userSection.setAttribute("data-email", user.email);
                // Affichage du nom complet
                var fullName = "".concat(user.firstname, " ").concat(user.lastname);
                userSection.innerHTML = "\n                <p>".concat(user.id, "</p>\n                <p class=\"firstname\">").concat(user.firstname, "</p>\n                <p class=\"lastname\">").concat(user.lastname, "</p>\n                <p class=\"email\">").concat(user.email, "</p>\n                <button class=\"btn btn-danger\">Supprimer</button>\n                <button class=\"btn btn-warning\">Modifier</button>\n                <button class=\"btn btn-primary\">Voir</button>\n            ");
                usersList.appendChild(userSection);
                // Gestion du click sur les boutons .btn-danger
                var btnDangers = document.querySelectorAll(".btn-danger");
                manageDelete(btnDangers);
                var btnEdit = document.querySelectorAll(".btn-warning");
                manageEdit(btnEdit);
                var btnShow = document.querySelectorAll(".btn-primary");
                manageShow(btnShow);
            });
        }
        else {
            var noUsers = document.createElement("p");
            noUsers.textContent = "Aucun utilisateur trouvé";
            usersList.appendChild(noUsers);
        }
    })
        .catch(function (e) {
        console.error("Erreur attrap\u00E9e, ".concat(e));
    });
    // Gestion de l'ajout d'un utilisateur
    var formAdd = document.querySelector("#form-add-user");
    manageAdd(formAdd);
}
// Variables globales pour éviter les multiples listeners et doubles soumissions
var isSubmitting = false; // Flag global pour empêcher les doubles soumissions
var FORM_LISTENER_ATTACHED = Symbol('formListenerAttached');
function manageAdd(formAdd) {
    // Vérifier si un listener a déjà été attaché
    if (formAdd[FORM_LISTENER_ATTACHED]) {
        console.log("Listener déjà attaché, on ignore");
        return;
    }
    // Marquer que le listener est attaché
    formAdd[FORM_LISTENER_ATTACHED] = true;
    var submitButton = formAdd.querySelector('button[type="submit"]');
    var originalButtonContent = submitButton ? submitButton.innerHTML : "";
    // Créer le handler
    var formSubmitHandler = function (event) {
        // Le formulaire n'envoie pas directement d'information
        event.preventDefault();
        // Empêcher les doubles soumissions
        if (isSubmitting) {
            return;
        }
        if (submitButton) {
            submitButton.disabled = true;
            submitButton.innerHTML = "Envoi en cours...";
        }
        isSubmitting = true;
        // Récupération des données via formData
        var formData = new FormData(formAdd);
        var addedUser = {
            firstname: formData.get("firstname"), // Changé
            lastname: formData.get("lastname"), // Ajouté
            email: formData.get("email"),
            password: formData.get("password"),
        };
        console.log("addedUser", addedUser);
        Api.addUserFromApi(addedUser)
            .then(function () {
            // Réinitialiser le formulaire après succès
            formAdd.reset();
            // Recharger la liste des utilisateurs
            Api.loadUsersFromApi();
        })
            .catch(function (error) {
            console.error("Erreur lors de l'ajout de l'utilisateur:", error);
        })
            .finally(function () {
            // Réactiver le bouton après traitement
            isSubmitting = false;
            if (submitButton) {
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonContent;
            }
        });
    };
    // Attacher le listener une seule fois
    formAdd.addEventListener("submit", formSubmitHandler, { once: false });
}
function manageDelete(btnDangers) {
    btnDangers.forEach(function (btn) {
        btn.addEventListener("click", function (event) {
            console.log("Click sur le bouton de suppression");
            // Suppression du div parent pour l'affichage
            var parentSection = event.target.parentElement;
            if (parentSection) {
                var userId = parentSection.getAttribute("data-userid");
                // On cache l'élément du DOM
                parentSection.style.display = "none";
                // Appel de la requête delete (api)
                if (userId) {
                    Api.deleteUserFromApi(userId)
                        .then(function (data) {
                        console.log(data);
                        // Test si la suppression a bien eu lieu
                        if ("delete" in data && data.delete == "true") {
                            parentSection.remove();
                        }
                    })
                        .catch(function (error) {
                        console.error("Erreur attrapée dans viewUser.ts", error);
                        setTimeout(function () {
                            parentSection.style.display = "flex";
                        }, 3000);
                    });
                }
            }
        });
    });
}
function manageEdit(btnEdit) {
    btnEdit.forEach(function (btn) {
        btn.addEventListener("click", function (event) {
            console.log("click sur btn edit");
            var parentSection = event.target.parentElement;
            if (!parentSection) {
                return;
            }
            var firstnameElement = parentSection.querySelector("p.firstname");
            var lastnameElement = parentSection.querySelector("p.lastname");
            var emailElement = parentSection.querySelector("p.email");
            var userId = parentSection.getAttribute("data-userid");
            if (firstnameElement && lastnameElement && emailElement && userId) {
                // Je modifie les balises <p> en input
                var firstnameInput_1 = document.createElement("input");
                firstnameInput_1.type = "text";
                firstnameInput_1.value = firstnameElement.textContent || '';
                var lastnameInput_1 = document.createElement("input");
                lastnameInput_1.type = "text";
                lastnameInput_1.value = lastnameElement.textContent || '';
                var emailInput_1 = document.createElement("input");
                emailInput_1.type = "email";
                emailInput_1.value = emailElement.textContent || '';
                firstnameElement.replaceWith(firstnameInput_1);
                lastnameElement.replaceWith(lastnameInput_1);
                emailElement.replaceWith(emailInput_1);
                // J'ajoute ensuite un bouton pour sauvegarder
                var saveBtn = document.createElement("button");
                saveBtn.className = "btn btn-success";
                saveBtn.textContent = "Enregistrer";
                parentSection.appendChild(saveBtn);
                saveBtn.addEventListener("click", function () {
                    var newFirstname = firstnameInput_1.value;
                    var newLastname = lastnameInput_1.value;
                    var newEmail = emailInput_1.value;
                    console.log({
                        id: userId,
                        firstname: newFirstname,
                        lastname: newLastname,
                        email: newEmail
                    });
                    Api.editUserFromApi({
                        id: userId,
                        firstname: newFirstname,
                        lastname: newLastname,
                        email: newEmail
                    });
                });
            }
        });
    });
}
function manageShow(btnShow) {
    btnShow.forEach(function (btn) {
        btn.addEventListener('click', function (event) {
            console.log("click sur le btn Voir");
            var parentSection = event.target.parentElement;
            if (!parentSection) {
                return;
            }
            var userId = parentSection.getAttribute("data-userid");
            if (userId) {
                Api.loadUserFromApi(userId);
            }
        });
    });
}
console.log("fin");
//# sourceMappingURL=viewUsers.js.map