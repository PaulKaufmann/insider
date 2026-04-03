import { test, expect } from '@playwright/test';

const LOGIN_PATH = process.env.LOGIN_PATH ?? '/member-login';
const HOME_PATH = process.env.HOME_PATH ?? '/';

test.describe('Login & Auth', () => {
  test.use({ storageState: { cookies: [], origins: [] } });

  test('nicht eingeloggt → redirect auf login-seite', async ({ page }) => {
    await page.goto(HOME_PATH);
    await expect(page).toHaveURL(new RegExp(LOGIN_PATH.replace('/', '\\/')));
  });

  test('login mit falschen credentials → fehlermeldung oder invalid_credentials in url', async ({ page }) => {
    await page.goto(LOGIN_PATH);
    await page.fill('#user_login', 'nichtexistent');
    await page.fill('#user_pass', 'FalschesPasswort1!');
    await page.click('#wp-submit');
    // WordPress hängt ?login=invalid_credentials an die URL
    // oder zeigt #login_error (Standard-WP) oder bleibt auf Login-Seite
    await page.waitForURL(url => url.href.includes('invalid_credentials') || url.href.includes('member-login'), { timeout: 5_000 });
    const url = page.url();
    const hasError =
      url.includes('invalid_credentials') ||
      (await page.locator('#login_error').isVisible());
    expect(hasError).toBe(true);
  });

  test('login mit gültigen credentials → nicht mehr auf login-seite', async ({ page }) => {
    await page.goto(LOGIN_PATH);
    await page.fill('#user_login', process.env.SUBSCRIBER_USER!);
    await page.fill('#user_pass', process.env.SUBSCRIBER_PASS!);
    await page.click('#wp-submit');
    await expect(page).not.toHaveURL(new RegExp(LOGIN_PATH.replace('/', '\\/')), { timeout: 10_000 });
  });
});
