<?php
/**
 * Package C — demo authentication & authorisation.
 *
 *   USER → ROLE → SCOPE → DATA
 *   Teacher → classroom_id
 *   Parent  → student_id → the child's classroom_id
 *
 * The scope always comes from $_SESSION['user'] — never from the URL or request body.
 * Not a real auth system: no PIN hashing or rate limiting because this is a sales demo.
 */
declare(strict_types=1);

const ROLES = ['teacher', 'parent'];

/** Query-string keys someone might try in order to jump to another scope. */
const SCOPE_QUERY_KEYS = ['classroom_id', 'classroom', 'room', 'student_id', 'student', 'child', 'teacher_id', 'parent_id'];

function normalize_phone(string $phone): string
{
    return preg_replace('/\D+/', '', $phone) ?? '';
}

function demo_accounts(string $role): array
{
    return mock_data()[$role === 'teacher' ? 'teacherAccounts' : 'parentAccounts'];
}

/* ----------------------------------------------------------------------------
 * Login / logout
 * ------------------------------------------------------------------------- */

function auth_login(string $role, string $phone, string $pin): bool
{
    mk_log('Auth', 'auth_login START', ['role' => $role, 'phone' => $phone]);
    if (!in_array($role, ROLES, true)) {
        return false;
    }

    $phone = normalize_phone($phone);
    foreach (demo_accounts($role) as $account) {
        if ($account['phone'] !== $phone || !hash_equals($account['pin'], $pin)) {
            continue;
        }

        $child = $role === 'parent' ? get_student($account['student_id']) : null;
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'id'           => $account['id'],
            'name'         => $account['name'],
            'role'         => $role,
            'classroom_id' => $role === 'teacher' ? $account['classroom_id'] : $child['classroom_id'],
            'student_id'   => $role === 'parent' ? $account['student_id'] : null,
        ];
        mk_log('Auth', 'auth_login END success', $_SESSION['user']);
        return true;
    }

    mk_log('Auth', 'auth_login END failed', ['role' => $role, 'phone' => $phone]);
    return false;
}

function auth_logout(): void
{
    mk_log('Auth', 'auth_logout', ['user' => $_SESSION['user'] ?? null]);
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $p = session_get_cookie_params();
        setcookie(session_name(), '', time() - 3600, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
    }
    session_destroy();
}

/* ----------------------------------------------------------------------------
 * Current user
 * ------------------------------------------------------------------------- */

/**
 * The logged-in user: the session record re-validated against the account list (as a real
 * system would re-check the database) and enriched with display data. Null when logged out.
 */
function current_user(): ?array
{
    static $cache = false;
    if ($cache !== false) {
        return $cache;
    }
    $cache = null;

    $session = $_SESSION['user'] ?? null;
    if (!is_array($session) || !in_array($session['role'] ?? '', ROLES, true)) {
        return null;
    }

    $account = find_by_id(demo_accounts($session['role']), (int) $session['id']);
    if (!$account) {
        mk_log('Auth', 'current_user account no longer exists', $session);
        return null;
    }

    if ($session['role'] === 'teacher') {
        $teacher = get_teacher($account['teacher_id']);
        $classroom = get_classroom($account['classroom_id']);
        $cache = $session + [
            'teacher_id' => $account['teacher_id'],
            'fullName'   => $teacher['fullName'] ?? $account['name'],
            'phone'      => $account['phone'],
            'emoji'      => $teacher['emoji'] ?? '👩‍🏫',
            'classroom'  => $classroom,
            'child'      => null,
            'scopeLabel' => $classroom['name'],
            'lockLabel'  => 'เฉพาะห้องนี้',
        ];
        // Scope is always re-derived from the account, never trusted from elsewhere.
        $cache['classroom_id'] = $account['classroom_id'];
    } else {
        $child = get_student($account['student_id']);
        $classroom = get_classroom($child['classroom_id']);
        $cache = $session + [
            'relation'   => $account['relation'],
            'phone'      => $account['phone'],
            'emoji'      => $account['relation'] === 'พ่อ' ? '👨' : '👩',
            'classroom'  => $classroom,
            'child'      => $child,
            'scopeLabel' => $child['nickname'] . ' · ' . $classroom['name'],
            'lockLabel'  => 'เฉพาะ' . $child['nickname'],
        ];
        $cache['student_id'] = $account['student_id'];
        $cache['classroom_id'] = $child['classroom_id'];
    }
    return $cache;
}

