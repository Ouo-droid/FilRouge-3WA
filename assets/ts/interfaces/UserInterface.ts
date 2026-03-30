export interface User {
    id: string | number;
    firstname: string;
    lastname: string;
    email: string;
    password?: string;
    roleId?: string;
    jobtitle?: string;
    fieldofwork?: string;
    degree?: string[];
}