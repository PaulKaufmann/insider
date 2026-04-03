# insider / inseider – CLAUDE.md

## Projektübersicht

**Produkt:** insider (Codename: inseider) – ein internes Community-Portal für Sparkassen-Mitarbeiter  
**Theme-Typ:** Genesis Framework Child Theme  
**Sprache:** PHP (WordPress), JavaScript (jQuery), CSS  
**Version:** 2.3.0 (config.php) / 2.2 (style.css)  
**Status:** Produktiv im Einsatz bei mehreren Sparkassen-Instanzen

Das Theme ermöglicht Mitarbeitern, Posts mit verschiedenen Medientypen (Bilder, Videos via Vimeo, Dokumente) zu veröffentlichen, mit einem rollenbasierten Zugriffssystem, Push-Benachrichtigungen (OneSignal), E-Mail-Benachrichtigungen und einem Gewinnspiel-System.

---

## Wichtigste Constraints

### Produktionskompatibilität hat oberste Priorität
- Das Produkt läuft produktiv auf mehreren Instanzen
- **Keine Änderungen an WordPress-sichtbaren Parametern** (Post-Meta-Keys, Option-Names, User-Meta-Keys, Taxonomie-Slugs, Hook-Namen, AJAX-Action-Namen), auch wenn Tippfehler darin sind
- Beispiele für absichtlich beibehaltene Tippfehler:
  - `notificatiosn_triggered_by` (Post-Meta-Key)
  - `business_parter_post_id` (User-Meta-Key)
  - `inseider` (Theme-Name/Verzeichnis)
  - Funktion `can_print_debuggs()` (doppeltes g)
- Bestehende Datenbankschemas und API-Contracts dürfen nicht gebrochen werden

### WordPress-Konventionen
- Das Theme ist ein **Child Theme** des Genesis Frameworks – immer im Kontext von Genesis denken
- Keine Änderungen an `style.css`-Header ohne explizite Anfrage (Theme Name, Template, etc.)
- Alle AJAX-Handler sind über `wp_ajax_` und `wp_ajax_nopriv_` registriert

---

## Verzeichnisstruktur

```
inseider/
├── assets/              # XLS-Template für Massenimport von Benutzern
├── config/              # Genesis-Theme-Konfiguration
│   ├── appearance.php
│   ├── child-theme-settings.php
│   ├── responsive-menus.php
│   └── theme-supports.php
├── css/                 # Ergänzende Stylesheets (Timepicker, TinyMCE-Skin)
├── fonts/               # Schriftarten
├── functions/           # Modulare Funktionen
│   ├── ajax.php         # AJAX-Handler (more posts, user edit, image upload, OneSignal)
│   ├── comments.php     # Kommentar-System mit Inline-Editing
│   ├── frontend.php     # Frontend-JS (Pull-to-Refresh, Textarea, Datepicker)
│   ├── notifications.php # E-Mail & Push-Benachrichtigungen
│   ├── posting.php      # Post-Erstellung, Vimeo-Upload, Gewinnspiele
│   ├── search.php       # Suchformular
│   ├── security.php     # Passwort, Cookies, Zugriffskontrolle
│   └── user.php         # Benutzer erstellen/bearbeiten
├── images/              # Theme-Bilder
├── individual/          # Instanz-spezifische Konfiguration (wird pro Kunde angepasst)
│   ├── config.php       # Hauptkonfiguration (1459 Zeilen) – enthält Credentials!
│   ├── config_dev.php   # Entwicklungskonfiguration
│   ├── strings.php      # Deutsche Strings/Übersetzungen
│   └── unit-test.php    # Minimaler Sicherheitstest
├── js/                  # JavaScript
│   ├── ajax_inseider_v1.2.js  # Haupt-AJAX-Client
│   ├── Sw.js            # Service Worker
│   ├── file-uploader/   # Datei-Upload-Bibliothek
│   ├── heic2any/        # HEIC-Konvertierung (iOS)
│   └── tinymce/         # TinyMCE-Editor (komplett eingebettet)
├── lib/                 # Bibliotheken
│   ├── class.fileuploader.php  # Chunked File Upload
│   ├── customize.php    # WordPress Customizer
│   ├── helper-functions.php
│   ├── output.php       # Dynamische Inline-Styles
│   └── wpforms.php
├── page-templates/      # Seitenvorlagen
│   ├── account.php      # Benutzerprofil
│   ├── change-password.php
│   ├── communication.php
│   ├── frontenduploading.php   # Datei-Upload-Interface
│   ├── frontendUserManagement.php  # Benutzerverwaltung
│   ├── get_password.php
│   ├── list.php         # Haupt-Feed/Listing
│   ├── logIn.php        # Login-Seite
│   ├── sort.php         # Content-Sortierung (APTO)
│   └── tescht.php       # Admin-Utility-Seite (2700+ Zeilen!)
├── uploading/           # Upload-Hilfsdateien
├── functions.php        # Haupt-Theme-Funktionen (851 Zeilen)
├── single.php           # Single-Post-Template (1703 Zeilen)
├── style.css            # Haupt-Stylesheet (92KB)
└── 404.php
```

---

## Rollen & Berechtigungen

