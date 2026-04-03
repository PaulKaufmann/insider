# Testing-Konzept: insider/inseider

## Ziel

Tests sollen den aktuellen Status Quo festzurren, damit Refactoring und neue Features
sicher eingeführt werden können. Der Fokus liegt auf **Regressionssicherheit**, nicht
auf vollständiger Abdeckung.

**Wichtig:** Tests laufen ausschließlich auf einer dedizierten Testinstanz.
Kein Test darf eine Produktions-DB, produktive Dateien oder externe Dienste
(Vimeo, OneSignal, SMTP) in echten Requests treffen.

---

## Empfohlener Stack

### PHP-Unit-Tests: WP-CLI + PHPUnit + WP Test Utils

```
phpunit/phpunit
yoast/wp-test-utils          # WordPress-kompatible Test-Basisklassen
10up/wp_mock                 # Für pure PHP-Unit-Tests ohne WordPress-Bootstrap
```

Alternativ (wenn vollständige WordPress-Instanz verfügbar):
```
Brain\Monkey                 # WordPress-Funktions-Mocking
```

### JavaScript-Tests: Jest

```
jest
@testing-library/jest-dom
```

Für die AJAX-Funktionen in `ajax_inseider_v1.2.js` reicht Jest mit manuellem
jQuery-Mock oder `jest-environment-jsdom`.

### End-to-End: Playwright oder Cypress

Für die wichtigsten User-Journeys (Login, Post erstellen, Feed laden) –
läuft gegen die Testinstanz mit dedizierter Test-DB.

---

## Testpyramide

```
         /  E2E  \          ← wenige, hoher Wert: Login, Post-Flow, Feed
        /----------\
       /Integration \       ← AJAX-Endpoints, Berechtigungsprüfungen
      /--------------\
     /   Unit Tests   \     ← Reine Logikfunktionen ohne WP-Abhängigkeit
    /------------------\
```

---

## Priorisierung: Was zuerst testen?

### Priorität 1 – Sicherheitskritisch

Diese Funktionen schützen Produktionsdaten. Ein Fehler hier hat sofortige Folgen.

**Passwortvalidierung** (`functions/security.php` → `is_valid_password()`)
```php
// Regeln laut Code:
// - Mindestens 8 Zeichen
// - Mind. 1 Großbuchstabe
// - Mind. 1 Kleinbuchstabe
// - Mind. 1 Ziffer
// - Mind. 1 Sonderzeichen
// - Keine Leerzeichen
```
→ Unit-Tests für alle Grenzfälle, Tippfehler in Fehlermeldungen als Snapshot sichern.

**Force-Login-Bypass** (`functions/security.php` → `spk_forcelogin_bypass()`)
```
Öffentliche URLs: Login-Seite, Impressum, Passwort-Vergessen
Gesperrte URLs: alles andere
```
→ Unit-Tests mit URL-Fixtures.

**AJAX-Berechtigungen** (`functions/ajax.php`)
```
more_post_ajax        → nur für eingeloggte User
edit_user_ajax        → nur mit can('edit_users')
editor_image_upload   → nur für eingeloggte User
onesignal_player_id   → nur für eingeloggte User
```
→ Integration-Tests: Request als nicht-eingeloggter User → erwartet `false`.

### Priorität 2 – Geschäftslogik

**Benachrichtigungs-Strategien** (`functions/notifications.php`)
Strategien `NEVER`, `ALWAYS`, `MANUAL`, `DRAFT_STATUS` – jede muss korrekt
triggern oder nicht triggern.

**Gewinnspiel-Lifecycle** (`functions/posting.php`)
- `start_prize_draw()` setzt Status korrekt
- `end_prize_draw()` wählt Gewinner, setzt Status, sendet Benachrichtigung

**User-Erstellung** (`functions/user.php` → `spk_create_User()`)
- Passwort-Generierung erfüllt Regeln
- Doppelte E-Mail wird abgefangen

### Priorität 3 – Template-Output (Snapshot-Tests)

**AJAX-Response-Format**
Die JavaScript-Seite erwartet bestimmte JSON-Strukturen von:
- `more_post_ajax` – Array von Post-Objekten
- `edit_user_ajax` – User-Objekt mit `first_name`, `last_name`, `role`, `role_name`, `user_email`

→ Snapshot dieser Strukturen festhalten und bei Änderungen reviewen.

---

## Konkrete Test-Struktur

