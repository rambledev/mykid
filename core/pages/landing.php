<?php
/** Package landing page (Package A / B) — content comes from the package config. */
declare(strict_types=1);

$pkg = pkg();
$roleInfo = [
    'super_admin' => 'ดูแลทุกโรงเรียนในระบบ เพิ่ม/แก้ไขโรงเรียน และจัดการผู้ดูแลโรงเรียน',
    'admin'       => 'จัดการนักเรียน ครู ห้องเรียน กิจกรรม และอาหารของโรงเรียนตัวเอง',
    'executive'   => 'ดู Dashboard, KPI และรายงานภาพรวม แบบอ่านอย่างเดียว',
    'teacher'     => has_feature('chat')
        ? 'สถานะห้อง เช็คชื่อ สุขภาพ การนอน ผลงาน ดาว และแชทกับผู้ปกครอง เฉพาะห้องตัวเอง'
        : 'อัปเดตสถานะ จัดการกิจกรรมและอาหาร เฉพาะห้องตัวเอง',
    'parent'      => has_feature('chat')
        ? 'ติดตามลูกครบทุกด้าน ไทม์ไลน์ อาหาร การนอน สุขภาพ ผลงาน CCTV และแชทกับครู'
        : 'ดูสถานะ กิจกรรม และอาหาร เฉพาะลูกของตัวเอง',
];
$schools = store_rows('schools');
$students = store_rows('students');
$users = store_rows('users');

mk_header(['id' => 'landing', 'title' => $pkg['code'], 'layout' => 'landing', 'theme' => 'landing']);
?>

<nav class="topnav">
    <a class="topnav__back" href="<?= e(root_url('index.php')) ?>"><?= icon('back') ?> แพ็กเกจทั้งหมด</a>
    <span class="topnav__brand"><span class="appbar__logo"><?= logo_svg() ?></span> Mykid</span>
</nav>

<section class="landing-hero landing-hero--<?= e($pkg['tier']) ?>">
    <span class="landing-hero__decor landing-hero__decor--sun"><?= decor_svg('sun') ?></span>
    <span class="landing-hero__decor landing-hero__decor--rainbow"><?= decor_svg('rainbow') ?></span>
    <span class="landing-hero__decor landing-hero__decor--flower"><?= decor_svg('flower') ?></span>

    <span class="pill pill--white"><?= e($pkg['code']) ?> · <?= e($pkg['statusLabel']) ?> <span class="tier-badge"><?= e($pkg['tierLabel']) ?></span></span>
    <h1 class="landing-hero__title"><?= e($pkg['name']) ?></h1>
    <p class="landing-hero__tagline"><?= e($pkg['landingTitle']) ?></p>
    <p class="hint"><?= e($pkg['landingSub']) ?></p>

    <div class="landing-hero__price">
        <?php if ($pkg['price']): ?>
            <strong><?= e($pkg['price']) ?></strong><span><?= e($pkg['priceUnit']) ?></span>
        <?php else: ?>
            <strong class="landing-hero__price-note"><?= e($pkg['priceNote']) ?></strong><span><?= e($pkg['priceUnit']) ?></span>
        <?php endif; ?>
    </div>
    <ul class="inline-checks">
        <?php foreach ($pkg['includes'] as $item): ?><li><?= icon('check') ?><?= e($item) ?></li><?php endforeach; ?>
    </ul>
    <a class="btn btn--primary btn--lg landing-hero__cta" href="login.php">เข้าสู่ Demo <?= icon('next') ?></a>
    <?php if ($pkg['multiSchool']): ?>
        <a class="btn btn--soft landing-hero__cta" href="demo.php">🏫 คู่มือ Demo หลายโรงเรียน</a>
    <?php endif; ?>
</section>

<section class="landing-section">
    <?php section_head('✨', 'ฟีเจอร์ทั้งหมด'); ?>
    <ul class="check-grid">
        <?php foreach ($pkg['features'] as [$emoji, $label]): ?>
            <li><span class="check-grid__tick" aria-hidden="true">✓</span><span aria-hidden="true"><?= $emoji ?></span> <?= e($label) ?></li>
        <?php endforeach; ?>
    </ul>
</section>

<section class="landing-section">
    <?php section_head('👥', 'ผู้ใช้งาน ' . count($pkg['roles']) . ' บทบาท'); ?>
    <div class="role-grid role-grid--multi">
        <?php foreach ($pkg['roles'] as $role): ?>
            <?php $meta = role_meta($role); ?>
            <article class="role-card role-card--<?= e($meta['theme']) ?>">
                <span class="role-card__emoji" aria-hidden="true"><?= $meta['emoji'] ?></span>
                <h3><?= e($meta['th']) ?> <small><?= e($meta['label']) ?></small></h3>
                <p><?= e($roleInfo[$role]) ?></p>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="landing-section">
    <?php section_head('🔐', 'สิทธิ์และการแยกข้อมูล'); ?>
    <div class="card scope">
        <p class="scope-flow">USER → ROLE → <?= $pkg['multiSchool'] ? 'SCHOOL → ' : '' ?>CLASSROOM / STUDENT → DATA</p>
        <ul class="scope__list">
            <?php if ($pkg['multiSchool']): ?>
                <li class="is-yes">ทุกข้อมูลผูกกับ school_id — โรงเรียนหนึ่งมองไม่เห็นข้อมูลอีกโรงเรียน</li>
            <?php endif; ?>
            <li class="is-yes">ครูเห็นเฉพาะห้องของตัวเอง ผู้ปกครองเห็นเฉพาะลูกของตัวเอง</li>
            <li class="is-yes">ผู้บริหารดูรายงานได้ทั้งโรงเรียน แต่แก้ไขข้อมูลไม่ได้</li>
            <li class="is-yes">สิทธิ์มาจาก Session เท่านั้น เปลี่ยนผ่าน URL ไม่ได้</li>
        </ul>
    </div>
</section>

<section class="landing-section">
    <?php section_head('🏫', 'ข้อมูลตัวอย่างใน Demo'); ?>
    <div class="school-cards">
        <?php foreach ($schools as $school): ?>
            <?php $count = count(where($students, 'school_id', $school['id'])); ?>
            <div class="card demo-school">
                <p class="demo-school__name"><?= $school['emoji'] ?> <?= e($school['name']) ?></p>
                <div class="stat-row">
                    <div class="stat"><strong><?= count(where(store_rows('classrooms'), 'school_id', $school['id'])) ?></strong><span>ห้องเรียน</span></div>
                    <div class="stat"><strong><?= $count ?></strong><span>นักเรียน</span></div>
                    <div class="stat"><strong><?= count(where(store_rows('teachers'), 'school_id', $school['id'])) ?></strong><span>ครู</span></div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
    <p class="hint hint--card">🔑 มีบัญชีทดลอง <?= count($users) ?> บัญชี ครบทุกบทบาท<?= $pkg['multiSchool'] ? ' และทุกโรงเรียน' : '' ?> — เลือกได้ในหน้าเข้าสู่ระบบ (PIN 123456 ทุกบัญชี)</p>
    <a class="btn btn--primary btn--lg btn--block landing-cta-bottom" href="login.php">เข้าสู่ Demo <?= icon('next') ?></a>
</section>

<p class="landing-footer">© <?= (int) date('Y') + 543 ?> Mykid · เวอร์ชันสาธิต (Demo)</p>

<?php mk_footer(); ?>
