# Playwright E2E Testing – Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Playwright E2E-Testsuite gegen dev.muxd.de aufsetzen, die Login, Feed, Post-Erstellung und rollenbasierte Berechtigungen absichert.

**Architecture:** Playwright läuft lokal und in GitHub Actions gegen die bestehende Dev-Instanz. Pro Rolle wird einmalig eine Browser-Session gespeichert und wiederverwendet. Tests sind in thematische Spec-Dateien aufgeteilt.

**Tech Stack:** Node.js, Playwright, TypeScript, dotenv, GitHub Actions

---

## Dateistruktur

```
dev/
├── tests/
│   ├── e2e/
│   │   ├── auth.setup.ts            ← Login pro Rolle, Session in .auth/ speichern
│   │   ├── login.spec.ts            ← Auth & Redirect-Verhalten
│   │   ├── feed.spec.ts             ← Feed laden & AJAX-Paginierung
│   │   ├── post.spec.ts             ← Post erstellen (Author-Rolle)
│   │   └── permissions.spec.ts     ← Rollenrechte
│   ├── .auth/                       ← gitignored; gespeicherte Sessions
│   └── .env.example
├── playwright.config.ts
├── package.json
└── .github/
    └── workflows/
        └── e2e.yml
```

---

## Task 1: Node-Projekt und Playwright initialisieren

**Files:**
- Create: `dev/package.json`
- Create: `dev/playwright.config.ts`
- Create: `dev/tests/.env.example`
- Create: `dev/.gitignore` (ergänzen falls vorhanden)

- [ ] **Schritt 1: In `dev/` wechseln und Node-Projekt anlegen**

```bash
cd /Users/paulkaufmann/Workspace/insider/dev
npm init -y
npm install --save-dev @playwright/test dotenv
npx playwright install chromium
```

Erwartete Ausgabe: `✓ chromium ... installed`

- [ ] **Schritt 2: `playwright.config.ts` anlegen**

```typescript
// dev/playwright.config.ts
import { defineConfig, devices } from '@playwright/test';
import * as dotenv from 'dotenv';
dotenv.config({ path: './tests/.env' });

export default defineConfig({
  testDir: './tests/e2e',
  timeout: 30_000,
  retries: 1,
  use: {
    baseURL: process.env.BASE_URL ?? 'https://dev.muxd.de',
    trace: 'on-first-retry',
  },
  projects: [
    {
      name: 'setup',
      testMatch: /auth\.setup\.ts/,
    },
    {
      name: 'chromium',
      use: {
        ...devices['Desktop Chrome'],
      },
      dependencies: ['setup'],
    },
  ],
});
```

- [ ] **Schritt 3: `.env.example` anlegen**

```
# dev/tests/.env.example
# Kopieren zu tests/.env und Werte eintragen
BASE_URL=https://dev.muxd.de
ADMIN_USER=
ADMIN_PASS=
AUTHOR_USER=
AUTHOR_PASS=
SUBSCRIBER_USER=
SUBSCRIBER_PASS=
BUSINESS_PARTNER_USER=
BUSINESS_PARTNER_PASS=
```

- [ ] **Schritt 4: `.env` aus `.env.example` kopieren und mit echten Test-Credentials füllen**

```bash
cp tests/.env.example tests/.env
# Dann tests/.env mit den Zugangsdaten der Test-User befüllen
```

- [ ] **Schritt 5: `.gitignore` ergänzen**

Folgende Zeilen zu `dev/.gitignore` hinzufügen (Datei anlegen falls nicht vorhanden):

```
tests/.env
tests/.auth/
node_modules/
playwright-report/
test-results/
```

- [ ] **Schritt 6: Playwright-Installation verifizieren**

```bash
cd /Users/paulkaufmann/Workspace/insider/dev
npx playwright --version
```

Erwartete Ausgabe: `Version 1.x.x`

- [ ] **Schritt 7: Committen**

```bash
git add package.json playwright.config.ts tests/.env.example .gitignore
git commit -m "test: playwright setup mit config und env-template"
```

---

## Task 2: Session-Setup pro Rolle

**Files:**
- Create: `dev/tests/e2e/auth.setup.ts`

Der Setup-Schritt loggt jeden Test-User einmalig ein und speichert die Browser-Session. Alle nachfolgenden Tests nutzen diese Sessions – kein Re-Login nötig.

- [ ] **Schritt 1: `tests/e2e/auth.setup.ts` anlegen**

```typescript
// dev/tests/e2e/auth.setup.ts
import { test as setup, expect } from '@playwright/test';
import * as path from 'path';

const authDir = path.join(__dirname, '../.auth');

async function loginAs(page: any, user: string, pass: string, sessionFile: string) {
  await page.goto('/member-login');
  await page.fill('#user_login', user);
  await page.fill('#user_pass', pass);
  await page.click('#wp-submit');
  // Nach Login muss der Feed sichtbar sein
  await expect(page.locator('body')).not.toContainText('Anmelden', { timeout: 10_000 });
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
```

