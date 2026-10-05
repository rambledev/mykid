<?php
/**
 * Package C — raw data access (UNSCOPED).
 *
 * These functions return data regardless of who is logged in, like plain SQL queries.
 * Pages and the API must not call them to display people's data — use
 * includes/data-filter.php, which applies the current user's permission scope.
 * Replace the bodies with database queries later; callers stay unchanged.
 */
declare(strict_types=1);

function get_school(): array
{
    return mock_data()['school'];
}

function get_classrooms(): array
{
    return mock_data()['classrooms'];
}

function find_by_id(array $rows, int $id): ?array
{
    foreach ($rows as $row) {
        if ($row['id'] === $id) {
            return $row;
        }
    }
    return null;
}

function where_classroom(array $rows, int $classroomId): array
{
    return array_values(array_filter($rows, fn ($row) => $row['classroom_id'] === $classroomId));
}

function get_classroom(int $id): ?array
{
    return find_by_id(mock_data()['classrooms'], $id);
}

function get_teacher(int $id): ?array
{
    return find_by_id(mock_data()['teachers'], $id);
}

function get_teachers_by_classroom(int $classroomId): array
{
    return where_classroom(mock_data()['teachers'], $classroomId);
}

function get_student(int $id): ?array
{
    return find_by_id(mock_data()['students'], $id);
}

function get_students_by_classroom(int $classroomId): array
{
    return where_classroom(mock_data()['students'], $classroomId);
}

function get_statuses(): array
{
    return mock_data()['statuses'];
}

function get_status_def(string $code): ?array
{
    foreach (mock_data()['statuses'] as $status) {
        if ($status['code'] === $code) {
            return $status;
        }
    }
    return null;
}

function get_activity_icons(): array
{
    return mock_data()['activityIcons'];
}

function get_meal_types(): array
{
    return mock_data()['mealTypes'];
}

/** Aggregate numbers only (no personal data) — safe for the public landing page. */
function school_stats(): array
{
    $data = mock_data();
    return [
        'classrooms' => count($data['classrooms']),
        'students'   => count($data['students']),
        'teachers'   => count($data['teachers']),
    ];
}
