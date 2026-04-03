<?php
// dev/tests/php/test_forcelogin.php

require_once __DIR__ . '/helpers.php';

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
// Hinweis: /member-login wird vom Force-Login-Plugin selbst freigegeben,
// nicht von spk_forcelogin_bypass() – daher hier nicht getestet.
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
