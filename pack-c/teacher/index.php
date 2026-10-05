<?php
/** Package C — Teacher dashboard. */
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$user = require_role('teacher');
$ctx = page_context($user);
$students = get_teacher_students($user);   // filtered by the teacher's classroom_id
$colleagues = get_teacher_colleagues($user);

$page = ['id' => 'teacher-home', 'title' => 'หน้าหลักครู', 'layout' => 'app', 'nav' => 'home'];
require PACKC_ROOT . '/includes/header.php';
?>

<section class="greeting">
    <div>
        <p class="greeting__hello">สวัสดีค่ะ คุณครู <span aria-hidden="true">👋</span></p>
        <p class="greeting__name"><?= e($user['name']) ?> · ครูประจำชั้น<?= e($ctx['classroom']['name']) ?></p>
        <p class="greeting__date">📅 วันนี้<?= e(thai_date()) ?></p>
    </div>
    <span class="greeting__clock" data-clock aria-label="เวลาปัจจุบัน"><?= date('H:i') ?></span>
</section>

<?php render_scope_card($ctx, $students, $colleagues); ?>

<div class="dash-grid">
    <section class="dash-grid__status" aria-label="สถานะปัจจุบัน">
        <?php fragment('status-card', $ctx); ?>
    </section>

    <section class="card">
        <?php section_head('📋', 'กิจกรรมวันนี้',
            '<button type="button" class="btn btn--soft btn--sm" data-action="add-activity">' . icon('plus') . ' เพิ่ม</button>'); ?>
        <?php fragment('activities-manage', $ctx); ?>
        <a class="card__link" href="activities.php">จัดการกิจกรรมทั้งหมด <?= icon('next') ?></a>
    </section>

    <section class="card">
        <?php section_head('🍱', 'อาหารวันนี้',
            '<a class="btn btn--soft btn--sm" href="food.php">' . icon('edit') . ' แก้ไขเมนู</a>'); ?>
        <?php fragment('food-summary', $ctx); ?>
    </section>

    <section class="card dash-grid__wide">
        <?php section_head('🧒', 'นักเรียน', '<span class="count-badge">' . count($students) . ' คน</span>', $ctx['classroom']['name']); ?>
        <?php render_student_grid($students); ?>
    </section>
</div>

<?php
render_status_sheet($ctx);
render_activity_sheet();
require PACKC_ROOT . '/includes/footer.php';
