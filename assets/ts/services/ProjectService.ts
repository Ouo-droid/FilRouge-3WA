import { getCsrfToken } from "./Api";
import { Project } from "../interfaces/ProjectInterface";

export default class ProjectService {

    static async loadProjectsFromApi() {
        try {
            const response = await fetch("api/projects");
            if (!response.ok) throw new Error(`Erreur lors de la récupération des projets`);
            return await response.json();
        } catch (error) {
            console.error(`Erreur attrapée : ${error}`);
        }
    }

    static async loadProjectFromApi(projectId: string): Promise<any> {
        try {
            const response = await fetch(`/api/project/${projectId}`, { method: "GET" });

            if (response.status === 200) {
                const data = await response.json();
                if (data) {
                    ProjectService.showModalProject({
                        name: data.project.name,
                        description: data.project.description,
                        theoricalDeadLine: data.project.theoricalDeadLine,
                        realDeadLine: data.project.realDeadLine,
                    });
                    return data;
                } else {
                    ProjectService.showModalProject("Projet non récupéré");
                }
            } else {
                ProjectService.showModalProject("Erreur lors de la récupération du projet");
            }
        } catch (error) {
            console.error("Erreur catch :", error);
            ProjectService.showModalProject("Erreur lors de la récupération du projet");
        }
    }

    static async addProjectFromApi(project: Omit<Project, "id">): Promise<void> {
        if (!project.name) {
            return Promise.reject(new Error("Le nom du projet est obligatoire"));
        }

        try {
            const response = await fetch("api/add/project", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-Token": getCsrfToken(),
                },
                body: JSON.stringify(project),
            });

            if (!response.ok) throw new Error(`Erreur HTTP ${response.status}`);

            const data = await response.json();

            if (data.success) {
                ProjectService.showModalProject("Projet ajouté avec succès !");
            } else {
                ProjectService.showModalProject(`Erreur : ${data.message}`);
            }
        } catch (error) {
            console.error(error);
            ProjectService.showModalProject("Erreur lors de la création du projet");
        }
    }

    static async editProjectFromApi(project: Project): Promise<any> {
        if (!project || !project.id) {
            return Promise.reject(new Error("Projet inconnu"));
        }

        try {
            const response = await fetch(`api/edit/project/${project.id}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-Token": getCsrfToken(),
                },
                body: JSON.stringify(project),
            });

            if (response.status === 200) {
                const data = await response.json();
                if (data.success) {
                    ProjectService.showModalProject("Projet modifié avec succès !");
                    return data;
                } else {
                    ProjectService.showModalProject(`Erreur : ${data.message}`);
                }
            } else {
                throw new Error(`Erreur HTTP ${response.status}`);
            }
        } catch (error) {
            console.error(`Erreur attrapée ${error}`);
            ProjectService.showModalProject("Erreur lors de la modification du projet");
        }
    }

    static async deleteProjectFromApi(projectId: string): Promise<Object> {
        try {
            const response = await fetch(`/api/delete/project/${projectId}`, {
                method: "DELETE",
                headers: { "X-CSRF-Token": getCsrfToken() },
            });
            return await response.json();
        } catch (error) {
            console.error("Erreur lors de la suppression :", error);
            throw error;
        }
    }

    static showModalProject(
        content: string | { name: string; description?: string; theoricalDeadLine?: string; realDeadLine?: string },
    ) {
        const modal = document.getElementById("projectModal") as HTMLElement;
        const modalMessage = document.getElementById("project-modal-message") as HTMLElement;
        const modalProjectDetails = document.getElementById("project-modal-details") as HTMLElement;
        const modalName = document.getElementById("project-modal-name") as HTMLElement;
        const modalDescription = document.getElementById("project-modal-description") as HTMLElement;
        const modalTheoricalDeadline = document.getElementById("project-modal-theoretical-deadline") as HTMLElement;
        const modalRealDeadline = document.getElementById("project-modal-real-deadline") as HTMLElement;
        const closeModal = document.getElementById("close-project-modal") as HTMLElement;

        modalMessage.style.display = "none";
        modalProjectDetails.style.display = "none";

        if (typeof content === "string") {
            modalMessage.textContent = content;
            modalMessage.style.display = "block";
        } else {
            modalName.textContent = content.name || "Non disponible";
            modalDescription.textContent = content.description || "Non disponible";
            modalTheoricalDeadline.textContent = content.theoricalDeadLine || "Non disponible";
            modalRealDeadline.textContent = content.realDeadLine || "Non disponible";
            modalProjectDetails.style.display = "block";
        }

        modal.style.display = "flex";

        closeModal.addEventListener("click", () => {
            modal.style.display = "none";
            location.reload();
        });
    }
}
