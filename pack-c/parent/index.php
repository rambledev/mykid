<?php
/** Package C — Parent dashboard (read-only view of the child's classroom). */
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$user = require_role('parent');
$ctx = page_context($user);
$child = $ctx['child'];   // the only student this parent can see (from student_id)

$page = ['id' => 'parent-home', 'title' => 'หน้าหลักผู้ปกครอง', 'layout' => 'app', 'nav' => 'home'];
require PACKC_ROOT . '/includes/header.php';
?>

<section class="greeting greeting--parent">
    <div>
        <p class="greeting__hello">สวัสดีค่ะ คุณ<?= e($user['relation']) ?> <span aria-hidden="true">💗</span></p>
        <p class="greeting__child"><?= student_avatar($child, 'sm') ?> <?= e($child['nickname']) ?> · <?= e($ctx['classroom']['name']) ?></p>
        <p class="greeting__date">📅 วันนี้<?= e(thai_date()) ?></p>
    </div>
    <span class="greeting__clock" data-clock aria-label="เวลาปัจจุบัน"><?= date('H:i') ?></span>
</section>

<div class="dash-grid">
    <section class="dash-grid__status" aria-label="สถานะปัจจุบันของลูก">
        <?php fragment('status-card', $ctx); ?>
    </section>

    <section class="card">
        <?php section_head('🏫', 'วันนี้ที่โรงเรียน', '<a class="btn btn--soft btn--sm" href="activities.php">ดูทั้งหมด</a>'); ?>
        <?php fragment('activities-timeline-compact', $ctx); ?>
    </section>

    <section class="card">
        <?php section_head('🍽️', 'อาหารวันนี้', '<a class="btn btn--soft btn--sm" href="food.php">ดูเมนู</a>'); ?>
        <?php fragment('food-summary', $ctx); ?>
    </section>

    <section class="card">
        <?php section_head('🧒', 'ข้อมูลลูก'); ?>
        <?php render_child_card($child); ?>
    </section>
</div>

<?php require PACKC_ROOT . '/includes/footer.php'; ?>
