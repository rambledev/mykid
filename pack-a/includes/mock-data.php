<?php
/**
 * Package A — mock data: 5 schools of different sizes, each fully isolated by school_id.
 *   1 โรงเรียนอนุบาลโพนสูง      3 ห้อง · 60 นักเรียน (from Package C's mock data)
 *   2 โรงเรียนอนุบาลสายรุ้ง      3 ห้อง · 60 นักเรียน
 *   3 โรงเรียนอนุบาลดวงดาว       3 ห้อง · 57 นักเรียน
 *   4 โรงเรียนอนุบาลบ้านดอกไม้   2 ห้อง · 24 นักเรียน (ทดลองใช้)
 *   5 โรงเรียนอนุบาลลูกโป่ง       4 ห้อง · 60 นักเรียน
 */
declare(strict_types=1);

function pkg_seed(): array
{
    $schools = [
        seed_school_from_package_c(),
        seed_generated_school(2, 'โรงเรียนอนุบาลสายรุ้ง', 'อนุบาลสายรุ้ง', '🌈',
            [['อนุบาล 1', '🐼', '#DDF7E6', 20, 2], ['อนุบาล 2', '🐨', '#F1EAFF', 20, 2], ['อนุบาล 3', '🦊', '#FFEAF2', 20, 1]],
            [['ครูแพรว', 'นางสาวแพรวา ใจสะอาด'], ['ครูดารา', 'นางดารา พูนสุข'], ['ครูปิยะ', 'นางสาวปิยะดา ศรีทอง'],
             ['ครูจันทร์', 'นางจันทร์เพ็ญ วงศ์ดี'], ['ครูนภา', 'นางสาวนภา รัตนวงศ์']],
            ['classroom' => 4, 'student' => 61, 'teacher' => 6, 'activity' => 201], 0),
        seed_generated_school(3, 'โรงเรียนอนุบาลดวงดาว', 'อนุบาลดวงดาว', '⭐',
            [['อนุบาล 1', '🐧', '#DFF4FF', 19, 2], ['อนุบาล 2', '🐬', '#FFF4CC', 19, 1], ['อนุบาล 3', '🦄', '#F1EAFF', 19, 2]],
            [['ครูกานต์', 'นางสาวกานต์ธิดา ศรีสุข'], ['ครูพิม', 'นางพิมพ์ใจ แสงทอง'], ['ครูสายใจ', 'นางสายใจ บุญมี'],
             ['ครูอร', 'นางสาวอรอุมา จันทร์ดี'], ['ครูมุก', 'นางสาวมุกดา คำแก้ว']],
            ['classroom' => 7, 'student' => 121, 'teacher' => 11, 'activity' => 301], 30),
        seed_generated_school(4, 'โรงเรียนอนุบาลบ้านดอกไม้', 'อนุบาลบ้านดอกไม้', '🌻',
            [['อนุบาล 1', '🐝', '#FFF4CC', 12, 1], ['อนุบาล 2', '🦋', '#DDF7E6', 12, 1]],
            [['ครูดอกรัก', 'นางสาวดอกรัก ทองใบ'], ['ครูเพ็ญ', 'นางเพ็ญศรี ใจกว้าง']],
            ['classroom' => 10, 'student' => 178, 'teacher' => 16, 'activity' => 401], 60, 'trial'),
        seed_generated_school(5, 'โรงเรียนอนุบาลลูกโป่ง', 'อนุบาลลูกโป่ง', '🎈',
            [['เตรียมอนุบาล', '🐤', '#FFEAF2', 15, 1], ['อนุบาล 1', '🐻', '#DFF4FF', 15, 2], ['อนุบาล 2', '🐳', '#F1EAFF', 15, 1], ['อนุบาล 3', '🦒', '#FFF4CC', 15, 2]],
            [['ครูอ้อม', 'นางอัมพร สุขสม'], ['ครูนก', 'นางสาวนกน้อย แก้วกล้า'], ['ครูจอย', 'นางสาวจอยรักษ์ มีสุข'],
             ['ครูแอน', 'นางแอนนา ศรีบุญ'], ['ครูหญิง', 'นางสาวหญิงไทย ดวงแก้ว'], ['ครูต่าย', 'นางต่ายทอง บุญเพ็ง']],
            ['classroom' => 12, 'student' => 202, 'teacher' => 18, 'activity' => 501], 72),
    ];

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
        // School 1 — โพนสูง
        $staff('คุณแอดมินโพนสูง', '0900000002', 'admin', 1),
        $staff('ผู้อำนวยการโพนสูง', '0900000003', 'executive', 1),
    ];
    $id = 10;
    array_push($users, ...seed_users_from_package_c($id));

    // School 2 — สายรุ้ง
    $id = 30;
    $users[] = $staff('คุณแอดมินสายรุ้ง', '0920000002', 'admin', 2);
    $users[] = $staff('ผู้อำนวยการสายรุ้ง', '0920000003', 'executive', 2);
    $users[] = $teacher($schools[1], 6, '0920000011');
    $users[] = $teacher($schools[1], 8, '0920000012');
    $users[] = $teacher($schools[1], 10, '0920000013');
    $users[] = $parent($schools[1], 61, '0920000021');
    $users[] = $parent($schools[1], 81, '0920000022');

    // School 3 — ดวงดาว
    $id = 50;
    $users[] = $staff('คุณแอดมินดวงดาว', '0930000002', 'admin', 3);
    $users[] = $staff('ผู้อำนวยการดวงดาว', '0930000003', 'executive', 3);
    $users[] = $teacher($schools[2], 11, '0930000011');
    $users[] = $teacher($schools[2], 13, '0930000012');
    $users[] = $teacher($schools[2], 14, '0930000013');
    $users[] = $parent($schools[2], 121, '0930000021');
    $users[] = $parent($schools[2], 140, '0930000022');

    // School 4 — บ้านดอกไม้ (trial)
    $id = 70;
    $users[] = $staff('คุณแอดมินบ้านดอกไม้', '0940000002', 'admin', 4);
    $users[] = $staff('ผู้อำนวยการบ้านดอกไม้', '0940000003', 'executive', 4);
    $users[] = $teacher($schools[3], 16, '0940000011');
    $users[] = $teacher($schools[3], 17, '0940000012');
    $users[] = $parent($schools[3], 178, '0940000021');
    $users[] = $parent($schools[3], 190, '0940000022');

    // School 5 — ลูกโป่ง
    $id = 90;
    $users[] = $staff('คุณแอดมินลูกโป่ง', '0950000002', 'admin', 5);
    $users[] = $staff('ผู้อำนวยการลูกโป่ง', '0950000003', 'executive', 5);
    $users[] = $teacher($schools[4], 18, '0950000011');
    $users[] = $teacher($schools[4], 19, '0950000012');
    $users[] = $teacher($schools[4], 21, '0950000013');
    $users[] = $teacher($schools[4], 22, '0950000014');
    $users[] = $parent($schools[4], 202, '0950000021');
    $users[] = $parent($schools[4], 217, '0950000022');

    return seed_build_tables($schools, $users, ['15:30', '🏠', 'เตรียมกลับบ้าน', 'เก็บของใช้และรอผู้ปกครองมารับ']);
}
