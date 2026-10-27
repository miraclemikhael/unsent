<?php
defined('ROOT') || exit;
/**
 * @var mixed $d
 * @var mixed $me
 * @var mixed $mi
 * @var mixed $a
 */
if ($a == 'theme') {
    if (in_array($_POST['v'] ?? '', ['light', 'dark'])) {
        $d['users'][$mi]['a_theme'] = $_POST['v'];
        save_data($d);
    }
    exit;
}
if ($a == 'mood') {
    $m = $_POST['m'] ?? '';
    if (in_array($m, ['Heavy', 'Low', 'Okay', 'Good', 'Bright'])) {
        $g = mb_substr(trim(strip_tags($_POST['g'] ?? '')), 0, 80);
        $ms = $d['users'][$mi]['moods'] ?? [];
        $ms[date('Y-m-d')] = ['m' => $m, 'g' => $g];
        krsort($ms);
        $d['users'][$mi]['moods'] = array_slice($ms, 0, 60, true);
        save_data($d);
    }
    redirect_to_page('home');
}
if ($a == 'avup') {
    header('Content-Type: application/json');
    $f = $_FILES['pic'] ?? null;
    $ok = $f && $f['error'] === 0 && $f['size'] <= 5 * 1048576 && ($im = @getimagesize($f['tmp_name'])) && in_array($im[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP]);
    if (!$ok) {
        echo json_encode(['ok' => 0, 'err' => 'Please choose a PNG, JPG or WebP under 5 MB.']);
        exit;
    }
    @mkdir(ROOT . '/uploads', 0777, true);
    $x = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_WEBP => 'webp'][$im[2]];
    $n = 'uploads/' . bin2hex(random_bytes(8)) . '.' . $x;
    if (!move_uploaded_file($f['tmp_name'], ROOT . '/' . $n)) {
        echo json_encode(['ok' => 0, 'err' => 'Upload failed, try again.']);
        exit;
    }
    delete_upload($d['users'][$mi]['avatar'] ?? '');
    $d['users'][$mi]['avatar'] = $n;
    save_data($d);
    echo json_encode(['ok' => 1, 'src' => $n]);
    exit;
}
if ($a == 'avrm') {
    header('Content-Type: application/json');
    delete_upload($d['users'][$mi]['avatar'] ?? '');
    unset($d['users'][$mi]['avatar']);
    save_data($d);
    echo json_encode(['ok' => 1]);
    exit;
}
if ($a == 'profile') {
    $old = $me['email'];
    $bad = '';
    $new = strtolower(trim($_POST['email'] ?? ''));
    if (!filter_var($new, FILTER_VALIDATE_EMAIL)) {
        $new = $old;
        $bad = 'That email address isn’t valid.';
    }
    foreach ($d['users'] as $j => $u) if ($j != $mi && strtolower($u['email']) == strtolower($new)) {
        $new = $old;
        $bad = 'That email is already in use.';
    }
    $nm = mb_substr(trim($_POST['name'] ?? ''), 0, 60);
    if ($nm !== '') $d['users'][$mi]['name'] = $nm;
    else $bad = $bad ?: 'Please enter your name.';
    $un = trim($_POST['username'] ?? '');
    $udup = false;
    foreach ($d['users'] as $j => $u) if ($j != $mi && strcasecmp($u['username'], $un) == 0) $udup = true;
    if (preg_match('/^[\w.\-]{2,30}$/', $un) && !$udup) $d['users'][$mi]['username'] = $un;
    else $bad = $bad ?: 'Usernames use 2–30 letters, numbers, dots or dashes, and must be unique.';
    $d['users'][$mi]['phone'] = preg_replace('/[^\d+\-\s().]/', '', mb_substr(trim($_POST['phone'] ?? ''), 0, 25));
    $d['users'][$mi]['email'] = $new;
    if ($new != $old) foreach ($d['letters'] as $j => $l) if (is_owner($l, $old)) $d['letters'][$j]['owner'] = $new;
    $_POST['email'] = $new;
    $_SESSION['flash'] = $bad ?: 'Changes saved';
    if ($bad) $_SESSION['flash_t'] = 'error';
    $d['users'][$mi]['private'] = isset($_POST['private']) ? 1 : 0;
    foreach (['a_calm'] as $k) $d['users'][$mi][$k] = isset($_POST[$k]) ? 1 : 0;
    $d['users'][$mi]['a_theme'] = in_array($_POST['a_theme'] ?? '', ['light', 'dark', 'auto']) ? $_POST['a_theme'] : 'light';
    save_data($d);
    $_SESSION['u'] = $_POST['email'];
    redirect_to_page('profile#' . preg_replace('/\W/', '', $_POST['tab'] ?? 'profile'));
}
if ($a == 'delacct') {
    delete_upload($d['users'][$mi]['avatar'] ?? '');
    foreach ($d['letters'] as $l) if (is_owner($l, $me['email'])) delete_upload($l['front'] ?? '');
    $d['letters'] = array_values(array_filter($d['letters'], fn($l) => !is_owner($l, $me['email'])));
    unset($d['users'][$mi]);
    $d['users'] = array_values($d['users']);
    save_data($d);
    session_destroy();
    redirect_to_page('signin');
}
