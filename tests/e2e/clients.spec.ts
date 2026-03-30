import { test, expect } from '@playwright/test';

// Configuration de base
const BASE_URL = 'http://localhost:8000';

// Données de test
const TEST_USER = {
    email: 'test@example.com',
    password: 'Test123!',
};

const TEST_CLIENT = {
    numSIRET: '12345678901234',
    companyName: 'Entreprise Test E2E',
    workfield: 'Informatique',
    contactFirstname: 'Alice',
    contactLastname: 'Format'
};

// ========================================
// HELPER: Connexion automatique
// ========================================

async function login(page) {
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="email"]', TEST_USER.email);
    await page.fill('input[name="password"]', TEST_USER.password);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
}

// ========================================
// TESTS CRUD CLIENTS
// ========================================

test.describe('Gestion des Clients', () => {

    test.beforeEach(async ({ page }) => {
        await login(page);
    });

    test('Doit afficher la liste des clients', async ({ page }) => {
        await page.goto(`${BASE_URL}/clients`);

        // Vérifier le titre de la page
        await expect(page.locator('.page-header h1')).toContainText('Gestion des Clients');

        // Vérifier la présence de la grille de clients ou du message "aucun client"
        const hasClients = await page.locator('#client-list').isVisible().catch(() => false);
        const noClients = await page.locator('.no-clients').isVisible().catch(() => false);

        expect(hasClients || noClients).toBeTruthy();
    });

    test('Doit créer un nouveau client', async ({ page }) => {
        await page.goto(`${BASE_URL}/clients`);

        // Cliquer sur le bouton "Nouveau Client"
        await page.click('#create-client-btn');

        // Attendre que le formulaire apparaisse (form-overlay)
        await page.waitForSelector('.form-overlay', { state: 'visible' });

        // Générer un SIRET unique pour éviter les conflits
        const uniqueSiret = Math.floor(10000000000000 + Math.random() * 90000000000000).toString();

        // Remplir le formulaire
        await page.fill('#create-siret', uniqueSiret);
        await page.fill('#create-company-name', TEST_CLIENT.companyName);
        await page.fill('#create-workfield', TEST_CLIENT.workfield);
        await page.fill('#create-contact-firstname', TEST_CLIENT.contactFirstname);
        await page.fill('#create-contact-lastname', TEST_CLIENT.contactLastname);

        // Gérer l'alerte de confirmation browser
        page.on('dialog', dialog => dialog.accept());

        // Soumettre le formulaire
        await page.click('.btn-save');

        // Attendre que la page se recharge et vérifier la présence du client
        await page.waitForLoadState('networkidle');
        await expect(page.locator('.item-card h3').filter({ hasText: TEST_CLIENT.companyName }).first()).toBeVisible();
    });

    test('Doit afficher les détails d\'un client', async ({ page }) => {
        await page.goto(`${BASE_URL}/clients`);

        // S'assurer qu'il y a au moins un client, sinon en créer un ou sauter
        const clientCount = await page.locator('.item-card').count();
        if (clientCount === 0) {
            test.skip();
            return;
        }

        // Cliquer sur "Voir détails" du premier client
        const viewBtn = page.locator('.btn-info').first();
        await viewBtn.click();

        // Vérifier que la modal s'ouvre
        await page.waitForSelector('#clientModal', { state: 'visible' });
        await expect(page.locator('#clientModal .modal-header h3')).toContainText('Détails du Client');

        // Vérifier que les détails sont chargés
        await expect(page.locator('#client-modal-numSIRET')).not.toBeEmpty();
        await expect(page.locator('#client-modal-company-name')).not.toBeEmpty();
    });

    test('Doit modifier un client existant', async ({ page }) => {
        await page.goto(`${BASE_URL}/clients`);

        const clientCount = await page.locator('.item-card').count();
        if (clientCount === 0) {
            test.skip();
            return;
        }

        // Cliquer sur "Modifier" du premier client
        const editBtn = page.locator('.btn-warning').first();
        await editBtn.click();

        // Attendre que le formulaire d'édition apparaisse
        await page.waitForSelector('.form-overlay', { state: 'visible' });

        // Modifier le nom de l'entreprise
        const updatedName = `${TEST_CLIENT.companyName} MODIFIÉ`;
        await page.fill('#edit-company-name', updatedName);

        // Gérer l'alerte
        page.on('dialog', dialog => dialog.accept());

        // Sauvegarder
        await page.click('.btn-save');

        // Vérifier le changement
        await page.waitForLoadState('networkidle');
        await expect(page.locator('.item-card h3').filter({ hasText: updatedName }).first()).toBeVisible();
    });

    test('Doit supprimer un client', async ({ page }) => {
        await page.goto(`${BASE_URL}/clients`);

        const clientCards = page.locator('.item-card');
        const countBefore = await clientCards.count();

        if (countBefore === 0) {
            test.skip();
            return;
        }

        // Gérer la confirmation
        page.once('dialog', dialog => dialog.accept());

        // Cliquer sur supprimer du dernier client
        await page.locator('.btn-danger').last().click();

        // Attendre le rechargement
        await page.waitForLoadState('networkidle');

        // Vérifier que le nombre a diminué
        const countAfter = await clientCards.count();
        expect(countAfter).toBeLessThan(countBefore);
    });

    test('Doit valider le format SIRET', async ({ page }) => {
        await page.goto(`${BASE_URL}/clients`);
        await page.click('#create-client-btn');
        await page.waitForSelector('.form-overlay', { state: 'visible' });

        // Entrer un SIRET invalide (trop court)
        await page.fill('#create-siret', '123');
        await page.fill('#create-company-name', 'Test Erreur');

        // Tenter de soumettre
        await page.click('.btn-save');

        // Vérifier que le champ est marqué comme invalide ou que le pattern est présent
        const siretInput = page.locator('#create-siret');
        const isValid = await siretInput.evaluate((node) => node.checkValidity());
        expect(isValid).toBeFalsy();
    });
});
