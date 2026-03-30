import { test, expect } from '@playwright/test';

// Configuration de base
const BASE_URL = 'http://localhost:8000';

// Données de test
const TEST_USER = {
  email: 'test@example.com',
  password: 'Test123!',
  firstname: 'Test',
  lastname: 'User'
};

const TEST_PROJECT = {
  name: 'Projet Test E2E',
  description: 'Description du projet de test',
  beginDate: '2025-01-01',
  theoricalDeadLine: '2025-12-31'
};

const TEST_TASK = {
  name: 'Tâche Test E2E',
  description: 'Description de la tâche de test',
  type: 'Development',
  priority: 'high'
};

const TEST_CLIENT = {
  numSIRET: '12345678901234',
  companyName: 'Entreprise Test',
  workfield: 'IT',
  contactFirstname: 'Jean',
  contactLastname: 'Dupont'
};

// ========================================
// TESTS D'AUTHENTIFICATION
// ========================================

test.describe('Authentification', () => {

  test('Doit afficher la page de connexion', async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);
    await expect(page).toHaveTitle(/KENTEC - Système de Gestion/i);
    await expect(page.locator('input[name="email"]')).toBeVisible();
    await expect(page.locator('input[name="password"]')).toBeVisible();
    await expect(page.locator('button[type="submit"]')).toBeVisible();
  });

  test('Doit afficher une erreur avec des identifiants invalides', async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="email"]', 'invalid@example.com');
    await page.fill('input[name="password"]', 'wrongpassword');
    await page.click('button[type="submit"]');

    // Attendre un message d'erreur ou une redirection vers login
    await page.waitForTimeout(1000);
    const url = page.url();
    expect(url).toContain('login');
  });

  test('Doit se connecter avec des identifiants valides', async ({ page }) => {
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="email"]', TEST_USER.email);
    await page.fill('input[name="password"]', TEST_USER.password);
    await page.click('button[type="submit"]');

    // Vérifier la redirection vers le dashboard
    await page.waitForLoadState('networkidle');
    const url = page.url();
    expect(url).toMatch(/\/(dashboard|home|\/)?$/);
  });

  test('Doit se déconnecter', async ({ page }) => {
    // Connexion préalable
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="email"]', TEST_USER.email);
    await page.fill('input[name="password"]', TEST_USER.password);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');

    // Déconnexion - Chercher le bouton "Se déconnecter"
    const logoutBtn = page.locator('button:has-text("Se déconnecter"), a[href*="logout"]');
    await logoutBtn.click();

    // Vérifier la redirection vers login
    await page.waitForLoadState('networkidle');
    const url = page.url();
    expect(url).toContain('login');
  });
});

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
// TESTS CRUD PROJETS
// ========================================

