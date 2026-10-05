<?php
/**
 * Package C bootstrap — included at the top of every Package C page and the API.
 */
declare(strict_types=1);

date_default_timezone_set('Asia/Bangkok');

define('MYKID_ENV', getenv('MYKID_ENV') ?: 'development');
define('MYKID_DEBUG', MYKID_ENV === 'development');
define('PACKC_ROOT', realpath(dirname(__DIR__)));
define('PACKC_STORAGE_FILE', PACKC_ROOT . '/storage/demo-state.json');

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name('MYKIDC');
    session_start();
}

require_once __DIR__ . '/helpers.php';
require_once __DIR__ . '/mock-data.php';
require_once __DIR__ . '/repository.php';
require_once __DIR__ . '/store.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/data-filter.php';
require_once __DIR__ . '/components.php';
