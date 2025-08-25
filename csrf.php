<?php
// Simple CSRF helper. Include this file on pages with forms.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_validate(?string $token): bool {
    if (!$token || empty($_SESSION['csrf_token'])) {
        return false;
    }
    $isValid = hash_equals($_SESSION['csrf_token'], $token);
    if ($isValid) {
        // Rotate token after successful validation
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $isValid;
}