- [ ] **Schritt 2: `.auth/`-Verzeichnis anlegen**

```bash
mkdir -p /Users/paulkaufmann/Workspace/insider/dev/tests/.auth
```

- [ ] **Schritt 3: Setup-Schritt ausführen und prüfen ob Sessions angelegt werden**

```bash
cd /Users/paulkaufmann/Workspace/insider/dev
npx playwright test auth.setup.ts --project=setup
```

Erwartete Ausgabe: `4 passed` – und vier JSON-Dateien in `tests/.auth/`.

- [ ] **Schritt 4: Committen**

```bash
git add tests/e2e/auth.setup.ts
git commit -m "test: session-setup für alle 4 test-rollen"
```

---

## Task 3: Login-Tests

**Files:**
- Create: `dev/tests/e2e/login.spec.ts`

- [ ] **Schritt 1: `tests/e2e/login.spec.ts` anlegen**

```typescript
// dev/tests/e2e/login.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Login & Auth', () => {
  test.use({ storageState: { cookies: [], origins: [] } }); // kein gespeicherter Login

  test('nicht eingeloggt → redirect auf /member-login', async ({ page }) => {
    await page.goto('/');
    await expect(page).toHaveURL(/member-login/);
  });

  test('login mit falschen credentials zeigt fehlermeldung', async ({ page }) => {
    await page.goto('/member-login');
    await page.fill('#user_login', 'nichtexistent');
    await page.fill('#user_pass', 'FalschesPasswort1!');
    await page.click('#wp-submit');
    // WordPress zeigt Fehlermeldung im #login_error oder .login-error Element
    const errorVisible =
      (await page.locator('#login_error').isVisible()) ||
      (await page.locator('.login-error').isVisible());
    expect(errorVisible).toBe(true);
  });

  test('login mit gültigen credentials → feed sichtbar', async ({ page }) => {
    await page.goto('/member-login');
    await page.fill('#user_login', process.env.SUBSCRIBER_USER!);
    await page.fill('#user_pass', process.env.SUBSCRIBER_PASS!);
    await page.click('#wp-submit');
    await expect(page).not.toHaveURL(/member-login/);
    // Feed-Container muss sichtbar sein (Selektor ggf. nach erstem Lauf anpassen)
    await expect(page.locator('.post-list, .entry, article').first()).toBeVisible();
  });
});
```

- [ ] **Schritt 2: Tests ausführen**

```bash
cd /Users/paulkaufmann/Workspace/insider/dev
npx playwright test login.spec.ts --project=chromium
```

Erwartete Ausgabe: `3 passed`

Falls ein Test schlägt weil der Selektor nicht stimmt: Dev-Instanz im Browser öffnen, Selektor mit DevTools ermitteln, Skript anpassen.

- [ ] **Schritt 3: Committen**

```bash
git add tests/e2e/login.spec.ts
git commit -m "test: login und redirect verhalten"
```

---

## Task 4: Feed-Tests

**Files:**
- Create: `dev/tests/e2e/feed.spec.ts`

- [ ] **Schritt 1: `playwright.config.ts` um subscriber-Session erweitern**

Im `projects`-Array das `chromium`-Projekt so anpassen dass die subscriber-Session als Default genutzt wird:

```typescript
// In playwright.config.ts, im chromium-Projekt:
{
  name: 'chromium',
  use: {
    ...devices['Desktop Chrome'],
    storageState: 'tests/.auth/subscriber.json',
  },
  dependencies: ['setup'],
},
```

- [ ] **Schritt 2: `tests/e2e/feed.spec.ts` anlegen**

```typescript
// dev/tests/e2e/feed.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Feed', () => {
  test('feed lädt mindestens einen post', async ({ page }) => {
    await page.goto('/');
    const posts = page.locator('article, .post-item, .entry');
    await expect(posts.first()).toBeVisible({ timeout: 10_000 });
    expect(await posts.count()).toBeGreaterThan(0);
  });

  test('"mehr laden"-button lädt weitere posts nach', async ({ page }) => {
    await page.goto('/');

    const initialCount = await page.locator('article, .post-item, .entry').count();

    const loadMoreBtn = page.locator('button:has-text("Mehr"), a:has-text("Mehr"), [data-action="more"]');
    await expect(loadMoreBtn).toBeVisible({ timeout: 5_000 });
    await loadMoreBtn.click();

    // Nach AJAX-Anfrage soll die Anzahl gestiegen sein
    await page.waitForTimeout(2_000);
    const newCount = await page.locator('article, .post-item, .entry').count();
    expect(newCount).toBeGreaterThan(initialCount);
  });
});
```

