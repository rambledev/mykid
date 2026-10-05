<?php
/** Package C — Teacher: manage today's activities (add / edit / delete). */
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$user = require_role('teacher');
$ctx = page_context($user);

$page = ['id' => 'teacher-activities', 'title' => 'จัดการกิจกรรม', 'layout' => 'app', 'nav' => 'activities'];
require PACKC_ROOT . '/includes/header.php';

page_title('📋', 'จัดการกิจกรรมวันนี้', thai_date());
render_scope_card($ctx, get_teacher_students($user), get_teacher_colleagues($user));
?>

<button type="button" class="btn btn--primary btn--lg btn--block add-cta" data-action="add-activity">
    <?= icon('plus') ?> เพิ่มกิจกรรม
</button>

<section class="card">
    <?php section_head('🗓️', 'ตารางกิจกรรม', '', 'เรียงตามเวลาอัตโนมัติ · ' . $ctx['classroom']['name']); ?>
    <?php fragment('activities-manage', $ctx); ?>
</section>

<p class="hint hint--card">💡 กิจกรรมที่เพิ่มหรือแก้ไข จะแสดงในหน้า “วันนี้ที่โรงเรียน” ของผู้ปกครองทันที</p>

<?php
render_activity_sheet();
require PACKC_ROOT . '/includes/footer.php';
