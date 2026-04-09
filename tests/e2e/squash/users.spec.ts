import { test, expect } from '@playwright/test';
import { login, BASE_URL } from './auth-utils';

test.describe('Squash - Utilisateurs', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('Liste des utilisateurs', async ({ page }) => {
        await page.goto(`${BASE_URL}/users`);
        await expect(page.locator('.page-header h1')).toContainText('Utilisateurs');
    });

    test('Création et suppression d\'un utilisateur', async ({ page }) => {
        await page.goto(`${BASE_URL}/users`);

        const timestamp = Date.now();
        const email = `squash${timestamp}@example.com`;
        const firstname = 'Antoine';
        const lastname = 'Squash';

        // Création
        await page.fill('#firstname', firstname);
        await page.fill('#lastname', lastname);
        await page.fill('#email', email);
        await page.fill('#password', 'Squash79@');

        page.on('dialog', dialog => dialog.accept());
        await page.click('#users-form-submit');
        await page.waitForTimeout(2000);

        await expect(page.locator('.user-card').filter({ hasText: `${firstname} ${lastname}` }).first()).toBeVisible();

        // Suppression
        page.once('dialog', dialog => dialog.accept());
        await page.locator('.user-card').filter({ hasText: `${firstname} ${lastname}` }).locator('.delete-user').click();
        await page.waitForTimeout(2000);
        await expect(page.locator('.user-card').filter({ hasText: email })).not.toBeVisible();
    });
});
