<?php
defined('ROOT') || exit;
/**
 * @var mixed $a
 */
if ($a == 'logout') {
    session_destroy();
    go('signin');
}
