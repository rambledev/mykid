<?php
/**
 * Package C — reusable UI components.
 *
 * Components only render data handed to them through $ctx (built by page_context() in
 * data-filter.php), so they can never show data outside the user's scope.
 *
 * Dynamic blocks are rendered through render_fragment() so the exact same markup is used
 * on first page load and in API responses (the browser swaps [data-fragment] contents).
 */
declare(strict_types=1);

/* ============================================================================
 * Icons & artwork (inline SVG — no external images)
 * ========================================================================= */

function icon(string $name, string $class = ''): string
{
    static $paths = [
        'home'     => '<path d="M3 10.5 12 3l9 7.5V20a1.5 1.5 0 0 1-1.5 1.5H15v-6H9v6H4.5A1.5 1.5 0 0 1 3 20z"/>',
        'calendar' => '<rect x="3" y="4.5" width="18" height="17" rx="3"/><path d="M8 2.5v4M16 2.5v4M3 10h18"/>',
        'food'     => '<path d="M3 12h18a9 9 0 0 1-18 0z"/><path d="M8 8c0-1.5 1-2 1-3.5M12 8c0-1.5 1-2 1-3.5M16 8c0-1.5 1-2 1-3.5"/>',
        'status'   => '<circle cx="12" cy="12" r="9"/><circle cx="12" cy="12" r="3.5" fill="currentColor"/>',
        'user'     => '<circle cx="12" cy="8" r="4"/><path d="M4 21v-1a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v1"/>',
        'child'    => '<circle cx="12" cy="12" r="9"/><path d="M8.5 14.5s1.3 1.8 3.5 1.8 3.5-1.8 3.5-1.8"/><path d="M9 10h.01M15 10h.01" stroke-width="3"/>',
        'plus'     => '<path d="M12 5v14M5 12h14"/>',
        'edit'     => '<path d="M12 20h9"/><path d="M16.5 3.5a2.1 2.1 0 0 1 3 3L7 19l-4 1 1-4z"/>',
        'trash'    => '<path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>',
        'logout'   => '<path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5M21 12H9"/>',
        'back'     => '<path d="M15 18l-6-6 6-6"/>',
        'next'     => '<path d="M9 18l6-6-6-6"/>',
        'refresh'  => '<path d="M23 4v6h-6"/><path d="M20.5 15a9 9 0 1 1-2.1-9.4L23 10"/>',
        'clock'    => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/>',
        'phone'    => '<rect x="6" y="2" width="12" height="20" rx="3"/><path d="M11 18h2"/>',
        'lock'     => '<rect x="4" y="11" width="16" height="10" rx="2.5"/><path d="M8 11V7a4 4 0 0 1 8 0v4"/>',
        'check'    => '<path d="M20 6 9 17l-5-5"/>',
        'close'    => '<path d="M18 6 6 18M6 6l12 12"/>',
        'users'    => '<circle cx="9" cy="8" r="4"/><path d="M2 21v-1a6 6 0 0 1 6-6h2a6 6 0 0 1 6 6v1"/><path d="M16 4.1a4 4 0 0 1 0 7.8M19 14.5a6 6 0 0 1 3 5.5v1"/>',
    ];
    $class = trim('icon ' . $class);
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">'
        . ($paths[$name] ?? '') . '</svg>';
}

function logo_svg(): string
{
    return '<svg viewBox="0 0 48 48" aria-hidden="true"><rect width="48" height="48" rx="14" fill="#FFE9A8"/>'
        . '<g stroke="#FFB547" stroke-width="2.5" stroke-linecap="round"><line x1="24" y1="5" x2="24" y2="9"/><line x1="24" y1="39" x2="24" y2="43"/><line x1="5" y1="24" x2="9" y2="24"/><line x1="39" y1="24" x2="43" y2="24"/><line x1="10.6" y1="10.6" x2="13.4" y2="13.4"/><line x1="34.6" y1="34.6" x2="37.4" y2="37.4"/><line x1="10.6" y1="37.4" x2="13.4" y2="34.6"/><line x1="34.6" y1="13.4" x2="37.4" y2="10.6"/></g>'
        . '<circle cx="24" cy="24" r="10.5" fill="#FFC94D"/><circle cx="20.5" cy="23" r="1.5" fill="#5A3A26"/><circle cx="27.5" cy="23" r="1.5" fill="#5A3A26"/>'
        . '<path d="M20.5 27q3.5 3 7 0" stroke="#5A3A26" stroke-width="1.6" fill="none" stroke-linecap="round"/>'
        . '<circle cx="18" cy="26.5" r="1.5" fill="#FF8FB1" opacity=".6"/><circle cx="30" cy="26.5" r="1.5" fill="#FF8FB1" opacity=".6"/></svg>';
}

