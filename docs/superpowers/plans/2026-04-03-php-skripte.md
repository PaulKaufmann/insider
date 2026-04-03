# PHP-Logik-Tests (Skripte ohne Framework) – Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Einfache PHP-Skripte die reine Logikfunktionen des Themes ohne WordPress-Bootstrap testen – Passwortvalidierung, Force-Login-Bypass und Meta-Key-Contracts.

**Architecture:** Jedes Testskript lädt nur die relevante Theme-PHP-Datei und definiert nötige WordPress-Stubs inline. Die Skripte geben `[PASS]`/`[FAIL]` aus und setzen Exit-Code 1 bei Fehlern. `run_all.sh` führt alle Skripte aus und kombiniert die Exit-Codes für CI.

**Tech Stack:** PHP 8.4 (system), Bash

---

## Dateistruktur

```
dev/tests/php/
├── helpers.php             ← assert_true(), assert_equals(), pass(), fail()
├── run_all.sh              ← Alle Skripte ausführen, kombinierter Exit-Code
├── test_password.php       ← is_valid_password() alle Regeln + Fehlertexte
├── test_forcelogin.php     ← spk_forcelogin_bypass() URL-Fixtures
└── test_meta_contracts.php ← Inventar Meta-Keys und AJAX-Actions
```

---

## Task 1: Hilfs-Infrastruktur

**Files:**
- Create: `dev/tests/php/helpers.php`
- Create: `dev/tests/php/run_all.sh`

- [ ] **Schritt 1: `tests/php/helpers.php` anlegen**

```php
<?php
// dev/tests/php/helpers.php

$GLOBALS['__test_failed'] = false;

function pass(string $label): void {
    echo "[PASS] {$label}\n";
}

function fail(string $label, string $expected = '', string $got = ''): void {
    $GLOBALS['__test_failed'] = true;
    echo "[FAIL] {$label}\n";
    if ($expected !== '') {
        echo "       expected: {$expected}\n";
        echo "       got:      {$got}\n";
    }
}

function assert_true(bool $condition, string $label): void {
    if ($condition) {
        pass($label);
    } else {
        fail($label, 'true', 'false');
    }
}

function assert_false(bool $condition, string $label): void {
    if (!$condition) {
        pass($label);
    } else {
        fail($label, 'false', 'true');
    }
}

function assert_equals(mixed $expected, mixed $got, string $label): void {
    if ($expected === $got) {
        pass($label);
    } else {
        fail($label, var_export($expected, true), var_export($got, true));
    }
}

function assert_contains(string $needle, array $haystack, string $label): void {
    if (in_array($needle, $haystack, true)) {
        pass($label);
    } else {
        fail($label, "array contains '{$needle}'", implode(', ', $haystack));
    }
}

function exit_with_result(): void {
    if ($GLOBALS['__test_failed']) {
        exit(1);
    }
    exit(0);
}
```

- [ ] **Schritt 2: `tests/php/run_all.sh` anlegen**

```bash
#!/usr/bin/env bash
# dev/tests/php/run_all.sh
# Führt alle PHP-Testskripte aus und gibt kombinierten Exit-Code zurück.

set -e
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
FAILED=0

run_test() {
    echo "--- $1 ---"
    php "$SCRIPT_DIR/$1" || FAILED=1
    echo ""
}

run_test test_password.php
run_test test_forcelogin.php
run_test test_meta_contracts.php

if [ $FAILED -ne 0 ]; then
    echo "❌ Einige Tests schlugen fehl."
    exit 1
else
    echo "✅ Alle PHP-Tests bestanden."
    exit 0
fi
```

- [ ] **Schritt 3: run_all.sh ausführbar machen**

```bash
chmod +x /Users/paulkaufmann/Workspace/insider/dev/tests/php/run_all.sh
```

- [ ] **Schritt 4: Committen**

```bash
git add tests/php/helpers.php tests/php/run_all.sh
git commit -m "test: php test-infrastruktur (helpers und run_all)"
```

---

## Task 2: Passwortvalidierungs-Tests

**Files:**
- Create: `dev/tests/php/test_password.php`

