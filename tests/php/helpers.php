<?php
// dev/tests/php/helpers.php

$GLOBALS['__test_failed'] = false;

// WordPress-Stubs: werden von allen Testskripten benötigt
if (!function_exists('add_filter')) {
    function add_filter(string $hook, mixed $callback, int $priority = 10, int $accepted_args = 1): void {}
}
if (!function_exists('add_action')) {
    function add_action(string $hook, mixed $callback, int $priority = 10, int $accepted_args = 1): void {}
}
if (!function_exists('is_user_logged_in')) {
    function is_user_logged_in(): bool { return false; }
}
if (!function_exists('wp_rand')) {
    function wp_rand(int $min = 0, int $max = 0): int { return rand($min, $max); }
}

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
