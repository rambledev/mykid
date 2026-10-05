<?php
/**
 * Package B — Mykid Standard. Single school, 4 roles (Admin, Executive, Teacher, Parent).
 * Marketing info comes from the shared catalogue (includes/packages.php).
 */
declare(strict_types=1);

$catalogue = require dirname(__DIR__, 2) . '/includes/packages.php';

return $catalogue['b'] + [
    'session'     => 'MYKIDB',
    'tier'        => 'standard',
    'tierLabel'   => 'Standard',
    'multiSchool' => false,
    'meals'       => ['breakfast', 'lunch', 'snack', 'milk'],
    'modules'     => ['students', 'teachers', 'classrooms', 'activities', 'food', 'status', 'reports', 'attendanceReport', 'cctv'],

    // Navigation per role: [key, label, page, icon, show in mobile bottom nav]
    'nav' => [
        'admin' => [
            ['home', 'หน้าหลัก', 'admin/index.php', 'home', true],
            ['students', 'นักเรียน', 'admin/students.php', 'child', true],
            ['teachers', 'ครู', 'admin/teachers.php', 'users', false],
            ['classrooms', 'ห้องเรียน', 'admin/classrooms.php', 'school', false],
            ['activities', 'กิจกรรม', 'admin/activities.php', 'list', true],
            ['food', 'อาหาร', 'admin/food.php', 'food', true],
            ['cctv', 'กล้องวงจรปิด', 'admin/cctv.php', 'camera', false],
            ['account', 'บัญชี', 'admin/account.php', 'user', false],
        ],
        'executive' => [
            ['home', 'ภาพรวม', 'executive/index.php', 'home', true],
            ['dashboard', 'แดชบอร์ด', 'executive/dashboard.php', 'chart', true],
            ['reports', 'รายงาน', 'executive/reports.php', 'report', true],
            ['cctv', 'กล้องวงจรปิด', 'executive/cctv.php', 'camera', true],
            ['account', 'บัญชี', 'executive/account.php', 'user', true],
        ],
        'teacher' => [
            ['home', 'หน้าหลัก', 'teacher/index.php', 'home', true],
            ['activities', 'กิจกรรม', 'teacher/activities.php', 'list', true],
            ['food', 'อาหาร', 'teacher/food.php', 'food', false],
            ['status', 'สถานะ', 'teacher/status.php', 'status', true],
            ['cctv', 'กล้อง', 'teacher/cctv.php', 'camera', true],
            ['account', 'บัญชี', 'teacher/account.php', 'user', false],
        ],
        'parent' => [
            ['home', 'หน้าหลัก', 'parent/index.php', 'home', true],
            ['today', 'วันนี้', 'parent/activities.php', 'list', true],
            ['food', 'อาหาร', 'parent/food.php', 'food', true],
            ['cctv', 'กล้อง', 'parent/cctv.php', 'camera', true],
            ['child', 'ลูกของฉัน', 'parent/child.php', 'child', false],
            ['account', 'บัญชี', 'parent/account.php', 'user', false],
        ],
    ],
];
