// assets/ts/services/SearchService.ts

export class SearchService {
    /**
     * Effectue une requête d'autocomplétion
     */
    public async autocomplete(query: string, entity: string, debounceDelay: number = 300): Promise<any[]> {
        // En situation réelle, on pourrait implémenter un debounce ici si ce n'est pas fait dans le composant
        const response = await fetch(`/search/autocomplete?q=${encodeURIComponent(query)}&entity=${entity}`);
        
        if (!response.ok) {
            throw new Error('Erreur lors de la récupération des suggestions');
        }
        
        return await response.json();
    }

    /**
     * Surligne le texte recherché dans une chaîne
     */
    public highlightText(text: string, query: string): string {
        if (!query) return text;
        const regex = new RegExp(`(${query})`, 'gi');
        return text.replace(regex, '<span class="highlight">$1</span>');
    }
}

export const searchService = new SearchService();
