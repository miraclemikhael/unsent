<?php
// One-time import: data.json -> MySQL. Run: php migrate.php
// Edit the credentials below, and run schema.sql first.
$pdo = new PDO('mysql:host=localhost;dbname=letters_app;charset=utf8mb4', 'root', '', [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
]);

$data = json_decode(file_get_contents(__DIR__ . '/data.json'), true);
$pdo->beginTransaction();

// ---- users + mood logs ----
$insUser = $pdo->prepare('INSERT INTO users
  (name, username, email, phone, is_private, password_hash, theme, n_remind, n_streak, n_tips, a_calm)
  VALUES (?,?,?,?,?,?,?,?,?,?,?)');
$insMood = $pdo->prepare('INSERT INTO mood_logs (user_id, log_date, mood, note)
  VALUES (?,?,?,?) ON DUPLICATE KEY UPDATE mood=VALUES(mood), note=VALUES(note)');

$byEmail = [];     // email    => user id
$byUsername = [];  // username => user id

foreach ($data['users'] as $u) {
    $insUser->execute([
        $u['name'], $u['username'], strtolower($u['email']), $u['phone'] ?? '',
        $u['private'] ?? 1, $u['hash'], $u['a_theme'] ?? 'dark',
        $u['n_remind'] ?? 0, $u['n_streak'] ?? 0, $u['n_tips'] ?? 0, $u['a_calm'] ?? 0,
    ]);
    $id = (int)$pdo->lastInsertId();
    $byEmail[strtolower($u['email'])] = $id;
    $byUsername[$u['username']] = $id;

    foreach (($u['moods'] ?? []) as $date => $m) {
        $insMood->execute([$id, $date, $m['m'] ?? '', $m['g'] ?? '']);
    }
}

// ---- letters ----
$insLetter = $pdo->prepare('INSERT INTO letters
  (id, user_id, kind, recipient, relationship, title, body, paper, front_image, font, color, written_on)
  VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');

$skipped = [];
foreach ($data['letters'] as $l) {
    $owner = strtolower($l['owner']);
    // exact email match, else fall back to the part before "@" as username
    $uid = $byEmail[$owner] ?? $byUsername[explode('@', $owner)[0]] ?? null;
    if (!$uid) { $skipped[] = $l['id']; continue; }

    $ts = strtotime($l['date'] ?? '');
    $insLetter->execute([
        $l['id'], $uid, $l['kind'] ?? 'someone', $l['to'] ?? '', $l['rel'] ?? '',
        $l['title'], $l['body'], $l['paper'] ?? 'quiet', $l['front'] ?? '',
        $l['pfont'] ?? '', $l['pcolor'] ?? '', $ts ? date('Y-m-d', $ts) : null,
    ]);
}

$pdo->commit();
echo "Done. Skipped letters (no matching owner): " . ($skipped ? implode(', ', $skipped) : 'none') . "\n";
