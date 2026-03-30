export function escapeHtml(text: string | null | undefined): string {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

export function formatDate(dateString: string): string {
    try {
        const date = new Date(dateString);
        return date.toLocaleDateString('fr-FR') + ' ' + date.toLocaleTimeString('fr-FR', {hour: '2-digit', minute: '2-digit'});
    } catch (error) {
        return dateString;
    }
}

export function formatDateForInput(dateString: string | null): string {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        return date.toISOString().split('T')[0];
    } catch {
        return '';
    }
}

export function formatDateTimeForInput(dateString: string | null): string {
    if (!dateString) return '';
    try {
        const date = new Date(dateString);
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');
        return `${year}-${month}-${day}T${hours}:${minutes}`;
    } catch {
        return '';
    }
}
