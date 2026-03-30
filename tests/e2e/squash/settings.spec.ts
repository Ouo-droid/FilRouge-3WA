import { test, expect } from '@playwright/test';
import { login, BASE_URL } from './auth-utils';

test.describe('Squash - Paramètres', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('Chargement de la page des paramètres', async ({ page }) => {
        await page.goto(`${BASE_URL}/settings`);
        await expect(page.locator('h1')).toContainText(/Paramètres|Mon Profil/i);
    });
});
