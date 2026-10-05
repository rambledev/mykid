<?php
/** Package C — demo login (role + phone + 6-digit PIN). */
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

if ($existing = current_user()) {
    redirect(dashboard_path($existing['role']));
}

$role = in_array($_POST['role'] ?? $_GET['role'] ?? '', ROLES, true) ? ($_POST['role'] ?? $_GET['role']) : 'teacher';
$phone = '';
$error = flash('login_error');
$notice = isset($_GET['logout']) ? 'ออกจากระบบเรียบร้อยแล้ว' : null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $phone = normalize_phone((string) ($_POST['phone'] ?? ''));
    $pinInput = $_POST['pin'] ?? '';
    $pin = is_array($pinInput) ? implode('', array_map('strval', $pinInput)) : (string) $pinInput;

    if (!verify_csrf($_POST['csrf'] ?? null)) {
        $error = 'หน้านี้เปิดค้างไว้นานเกินไป กรุณาลองใหม่อีกครั้ง';
    } elseif (strlen($phone) !== 10) {
        $error = 'กรุณากรอกเบอร์โทรศัพท์ 10 หลัก';
    } elseif (!preg_match('/^\d{6}$/', $pin)) {
        $error = 'กรุณากรอกรหัส PIN ให้ครบ 6 หลัก';
    } elseif (auth_login($role, $phone, $pin)) {
        flash('toast', 'เข้าสู่ระบบสำเร็จ ยินดีต้อนรับค่ะ 🎉');
        redirect(dashboard_path($role));
    } else {
        $error = 'เบอร์โทรศัพท์หรือรหัส PIN ไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง';
    }
}

// Demo account selector — shows each account's permission scope.
$demoGroups = [
    'teacher' => array_map(fn ($a) => $a + ['scope' => 'ห้อง' . get_classroom($a['classroom_id'])['name'], 'emoji' => '👩‍🏫'], demo_accounts('teacher')),
    'parent'  => array_map(function ($a) {
        $child = get_student($a['student_id']);
        return $a + ['scope' => 'บุตร: ' . $child['nickname'] . ' · ' . $child['classroom'], 'emoji' => $a['relation'] === 'พ่อ' ? '👨' : '👩'];
    }, demo_accounts('parent')),
];

$user = null;
$page = ['id' => 'login', 'title' => 'เข้าสู่ระบบ', 'layout' => 'auth', 'theme' => $role];
require PACKC_ROOT . '/includes/header.php';
?>

<div class="auth">
    <a class="topnav__back auth__back" href="index.php"><?= icon('back') ?> กลับ</a>

    <div class="auth__art" aria-hidden="true">
        <span class="auth__sun"><?= decor_svg('sun') ?></span>
        <span class="auth__cloud"><?= decor_svg('cloud') ?></span>
        <span class="auth__rainbow"><?= decor_svg('rainbow') ?></span>
        <span class="auth__logo"><?= logo_svg() ?></span>
    </div>

    <h1 class="auth__title">ยินดีต้อนรับสู่ Mykid</h1>
    <p class="auth__sub"><?= e(get_school()['name']) ?></p>

    <form class="auth__card" method="post" action="login.php" data-login-form novalidate>
        <input type="hidden" name="csrf" value="<?= e(csrf_token()) ?>">

        <fieldset class="role-switch">
            <legend class="visually-hidden">เลือกบทบาท</legend>
            <label class="role-switch__item role-switch__item--teacher">
                <input type="radio" name="role" value="teacher"<?= $role === 'teacher' ? ' checked' : '' ?>>
                <span><b aria-hidden="true">👩‍🏫</b>ครู</span>
            </label>
            <label class="role-switch__item role-switch__item--parent">
                <input type="radio" name="role" value="parent"<?= $role === 'parent' ? ' checked' : '' ?>>
                <span><b aria-hidden="true">👨‍👩‍👧</b>ผู้ปกครอง</span>
            </label>
        </fieldset>

        <?php if ($notice): ?><p class="alert alert--ok" role="status">👋 <?= e($notice) ?></p><?php endif; ?>
        <?php if ($error): ?><p class="alert alert--error" role="alert"><?= e($error) ?></p><?php endif; ?>

        <label class="field">
            <span class="field__label"><?= icon('phone') ?> เบอร์โทรศัพท์</span>
            <input class="input input--lg" type="tel" name="phone" inputmode="numeric" autocomplete="tel"
                   maxlength="12" placeholder="08x-xxx-xxxx" value="<?= e($phone) ?>" required data-phone-input>
        </label>

        <div class="field">
            <span class="field__label" id="pin-label"><?= icon('lock') ?> รหัส PIN 6 หลัก</span>
            <div class="pin" role="group" aria-labelledby="pin-label" data-pin>
                <?php for ($i = 0; $i < 6; $i++): ?>
                    <input class="pin__box" type="password" name="pin[]" inputmode="numeric" pattern="[0-9]*"
                           maxlength="1" autocomplete="one-time-code" aria-label="PIN หลักที่ <?= $i + 1 ?>">
                <?php endfor; ?>
            </div>
        </div>

        <button class="btn btn--primary btn--lg btn--block" type="submit">เข้าสู่ระบบ</button>

        <div class="demo-hint">
            <p class="demo-hint__title">🔑 บัญชีทดลอง <small>แตะเพื่อกรอกอัตโนมัติ · PIN 123456 ทุกบัญชี</small></p>
            <?php foreach ($demoGroups as $groupRole => $accounts): ?>
                <ul class="demo-accounts demo-accounts--<?= e($groupRole) ?>" data-demo-group="<?= e($groupRole) ?>">
                    <?php foreach ($accounts as $acc): ?>
                        <li>
                            <button type="button" class="demo-account-btn" data-action="fill-account"
                                    data-role="<?= e($groupRole) ?>" data-phone="<?= e($acc['phone']) ?>" data-pin="<?= e($acc['pin']) ?>">
                                <span class="demo-account-btn__emoji" aria-hidden="true"><?= $acc['emoji'] ?></span>
                                <span class="demo-account-btn__text">
                                    <strong><?= e($acc['name']) ?></strong>
                                    <small><?= e($acc['phone']) ?> · <?= e($acc['scope']) ?></small>
                                </span>
                            </button>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        </div>
    </form>
</div>

<?php require PACKC_ROOT . '/includes/footer.php'; ?>
