<?php
/**
 * Package A — Multi-School demo guide (public, like the landing page).
 * Shows each demo school with its accounts, a presentation script, and what each account
 * can / cannot see. Account buttons open login.php?demo=<phone> which pre-fills the form.
 */
declare(strict_types=1);

$schools = store_rows('schools');
$users = store_rows('users');
$count = fn (string $table, int $sid) => count(where(store_rows($table), 'school_id', $sid));
$login = fn (array $u) => 'login.php?role=' . $u['role'] . '&demo=' . $u['phone'];
$scopeOf = function (array $u): string {
    return match ($u['role']) {
        'admin', 'executive' => 'ทั้งโรงเรียน',
        'teacher' => 'ห้อง' . (find_row('classrooms', (int) $u['classroom_id'])['name'] ?? ''),
        'parent' => (find_row('students', (int) $u['student_id'])['nickname'] ?? '') . ' · ' . (find_row('classrooms', (int) $u['classroom_id'])['name'] ?? ''),
        default => 'ทุกโรงเรียน',
    };
};
$super = array_values(array_filter($users, fn ($u) => $u['role'] === 'super_admin'))[0];
$byRole = fn (int $sid, string $role) => array_values(array_filter($users, fn ($u) => $u['school_id'] === $sid && $u['role'] === $role));
$first = fn (int $sid, string $role) => $byRole($sid, $role)[0] ?? null;

$steps = [
    ['🪐', 'Super Admin เห็นทุกโรงเรียน', 'ภาพรวม ' . count($schools) . ' โรงเรียน → “ดูรายโรงเรียน” → “เปรียบเทียบ”', $super],
    ['🛡️', 'Admin โรงเรียนที่ 1 เห็นเฉพาะโรงเรียนตัวเอง', 'เปิดเมนูนักเรียน แล้วลองเติม ?school_id=2 ท้าย URL → ระบบไม่ยอม', $first(1, 'admin')],
    ['🛡️', 'Admin โรงเรียนที่ 2 — ข้อมูลเปลี่ยนทั้งชุด', 'รายชื่อนักเรียน ครู ห้อง กล้อง เป็นของโรงเรียนที่ 2 ทั้งหมด', $first(2, 'admin')],
    ['💼', 'ผู้บริหารดูรายงานของโรงเรียนตัวเอง (อ่านอย่างเดียว)', 'KPI กราฟ และรายงาน คำนวณจากข้อมูลของโรงเรียนนี้เท่านั้น', $first(2, 'executive')],
    ['👩‍🏫', 'ครูเห็นเฉพาะห้องตัวเองในโรงเรียนตัวเอง', 'นักเรียน เช็คชื่อ แชท กล้อง เฉพาะห้องที่รับผิดชอบ', $first(2, 'teacher')],
    ['👩', 'ผู้ปกครองเห็นเฉพาะลูกตัวเอง', 'โรงเรียนอื่นใช้แอปเดียวกัน แต่มองไม่เห็นกันเลย', $first(2, 'parent')],
];

mk_header(['id' => 'demo-guide', 'title' => 'คู่มือ Demo Multi-School', 'layout' => 'landing', 'theme' => 'landing']);
?>
<nav class="topnav">
    <a class="topnav__back" href="index.php"><?= icon('back') ?> Mykid Full</a>
    <span class="topnav__brand"><span class="appbar__logo"><?= logo_svg() ?></span> Mykid</span>
</nav>

<section class="landing-hero landing-hero--full">
    <span class="landing-hero__decor landing-hero__decor--sun"><?= decor_svg('sun') ?></span>
    <span class="pill pill--white">Package A · Multi-School Demo</span>
    <h1 class="landing-hero__title">หลายโรงเรียน ระบบเดียว</h1>
    <p class="landing-hero__tagline"><?= count($schools) ?> โรงเรียน · <?= count(store_rows('students')) ?> นักเรียน · <?= count(store_rows('teachers')) ?> ครู · <?= count($users) ?> บัญชีทดลอง</p>
    <p class="scope-flow">USER → ROLE → SCHOOL (school_id) → CLASSROOM → STUDENT → DATA</p>
    <p class="hint">ทุกข้อมูลผูกกับ school_id และสิทธิ์มาจาก Session เท่านั้น — เปลี่ยนโรงเรียนผ่าน URL ไม่ได้ · PIN 123456 ทุกบัญชี</p>
</section>