/** Small decorative shapes (cloud, star, sun, rainbow, flower). */
function decor_svg(string $name): string
{
    return match ($name) {
        'cloud'   => '<svg viewBox="0 0 112 68" aria-hidden="true"><path fill="currentColor" d="M28 64C14 64 4 55 4 43s10-21 23-21c3-12 14-20 27-20 15 0 27 10 29 24h3c12 0 22 9 22 20s-10 18-22 18z"/></svg>',
        'star'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 1.5l3.1 6.6 7.2.9-5.3 5 1.4 7.1L12 17.6l-6.4 3.5L7 14 1.7 9l7.2-.9z"/></svg>',
        'sun'     => '<svg viewBox="0 0 48 48" aria-hidden="true"><g stroke="#FFC94D" stroke-width="3" stroke-linecap="round"><path d="M24 3v5M24 40v5M3 24h5M40 24h5M9 9l3.5 3.5M35.5 35.5 39 39M9 39l3.5-3.5M35.5 12.5 39 9"/></g><circle cx="24" cy="24" r="10" fill="#FFD66B"/></svg>',
        'rainbow' => '<svg viewBox="0 0 120 64" aria-hidden="true"><g fill="none" stroke-width="8" stroke-linecap="round"><path d="M12 60a48 48 0 0 1 96 0" stroke="#FFC4C4"/><path d="M22 60a38 38 0 0 1 76 0" stroke="#FFE9A8"/><path d="M32 60a28 28 0 0 1 56 0" stroke="#D4F5DF"/><path d="M42 60a18 18 0 0 1 36 0" stroke="#BFE7FF"/></g></svg>',
        'flower'  => '<svg viewBox="0 0 40 40" aria-hidden="true"><g fill="#FFB3CB"><circle cx="20" cy="9" r="7"/><circle cx="30.5" cy="16.6" r="7"/><circle cx="26.5" cy="29" r="7"/><circle cx="13.5" cy="29" r="7"/><circle cx="9.5" cy="16.6" r="7"/></g><circle cx="20" cy="20" r="6" fill="#FFD66B"/></svg>',
        default   => '',
    };
}

