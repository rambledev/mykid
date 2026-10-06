<?php
/** Parent (Package A) — รับ-ส่งบุตรหลาน: notify the teacher (ETA) and follow the status live. Own children only. */
declare(strict_types=1);

$user = current_user();
$children = parent_children($user);
$steps = mk_catalog()['pickupStatus'];
$order = array_column($steps, 'code');

mk_header(['id' => 'parent-pickup', 'title' => 'รับ-ส่ง', 'nav' => 'pickup']);
page_title('🚸', 'รับ-ส่งบุตรหลาน', thai_date());
?>
<?php $rows = pickup_today_rows(); ?>
<div data-live id="live-pickup" data-pickup-live data-pickup-signature="<?= e(pickup_signature($rows)) ?>">
    <?php foreach ($children as $child): ?>
        <?php
        $p = pickup_for_student($child['id']);
        $meta = $p ? pickup_status_meta($p['status']) : null;
        $reached = $p ? array_search($p['status'], $order, true) : -1;
        ?>
        <section class="card pickup-card<?= $p ? ' pickup-card--' . e($p['status']) : '' ?>">
            <div class="pickup-card__child">
                <?= student_avatar($child, 'md') ?>
                <div>
                    <strong><?= e($child['nickname']) ?></strong>
                    <small><?= e($child['name']) ?> · ห้อง<?= e($child['classroom']) ?></small>
                </div>
            </div>

            <?php if (!$p): ?>
                <div class="pickup-state pickup-state--none">
                    <span class="pickup-state__emoji" aria-hidden="true">🏫</span>
                    <p><strong>ยังไม่ได้แจ้งมารับวันนี้</strong>กดปุ่มด้านล่างเมื่อกำลังเดินทางมาโรงเรียน ครูจะเตรียม<?= e($child['nickname']) ?>ไว้ที่จุดรับ</p>
                </div>
                <button type="button" class="btn btn--primary btn--block" data-action="pickup-open" data-student="<?= $child['id'] ?>" data-name="<?= e($child['nickname']) ?>">🚗 แจ้งมารับ</button>
            <?php else: ?>
                <div class="pickup-state pickup-state--<?= e($p['status']) ?>" role="status">
                    <span class="pickup-state__emoji" aria-hidden="true"><?= $meta['emoji'] ?></span>
                    <p>
                        <strong><?= e($meta['parent']) ?></strong>
                        <?php if ($p['status'] === 'coming'): ?>
                            แจ้งครูแล้ว<br>คุณจะถึงโรงเรียนภายใน <?= (int) $p['eta_minutes'] ?> นาที (ประมาณ <?= e(pickup_hm($p['eta_at'])) ?> น.)
                        <?php elseif ($p['status'] === 'preparing'): ?>
                            คุณครูกำลังพา<?= e($child['nickname']) ?>ไปรอที่จุดรับกลับบ้าน
                        <?php elseif ($p['status'] === 'waiting'): ?>
                            ครูพานักเรียนมาถึงจุดรับกลับบ้านแล้ว<br>กรุณามารับนักเรียนได้เลย
                        <?php else: ?>
                            เวลา <?= e(pickup_hm($p['completed_at'])) ?> น. · <?= e($p['completed_by_name'] ?? '') ?>
                        <?php endif; ?>
                    </p>
                </div>
                <ol class="pickup-steps" aria-label="ขั้นตอนการรับ-ส่ง">
                    <?php foreach ($steps as $i => $st): ?>
                        <?php $time = $p[$st['code'] === 'coming' ? 'requested_at' : $st['code'] . '_at'] ?? null; ?>
                        <li class="pickup-steps__item<?= $i <= $reached ? ' is-done' : '' ?><?= $i === $reached ? ' is-current' : '' ?>">
                            <span aria-hidden="true"><?= $st['emoji'] ?></span>
                            <small><?= e($st['label']) ?></small>
                            <time><?= $i <= $reached && $time ? e(pickup_hm($time)) : '' ?></time>
                        </li>
                    <?php endforeach; ?>
                </ol>
            <?php endif; ?>
        </section>
    <?php endforeach; ?>
    <?php if (!$children): ?>
        <?= empty_state('🚸', 'ไม่พบข้อมูลบุตรหลาน') ?>
    <?php endif; ?>
</div>
<p class="hint hint--card">🔒 ครูจะส่งตัวนักเรียนที่จุดรับกลับบ้านเท่านั้น · หน้านี้อัปเดตสถานะอัตโนมัติ</p>

<?php render_sheet_open('sheet-pickup', 'แจ้งมารับ', 'ครูจะได้รับแจ้งทันที'); ?>
<form class="form" data-pickup-form novalidate>
    <input type="hidden" name="student_id" value="">
    <fieldset class="pickup-eta">
        <legend>จะถึงโรงเรียนใน</legend>
        <div class="pickup-eta__options">
            <?php foreach (PICKUP_ETA_OPTIONS as $min): ?>
                <label class="pickup-eta__option">
                    <input type="radio" name="eta_minutes" value="<?= $min ?>">
                    <span><strong><?= $min ?></strong> นาที</span>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>
    <p class="form__error" data-form-error hidden></p>
    <div class="row-actions">
        <button type="button" class="btn btn--ghost" data-action="close-sheet">ยกเลิก</button>
        <button type="submit" class="btn btn--primary"><?= icon('check') ?> ยืนยันการมารับ</button>
    </div>
</form>
<?php render_sheet_close(); ?>
<?php mk_footer(); ?>
