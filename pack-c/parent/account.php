<?php
/** Package C — Parent account page. */
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$user = require_role('parent');
$ctx = page_context($user);

$page = ['id' => 'parent-account', 'title' => 'บัญชี', 'layout' => 'app', 'nav' => 'account'];
require PACKC_ROOT . '/includes/header.php';
?>

<section class="profile">
    <span class="profile__avatar" aria-hidden="true"><?= $user['emoji'] ?></span>
    <h1 class="profile__name"><?= e($user['name']) ?></h1>
    <p class="profile__meta">ผู้ปกครองของ<?= e($ctx['child']['nickname']) ?></p>
    <span class="chip chip--role">💗 คุณ<?= e($user['relation']) ?></span>
</section>

<section class="card">
    <ul class="menu-list">
        <li><span>📱 เบอร์โทรศัพท์</span><strong><?= e($user['phone']) ?></strong></li>
        <li><span>🧒 บุตรหลาน</span><strong><?= e($ctx['child']['nickname']) ?> · <?= e($ctx['classroom']['name']) ?></strong></li>
        <li><span>🏫 โรงเรียน</span><strong><?= e(get_school()['name']) ?></strong></li>
        <li><span>📦 แพ็กเกจ</span><strong>Mykid Basic</strong></li>
    </ul>
</section>

<p class="hint hint--card">🔔 หน้าหลักจะอัปเดตเองเมื่อคุณครูเปลี่ยนสถานะ ไม่ต้องกดรีเฟรช</p>

<a class="btn btn--danger-soft btn--lg btn--block" href="<?= e(url('logout.php')) ?>"><?= icon('logout') ?> ออกจากระบบ</a>

<?php require PACKC_ROOT . '/includes/footer.php'; ?>