- [ ] **Schritt 3: Tests ausführen**

```bash
cd /Users/paulkaufmann/Workspace/insider/dev
npx playwright test feed.spec.ts --project=chromium
```

Erwartete Ausgabe: `2 passed`

- [ ] **Schritt 4: Committen**

```bash
git add tests/e2e/feed.spec.ts playwright.config.ts
git commit -m "test: feed laden und mehr-laden ajax"
```

---

## Task 5: Post-Erstellungs-Tests

**Files:**
- Create: `dev/tests/e2e/post.spec.ts`

- [ ] **Schritt 1: `tests/e2e/post.spec.ts` anlegen**

```typescript
// dev/tests/e2e/post.spec.ts
import { test, expect } from '@playwright/test';
import * as path from 'path';

test.describe('Post erstellen (Author)', () => {
  test.use({ storageState: 'tests/.auth/author.json' });

  test('post mit text erfolgreich erstellen', async ({ page }) => {
    // Zur Post-Erstellungsseite navigieren (Selektor nach Dev-Instanz anpassen)
    await page.goto('/');
    const createBtn = page.locator('a:has-text("Beitrag"), a:has-text("Erstellen"), [href*="create"], [href*="neu"]');
    await expect(createBtn.first()).toBeVisible();
    await createBtn.first().click();

    // Titel eingeben
    await page.fill('input[name="post_title"], #post-title', 'Testbeitrag Playwright ' + Date.now());

    // Text eingeben (TinyMCE oder normales Textarea)
    const textarea = page.locator('textarea[name="content"], #content');
    if (await textarea.isVisible()) {
      await textarea.fill('Dies ist ein automatisch erstellter Testbeitrag.');
    } else {
      // TinyMCE iframe
      const frame = page.frameLocator('#content_ifr');
      await frame.locator('body').fill('Dies ist ein automatisch erstellter Testbeitrag.');
    }

    // Absenden
    await page.click('button[type="submit"], input[type="submit"], #publish');

    // Erfolgsmeldung oder Weiterleitung auf den Post
    await expect(page.locator('body')).not.toContainText('Fehler', { timeout: 10_000 });
  });

  test('pflichtfelder-validierung schlägt an wenn kein titel', async ({ page }) => {
    await page.goto('/');
    const createBtn = page.locator('a:has-text("Beitrag"), a:has-text("Erstellen"), [href*="create"], [href*="neu"]');
    await createBtn.first().click();

    // Formular ohne Titel absenden
    await page.click('button[type="submit"], input[type="submit"], #publish');

    // Fehlermeldung muss sichtbar sein
    const hasError =
      (await page.locator('.error, .alert, [class*="error"]').isVisible()) ||
      (await page.locator(':text("Pflicht"), :text("erforderlich"), :text("Titel")').isVisible());
    expect(hasError).toBe(true);
  });
});
```

- [ ] **Schritt 2: Tests ausführen**

```bash
cd /Users/paulkaufmann/Workspace/insider/dev
npx playwright test post.spec.ts --project=chromium
```

Erwartete Ausgabe: `2 passed`

Falls Selektoren nicht stimmen: `npx playwright test post.spec.ts --headed` ausführen und im Browser die richtigen Selektoren ermitteln.

- [ ] **Schritt 3: Committen**

```bash
git add tests/e2e/post.spec.ts
git commit -m "test: post erstellen und pflichtfeld-validierung"
```

---

## Task 6: Berechtigungs-Tests

**Files:**
- Create: `dev/tests/e2e/permissions.spec.ts`

- [ ] **Schritt 1: `tests/e2e/permissions.spec.ts` anlegen**

```typescript
// dev/tests/e2e/permissions.spec.ts
import { test, expect } from '@playwright/test';

test.describe('Subscriber: eingeschränkte Rechte', () => {
  test.use({ storageState: 'tests/.auth/subscriber.json' });

  test('subscriber sieht keinen "beitrag erstellen"-button', async ({ page }) => {
    await page.goto('/');
    const createBtn = page.locator('a:has-text("Erstellen"), a:has-text("Beitrag erstellen"), [href*="create"]');
    await expect(createBtn).toHaveCount(0);
  });
});

test.describe('Admin: volle Rechte', () => {
  test.use({ storageState: 'tests/.auth/admin.json' });

  test('admin sieht benutzerverwaltung', async ({ page }) => {
    await page.goto('/');
    const userMgmt = page.locator('a:has-text("Benutzer"), a[href*="user-management"], a[href*="benutzerverwaltung"]');
    await expect(userMgmt.first()).toBeVisible();
  });
});

test.describe('Business Partner: auf einen Post eingeschränkt', () => {
  test.use({ storageState: 'tests/.auth/business-partner.json' });

  test('business partner wird auf seinen post weitergeleitet', async ({ page }) => {
    await page.goto('/');
    // Business Partner darf nicht auf der normalen Startseite landen
    // sondern wird auf seinen zugewiesenen Post weitergeleitet
    await expect(page).not.toHaveURL(/^\/$|\/home\/?$/);
  });

  test('business partner kann nicht auf andere posts zugreifen', async ({ page }) => {
    // Versuche eine andere Seite zu öffnen – muss wieder auf den eigenen Post redirecten
    await page.goto('/home');
    const url = page.url();
    // Nach dem Redirect darf es nicht /home sein
    expect(url).not.toMatch(/\/home\/?$/);
  });
});
```