/** Cartoon child avatar drawn from the student's avatar config (see mock-data.php). */
function avatar_svg(array $a): string
{
    $skin = e($a['skin']);
    $hair = e($a['hair']);
    $back = $front = $accessory = '';

    switch ($a['style']) {
        case 'pigtails':
            $back = "<circle cx=\"13\" cy=\"36\" r=\"7\" fill=\"$hair\"/><circle cx=\"51\" cy=\"36\" r=\"7\" fill=\"$hair\"/><circle cx=\"32\" cy=\"34\" r=\"17\" fill=\"$hair\"/>";
            $front = "<path d=\"M17 35Q17 19 32 19Q47 19 47 35Q41 27 32 27Q23 27 17 35Z\" fill=\"$hair\"/>";
            $accessory = '<path d="M43 20l-6-4v8zM43 20l6-4v8z" fill="#FF7AA8"/><circle cx="43" cy="20" r="2" fill="#FF5C93"/>';
            break;
        case 'bob':
            $back = "<path d=\"M14 37Q14 16 32 16Q50 16 50 37L50 49Q32 53 14 49Z\" fill=\"$hair\"/>";
            $front = "<path d=\"M17 34Q18 20 32 20Q46 20 47 34Q43 27 37 28Q34 24 30 27Q23 27 17 34Z\" fill=\"$hair\"/>";
            $accessory = '<path d="M22 22.5l1.2 2.4 2.6.4-1.9 1.8.5 2.6-2.4-1.3-2.3 1.3.4-2.6-1.9-1.8 2.6-.4z" fill="#FFD36E"/>';
            break;
        case 'spiky':
            $front = "<path d=\"M16 35Q15 22 22 19L23 13L28 17L32 11L36 17L41 13L42 19Q49 22 48 35Q42 27 32 28Q22 27 16 35Z\" fill=\"$hair\"/>";
            break;
        default: // short
            $back = "<circle cx=\"32\" cy=\"34\" r=\"16\" fill=\"$hair\"/>";
            $front = "<path d=\"M16 36Q15 19 32 18Q49 19 48 36Q46 28 40 26Q33 30 24 27Q19 29 16 36Z\" fill=\"$hair\"/>";
    }

    return '<svg viewBox="0 0 64 64" aria-hidden="true">'
        . '<rect width="64" height="64" fill="' . e($a['bg']) . '"/>'
        . $back
        . '<path d="M11 64Q13 51 32 50Q51 51 53 64Z" fill="' . e($a['shirt']) . '"/>'
        . "<circle cx=\"17\" cy=\"38\" r=\"3.2\" fill=\"$skin\"/><circle cx=\"47\" cy=\"38\" r=\"3.2\" fill=\"$skin\"/>"
        . "<circle cx=\"32\" cy=\"37\" r=\"15\" fill=\"$skin\"/>"
        . $front
        . '<ellipse cx="26.5" cy="38" rx="1.8" ry="2.2" fill="#3B2A20"/><ellipse cx="37.5" cy="38" rx="1.8" ry="2.2" fill="#3B2A20"/>'
        . '<circle cx="27.1" cy="37.2" r=".6" fill="#fff"/><circle cx="38.1" cy="37.2" r=".6" fill="#fff"/>'
        . '<circle cx="22.5" cy="42.5" r="2.4" fill="#FF8FB1" opacity=".45"/><circle cx="41.5" cy="42.5" r="2.4" fill="#FF8FB1" opacity=".45"/>'
        . '<path d="M28.5 43.5Q32 47 35.5 43.5" stroke="#3B2A20" stroke-width="1.6" fill="none" stroke-linecap="round"/>'
        . $accessory
        . '</svg>';
}

function student_avatar(array $student, string $size = 'md'): string
{
    return '<span class="avatar avatar--' . e($size) . '" role="img" aria-label="' . e($student['nickname']) . '">'
        . avatar_svg($student['avatar']) . '</span>';
}

/* ============================================================================
 * Layout helpers
 * ========================================================================= */

function section_head(string $emoji, string $title, string $actions = '', string $subtitle = ''): void
{
    ?>
    <div class="section-head">
        <div>
            <h2 class="section-head__title"><span class="section-head__emoji" aria-hidden="true"><?= $emoji ?></span><?= e($title) ?></h2>
            <?php if ($subtitle !== ''): ?><p class="section-head__sub"><?= e($subtitle) ?></p><?php endif; ?>
        </div>
        <?php if ($actions !== ''): ?><div class="section-head__actions"><?= $actions ?></div><?php endif; ?>
    </div>
    <?php
}

function page_title(string $emoji, string $title, string $subtitle = ''): void
{
    ?>
    <header class="page-title">
        <span class="page-title__emoji" aria-hidden="true"><?= $emoji ?></span>
        <div>
            <h1><?= e($title) ?></h1>
            <?php if ($subtitle !== ''): ?><p><?= e($subtitle) ?></p><?php endif; ?>
        </div>
    </header>
    <?php
}

/**
 * Teacher's permission scope: the one classroom this account is bound to
 * (no room switcher — teachers only ever see their own classroom).
 */
