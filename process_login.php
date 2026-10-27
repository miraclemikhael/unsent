<?php
defined('ROOT') || exit;
/**
 * @var mixed $p
 * @var mixed $d
 * @var mixed $err
 * @var mixed $a
 */
if ($a == 'login') {
    $lid = strtolower(trim($_POST['id'] ?? ''));
    $lpw = (string)($_POST['pw'] ?? '');
    if (($_SESSION['lock'] ?? 0) > time()) $err = 'Too many attempts. Please wait a minute and try again.';
    else foreach ($d['users'] as $i => $u) if ($lid !== '' && (strtolower($u['email']) == $lid || strtolower($u['username']) == $lid) && password_verify($lpw, $u['hash'])) {
        if (($u['status'] ?? '') === 'suspended') {
            $err = 'This account is suspended. Please contact support.';
            break;
        }
        session_regenerate_id(true);
        unset($_SESSION['fails'], $_SESSION['lock']);
        $_SESSION['u'] = $u['email'];
        $d['users'][$i]['last_login'] = date('Y-m-d H:i:s');
        save_data($d);
        if (($u['role'] ?? '') === 'admin') {
            $_SESSION['flash'] = 'Signed in as admin';
            redirect_to_page('admin');
        }
        redirect_to_page('home');
    }
    if (!$err) {
        usleep(250000);
        $_SESSION['fails'] = ($_SESSION['fails'] ?? 0) + 1;
        if ($_SESSION['fails'] >= 6) {
            $_SESSION['lock'] = time() + 60;
            $_SESSION['fails'] = 0;
            $err = 'Too many attempts. Please wait a minute and try again.';
        }
    }
    $err = $err ?: 'Wrong details, try again.';
    $p = 'signin';
}
if ($a == 'register') {
    $name = mb_substr(trim($_POST['name'] ?? ''), 0, 60);
    $em = strtolower(trim($_POST['id'] ?? ''));
    $pw = $_POST['pw'] ?? '';
    $dup = false;
    foreach ($d['users'] as $u) if (strtolower($u['email']) == strtolower($em)) $dup = true;
    if (($d['settings']['signups'] ?? 1) == 0) {
        $err = 'New sign-ups are paused right now.';
        $p = 'signin';
    } elseif ($name === '' || !filter_var($em, FILTER_VALIDATE_EMAIL)) {
        $err = 'Please enter your name and a valid email.';
        $p = 'signin';
    } elseif (!password_ok($pw)) {
        $err = 'Use at least 8 characters with a letter and a number.';
        $p = 'signin';
    } elseif ($pw !== ($_POST['pw2'] ?? '')) {
        $err = 'Passwords don’t match.';
        $p = 'signin';
    } elseif ($dup) {
        $err = 'That email already has an account. Try signing in.';
        $p = 'signin';
    } else {
        $un = $base = strtolower(preg_replace('/\W/', '', $name)) ?: 'user';
        $n = 2;
        while (in_array($un, array_column($d['users'], 'username'))) $un = $base . $n++;
        $d['users'][] = ['name' => $name, 'username' => $un, 'email' => $em, 'phone' => '', 'private' => 1, 'hash' => password_hash($pw, PASSWORD_DEFAULT), 'joined' => date('Y-m-d'), 'last_login' => date('Y-m-d H:i:s'), 'pw_changed' => date('Y-m-d H:i:s')];
        save_data($d);
        session_regenerate_id(true);
        $_SESSION['u'] = $em;
        redirect_to_page('home');
    }
}
if ($a == 'verify') {
    $rid = strtolower(trim($_POST['id'] ?? ''));
    $hit = null;
    foreach ($d['users'] as $u) if ($rid !== '' && (strtolower($u['email']) == $rid || strtolower($u['username']) == $rid)) $hit = $u;
    $p = 'recover';
    if (!DEMO) $err = 'Password recovery by email isn’t set up yet. Please contact support.';
    elseif (!$hit || ($hit['role'] ?? '') === 'admin') $err = 'We couldn’t find a member account with that email.';
    elseif (($_POST['code'] ?? '') === '72550') {
        $_SESSION['rec'] = $hit['email'];
        redirect_to_page('recover');
    } else $err = 'Wrong code. Demo code is 72550.';
}
if ($a == 'recover') {
    if (!DEMO || empty($_SESSION['rec'])) redirect_to_page('recover');
    if (!password_ok($_POST['pw'] ?? '')) {
        $err = 'Use at least 8 characters with a letter and a number.';
        $p = 'recover';
    } else {
        foreach ($d['users'] as $i => $u) if (strtolower($u['email']) == strtolower($_SESSION['rec'])) {
            $d['users'][$i]['hash'] = password_hash($_POST['pw'], PASSWORD_DEFAULT);
            $d['users'][$i]['pw_changed'] = date('Y-m-d H:i:s');
        }
        save_data($d);
        unset($_SESSION['rec']);
        $_SESSION['flash'] = 'Password updated. You can sign in now.';
        redirect_to_page('signin');
    }
}
