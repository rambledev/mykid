<?php
/** Package C — Parent: my child's profile and classroom (no other children's data). */
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$user = require_role('parent');
$ctx = page_context($user);
$child = $ctx['child'];                 // from the session's student_id only
$teachers = get_parent_teachers($user); // the child's homeroom teachers

$page = ['id' => 'parent-child', 'title' => 'ลูกของฉัน', 'layout' => 'app', 'nav' => 'child'];
require PACKC_ROOT . '/includes/header.php';

page_title('🧒', 'ลูกของฉัน');
?>

<section class="card card--hero-pink">
    <?php render_child_card($child, true); ?>
</section>

<section class="card">
    <ul class="menu-list">
        <li><span>🆔 รหัสนักเรียน</span><strong><?= e($child['code']) ?></strong></li>
        <li><span>🏷️ ชื่อเล่น</span><strong><?= e($child['nickname']) ?></strong></li>
        <li><span>🎒 ห้องเรียน</span><strong><?= e($ctx['classroom']['name']) ?></strong></li>
        <li><span>👤 ผู้ปกครอง</span><strong><?= e($child['parentName']) ?></strong></li>
    </ul>
</section>

<section class="card">
    <?php section_head('👩‍🏫', 'คุณครูประจำห้อง', '<span class="count-badge">' . count($teachers) . ' คน</span>'); ?>
    <ul class="teacher-list">
        <?php foreach ($teachers as $t): ?>
            <li>
                <span class="teacher-list__avatar" aria-hidden="true"><?= $t['emoji'] ?></span>
                <div><strong><?= e($t['nickname']) ?></strong><small><?= e($t['fullName']) ?></small></div>
            </li>
        <?php endforeach; ?>
    </ul>
</section>

<p class="hint hint--card">🔒 เพื่อความเป็นส่วนตัว ผู้ปกครองจะเห็นเฉพาะข้อมูลของ<?= e($child['nickname']) ?>เท่านั้น ไม่แสดงรายชื่อหรือข้อมูลของเด็กคนอื่น</p>

<?php require PACKC_ROOT . '/includes/footer.php'; ?>
