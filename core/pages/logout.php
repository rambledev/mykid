<?php
/** Logout: destroy the session and return to the login page. */
declare(strict_types=1);

$previousRole = current_user()['role'] ?? null;
auth_logout(); // session_destroy()
redirect('login.php?logout=1' . ($previousRole ? '&role=' . $previousRole : ''));
