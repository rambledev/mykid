<?php
/** Package C — Teacher account page. */
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$user = require_role('teacher');
$ctx = page_context($user);
$homeroom = get_teacher_classroom($user);

$page = ['id' => 'teacher-account', 'title' => 'บัญชี', 'layout' => 'app', 'nav' => 'account'];
require PACKC_ROOT . '/includes/header.php';
?>

<section class="profile">
    <span class="profile__avatar" aria-hidden="true"><?= $user['emoji'] ?></span>
    <h1 class="profile__name"><?= e($user['name']) ?></h1>
    <p class="profile__meta"><?= e($user['fullName']) ?></p>
    <span class="chip chip--role">👩‍🏫 ครูประจำชั้น <?= e($homeroom['name']) ?></span>
</section>

<?php render_scope_card($ctx, get_teacher_students($user), get_teacher_colleagues($user)); ?>

<section class="card">
    <?php section_head('🔐', 'สิทธิ์การใช้งาน', '', 'กำหนดจากบัญชีที่เข้าสู่ระบบ'); ?>
    <ul class="menu-list">
        <li><span>👤 บทบาท</span><strong>ครู (Teacher)</strong></li>
        <li><span>🏫 ขอบเขตข้อมูล</span><strong>ห้อง<?= e($homeroom['name']) ?> เท่านั้น</strong></li>
        <li><span>✏️ จัดการได้</span><strong>สถานะ · กิจกรรม · อาหาร</strong></li>
        <li><span>🚫 เข้าถึงไม่ได้</span><strong>ห้องอื่นทั้งหมด</strong></li>
    </ul>
</section>

<section class="card">
    <ul class="menu-list">
        <li><span>📱 เบอร์โทรศัพท์</span><strong><?= e($user['phone']) ?></strong></li>
        <li><span>🏫 โรงเรียน</span><strong><?= e(get_school()['name']) ?></strong></li>
        <li><span>📦 แพ็กเกจ</span><strong>Mykid Basic</strong></li>
    </ul>
</section>

<section class="card">
    <?php section_head('🧪', 'เครื่องมือ Demo', '', 'คืนค่าสถานะ กิจกรรม และอาหาร ของห้อง' . $homeroom['name'] . ' กลับเป็นค่าเริ่มต้น'); ?>
    <button type="button" class="btn btn--soft btn--block" data-action="reset-demo"><?= icon('refresh') ?> รีเซ็ตข้อมูล Demo</button>
</section>

<a class="btn btn--danger-soft btn--lg btn--block" href="<?= e(url('logout.php')) ?>"><?= icon('logout') ?> ออกจากระบบ</a>

<?php require PACKC_ROOT . '/includes/footer.php'; ?>
