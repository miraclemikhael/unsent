<?php
defined('ROOT') || exit;
/**
 * @var mixed $p
 */
if ($p != 'signin' && $p != 'recover') echo '</main></div>';
if (!empty($_SESSION['flash'])) {
    echo '<script>window.FLASH=' . json_encode($_SESSION['flash']) . ';window.FLASH_T=' . json_encode($_SESSION['flash_t'] ?? 'success') . '</script>';
    unset($_SESSION['flash'], $_SESSION['flash_t']);
}
$JS = ['core', 'auth', 'write', 'letters', 'profile', 'password', 'sidebar', 'home', 'admin', 'theme', 'mood', 'templates', 'effects'];
foreach ($JS as $j) echo '<script src="js/' . $j . '.js?v=' . @filemtime(ROOT . "/js/$j.js") . '"></script>';
echo '<script type="module" src="js/motion.js?v=' . @filemtime(ROOT . '/js/motion.js') . '"></script></body></html>';
