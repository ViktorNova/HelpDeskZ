<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
include(INCLUDES.'staff.inc.php');
header("X-FRAME-OPTIONS: DENY");	
if($staff_status != 1){
	$filename = CONTROLLERS.'staff/login_action.php';	
}else{
	if($staff['status'] != 'Enable'){
		staffLogout();
	}
	if(in_array($staff['timezone'], $timezone)){
		date_default_timezone_set($staff['timezone']);
	}	
	if($action == 'index'){
		$action = 'dashboard';	
	}
	$filename = CONTROLLERS.'staff/'.$action.'_action.php';
	if (!is_file($filename)){
		$filename = CONTROLLERS.'home_controller.php';
		$action = '404notfound';
	}
}
include($filename);
exit;
?>