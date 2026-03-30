import { getCsrfToken } from "./Api";
import { Task } from "../interfaces/TaskInterface";

export default class TaskService {

    static async loadTasksFromApi() {
        try {
            const response = await fetch("api/tasks");
            if (!response.ok) throw new Error(`Erreur lors de la récupération des tâches`);
            return await response.json();
        } catch (error) {
            console.error(`Erreur attrapée : ${error}`);
        }
    }

    static async loadTaskFromApi(taskId: string): Promise<any> {
        try {
            const response = await fetch(`/api/task/${taskId}`, { method: "GET" });

            if (response.status === 200) {
                const data = await response.json();
                if (data) {
                    TaskService.showModalTask({
                        name: data.task.name,
                        description: data.task.description,
                        type: data.task.type,
                        format: data.task.format,
                        priority: data.task.priority,
                        theoricalEndDate: data.task.theoricalEndDate,
                        realEndDate: data.task.realEndDate,
                    });
                    return data;
                } else {
                    TaskService.showModalTask("Tâche non récupérée");
                }
            } else {
                TaskService.showModalTask("Erreur lors de la récupération de la tâche");
            }
        } catch (error) {
            console.error("Erreur catch :", error);
            TaskService.showModalTask("Erreur lors de la récupération de la tâche");
        }
    }

    static async addTaskFromApi(task: Omit<Task, "id">): Promise<void> {
        if (!task.name) {
            return Promise.reject(new Error("Le nom de la tâche est obligatoire"));
        }

        try {
            const response = await fetch("api/add/task", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-Token": getCsrfToken(),
                },
                body: JSON.stringify(task),
            });

            if (!response.ok) throw new Error(`Erreur HTTP ${response.status}`);

            const data = await response.json();

            if (data.success) {
                TaskService.showModalTask("Tâche ajoutée avec succès !");
            } else {
                TaskService.showModalTask(`Erreur : ${data.message}`);
            }
        } catch (error) {
            console.error(error);
            TaskService.showModalTask("Erreur lors de la création de la tâche");
        }
    }

    static async editTaskFromApi(task: Task): Promise<any> {
        if (!task || !task.id) {
            return Promise.reject(new Error("Tâche inconnue"));
        }

        try {
            const response = await fetch(`api/edit/task/${task.id}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-Token": getCsrfToken(),
                },
                body: JSON.stringify(task),
            });

            if (response.status === 200) {
                const data = await response.json();
                if (data.success) {
                    TaskService.showModalTask("Tâche modifiée avec succès !");
                    return data;
                } else {
                    TaskService.showModalTask(`Erreur : ${data.message}`);
                }
            } else {
                throw new Error(`Erreur HTTP ${response.status}`);
            }
        } catch (error) {
            console.error(`Erreur attrapée ${error}`);
            TaskService.showModalTask("Erreur lors de la modification de la tâche");
        }
    }

    static async deleteTaskFromApi(taskId: string): Promise<Object> {
        try {
            const response = await fetch(`/api/delete/task/${taskId}`, {
                method: "DELETE",
                headers: { "X-CSRF-Token": getCsrfToken() },
            });
            return await response.json();
        } catch (error) {
            console.error("Erreur lors de la suppression :", error);
            throw error;
        }
    }

    static showModalTask(
        content: string | { name: string; description?: string; type?: string; format?: string; priority?: string; theoricalEndDate?: string; realEndDate?: string },
    ) {
        const modal = document.getElementById("taskModal") as HTMLElement;
        const modalMessage = document.getElementById("task-modal-message") as HTMLElement;
        const modalTaskDetails = document.getElementById("task-modal-details") as HTMLElement;
        const modalName = document.getElementById("task-modal-name") as HTMLElement;
        const modalDescription = document.getElementById("task-modal-description") as HTMLElement;
        const modalType = document.getElementById("task-modal-type") as HTMLElement;
        const modalFormat = document.getElementById("task-modal-format") as HTMLElement;
        const modalPriority = document.getElementById("task-modal-priority") as HTMLElement;
        const modalTheoricalEndDate = document.getElementById("task-modal-theoretical-end-date") as HTMLElement;
        const modalRealEndDate = document.getElementById("task-modal-real-end-date") as HTMLElement;
        const closeModal = document.getElementById("close-task-modal") as HTMLElement;

        if (!modal) return;

        modalMessage.style.display = "none";
        modalTaskDetails.style.display = "none";

        if (typeof content === "string") {
            if (modalMessage) {
                modalMessage.textContent = content;
                modalMessage.style.display = "block";
            }
        } else {
            if (modalName) modalName.textContent = content.name || "Non disponible";
            if (modalDescription) modalDescription.textContent = content.description || "Non disponible";
            if (modalType) modalType.textContent = content.type || "Non disponible";
            if (modalFormat) modalFormat.textContent = content.format || "Non disponible";
            if (modalPriority) modalPriority.textContent = content.priority || "Non disponible";
            if (modalTheoricalEndDate) modalTheoricalEndDate.textContent = content.theoricalEndDate || "Non disponible";
            if (modalRealEndDate) modalRealEndDate.textContent = content.realEndDate || "Non disponible";
            if (modalTaskDetails) modalTaskDetails.style.display = "block";
        }

        modal.style.display = "flex";

        if (closeModal) {
            closeModal.addEventListener("click", () => {
                modal.style.display = "none";
                location.reload();
            });
        }
    }
}
