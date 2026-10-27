<?php
defined('ROOT') || exit;

/* ---- MySQL settings (Laragon default: user root, no password) ---- */
const DB_HOST = '127.0.0.1';
const DB_NAME = 'unsent';
const DB_USER = 'root';
const DB_PASS = '';

/* The site keeps all its data in one PHP array $d:
     $d['users']   = list of users   (each user has a 'moods' list inside)
     $d['letters'] = list of letters
     $d['audit']   = admin audit log
   These two lists say: "array key on the site" => "column in the MySQL table".
   Any key not listed here is saved in the 'extra' column, so nothing is lost. */
const USER_FIELDS = [
    'name' => 'name', 'username' => 'username', 'email' => 'email', 'phone' => 'phone',
    'private' => 'is_private', 'hash' => 'password_hash', 'role' => 'role', 'status' => 'status',
    'joined' => 'joined', 'last_active' => 'last_active', 'avatar' => 'avatar',
    'a_theme' => 'theme', 'a_calm' => 'calm_mode',
    'n_remind' => 'remind_on', 'n_streak' => 'streak_on', 'n_tips' => 'tips_on', 'n_time' => 'remind_time',
];
const LETTER_FIELDS = [
    'id' => 'id', 'owner' => 'owner_email', 'kind' => 'kind', 'to' => 'recipient', 'rel' => 'relationship',
    'title' => 'title', 'body' => 'body', 'paper' => 'paper', 'date' => 'written_on', 'front' => 'front_image',
    'pfont' => 'postcard_font', 'pcolor' => 'postcard_color', 'efont' => 'envelope_font', 'esize' => 'envelope_size',
];
const AUDIT_FIELDS = ['t' => 'at_time', 'by' => 'admin_name', 'a' => 'action', 'on' => 'target'];

/* open the MySQL connection (only once per page) */
function db_connect()
{
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4', DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false, /* numbers come back as numbers */
            ]);
        } catch (PDOException $e) {
            http_response_code(500);
            exit('Cannot connect to MySQL. Start Laragon, import unsent_database.sql in HeidiSQL, and check config/database.php');
        }
    }
    return $pdo;
}

/* turn one MySQL row into a site array (empty columns are left out) */
function row_to_array($row, $fields)
{
    $item = [];
    foreach ($fields as $key => $column) {
        if ($row[$column] !== null) $item[$key] = $row[$column];
    }
    if (!empty($row['extra'])) $item += json_decode($row['extra'], true) ?: [];
    return $item;
}

/* turn one site array into a MySQL row */
function array_to_row($item, $fields)
{
    $row = [];
    foreach ($fields as $key => $column) {
        $row[$column] = $item[$key] ?? null;
        unset($item[$key]);
    }
    unset($item['_id'], $item['moods']);
    $row['extra'] = $item ? json_encode($item, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) : null;
    return $row;
}

/* insert a row, or replace it when the same id already exists */
function put_row($table, $row)
{
    $columns = '`' . implode('`,`', array_keys($row)) . '`';
    $marks = implode(',', array_fill(0, count($row), '?'));
    db_connect()->prepare("REPLACE INTO `$table` ($columns) VALUES ($marks)")->execute(array_values($row));
}

/* delete every row of $table whose id is not in $keep */
function delete_others($table, $keep)
{
    $pdo = db_connect();
    if (!$keep) {
        $pdo->exec("DELETE FROM `$table`");
        return;
    }
    $marks = implode(',', array_fill(0, count($keep), '?'));
    $pdo->prepare("DELETE FROM `$table` WHERE `id` NOT IN ($marks)")->execute(array_values($keep));
}

/* read everything from MySQL into the $d array */
function load_data()
{
    $pdo = db_connect();
    $d = ['users' => [], 'letters' => []];
    $position = []; /* user id => place in $d['users'] */
    foreach ($pdo->query('SELECT * FROM users ORDER BY pos, id') as $row) {
        $user = row_to_array($row, USER_FIELDS);
        $user['_id'] = (int)$row['id'];
        $d['users'][] = $user;
        $position[$row['id']] = array_key_last($d['users']);
    }
    foreach ($pdo->query('SELECT * FROM moods ORDER BY id') as $row) {
        if (!isset($position[$row['user_id']])) continue;
        $mood = ['m' => $row['mood'], 'g' => $row['note']];
        $d['users'][$position[$row['user_id']]]['moods'][$row['day']] = array_filter($mood, fn($v) => $v !== null);
    }
    foreach ($pdo->query('SELECT * FROM letters ORDER BY pos, id') as $row) {
        $d['letters'][] = row_to_array($row, LETTER_FIELDS);
    }
    $audit = [];
    foreach ($pdo->query('SELECT * FROM audit_log ORDER BY id') as $row) {
        $audit[] = row_to_array($row, AUDIT_FIELDS);
    }
    if ($audit) $d['audit'] = $audit;
    foreach ($pdo->query('SELECT * FROM settings') as $row) {
        $d[$row['name']] = json_decode($row['value'], true);
    }
    return $d;
}

/* write the $d array back into MySQL (all or nothing) */
function save_data(&$d)
{
    $pdo = db_connect();
    $pdo->beginTransaction();
    try {
        /* users + their moods */
        $keepUsers = [];
        foreach (array_keys($d['users']) as $i) {
            $row = array_to_row($d['users'][$i], USER_FIELDS);
            $row = ['id' => $d['users'][$i]['_id'] ?? null, 'pos' => count($keepUsers)] + $row;
            put_row('users', $row);
            $id = $row['id'] ?? (int)$pdo->lastInsertId();
            $d['users'][$i]['_id'] = $id;
            $keepUsers[] = $id;
            $pdo->prepare('DELETE FROM moods WHERE user_id = ?')->execute([$id]);
            foreach (($d['users'][$i]['moods'] ?? []) as $day => $mood) {
                put_row('moods', ['user_id' => $id, 'day' => (string)$day, 'mood' => $mood['m'] ?? null, 'note' => $mood['g'] ?? null]);
            }
        }
        delete_others('users', $keepUsers);
        $pdo->exec('DELETE FROM moods WHERE user_id NOT IN (SELECT id FROM users)');

        /* letters */
        $keepLetters = [];
        foreach (array_values($d['letters']) as $pos => $letter) {
            $row = ['pos' => $pos] + array_to_row($letter, LETTER_FIELDS);
            put_row('letters', $row);
            $keepLetters[] = $row['id'];
        }
        delete_others('letters', $keepLetters);

        /* audit log: simple way = clear it and write it again */
        $pdo->exec('DELETE FROM audit_log');
        foreach (($d['audit'] ?? []) as $entry) put_row('audit_log', array_to_row($entry, AUDIT_FIELDS));

        /* any other top-level value (like 'wiped2') */
        foreach ($d as $name => $value) {
            if (in_array($name, ['users', 'letters', 'audit'], true)) continue;
            put_row('settings', ['name' => $name, 'value' => json_encode($value)]);
        }
        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        error_log('save_data failed: ' . $e->getMessage());
        http_response_code(500);
        exit('Could not save to the database.');
    }
}