/* ----------------------------------------------------------------------------
 * Page guards
 * ------------------------------------------------------------------------- */

function require_login(): array
{
    $user = current_user();
    if (!$user) {
        flash('login_error', 'กรุณาเข้าสู่ระบบก่อนใช้งาน');
        redirect('login.php');
    }
    return $user;
}

/** Role check + scope check for a page. Returns the current user. */
function require_role(string $role): array
{
    $user = current_user();
    if (!$user) {
        flash('login_error', 'กรุณาเข้าสู่ระบบก่อนใช้งาน');
        redirect('login.php?role=' . $role);
    }
    if ($user['role'] !== $role) {
        mk_log('Auth', 'PERMISSION DENIED wrong role', ['need' => $role, 'user' => $user['role'], 'uri' => $_SERVER['REQUEST_URI'] ?? '']);
        flash('toast', ['message' => 'หน้านี้สำหรับ' . ($role === 'teacher' ? 'คุณครู' : 'ผู้ปกครอง') . 'เท่านั้น', 'type' => 'lock']);
        redirect(dashboard_path($user['role']));
    }
    guard_scope_query($user);
    return $user;
}

function dashboard_path(string $role): string
{
    return $role . '/index.php';
}

/**
 * Direct-URL protection: scope never comes from the query string. If someone adds
 * ?classroom_id=2 or ?student_id=25, log it, tell them politely, and reload the clean URL.
 */
function guard_scope_query(array $user): void
{
    $tampered = array_intersect_key($_GET, array_flip(SCOPE_QUERY_KEYS));
    if (!$tampered) {
        return;
    }
    mk_log('Auth', 'SCOPE QUERY IGNORED', ['user' => $user['id'], 'role' => $user['role'], 'scope' => scope_classroom_id($user), 'query' => $tampered]);
    flash('toast', ['message' => 'คุณดูได้เฉพาะข้อมูล' . $user['scopeLabel'] . ' ตามสิทธิ์ของบัญชีนี้', 'type' => 'lock']);
    header('Location: ' . strtok($_SERVER['REQUEST_URI'], '?'));
    exit;
}

/* ----------------------------------------------------------------------------
 * Permission checks (ROLE + SCOPE)
 * ------------------------------------------------------------------------- */

/** The only classroom this user may access — derived from the session account. */
function scope_classroom_id(array $user): int
{
    return (int) $user['classroom_id'];
}

function can_view_classroom(array $user, int $classroomId): bool
{
    return in_array($user['role'], ROLES, true) && scope_classroom_id($user) === $classroomId;
}

function can_manage_classroom(array $user, int $classroomId): bool
{
    return $user['role'] === 'teacher' && scope_classroom_id($user) === $classroomId;
}

function can_view_student(array $user, int $studentId): bool
{
    $student = get_student($studentId);
    if (!$student) {
        return false;
    }
    return match ($user['role']) {
        'teacher' => $student['classroom_id'] === scope_classroom_id($user),
        'parent'  => $studentId === (int) $user['student_id'],
        default   => false,
    };
}

/** Throw when a permission check fails (used by the API). */
function authorize(bool $allowed, string $message, array $context = []): void
{
    if (!$allowed) {
        mk_log('Auth', 'PERMISSION DENIED', $context);
        throw new PermissionDeniedException($message);
    }
}
