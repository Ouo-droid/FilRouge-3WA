import { test, expect } from '@playwright/test';
import { login, BASE_URL } from './auth-utils';

test.describe('Squash - Projets', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('Liste des projets', async ({ page }) => {
        await page.goto(`${BASE_URL}/projects`);
        await expect(page.locator('.page-header h1')).toContainText('Projets');
    });

    test('Création, édition et suppression d\'un projet', async ({ page }) => {
        await page.goto(`${BASE_URL}/projects`);

        const projectName = `Projet Squash ${Date.now()}`;

        // Création
        await page.click('#create-project-btn');
        await page.waitForSelector('.form-overlay', { state: 'visible' });
        await page.fill('#create-name', projectName);
        await page.fill('#create-description', 'Description Squash');
        await page.fill('#create-begin-date', '2026-01-01');
        await page.fill('#create-theoretical-deadline', '2026-12-31');

        page.on('dialog', dialog => dialog.accept());
        await page.click('.btn-save');
        await page.waitForTimeout(2000);

        await expect(page.locator('.project-card__name').filter({ hasText: projectName })).toBeVisible();

        // Édition
        await page.locator('.project-card').filter({ hasText: projectName }).locator('.edit-project-btn').click();
        await page.waitForSelector('.form-overlay', { state: 'visible' });
        const updatedName = projectName + ' (MAJ)';
        await page.fill('#edit-name', updatedName);
        await page.click('.btn-save');
        await page.waitForTimeout(2000);
        await expect(page.locator('.project-card__name').filter({ hasText: updatedName })).toBeVisible();

        // Suppression
        page.once('dialog', dialog => dialog.accept());
        await page.locator('.project-card').filter({ hasText: updatedName }).locator('.delete-project-btn').click();
        await page.waitForTimeout(2000);
        await expect(page.locator('.project-card__name').filter({ hasText: updatedName })).not.toBeVisible();
    });
});
