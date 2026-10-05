<?php
/**
 * Package catalogue — single source of truth for package marketing info.
 * Used by the main index, the Package A/B landing pages (core/pages/landing.php)
 * and the Package C landing.
 */
declare(strict_types=1);

return [
    'a' => [
        'key'          => 'a',
        'code'         => 'Package A',
        'name'         => 'Mykid Full',
        'tagline'      => 'ระบบครบวงจรสำหรับโรงเรียนหลายแห่ง',
        'landingTitle' => 'ระบบบริหารโรงเรียนและดูแลเด็กแบบครบวงจร',
        'landingSub'   => 'รองรับหลายโรงเรียนในระบบเดียว ข้อมูลแต่ละโรงเรียนแยกขาดจากกัน',
        'emoji'        => '🏰',
        'status'       => 'ready',
        'statusLabel'  => 'พร้อมทดลอง',
        'price'        => null,
        'priceNote'    => 'สอบถามราคา',
        'priceUnit'    => 'ตามจำนวนโรงเรียน',
        'includes'     => ['Multi-School', 'Host + SSL', 'CCTV Ready', 'ดูแลระบบ'],
        'cta'          => 'ทดลอง Demo',
        'href'         => 'pack-a/index.php',
        'roles'        => ['super_admin', 'admin', 'executive', 'teacher', 'parent'],
        'features'     => [
            ['🏫', 'Multi-School'], ['🛡️', 'Admin'], ['💼', 'Executive'], ['👩‍🏫', 'Teacher'],
            ['👨‍👩‍👧', 'Parent'], ['🩺', 'Health'], ['🖼️', 'Portfolio'], ['⭐', 'Star'],
            ['🌱', 'Development'], ['💬', 'Chat'], ['🔔', 'Notification'], ['📅', 'Calendar'],
            ['📹', 'CCTV'], ['📊', 'Reports'],
        ],
    ],
    'b' => [
        'key'          => 'b',
        'code'         => 'Package B',
        'name'         => 'Mykid Standard',
        'tagline'      => 'ระบบสำหรับโรงเรียนที่มี Admin และผู้บริหาร',
        'landingTitle' => 'ระบบสำหรับโรงเรียนที่มี Admin และผู้บริหาร',
        'landingSub'   => 'จัดการข้อมูลทั้งโรงเรียนได้ที่เดียว ผู้บริหารดูรายงานได้ทันที',
        'emoji'        => '🏫',
        'status'       => 'ready',
        'statusLabel'  => 'พร้อมทดลอง',
        'price'        => '15,000',
        'priceUnit'    => 'บาท',
        'includes'     => ['4 บทบาท', 'Reports', 'Host + SSL', 'ดูแลระบบ'],
        'cta'          => 'ทดลอง Demo',
        'href'         => 'pack-b/index.php',
        'roles'        => ['admin', 'executive', 'teacher', 'parent'],
        'features'     => [
            ['🛡️', 'Admin'], ['💼', 'Executive'], ['👩‍🏫', 'Teacher'], ['👨‍👩‍👧', 'Parent'],
            ['🧒', 'Student Management'], ['📋', 'Activities'], ['🍱', 'Food'], ['🚦', 'Status'],
            ['📊', 'Reports'], ['📹', 'CCTV'],
        ],
    ],
    'c' => [
        'key'          => 'c',
        'code'         => 'Package C',
        'name'         => 'Mykid Basic',
        'tagline'      => 'ระบบพื้นฐานสำหรับครูและผู้ปกครอง',
        'emoji'        => '🌈',
        'status'       => 'ready',
        'statusLabel'  => 'พร้อมทดลอง',
        'price'        => '10,000',
        'priceUnit'    => 'บาท / ปี',
        'includes'     => ['Host', 'Domain', 'SSL', 'ดูแลระบบ 1 ปี'],
        'cta'          => 'ทดลอง Demo',
        'href'         => 'pack-c/index.php',
        'featured'     => true,
        'roles'        => ['teacher', 'parent'],
        'features'     => [
            ['🚦', 'ครูอัปเดตสถานะห้องเรียนได้ในคลิกเดียว'],
            ['📋', 'จัดการกิจกรรมประจำวัน'],
            ['🍱', 'แจ้งเมนูอาหารประจำวัน'],
            ['💗', 'ผู้ปกครองติดตามลูกได้ทุกที่ทุกเวลา'],
        ],
    ],
];
