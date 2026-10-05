<?php
/** Package C — Parent: today at school (status history + full activity timeline). */
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$user = require_role('parent');
$ctx = page_context($user);

$page = ['id' => 'parent-today', 'title' => 'วันนี้ที่โรงเรียน', 'layout' => 'app', 'nav' => 'today'];
require PACKC_ROOT . '/includes/header.php';

page_title('🏫', 'วันนี้ที่โรงเรียน', thai_date() . ' · ' . $ctx['classroom']['name']);
?>

<section class="card">
    <?php section_head('🗓️', 'กิจกรรมวันนี้'); ?>
    <?php fragment('activities-timeline', $ctx); ?>
</section>

<section class="card">
    <?php section_head('🚦', 'สถานะที่คุณครูอัปเดตวันนี้', '', 'สถานะของทั้งห้องเรียน'); ?>
    <?php fragment('status-history', $ctx); ?>
</section>

<?php require PACKC_ROOT . '/includes/footer.php'; ?>