<section class="landing-section">
    <?php section_head('🎬', 'ขั้นตอนนำเสนอ (แนะนำ)'); ?>
    <ol class="guide-steps">
        <?php foreach ($steps as $i => [$emoji, $title, $detail, $account]): ?>
            <?php if (!$account) { continue; } ?>
            <li class="card guide-step">
                <span class="guide-step__no"><?= $i + 1 ?></span>
                <div class="guide-step__body">
                    <strong><?= $emoji ?> <?= e($title) ?></strong>
                    <p><?= e($detail) ?></p>
                    <small><?= e($account['name']) ?> · <?= e($account['phone']) ?><?= $account['school_id'] ? ' · ' . e(find_row('schools', $account['school_id'])['shortName']) : '' ?></small>
                </div>
                <a class="btn btn--primary btn--sm" href="<?= e($login($account)) ?>">เข้าสู่ระบบ</a>
            </li>
        <?php endforeach; ?>
    </ol>
</section>

<section class="landing-section">
    <?php section_head('🏫', 'โรงเรียนและบัญชีทดลอง', '', 'กด “เข้าสู่ระบบ” แล้วระบบจะกรอกเบอร์และ PIN ให้'); ?>
    <div class="guide-schools">
        <?php foreach ($schools as $s): ?>
            <article class="card guide-school">
                <header class="room-card__head">
                    <span class="room-card__emoji" aria-hidden="true"><?= $s['emoji'] ?></span>
                    <h2><?= e($s['name']) ?></h2>
                    <?= $s['status'] === 'active' ? '<span class="tone tone--good">✅ ใช้งาน</span>' : '<span class="tone tone--warning">🧪 ทดลองใช้</span>' ?>
                </header>
                <p class="hint">school_id = <?= $s['id'] ?> · <?= e($s['province']) ?></p>
                <div class="stat-row">
                    <div class="stat"><strong><?= $count('classrooms', $s['id']) ?></strong><span>ห้อง</span></div>
                    <div class="stat"><strong><?= $count('students', $s['id']) ?></strong><span>นักเรียน</span></div>
                    <div class="stat"><strong><?= $count('teachers', $s['id']) ?></strong><span>ครู</span></div>
                    <div class="stat"><strong><?= $count('cameras', $s['id']) ?></strong><span>กล้อง</span></div>
                </div>
                <ul class="guide-accounts">
                    <?php foreach (['admin', 'executive', 'teacher', 'parent'] as $role): ?>
                        <?php foreach ($byRole($s['id'], $role) as $u): ?>
                            <li>
                                <span class="guide-accounts__role role-chip--<?= e(role_meta($role)['theme']) ?>"><?= role_meta($role)['emoji'] ?></span>
                                <div><strong><?= e($u['name']) ?></strong><small><?= e($u['phone']) ?> · <?= e($scopeOf($u)) ?></small></div>
                                <a class="btn btn--soft btn--sm" href="<?= e($login($u)) ?>">เข้าสู่ระบบ</a>
                            </li>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </ul>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="landing-section">
    <?php section_head('🔐', 'ใครเห็นอะไร'); ?>
    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead><tr><th scope="col">บัญชี</th><th scope="col">เห็น</th><th scope="col">ไม่เห็น</th></tr></thead>
                <tbody>
                    <tr><th scope="row">🪐 Super Admin</th><td>ทุกโรงเรียน (<?= count($schools) ?> แห่ง)</td><td>—</td></tr>
                    <tr><th scope="row">🛡️ Admin / 💼 ผู้บริหาร</th><td>ทุกข้อมูลในโรงเรียนตัวเอง</td><td>โรงเรียนอื่นทั้งหมด</td></tr>
                    <tr><th scope="row">👩‍🏫 ครู</th><td>ห้องตัวเอง + กล้องส่วนกลางที่อนุญาต</td><td>ห้องอื่น โรงเรียนอื่น</td></tr>
                    <tr><th scope="row">👨‍👩‍👧 ผู้ปกครอง</th><td>ลูกตัวเอง + ห้องของลูก</td><td>เด็กคนอื่น ห้องอื่น โรงเรียนอื่น</td></tr>
                </tbody>
            </table>
        </div>
    </div>
    <a class="btn btn--primary btn--lg btn--block landing-cta-bottom" href="<?= e($login($super)) ?>">เริ่ม Demo ด้วย Super Admin <?= icon('next') ?></a>
</section>

<p class="landing-footer">© <?= (int) date('Y') + 543 ?> Mykid · เวอร์ชันสาธิต (Demo)</p>
<?php mk_footer(); ?>
