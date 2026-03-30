import { getCsrfToken } from "./Api";
import { Client } from "../interfaces/ClientInterface";

export default class ClientService {

    static async loadClientsFromApi() {
        try {
            const response = await fetch("api/clients");
            if (!response.ok) throw new Error(`Erreur lors de la récupération des clients`);
            return await response.json();
        } catch (error) {
            console.error(`Erreur attrapée : ${error}`);
        }
    }

    static async loadClientFromApi(numSIRET: string): Promise<any> {
        try {
            const response = await fetch(`/api/client/${numSIRET}`, { method: "GET" });

            if (response.status === 200) {
                const data = await response.json();
                if (data) {
                    ClientService.showModalClient({
                        siret: data.client.siret,
                        companyName: data.client.companyName,
                        workfield: data.client.workfield,
                        contactFirstname: data.client.contactFirstname,
                        contactLastname: data.client.contactLastname,
                    });
                    return data;
                } else {
                    ClientService.showModalClient("Client non récupéré");
                }
            } else {
                ClientService.showModalClient("Erreur lors de la récupération du client");
            }
        } catch (error) {
            console.error("Erreur catch :", error);
            ClientService.showModalClient("Erreur lors de la récupération du client");
        }
    }

    static async addClientFromApi(client: Client): Promise<void> {
        if (!client.siret || !client.companyName) {
            return Promise.reject(new Error("Le numéro SIRET et le nom de l'entreprise sont obligatoires"));
        }

        if (client.siret.length !== 14 || !/^\d{14}$/.test(client.siret)) {
            return Promise.reject(new Error("Le numéro SIRET doit contenir exactement 14 chiffres"));
        }

        try {
            const response = await fetch("api/add/client", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-Token": getCsrfToken(),
                },
                body: JSON.stringify(client),
            });

            if (!response.ok) throw new Error(`Erreur HTTP ${response.status}`);

            const data = await response.json();

            if (data.success) {
                ClientService.showModalClient("Client ajouté avec succès !");
            } else {
                ClientService.showModalClient(`Erreur : ${data.message || data.error}`);
            }
        } catch (error) {
            console.error(error);
            ClientService.showModalClient("Erreur lors de la création du client");
        }
    }

    static async editClientFromApi(client: Client): Promise<any> {
        if (!client || !client.siret) {
            return Promise.reject(new Error("Client inconnu"));
        }

        try {
            const response = await fetch(`api/edit/client/${client.siret}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-Token": getCsrfToken(),
                },
                body: JSON.stringify(client),
            });

            if (response.status === 200) {
                const data = await response.json();
                if (data.success) {
                    ClientService.showModalClient("Client modifié avec succès !");
                    return data;
                } else {
                    ClientService.showModalClient(`Erreur : ${data.message || data.error}`);
                }
            } else {
                throw new Error(`Erreur HTTP ${response.status}`);
            }
        } catch (error) {
            console.error(`Erreur attrapée ${error}`);
            ClientService.showModalClient("Erreur lors de la modification du client");
        }
    }

    static async deleteClientFromApi(numSIRET: string): Promise<Object> {
        try {
            const response = await fetch(`/api/delete/client/${numSIRET}`, {
                method: "DELETE",
                headers: { "X-CSRF-Token": getCsrfToken() },
            });
            return await response.json();
        } catch (error) {
            console.error("Erreur lors de la suppression :", error);
            throw error;
        }
    }

    static showModalClient(
        content: string | { siret: string; companyName: string; workfield?: string; contactFirstname?: string; contactLastname?: string },
    ) {
        const modal = document.getElementById("clientModal") as HTMLElement;
        const modalMessage = document.getElementById("client-modal-message") as HTMLElement;
        const modalClientDetails = document.getElementById("client-modal-details") as HTMLElement;
        const modalNumSIRET = document.getElementById("client-modal-numSIRET") as HTMLElement;
        const modalCompanyName = document.getElementById("client-modal-company-name") as HTMLElement;
        const modalWorkfield = document.getElementById("client-modal-workfield") as HTMLElement;
        const modalContactFirstname = document.getElementById("client-modal-contact-firstname") as HTMLElement;
        const modalContactLastname = document.getElementById("client-modal-contact-lastname") as HTMLElement;
        const closeModal = document.getElementById("close-client-modal") as HTMLElement;

        if (!modal) return;

        modalMessage.style.display = "none";
        modalClientDetails.style.display = "none";

        if (typeof content === "string") {
            if (modalMessage) {
                modalMessage.textContent = content;
                modalMessage.style.display = "block";
            }
        } else {
            if (modalNumSIRET) modalNumSIRET.textContent = content.siret || "Non disponible";
            if (modalCompanyName) modalCompanyName.textContent = content.companyName || "Non disponible";
            if (modalWorkfield) modalWorkfield.textContent = content.workfield || "Non disponible";
            if (modalContactFirstname) modalContactFirstname.textContent = content.contactFirstname || "Non disponible";
            if (modalContactLastname) modalContactLastname.textContent = content.contactLastname || "Non disponible";
            if (modalClientDetails) modalClientDetails.style.display = "block";
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
