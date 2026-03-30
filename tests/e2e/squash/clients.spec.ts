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
        await page.fill('#create-siret', siret);
        await page.fill('#create-company-name', companyName);
        await page.fill('#create-workfield', 'Squash Testing');
        await page.fill('#create-contact-firstname', 'Antoine');
        await page.fill('#create-contact-lastname', 'Squash');

        page.on('dialog', dialog => dialog.accept());
        await page.click('.btn-save');
        await page.waitForTimeout(2000);

        await expect(page.locator('.item-card h3').filter({ hasText: companyName }).first()).toBeVisible();

        // Édition
        await page.locator('.item-card').filter({ hasText: companyName }).first().locator('.btn-warning').click();
        await page.waitForSelector('.form-overlay', { state: 'visible' });
        const updatedName = companyName + ' Updated';
        await page.fill('input[name="companyName"]', updatedName);
        await page.click('.btn-save');
        await page.waitForLoadState('load');
        await page.waitForTimeout(2000);
        await expect(page.locator('.item-card h3').filter({ hasText: updatedName }).first()).toBeVisible();

        // Suppression
        page.once('dialog', dialog => dialog.accept());
        await page.locator('.item-card').filter({ hasText: updatedName }).first().locator('.btn-danger').click();
        await page.waitForLoadState('load');
        await page.waitForTimeout(2000);
        await expect(page.locator('.item-card h3').filter({ hasText: updatedName }).first()).not.toBeVisible();
    });
});
