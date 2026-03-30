// assets/ts/components/SearchBar.ts

import { searchService, SearchService } from '../services/SearchService';

interface SearchBarOptions {
    inputSelector: string;
    entitySelector?: string;
    resultsSelector: string;
    minChars?: number;
    debounceDelay?: number;
    onSelect?: (result: any) => void;
}

export class SearchBar {
    private input: HTMLInputElement;
    private entitySelect: HTMLSelectElement | null;
    private resultsContainer: HTMLElement;
    private options: Required<SearchBarOptions>;
    private searchService: SearchService;
    private timeout: any = null;

    constructor(options: SearchBarOptions) {
        this.options = {
            minChars: 2,
            debounceDelay: 300,
            onSelect: (result) => this.defaultOnSelect(result),
            entitySelector: '',
            ...options
        };

        this.input = document.querySelector(this.options.inputSelector) as HTMLInputElement;
        this.entitySelect = this.options.entitySelector
            ? document.querySelector(this.options.entitySelector) as HTMLSelectElement
            : null;
        this.resultsContainer = document.querySelector(this.options.resultsSelector) as HTMLElement;
        this.searchService = searchService;

        if (!this.input || !this.resultsContainer) {
            console.error('Les éléments de recherche sont introuvables');
            return;
        }

        this.init();
    }

    private init(): void {
        this.input.addEventListener('input', () => this.handleInput());

        if (this.entitySelect) {
            this.entitySelect.addEventListener('change', () => this.handleInput());
        }

        document.addEventListener('click', (e) => {
            if (!this.input.contains(e.target as Node) &&
                !this.resultsContainer.contains(e.target as Node)) {
                this.hideResults();
            }
        });

        this.input.addEventListener('keydown', (e) => this.handleKeydown(e));
    }

    private handleInput(): void {
        clearTimeout(this.timeout);
        const query = this.input.value.trim();

        if (query.length < this.options.minChars) {
            this.hideResults();
            return;
        }

        this.timeout = setTimeout(async () => {
            try {
                this.showLoading();
                const entity = this.entitySelect ? this.entitySelect.value : 'users';
                const results = await this.searchService.autocomplete(query, entity);
                this.displayResults(results, query);
            } catch (error) {
                console.error('Erreur de recherche:', error);
                this.hideResults();
            }
        }, this.options.debounceDelay);
    }

    private displayResults(results: any[], query: string): void {
        if (results.length === 0) {
            this.resultsContainer.innerHTML = '<div class="autocomplete-item text-muted">Aucun résultat</div>';
            this.showResults();
            return;
        }

        this.resultsContainer.innerHTML = results
            .map((result, index) => `
                <div class="autocomplete-item" data-index="${index}" data-id="${result.id}">
                    ${this.searchService.highlightText(result.label, query)}
                </div>
            `)
            .join('');

        this.resultsContainer.querySelectorAll('.autocomplete-item').forEach((item, index) => {
            item.addEventListener('click', () => {
                this.options.onSelect(results[index]);
                this.hideResults();
            });
        });

        this.showResults();
    }

    private handleKeydown(e: KeyboardEvent): void {
        const items = this.resultsContainer.querySelectorAll('.autocomplete-item');
        const activeItem = this.resultsContainer.querySelector('.autocomplete-item.active');
        let currentIndex = -1;

        if (activeItem) {
            currentIndex = Array.from(items).indexOf(activeItem);
        }

        switch (e.key) {
            case 'ArrowDown':
                e.preventDefault();
                this.setActiveItem(items, currentIndex < items.length - 1 ? currentIndex + 1 : 0);
                break;
            case 'ArrowUp':
                e.preventDefault();
                this.setActiveItem(items, currentIndex > 0 ? currentIndex - 1 : items.length - 1);
                break;
            case 'Enter':
                if (activeItem) {
                    e.preventDefault();
                    (activeItem as HTMLElement).click();
                }
                break;
            case 'Escape':
                this.hideResults();
                break;
        }
    }

    private setActiveItem(items: NodeListOf<Element>, index: number): void {
        items.forEach((item, i) => {
            if (i === index) {
                item.classList.add('active');
                (item as HTMLElement).scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('active');
            }
        });
    }

    private showLoading(): void {
        this.resultsContainer.innerHTML = '<div class="autocomplete-item">Chargement...</div>';
        this.showResults();
    }

    private showResults(): void {
        this.resultsContainer.style.display = 'block';
    }

    private hideResults(): void {
        this.resultsContainer.style.display = 'none';
    }

    private defaultOnSelect(result: any): void {
        const entity = this.entitySelect ? this.entitySelect.value : 'users';
        window.location.href = `/${entity}/${result.id}`;
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('searchInput')) {
        new SearchBar({
            inputSelector: '#searchInput',
            entitySelector: '#entitySelect',
            resultsSelector: '#autocompleteResults'
        });
    }
});