function render_scope_card(array $ctx, array $students, array $teachers): void
{
    $room = $ctx['classroom'];
    ?>
    <section class="scope-card" aria-label="ห้องเรียนของฉัน" style="--room-bg: <?= e($room['color']) ?>">
        <span class="scope-card__emoji" aria-hidden="true"><?= $room['emoji'] ?></span>
        <div class="scope-card__info">
            <p class="scope-card__label">ห้องเรียนของฉัน</p>
            <h2 class="scope-card__room">ห้อง<?= e($room['name']) ?></h2>
            <p class="scope-card__meta">🧒 นักเรียน <?= count($students) ?> คน · 👩‍🏫 <?= e(implode(', ', array_column($teachers, 'nickname'))) ?></p>
        </div>
        <span class="scope-lock" title="บัญชีนี้เข้าถึงได้เฉพาะห้อง<?= e($room['name']) ?>">🔒 เฉพาะห้องนี้</span>
    </section>
    <?php
}

function nav_items(string $role): array
{
    if ($role === 'teacher') {
        return [
            ['key' => 'home',       'label' => 'หน้าหลัก', 'href' => 'teacher/index.php',      'icon' => 'home'],
            ['key' => 'activities', 'label' => 'กิจกรรม',  'href' => 'teacher/activities.php', 'icon' => 'calendar'],
            ['key' => 'food',       'label' => 'อาหาร',    'href' => 'teacher/food.php',       'icon' => 'food'],
            ['key' => 'status',     'label' => 'สถานะ',    'href' => 'teacher/status.php',     'icon' => 'status'],
            ['key' => 'account',    'label' => 'บัญชี',     'href' => 'teacher/account.php',    'icon' => 'user'],
        ];
    }
    return [
        ['key' => 'home',    'label' => 'หน้าหลัก',   'href' => 'parent/index.php',      'icon' => 'home'],
        ['key' => 'today',   'label' => 'วันนี้',      'href' => 'parent/activities.php', 'icon' => 'calendar'],
        ['key' => 'food',    'label' => 'อาหาร',      'href' => 'parent/food.php',       'icon' => 'food'],
        ['key' => 'child',   'label' => 'ลูกของฉัน',  'href' => 'parent/child.php',      'icon' => 'child'],
        ['key' => 'account', 'label' => 'บัญชี',       'href' => 'parent/account.php',    'icon' => 'user'],
    ];
}

function render_bottom_nav(string $role, string $active): void
{
    ?>
    <nav class="bottom-nav" aria-label="เมนูหลัก">
        <?php foreach (nav_items($role) as $item): ?>
            <?php $isActive = $item['key'] === $active; ?>
            <a class="bottom-nav__item<?= $isActive ? ' is-active' : '' ?>" href="<?= e(url($item['href'])) ?>"<?= $isActive ? ' aria-current="page"' : '' ?>>
                <?= icon($item['icon']) ?>
                <span><?= e($item['label']) ?></span>
            </a>
        <?php endforeach; ?>
    </nav>
    <?php
}

/* ============================================================================
 * Dynamic fragments
 * ========================================================================= */

const FRAGMENTS = [
    'status-card', 'status-hero', 'status-history', 'status-picker',
    'activities-manage', 'activities-timeline', 'activities-timeline-compact',
    'food-summary', 'food-manage', 'food-view',
];

/** Output a fragment wrapped in its [data-fragment] container. */
function fragment(string $key, array $ctx, string $tag = 'div', string $class = ''): void
{
    echo '<' . $tag . ' data-fragment="' . e($key) . '"' . ($class ? ' class="' . e($class) . '"' : '') . '>';
    render_fragment($key, $ctx);
    echo '</' . $tag . '>';
}

function render_fragment(string $key, array $ctx): void
{
    match ($key) {
        'status-card'                 => render_status_card($ctx),
        'status-hero'                 => render_status_hero($ctx),
        'status-history'              => render_status_history($ctx),
        'status-picker'               => render_status_picker($ctx),
        'activities-manage'           => render_activities_manage($ctx),
        'activities-timeline'         => render_activities_timeline($ctx, false),
        'activities-timeline-compact' => render_activities_timeline($ctx, true),
        'food-summary'                => render_food_summary($ctx),
        'food-manage'                 => render_food_manage($ctx),
        'food-view'                   => render_food_view($ctx),
        default                       => null,
    };
}

