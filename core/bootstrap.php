<?php
/**
 * Mykid core engine — shared by Package A (Mykid Full) and Package B (Mykid Standard).
 *
 * A package boots it from its own includes/bootstrap.php after defining MK_PKG_ROOT.
 * Package-specific parts live in that package's includes/: config.php (features, roles),
 * mock-data.php (schools, people, accounts) and permissions.php (role → scope → read/write).
 *
 * Package C is independent and untouched; the engine only READS its mock data
 * (pack-c/includes/mock-data.php) so all packages share the same school / students.
 */
declare(strict_types=1);

defined('MK_PKG_ROOT') || exit('Direct access not allowed');

date_default_timezone_set('Asia/Bangkok');

define('MK_ROOT', realpath(__DIR__ . '/..'));
define('MK_CORE', __DIR__);
define('MYKID_ENV', getenv('MYKID_ENV') ?: 'development');
define('MYKID_DEBUG', MYKID_ENV === 'development');

require_once MK_CORE . '/helpers.php';

/** Package configuration (pack-x/includes/config.php). */
function pkg(): array
{
    static $config = null;
    return $config ??= require MK_PKG_ROOT . '/includes/config.php';
}

function has_feature(string $feature): bool
{
    return in_array($feature, pkg()['modules'], true);
}

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_name(pkg()['session']);
    session_start();
}

require_once MK_ROOT . '/pack-c/includes/mock-data.php'; // shared source data (read-only)
require_once MK_CORE . '/catalog.php';
require_once MK_CORE . '/schema.php';
require_once MK_CORE . '/seed.php';
require_once MK_PKG_ROOT . '/includes/mock-data.php';
require_once MK_PKG_ROOT . '/includes/cctv-data.php';
require_once MK_CORE . '/store.php';
require_once MK_CORE . '/permissions.php';
require_once MK_CORE . '/cctv.php';
require_once MK_CORE . '/media.php';
require_once MK_CORE . '/pickup.php';
require_once MK_CORE . '/auth.php';
require_once MK_CORE . '/data-filter.php';
require_once MK_CORE . '/components.php';
require_once MK_CORE . '/charts.php';
require_once MK_CORE . '/layout.php';

/** Render a core page as the given role (used by the small page files in each package). */
function mk_render(string $page, ?string $role = null): void
{
    if ($role !== null) {
        require_role($role);
    }
    $file = MK_CORE . '/pages/' . $page . '.php';
    mk_log('Core', 'mk_render', ['page' => $page, 'role' => $role]);
    require $file;
}
