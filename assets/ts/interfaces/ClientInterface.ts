export interface Client {
    siret: string;
    companyName: string;
    workfield?: string;
    contactFirstname?: string;
    contactLastname?: string;
    contactEmail?: string;
    contactPhone?: string;
    address?: Address;
}

export interface Address {
    id?: string;
    streetNumber: number;
    streetLetter?: string;
    streetName: string;
    postCode: string;
    state?: string;
    city: string;
    country?: string;
}
