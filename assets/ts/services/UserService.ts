import { getCsrfToken } from "./Api";
import { User } from "../interfaces/UserInterface";

export default class UserService {

    static async loadUsersFromApi() {
        try {
            const response = await fetch("api/users");
            if (!response.ok) {
                throw new Error(`Erreur lors de la récupération des utilisateurs`);
            }
            return await response.json();
        } catch (error) {
            console.error(`Erreur attrapée : ${error}`);
        }
    }

    static loadUserFromApi(userId: string): Promise<void> {
        return fetch(`/api/user/${userId}`, { method: "GET" })
            .then((response) => {
                if (response.status === 200) return response.json();
            })
            .then((data) => {
                if (data) {
                    UserService.showModal({
                        name: data.user.name,
                        email: data.user.email,
                        roles: data.user.roles,
                    });
                    return data;
                } else {
                    UserService.showModal("User non récupéré");
                }
            })
            .catch((error) => {
                console.error("Erreur catch :", error);
            });
    }

    static addUserFromApi(user: Omit<User, "id">): Promise<void> {
        if (!user.firstname || !user.lastname || !user.email || !user.password || !user.roleId) {
            return Promise.reject(new Error("Tous les champs obligatoires doivent être remplis"));
        }

        return fetch("api/add/user", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": getCsrfToken(),
            },
            body: JSON.stringify({
                firstname: user.firstname,
                lastname: user.lastname,
                email: user.email,
                password: user.password,
                roleId: user.roleId,
            }),
        })
            .then((response) => {
                if (!response.ok) throw new Error(`Erreur HTTP ${response.status}`);
                return response.json();
            })
            .then((data) => {
                if (data.success) {
                    UserService.showModal("Utilisateur ajouté avec succès !");
                    return Promise.resolve();
                } else {
                    const error = new Error(data.message || "Erreur lors de la création de l'utilisateur");
                    UserService.showModal(`Erreur : ${data.message}`);
                    return Promise.reject(error);
                }
            })
            .catch((e) => {
                console.error(e);
                UserService.showModal(`Erreur : ${e.message || "Une erreur est survenue"}`);
                return Promise.reject(e);
            });
    }

    static deleteUserFromApi(userId: string): Promise<Object> {
        return fetch(`/api/delete/user/${userId}`, {
            method: "DELETE",
            headers: { "X-CSRF-Token": getCsrfToken() },
        })
            .then((response) => response.json())
            .then((data: { delete: string }) => data);
    }

    static editUserFromApi(user: {
        id: string;
        firstname: string;
        lastname: string;
        email: string;
        roleId?: string;
        jobtitle?: string;
        fieldofwork?: string;
        degree?: string[];
    }): Promise<any> {
        if (!user) return Promise.reject(new Error("Utilisateur inconnu"));

        const { id, firstname, lastname, email, roleId, jobtitle, fieldofwork, degree } = user;
        const body: Record<string, any> = { firstname, lastname, email };
        if (roleId) body.roleId = roleId;
        if (jobtitle !== undefined) body.jobtitle = jobtitle;
        if (fieldofwork !== undefined) body.fieldofwork = fieldofwork;
        if (degree !== undefined) body.degree = degree;

        return fetch(`api/edit/user/${id}`, {
            method: "PATCH",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-Token": getCsrfToken(),
            },
            body: JSON.stringify(body),
        })
            .then((response) => {
                if (response.status === 200) return response.json();
                throw new Error(`Erreur HTTP ${response.status}`);
            })
            .then((data) => {
                if (data.success) {
                    UserService.showModal("Utilisateur modifié avec succès !");
                    return data;
                } else {
                    UserService.showModal(`Erreur : ${data.message}`);
                }
            })
            .catch((error) => {
                console.error(`Erreur attrapée ${error}`);
            });
    }

    static showToast(message: string, type: 'success' | 'error' = 'success') {
        const toast = document.createElement('div');
        toast.className = `task-toast task-toast--${type}`;
        toast.setAttribute('role', type === 'error' ? 'alert' : 'status');
        toast.setAttribute('aria-live', type === 'error' ? 'assertive' : 'polite');
        toast.setAttribute('aria-atomic', 'true');
        toast.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}" aria-hidden="true"></i>
            <span>${message}</span>
        `;
        document.body.appendChild(toast);
        requestAnimationFrame(() => toast.classList.add('task-toast--visible'));
        setTimeout(() => {
            toast.classList.remove('task-toast--visible');
            toast.addEventListener('transitionend', () => toast.remove(), { once: true });
            if (type === 'success') location.reload();
        }, 2500);
    }

    /** @deprecated use showToast */
    static showModal(content: string | { name: string; email: string; roles: Array<any> }) {
        if (typeof content === 'string') {
            const isError = content.toLowerCase().startsWith('erreur');
            UserService.showToast(content, isError ? 'error' : 'success');
        }
    }
}
