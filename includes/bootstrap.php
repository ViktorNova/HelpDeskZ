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
require_once 'global.php';

if($settings['permalink'] == 1){
	$s_server = $_SERVER['SERVER_NAME'];
	$q_string = $_SERVER['REQUEST_URI'];
	$site_url = str_replace(array('http://','https://'),'',$settings['site_url']);
	$q_string = str_replace($site_url, '', $s_server.$q_string);
	$q_string = trim(filter_var($q_string, FILTER_SANITIZE_URL), '/');
	if(strpos($q_string, '?') !== FALSE)
		$q_string = substr($q_string, 0, strpos($q_string,'?'));
	$q_pieces = explode('/', $q_string);
	$controller = (empty($q_pieces[0])) ? 'home': $q_pieces[0];
	$action = (empty($q_pieces[1])) ? 'index': $q_pieces[1];
	$params = array();
	for($i=2;$i<count($q_pieces);$i++) {
		$params[] = $q_pieces[$i];
	}
	$filename = CONTROLLERS.$controller.'_controller.php';
	
	if (!is_file($filename)){
		$filename = CONTROLLERS.'home_controller.php';
		$controller = 'home';
		$action = '404notfound';
	}
}else{
	if(!isset($input->g['v'])){
		$controller = 'home';
		$action = 'index';
		$filename = CONTROLLERS.'home_controller.php';
	}else{
		$filename = CONTROLLERS.$input->g['v'].'_controller.php';
		if (!is_file($filename)){
			$filename = CONTROLLERS.'home_controller.php';
			$controller = 'home';
			$action = '404notfound';
		}else{
			$controller = $input->g['v'];
			$action = $input->g['action'];
		}
	}
	$action = ($action == '')?'index':$action;
	if(is_array($input->g['param'])){
		foreach($input->g['param'] as $v){
			$params[] = $v;
		}
	}
}

include($filename);
?>