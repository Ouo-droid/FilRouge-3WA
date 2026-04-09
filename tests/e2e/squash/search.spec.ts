import { test, expect } from '@playwright/test';
import { login, BASE_URL } from './auth-utils';

test.describe('Squash - Recherche', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('Navigation vers la page de recherche', async ({ page }) => {
        await page.goto(`${BASE_URL}/search`);
        await expect(page.locator('.page-header h1')).toContainText(/Recherche/i);
    });

    test('Recherche interne', async ({ page }) => {
        await page.goto(`${BASE_URL}/search`);

        const searchInput = page.locator('#searchInput');
        if (await searchInput.isVisible()) {
            await searchInput.fill('Test');
            await page.click('button[type="submit"]');
            await page.waitForLoadState('networkidle');
            expect(page.url()).toContain('q=Test');
        }
    });
});
