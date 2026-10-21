<?php
defined('ROOT') || exit;
function adate($s)
{
    $t = strtotime((string)$s);
    return $t ? date('M j, Y', $t) : '—';
}
function agot($s)
{
    $t = $s ? strtotime($s) : 0;
    if (!$t) return '—';
    $tm = strlen($s) > 10;
    return date('Y-m-d', $t) == date('Y-m-d') ? 'Today' . ($tm ? ', ' . date('H:i', $t) : '') : date('M j', $t) . ($tm ? ', ' . date('H:i', $t) : ', ' . date('Y', $t));
}
function uidn($em)
{
    return 'USR-' . (10000 + crc32(strtolower($em)) % 90000);
}
function dlt($c, $p, $lab)
{
    if (!$p) return $c ? 'New' : '';
    return sprintf('%+.1f%%', ($c - $p) / $p * 100) . ' vs ' . $lab;
}
/* per-day activity, counted only from dates: [date => [email => 1]], letters per day, check-ins per day */
function act($d)
{
    $ok = [];
    $ev = [];
    $lc = [];
    $mc = [];
    foreach ($d['users'] as $u) {
        $em = strtolower($u['email']);
        $ok[$em] = 1;
        foreach (($u['moods'] ?? []) as $k => $m) {
            $ev[$k][$em] = 1;
            $mc[$k] = ($mc[$k] ?? 0) + 1;
        }
        if (!empty($u['last_active'])) $ev[substr($u['last_active'], 0, 10)][$em] = 1;
    }
    foreach ($d['letters'] as $l) {
        $t = strtotime($l['date'] ?? '');
        if (!$t) continue;
        $k = date('Y-m-d', $t);
        $lc[$k] = ($lc[$k] ?? 0) + 1;
        $em = strtolower($l['owner']);
        if (isset($ok[$em])) $ev[$k][$em] = 1;
    }
    return [$ev, $lc, $mc];
}
function jn($u, $ev)
{
    if (!empty($u['joined'])) return $u['joined'];
    $em = strtolower($u['email']);
    $f = null;
    foreach ($ev as $k => $x) if (isset($x[$em]) && ($f === null || $k < $f)) $f = $k;
    return $f;
}
function la($u, $ev)
{
    if (!empty($u['last_active'])) return $u['last_active'];
    $em = strtolower($u['email']);
    $f = null;
    foreach ($ev as $k => $x) if (isset($x[$em]) && ($f === null || $k > $f)) $f = $k;
    return $f;
}
function aud(&$d, $msg, $on = '')
{
    global $me;
    $d['audit'][] = ['t' => date('Y-m-d H:i:s'), 'by' => $me['name'], 'a' => $msg, 'on' => $on];
    $d['audit'] = array_slice($d['audit'], -200);
}
function afrm($a, $k, $v, $view, $label, $cls, $conf = '')
{
    return '<form method=post class=inl' . ($conf ? ' data-confirm="' . e($conf) . '"' : '') . '><input type=hidden name=a value=' . $a . '><input type=hidden name=t value="' . e($_SESSION['csrf'] ?? '') . '"><input type=hidden name=' . $k . ' value="' . e($v) . '"><input type=hidden name=v value=' . $view . '><button class="btn sm ' . $cls . '">' . $label . '</button></form>';
}
/* the admin workspace has its own sidebar: no writing, letters or profile */
function ashell($act)
{
    global $me;
    $n = ['overview' => ['Overview', 'grid'], 'users' => ['Users', 'users'], 'activity' => ['Activity', 'pulse'], 'audit' => ['Admin audit log', 'clip'], 'settings' => ['Settings', 'sliders']];
    echo '<div class="app adm"><aside><div><a href="?p=admin"><img class=logo src=img/logo.png alt=Unsent></a><div class=aw>' . ic('shield', 15) . '<span>ADMIN WORKSPACE</span></div><nav>';
    foreach ($n as $k => $x) echo "<a href='?p=admin&amp;v=$k' class='" . ($k == $act ? 'on' : '') . "'>" . ic($x[1], 20) . "{$x[0]}</a>";
    echo '<a class=back2 href="?p=home">' . ic('arrow', 16) . 'Back to Unsent</a></nav></div><div class=foot2><div class=quote>' . ic('spark', 18) . '<p class=hand>Some words feel lighter once they’re written.</p><small>A little care behind every account.</small></div><div class=mew><div class=menu id=mem role=menu hidden><a role=menuitem href="?p=home">' . ic('home', 17) . 'Back to Unsent</a><form method=post><input type=hidden name=a value=logout><button role=menuitem class=out>' . ic('out', 17) . 'Log out</button></form></div><button type=button class=me id=meb aria-haspopup=menu aria-expanded=false aria-controls=mem>' . av($me) . '<span><b>' . e($me['name']) . '</b><small>Administrator</small></span>' . ic('dots', 18) . '</button></div></div></aside><main id=main tabindex=-1>';
}
function atop($crumb)
{
    echo '<div class=atop><small class=crumb>Admin / ' . e($crumb) . '</small><div class=apr><small>Appearance</small><div class=seg id=aseg><button type=button data-t=light>' . ic('sun', 14) . ' Light</button><button type=button data-t=dark>' . ic('moon', 14) . ' Dark</button></div></div></div>';
}
function ausers($d, $ev, $ps, $v)
{
    global $me;
    $U = $d['users'];
    usort($U, fn($a, $b) => strcmp(jn($b, $ev) ?? '', jn($a, $ev) ?? ''));
    $rows = '';
    foreach ($U as $u) {
        $self = strtolower($u['email']) == strtolower($me['email']);
        $sus = ($u['status'] ?? '') === 'suspended';
        $ad = ($u['role'] ?? '') === 'admin';
        $j = jn($u, $ev);
        $L = agot(la($u, $ev));
        $rows .= '<tr tabindex=0 data-s="' . e(strtolower($u['name'] . ' ' . $u['username'] . ' ' . $u['email'])) . '" data-st=' . ($sus ? 'suspended' : 'active') . ' data-stl=' . ($sus ? 'Suspended' : 'Active') . ' data-ad=' . ($ad ? 1 : 0) . ' data-role=' . ($ad ? 'Admin' : 'Member') . ' data-self=' . ($self ? 1 : 0) . ' data-j="' . e($j) . '" data-jd="' . e(adate($j)) . '" data-la="' . e($L) . '" data-name="' . e($u['name']) . '" data-email="' . e($u['email']) . '" data-id="' . uidn($u['email']) . '"><td><div class=who>' . av($u) . '<span><b>' . e($u['name']) . ($ad ? ' <em class="rb adm">Admin</em>' : '') . '</b><small>' . e($u['email']) . '</small></span></div></td><td>' . e(adate($j)) . '</td><td>' . e($L) . '</td><td><span class="st ' . ($sus ? 'no' : 'ok') . '">' . ($sus ? 'Suspended' : 'Active') . '</span></td><td class=ra><button type=button class="btn sm wh vw">View</button>' . ($self ? '' : afrm('adm_status', 'u', $u['email'], $v, $sus ? 'Reactivate' : 'Suspend', $sus ? 'wh' : 'rd')) . '</td></tr>';
    }
    $t = e($_SESSION['csrf'] ?? '');
    $f = fn($a, $id, $label, $cls, $conf = '') => '<form method=post class=inl id=' . $id . ($conf ? ' data-confirm="' . e($conf) . '"' : '') . '><input type=hidden name=a value=' . $a . '><input type=hidden name=t value="' . $t . '"><input type=hidden name=u><input type=hidden name=v value=' . $v . '><button class="btn sm ' . $cls . '" id=' . $id . 'b>' . $label . '</button></form>';
    echo '<div class=ugrid><div class="card um"><div class=between><h3>User management</h3><small class=mut>' . count($U) . ' accounts</small></div>'
        . '<div class=tools><label class=srch>' . ic('search', 16) . '<input id=uq placeholder="Search name or email" aria-label="Search users"></label><label class=sel>' . ic('filter', 16) . '<select id=ufs aria-label="Filter by status"><option value=all>All statuses<option value=active>Active<option value=suspended>Suspended<option value=admin>Admins</select></label><label class=sel><select id=ufj aria-label="Filter by join date"><option value=all>Joined: Any time<option value=7>Joined: Last 7 days<option value=30>Joined: Last 30 days</select></label></div>'
        . '<div class=tw><table class=at id=ut data-ps=' . $ps . '><thead><tr><th>User<th>Joined ↓<th>Last active<th>Status<th>Actions</tr></thead><tbody>' . $rows . '</tbody></table></div>'
        . '<div class=pgn><small id=uinfo></small><span><small id=upg></small><button type=button class="btn sm wh" id=uprev>Previous</button><button type=button class="btn sm wh" id=unext>Next</button></span></div></div>'
        . '<div class="card sum"><div class=between><h3>Account summary</h3><button type=button class=ib id=sx aria-label="Close summary">' . ic('close', 18) . '</button></div><div id=sbody><div class=sh><i id=sav></i><span><b id=sn></b><em class="st ok" id=ss></em></span></div><p class=mut id=se2></p><dl class=dls><div><dt>Account ID<dd id=si></div><div><dt>Joined<dd id=sj></div><div><dt>Last active<dd id=sl></div><div><dt>Role<dd id=sr></div></dl>'
        . '<div class=sact id=sact>' . $f('adm_role', 'fr', 'Make admin', 'yl') . $f('adm_status', 'fs', 'Suspend', 'rd') . $f('adm_user', 'fd', 'Delete', 'wh', 'Delete this account and every letter?') . '</div>'
        . '<div class=green>' . ic('lock', 19) . '<div><b>Their words stay theirs.</b><br><small>Letter content and mood entries are not accessible to admins.</small></div></div></div><p class=mut id=sempty hidden>Select a user to see their account summary.</p></div></div>';
}
