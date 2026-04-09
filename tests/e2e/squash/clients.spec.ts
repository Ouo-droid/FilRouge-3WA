import { test, expect } from '@playwright/test';
import { login, BASE_URL } from './auth-utils';

test.describe('Squash - Clients', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('Liste des clients', async ({ page }) => {
        await page.goto(`${BASE_URL}/clients`);
        await expect(page.locator('.page-header h1')).toContainText('Clients');
    });

    test('Création, édition et suppression d\'un client', async ({ page }) => {
        await page.goto(`${BASE_URL}/clients`);

        const companyName = `Client Squash ${Date.now()}`;
        const siret = Math.floor(10000000000000 + Math.random() * 90000000000000).toString();

        // Création
        await page.click('#create-client-btn');
        await page.waitForSelector('.form-overlay', { state: 'visible' });
        // Utilisation des sélecteurs vus dans clients.spec.ts
        await page.fill('#siret', siret);
        await page.fill('#companyName', companyName);
        await page.fill('#workfield', 'Squash Testing');
        await page.fill('#contactFirstname', 'Antoine');
        await page.fill('#contactLastname', 'Squash');
        await page.fill('#contactEmail', `test-${Date.now()}@example.com`);
        await page.fill('#streetNumber', '10');
        await page.fill('#streetName', 'Rue de Squash');
        await page.fill('#postCode', '75000');
        await page.fill('#city', 'Paris');

        page.on('dialog', dialog => dialog.accept());
        await page.click('.btn-save');
        await page.waitForTimeout(2000);

        await expect(page.locator('.client-row h3').filter({ hasText: companyName }).first()).toBeVisible();

        // Édition
        // Les boutons sont dans un dropdown
        await page.locator('.client-row').filter({ hasText: companyName }).first().locator('.btn-menu').click();
        await page.locator('.edit-client-btn').filter({ hasText: /Modifier/i }).click();

        await page.waitForSelector('.form-overlay', { state: 'visible' });
        const updatedName = companyName + ' Updated';
        await page.fill('#companyName', updatedName);
        await page.click('.btn-save');
        await page.waitForLoadState('load');
        await page.waitForTimeout(2000);
        await expect(page.locator('.client-row h3').filter({ hasText: updatedName }).first()).toBeVisible();

        // Suppression
        page.once('dialog', dialog => dialog.accept());
        await page.locator('.client-row').filter({ hasText: updatedName }).first().locator('.btn-menu').click();
        await page.locator('.delete-client-btn').filter({ hasText: /Supprimer/i }).click();

        await page.waitForLoadState('load');
        await page.waitForTimeout(2000);
        await expect(page.locator('.client-row h3').filter({ hasText: updatedName }).first()).not.toBeVisible();
    });
});
