<?php
/** Package C — logout: destroy the session, then suggest the other role so the demo flow continues. */
declare(strict_types=1);

require __DIR__ . '/includes/bootstrap.php';

$previousRole = current_user()['role'] ?? 'teacher';
auth_logout(); // session_destroy()

$nextRole = $previousRole === 'teacher' ? 'parent' : 'teacher';
redirect('login.php?logout=1&role=' . $nextRole);
