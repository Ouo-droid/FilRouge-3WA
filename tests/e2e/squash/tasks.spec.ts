import { test, expect } from '@playwright/test';
import { login, BASE_URL } from './auth-utils';

test.describe('Squash - Tâches', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('Liste des tâches', async ({ page }) => {
        await page.goto(`${BASE_URL}/tasks`);
        await expect(page.locator('.page-header h1')).toContainText('Tâches');
    });

    test('Création, édition et suppression d\'une tâche', async ({ page }) => {
        await page.goto(`${BASE_URL}/tasks`);

        const taskName = `Tâche Squash ${Date.now()}`;

        // Création
        await page.click('#create-task-btn');
        await page.waitForSelector('.form-overlay', { state: 'visible' });
        await page.fill('input[name="name"]', taskName);
        await page.fill('textarea[name="description"]', 'Description Tâche Squash');
        await page.fill('input[name="type"]', 'Testing');
        await page.selectOption('select[name="priority"]', 'high');

        page.on('dialog', dialog => dialog.accept());
        await page.click('.btn-save');
        await page.waitForTimeout(2000);

        await expect(page.locator('.item-card h3').filter({ hasText: taskName })).toBeVisible();

        // Édition
        await page.locator('.item-card').filter({ hasText: taskName }).first().locator('.btn-warning').click();
        await page.waitForSelector('.form-overlay', { state: 'visible' });
        const updatedName = taskName + ' (MAJ)';
        await page.fill('input[name="name"]', updatedName);
        await page.click('.btn-save');
        await page.waitForLoadState('load');
        await page.waitForTimeout(2000); // Laisser le temps à l'alerte d'être acceptée et à la page de recharger
        await expect(page.locator('.item-card h3').filter({ hasText: updatedName }).first()).toBeVisible();

        // Suppression (si disponible - vérification du bouton)
        const deleteBtn = page.locator('.item-card').filter({ hasText: updatedName }).locator('.btn-danger');
        if (await deleteBtn.isVisible()) {
            page.once('dialog', dialog => dialog.accept());
            await page.locator('.item-card').filter({ hasText: updatedName }).first().locator('.btn-danger').click();
            await page.waitForLoadState('load');
            await page.waitForTimeout(2000);
            await expect(page.locator('.item-card h3').filter({ hasText: updatedName })).not.toBeVisible();
        }
    });
});
