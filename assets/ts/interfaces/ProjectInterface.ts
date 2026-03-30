export interface Project {
    id?: number;
    name: string;
    description?: string;
    beginDate?: string;
    theoricalDeadLine?: string;
    realDeadLine?: string;
    userId?: number;
    numSIRET?: string;
    projectManagerId?: number;
}
