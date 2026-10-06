<?php
/**
 * Package A (Demo Final scope) — mock data: 2 schools, each fully isolated by school_id.
 *   1 ศูนย์พัฒนาเด็กเล็กบ้านโพนสูง 1   3 ห้อง · 60 นักเรียน · 5 ครู (from Package C's mock data, renamed here)
 *   2 ศูนย์พัฒนาเด็กเล็กบ้านโพนสูง 2   3 ห้อง · 60 นักเรียน · 5 ครู
 */
declare(strict_types=1);

function pkg_seed(): array
{
    $schools = [
        seed_school_from_package_c(),
        seed_generated_school(2, 'ศูนย์พัฒนาเด็กเล็กบ้านโพนสูง 2', 'ศพด.บ้านโพนสูง 2', '🌈',
            [['อนุบาล 1', '🐼', '#DDF7E6', 20, 2], ['อนุบาล 2', '🐨', '#F1EAFF', 20, 2], ['อนุบาล 3', '🦊', '#FFEAF2', 20, 1]],
            [['ครูแพรว', 'นางสาวแพรวา ใจสะอาด'], ['ครูดารา', 'นางดารา พูนสุข'], ['ครูปิยะ', 'นางสาวปิยะดา ศรีทอง'],
             ['ครูจันทร์', 'นางจันทร์เพ็ญ วงศ์ดี'], ['ครูนภา', 'นางสาวนภา รัตนวงศ์']],
            ['classroom' => 4, 'student' => 61, 'teacher' => 6, 'activity' => 201], 0),
    ];

    // Package A's school 1 has its own name (Package C keeps its original one).
    $schools[0]['school'] = ['name' => 'ศูนย์พัฒนาเด็กเล็กบ้านโพนสูง 1', 'shortName' => 'ศพด.บ้านโพนสูง 1'] + $schools[0]['school'];

    $id = 1;
    $staff = function (string $name, string $phone, string $role, ?int $school) use (&$id) {
        return ['id' => $id++, 'name' => $name, 'phone' => $phone, 'pin' => '123456', 'role' => $role,
            'school_id' => $school, 'classroom_id' => null, 'student_id' => null, 'teacher_id' => null];
    };
    $teacher = function (array $schoolData, int $teacherId, string $phone) use (&$id) {
        foreach ($schoolData['teachers'] as $t) {
            if ($t['id'] === $teacherId) {
                return ['id' => $id++, 'name' => $t['nickname'], 'phone' => $phone, 'pin' => '123456', 'role' => 'teacher',
                    'school_id' => $t['school_id'], 'classroom_id' => $t['classroom_id'], 'student_id' => null, 'teacher_id' => $t['id']];
            }
        }
        return null;
    };
    $parent = function (array $schoolData, int $studentId, string $phone) use (&$id) {
        foreach ($schoolData['students'] as $s) {
            if ($s['id'] === $studentId) {
                return ['id' => $id++, 'name' => 'คุณ' . $s['parentRelation'] . 'ของ' . $s['nickname'], 'phone' => $phone,
                    'pin' => '123456', 'role' => 'parent', 'school_id' => $s['school_id'], 'classroom_id' => $s['classroom_id'],
                    'student_id' => $s['id'], 'teacher_id' => null, 'relation' => $s['parentRelation']];
            }
        }
        return null;
    };

    $users = [
        $staff('ผู้ดูแลระบบ Mykid', '0900000001', 'super_admin', null),
        // School 1 — บ้านโพนสูง 1
        $staff('คุณแอดมินโพนสูง 1', '0900000002', 'admin', 1),
        $staff('ผู้อำนวยการโพนสูง 1', '0900000003', 'executive', 1),
    ];
    $id = 10;
    array_push($users, ...seed_users_from_package_c($id)); // 3 teachers + 6 parents (Package C accounts)

    // School 2 — บ้านโพนสูง 2
    $id = 30;
    $users[] = $staff('คุณแอดมินโพนสูง 2', '0920000002', 'admin', 2);
    $users[] = $staff('ผู้อำนวยการโพนสูง 2', '0920000003', 'executive', 2);
    $users[] = $teacher($schools[1], 6, '0920000011');
    $users[] = $teacher($schools[1], 8, '0920000012');
    $users[] = $teacher($schools[1], 10, '0920000013');
    $users[] = $parent($schools[1], 61, '0920000021');
    $users[] = $parent($schools[1], 81, '0920000022');
    $users[] = $parent($schools[1], 101, '0920000023');
    $users[] = $parent($schools[1], 62, '0920000024');

    return seed_build_tables($schools, $users, ['15:30', '🏠', 'เตรียมกลับบ้าน', 'เก็บของใช้และรอผู้ปกครองมารับ']);
}
