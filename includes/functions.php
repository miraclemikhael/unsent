<?php
defined('ROOT') || exit;
function own($l, $em)
{
    return strcasecmp((string)($l['owner'] ?? ''), (string)$em) === 0;
}
function pwok($p)
{
    return strlen($p) >= 8 && preg_match('/[A-Za-z]/', $p) && preg_match('/\d/', $p);
}
function e($s)
{
    return htmlspecialchars((string)$s, ENT_QUOTES);
}
function upf($f)
{
    /* uploads live in uploads/; old letters may still point at img/uploads/, so both are checked */
    if (!preg_match('~^(?:uploads|img/uploads)/([\w.\-]+)$~', (string)$f, $m)) return '';
    foreach (['uploads/', 'img/uploads/'] as $dir) if (is_file(ROOT . '/' . $dir . $m[1])) return $dir . $m[1];
    return '';
}
function rmf($f)
{
    if ($f = upf($f)) @unlink(ROOT . '/' . $f);
}
function av($u, $c = '')
{
    $a = upf($u['avatar'] ?? '');
    $n = mb_strtoupper(mb_substr(trim($u['name'] ?? '') ?: '?', 0, 1));
    return '<i class="av ' . $c . ($a ? ' has' : '') . '"' . ($a ? ' style="background-image:url(' . e($a) . ')"' : '') . '>' . ($a ? '' : e($n)) . '</i>';
}
function emptyst()
{
    return '<div class=empty><img class=lt src=img/empty.png alt=""><img class=dk src=img/empty-dark.png alt=""><a class="btn sm" href="?p=write">' . ic('pen', 17) . 'Write a letter</a></div>';
}
function cleanbody($h)
{
    $h = strip_tags((string)$h, '<b><i><u><br><div><p><ul><ol><li><strong><em><span>');
    /* opening tags keep nothing but our own class names (no onclick=, style=, href= ...) */
    return preg_replace_callback('/<(b|i|u|br|div|p|ul|ol|li|strong|em|span)\b([^>]*)>/i', function ($m) {
        preg_match('/class\s*=\s*"([^"]*)"/i', $m[2], $c);
        $k = array_filter(preg_split('/\s+/', $c[1] ?? ''), fn($x) => preg_match('/^(script|sm|f-(caveat|kalam|dancing|lora|elite|manrope)|s-(12|14|16|18|20|24|28|32|40|48))$/', $x));
        return '<' . strtolower($m[1]) . ($k ? ' class="' . implode(' ', $k) . '"' : '') . '>';
    }, $h);
}
function go($p)
{
    header("Location: index.php?p=$p");
    exit;
}
/* ---- admin: role comes from the saved user record, never from the browser ---- */
function isadmin()
{
    global $me;
    return ($me['role'] ?? '') === 'admin';
}

const KL = ['someone' => 'Someone', 'past' => 'My Past', 'other' => 'Other', 'myself' => 'Myself'];
function txt($h)
{
    return trim(html_entity_decode(strip_tags(str_replace(['<br>', '</div>', '</p>'], "\n", $h))));
}