| WP-Rolle | Anzeigename | Beschreibung |
|----------|-------------|--------------|
| `subscriber` | Benutzer | Normaler Mitarbeiter, liest und kommentiert |
| `author` | Autor | Kann Posts veröffentlichen |
| `contributor` | Redaktion | Redaktionelle Rechte |
| `administrator` | Administrator | Voller Zugriff |
| `business_partner` | Business Partner | Händler, eingeschränkt auf einen Post |
| `developer` | Developer | Entwickler-Testrolle |

---

## Kern-Funktionen

### Feed-System
5 Haupt-Feeds, konfiguriert in `individual/config.php`:
- **Home** (Cat ID: 1) – Hauptfeed
- **Stellen** (Cat ID: 63) – Stellenangebote
- **SchwarzesBrett** (Cat ID: 69) – Schwarzes Brett
- **Gewinnspiele** (Cat ID: 68) – Gewinnspiele
- **Boni** (Cat ID: 70) – Bonusinformationen

Die Kategorie-IDs sind Defaults – jede Instanz konfiguriert eigene IDs in `config.php`.

### Medientypen
- `image` – Bilder
- `video` – Vimeo-Videos
- `file` – Dokumente
- Custom-Typen per Instanz konfigurierbar

### Benachrichtigungssystem
Strategien (in `individual/config.php` pro Kategorie konfigurierbar):
- `NEVER` – Keine Benachrichtigungen
- `ALWAYS` – Immer benachrichtigen
- `MANUAL` – Nur bei explizitem Auslösen
- `DRAFT_STATUS` – Bei Statusänderungen

### Post-Meta-Keys (unveränderlich!)
```
videoId
media_type
media_info
documents_attachment_id
content_attachment_ids
notify
post_later
post_later_timeStr
favorite_posts
notifications_categories
notifications_posts
notifications_prize_draw
notifications_custom_email
business_parter_post_id          ← Tippfehler: absichtlich beibehalten
onesignal_player_ids
notificatiosn_triggered_by       ← Tippfehler: absichtlich beibehalten
post_edited_after_last_notification
notified_time
```

### AJAX-Actions (unveränderlich!)
```
more_post_ajax
edit_user_ajax
editor_image_upload_ajax
onesignal_player_id_ajax
```

---

## Abhängigkeiten

### WordPress-Plugins (erforderlich)
- **Genesis Framework** – Parent Theme (zwingend erforderlich)
- **V Force Login** – Zwingt nicht angemeldete Nutzer zum Login
- **OneSignal for WordPress** – Push-Benachrichtigungen

### WordPress-Plugins (optional/instanzabhängig)
- **WooCommerce** – E-Commerce (für manche Instanzen)
- **WPForms** – Formulare
- **APTO** – Custom post ordering (für sort.php)
- **Expire Passwords** – Passwortablauf (`Expire_Passwords` Klasse wird erwartet)

---

## Entwicklungshinweise

### Konfiguration pro Instanz
- `individual/config.php` ist die zentrale Konfigurationsdatei, die **pro Instanz** individuell angepasst wird – jede Sparkasse hat ihre eigene Version
- Enthält Credentials (SMTP, Vimeo-Token, OneSignal) – **nicht in öffentliche Repos committen**
- `individual/config_dev.php` ist die Entwicklungsversion (dev.muxd.de)
- `individual/strings.php` enthält instanzspezifische deutsche Texte
- **Tests laufen immer gegen eine konkrete Instanz** – die `tests/.env` konfiguriert welche. Instanz-spezifische Werte (Kategorie-IDs, öffentliche URLs, Label-Texte) gehören in die `.env`, nicht hardcoded in die Tests

### Umgebungserkennung
- `is_dev_version()` – Gibt an, ob Dev-Modus aktiv (Standard: `false`)
- `can_print_debuggs()` – Debug-Ausgaben erlaubt (Standard: `false`)

### Tippfehler im Produktionscode
Folgende Bezeichner enthalten absichtlich beibehaltene Tippfehler (da in Produktion):
- Theme-Verzeichnis: `inseider` (statt `insider`)
- Funktion: `can_print_debuggs()` (doppeltes g)
- Post-Meta: `notificatiosn_triggered_by`
- User-Meta: `business_parter_post_id`
- Seiten-Template: `tescht.php`
- `get_sick_note_Email()`, `get_posting_admins()` etc. mit gemischtem Casing

### Kritische Dateien
- `single.php` (1703 Zeilen) – Haupt-Post-Template mit fast der gesamten Post-Darstellungslogik
- `individual/config.php` (1459 Zeilen) – Gesamte Konfiguration, enthält Credentials
- `page-templates/tescht.php` (2700+ Zeilen) – Admin-Utility mit direkten DB-Operationen
- `functions/posting.php` (600+ Zeilen) – Kernlogik für Post-Erstellung

---

## Refactoring-Regeln

1. **Keine Breaking Changes** – alle WordPress-sichtbaren Bezeichner bleiben unverändert
2. **Keine Kommentare** auf selbsterklärendem Code, nur bei komplexer Logik
3. **Feste Dependency-Versionen** – kein `^` in package.json falls vorhanden
4. Vor Änderungen an einer Datei: immer erst lesen und verstehen
5. Refactoring schrittweise mit Rückwärtskompatibilität – keine Big-Bang-Rewrites
6. `tescht.php` und `unit-test.php` sind Debug/Admin-Tools, keine produktiven Features

---

## Testing-Konzept

Siehe separate Sektion unten. Tests dürfen **keinen Live-State** verändern und müssen auf einer Testinstanz laufen.
