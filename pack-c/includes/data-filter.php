<?php
/**
 * Package C — scoped data access (USER → ROLE → SCOPE → DATA).
 *
 * Every function takes the current user (from current_user(), i.e. $_SESSION['user']),
 * checks the role, and filters by that user's scope:
 *   Teacher → classroom_id of the account
 *   Parent  → student_id of the account → that child's classroom_id
 * Nothing here accepts a classroom or student id from the request.
 */
declare(strict_types=1);

function assert_role(array $user, string $role): void
{
    if ($user['role'] !== $role) {
        mk_log('DataFilter', 'PERMISSION DENIED wrong role', ['need' => $role, 'user' => $user['role']]);
        throw new PermissionDeniedException('ไม่มีสิทธิ์เข้าถึงข้อมูลนี้');
    }
}

/* ---------------------------------------------------------------------------
 * Teacher — scope: own classroom
 * ------------------------------------------------------------------------ */

function get_teacher_classroom(array $user): array
{
    assert_role($user, 'teacher');
    return get_classroom(scope_classroom_id($user));
}

function get_teacher_students(array $user): array
{
    assert_role($user, 'teacher');
    $classroomId = scope_classroom_id($user);
    $students = array_values(array_filter(
        mock_data()['students'],
        fn ($student) => $student['classroom_id'] === $classroomId
    ));
    mk_log('DataFilter', 'get_teacher_students', ['teacher' => $user['id'], 'classroom_id' => $classroomId, 'count' => count($students)]);
    return $students;
}

function get_teacher_colleagues(array $user): array
{
    assert_role($user, 'teacher');
    return get_teachers_by_classroom(scope_classroom_id($user));
}

function get_teacher_activities(array $user, array $state): array
{
    assert_role($user, 'teacher');
    return classroom_activities($state, scope_classroom_id($user));
}

function get_teacher_food(array $user, array $state): array
{
    assert_role($user, 'teacher');
    return classroom_food($state, scope_classroom_id($user));
}

function get_teacher_status(array $user, array $state): ?array
{
    assert_role($user, 'teacher');
    return classroom_current_status($state, scope_classroom_id($user));
}

function get_teacher_status_history(array $user, array $state): array
{
    assert_role($user, 'teacher');
    return classroom_status_history($state, scope_classroom_id($user));
}

/* ---------------------------------------------------------------------------
 * Parent — scope: own child → child's classroom
 * ------------------------------------------------------------------------ */

function get_parent_student(array $user): array
{
    assert_role($user, 'parent');
    $student = get_student((int) $user['student_id']);
    mk_log('DataFilter', 'get_parent_student', ['parent' => $user['id'], 'student_id' => $user['student_id']]);
    return $student;
}

/** The child's classroom id — looked up from the student record, the source of truth. */
function parent_classroom_id(array $user): int
{
    return get_parent_student($user)['classroom_id'];
}

function get_parent_classroom(array $user): array
{
    return get_classroom(parent_classroom_id($user));
}

/** Teachers of the child's room (names only — no other children or parents). */
function get_parent_teachers(array $user): array
{
    return get_teachers_by_classroom(parent_classroom_id($user));
}

function get_parent_activities(array $user, array $state): array
{
    return classroom_activities($state, parent_classroom_id($user));
}

function get_parent_food(array $user, array $state): array
{
    return classroom_food($state, parent_classroom_id($user));
}

function get_parent_status(array $user, array $state): ?array
{
    return classroom_current_status($state, parent_classroom_id($user));
}

function get_parent_status_history(array $user, array $state): array
{
    return classroom_status_history($state, parent_classroom_id($user));
}

/* ---------------------------------------------------------------------------
 * Page context — everything a page/fragment may render, already scoped.
 * Fragment renderers only read from this array, never from the raw state.
 * ------------------------------------------------------------------------ */

function page_context(array $user, ?array $state = null): array
{
    $state ??= store_load();
    $isTeacher = $user['role'] === 'teacher';

    return [
        'user'       => $user,
        'role'       => $user['role'],
        'classroom'  => $isTeacher ? get_teacher_classroom($user) : get_parent_classroom($user),
        'child'      => $isTeacher ? null : get_parent_student($user),
        'status'     => $isTeacher ? get_teacher_status($user, $state) : get_parent_status($user, $state),
        'history'    => $isTeacher ? get_teacher_status_history($user, $state) : get_parent_status_history($user, $state),
        'activities' => $isTeacher ? get_teacher_activities($user, $state) : get_parent_activities($user, $state),
        'food'       => $isTeacher ? get_teacher_food($user, $state) : get_parent_food($user, $state),
        'version'    => $state['version'],
    ];
}
