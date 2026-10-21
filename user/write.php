<?php
defined('ROOT') || exit;
/**
 * @var mixed $p
 * @var mixed $v
 * @var mixed $L
 */
    head('Write');
    shell('write');
    $cur = null;
    if ($p == 'editor') foreach ($L as $l) if ($l['id'] == ($_GET['id'] ?? '')) $cur = $l;
    if ($p == 'editor' && !$cur) go('write');
    $cur = $cur ?: ['id' => '', 'kind' => 'someone', 'to' => '', 'rel' => '', 'title' => '', 'paper' => 'img:birds', 'body' => ''];
    $body = trim($cur['body']) === '' ? '<p class="script">Dear you,</p><p><br></p><p class="script sm">With love,</p>' : cleanbody($cur['body']);
    if (!preg_match('/<(p|div)\b/i', $body)) $body = '<p>' . str_replace(['<br><br>', '<br />'], '</p><p>', $body) . '</p>';
    /* postcard stationery: name, back geometry in % (msg x,y,w,h / address x,y,w,h), ink colour */
    $PC = ['birds' => ['Quiet blue', 'Birds on a wire', [8, 11, 39, 79], [52, 44, 46, 60], '#2a4a8c'], 'seal' => ['Sea rocks', 'Seal Rocks, San Francisco', [4, 33, 43, 58], [56, 38, 62, 72], '#34445c'], 'park' => ['Green park', 'The Park, Rawalpindi', [9, 21, 36, 69], [52, 40, 46, 56], '#2f6b45'], 'corfu' => ['Corfu', 'Achilleion, Corfu', [16, 31, 34, 46], [56, 40, 54, 63], '#7a2f2f'], 'military' => ['Old office', 'Military Accounts Office', [8, 19, 39, 71], [53, 40, 47, 57], '#3f5a48']];
    $DATA = [];
    foreach ($PC as $k => $c) {
        $f = ROOT . "/img/postcards/$k-back.jpg";
        if (!is_file($f)) continue;
        [$w, $h] = getimagesize($f);
        $DATA["img:$k"] = ['name' => $c[0], 'note' => $c[1], 'ar' => "$w / $h", 'msg' => $c[2], 'addr' => $c[3], 'ink' => $c[4], 'src' => "img/postcards/$k"];
    }
    $pk = isset($DATA[$cur['paper']]) ? $cur['paper'] : array_key_first($DATA); ?>
    <form method=post id=ef><input type=hidden name=a value=letter><input type=hidden name=id id=lid value="<?= e($cur['id']) ?>"><input type=hidden name=paper id=paper value="<?= e($pk) ?>"><input type=hidden name=front id=front value="<?= e(upf($cur['front'] ?? '')) ?>"><input type=hidden name=pfont id=pfont value="<?= e($cur['pfont'] ?? '') ?>"><input type=hidden name=pcolor id=pcolor value="<?= e($cur['pcolor'] ?? '') ?>"><input type=hidden name=body id=body>
        <?php top('Your letter', 'Write freely, then see how it will feel on the page.', '<div class=acts><button class="btn sm wh">' . ic('bookmark', 17) . ' Save draft</button><button class="btn sm" name=keep value=1>' . ic('mail', 17) . ' Keep it unsent</button></div>'); ?>
        <div class=egrid>
            <div class="card ed">
                <div class=eh>
                    <div class=ehl><input name=title id=ttl value="<?= e($cur['title']) ?>" placeholder="Give your letter a title" maxlength=120 aria-label="Letter title">
                        <div class=meta><span class="chipto"><em>To</em><input name=to id=to value="<?= e($cur['to']) ?>" size="<?= max(7, strlen($cur['to'])) ?>" maxlength=40 placeholder=Someone aria-label="Addressed to"></span><label class=kindw><select name=kind id=kind class="tag <?= e($cur['kind']) ?>"><?php foreach (KL as $k => $v) echo "<option value=$k" . ($k == $cur['kind'] ? ' selected' : '') . ">" . e($v) . "</option>"; ?></select></label><small class=mut id=sv>Saved just now</small></div>
                    </div>
                    <div class=ib-row><button type=button class=ib data-c=undo title=Undo><?= ic('undo', 18) ?></button><button type=button class=ib data-c=redo title=Redo><?= ic('redo', 18) ?></button><button type=button class=ib id=clr title="Clear letter"><?= ic('dots', 18) ?></button></div>
                </div>
                <div class=tb><button type=button data-c=bold title=Bold><?= ic('bold', 16) ?></button><button type=button data-c=italic title=Italic><?= ic('italic', 16) ?></button><button type=button data-c=underline title=Underline><?= ic('underline', 16) ?></button><button type=button data-c=insertUnorderedList title=List><?= ic('list', 16) ?></button><i class=dv></i><select id=efnt class=tsel aria-label="Font for selected text" title="Font (applies to selected text, or what you type next)">
                        <option value="" disabled selected>Default</option>
                        <option value=manrope style="font-family:Manrope,sans-serif">Clean sans</option>
                        <option value=caveat style="font-family:Caveat,cursive">Handwriting</option>
                        <option value=kalam style="font-family:Kalam,cursive">Casual hand</option>
                        <option value=dancing style="font-family:'Dancing Script',cursive">Elegant script</option>
                        <option value=lora style="font-family:Lora,serif">Classic serif</option>
                        <option value=elite style="font-family:'Special Elite',monospace">Typewriter</option>
                    </select><select id=esz class=tsel aria-label="Size for selected text" title="Size (applies to selected text, or what you type next)">
                        <option value="">—</option><?php foreach ([12, 14, 16, 18, 20, 24, 28, 32, 40, 48] as $z) echo "<option value=$z>$z</option>"; ?>
                    </select><small class=mut id=wc></small></div>
                <div class=tpl id=tpl role=group aria-label="Letter templates"><small class="mut ico-line"><?= ic('spark', 14) ?> Start from a template</small><button type=button data-t=blank>Blank page</button><button type=button data-t=unsaid>Never said</button><button type=button data-t=thanks>Thank you</button><button type=button data-t=sorry>I’m sorry</button><button type=button data-t=miss>I miss you</button><button type=button data-t=past>To past me</button><button type=button data-t=goodbye>Goodbye</button></div>
                <div class=sheet>
                    <div class=zl aria-hidden=true><span>Opening</span><span>Your words</span><span>Closing</span></div>
                    <div id=ed contenteditable spellcheck=true><?= $body ?></div>
                </div>
                <div class=ef><small class=mut><?= ic('lock', 13) ?> Private and encrypted</small><small class=mut>Autosave on</small></div>
            </div>
            <div class="card beige pvp">
                <div class=between><span><b>Preview your letter</b><br><small class=mut>Postcard: <span id=pn></span></small></span><span class=ibs><button type=button class="btn sm wh pvb" id=expand title="Open full preview"><?= ic('expand', 16) ?> Preview</button></span></div>
                <div class=stage id=stage>
                    <div class=pc id=pc>
                        <div class=pc-inner>
                            <div class="pc-face pc-front"><img id=pcf alt="Postcard front">
                                <div class=pc-cap id=pt></div>
                            </div>
                            <div class="pc-face pc-back"><img id=pcb alt="Postcard back">
                                <div class=pc-msg>
                                    <div class="pc-text hand" id=pb></div>
                                </div>
                                <div class="pc-ad l1 hand"><span class=to-label>To:</span> <span id=pto></span></div>
                                <div class="pc-ad l2 hand" id=prel></div>
                                <div class=pc-mark><span>UNSENT</span><b id=pmd></b></div>
                            </div>
                        </div>
                    </div>
                </div>
                <small class="mut hint">Tap the card to flip it over</small>
                <div class=upl><input type=file id=upf accept="image/png,image/jpeg,image/webp" hidden><button type=button class="btn sm wh" id=upb><?= ic('upload', 16) ?> Use your own front image</button><button type=button class="btn sm wh" id=upx hidden>Remove</button><small class=mut id=upm>PNG, JPG or WebP · up to 8 MB</small></div>
                <div class=pcpick><small class=mut>Choose your postcard</small>
                    <div class=pcchips id=chips><?php foreach ($DATA as $k => $v) echo "<button type=button class='pt" . ($k == $pk ? ' on' : '') . "' data-k='" . e($k) . "' title='" . e($v['note']) . "'><img src='" . e($v['src']) . "-front.jpg' alt=''><span>" . e($v['name']) . "</span></button>"; ?></div>
                </div>
                <div class=pcfmt><small class=mut>Card text</small>
                    <div class=fmtrow><select id=fnt aria-label="Card font">
                            <option value="">Original font</option>
                            <option value=caveat>Handwriting</option>
                            <option value=kalam>Casual hand</option>
                            <option value=dancing>Elegant script</option>
                            <option value=lora>Classic serif</option>
                            <option value=elite>Typewriter</option>
                            <option value=manrope>Clean sans</option>
                        </select>
                        <div class=swa id=swa><button type=button class="cs o" data-c="" title="Original colour"></button><?php foreach (['#1f1f1f', '#2a4a8c', '#2f6b45', '#7a2f2f', '#6b3a7a', '#8a5a2b'] as $c) echo "<button type=button class=cs data-c=\"$c\" style=\"--c:$c\" title=\"$c\"></button>"; ?><label class=cpk title="Pick any colour"><input type=color id=cpk value="#2a4a8c"></label></div>
                    </div><small class="mut hint2">The card shows one paragraph. Click inside a paragraph to choose which one.</small>
                </div>
            </div>
        </div>
    </form>
    <div class=modal id=modal hidden><button type=button class=ib id=cm title=Close><?= ic('close', 18) ?></button>
        <div class="stage big" id=mp></div>
    </div>
    <script>
        window.PC = <?= json_encode($DATA) ?>;
        window.PK = <?= json_encode($pk) ?>;
        window.FR = <?= json_encode(upf($cur['front'] ?? '')) ?>;
        window.REL = <?= json_encode($cur['rel'] ?? '') ?>;
    </script>
