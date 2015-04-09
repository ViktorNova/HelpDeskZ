<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
require_once 'global.php';

if($settings['permalink'] == 1){
    $q_string = (isset($_SERVER['PATH_INFO'])?$_SERVER['PATH_INFO']:'');
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