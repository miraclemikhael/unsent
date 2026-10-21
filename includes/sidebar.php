<?php
defined('ROOT') || exit;
function shell($act)
{
    global $me;
    $n = ['home' => ['Home', 'home'], 'write' => ['Write', 'pen'], 'unsent' => ['Unsent', 'mail'], 'profile' => ['Profile', 'user']];
    if (isadmin()) $n['admin'] = ['Admin', 'shield'];
    echo '<div class=app><aside><div><a href="?p=home"><img class=logo src=img/logo.png alt=Unsent></a><nav>';
    foreach ($n as $k => $v) echo "<a href='?p=$k' class='" . ($k == $act ? 'on' : '') . "'>" . ic($v[1], 20) . "{$v[0]}</a>";
    echo '</nav></div><div class=foot2><div class=quote>' . ic('spark', 18) . '<p class=hand>Some words feel lighter once they\'re written.</p></div><div class=mew><div class=menu id=mem role=menu hidden><a role=menuitem href="?p=profile#profile">' . ic('user', 17) . 'Your profile</a><a role=menuitem href="?p=profile#appearance">' . ic('palette', 17) . 'Preferences</a><form method=post><input type=hidden name=a value=logout><button role=menuitem class=out>' . ic('out', 17) . 'Log out</button></form></div><button type=button class=me id=meb aria-haspopup=menu aria-expanded=false aria-controls=mem>' . av($me) . '<span><b>' . e($me['name']) . (isadmin() ? ' <em class="rb adm">Admin</em>' : '') . '</b><small>' . e($me['email']) . '</small></span>' . ic('dots', 18) . '</button></div></div></aside><main id=main tabindex=-1>';
}
