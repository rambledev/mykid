<?php
/**
 * Package C — JSON endpoint used by assets/js/app.js (existing UI plumbing).
 *
 * Request:  POST JSON { action, fragments: [keys...], ...payload }  + header X-CSRF-Token
 * Response: { ok, message, version, fragments: { key: html } }
 *
 * Authorisation = ROLE + SCOPE:
 *   - write actions require role "teacher" AND the teacher's own classroom_id
 *   - parents may only read the state of their own child's classroom
 *   - the classroom is always taken from the session; a classroom_id / student_id
 *     in the request that differs from the user's scope is rejected
 */
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

const TEACHER_ACTIONS = ['set_status', 'add_activity', 'update_activity', 'delete_activity', 'save_meal', 'reset_demo'];

$input = json_decode((string) file_get_contents('php://input'), true) ?: [];
$action = (string) ($input['action'] ?? '');
mk_log('Api', 'request START', ['action' => $action, 'input' => $input]);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'ไม่สามารถดำเนินการได้'], 405);
}

$user = current_user();
if (!$user) {
    json_response(['ok' => false, 'message' => 'กรุณาเข้าสู่ระบบอีกครั้ง', 'redirect' => url('login.php')], 401);
}
if (!verify_csrf($_SERVER['HTTP_X_CSRF_TOKEN'] ?? null)) {
    json_response(['ok' => false, 'message' => 'กรุณารีเฟรชหน้าแล้วลองใหม่อีกครั้ง'], 419);
}

try {
    // SCOPE comes from the session only.
    $classroomId = scope_classroom_id($user);

    // A client may not point the request at another classroom or child.
    if (isset($input['classroom_id'])) {
        authorize(can_view_classroom($user, (int) $input['classroom_id']), 'ไม่มีสิทธิ์เข้าถึงข้อมูลของห้องอื่น',
            ['user' => $user['id'], 'role' => $user['role'], 'requested_classroom' => $input['classroom_id']]);
    }
    if (isset($input['student_id'])) {
        authorize(can_view_student($user, (int) $input['student_id']), 'ไม่มีสิทธิ์เข้าถึงข้อมูลของเด็กคนอื่น',
            ['user' => $user['id'], 'role' => $user['role'], 'requested_student' => $input['student_id']]);
    }

    // ROLE + SCOPE for every write.
    if (in_array($action, TEACHER_ACTIONS, true)) {
        authorize(can_manage_classroom($user, $classroomId), 'บัญชีนี้ดูข้อมูลได้อย่างเดียว',
            ['user' => $user['id'], 'role' => $user['role'], 'action' => $action]);
    }

    $message = '';
    switch ($action) {
        case 'state':
            $state = store_load();
            // Parent polling: nothing changed, so skip rendering.
            if ((int) ($input['version'] ?? 0) === (int) $state['version']) {
                json_response(['ok' => true, 'changed' => false, 'version' => $state['version']]);
            }
            break;

        case 'set_status':
            $state = store_set_status($classroomId, (string) ($input['code'] ?? ''), (string) ($input['note'] ?? ''), (int) $user['teacher_id'])['state'];
            $message = 'อัปเดตสถานะเรียบร้อยแล้ว';
            break;

        case 'add_activity':
            $state = store_add_activity($classroomId, (array) ($input['activity'] ?? []))['state'];
            $message = 'เพิ่มกิจกรรมเรียบร้อยแล้ว';
            break;

        case 'update_activity':
            $state = store_update_activity($classroomId, (int) ($input['id'] ?? 0), (array) ($input['activity'] ?? []))['state'];
            $message = 'แก้ไขกิจกรรมเรียบร้อยแล้ว';
            break;

        case 'delete_activity':
            $state = store_delete_activity($classroomId, (int) ($input['id'] ?? 0))['state'];
            $message = 'ลบกิจกรรมเรียบร้อยแล้ว';
            break;

        case 'save_meal':
            $state = store_save_meal($classroomId, (string) ($input['meal'] ?? ''), (array) ($input['items'] ?? []))['state'];
            $message = 'บันทึกเมนูอาหารเรียบร้อยแล้ว';
            break;

        case 'reset_demo':
            $state = store_reset_classroom($classroomId)['state'];
            $message = 'รีเซ็ตข้อมูล Demo ของห้อง' . $user['classroom']['name'] . 'เรียบร้อยแล้ว';
            break;

        default:
            json_response(['ok' => false, 'message' => 'ไม่สามารถดำเนินการได้ กรุณาลองใหม่อีกครั้ง'], 400);
    }

    // Re-render only the requested fragments, from scoped data.
    $ctx = page_context($user, $state);
    $fragments = [];
    foreach ((array) ($input['fragments'] ?? []) as $key) {
        if (is_string($key) && in_array($key, FRAGMENTS, true)) {
            $fragments[$key] = capture(fn () => render_fragment($key, $ctx));
        }
    }

    mk_log('Api', 'request END', ['action' => $action, 'classroom_id' => $classroomId, 'version' => $state['version'], 'fragments' => array_keys($fragments)]);
    json_response([
        'ok'        => true,
        'changed'   => true,
        'message'   => $message,
        'version'   => $state['version'],
        'fragments' => $fragments,
    ]);
} catch (PermissionDeniedException $ex) {
    mk_log('Api', 'permission ERROR', ['action' => $action, 'user' => $user['id'], 'role' => $user['role'], 'error' => $ex->getMessage(), 'input' => $input]);
    json_response(['ok' => false, 'message' => $ex->getMessage()], 403);
} catch (DemoValidationException $ex) {
    mk_log('Api', 'validation ERROR', ['action' => $action, 'error' => $ex->getMessage(), 'input' => $input]);
    json_response(['ok' => false, 'message' => $ex->getMessage()], 422);
} catch (Throwable $ex) {
    mk_log('Api', 'unexpected ERROR', ['action' => $action, 'error' => $ex->getMessage(), 'file' => $ex->getFile() . ':' . $ex->getLine(), 'input' => $input]);
    json_response(['ok' => false, 'message' => 'ระบบกำลังดำเนินการ กรุณาลองใหม่อีกครั้ง'], 500);
}
