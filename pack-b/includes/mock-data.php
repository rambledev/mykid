<?php
/**
 * Package B — mock data: one school (โรงเรียนอนุบาลโพนสูง, school_id = 1).
 * Classrooms, teachers, 60 students, activities and menus come from Package C's mock data;
 * Admin + Executive accounts are added on top of Package C's teacher / parent accounts.
 */
declare(strict_types=1);

function pkg_seed(): array
{
    $id = 1;
    $users = [
        ['id' => $id++, 'name' => 'คุณแอดมินโรงเรียน', 'phone' => '0800000001', 'pin' => '123456', 'role' => 'admin',
         'school_id' => 1, 'classroom_id' => null, 'student_id' => null, 'teacher_id' => null],
        ['id' => $id++, 'name' => 'ผู้อำนวยการโรงเรียน', 'phone' => '0800000002', 'pin' => '123456', 'role' => 'executive',
         'school_id' => 1, 'classroom_id' => null, 'student_id' => null, 'teacher_id' => null],
    ];
    array_push($users, ...seed_users_from_package_c($id));

    return seed_build_tables([seed_school_from_package_c()], $users);
}
