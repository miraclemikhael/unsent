<?php
defined('ROOT') || exit;
const DB = ROOT . '/config/data.json';
function db()
{
    $d = null;
    if (is_file(DB)) {
        $d = json_decode((string)file_get_contents(DB), true);
        if (!is_array($d)) { /* never overwrite a file we cannot read */
            http_response_code(500);
            exit('data.json could not be read. Restore it from a backup.');
        }
    }
    $d = is_array($d) ? $d : [];
    return $d + ['users' => [], 'letters' => []];
}
function sv($d)
{
    $tmp = DB . '.' . bin2hex(random_bytes(4)) . '.tmp';
    if (file_put_contents($tmp, json_encode($d, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE), LOCK_EX) !== false) rename($tmp, DB);
    else @unlink($tmp);
}
