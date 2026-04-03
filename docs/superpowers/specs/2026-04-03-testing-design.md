# Testing Design: insider/inseider

**Datum:** 2026-04-03  
**Status:** Approved

## Ziel

Regressionssicherheit beim Refactoring und Absicherung der Geschäftslogik. Tests sollen festzurren was aktuell funktioniert, damit zukünftige Änderungen sicher eingeführt werden können.

## Scope

- Playwright E2E gegen die Dev-Instanz (dev.muxd.de)
- Einfache PHP-Skripte für isolierte Logikfunktionen (kein Framework)
- GitHub Actions CI (kein nennenswerter Mehraufwand gegenüber lokalem Setup)

Explizit ausgeschlossen: PHPUnit mit WordPress-Bootstrap, TinyMCE-Plugins, Genesis Framework Internals, echte SMTP/Vimeo/OneSignal-Aufrufe.

---

## Teil 1: Playwright E2E

### Projektstruktur

```
dev/
├── inseider/                        ← Theme (unverändert)
├── tests/
│   ├── e2e/
│   │   ├── auth.setup.ts            ← Login einmal pro Rolle, Session speichern
│   │   ├── login.spec.ts            ← Auth & Zugriffskontrolle
│   │   ├── feed.spec.ts             ← Feed & AJAX-Paginierung
│   │   ├── post.spec.ts             ← Post-Erstellung
│   │   └── permissions.spec.ts      ← Berechtigungen nach Rolle
│   ├── php/                         ← PHP-Skripte (Teil 2)
│   ├── .auth/                       ← Gespeicherte Sessions (nicht committen)
│   │   ├── admin.json
│   │   ├── author.json
│   │   ├── subscriber.json
│   │   └── business-partner.json
│   ├── .env.example                 ← Credentials-Template
│   └── playwright.config.ts
├── package.json
└── .github/
    └── workflows/
        └── e2e.yml
```

### Sessions & Credentials

- Pro Rolle eine gespeicherte Browser-Session (`storageState`) – kein Re-Login vor jedem Test
- Credentials in `.env` (lokal) bzw. GitHub Secrets (CI), nie committen
- `.env.example` dokumentiert alle nötigen Variablen

```
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

### Testfälle nach Priorität

**login.spec.ts – Auth & Zugriffskontrolle**
- Nicht eingeloggt → Redirect auf Login-Seite
- Login mit gültigen Credentials → Feed sichtbar
- Login mit falschen Credentials → Fehlermeldung sichtbar
- Logout funktioniert

**feed.spec.ts – Feed & AJAX**
- Feed lädt mindestens einen Post
- "Mehr laden"-Button lädt weitere Posts nach (AJAX)
- Kategorie-Wechsel zeigt Posts der richtigen Kategorie

**post.spec.ts – Post-Erstellung** *(als Author)*
- Post mit Text erfolgreich erstellen
- Post mit Bild erfolgreich erstellen
- Pflichtfelder-Validierung schlägt korrekt an

**permissions.spec.ts – Berechtigungen nach Rolle**
- Subscriber: kein "Post erstellen" sichtbar
- Admin: Benutzerverwaltung sichtbar
- Business Partner: wird auf seinen zugewiesenen Post beschränkt, andere Inhalte nicht zugänglich

### GitHub Actions

`.github/workflows/e2e.yml` läuft bei jedem Push. Credentials kommen aus GitHub Secrets. Playwright-Browser werden im CI gecacht.

---

## Teil 2: PHP-Skripte

### Grundprinzip

Einfache PHP-Skripte ohne Composer, ohne PHPUnit, ohne WordPress-Bootstrap. Nur reine Logikfunktionen die kein laufendes WordPress brauchen. Jedes Skript gibt `[PASS]`/`[FAIL]` aus und setzt den Exit-Code – CI-kompatibel.

### Struktur

```
tests/php/
├── run_all.sh              ← Führt alle Skripte aus, kombinierter Exit-Code
├── helpers.php             ← assert()-Hilfsfunktionen, [PASS]/[FAIL]-Output
├── test_password.php       ← is_valid_password() alle Regeln + Fehlertexte
├── test_forcelogin.php     ← spk_forcelogin_bypass() öffentliche vs. gesperrte URLs
└── test_meta_contracts.php ← Inventar aller Meta-Keys und AJAX-Actions
```

### Testfälle

**test_password.php**
- Gültiges Passwort wird akzeptiert
- Zu kurz (< 8 Zeichen) wird abgelehnt
- Kein Großbuchstabe → abgelehnt
- Keine Ziffer → abgelehnt
- Kein Sonderzeichen → abgelehnt
- Leerzeichen → abgelehnt
- Fehlermeldungstexte als Snapshot: schlägt an wenn jemand die deutschen Strings ändert

**test_forcelogin.php**
- `/member-login` → bypass = true
- `/impressum` → bypass = true
- `/home` → bypass = false
- `/wp-admin` → bypass = false

**test_meta_contracts.php**
- Inventar aller bekannten Post-Meta-Keys, User-Meta-Keys und AJAX-Action-Namen
- Tippfehler explizit dokumentiert und als korrekt markiert:
  - `notificatiosn_triggered_by`
  - `business_parter_post_id`
- Schlägt fehl wenn die Liste geändert wird ohne bewusste Entscheidung

### Beispiel-Output

```
[PASS] is_valid_password: valid password accepted
[PASS] is_valid_password: too short rejected
[PASS] is_valid_password: no uppercase rejected
[FAIL] is_valid_password: error message changed
       expected: "Das Passwort muss mindestens 8 Zeichen lang sein."
       got:      "Passwort zu kurz."
```

---

## Nicht in Scope

- PHPUnit mit WordPress-Bootstrap (zu viel Setup, Nutzen nicht proportional)
- Mocking von SMTP, Vimeo API, OneSignal API
- TinyMCE-Plugin-Tests
- Genesis Framework Internals
- WooCommerce-Integration

## Entscheidungen & Begründungen

| Entscheidung | Begründung |
|---|---|
| E2E gegen echte Dev-Instanz statt Mocks | Kein Phantom-Grün; testet was wirklich passiert |
| Sessions pro Rolle cached | Spart Zeit, kein Re-Login vor jedem Test |
| PHP-Skripte statt PHPUnit | Kein Composer-Overhead für überschaubare Logik |
| Meta-Key-Contract-Test | Sicherheitsnetz gegen versehentliche Umbenennung produktiver DB-Keys |
| CI via GitHub Actions | Kein Mehraufwand, da Playwright-Support out-of-the-box |
