<?php
/** Package C — landing page (entry point of the demo). */
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$packages = require PACKC_ROOT . '/../includes/packages.php';
$pkg = $packages['c'];
$school = get_school();
$stats = school_stats();
$data = mock_data();
$user = null;

$page = ['id' => 'landing', 'title' => 'Package C', 'layout' => 'landing', 'theme' => 'landing'];
require PACKC_ROOT . '/includes/header.php';
?>

<nav class="topnav">
    <a class="topnav__back" href="../index.php"><?= icon('back') ?> แพ็กเกจทั้งหมด</a>
    <span class="topnav__brand"><span class="appbar__logo"><?= logo_svg() ?></span> Mykid</span>
</nav>

<section class="landing-hero">
    <span class="landing-hero__decor landing-hero__decor--sun"><?= decor_svg('sun') ?></span>
    <span class="landing-hero__decor landing-hero__decor--rainbow"><?= decor_svg('rainbow') ?></span>
    <span class="landing-hero__decor landing-hero__decor--flower"><?= decor_svg('flower') ?></span>

    <span class="pill pill--white"><?= e($pkg['code']) ?> · <?= e($pkg['statusLabel']) ?></span>
    <h1 class="landing-hero__title"><?= e($pkg['name']) ?></h1>
    <p class="landing-hero__tagline"><?= e($pkg['tagline']) ?></p>

    <div class="landing-hero__price">
        <strong><?= e($pkg['price']) ?></strong>
        <span><?= e($pkg['priceUnit']) ?></span>
    </div>
    <ul class="inline-checks">
        <?php foreach ($pkg['includes'] as $item): ?><li><?= icon('check') ?><?= e($item) ?></li><?php endforeach; ?>
    </ul>

    <a class="btn btn--primary btn--lg landing-hero__cta" href="login.php">เข้าสู่ Demo <?= icon('next') ?></a>
</section>

<section class="landing-section">
    <?php section_head('✨', 'ฟีเจอร์หลัก'); ?>
    <div class="feature-grid">
        <?php foreach ($pkg['features'] as [$emoji, $text]): ?>
            <div class="feature"><span class="feature__icon" aria-hidden="true"><?= $emoji ?></span><p><?= e($text) ?></p></div>
        <?php endforeach; ?>
    </div>
</section>

<section class="landing-section">
    <?php section_head('👥', 'ผู้ใช้งาน 2 บทบาท'); ?>
    <div class="role-grid">
        <article class="role-card role-card--teacher">
            <span class="role-card__emoji" aria-hidden="true">👩‍🏫</span>
            <h3>ครู</h3>
            <p>จัดการข้อมูลทั้งหมด: กดเปลี่ยนสถานะห้อง เพิ่ม/แก้ไขกิจกรรม และแจ้งเมนูอาหาร</p>
        </article>
        <article class="role-card role-card--parent">
            <span class="role-card__emoji" aria-hidden="true">👨‍👩‍👧</span>
            <h3>ผู้ปกครอง</h3>
            <p>ดูสถานะล่าสุดของห้องที่ลูกอยู่ กิจกรรมวันนี้ และอาหารที่ลูกได้ทาน</p>
        </article>
    </div>
</section>

<section class="landing-section">
    <?php section_head('📌', 'ขอบเขตของ Package C'); ?>
    <div class="card scope">
        <ul class="scope__list">
            <li class="is-yes">สถานะเป็นของทั้งห้องเรียน ครูกดเปลี่ยนเอง</li>
            <li class="is-yes">ผู้ปกครองเห็นสถานะล่าสุดของห้องที่ลูกอยู่</li>
            <li class="is-no">ไม่มีสถานะอัตโนมัติตามตารางเวลา</li>
            <li class="is-no">ไม่มีสถานะรายบุคคล</li>
            <li class="is-no">ไม่มี Admin และผู้บริหาร <small>(มีใน Package A/B)</small></li>
        </ul>
    </div>
</section>

<section class="landing-section">
    <?php section_head('🏫', 'ข้อมูลตัวอย่างใน Demo'); ?>
    <div class="card demo-school">
        <p class="demo-school__name"><?= e($school['name']) ?></p>
        <div class="stat-row">
            <div class="stat"><strong><?= $stats['classrooms'] ?></strong><span>ห้องเรียน</span></div>
            <div class="stat"><strong><?= $stats['students'] ?></strong><span>นักเรียน</span></div>
            <div class="stat"><strong><?= $stats['teachers'] ?></strong><span>ครูประจำห้อง</span></div>
        </div>
        <ul class="room-list">
            <?php foreach (get_classrooms() as $room): ?>
                <li>
                    <span class="room-list__emoji" style="background: <?= e($room['color']) ?>"><?= $room['emoji'] ?></span>
                    <strong><?= e($room['name']) ?></strong>
                    <span><?= count(get_students_by_classroom($room['id'])) ?> คน · ครู <?= count(get_teachers_by_classroom($room['id'])) ?> คน</span>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>

<section class="landing-section">
    <?php section_head('🔑', 'บัญชีทดลอง'); ?>
    <div class="account-grid">
        <div class="demo-account demo-account--teacher">
            <p class="demo-account__role">👩‍🏫 ครู</p>
            <p>เบอร์ <strong><?= e($data['teacherAccounts'][0]['phone']) ?></strong></p>
            <p>PIN <strong><?= e($data['teacherAccounts'][0]['pin']) ?></strong></p>
        </div>
        <div class="demo-account demo-account--parent">
            <p class="demo-account__role">👨‍👩‍👧 ผู้ปกครอง</p>
            <p>เบอร์ <strong><?= e($data['parentAccounts'][0]['phone']) ?></strong></p>
            <p>PIN <strong><?= e($data['parentAccounts'][0]['pin']) ?></strong></p>
        </div>
    </div>
    <a class="btn btn--primary btn--lg btn--block landing-cta-bottom" href="login.php">เข้าสู่ Demo <?= icon('next') ?></a>
</section>

<p class="landing-footer">© <?= (int) date('Y') + 543 ?> Mykid · เวอร์ชันสาธิต (Demo)</p>

<?php require PACKC_ROOT . '/includes/footer.php'; ?>