Die Funktion `is_valid_password()` liegt in `inseider/functions/security.php` (Zeile 188-213).
Sie hat keine WordPress-Abhängigkeiten – kann direkt includiert werden.

- [ ] **Schritt 1: `tests/php/test_password.php` anlegen**

```php
<?php
// dev/tests/php/test_password.php

require_once __DIR__ . '/helpers.php';

// is_valid_password() hat keine WP-Abhängigkeiten – direkt laden
require_once __DIR__ . '/../../inseider/functions/security.php';

echo "=== is_valid_password ===\n\n";

// --- Gültiges Passwort ---
$result = is_valid_password('Abc123!x');
assert_true($result === true, 'gültiges passwort wird akzeptiert');

// --- Zu kurz ---
$result = is_valid_password('Ab1!');
assert_true(is_array($result), 'zu kurz → gibt array zurück');
assert_contains(
    'Das Passwort muss mindestens 8 Zeichen lang sein!',
    $result,
    'zu kurz → korrekte fehlermeldung'
);

// --- Kein Großbuchstabe ---
$result = is_valid_password('abc123!x');
assert_true(is_array($result), 'kein großbuchstabe → gibt array zurück');
assert_contains(
    'Das Passwort muss mindestens einen Großbuchstaben enthalten!',
    $result,
    'kein großbuchstabe → korrekte fehlermeldung'
);

// --- Keine Ziffer ---
$result = is_valid_password('Abcdefg!');
assert_true(is_array($result), 'keine ziffer → gibt array zurück');
assert_contains(
    'Das Passwort muss mindestens eine Ziffer enthalten!',
    $result,
    'keine ziffer → korrekte fehlermeldung'
);

// --- Kein Sonderzeichen ---
$result = is_valid_password('Abcdefg1');
assert_true(is_array($result), 'kein sonderzeichen → gibt array zurück');
assert_contains(
    'Das Passwort muss mindestens ein Sonderzeichen enthalten!',
    $result,
    'kein sonderzeichen → korrekte fehlermeldung'
);

// --- Leerzeichen ---
$result = is_valid_password('Abc 123!');
assert_true(is_array($result), 'leerzeichen → gibt array zurück');
assert_contains(
    'Das Passwort darf keine Leerzeichen enthalten!',
    $result,
    'leerzeichen → korrekte fehlermeldung'
);

// --- Genau 8 Zeichen (Grenzfall) ---
$result = is_valid_password('Abc123!x');
assert_true($result === true, 'genau 8 zeichen → gültig');

// --- Mehrere Fehler gleichzeitig ---
$result = is_valid_password('abc');
assert_true(is_array($result), 'mehrere fehler → gibt array zurück');
assert_true(count($result) >= 2, 'mehrere fehler → mindestens 2 fehlermeldungen');

exit_with_result();
```

- [ ] **Schritt 2: Test ausführen**

```bash
php /Users/paulkaufmann/Workspace/insider/dev/tests/php/test_password.php
```

Erwartete Ausgabe:
```
=== is_valid_password ===

[PASS] gültiges passwort wird akzeptiert
[PASS] zu kurz → gibt array zurück
[PASS] zu kurz → korrekte fehlermeldung
...
```

Falls ein `[FAIL]` erscheint: die Fehlermeldung in `security.php` hat sich verändert – das ist genau der Zweck dieses Tests.

- [ ] **Schritt 3: Committen**

```bash
git add tests/php/test_password.php
git commit -m "test: passwortvalidierung alle regeln und fehlermeldungs-snapshots"
```

---

## Task 3: Force-Login-Bypass-Tests

**Files:**
- Create: `dev/tests/php/test_forcelogin.php`

`spk_forcelogin_bypass()` braucht nur `$_SERVER['REQUEST_URI']` und gibt ein bool zurück.
Es muss `add_filter()` als Stub definiert werden, da `security.php` es beim Laden aufruft.

- [ ] **Schritt 1: `tests/php/test_forcelogin.php` anlegen**

