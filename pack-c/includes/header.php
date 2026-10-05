<?php
/**
 * Package C — page header.
 *
 * Set before including:
 *   $page = ['id' => 'teacher-home', 'title' => '...', 'layout' => 'app'|'auth'|'landing', 'nav' => 'home', 'theme' => 'teacher'|'parent'|'landing'];
 *   $user (optional) — the logged-in user for app pages.
 */
declare(strict_types=1);

$page += ['id' => '', 'title' => '', 'layout' => 'app', 'nav' => '', 'theme' => null];
$user ??= null;
$theme = $page['theme'] ?? ($user['role'] ?? 'landing');
$themeColor = ['teacher' => '#DFF4FF', 'parent' => '#FFEAF2'][$theme] ?? '#FFF6E9';
$school = get_school();
$fullTitle = ($page['title'] !== '' ? $page['title'] . ' | ' : '') . 'Mykid Basic';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="<?= e($themeColor) ?>">
    <meta name="robots" content="noindex">
    <title><?= e($fullTitle) ?></title>
    <link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
</head>
<body class="layout-<?= e($page['layout']) ?> theme-<?= e($theme) ?>" data-page="<?= e($page['id']) ?>">
<div class="bg-decor" aria-hidden="true">
    <span class="bg-decor__cloud bg-decor__cloud--1"><?= decor_svg('cloud') ?></span>
    <span class="bg-decor__cloud bg-decor__cloud--2"><?= decor_svg('cloud') ?></span>
    <span class="bg-decor__star bg-decor__star--1"><?= decor_svg('star') ?></span>
    <span class="bg-decor__star bg-decor__star--2"><?= decor_svg('star') ?></span>
</div>

<?php if ($page['layout'] === 'app' && $user): ?>
    <header class="appbar">
        <a class="appbar__brand" href="<?= e(url(dashboard_path($user['role']))) ?>">
            <span class="appbar__logo"><?= logo_svg() ?></span>
            <span class="appbar__text">
                <small>Mykid Basic · <?= $user['role'] === 'teacher' ? 'ครู' : 'ผู้ปกครอง' ?></small>
                <strong><?= e($school['name']) ?></strong>
            </span>
        </a>
        <a class="appbar__user" href="<?= e(url($user['role'] . '/account.php')) ?>" aria-label="บัญชีของฉัน">
            <span aria-hidden="true"><?= $user['emoji'] ?></span>
        </a>

        <!-- Current user + permission scope (always from the session) -->
        <div class="appbar__scope">
            <span class="appbar__who">
                <span class="appbar__who-emoji" aria-hidden="true"><?= $user['emoji'] ?></span>
                <span class="appbar__who-text">
                    <strong><?= e($user['name']) ?></strong>
                    <small><?= e($user['scopeLabel']) ?></small>
                </span>
            </span>
            <span class="scope-lock" title="บัญชีนี้เข้าถึงได้<?= e($user['lockLabel']) ?>">🔒 <?= e($user['lockLabel']) ?></span>
            <a class="appbar__logout" href="<?= e(url('logout.php')) ?>" aria-label="ออกจากระบบ" title="ออกจากระบบ"><?= icon('logout') ?></a>
        </div>
    </header>
<?php endif; ?>

<main class="page page--<?= e($page['layout']) ?>" id="main">
