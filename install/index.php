<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.2
 */
error_reporting(E_ALL & ~E_NOTICE);
session_start();
define('HELPDESKZ_PATH', dirname(dirname(__FILE__)).'/');
require_once __DIR__.'/functions.php';
helpdeskz_header();
?>
<p>Welcome to HelpDeskZ v<?php echo HELPDESKZ_VERSION;?>, please select the action that you want to do:</p>

    <div align="center">
        <button onclick="location.href='./install.php';">Install a fresh copy</button>
        <button onclick="location.href='./upgrade.php';">Upgrade my HelpDeskZ</button>
    </div>
<?php
helpdeskz_footer();
?>