```php
<?php
// dev/tests/php/test_forcelogin.php

require_once __DIR__ . '/helpers.php';

// WordPress-Stubs die beim Laden von security.php benötigt werden
function add_filter(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void {}
function add_action(string $hook, callable $callback, int $priority = 10, int $accepted_args = 1): void {}
function is_user_logged_in(): bool { return false; }
function wp_rand(int $min = 0, int $max = 0): int { return rand($min, $max); }

require_once __DIR__ . '/../../inseider/functions/security.php';

echo "=== spk_forcelogin_bypass ===\n\n";

function test_bypass(string $url, bool $expected_bypass, string $label): void {
    $_SERVER['REQUEST_URI'] = $url;
    $result = spk_forcelogin_bypass(false);
    if ($result === $expected_bypass) {
        pass($label);
    } else {
        fail($label, $expected_bypass ? 'bypass=true' : 'bypass=false', $result ? 'bypass=true' : 'bypass=false');
    }
}

// --- Öffentliche URLs (bypass = true) ---
test_bypass('/member-login',              true,  '/member-login ist öffentlich');
test_bypass('/member-password-lost',      true,  '/member-password-lost ist öffentlich');
test_bypass('/member-password-reset',     true,  '/member-password-reset ist öffentlich');
test_bypass('/impressum',                 true,  '/impressum ist öffentlich');
test_bypass('/datenschutz',               true,  '/datenschutz ist öffentlich');
test_bypass('/external-file',             true,  '/external-file ist öffentlich');
test_bypass('/erklaerung-zur-barrierefreiheit', true, '/erklaerung-zur-barrierefreiheit ist öffentlich');

// --- Gesperrte URLs (bypass = false) ---
test_bypass('/home',                      false, '/home erfordert login');
test_bypass('/wp-admin',                  false, '/wp-admin erfordert login');
test_bypass('/',                          false, '/ erfordert login');
test_bypass('/stellen',                   false, '/stellen erfordert login');

// --- Query-Strings werden ignoriert ---
test_bypass('/impressum?foo=bar',         true,  '/impressum mit query-string ist öffentlich');
test_bypass('/home?page=2',               false, '/home mit query-string erfordert login');

exit_with_result();
```

- [ ] **Schritt 2: Test ausführen**

```bash
php /Users/paulkaufmann/Workspace/insider/dev/tests/php/test_forcelogin.php
```

Erwartete Ausgabe: alle `[PASS]`

Falls `/member-login` nicht als öffentlich erkannt wird: die URL ist nicht in `spk_forcelogin_bypass()` gelistet (die Funktion handhabt `/member-login` nicht explizit – der Force-Login-Plugin selbst erlaubt die Login-Seite). In dem Fall diesen Test-Case entfernen oder mit einem Kommentar versehen.

- [ ] **Schritt 3: Committen**

```bash
git add tests/php/test_forcelogin.php
git commit -m "test: force-login-bypass öffentliche und gesperrte urls"
```

---

## Task 4: Meta-Key-Contracts

**Files:**
- Create: `dev/tests/php/test_meta_contracts.php`

Dieses Skript braucht kein WordPress und keine Theme-Datei – es ist ein reines Inventar-Dokument das als Test ausgeführt wird. Schlägt fehl wenn jemand die Liste ohne Bewusstsein ändert.

- [ ] **Schritt 1: `tests/php/test_meta_contracts.php` anlegen**

