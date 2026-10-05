<?php
/** Admin — menus per classroom (own school). */
declare(strict_types=1);

$rooms = my_classrooms();
mk_header(['id' => 'admin-food', 'title' => 'รายการอาหาร', 'nav' => 'food']);
page_title('🍱', 'รายการอาหารวันนี้', 'กำหนดเมนูให้แต่ละห้อง');
tab_bar('rooms', array_column(array_map(fn ($r) => ['k' => $r['id'], 'v' => $r['emoji'] . ' ' . $r['name']], $rooms), 'v', 'k'));
?>
<div data-live id="live-admin-food">
    <?php foreach ($rooms as $i => $room): ?>
        <section data-tab-panel="rooms:<?= $room['id'] ?>"<?= $i ? ' hidden' : '' ?>>
            <?php render_food_cards(classroom_food($room['id']), $room['id']); ?>
        </section>
    <?php endforeach; ?>
</div>
<?php mk_footer(); ?>