/* ---------- Status ---------- */

function render_status_card(array $ctx): void
{
    $status = $ctx['status'];
    $isParent = $ctx['role'] === 'parent';
    $lead = $isParent ? 'ตอนนี้' . $ctx['child']['nickname'] . 'กำลัง...' : 'ตอนนี้เด็ก ๆ กำลัง...';
    ?>
    <div class="status-card<?= $isParent ? ' status-card--parent' : '' ?>" style="--status-bg: <?= e($status['color'] ?? '#F4F6FB') ?>">
        <span class="status-card__decor status-card__decor--cloud"><?= decor_svg('cloud') ?></span>
        <span class="status-card__decor status-card__decor--star"><?= decor_svg('star') ?></span>

        <p class="status-card__room"><span class="live-dot" aria-hidden="true"></span>สถานะห้อง <?= e($ctx['classroom']['name']) ?></p>
        <p class="status-card__lead"><?= e($lead) ?></p>

        <?php if ($status): ?>
            <div class="status-card__main">
                <span class="status-card__emoji" aria-hidden="true"><?= $status['emoji'] ?></span>
                <strong class="status-card__label"><?= e($status['text']) ?></strong>
            </div>
            <p class="status-card__time">
                <?= icon('clock') ?>
                <?= $isParent ? 'อัปเดตล่าสุด ' : '' ?><?= e(thai_time($status['at'])) ?> · โดย <?= e($status['byName']) ?>
            </p>
        <?php else: ?>
            <p class="status-card__empty">ยังไม่มีการอัปเดตสถานะวันนี้</p>
        <?php endif; ?>

        <?php if (!$isParent): ?>
            <button type="button" class="btn btn--primary btn--lg btn--block" data-action="open-sheet" data-sheet="sheet-status">
                <?= icon('refresh') ?> เปลี่ยนสถานะ
            </button>
        <?php endif; ?>
    </div>
    <?php
}

function render_status_hero(array $ctx): void
{
    $status = $ctx['status'];
    ?>
    <div class="status-hero" style="--status-bg: <?= e($status['color'] ?? '#F4F6FB') ?>">
        <p class="status-hero__live"><span class="live-dot live-dot--lg" aria-hidden="true"></span>สถานะปัจจุบัน · <?= e($ctx['classroom']['name']) ?></p>
        <?php if ($status): ?>
            <span class="status-hero__emoji" aria-hidden="true"><?= $status['emoji'] ?></span>
            <strong class="status-hero__label">กำลัง<?= e($status['text']) ?></strong>
            <p class="status-hero__time">เวลา <?= e(thai_time($status['at'])) ?> · โดย <?= e($status['byName']) ?></p>
        <?php else: ?>
            <span class="status-hero__emoji" aria-hidden="true">🌤️</span>
            <strong class="status-hero__label">ยังไม่มีสถานะวันนี้</strong>
            <p class="status-hero__time">เลือกสถานะด้านล่างเพื่อเริ่มต้น</p>
        <?php endif; ?>
    </div>
    <?php
}

function render_status_history(array $ctx): void
{
    $history = $ctx['history'];
    if (!$history) {
        echo '<p class="empty">ยังไม่มีการอัปเดตสถานะวันนี้</p>';
        return;
    }
    ?>
    <ol class="history">
        <?php foreach ($history as $i => $h): ?>
            <li class="history__item<?= $i === 0 ? ' is-current' : '' ?>">
                <span class="history__emoji" style="--status-bg: <?= e($h['color']) ?>" aria-hidden="true"><?= $h['emoji'] ?></span>
                <span class="history__text"><?= e($h['text']) ?><?php if ($i === 0): ?> <span class="tag">ล่าสุด</span><?php endif; ?></span>
                <span class="history__time"><?= e(date('H:i', strtotime($h['at']))) ?> น.</span>
            </li>
        <?php endforeach; ?>
    </ol>
    <?php
}

