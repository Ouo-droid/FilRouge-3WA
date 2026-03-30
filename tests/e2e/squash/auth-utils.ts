export const BASE_URL = 'http://localhost:8000';
export const TEST_USER = {
    email: 'test@example.com',
    password: 'Antoine79@'
};

export async function login(page) {
    await page.goto(`${BASE_URL}/login`);
    await page.fill('input[name="email"]', TEST_USER.email);
    await page.fill('input[name="password"]', TEST_USER.password);
    await page.click('button[type="submit"]');
    await page.waitForLoadState('networkidle');
}
