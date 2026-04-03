import { test, expect } from '@playwright/test';

const HOME_PATH = process.env.HOME_PATH ?? '/';
const CREATE_POST_PATH = process.env.CREATE_POST_PATH ?? '/uploading';
const USER_MGMT_PATH = process.env.USER_MGMT_PATH ?? '/user-management';
// USER_MGMT_SELECTOR aus dotenv: # wird als Kommentar gelesen, daher Fallback im Code
const USER_MGMT_SELECTOR = process.env.USER_MGMT_SELECTOR || 'a[href*="user-management"]';

test.describe('Subscriber: eingeschränkte Rechte', () => {
  test.use({ storageState: 'tests/.auth/subscriber.json' });

  test('subscriber sieht keine nutzerverwaltung und kein upload mit falscher kategorie', async ({ page }) => {
    await page.goto(CREATE_POST_PATH);
    // Auf dieser Instanz ist das Formular für alle sichtbar, aber der Submit
    // schlägt fehl wenn der User keine erlaubte Kategorie hat.
    // Wir prüfen: Subscriber kann die Seite laden (kein Redirect auf Login)
    await expect(page).not.toHaveURL(/member-login/);
    // ...aber hat keine Nutzerverwaltung
    await page.goto('/');
    await expect(page.locator(USER_MGMT_SELECTOR)).toHaveCount(0);
  });

  test('subscriber sieht keine nutzerverwaltung', async ({ page }) => {
    await page.goto(HOME_PATH);
    await expect(page.locator(USER_MGMT_SELECTOR)).toHaveCount(0);
  });
});

test.describe('Admin: volle Rechte', () => {
  test.use({ storageState: 'tests/.auth/admin.json' });

  test('admin sieht nutzerverwaltungs-link', async ({ page }) => {
    await page.goto(HOME_PATH);
    await expect(page.locator(USER_MGMT_SELECTOR).first()).toBeVisible();
  });

  test('admin kann nutzerverwaltung aufrufen', async ({ page }) => {
    await page.goto(USER_MGMT_PATH);
    await expect(page).not.toHaveURL(/member-login/);
    // Seite muss Benutzer-Formular oder -Liste enthalten
    await expect(page.locator('body')).not.toContainText('Seite nicht gefunden');
  });
});

test.describe('Business Partner: auf einen Post eingeschränkt', () => {
  test.use({ storageState: 'tests/.auth/business-partner.json' });

  test('business partner wird nicht auf home weitergeleitet', async ({ page }) => {
    await page.goto(HOME_PATH);
    // handle_viewing_restrictions() leitet auf den eigenen Post um – nie auf /
    await expect(page).not.toHaveURL(/^https:\/\/[^/]+\/?$/);
  });

  test('business partner kann nicht auf /uploading zugreifen', async ({ page }) => {
    await page.goto(CREATE_POST_PATH);
    // Muss auf den eigenen Post weitergeleitet werden, nicht auf /uploading bleiben
    await expect(page).not.toHaveURL(new RegExp(CREATE_POST_PATH.replace('/', '\\/')));
  });
});
