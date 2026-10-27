<?php
defined('ROOT') || exit;
/* true if this letter belongs to this email */
function is_owner($l, $em)
{
    return strcasecmp((string)($l['owner'] ?? ''), (string)$em) === 0;
}
/* true if password has 8+ chars, a letter and a number */
function password_ok($p)
{
    return strlen($p) >= 8 && preg_match('/[A-Za-z]/', $p) && preg_match('/\d/', $p);
}
/* make text safe to print in HTML (stops code injection) */
function esc($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES);
}
/* check an upload file name is safe and exists; return its path or empty text */
function upload_path($f)
{
    /* uploads live in uploads/; old letters may still point at img/uploads/, so both are checked */
    if (!preg_match('~^(?:uploads|img/uploads)/([\w.\-]+)$~', (string)$f, $m)) return '';
    foreach (['uploads/', 'img/uploads/'] as $dir) if (is_file(ROOT . '/' . $dir . $m[1])) return $dir . $m[1];
    return '';
}
/* delete an uploaded file from disk */
function delete_upload($f)
{
    if ($f = upload_path($f)) @unlink(ROOT . '/' . $f);
}
/* make the round profile picture (or first letter) HTML */
function avatar_html($u, $c = '')
{
    $a = upload_path($u['avatar'] ?? '');
    $n = mb_strtoupper(mb_substr(trim($u['name'] ?? '') ?: '?', 0, 1));
    return '<i class="av ' . $c . ($a ? ' has' : '') . '"' . ($a ? ' style="background-image:url(' . esc($a) . ')"' : '') . '>' . ($a ? '' : esc($n)) . '</i>';
}
/* HTML shown when there are no letters yet */
function empty_state()
{
    return '<div class=empty><img class=lt src=img/empty.png alt=""><img class=dk src=img/empty-dark.png alt=""><a class="btn sm" href="?p=write">' . icon('pen', 17) . 'Write a letter</a></div>';
}
/* keep only safe HTML tags in a letter body */
function clean_body($h)
{
    $h = strip_tags((string)$h, '<b><i><u><br><div><p><ul><ol><li><strong><em><span>');
    /* opening tags keep nothing but our own class names (no onclick=, style=, href= ...) */
    return preg_replace_callback('/<(b|i|u|br|div|p|ul|ol|li|strong|em|span)\b([^>]*)>/i', function ($m) {
        preg_match('/class\s*=\s*"([^"]*)"/i', $m[2], $c);
        $k = array_filter(preg_split('/\s+/', $c[1] ?? ''), fn($x) => preg_match('/^(script|sm|f-(caveat|kalam|dancing|lora|elite|manrope)|s-(12|14|16|18|20|24|28|32|40|48))$/', $x));
        return '<' . strtolower($m[1]) . ($k ? ' class="' . implode(' ', $k) . '"' : '') . '>';
    }, $h);
}
/* send the browser to index.php?p=PAGE and stop */
function redirect_to_page($p)
{
    header("Location: index.php?p=$p");
    exit;
}
/* ---- admin: role comes from the saved user record, never from the browser ---- */
function is_admin()
{
    global $me;
    return ($me['role'] ?? '') === 'admin';
}

const KL = ['someone' => 'Someone', 'past' => 'My Past', 'other' => 'Other', 'myself' => 'Myself'];
/* turn letter HTML into plain text */
function plain_text($h)
{
    return trim(html_entity_decode(strip_tags(str_replace(['<br>', '</div>', '</p>'], "\n", $h))));
}
