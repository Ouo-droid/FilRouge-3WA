import { test, expect } from '@playwright/test';
import { BASE_URL, TEST_USER } from './auth-utils';

test.describe('Squash - Authentification', () => {

    test('Accès à la page de login', async ({ page }) => {
        await page.goto(`${BASE_URL}/login`);
        await expect(page).toHaveTitle(/KenTec/i);
    });

    test('Échec de connexion avec mauvais identifiants', async ({ page }) => {
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', 'wrong@example.com');
        await page.fill('input[name="password"]', 'WrongPass123!');
        await page.click('button[type="submit"]');

        await page.waitForTimeout(1000);
        expect(page.url()).toContain('login');
    });

    test('Connexion réussie avec les identifiants Squash', async ({ page }) => {
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', TEST_USER.email);
        await page.fill('input[name="password"]', TEST_USER.password);
        await page.click('button[type="submit"]');

        await page.waitForLoadState('networkidle');
        // Devrait être redirigé vers le dashboard ou la home
        expect(page.url()).toMatch(/\/(dashboard|home|\/)?$/);
    });

    test('Déconnexion', async ({ page }) => {
        // Login d'abord
        await page.goto(`${BASE_URL}/login`);
        await page.fill('input[name="email"]', TEST_USER.email);
        await page.fill('input[name="password"]', TEST_USER.password);
        await page.click('button[type="submit"]');
        await page.waitForLoadState('networkidle');

        // Logout
        const logoutBtn = page.locator('a[href*="logout"], button:has-text("Se déconnecter")');
        if (await logoutBtn.isVisible()) {
            await logoutBtn.click();
            await page.waitForLoadState('networkidle');
            expect(page.url()).toContain('login');
        }
    });
});

export async function login(page) {
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="email"]', TEST_USER.email);
    await page.fill('input[name="password"]', TEST_USER.password);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
}
