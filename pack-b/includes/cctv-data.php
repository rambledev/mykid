<?php
/**
 * Package B — CCTV mock data: โรงเรียนอนุบาลโพนสูง (school_id 1), 5 × TP-Link Tapo C200C.
 *
 * Every camera starts as stream_type "mock" with an empty stream_url, so the demo works
 * with no media server. To go live, an admin sets stream_type = hls / webrtc and the
 * media-server URL in admin/cctv.php — no UI change needed (see docs/cctv-architecture.md).
 * Camera layout (CAM-01..05, classroom vs common area) comes from cctv_school_cameras().
 */
declare(strict_types=1);

function pkg_cctv_cameras(): array
{
    return [
        1 => cctv_school_cameras(['CAM-03' => 'offline']), // CAM-03 offline in the demo
    ];
}