test.describe('Gestion des Projets', () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('Doit afficher la liste des projets', async ({ page }) => {
    await page.goto(`${BASE_URL}/projects`);

    // Vérifier le titre de la page
    await expect(page.locator('.page-header h1')).toContainText('Projets');

    // Vérifier la présence de la grille de projets ou du message "aucun projet"
    const hasProjects = await page.locator('.project-grid').isVisible().catch(() => false);
    const noProjects = await page.locator('.no-projects').isVisible().catch(() => false);

    expect(hasProjects || noProjects).toBeTruthy();
  });

  test('Doit créer un nouveau projet', async ({ page }) => {
    await page.goto(`${BASE_URL}/projects`);

    // Cliquer sur le bouton "Nouveau Projet"
    await page.click('#create-project-btn');

    // Attendre que le formulaire modal apparaisse
    await page.waitForSelector('.form-overlay', { state: 'visible' });

    // Remplir le formulaire
    await page.fill('input[name="name"]', TEST_PROJECT.name);
    await page.fill('textarea[name="description"]', TEST_PROJECT.description);
    await page.fill('input[name="beginDate"]', TEST_PROJECT.beginDate);
    await page.fill('input[name="theoricalDeadLine"]', TEST_PROJECT.theoricalDeadLine);

    // Soumettre le formulaire
    await page.click('.btn-save');

    // Attendre la confirmation et le rechargement
    page.on('dialog', dialog => dialog.accept());
    await page.waitForTimeout(2000);

    // Vérifier que le projet apparaît dans la liste
    await expect(page.locator('.item-card h3').filter({ hasText: TEST_PROJECT.name })).toBeVisible();
  });

  test('Doit afficher les détails d\'un projet', async ({ page }) => {
    await page.goto(`${BASE_URL}/projects`);

    // Cliquer sur "Voir détails" du premier projet
    const viewBtn = page.locator('.btn-info').first();
    await viewBtn.click();

    // Vérifier que la modal s'ouvre
    await page.waitForSelector('#projectModal', { state: 'visible' });
    await expect(page.locator('#projectModal .modal-header h3')).toContainText('Détails du Projet');
  });

  test('Doit modifier un projet existant', async ({ page }) => {
    await page.goto(`${BASE_URL}/projects`);

    // Cliquer sur "Modifier" du premier projet
    const editBtn = page.locator('.btn-warning').first();
    await editBtn.click();

    // Attendre que le formulaire d'édition apparaisse
    await page.waitForSelector('.form-overlay', { state: 'visible' });

    // Modifier le nom
    const newName = `${TEST_PROJECT.name} - Modifié ${Date.now()}`;
    await page.fill('input[name="name"]', newName);

    // Sauvegarder
    await page.click('.btn-save');

    // Accepter l'alerte de confirmation
    page.on('dialog', dialog => dialog.accept());
    await page.waitForTimeout(2000);

    // Vérifier que le nouveau nom apparaît
    await expect(page.locator('.item-card h3').filter({ hasText: 'Modifié' })).toBeVisible();
  });

  test('Doit supprimer un projet', async ({ page }) => {
    await page.goto(`${BASE_URL}/projects`);

    // Compter les projets avant suppression
    const projectCards = page.locator('.item-card');
    const countBefore = await projectCards.count();

    if (countBefore === 0) {
      test.skip();
      return;
    }

    // Gérer la confirmation
    page.on('dialog', dialog => dialog.accept());

    // Cliquer sur supprimer du dernier projet
    await page.locator('.btn-danger').last().click();

    // Attendre le rechargement
    await page.waitForTimeout(2000);

    // Vérifier que le nombre a diminué ou qu'il n'y a plus de projets
    const countAfter = await projectCards.count().catch(() => 0);
    expect(countAfter).toBeLessThan(countBefore);
  });
});

// ========================================
// TESTS CRUD UTILISATEURS
// ========================================

test.describe('Gestion des Utilisateurs', () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('Doit afficher la liste des utilisateurs', async ({ page }) => {
    await page.goto(`${BASE_URL}/users`);
    await expect(page.locator('.page-header h1')).toContainText('Utilisateurs');

    // Vérifier la présence du formulaire d'ajout
    await expect(page.locator('#form-add-user')).toBeVisible();
  });

  test('Doit créer un nouvel utilisateur', async ({ page }) => {
    await page.goto(`${BASE_URL}/users`);

    const timestamp = Date.now();

    // Remplir le formulaire d'ajout
    await page.fill('#firstname', 'Nouveau');
    await page.fill('#lastname', 'Utilisateur');
    await page.fill('#email', `user${timestamp}@example.com`);
    await page.fill('#password', 'Password123!');

    // Soumettre
    await page.click('#form-add-user button[type="submit"]');

    // Attendre la confirmation
    page.on('dialog', dialog => dialog.accept());
    await page.waitForTimeout(2000);

    // Vérifier que l'utilisateur apparaît
    await expect(page.locator('.item-card').filter({ hasText: 'Nouveau Utilisateur' })).toBeVisible();
  });

  test('Doit afficher les détails d\'un utilisateur', async ({ page }) => {
    await page.goto(`${BASE_URL}/users`);

    // Cliquer sur "Voir" du premier utilisateur
    const viewBtn = page.locator('.btn-primary-action').first();
    await viewBtn.click();

    // Vérifier que la modal s'ouvre
    await page.waitForSelector('#notificationModal', { state: 'visible' });
    await expect(page.locator('#modal-user-details')).toBeVisible();
  });

  test('Doit supprimer un utilisateur', async ({ page }) => {
    await page.goto(`${BASE_URL}/users`);

    const userCards = page.locator('.item-card');
    const countBefore = await userCards.count();

    if (countBefore === 0) {
      test.skip();
      return;
    }

    // Gérer la confirmation
    page.on('dialog', dialog => dialog.accept());

    // Supprimer le dernier utilisateur
    await page.locator('.item-card .btn-danger').last().click();

    await page.waitForTimeout(2000);

    const countAfter = await userCards.count().catch(() => 0);
    expect(countAfter).toBeLessThan(countBefore);
  });
});

// ========================================
// TESTS CRUD TÂCHES
// ========================================

