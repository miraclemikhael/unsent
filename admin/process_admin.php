<?php
defined('ROOT') || exit;
/**
 * @var mixed $d
 * @var mixed $me
 * @var mixed $a
 * @var mixed $j
 */
if (str_starts_with($a, 'adm_')) {
    if (!isadmin() || !hash_equals($_SESSION['csrf'] ?? '', $_POST['t'] ?? '')) go('home');
    $who = strtolower($_POST['u'] ?? '');
    $self = strtolower($me['email']);
    foreach ($d['users'] as $j => $u) if (strtolower($u['email']) == $who && $who != $self) {
        if ($a == 'adm_role') {
            $adm = ($u['role'] ?? '') === 'admin';
            $d['users'][$j]['role'] = $adm ? 'user' : 'admin';
            aud($d, $adm ? 'Removed admin access from' : 'Made admin', $u['name']);
            $_SESSION['flash'] = 'Role updated';
        }
        if ($a == 'adm_status') {
            $sus = ($u['status'] ?? '') === 'suspended';
            $d['users'][$j]['status'] = $sus ? 'active' : 'suspended';
            aud($d, $sus ? 'Reactivated' : 'Suspended', $u['name']);
            $_SESSION['flash'] = $sus ? 'Account reactivated' : 'Account suspended';
        }
        if ($a == 'adm_user') {
            rmf($u['avatar'] ?? '');
            foreach ($d['letters'] as $l) if (strtolower($l['owner']) == $who) rmf($l['front'] ?? '');
            $d['letters'] = array_values(array_filter($d['letters'], fn($l) => strtolower($l['owner']) != $who));
            unset($d['users'][$j]);
            $d['users'] = array_values($d['users']);
            aud($d, 'Deleted account of', $u['name']);
            $_SESSION['flash'] = 'Account deleted';
        }
        break;
    }
    if ($a == 'adm_set') {
        $d['settings']['signups'] = isset($_POST['signups']) ? 1 : 0;
        aud($d, 'Turned new sign-ups ' . ($d['settings']['signups'] ? 'on' : 'off'));
        $_SESSION['flash'] = 'Settings saved';
    }
    sv($d);
    go('admin&v=' . preg_replace('/\W/', '', $_POST['v'] ?? 'overview'));
}
