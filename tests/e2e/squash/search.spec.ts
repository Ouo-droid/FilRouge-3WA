import { test, expect } from '@playwright/test';
import { login, BASE_URL } from './auth-utils';

test.describe('Squash - Recherche', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('Recherche globale', async ({ page }) => {
        await page.goto(`${BASE_URL}/`);

        const searchInput = page.locator('#search-input');
        if (await searchInput.isVisible()) {
            await searchInput.fill('Test');
            await page.keyboard.press('Enter');
            await page.waitForLoadState('networkidle');
            expect(page.url()).toContain('/search');
        }
    });

    test('Navigation vers la page de recherche', async ({ page }) => {
        await page.goto(`${BASE_URL}/search`);
        await expect(page.locator('h1')).toContainText(/Recherche/i);
    });
});
