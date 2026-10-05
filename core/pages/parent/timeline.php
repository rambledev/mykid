<?php
/** Parent (Package A) — daily timeline: schedule + teacher updates + check-in, meals, sleep, pickup. */
declare(strict_types=1);

$user = current_user();
$child = $user['child'];
$cid = $child['classroom_id'];

$events = [];
foreach (classroom_activities($cid) as $a) {
    $events[] = ['time' => $a['time'], 'icon' => $a['icon'], 'title' => $a['title'], 'detail' => $a['detail'], 'kind' => 'schedule'];
}
foreach (classroom_status_history($cid) as $s) {
    $events[] = ['time' => date('H:i', strtotime($s['at'])), 'icon' => $s['emoji'], 'title' => 'คุณครูอัปเดต: ' . $s['text'], 'detail' => 'โดย ' . $s['byName'], 'kind' => 'status'];
}
$att = student_today('attendance', $child['id']);
if ($att && $att['checkIn']) {
    $events[] = ['time' => $att['checkIn'], 'icon' => '🏫', 'title' => $child['nickname'] . 'มาถึงโรงเรียน', 'detail' => cat_find('attendance', $att['status'])['label'], 'kind' => 'child'];
}
$sleep = student_today('sleepRecords', $child['id']);
if ($sleep && $sleep['start']) {
    $events[] = ['time' => $sleep['start'], 'icon' => '😴', 'title' => $child['nickname'] . 'เริ่มนอนกลางวัน', 'detail' => $sleep['end'] ? 'ตื่น ' . $sleep['end'] . ' น. · นอน ' . thai_duration(minutes_between($sleep['start'], $sleep['end'])) : '', 'kind' => 'child'];
}
$pickup = student_today('pickupRequests', $child['id']);
if ($pickup) {
    $events[] = ['time' => $pickup['time'], 'icon' => '🚗', 'title' => 'ผู้มารับ: ' . $pickup['person'], 'detail' => cat_find('pickupStatus', $pickup['status'])['label'], 'kind' => 'child'];
}
usort($events, fn ($a, $b) => strcmp($a['time'], $b['time']));

mk_header(['id' => 'parent-timeline', 'title' => 'ไทม์ไลน์วันนี้', 'nav' => 'timeline']);
page_title('🕘', 'ไทม์ไลน์วันนี้', thai_date() . ' · ' . $child['nickname']);
?>
<div data-live id="live-timeline">
    <section class="card">
        <ul class="chart-legend timeline-legend">
            <li><span class="chart-legend__swatch kind--schedule"></span>ตารางกิจกรรม</li>
            <li><span class="chart-legend__swatch kind--status"></span>อัปเดตจากครู</li>
            <li><span class="chart-legend__swatch kind--child"></span>เรื่องของ<?= e($child['nickname']) ?></li>
        </ul>
        <ol class="timeline timeline--rich">
            <?php foreach ($events as $ev): ?>
                <li class="timeline__item kind--<?= e($ev['kind']) ?>">
                    <span class="timeline__time"><?= e($ev['time']) ?></span>
                    <span class="timeline__dot" aria-hidden="true"><?= $ev['icon'] ?></span>
                    <div class="timeline__body"><strong><?= e($ev['title']) ?></strong><?php if ($ev['detail']): ?><p><?= e($ev['detail']) ?></p><?php endif; ?></div>
                </li>
            <?php endforeach; ?>
        </ol>
    </section>
</div>
<?php mk_footer(); ?>