function render_status_picker(array $ctx): void
{
    $current = $ctx['status'];
    ?>
    <div class="status-grid">
        <?php foreach (get_statuses() as $s): ?>
            <?php $isCurrent = $current && $current['code'] === $s['code']; ?>
            <button type="button" class="status-btn<?= $isCurrent ? ' is-current' : '' ?>" style="--status-bg: <?= e($s['color']) ?>"
                    data-action="set-status" data-code="<?= e($s['code']) ?>" aria-pressed="<?= $isCurrent ? 'true' : 'false' ?>">
                <span class="status-btn__emoji" aria-hidden="true"><?= $s['emoji'] ?></span>
                <span class="status-btn__label"><?= e($s['label']) ?></span>
            </button>
        <?php endforeach; ?>
    </div>
    <?php
}

/** Note field + status buttons. The note sits outside the fragment so a refresh never wipes typing. */
function render_status_controls(array $ctx): void
{
    ?>
    <div class="status-controls" data-status-scope>
        <label class="field">
            <span class="field__label">รายละเอียดเพิ่มเติม <small>(ไม่บังคับ)</small></span>
            <input class="input" type="text" maxlength="40" data-status-note
                   placeholder="เช่น ศิลปะ → แสดงเป็น “ทำกิจกรรมศิลปะ”">
        </label>
        <?php fragment('status-picker', $ctx); ?>
        <p class="hint">💡 สถานะนี้ใช้กับเด็กทุกคนในห้อง <?= e($ctx['classroom']['name']) ?> และผู้ปกครองจะเห็นทันที</p>
    </div>
    <?php
}

/* ---------- Activities ---------- */

function render_activities_manage(array $ctx): void
{
    $activities = $ctx['activities'];
    if (!$activities) {
        echo '<div class="empty empty--box"><span>📋</span><p>ยังไม่มีกิจกรรมวันนี้<br>กด “เพิ่มกิจกรรม” เพื่อเริ่มต้น</p></div>';
        return;
    }
    ?>
    <ol class="activity-list">
        <?php foreach ($activities as $a): ?>
            <li class="activity" data-activity="<?= e(json_encode($a, JSON_UNESCAPED_UNICODE)) ?>">
                <span class="activity__time"><?= e($a['time']) ?></span>
                <span class="activity__icon" aria-hidden="true"><?= $a['icon'] ?></span>
                <div class="activity__body">
                    <strong><?= e($a['title']) ?></strong>
                    <?php if ($a['detail'] !== ''): ?><p><?= e($a['detail']) ?></p><?php endif; ?>
                </div>
                <div class="activity__actions">
                    <button type="button" class="icon-btn" data-action="edit-activity" aria-label="แก้ไข <?= e($a['title']) ?>"><?= icon('edit') ?></button>
                    <button type="button" class="icon-btn icon-btn--danger" data-action="delete-activity" data-id="<?= e($a['id']) ?>" aria-label="ลบ <?= e($a['title']) ?>"><?= icon('trash') ?></button>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
    <?php
}

function render_activities_timeline(array $ctx, bool $compact): void
{
    $activities = $ctx['activities'];
    if (!$activities) {
        echo '<p class="empty">คุณครูยังไม่ได้เพิ่มกิจกรรมของวันนี้</p>';
        return;
    }
    ?>
    <ol class="timeline<?= $compact ? ' timeline--compact' : '' ?>">
        <?php foreach ($activities as $a): ?>
            <li class="timeline__item">
                <span class="timeline__time"><?= e($a['time']) ?></span>
                <span class="timeline__dot" aria-hidden="true"><?= $a['icon'] ?></span>
                <div class="timeline__body">
                    <strong><?= e($a['title']) ?></strong>
                    <?php if (!$compact && $a['detail'] !== ''): ?><p><?= e($a['detail']) ?></p><?php endif; ?>
                </div>
            </li>
        <?php endforeach; ?>
    </ol>
    <?php
}

