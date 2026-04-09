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
        await page.fill('#create-name', taskName);
        await page.fill('#create-description', 'Description Tâche Squash');
        await page.fill('#create-effortRequired', '8');
        await page.selectOption('#create-type', { label: 'Tests' });
        await page.selectOption('#create-priority', 'high');

        page.on('dialog', dialog => dialog.accept());
        await page.click('.btn-save');
        await page.waitForTimeout(2000);

        await expect(page.locator('.task-card__name').filter({ hasText: taskName })).toBeVisible();

        // Édition
        // Les boutons sont dans un dropdown
        await page.locator('.task-card').filter({ hasText: taskName }).first().locator('.btn-menu').click();
        await page.locator('.edit-task-btn').filter({ hasText: /Modifier/i }).click();

        await page.waitForSelector('.form-overlay', { state: 'visible' });
        const updatedName = taskName + ' (MAJ)';
        await page.fill('#edit-name', updatedName);
        await page.click('.btn-save');
        await page.waitForLoadState('load');
        await page.waitForTimeout(2000); // Laisser le temps à l'alerte d'être acceptée et à la page de recharger
        await expect(page.locator('.task-card__name').filter({ hasText: updatedName }).first()).toBeVisible();

        // Suppression (si disponible - vérification du bouton)
        await page.locator('.task-card').filter({ hasText: updatedName }).first().locator('.btn-menu').click();
        const deleteBtn = page.locator('.delete-btn').filter({ hasText: /Supprimer/i });
        if (await deleteBtn.isVisible()) {
            page.once('dialog', dialog => dialog.accept());
            await deleteBtn.click();
            await page.waitForLoadState('load');
            await page.waitForTimeout(2000);
            await expect(page.locator('.task-card__name').filter({ hasText: updatedName })).not.toBeVisible();
        }
    });
});
