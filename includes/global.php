<?php
/*******************************************************************************
*  Title: Help Desk Software HelpDeskZ
*  Version: 1.0 from 17th March 2015
*  Author: Evolution Script S.A.C.
*  Website: http://www.helpdeskz.com
********************************************************************************
*  COPYRIGHT AND TRADEMARK NOTICE
*  Copyright 2015 Evolution Script S.A.C.. All Rights Reserved.
*  HelpDeskZ is a registered trademark of Evolution Script S.A.C..

*  The HelpDeskZ may be used and modified free of charge by anyone
*  AS LONG AS COPYRIGHT NOTICES AND ALL THE COMMENTS REMAIN INTACT.
*  By using this code you agree to indemnify Evolution Script S.A.C. from any
*  liability that might arise from it's use.

*  Selling the code for this program, in part or full, without prior
*  written consent is expressly forbidden.

*  Using this code, in part or full, to create derivate work,
*  new scripts or products is expressly forbidden. Obtain permission
*  before redistributing this software over the Internet or in
*  any other medium. In all cases copyright and header must remain intact.
*  This Copyright is in full effect in any country that has International
*  Trade Agreements with the United States of America

*  Removing any of the copyright notices without purchasing a license
*  is expressly forbidden. To remove HelpDeskZ copyright notice you must purchase
*  a license for this script. For more information on how to obtain
*  a license please visit the page below:
*  https://www.helpdeskz.com/contact
*******************************************************************************/
session_start();
error_reporting(E_ALL & ~E_NOTICE);	
define('ROOTPATH', dirname(dirname(__FILE__)).'/');
define('INCLUDES', ROOTPATH . 'includes/');
define('CONTROLLERS', ROOTPATH . 'controllers/');
define('TEMPLATES', ROOTPATH . 'views/');
define('CLIENT_TEMPLATE', TEMPLATES . 'client/');
define('STAFF_TEMPLATE', TEMPLATES . 'staff/');
define('ADMIN_TEMPLATE', TEMPLATES . 'admin/');
define('UPLOAD_DIR', ROOTPATH . 'uploads/');


require_once INCLUDES.'classes/classRegistry.php';
require_once INCLUDES.'classes/classInput.php';
require_once INCLUDES.'classes/classMailer.php';
require_once INCLUDES.'functions.php';
require_once INCLUDES.'timezone.inc.php';
// DB Connection
$helpdeskz = new Registry();
$input = new Input_Cleaner();
if($helpdeskz->config['Database']['type'] == 'mysqli'){
	require_once INCLUDES.'classes/classMysqli.php';	
	$db = new MySQLIDB();
}else{
	require_once INCLUDES.'classes/classMysql.php';	
	$db = new MySQLDB();
}
$db->connect($helpdeskz->config['Database']['dbname'], $helpdeskz->config['Database']['servername'], $helpdeskz->config['Database']['username'], $helpdeskz->config['Database']['password'], $helpdeskz->config['Database']['tableprefix']);

$settings = array();
$q = $db->query("SELECT * FROM ".TABLE_PREFIX."settings");
while($r = $db->fetch_array($q)){
	$settings[$r['field']] = $r['value'];
}
if(in_array($settings['timezone'], $timezone)){
	date_default_timezone_set($settings['timezone']);
}