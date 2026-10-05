<?php
/** Package C — Teacher: manage today's food menu. */
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$user = require_role('teacher');
$ctx = page_context($user);

$page = ['id' => 'teacher-food', 'title' => 'จัดการอาหาร', 'layout' => 'app', 'nav' => 'food'];
require PACKC_ROOT . '/includes/header.php';

page_title('🍱', 'เมนูอาหารวันนี้', thai_date() . ' · ห้อง' . $ctx['classroom']['name']);
?>

<p class="hint hint--card">🔒 เมนูนี้เป็นของห้อง<?= e($ctx['classroom']['name']) ?> เท่านั้น กด “แก้ไข” ที่มื้อที่ต้องการ แล้วกด “บันทึก”</p>

<?php fragment('food-manage', $ctx, 'section'); ?>

<?php require PACKC_ROOT . '/includes/footer.php'; ?>