```php
<?php
// dev/tests/php/test_meta_contracts.php
//
// ZWECK: Dieses Skript dokumentiert alle WordPress-sichtbaren Bezeichner des
// inseider-Themes. Tippfehler sind ABSICHTLICH so – sie stehen in der
// Produktionsdatenbank und dürfen nicht geändert werden.
//
// Wenn du einen Bezeichner ändern willst: erst diesen Test anpassen,
// dann sicherstellen dass ALLE Datenbankwerte migriert werden.

require_once __DIR__ . '/helpers.php';

echo "=== Meta-Key Contracts ===\n\n";

// --- Post-Meta-Keys ---
// Quelle: functions/posting.php, functions/notifications.php, single.php
$post_meta_keys = [
    'videoId',
    'media_type',
    'media_info',
    'documents_attachment_id',
    'content_attachment_ids',
    'notify',
    'post_later',
    'post_later_timeStr',
    'notificatiosn_triggered_by',        // TIPPFEHLER: absichtlich (steht in DB)
    'post_edited_after_last_notification',
    'notified_time',
    'disable_manual_notifications',
];

// --- User-Meta-Keys ---
// Quelle: functions/notifications.php, functions/security.php, page-templates/account.php
$user_meta_keys = [
    'favorite_posts',
    'notifications_categories',
    'notifications_posts',
    'notifications_prize_draw',
    'notifications_custom_email',
    'business_parter_post_id',           // TIPPFEHLER: absichtlich (steht in DB)
    'onesignal_player_ids',
    'first_name',
    'last_name',
];

// --- AJAX-Action-Namen ---
// Quelle: functions/ajax.php
$ajax_actions = [
    'more_post_ajax',
    'edit_user',
    'editor_image_upload',
    'onesignal_player_id',
    'accounts_ajax',
    'single_ajax',
];

// --- WP-Option-Namen ---
$wp_options = [
    'sending_emails',
];

// Anzahl-Checks: schlagen an wenn jemand einen Key hinzufügt oder entfernt
// ohne diesen Test zu aktualisieren
assert_equals(12, count($post_meta_keys),  'post-meta-keys: anzahl unverändert (12)');
assert_equals(9,  count($user_meta_keys),  'user-meta-keys: anzahl unverändert (9)');
assert_equals(6,  count($ajax_actions),    'ajax-actions: anzahl unverändert (6)');
assert_equals(1,  count($wp_options),      'wp-options: anzahl unverändert (1)');

// Spot-checks für die kritischen Tippfehler
assert_contains('notificatiosn_triggered_by', $post_meta_keys, 'tippfehler notificatiosn_triggered_by ist dokumentiert');
assert_contains('business_parter_post_id',    $user_meta_keys, 'tippfehler business_parter_post_id ist dokumentiert');

echo "\nDie obigen Anzahl-Checks schlagen an wenn Keys hinzugefügt oder entfernt\nwerden. Das ist Absicht – bitte bewusst anpassen.\n\n";

exit_with_result();
```

- [ ] **Schritt 2: Test ausführen**

```bash
php /Users/paulkaufmann/Workspace/insider/dev/tests/php/test_meta_contracts.php
```

Erwartete Ausgabe: alle `[PASS]`

- [ ] **Schritt 3: Committen**

```bash
git add tests/php/test_meta_contracts.php
git commit -m "test: meta-key contracts und ajax-action inventar"
```

---

## Task 5: Gesamtlauf und CI-Integration

**Files:**
- Modify: `dev/.github/workflows/e2e.yml` (PHP-Tests ergänzen)

- [ ] **Schritt 1: Alle PHP-Tests lokal ausführen**

```bash
bash /Users/paulkaufmann/Workspace/insider/dev/tests/php/run_all.sh
```

Erwartete Ausgabe:
```
--- test_password.php ---
=== is_valid_password ===
[PASS] ...

--- test_forcelogin.php ---
...

--- test_meta_contracts.php ---
...

✅ Alle PHP-Tests bestanden.
```

- [ ] **Schritt 2: PHP-Tests in GitHub Actions Workflow einbinden**

In `dev/.github/workflows/e2e.yml` einen separaten Job für PHP-Tests ergänzen:

```yaml
  php-tests:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v4

      - name: Setup PHP
        uses: shivammathur/setup-php@v2
        with:
          php-version: '8.4'

      - name: Run PHP logic tests
        working-directory: dev
        run: bash tests/php/run_all.sh
```

Diesen Block auf der gleichen Einrückungsebene wie der `e2e`-Job einfügen (als zweiter Job im `jobs:`-Block).

- [ ] **Schritt 3: `package.json` Test-Script für PHP ergänzen**

```json
{
  "scripts": {
    "test:e2e": "playwright test",
    "test:e2e:headed": "playwright test --headed",
    "test:e2e:report": "playwright show-report",
    "test:php": "bash tests/php/run_all.sh",
    "test": "npm run test:php && npm run test:e2e"
  }
}
```

- [ ] **Schritt 4: Final committen**

```bash
git add .github/workflows/e2e.yml package.json
git commit -m "ci: php-tests in github actions und npm test script"
```
