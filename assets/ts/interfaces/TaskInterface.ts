export interface Task {
    id?: number;
    name: string;
    description?: string;
    type?: string;
    format?: string;
    priority?: string;
    difficulty?: string;
    effortRequired?: number;
    effortMade?: number;
    beginDate?: string;
    theoricalEndDate?: string;
    realEndDate?: string;
    developerId?: number;
    projectId?: number;
    stateId?: number;
}

