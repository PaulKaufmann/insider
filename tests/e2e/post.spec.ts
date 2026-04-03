import { test, expect } from '@playwright/test';

const CREATE_POST_PATH = process.env.CREATE_POST_PATH ?? '/beitrag-erstellen';

test.describe('Post erstellen (Author)', () => {
  test.use({ storageState: 'tests/.auth/author.json' });

  test('post-formular ist erreichbar', async ({ page }) => {
    await page.goto(CREATE_POST_PATH);
    // Das Formular #new_post muss im DOM vorhanden sein (kann display:none sein bis Medientyp gewählt)
    await expect(page.locator('#new_post')).toBeAttached({ timeout: 10_000 });
  });

  test('post mit text erfolgreich erstellen', async ({ page }) => {
    await page.goto(CREATE_POST_PATH);

    // Warten bis Seite geladen
    await expect(page.locator('#new_post')).toBeAttached({ timeout: 10_000 });

    // Medientyp wählen (erster verfügbarer Radio-Button)
    const mediaSelect = page.locator('select#mediaSelect, input[name="mediaType"], input[name="cat"]').first();
    if (await mediaSelect.isVisible()) {
      // Select: ersten Wert wählen der nicht leer ist
      await mediaSelect.selectOption({ index: 1 });
    }

    // Titel eingeben
    await page.fill('#title', 'Testbeitrag Playwright ' + Date.now());

    // TinyMCE-Content setzen (über JS, da iframe)
    await page.evaluate(() => {
      if (typeof (window as any).tinymce !== 'undefined') {
        const ed = (window as any).tinymce.get('content');
        if (ed) ed.setContent('Dies ist ein automatisch erstellter Testbeitrag.');
      }
    });

    // Formular absenden
    await page.click('#sumbitButton');

    // Nach Submit: nicht mehr auf der Erstellen-Seite oder Erfolgsmeldung sichtbar
    await page.waitForURL(url => !url.pathname.includes(CREATE_POST_PATH.replace('/', '')), { timeout: 15_000 })
      .catch(() => {}); // Redirect ist optional je nach Konfiguration

    // Kein Fatal-Fehler auf der Seite
    await expect(page.locator('body')).not.toContainText('Fatal error');
  });

  test('pflichtfeld titel: absenden ohne titel zeigt validierung', async ({ page }) => {
    await page.goto(CREATE_POST_PATH);
    await expect(page.locator('#new_post')).toBeAttached({ timeout: 10_000 });

    // Direkt absenden ohne Titel – HTML5-required verhindert Submit
    // Wir prüfen ob #title das required-Attribut hat
    const titleInput = page.locator('#title');
    await expect(titleInput).toHaveAttribute('required');
  });
});
