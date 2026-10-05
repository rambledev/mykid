<?php
/**
 * Package A — CCTV mock data: TP-Link Tapo C200C, one per classroom + playground + entrance
 * per school (5 schools → 25 cameras).
 * Every camera row carries school_id, so schools never see each other's cameras.
 * All cameras start as stream_type "mock" (see docs/cctv-architecture.md to go live).
 */
declare(strict_types=1);

function pkg_cctv_cameras(): array
{
    return [
        1 => cctv_school_cameras(['CAM-03' => 'offline']),     // โรงเรียนอนุบาลโพนสูง
        2 => cctv_school_cameras([]),                          // โรงเรียนอนุบาลสายรุ้ง
        3 => cctv_school_cameras(['CAM-05' => 'maintenance']), // โรงเรียนอนุบาลดวงดาว
        4 => cctv_school_cameras(['CAM-04' => 'offline'], 2),  // โรงเรียนอนุบาลบ้านดอกไม้ (2 ห้อง)
        5 => cctv_school_cameras([], 4),                       // โรงเรียนอนุบาลลูกโป่ง (4 ห้อง)
    ];
}
