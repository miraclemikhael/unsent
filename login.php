<?php
defined('ROOT') || exit;
/**
 * @var mixed $err
 */
    page_head('Sign in');
    $new = isset($_GET['new']); ?>
    <div class=split>
        <section class=left><img class=slogo src=img/logo.png alt=Unsent>
            <div class=tl><small class=red>YOUR QUIET WRITING SPACE</small>
                <h2 class=hand>Write what you can’t say. Keep what you’re not ready to send.</h2>
                <p class=mut>A private place to name the feeling, write the letter, and let the words become a little lighter.</p>
            </div><img class=art src=img/signin.png alt=""><small class="mut ico-line"><?= icon('lock', 14) ?> Private by design. Your letters stay yours.</small>
        </section>
        <section class=right>
            <div class=hl><small class=mut>Need a little help?</small><b class=ico-line><?= icon('help', 16) ?> Support</b></div>
            <form method=post class=box><input type=hidden name=a value=<?= $new ? 'register' : 'login' ?>>
                <h1><?= $new ? 'Create your space.' : 'Welcome back,<br>You’ve been missed.' ?></h1>
                <p class=mut>Sign in to return to your letters and your private space.</p>
                <?php if ($err) echo "<p class=err>$err</p>";
                if ($new) echo '<label>Your name<input name=name value="' . esc($_POST['name'] ?? '') . '" required></label>'; ?>
                <label>Email or username<input name=id value="<?= esc($_POST['id'] ?? '') ?>" autocomplete=username autocapitalize=off spellcheck=false required></label><label>Password<input type=password name=pw autocomplete="<?= $new ? 'new-password' : 'current-password' ?>" required></label>
                <?php if ($new) echo '<label>Confirm password<input type=password name=pw2 required></label>';
                if (!$new) echo '<div class=between><label class=chk><input type=checkbox checked> Remember me</label><a href="?p=recover"><b>Forgot password?</b></a></div>'; ?>
                <button class=btn><?= icon('arrow', 17) ?> <?= $new ? 'Create account' : 'Sign in' ?></button>
                
                <p class="mut c"><?= $new ? 'Have an account? <a href="?p=signin"><b>Sign in</b></a>' : 'No account yet? <a href="?p=signin&new=1"><b>Create one</b></a>' ?></p>
            </form><small class="foot mut">By continuing, you agree to the Terms and Privacy Policy.</small>
        </section>
    </div>
