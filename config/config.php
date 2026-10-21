<?php
/* Unsent · config: switches, headers, session, CSRF token, output filter. */
defined('ROOT') || exit;
/* ---- switches ----
   DEMO: true keeps the demo admin button + the demo recovery code (72550). Set false before going live. */
const DEMO = true;
const TZ = 'Asia/Jakarta'; /* mood days + "today" follow this zone */
date_default_timezone_set(TZ);
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: SAMEORIGIN');
header('Referrer-Policy: same-origin');
session_set_cookie_params(['httponly' => true, 'samesite' => 'Lax']);
session_start();
$_SESSION['csrf'] ??= bin2hex(random_bytes(16));
$GLOBALS['CSRF'] = $_SESSION['csrf'];
/* every POST form gets the session token automatically */
function csrfinj($h)
{
    $t = '<input type=hidden name=t value="' . ($GLOBALS['CSRF'] ?? '') . '">';
    return preg_replace('~<form\b[^>]*\bmethod=["\']?post\b[^>]*>~i', '$0' . $t, $h);
}
ob_start(fn($h) => csrfinj(preg_replace_callback('~img/(\w+)\.png~', function ($m) {
    $f = is_file(ROOT . "/img/{$m[1]}.svg") ? "img/{$m[1]}.svg" : $m[0];
    return $f . '?v=' . @filemtime(ROOT . '/' . $f);
}, $h)));
