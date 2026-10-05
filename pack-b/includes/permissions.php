<?php
/**
 * Package B — permission matrix: ROLE → SCOPE → tables it may READ / WRITE.
 *
 *   scope 'school'    → every row of the user's school (school_id from the session)
 *   scope 'classroom' → the teacher's classroom (classroom_id from the session)
 *   scope 'student'   → the parent's child (student_id from the session)
 *
 * The engine (core/permissions.php) applies these rules to every read and write.
 */
declare(strict_types=1);

return [
    'admin' => [
        'scope' => 'school',
        'read'  => '*',
        'write' => ['students', 'teachers', 'classrooms', 'activities', 'foodMenus', 'cameras'],
    ],
    'executive' => [
        'scope' => 'school',
        'read'  => '*',
        'write' => [], // read only
    ],
    // Teacher / Parent read cameras ONLY through can_view_camera() (core/cctv.php), never the generic reader.
    'teacher' => [
        'scope' => 'classroom',
        'read'  => ['schools', 'classrooms', 'teachers', 'students', 'activities', 'foodMenus', 'statuses'],
        'write' => ['activities', 'foodMenus', 'statuses'],
    ],
    'parent' => [
        'scope' => 'student',
        'read'  => ['schools', 'classrooms', 'teachers', 'students', 'activities', 'foodMenus', 'statuses'],
        'write' => [],
    ],
];
