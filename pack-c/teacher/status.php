<?php
/** Package C — Teacher: change the classroom status (manual only, whole classroom). */
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$user = require_role('teacher');
$ctx = page_context($user);

$page = ['id' => 'teacher-status', 'title' => 'เปลี่ยนสถานะ', 'layout' => 'app', 'nav' => 'status'];
require PACKC_ROOT . '/includes/header.php';

render_scope_card($ctx, get_teacher_students($user), get_teacher_colleagues($user));
fragment('status-hero', $ctx, 'section');
?>

<section class="card">
    <?php section_head('🚦', 'เปลี่ยนสถานะ', '', 'แตะเพื่ออัปเดตทันที'); ?>
    <?php render_status_controls($ctx); ?>
</section>

<section class="card">
    <?php section_head('🕘', 'ประวัติสถานะวันนี้'); ?>
    <?php fragment('status-history', $ctx); ?>
</section>

<?php require PACKC_ROOT . '/includes/footer.php'; ?>