test.describe('Gestion des Tâches', () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('Doit afficher la liste des tâches', async ({ page }) => {
    await page.goto(`${BASE_URL}/tasks`);
    await expect(page.locator('.page-header h1')).toContainText('Tâches');
  });

  test('Doit créer une nouvelle tâche', async ({ page }) => {
    await page.goto(`${BASE_URL}/tasks`);

    // Cliquer sur "Nouvelle Tâche"
    await page.click('#create-task-btn');

    // Attendre le formulaire
    await page.waitForSelector('.form-overlay', { state: 'visible' });

    // Remplir le formulaire
    await page.fill('input[name="name"]', TEST_TASK.name);
    await page.fill('textarea[name="description"]', TEST_TASK.description);
    await page.fill('input[name="type"]', TEST_TASK.type);
    await page.selectOption('select[name="priority"]', TEST_TASK.priority);

    // Soumettre
    await page.click('.btn-save');

    page.on('dialog', dialog => dialog.accept());
    await page.waitForTimeout(2000);

    // Vérifier la création
    await expect(page.locator('.item-card h3').filter({ hasText: TEST_TASK.name })).toBeVisible();
  });

  test('Doit modifier une tâche', async ({ page }) => {
    await page.goto(`${BASE_URL}/tasks`);

    // Cliquer sur modifier
    await page.locator('.btn-warning').first().click();

    await page.waitForSelector('.form-overlay', { state: 'visible' });

    // Modifier le nom
    const newName = `${TEST_TASK.name} - Modifiée`;
    await page.fill('input[name="name"]', newName);

    await page.click('.btn-save');

    page.on('dialog', dialog => dialog.accept());
    await page.waitForTimeout(2000);
  });
});

// ========================================
// TESTS CRUD CLIENTS
// ========================================

test.describe('Gestion des Clients', () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('Doit afficher la liste des clients', async ({ page }) => {
    await page.goto(`${BASE_URL}/clients`);
    await expect(page.locator('.page-header h1')).toContainText('Clients');
  });

  test('Doit créer un nouveau client', async ({ page }) => {
    await page.goto(`${BASE_URL}/clients`);

    // Cliquer sur "Nouveau Client"
    await page.click('#create-client-btn');

    await page.waitForSelector('.form-overlay', { state: 'visible' });

    // Générer un SIRET unique
    const timestamp = Date.now().toString().slice(-9);
    const siret = `12345${timestamp}`;

    // Remplir le formulaire
    await page.fill('input[name="numSIRET"]', siret);
    await page.fill('input[name="companyName"]', TEST_CLIENT.companyName);
    await page.fill('input[name="workfield"]', TEST_CLIENT.workfield);
    await page.fill('input[name="contactFirstname"]', TEST_CLIENT.contactFirstname);
    await page.fill('input[name="contactLastname"]', TEST_CLIENT.contactLastname);

    // Soumettre
    await page.click('.btn-save');

    page.on('dialog', dialog => dialog.accept());
    await page.waitForTimeout(2000);

    // Vérifier la création
    await expect(page.locator('.item-card h3').filter({ hasText: TEST_CLIENT.companyName })).toBeVisible();
  });

  test('Doit afficher les détails d\'un client', async ({ page }) => {
    await page.goto(`${BASE_URL}/clients`);

    // Cliquer sur "Voir détails"
    await page.locator('.btn-info').first().click();

    // Vérifier la modal
    await page.waitForSelector('#clientModal', { state: 'visible' });
    await expect(page.locator('#clientModal .modal-header h3')).toContainText('Détails du Client');
  });
});

// ========================================
// TESTS API
// ========================================

