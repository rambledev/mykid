<?php
/**
 * รับ-ส่ง (student pickup) — Package A.
 *
 *   Parent  : แจ้งมารับ (ETA 5–30 min)          → coming
 *   Teacher : เตรียมนักเรียน                      → preparing
 *   Teacher : นักเรียนถึงจุดรับแล้ว               → waiting
 *   Teacher : ส่งมอบนักเรียนเรียบร้อย              → completed (+ completed_at / completed_by)
 *
 * Every read goes through scoped('pickups') (session user → school → classroom / child);
 * every write re-checks role + scope on the server. No GPS / location of any kind:
 * the only "where" is the ETA the parent picks.
 */
declare(strict_types=1);

const PICKUP_ETA_OPTIONS = [5, 10, 15, 20, 30];

/** Allowed transitions: current status → the only next status (teacher side). */
const PICKUP_FLOW = ['coming' => 'preparing', 'preparing' => 'waiting', 'waiting' => 'completed'];

/** Teacher button label for moving a request out of its current status. */
const PICKUP_ACTIONS = [
    'coming'    => ['icon' => '🚶', 'label' => 'เตรียมนักเรียน'],
    'preparing' => ['icon' => '📍', 'label' => 'นักเรียนถึงจุดรับแล้ว'],
    'waiting'   => ['icon' => '🤝', 'label' => 'ส่งมอบนักเรียนเรียบร้อย'],
];

function pickup_status_meta(string $status): array
{
    return cat_find('pickupStatus', $status) ?? ['code' => $status, 'label' => $status, 'emoji' => '•', 'tone' => 'neutral', 'parent' => '', 'teacher' => ''];
}

/** Today's pickups the session user may see (newest request first). */
function pickup_today_rows(): array
{
    $rows = array_values(array_filter(scoped('pickups'), fn ($p) => ($p['date'] ?? '') === today()));
    usort($rows, fn ($a, $b) => strcmp($b['requested_at'], $a['requested_at']));
    return $rows;
}

/** Latest pickup of one child today (null = not requested yet). */
function pickup_for_student(int $studentId): ?array
{
    foreach (pickup_today_rows() as $p) {
        if ($p['student_id'] === $studentId) {
            return $p;
        }
    }
    return null;
}

/** Children of the logged-in parent (scope-filtered; one card per child). */
function parent_children(array $user): array
{
    return array_values(array_filter(scoped('students'), fn ($s) => $s['id'] === $user['student_id']));
}

/** Plain data sent to the browser by pickup_status (no internal ids beyond the row / child). */
function pickup_public(array $p): array
{
    $meta = pickup_status_meta($p['status']);
    return [
        'id' => $p['id'], 'student_id' => $p['student_id'], 'status' => $p['status'],
        'label' => $meta['emoji'] . ' ' . $meta['label'], 'message' => $meta['parent'],
        'eta_minutes' => $p['eta_minutes'], 'requested_at' => pickup_hm($p['requested_at']), 'eta_at' => pickup_hm($p['eta_at']),
        'completed_at' => pickup_hm($p['completed_at'] ?? null),
    ];
}

/** Changes whenever a visible pickup is added or moves to another status. */
function pickup_signature(array $rows): string
{
    return md5(json_encode(array_map(fn ($p) => [$p['id'], $p['status']], $rows)));
}

function pickup_hm(?string $datetime): string
{
    return $datetime ? date('H:i', strtotime($datetime)) : '';
}

/** Parent: create a request for one of their own children. Returns the new row. */
function pickup_create(array $user, int $studentId, int $etaMinutes): array
{
    mk_log('Pickup', 'pickup_create START', ['user' => $user['id'], 'student_id' => $studentId, 'eta' => $etaMinutes]);
    authorize($user['role'] === 'parent' && can_write_table($user, 'pickups'), 'บัญชีนี้ไม่สามารถแจ้งมารับได้', ['user' => $user['id'], 'role' => $user['role']]);
    if (!in_array($etaMinutes, PICKUP_ETA_OPTIONS, true)) {
        throw new DemoValidationException('กรุณาเลือกเวลาที่จะถึงโรงเรียน');
    }
    $student = find_row('students', $studentId);
    authorize($student !== null && row_in_scope($user, 'students', $student), 'ไม่มีสิทธิ์แจ้งมารับนักเรียนคนนี้', ['user' => $user['id'], 'student_id' => $studentId]);
    $scope = apply_write_scope($user, 'pickups', ['student_id' => $student['id']]); // school / classroom / child from the session

    $now = time();
    $row = $scope + [
        'date' => today(), 'status' => 'coming',
        'parent_id' => $user['id'], 'parent_name' => 'คุณ' . ($user['relation'] ?? 'แม่'),
        'eta_minutes' => $etaMinutes, 'requested_at' => date('Y-m-d H:i:s', $now), 'eta_at' => date('Y-m-d H:i:s', $now + $etaMinutes * 60),
        'preparing_at' => null, 'waiting_at' => null, 'completed_at' => null, 'completed_by' => null, 'completed_by_name' => null,
    ];
    $created = store_mutate(function (array &$state) use ($row) {
        foreach ($state['tables']['pickups'] ?? [] as $p) {
            if ($p['student_id'] === $row['student_id'] && $p['date'] === $row['date']) {
                throw new DemoValidationException($p['status'] === 'completed' ? 'วันนี้ส่งมอบนักเรียนเรียบร้อยแล้ว' : 'แจ้งมารับไว้แล้ว กรุณารอการอัปเดตจากครู');
            }
        }
        $row = ['id' => $state['nextId']++] + $row;
        $state['tables']['pickups'][] = $row;
        return $row;
    })['result'];
    mk_log('Pickup', 'pickup_create END', ['id' => $created['id'], 'student_id' => $created['student_id'], 'eta_at' => $created['eta_at']]);
    return $created;
}

/** Teacher: move a request of their own classroom to the next status. Returns the updated row. */
function pickup_advance(array $user, int $id, string $to): array
{
    mk_log('Pickup', 'pickup_advance START', ['user' => $user['id'], 'id' => $id, 'to' => $to]);
    authorize($user['role'] === 'teacher' && can_write_table($user, 'pickups'), 'บัญชีนี้ไม่สามารถอัปเดตการรับ-ส่งได้', ['user' => $user['id'], 'role' => $user['role']]);
    $updated = store_mutate(function (array &$state) use ($user, $id, $to) {
        $index = store_find($state, 'pickups', $id);
        $row = $index === null ? null : $state['tables']['pickups'][$index];
        authorize($row !== null && row_in_scope($user, 'pickups', $row), 'ไม่มีสิทธิ์จัดการการรับ-ส่งของนักเรียนคนนี้', ['user' => $user['id'], 'id' => $id]);
        if ((PICKUP_FLOW[$row['status']] ?? null) !== $to) {
            throw new DemoValidationException('สถานะมีการเปลี่ยนแปลงแล้ว กรุณาลองใหม่อีกครั้ง');
        }
        $row['status'] = $to;
        $row[$to . '_at'] = date('Y-m-d H:i:s');
        if ($to === 'completed') {
            $row['completed_by'] = $user['id'];
            $row['completed_by_name'] = $user['name'];
        }
        $state['tables']['pickups'][$index] = $row;
        return $row;
    })['result'];
    mk_log('Pickup', 'pickup_advance END', ['id' => $id, 'status' => $updated['status']]);
    return $updated;
}
