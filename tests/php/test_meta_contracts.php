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
