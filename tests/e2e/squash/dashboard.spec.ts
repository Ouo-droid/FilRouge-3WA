import { test, expect } from '@playwright/test';
import { login, BASE_URL } from './auth-utils';

test.describe('Squash - Dashboard', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('Chargement du Dashboard', async ({ page }) => {
        await page.goto(`${BASE_URL}/`);
        // Vérifier un élément clé du dashboard, par exemple le logo ou un titre
        await expect(page.locator('.logo h4')).toBeVisible();
        await expect(page.locator('.logo h4')).toContainText('KENTEC');
    });

    test('Navigation vers les Projets depuis la Sidebar', async ({ page }) => {
        await page.goto(`${BASE_URL}/`);
        await page.click('.sidebar a[href="/projects"]');
        await page.waitForLoadState('networkidle');
        expect(page.url()).toContain('/projects');
    });

    test('Navigation vers les Tâches depuis la Sidebar', async ({ page }) => {
        await page.goto(`${BASE_URL}/`);
        await page.click('.sidebar a[href="/tasks"]');
        await page.waitForLoadState('networkidle');
        expect(page.url()).toContain('/tasks');
    });

    test('Navigation vers les Clients depuis la Sidebar', async ({ page }) => {
        await page.goto(`${BASE_URL}/`);
        await page.click('.sidebar a[href="/clients"]');
        await page.waitForLoadState('networkidle');
        expect(page.url()).toContain('/clients');
    });
});