test.describe('Tests API', () => {

  test('GET /api/users doit retourner la liste des utilisateurs', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/users`);
    expect(response.ok()).toBeTruthy();
    const data = await response.json();
    expect(data).toHaveProperty('users');
    expect(Array.isArray(data.users)).toBeTruthy();
  });

  test('GET /api/projects doit retourner la liste des projets', async ({ request }) => {
    const response = await request.get(`${BASE_URL}/api/projects`);
    expect(response.ok()).toBeTruthy();
    const data = await response.json();
    expect(data).toHaveProperty('projects');
    expect(Array.isArray(data.projects)).toBeTruthy();
  });

  test('POST /api/add/project doit créer un projet', async ({ request }) => {
    const response = await request.post(`${BASE_URL}/api/add/project`, {
      data: {
        name: 'Projet API Test',
        description: 'Test via API',
        beginDate: '2025-01-01',
        theoricalDeadLine: '2025-12-31'
      }
    });
    expect(response.ok()).toBeTruthy();
    const data = await response.json();
    expect(data.success).toBeTruthy();
  });
});

// ========================================
// TESTS DE NAVIGATION
// ========================================

test.describe('Navigation', () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('Doit naviguer entre les différentes sections', async ({ page }) => {
    // Dashboard
    await page.goto(`${BASE_URL}/`);
    await expect(page.locator('.logo h4')).toContainText('KENTEC');

    // Projets
    await page.click('.sidebar a[href="/projects"]');
    await page.waitForLoadState('networkidle');
    expect(page.url()).toContain('/projects');

    // Tâches
    await page.click('.sidebar a[href="/tasks"]');
    await page.waitForLoadState('networkidle');
    expect(page.url()).toContain('/tasks');

    // Clients
    await page.click('.sidebar a[href="/clients"]');
    await page.waitForLoadState('networkidle');
    expect(page.url()).toContain('/clients');
  });

  test('Les liens de la sidebar doivent être actifs au bon endroit', async ({ page }) => {
    await page.goto(`${BASE_URL}/projects`);

    // Vérifier que le lien "Projets" a la classe active
    const projectLink = page.locator('.sidebar a[href="/projects"]');
    await expect(projectLink).toHaveClass(/active/);
  });
});

// ========================================
// TESTS DE VALIDATION
// ========================================

test.describe('Validation des formulaires', () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('Doit valider les champs requis pour un projet', async ({ page }) => {
    await page.goto(`${BASE_URL}/projects`);
    await page.click('#create-project-btn');

    await page.waitForSelector('.form-overlay', { state: 'visible' });

    // Essayer de soumettre sans remplir
    await page.click('.btn-save');

    // Le formulaire ne devrait pas se soumettre (validation HTML5)
    const nameInput = page.locator('input[name="name"]');
    await expect(nameInput).toHaveAttribute('required', '');
  });

  test('Doit valider le format SIRET (14 chiffres)', async ({ page }) => {
    await page.goto(`${BASE_URL}/clients`);
    await page.click('#create-client-btn');

    await page.waitForSelector('.form-overlay', { state: 'visible' });

    // Tenter d'entrer un SIRET invalide
    await page.fill('input[name="numSIRET"]', '123'); // Trop court
    await page.fill('input[name="companyName"]', 'Test');

    // Le pattern devrait empêcher la soumission
    const siretInput = page.locator('input[name="numSIRET"]');
    await expect(siretInput).toHaveAttribute('pattern', '[0-9]{14}');
  });
});

// ========================================
// TESTS DE PERFORMANCE
// ========================================

test.describe('Performance', () => {

  test('La page d\'accueil doit se charger rapidement', async ({ page }) => {
    const startTime = Date.now();
    await page.goto(BASE_URL);
    await page.waitForLoadState('networkidle');
    const loadTime = Date.now() - startTime;

    expect(loadTime).toBeLessThan(5000); // 5 secondes max
  });

  test('La liste des projets doit se charger rapidement', async ({ page }) => {
    await login(page);

    const startTime = Date.now();
    await page.goto(`${BASE_URL}/projects`);
    await page.waitForLoadState('networkidle');
    const loadTime = Date.now() - startTime;

    expect(loadTime).toBeLessThan(5000);
  });
});

// ========================================
// TESTS DE RESPONSIVITÉ
// ========================================

test.describe('Responsive Design', () => {

  test.beforeEach(async ({ page }) => {
    await login(page);
  });

  test('Doit s\'afficher correctement sur mobile', async ({ page }) => {
    await page.setViewportSize({ width: 375, height: 667 }); // iPhone SE

    await page.goto(`${BASE_URL}/projects`);

    // Vérifier que la sidebar est adaptée
    await expect(page.locator('.sidebar')).toBeVisible();

    // Vérifier que les cartes sont en colonne unique
    const projectCards = page.locator('.item-card');
    if (await projectCards.count() > 0) {
      const firstCard = projectCards.first();
      const box = await firstCard.boundingBox();
      expect(box.width).toBeGreaterThan(300); // Devrait prendre toute la largeur
    }
  });

  test('Doit s\'afficher correctement sur tablette', async ({ page }) => {
    await page.setViewportSize({ width: 768, height: 1024 }); // iPad

    await page.goto(`${BASE_URL}/projects`);

    await expect(page.locator('.projects-header')).toBeVisible();
  });
});