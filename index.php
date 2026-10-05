<?php
declare(strict_types=1);

require __DIR__ . '/includes/common.php';
$packages = require __DIR__ . '/includes/packages.php';
?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="theme-color" content="#FFF6E9">
    <title>Mykid — ระบบติดตามและดูแลเด็กสำหรับโรงเรียนอนุบาล</title>
    <link rel="stylesheet" href="<?= e(root_asset('assets/css/style.css')) ?>">
</head>
<body class="site">
<?= sky_decor() ?>

<main class="site__main">
    <header class="hero">
        <span class="hero__logo"><?= logo_svg() ?></span>
        <h1 class="hero__title">Mykid</h1>
        <p class="hero__subtitle">ระบบติดตามและดูแลเด็กสำหรับโรงเรียนอนุบาล</p>
        <span class="pill pill--soft">✨ Demo สำหรับนำเสนอ</span>
    </header>

    <section class="packages" aria-label="แพ็กเกจ Mykid">
        <?php foreach ($packages as $pkg): ?>
            <?php $featured = !empty($pkg['featured']); ?>
            <article class="pkg <?= $featured ? 'pkg--featured' : '' ?> pkg--<?= e($pkg['key']) ?>">
                <div class="pkg__head">
                    <span class="pkg__emoji" aria-hidden="true"><?= $pkg['emoji'] ?></span>
                    <div>
                        <p class="pkg__code"><?= e($pkg['code']) ?></p>
                        <h2 class="pkg__name"><?= e($pkg['name']) ?></h2>
                    </div>
                </div>

                <p class="pkg__tagline"><?= e($pkg['tagline']) ?></p>

                <p class="pkg__price">
                    <?php if ($pkg['price']): ?>
                        <strong><?= e($pkg['price']) ?></strong>
                    <?php else: ?>
                        <strong class="pkg__price-note"><?= e($pkg['priceNote']) ?></strong>
                    <?php endif; ?>
                    <span><?= e($pkg['priceUnit']) ?></span>
                </p>
                <?php if ($featured): ?>
                    <p class="pkg__includes-title">รวม:</p>
                    <ul class="checklist">
                        <?php foreach ($pkg['includes'] as $item): ?>
                            <li><?= e($item) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php else: ?>
                    <ul class="pkg__features">
                        <?php foreach ($pkg['features'] as [$emoji, $label]): ?>
                            <li><span aria-hidden="true"><?= $emoji ?></span> <?= e($label) ?></li>
                        <?php endforeach; ?>
                    </ul>
                <?php endif; ?>
                <div class="pkg__roles"><?= role_chips($pkg['roles']) ?></div>

                <a class="btn <?= $featured ? 'btn--primary' : 'btn--outline' ?> btn--block" href="<?= e($pkg['href']) ?>">
                    <?= e($pkg['cta']) ?> <span aria-hidden="true">→</span>
                </a>
            </article>
        <?php endforeach; ?>
    </section>
</main>

<footer class="site__footer">
    <p>© <?= (int) date('Y') + 543 ?> Mykid · เวอร์ชันสาธิต (Demo)</p>
</footer>

<script src="<?= e(root_asset('assets/js/app.js')) ?>"></script>
</body>
</html>
