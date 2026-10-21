<?php
defined('ROOT') || exit;
function rows($L, $link = false)
{
    foreach ($L as $l) {
        $t = txt($l['body']);
        $tg = $link ? 'a' : 'div';
        $at = $link ? ' href="?p=unsent&open=' . urlencode($l['id']) . '" aria-label="Open letter: ' . e($l['title']) . '"' : ' tabindex=0 role=button';
        echo '<' . $tg . $at . ' class=row data-k="' . e($l['kind']) . '" data-t="' . e($l['title']) . '" data-b="' . e($t) . '" data-d="' . e($l['date']) . ' · ' . str_word_count($t) . ' words" data-id="' . e($l['id']) . '" data-tag="' . e(KL[$l['kind']] ?? '') . '" data-pc="' . (upf($l['front'] ?? '') ?: (str_starts_with($l['paper'], 'img:') && is_file(ROOT . '/img/postcards/' . substr($l['paper'], 4) . '-front.jpg') ? 'img/postcards/' . substr($l['paper'], 4) . '-front.jpg' : '')) . '"><img src="' . (upf($l['front'] ?? '') ?: (str_starts_with($l['paper'], 'img:') && is_file(ROOT . '/img/postcards/' . substr($l['paper'], 4) . '-front.jpg') ? 'img/postcards/' . substr($l['paper'], 4) . '-front.jpg' : 'img/thumb.png')) . '" alt="" class="' . (str_starts_with($l['paper'], 'img:') || upf($l['front'] ?? '') ? 'pcthumb' : '') . '"><b>' . e($l['title']) . '</b><span class="tag ' . e($l['kind']) . '">' . e(KL[$l['kind']] ?? '') . '</span><small class=to>To ' . e($l['to'] ?: 'you') . ',</small><small class=ex>“' . e(mb_substr($t, 0, 62)) . '”</small><small class=dt>' . e($l['date']) . '</small></' . $tg . '>';
    }
}
function top($h, $s, $btn = '')
{
    echo "<header class=top><div><h1>$h</h1><p class=mut>$s</p></div>$btn</header>";
}
