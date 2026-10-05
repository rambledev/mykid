<?php
/** Parent (Package A) — who picks the child up today. */
declare(strict_types=1);

$user = current_user();
$child = $user['child'];

mk_header(['id' => 'parent-pickup', 'title' => 'ผู้มารับ', 'nav' => 'pickup']);
page_title('🚗', 'ผู้มารับวันนี้', $child['nickname'] . ' · ' . thai_date());
?>
<div data-live id="live-pickup">
    <?php $p = student_today('pickupRequests', $child['id']); ?>
    <?php if ($p): ?>
        <section class="card pickup-card">
            <span class="pickup-card__emoji" aria-hidden="true">🙋</span>
            <ul class="menu-list">
                <li><span>ผู้มารับ</span><strong><?= e($p['person']) ?></strong></li>
                <li><span>ความสัมพันธ์</span><strong><?= e($p['relation']) ?></strong></li>
                <li><span>เวลารับ</span><strong><?= e($p['time']) ?> น.</strong></li>
                <li><span>สถานะ</span><?= tone_badge(cat_find('pickupStatus', $p['status'])) ?></li>
            </ul>
            <div class="row-actions">
                <button type="button" class="btn btn--soft" data-action="open-form" data-table="pickupRequests" data-record="<?= json_attr($p) ?>"><?= icon('edit') ?> เปลี่ยนผู้มารับ</button>
                <?php if ($p['status'] === 'pending'): ?>
                    <button type="button" class="btn btn--primary" data-action="quick-save" data-table="pickupRequests" data-id="<?= $p['id'] ?>" data-field="status" data-value="confirmed"><?= icon('check') ?> ยืนยัน</button>
                <?php else: ?>
                    <span class="hint">✅ คุณครูรับทราบแล้ว</span>
                <?php endif; ?>
            </div>
        </section>
    <?php else: ?>
        <?= empty_state('🚗', 'ยังไม่มีข้อมูลผู้มารับวันนี้') ?>
    <?php endif; ?>
</div>
<p class="hint hint--card">🔒 คุณครูจะส่งตัวเด็กให้เฉพาะผู้มารับที่ยืนยันไว้เท่านั้น</p>
<?php
render_form_sheet('pickupRequests', 'เปลี่ยนผู้มารับ', ['student_id']);
mk_footer();
