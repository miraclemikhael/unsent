<?php
defined('ROOT') || exit;
/* print the user sidebar and open the main area */
function page_start($act)
{
    global $me;
    $n = ['home' => ['Home', 'home'], 'write' => ['Write', 'pen'], 'unsent' => ['Unsent', 'mail'], 'profile' => ['Profile', 'user']];
    if (is_admin()) $n['admin'] = ['Admin', 'shield'];
    echo '<div class=app><aside><div><a href="?p=home"><img class=logo src=img/logo.png alt=Unsent></a><nav>';
    foreach ($n as $k => $v) echo "<a href='?p=$k' class='" . ($k == $act ? 'on' : '') . "'>" . icon($v[1], 20) . "{$v[0]}</a>";
    echo '</nav></div><div class=foot2><div class=quote>' . icon('spark', 18) . '<p class=hand>Some words feel lighter once they\'re written.</p></div><div class=mew><div class=menu id=mem role=menu hidden><a role=menuitem href="?p=profile#profile">' . icon('user', 17) . 'Your profile</a><a role=menuitem href="?p=profile#appearance">' . icon('palette', 17) . 'Preferences</a><form method=post><input type=hidden name=a value=logout><button role=menuitem class=out>' . icon('out', 17) . 'Log out</button></form></div><button type=button class=me id=meb aria-haspopup=menu aria-expanded=false aria-controls=mem>' . avatar_html($me) . '<span><b>' . esc($me['name']) . (is_admin() ? ' <em class="rb adm">Admin</em>' : '') . '</b><small>' . esc($me['email']) . '</small></span>' . icon('dots', 18) . '</button></div></div></aside><main id=main tabindex=-1>';
}
