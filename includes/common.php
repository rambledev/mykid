<?php
/**
 * Shared helpers for the top-level demo pages (main index + Package A/B placeholders).
 */
declare(strict_types=1);

date_default_timezone_set('Asia/Bangkok');

define('MYKID_ROOT', dirname(__DIR__));

/** Role labels + pastel colours shared by every package page. */
const MYKID_ROLES = [
    'super_admin' => ['label' => 'Super Admin', 'emoji' => '🪐', 'class' => 'role-chip--super'],
    'teacher'   => ['label' => 'ครู',       'emoji' => '👩‍🏫', 'class' => 'role-chip--teacher'],
    'parent'    => ['label' => 'ผู้ปกครอง', 'emoji' => '👨‍👩‍👧', 'class' => 'role-chip--parent'],
    'admin'     => ['label' => 'Admin',     'emoji' => '🛡️', 'class' => 'role-chip--admin'],
    'executive' => ['label' => 'ผู้บริหาร',  'emoji' => '💼', 'class' => 'role-chip--executive'],
];

function e($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

/** Asset URL with cache-busting version. $prefix is the relative path back to the project root. */
function root_asset(string $path, string $prefix = ''): string
{
    $file = MYKID_ROOT . '/' . $path;
    $version = is_file($file) ? (string) filemtime($file) : '1';
    return $prefix . $path . '?v=' . $version;
}

function logo_svg(): string
{
    return '<svg viewBox="0 0 48 48" aria-hidden="true"><rect width="48" height="48" rx="14" fill="#FFE9A8"/>'
        . '<g stroke="#FFB547" stroke-width="2.5" stroke-linecap="round"><line x1="24" y1="5" x2="24" y2="9"/><line x1="24" y1="39" x2="24" y2="43"/><line x1="5" y1="24" x2="9" y2="24"/><line x1="39" y1="24" x2="43" y2="24"/><line x1="10.6" y1="10.6" x2="13.4" y2="13.4"/><line x1="34.6" y1="34.6" x2="37.4" y2="37.4"/><line x1="10.6" y1="37.4" x2="13.4" y2="34.6"/><line x1="34.6" y1="13.4" x2="37.4" y2="10.6"/></g>'
        . '<circle cx="24" cy="24" r="10.5" fill="#FFC94D"/><circle cx="20.5" cy="23" r="1.5" fill="#5A3A26"/><circle cx="27.5" cy="23" r="1.5" fill="#5A3A26"/>'
        . '<path d="M20.5 27q3.5 3 7 0" stroke="#5A3A26" stroke-width="1.6" fill="none" stroke-linecap="round"/>'
        . '<circle cx="18" cy="26.5" r="1.5" fill="#FF8FB1" opacity=".6"/><circle cx="30" cy="26.5" r="1.5" fill="#FF8FB1" opacity=".6"/></svg>';
}

/** Soft background decorations (clouds, sun, stars, rainbow). Purely visual. */
function sky_decor(): string
{
    $cloud = '<svg viewBox="0 0 112 68"><path fill="currentColor" d="M28 64C14 64 4 55 4 43s10-21 23-21c3-12 14-20 27-20 15 0 27 10 29 24h3c12 0 22 9 22 20s-10 18-22 18z"/></svg>';
    $star = '<svg viewBox="0 0 24 24"><path fill="currentColor" d="M12 1.5l3.1 6.6 7.2.9-5.3 5 1.4 7.1L12 17.6l-6.4 3.5L7 14 1.7 9l7.2-.9z"/></svg>';
    $rainbow = '<svg viewBox="0 0 120 64"><g fill="none" stroke-width="8" stroke-linecap="round"><path d="M12 60a48 48 0 0 1 96 0" stroke="#FFC4C4"/><path d="M22 60a38 38 0 0 1 76 0" stroke="#FFE9A8"/><path d="M32 60a28 28 0 0 1 56 0" stroke="#D4F5DF"/><path d="M42 60a18 18 0 0 1 36 0" stroke="#BFE7FF"/></g></svg>';

    return '<div class="sky" aria-hidden="true">'
        . '<span class="sky__item sky__cloud sky__cloud--1">' . $cloud . '</span>'
        . '<span class="sky__item sky__cloud sky__cloud--2">' . $cloud . '</span>'
        . '<span class="sky__item sky__cloud sky__cloud--3">' . $cloud . '</span>'
        . '<span class="sky__item sky__star sky__star--1">' . $star . '</span>'
        . '<span class="sky__item sky__star sky__star--2">' . $star . '</span>'
        . '<span class="sky__item sky__star sky__star--3">' . $star . '</span>'
        . '<span class="sky__item sky__rainbow">' . $rainbow . '</span>'
        . '</div>';
}

function role_chips(array $roles): string
{
    $html = '';
    foreach ($roles as $role) {
        $r = MYKID_ROLES[$role];
        $html .= '<span class="role-chip ' . $r['class'] . '">' . $r['emoji'] . ' ' . e($r['label']) . '</span>';
    }
    return $html;
}
