<?php
/**
 * Package C — generic helpers (escaping, URLs, dates, logging, CSRF, flash messages).
 */
declare(strict_types=1);

/** Dev log to the PHP error log. Format: [Mykid][Scope] message {context} */
function mk_log(string $scope, string $message, array $context = []): void
{
    if (!MYKID_DEBUG) {
        return;
    }
    $ctx = $context ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : '';
    error_log('[Mykid][' . $scope . '] ' . $message . $ctx);
}

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/**
 * Relative path from the current script back to the Package C root ('' or '../'),
 * so the demo works whether it is served from the web root or a sub-folder.
 */
function base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    $script = realpath($_SERVER['SCRIPT_FILENAME'] ?? '') ?: PACKC_ROOT . '/index.php';
    $rel = trim(str_replace('\\', '/', substr(dirname($script), strlen(PACKC_ROOT))), '/');
    $base = $rel === '' ? '' : str_repeat('../', count(explode('/', $rel)));
    return $base;
}

function url(string $path = ''): string
{
    return base_path() . ltrim($path, '/');
}

function asset(string $path): string
{
    $file = PACKC_ROOT . '/assets/' . $path;
    $version = is_file($file) ? (string) filemtime($file) : '1';
    return url('assets/' . $path) . '?v=' . $version;
}

function redirect(string $path): never
{
    mk_log('Helpers', 'redirect', ['to' => $path]);
    header('Location: ' . url($path));
    exit;
}

function thai_date(?int $timestamp = null): string
{
    $timestamp ??= time();
    $days = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสบดี', 'ศุกร์', 'เสาร์'];
    $months = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน',
        'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];

    return 'วัน' . $days[(int) date('w', $timestamp)] . 'ที่ ' . date('j', $timestamp) . ' '
        . $months[(int) date('n', $timestamp)] . ' ' . ((int) date('Y', $timestamp) + 543);
}

/** "10:30 น." — prefixed with a short date when it is not today. */
function thai_time(?string $datetime): string
{
    if (!$datetime) {
        return '-';
    }
    $ts = strtotime($datetime) ?: time();
    $time = date('H:i', $ts) . ' น.';
    return date('Y-m-d', $ts) === date('Y-m-d') ? $time : date('j/n', $ts) . ' · ' . $time;
}

function is_today(?string $datetime): bool
{
    return $datetime !== null && date('Y-m-d', strtotime($datetime) ?: 0) === date('Y-m-d');
}

function csrf_token(): string
{
    if (empty($_SESSION['csrf'])) {
        $_SESSION['csrf'] = bin2hex(random_bytes(16));
    }
    return $_SESSION['csrf'];
}

function verify_csrf(?string $token): bool
{
    return is_string($token) && hash_equals(csrf_token(), $token);
}

/** Set (2 args) or read-once (1 arg) a flash value; toasts may be ['message' => ..., 'type' => ...]. */
function flash(string $key, string|array|null $message = null): string|array|null
{
    if ($message !== null) {
        $_SESSION['flash'][$key] = $message;
        return null;
    }
    $value = $_SESSION['flash'][$key] ?? null;
    unset($_SESSION['flash'][$key]);
    return $value;
}

function json_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    header('Content-Type: application/json; charset=UTF-8');
    header('Cache-Control: no-store');
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

/** Render a callback into a string (used to reuse components inside API responses). */
function capture(callable $render): string
{
    ob_start();
    $render();
    return (string) ob_get_clean();
}