- [ ] **Schritt 2: Tests ausführen**

```bash
cd /Users/paulkaufmann/Workspace/insider/dev
npx playwright test permissions.spec.ts --project=chromium
```

Erwartete Ausgabe: `4 passed`

- [ ] **Schritt 3: Committen**

```bash
git add tests/e2e/permissions.spec.ts
git commit -m "test: rollenbasierte berechtigungen subscriber, admin, business partner"
```

---

## Task 7: GitHub Actions CI

**Files:**
- Create: `dev/.github/workflows/e2e.yml`

- [ ] **Schritt 1: `.github/workflows/e2e.yml` anlegen**

```yaml
# dev/.github/workflows/e2e.yml
name: E2E Tests

on:
  push:
    branches: [main, master]
  pull_request:
    branches: [main, master]

jobs:
  e2e:
    runs-on: ubuntu-latest
    timeout-minutes: 15

    steps:
      - uses: actions/checkout@v4

      - uses: actions/setup-node@v4
        with:
          node-version: '20'
          cache: 'npm'
          cache-dependency-path: 'dev/package-lock.json'

      - name: Install dependencies
        working-directory: dev
        run: npm ci

      - name: Install Playwright browsers
        working-directory: dev
        run: npx playwright install --with-deps chromium

      - name: Run E2E tests
        working-directory: dev
        env:
          BASE_URL: ${{ secrets.BASE_URL }}
          ADMIN_USER: ${{ secrets.ADMIN_USER }}
          ADMIN_PASS: ${{ secrets.ADMIN_PASS }}
          AUTHOR_USER: ${{ secrets.AUTHOR_USER }}
          AUTHOR_PASS: ${{ secrets.AUTHOR_PASS }}
          SUBSCRIBER_USER: ${{ secrets.SUBSCRIBER_USER }}
          SUBSCRIBER_PASS: ${{ secrets.SUBSCRIBER_PASS }}
          BUSINESS_PARTNER_USER: ${{ secrets.BUSINESS_PARTNER_USER }}
          BUSINESS_PARTNER_PASS: ${{ secrets.BUSINESS_PARTNER_PASS }}
        run: npx playwright test

      - name: Upload test report
        uses: actions/upload-artifact@v4
        if: failure()
        with:
          name: playwright-report
          path: dev/playwright-report/
          retention-days: 7
```

- [ ] **Schritt 2: GitHub Secrets anlegen**

Im GitHub-Repository unter Settings → Secrets and variables → Actions folgende Secrets anlegen:
- `BASE_URL` → `https://dev.muxd.de`
- `ADMIN_USER`, `ADMIN_PASS`
- `AUTHOR_USER`, `AUTHOR_PASS`
- `SUBSCRIBER_USER`, `SUBSCRIBER_PASS`
- `BUSINESS_PARTNER_USER`, `BUSINESS_PARTNER_PASS`

- [ ] **Schritt 3: Committen und Push**

```bash
git add .github/workflows/e2e.yml
git commit -m "ci: github actions workflow für playwright e2e"
```

- [ ] **Schritt 4: Pipeline im GitHub-Interface prüfen**

Nach dem Push unter Actions prüfen ob der Workflow startet und die Tests grün sind.

---

## Task 8: Gesamtlauf und Abnahme

- [ ] **Schritt 1: Alle Tests lokal komplett durchlaufen**

```bash
cd /Users/paulkaufmann/Workspace/insider/dev
npx playwright test --project=chromium
```

Erwartete Ausgabe: alle Tests `passed`. Bei Fehlern: Selektoren gegen die Live-Instanz prüfen.

- [ ] **Schritt 2: HTML-Report anschauen**

```bash
npx playwright show-report
```

- [ ] **Schritt 3: `package.json` Test-Script ergänzen**

```json
{
  "scripts": {
    "test:e2e": "playwright test",
    "test:e2e:headed": "playwright test --headed",
    "test:e2e:report": "playwright show-report"
  }
}
```

- [ ] **Schritt 4: Final committen**

```bash
git add package.json
git commit -m "test: npm scripts für playwright"
```
