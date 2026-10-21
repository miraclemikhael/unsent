<?php
defined('ROOT') || exit;
/**
 * @var mixed $d
 * @var mixed $me
 * @var mixed $a
 */
if ($a == 'start') {
    $id = uniqid();
    $d['letters'][] = ['id' => $id, 'owner' => $me['email'], 'kind' => $_POST['kind'] ?? 'someone', 'to' => mb_substr(trim($_POST['to'] ?? ''), 0, 40), 'rel' => '', 'title' => mb_substr(trim($_POST['title'] ?? ''), 0, 120) ?: 'Untitled letter', 'body' => '', 'paper' => 'quiet', 'date' => date('M j, Y')];
    sv($d);
    go("editor&id=$id");
}
if ($a == 'letter') {
    $id = preg_match('/^\w{1,20}$/', $_POST['id'] ?? '') ? $_POST['id'] : '';
    $f = [];
    foreach ($d['letters'] as $i => $l) if ($id && $l['id'] == $id && own($l, $me['email'])) $f[] = $i;
    $kind = in_array($_POST['kind'] ?? '', ['someone', 'past', 'other', 'myself']) ? $_POST['kind'] : 'someone';
    $row = ['title' => mb_substr(trim($_POST['title'] ?? ''), 0, 120) ?: 'Untitled letter', 'to' => mb_substr(trim($_POST['to'] ?? ''), 0, 40), 'kind' => $kind, 'body' => cleanbody($_POST['body'] ?? ''), 'paper' => preg_replace('/[^\w:.\-]/', '', $_POST['paper'] ?? 'img:birds'), 'front' => upf($_POST['front'] ?? ''), 'pfont' => in_array($_POST['pfont'] ?? '', ['caveat', 'kalam', 'dancing', 'lora', 'elite', 'manrope']) ? $_POST['pfont'] : '', 'pcolor' => preg_match('/^#[0-9a-fA-F]{6}$/', $_POST['pcolor'] ?? '') ? $_POST['pcolor'] : '', 'efont' => in_array($_POST['efont'] ?? '', ['caveat', 'kalam', 'dancing', 'lora', 'elite', 'manrope']) ? $_POST['efont'] : '', 'esize' => in_array((int)($_POST['esize'] ?? 0), [12, 16, 18, 20, 24, 28, 32, 40, 48]) ? (int)$_POST['esize'] : 0];
    foreach ($f ? [$d['letters'][$f[0]]] : [] as $o) if (($o['front'] ?? '') && $o['front'] !== $row['front']) rmf($o['front']);
    if ($f) {
        $d['letters'][$f[0]] = array_merge($d['letters'][$f[0]], $row);
    } else {
        $id = uniqid();
        $d['letters'][] = array_merge(['id' => $id, 'owner' => $me['email'], 'rel' => '', 'date' => date('M j, Y')], $row);
    }
    sv($d);
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['ok' => 1, 'id' => $id]);
        exit;
    }
    go(isset($_POST['keep']) ? 'unsent' : "editor&id=$id");
}
if ($a == 'clearall') {
    foreach ($d['letters'] as $l) if (own($l, $me['email'])) rmf($l['front'] ?? '');
    $d['letters'] = array_values(array_filter($d['letters'], fn($l) => !own($l, $me['email'])));
    sv($d);
    go('unsent');
}
if ($a == 'pcup') {
    header('Content-Type: application/json');
    $f = $_FILES['pic'] ?? null;
    $ok = $f && $f['error'] === 0 && $f['size'] <= 8 * 1048576 && ($im = @getimagesize($f['tmp_name'])) && in_array($im[2], [IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_WEBP]);
    if (!$ok) {
        echo json_encode(['ok' => 0, 'err' => 'Please choose a PNG, JPG or WebP under 8 MB.']);
        exit;
    }
    @mkdir(ROOT . '/uploads', 0777, true);
    $x = [IMAGETYPE_PNG => 'png', IMAGETYPE_JPEG => 'jpg', IMAGETYPE_WEBP => 'webp'][$im[2]];
    $n = 'uploads/' . bin2hex(random_bytes(8)) . '.' . $x;
    move_uploaded_file($f['tmp_name'], ROOT . '/' . $n);
    echo json_encode(['ok' => 1, 'src' => $n]);
    exit;
}
if ($a == 'del') {
    $did = (string)($_POST['id'] ?? '');
    foreach ($d['letters'] as $l) if ($l['id'] == $did && own($l, $me['email'])) rmf($l['front'] ?? '');
    $d['letters'] = array_values(array_filter($d['letters'], fn($l) => !($l['id'] == $did && own($l, $me['email']))));
    sv($d);
    go('unsent');
}