```
inseider/
└── tests/
    ├── bootstrap.php            # WordPress-Testumgebung laden
    ├── phpunit.xml
    │
    ├── unit/
    │   ├── SecurityTest.php     # is_valid_password, spk_forcelogin_bypass
    │   ├── UserTest.php         # spk_create_User, spk_edit_User
    │   ├── NotificationTest.php # Strategien, E-Mail-Auslösung
    │   └── PostingTest.php      # Gewinnspiel-Lifecycle, post_later
    │
    ├── integration/
    │   ├── AjaxTest.php         # AJAX-Endpoints mit simulierten Requests
    │   └── PermissionsTest.php  # Rollen × Aktionen Matrix
    │
    ├── snapshots/
    │   ├── ajax-more-post.json  # Erwartete JSON-Struktur more_post_ajax
    │   └── ajax-edit-user.json  # Erwartete JSON-Struktur edit_user_ajax
    │
    └── e2e/
        ├── login.spec.ts        # Login-Flow
        ├── post-creation.spec.ts # Post erstellen mit Bild/Video/Dokument
        └── feed.spec.ts         # Feed laden, mehr Posts laden
```

---

## Konkrete Beispiel-Tests

### Unit: Passwortvalidierung

```php
class SecurityTest extends WP_UnitTestCase {

    public function test_valid_password() {
        $this->assertTrue(is_valid_password('Abc123!x') === true);
    }

    public function test_too_short() {
        $result = is_valid_password('Ab1!');
        $this->assertIsArray($result);
        $this->assertNotEmpty($result);
    }

    public function test_no_uppercase() {
        $result = is_valid_password('abc123!x');
        $this->assertIsArray($result);
    }

    public function test_no_special_char() {
        $result = is_valid_password('Abcdef12');
        $this->assertIsArray($result);
    }

    public function test_with_space() {
        $result = is_valid_password('Abc 123!');
        $this->assertIsArray($result);
    }

    // Fehlermeldungs-Text als Snapshot sichern
    public function test_error_messages_unchanged() {
        $result = is_valid_password('short');
        $this->assertContains(
            'Das Passwort muss mindestens 8 Zeichen lang sein.',
            $result
        );
    }
}
```

### Unit: Force-Login-Bypass

```php
class ForceLoginTest extends WP_UnitTestCase {

    /** @dataProvider publicUrlProvider */
    public function test_public_urls_are_bypassed(string $url) {
        $_SERVER['REQUEST_URI'] = $url;
        $result = spk_forcelogin_bypass(false);
        $this->assertTrue($result, "URL {$url} sollte öffentlich sein");
    }

    public function publicUrlProvider(): array {
        return [
            'login'    => ['/anmelden'],
            'impressum' => ['/impressum'],
        ];
    }

    public function test_private_urls_are_not_bypassed() {
        $_SERVER['REQUEST_URI'] = '/home';
        $result = spk_forcelogin_bypass(false);
        $this->assertFalse($result);
    }
}
```

### Integration: AJAX-Berechtigungen

```php
class AjaxPermissionsTest extends WP_Ajax_UnitTestCase {

    public function test_more_post_ajax_requires_login() {
        // Nicht eingeloggter User
        $this->expectException('WPAjaxDieStopException');
        $this->_handleAjax('more_post_ajax');
        // Erwartet: 'false' als Response
    }

    public function test_edit_user_requires_edit_users_capability() {
        $subscriber = self::factory()->user->create(['role' => 'subscriber']);
        wp_set_current_user($subscriber);

        $this->expectException('WPAjaxDieContinueException');
        $this->_handleAjax('edit_user');
        // Erwartet: leere Response wegen fehlender Berechtigung
    }
}
```

### Snapshot: AJAX-Response-Struktur

```php
public function test_edit_user_ajax_response_structure() {
    $admin = self::factory()->user->create(['role' => 'administrator']);
    wp_set_current_user($admin);

    $target = self::factory()->user->create([
        'role'       => 'author',
        'user_email' => 'test@example.com',
        'first_name' => 'Max',
        'last_name'  => 'Mustermann',
    ]);

    $_POST['user'] = get_user_by('id', $target)->user_login;

    try {
        $this->_handleAjax('edit_user');
    } catch (WPAjaxDieContinueException $e) {
        $response = json_decode($this->_last_response);
        $this->assertObjectHasAttribute('first_name', $response);
        $this->assertObjectHasAttribute('last_name', $response);
        $this->assertObjectHasAttribute('role', $response);
        $this->assertObjectHasAttribute('role_name', $response);
        $this->assertObjectHasAttribute('user_email', $response);
    }
}
```

### E2E: Login-Flow (Playwright)

