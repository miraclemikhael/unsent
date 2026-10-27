<?php
defined('ROOT') || exit;
/**
 * @var mixed $a
 */
if ($a == 'logout') {
    session_destroy();
    redirect_to_page('signin');
}
