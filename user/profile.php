<?php
defined('ROOT') || exit;
/**
 * @var mixed $me
 */
    head('Profile');
    shell('profile'); ?>
    <form method=post><input type=hidden name=a value=profile><?php top('Your space', 'Manage your profile, privacy, and the way Unsent feels.', '<button class="btn sm">' . ic('check', 16) . ' Save changes</button>'); ?>
        <input type=hidden name=tab id=tabin value=profile>
        <div class=pgrid>
            <div class="beige card tabs" id=ptabs><?php foreach ([['profile', 'user', 'Profile'], ['privacy', 'shield', 'Privacy'], ['security', 'lock', 'Password &amp; security'], ['appearance', 'palette', 'Appearance']] as $t) echo '<a href="#' . $t[0] . '" data-t=' . $t[0] . ($t[0] == 'profile' ? ' class=on' : '') . '>' . ic($t[1], 17) . ' ' . $t[2] . ' <span>' . ic('chevr', 16) . '</span></a>'; ?><button form=lo class=lo><?= ic('out', 17) ?> Log out</button></div>
            <div id=ppanels>
                <div data-p=profile>
                    <div class=card>
                        <div class=between>
                            <h3>Profile details</h3><small class=mut>* Required</small>
                        </div><small class=mut>How you appear in your private writing space.</small>
                        <div class=ph><?= av($me, 'big') ?><span><b>Profile photo</b><br><small class=mut id=avm>Choose something that feels like you. JPG, PNG or WebP, up to 5 MB.</small></span><input type=file id=avf accept="image/png,image/jpeg,image/webp" hidden><button type=button class="btn sm wh" id=avb><?= ic('camera', 16) ?> Change photo</button><button type=button class="btn sm wh" id=avx<?= upf($me['avatar'] ?? '') ? '' : ' hidden' ?>>Remove</button></div>
                        <div class=two><label>Your name<input name=name value="<?= e($me['name']) ?>"></label><label>Username<input name=username value="<?= e($me['username']) ?>"></label><label>Email<input name=email value="<?= e($me['email']) ?>"></label><label>Phone number<input name=phone value="<?= e($me['phone'] ?? '') ?>"></label></div>
                    </div>
                </div>
                <div data-p=privacy hidden>
                    <div class=card>
                        <div class=between><span><b>Private by default</b><br><small class=mut>Only you can open your letters</small></span><label class=sw><input type=checkbox name=private <?= ($me['private'] ?? 1) ? 'checked' : '' ?>><i></i></label></div>
                        <div class=note><?= ic('check', 15) ?> App previews hide letter content automatically.</div>
                    </div>
                    <div class="card danger" style="margin-top:20px"><span><b>Delete your account</b><br><small class=mut>Permanently remove your profile and every unsent letter.</small></span><button form=da class="btn sm rd"><?= ic('trash', 16) ?> Delete account</button></div>
                </div>
                <div data-p=security hidden>
                    <div class=card>
                        <div class=between><span><b>Password &amp; security</b><br><small class=mut>Last changed 3 months ago</small></span><a class="btn sm wh" href="?p=recover">Change</a></div>
                        <div class=note><?= ic('lock', 15) ?> Your account and private letters are protected.</div>
                    </div>
                </div>
                <div data-p=appearance hidden>
                    <div class=card>
                        <h3>Appearance</h3><small class=mut>Make Unsent feel the way you like.</small>
                        <div class=prow><b>Theme</b>
                            <div class=themes><?php foreach ([['light', 'Light'], ['dark', 'Dark'], ['auto', 'Match my device']] as $t) echo '<label class=thm><input type=radio name=a_theme value=' . $t[0] . (($me['a_theme'] ?? 'light') == $t[0] ? ' checked' : '') . '><span>' . $t[1] . '</span></label>'; ?></div>
                        </div>
                        <div class="between prow"><span><b>Reduce motion</b><br><small class=mut>Turn off card flips, hover lifts and other animation</small></span><label class=sw><input type=checkbox name=a_calm <?= !empty($me['a_calm']) ? 'checked' : '' ?>><i></i></label></div>
                        <div class=note>Theme changes preview right away. Press Save changes to keep them.</div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <form id=lo method=post><input type=hidden name=a value=logout></form>
    <form id=da method=post><input type=hidden name=a value=delacct></form>
