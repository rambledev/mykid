<?php
/** Package C — Parent: today's food menu. */
declare(strict_types=1);

require __DIR__ . '/../includes/bootstrap.php';

$user = require_role('parent');
$ctx = page_context($user);

$page = ['id' => 'parent-food', 'title' => 'อาหารวันนี้', 'layout' => 'app', 'nav' => 'food'];
require PACKC_ROOT . '/includes/header.php';

page_title('🍽️', 'อาหารวันนี้', 'เมนูที่' . $ctx['child']['nickname'] . 'ได้ทานวันนี้');
fragment('food-view', $ctx, 'section');

require PACKC_ROOT . '/includes/footer.php';