```typescript
test('Login mit gültigen Credentials', async ({ page }) => {
    await page.goto('/');
    // Erwartung: Redirect auf Login-Seite
    await expect(page).toHaveURL(/anmelden/);

    await page.fill('#user_login', process.env.TEST_USER);
    await page.fill('#user_pass', process.env.TEST_PASSWORD);
    await page.click('#wp-submit');

    await expect(page).toHaveURL(/home/);
    await expect(page.locator('.post-list')).toBeVisible();
});

test('Ungültige Credentials zeigen Fehlermeldung', async ({ page }) => {
    await page.goto('/anmelden');
    await page.fill('#user_login', 'falsch');
    await page.fill('#user_pass', 'falsch');
    await page.click('#wp-submit');

    await expect(page.locator('.login-error')).toBeVisible();
});
```

---

## Wichtige Meta-Keys als Fixtures sichern

Diese Keys müssen in einem Test-Fixture explizit dokumentiert sein, damit
eine versehentliche Umbenennung sofort auffällt:

```php
class MetaKeyContractTest extends WP_UnitTestCase {

    private array $expected_post_meta_keys = [
        'videoId',
        'media_type',
        'media_info',
        'documents_attachment_id',
        'content_attachment_ids',
        'notify',
        'post_later',
        'post_later_timeStr',
        'notificatiosn_triggered_by',   // Tippfehler: absichtlich
        'post_edited_after_last_notification',
        'notified_time',
    ];

    private array $expected_user_meta_keys = [
        'favorite_posts',
        'notifications_categories',
        'notifications_posts',
        'notifications_prize_draw',
        'notifications_custom_email',
        'business_parter_post_id',       // Tippfehler: absichtlich
        'onesignal_player_ids',
    ];

    private array $expected_ajax_actions = [
        'more_post_ajax',
        'edit_user_ajax',
        'editor_image_upload_ajax',
        'onesignal_player_id_ajax',
    ];

    public function test_post_meta_keys_documented() {
        // Dieser Test dokumentiert die Keys – schlägt fehl wenn jemand
        // $expected_post_meta_keys ändert, ohne bewusste Entscheidung
        $this->assertCount(11, $this->expected_post_meta_keys);
    }

    public function test_ajax_actions_are_registered() {
        foreach ($this->expected_ajax_actions as $action) {
            $this->assertTrue(
                has_action("wp_ajax_{$action}"),
                "AJAX-Action wp_ajax_{$action} ist nicht registriert"
            );
        }
    }
}
```

---

## Setup-Empfehlung

### 1. PHPUnit + WP Test Utils installieren

```bash
composer require --dev phpunit/phpunit yoast/wp-test-utils
```

### 2. Testdatenbank einrichten

```bash
# Im WP-CLI-Kontext der Testinstanz:
wp db create --dbname=insider_test
# .env.testing anlegen:
# DB_NAME=insider_test
# DB_USER=...
# DB_PASSWORD=...
# WP_TESTS_DIR=/path/to/wordpress-tests-lib
```

### 3. bootstrap.php

```php
<?php
// tests/bootstrap.php
$_tests_dir = getenv('WP_TESTS_DIR') ?: '/tmp/wordpress-tests-lib';
require_once $_tests_dir . '/includes/functions.php';

function _manually_load_theme() {
    // Genesis als Parent laden
    add_filter('stylesheet', fn() => 'inseider');
    add_filter('template', fn() => 'genesis');
}
tests_add_filter('muplugins_loaded', '_manually_load_theme');

require $_tests_dir . '/includes/bootstrap.php';
```

### 4. phpunit.xml

```xml
<?xml version="1.0"?>
<phpunit bootstrap="tests/bootstrap.php" colors="true">
    <testsuites>
        <testsuite name="Unit">
            <directory>tests/unit</directory>
        </testsuite>
        <testsuite name="Integration">
            <directory>tests/integration</directory>
        </testsuite>
    </testsuites>
</phpunit>
```

---

## Was wir NICHT testen

- **TinyMCE-Plugins** – Externe Library, keine eigene Logik
- **Genesis Framework-Internals** – Parent-Theme-Verhalten
- **SMTP-Versand** – Mocking reicht, kein echter Mailversand in Tests
- **Vimeo API** – Nur Mock; echter API-Call gehört nicht in Unit/Integration-Tests
- **OneSignal API** – Gleiches Prinzip

---

## Schrittweise Einführung

1. **Phase 1:** `SecurityTest.php` – Passwortvalidierung & Force-Login-Bypass (2–3 Stunden)
2. **Phase 2:** `MetaKeyContractTest.php` – AJAX-Actions & Meta-Keys dokumentieren (1 Stunde)
3. **Phase 3:** `AjaxPermissionsTest.php` – Berechtigungsmatrix (3–4 Stunden)
4. **Phase 4:** E2E Login-Flow (2 Stunden Setup + 1 Stunde Tests)
5. **Phase 5:** Geschäftslogik (Gewinnspiele, Benachrichtigungen)

Jede Phase produziert einen lauffähigen Test-Stand, der danach im CI läuft.
