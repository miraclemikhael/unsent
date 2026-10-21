<?php
defined('ROOT') || exit;
/**
 * @var mixed $me
 * @var mixed $L
 */
    head('Home');
    shell('home');
    top('<span data-greet>Good morning</span>, ' . e(strtolower(strtok($me['name'], ' '))) . '.', 'A quiet space for the things that are hard to say.', '<a class="btn" href="?p=write">' . ic('pen', 17) . 'Write something</a>'); ?>
    <div class=hgrid>
        <div class=hmain><?php $MO = ['Heavy' => 'heavy', 'Low' => 'low', 'Okay' => 'okay', 'Good' => 'good', 'Bright' => 'bright'];
                            // per-mood copy: [hint under heading, input placeholder, closing message]
                            $MP = [
                                'Heavy' => ['That sounds heavy. You can let it out here.', 'What’s weighing on you?', 'Thank you for naming it. Carrying <b class="gw hand">{g}</b> is hard, and you don’t have to hold it alone. We’re here with you.', 'weighing on you: '],
                                'Low' => ['It’s okay to feel low. Want to say why?', 'What’s got you feeling down?', 'Thank you for sharing that <b class="gw hand">{g}</b> is on your heart. Be gentle with yourself today.', 'feeling down about '],
                                'Okay' => ['A steady kind of day. Anything on your mind?', 'What’s on your mind today?', 'Thanks for checking in. We’re glad you took a moment for <b class="gw hand">{g}</b>.', 'on your mind: '],
                                'Good' => ['Nice! Let’s remember what made it good.', 'What made today good?', 'We’re glad that <b class="gw hand">{g}</b> made your day good! We wish you more of it.', 'good because of '],
                                'Bright' => ['Love that for you! Tell us what’s lighting you up.', 'What’s making you so happy?', 'We’re so happy that <b class="gw hand">{g}</b> means a lot to you! Keep shining.', 'happy about '],
                            ];
                            $ml = $me['moods'] ?? [];
                            $today = date('Y-m-d');
                            $tm = $ml[$today] ?? null;
                            $mp0 = $MP[$tm['m'] ?? ''] ?? null;
                            $redo = isset($_GET['redo']);
                            $streak = 0;
                            for ($i = $tm ? 0 : 1; isset($ml[date('Y-m-d', strtotime("-$i day"))]); $i++) $streak++; ?>
            <div class=mood>
                <div class=mc>
                    <div class=eyebrow><?= ic('spark', 17) ?>Daily check-in</div>
                    <?php if ($tm && !$redo) {
                        $k = $MO[$tm['m']]; ?>
                        <div class=mdone><img class=mface src="img/mood/<?= $k ?>-on.svg" alt="<?= e($tm['m']) ?>">
                            <div>
                                <p class=mmsg><?php if (($tm['g'] ?? '') !== '') {
                                                    echo str_replace('{g}', e($tm['g'] ?? ''), $MP[$tm['m']][2]);
                                                } else echo 'Thanks for checking in. You’re feeling <b>' . e($tm['m']) . '</b> today.'; ?></p><a class="btn sm wh" href="?p=home&amp;redo=1">Change today’s mood</a>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div>
                            <h2 class=hand>How are you feeling?</h2>
                            <p id=mhint data-def="Tap a face, then log it."><?= e($mp0[0] ?? 'Tap a face, then log it.') ?></p>
                        </div>
                        <form method=post class=mform id=mform><input type=hidden name=a value=mood><input type=hidden name=m id=mval value="<?= e($tm['m'] ?? '') ?>">
                            <div class=mpick><?php foreach ($MO as $n => $k) {
                                                    $on = ($tm['m'] ?? '') == $n;
                                                    echo "<button type=button class='mo" . ($on ? ' on' : '') . "' data-m=$n data-k=$k data-hint=\"" . e($MP[$n][0]) . "\" data-ph=\"" . e($MP[$n][1]) . "\" aria-pressed=" . ($on ? 'true' : 'false') . "><img src=img/mood/$k" . ($on ? '-on' : '') . ".svg alt=''><span>$n</span></button>";
                                                } ?></div>
                            <div class=mrow><label class=gin><?= ic('heart', 17) ?><input name=g maxlength=80 id=gtxt placeholder="<?= e($mp0[1] ?? 'How are you feeling, in a few words?') ?>" value="<?= e($tm['g'] ?? '') ?>" aria-label="Tell us more about your mood"></label><button class="btn mlog" id=mlog<?= $tm ? '' : ' disabled' ?>>Log mood <?= ic('plusc', 17) ?></button></div>
                        </form>
                    <?php } ?>
                </div><img class=reach src=img/hand.png alt="">
            </div>
            <div class="card rl">
                <div class=between>
                    <div>
                        <h2 class=hand>Recent letters</h2><small class=mut>Your words stay private, always.</small>
                    </div><a href="?p=unsent"><b class=sm>View all <?= ic('arrow', 14) ?></b></a>
                </div><?php if (!$L) echo emptyst();
                        rows(array_slice($L, 0, 3), true); ?>
            </div>
        </div>
        <div class=hside>
            <div class=card>
                <div class=between><i class="ic hot"><?= ic('flame') ?></i><small class=mut>This week · <?= array_sum(array_map(fn($i) => isset($ml[date("Y-m-d", strtotime("+$i day", strtotime("monday this week")))]) ? 1 : 0, range(0, 6))) ?>/7</small></div>
                <div class=streak><?= $streak ?> day<?= $streak == 1 ? '' : 's' ?></div><small class=mut style="font-size:12px"><?= $streak ? 'You made room for your thoughts.' : 'Log a mood to start your streak.' ?></small>
                <div class=week><?php $mon = strtotime('monday this week');
                                $wk = 0;
                                $dt = '';
                                foreach (range(0, 6) as $i) {
                                    $ts = strtotime("+$i day", $mon);
                                    $k = date('Y-m-d', $ts);
                                    $x = $ml[$k] ?? null;
                                    $fut = $k > $today;
                                    if ($x) $wk++;
                                    $t = ($k == $today ? 'Today' : date('D j M', $ts)) . ($x ? ' · ' . $x['m'] . (($x['g'] ?? '') !== '' ? ' · ' . $MP[$x['m']][3] . $x['g'] : '') : ($fut ? ' · still ahead' : ' · nothing logged'));
                                    if ($k == $today) $dt = $t;
                                    echo '<button type=button class="wd' . ($k == $today ? ' td' : '') . ($fut ? ' fut' : '') . '" data-t="' . e($t) . '" aria-label="' . e($t) . '" aria-pressed=' . ($k == $today ? 'true' : 'false') . '><span class="' . ($x ? 'log' : '') . '">' . ($x ? '<img src=img/mood/' . $MO[$x['m']] . '-on.svg alt="">' : '') . '</span><small>' . substr(date('D', $ts), 0, 1) . '</small></button>';
                                } ?></div><small class="mut wdet" id=wdet aria-live=polite><?= e($dt) ?></small>
            </div>
            <div class="card dark">
                <div class=between><?= ic('quote') ?></div>
                <div class=fx-env data-scene=envelope aria-hidden=true></div>
                <h2 class=hand>What would you say if you knew you’d be understood?</h2><a class="btn wh" href="?p=write"><?= ic('arrow', 17) ?> Start with this</a>
            </div>
            <div class="green"><?= ic('lock', 19) ?><div><b>Private by design</b><br><small>Your letters are visible only to you.</small></div>
            </div>
        </div>
    </div>