/* ---------- Food ---------- */

function render_food_summary(array $ctx): void
{
    ?>
    <ul class="food-summary">
        <?php foreach ($ctx['food'] as $m): ?>
            <li style="--meal-bg: <?= e($m['color']) ?>">
                <span class="food-summary__icon" aria-hidden="true"><?= $m['icon'] ?></span>
                <div>
                    <small><?= e($m['label']) ?></small>
                    <strong><?= e(implode(' + ', $m['items'])) ?></strong>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
}

function render_food_manage(array $ctx): void
{
    ?>
    <div class="meal-grid">
        <?php foreach ($ctx['food'] as $m): ?>
            <article class="meal" style="--meal-bg: <?= e($m['color']) ?>" data-meal="<?= e($m['meal']) ?>">
                <header class="meal__head">
                    <span class="meal__icon" aria-hidden="true"><?= $m['icon'] ?></span>
                    <div class="meal__title">
                        <h3><?= e($m['label']) ?></h3>
                        <small><?= icon('clock') ?> <?= e($m['time']) ?> น.</small>
                    </div>
                    <button type="button" class="btn btn--soft btn--sm meal__edit" data-action="edit-meal"><?= icon('edit') ?> แก้ไข</button>
                </header>
                <ul class="meal__items">
                    <?php foreach ($m['items'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                </ul>
                <form class="meal__form" data-meal-form hidden>
                    <label class="field">
                        <span class="field__label">รายการอาหาร <small>(1 บรรทัด ต่อ 1 รายการ)</small></span>
                        <textarea class="input" name="items" rows="3" required><?= e(implode("\n", $m['items'])) ?></textarea>
                    </label>
                    <div class="row-actions">
                        <button type="button" class="btn btn--ghost" data-action="cancel-meal">ยกเลิก</button>
                        <button type="submit" class="btn btn--primary"><?= icon('check') ?> บันทึก</button>
                    </div>
                </form>
            </article>
        <?php endforeach; ?>
    </div>
    <?php
}

function render_food_view(array $ctx): void
{
    ?>
    <div class="meal-grid">
        <?php foreach ($ctx['food'] as $m): ?>
            <article class="meal meal--view" style="--meal-bg: <?= e($m['color']) ?>">
                <header class="meal__head">
                    <span class="meal__icon" aria-hidden="true"><?= $m['icon'] ?></span>
                    <div class="meal__title">
                        <h3><?= e($m['label']) ?></h3>
                        <small><?= icon('clock') ?> <?= e($m['time']) ?> น.</small>
                    </div>
                </header>
                <ul class="meal__items">
                    <?php foreach ($m['items'] as $item): ?><li><?= e($item) ?></li><?php endforeach; ?>
                </ul>
            </article>
        <?php endforeach; ?>
    </div>
    <?php
}

/* ---------- Students & child ---------- */

function render_student_grid(array $students): void
{
    ?>
    <ul class="student-grid">
        <?php foreach ($students as $s): ?>
            <li class="student" title="<?= e($s['name']) ?>">
                <?= student_avatar($s) ?>
                <span class="student__name"><?= e($s['nickname']) ?></span>
            </li>
        <?php endforeach; ?>
    </ul>
    <?php
}

function render_child_card(array $child, bool $detailed = false): void
{
    $room = get_classroom($child['classroom_id']);
    ?>
    <div class="child-card">
        <?= student_avatar($child, 'xl') ?>
        <div class="child-card__info">
            <h3 class="child-card__name"><?= e($child['nickname']) ?></h3>
            <?php if ($detailed): ?><p class="child-card__full"><?= e($child['name']) ?></p><?php endif; ?>
            <p class="child-card__meta"><span class="chip"><?= $room['emoji'] ?> <?= e($room['name']) ?></span></p>
            <p class="child-card__school">🏫 <?= e(get_school()['name']) ?></p>
        </div>
    </div>
    <?php
}

/* ============================================================================
 * Sheets (bottom-sheet dialogs)
 * ========================================================================= */

function render_sheet_open(string $id, string $title, string $subtitle = '', string $extraClass = ''): void
{
    ?>
    <div class="sheet <?= e($extraClass) ?>" id="<?= e($id) ?>" aria-hidden="true">
        <div class="sheet__backdrop" data-action="close-sheet"></div>
        <div class="sheet__panel" role="dialog" aria-modal="true" aria-labelledby="<?= e($id) ?>-title">
            <span class="sheet__handle" aria-hidden="true"></span>
            <div class="sheet__head">
                <div>
                    <h2 class="sheet__title" id="<?= e($id) ?>-title"><?= e($title) ?></h2>
                    <?php if ($subtitle !== ''): ?><p class="sheet__sub"><?= e($subtitle) ?></p><?php endif; ?>
                </div>
                <button type="button" class="icon-btn" data-action="close-sheet" aria-label="ปิด"><?= icon('close') ?></button>
            </div>
    <?php
}

function render_sheet_close(): void
{
    echo '</div></div>';
}

function render_status_sheet(array $ctx): void
{
    render_sheet_open('sheet-status', 'เปลี่ยนสถานะห้องเรียน', 'เลือกสิ่งที่เด็ก ๆ กำลังทำอยู่ตอนนี้');
    render_status_controls($ctx);
    render_sheet_close();
}

function render_activity_sheet(): void
{
    render_sheet_open('sheet-activity', 'เพิ่มกิจกรรม', 'กิจกรรมจะแสดงในหน้าผู้ปกครองทันที');
    ?>
    <form class="form" data-activity-form novalidate>
        <input type="hidden" name="id" value="">
        <div class="form__row">
            <label class="field field--time">
                <span class="field__label">เวลา</span>
                <input class="input" type="time" name="time" required value="09:00">
            </label>
            <label class="field">
                <span class="field__label">ชื่อกิจกรรม</span>
                <input class="input" type="text" name="title" maxlength="80" required placeholder="เช่น กิจกรรมเรียนรู้ตัวเลข">
            </label>
        </div>
        <label class="field">
            <span class="field__label">รายละเอียด <small>(ไม่บังคับ)</small></span>
            <textarea class="input" name="detail" rows="2" maxlength="200" placeholder="เช่น นับเลข 1–10 ผ่านเกมและเพลง"></textarea>
        </label>
        <fieldset class="field icon-picker">
            <legend class="field__label">ไอคอน</legend>
            <div class="icon-picker__grid">
                <?php foreach (get_activity_icons() as $i => $emoji): ?>
                    <label class="icon-picker__item">
                        <input type="radio" name="icon" value="<?= e($emoji) ?>"<?= $i === 0 ? ' checked' : '' ?>>
                        <span><?= $emoji ?></span>
                    </label>
                <?php endforeach; ?>
            </div>
        </fieldset>
        <p class="form__error" data-form-error hidden></p>
        <div class="row-actions">
            <button type="button" class="btn btn--ghost" data-action="close-sheet">ยกเลิก</button>
            <button type="submit" class="btn btn--primary"><?= icon('check') ?> <span data-submit-label>บันทึกกิจกรรม</span></button>
        </div>
    </form>
    <?php
    render_sheet_close();
}

function render_confirm_sheet(): void
{
    ?>
    <div class="sheet sheet--confirm" id="sheet-confirm" aria-hidden="true">
        <div class="sheet__backdrop" data-action="close-sheet"></div>
        <div class="sheet__panel" role="alertdialog" aria-modal="true" aria-labelledby="sheet-confirm-title">
            <span class="confirm__icon" aria-hidden="true" data-confirm-icon>🗑️</span>
            <h2 class="sheet__title" id="sheet-confirm-title" data-confirm-title>ยืนยัน</h2>
            <p class="sheet__sub" data-confirm-message></p>
            <div class="row-actions">
                <button type="button" class="btn btn--ghost" data-action="close-sheet">ยกเลิก</button>
                <button type="button" class="btn btn--danger" data-confirm-ok>ยืนยัน</button>
            </div>
        </div>
    </div>
    <?php
}
