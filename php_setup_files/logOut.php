<?php
// php_setup_files/logOut.php
declare(strict_types=1);

ini_set('session.use_strict_mode', '1');
session_start();

// 1) Clear all session data
$_SESSION = [];

// 2) Kill the session cookie (if any)
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),        // name
        '',                    // value
        time() - 42000,        // expire in the past
        $params['path'] ?? '/', 
        $params['domain'] ?? '',
        (bool)($params['secure'] ?? false),
        (bool)($params['httponly'] ?? true)
    );
}

// 3) Destroy the session on the server
session_destroy();

// 4) Redirect to login (or home if you prefer)
header('Location: /php_setup_files/login.php?logged_out=1');
exit;
