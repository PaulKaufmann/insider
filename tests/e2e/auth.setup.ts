import { test as setup, expect } from '@playwright/test';
import * as path from 'path';

const authDir = path.join(__dirname, '../.auth');

async function loginAs(page: any, user: string, pass: string, sessionFile: string) {
  await page.goto('/member-login');
  await page.fill('#user_login', user);
  await page.fill('#user_pass', pass);
  await page.click('#wp-submit');
  // Nach Login muss man nicht mehr auf der Login-Seite sein
  await expect(page).not.toHaveURL(/member-login/, { timeout: 10_000 });
  await page.context().storageState({ path: sessionFile });
}

setup('setup admin session', async ({ page }) => {
  await loginAs(
    page,
    process.env.ADMIN_USER!,
    process.env.ADMIN_PASS!,
    path.join(authDir, 'admin.json')
  );
});

setup('setup author session', async ({ page }) => {
  await loginAs(
    page,
    process.env.AUTHOR_USER!,
    process.env.AUTHOR_PASS!,
    path.join(authDir, 'author.json')
  );
});

setup('setup subscriber session', async ({ page }) => {
  await loginAs(
    page,
    process.env.SUBSCRIBER_USER!,
    process.env.SUBSCRIBER_PASS!,
    path.join(authDir, 'subscriber.json')
  );
});

setup('setup business partner session', async ({ page }) => {
  await loginAs(
    page,
    process.env.BUSINESS_PARTNER_USER!,
    process.env.BUSINESS_PARTNER_PASS!,
    path.join(authDir, 'business-partner.json')
  );
});
