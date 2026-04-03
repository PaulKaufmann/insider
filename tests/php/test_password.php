<?php
// dev/tests/php/test_password.php

require_once __DIR__ . '/helpers.php';

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
