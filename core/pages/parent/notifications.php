<?php
/** Parent (Package A) — announcements, events, teacher messages, alerts. */
declare(strict_types=1);

$user = current_user();
$rows = scoped('notifications');
foreach (scoped('messages') as $m) {
    if ($m['from'] === 'teacher') {
        $rows[] = ['type' => 'teacher', 'title' => 'ข้อความจาก' . $m['sender'], 'body' => $m['text'], 'at' => $m['at']];
    }
}

mk_header(['id' => 'parent-notifications', 'title' => 'การแจ้งเตือน', 'nav' => 'notifications']);
page_title('🔔', 'การแจ้งเตือน', 'ประกาศโรงเรียน · กิจกรรม · ข้อความจากครู');
?>
<section class="card" data-live id="live-notifications"><?php render_notification_list($rows); ?></section>
<?php mk_footer(); ?>
