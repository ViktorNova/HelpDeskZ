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
    /* @since 1.0.2 */
    $uri = parse_url($_SERVER['REQUEST_URI']);
    $query = isset($uri['query']) ? $uri['query'] : '';
    $uri = isset($uri['path']) ? $uri['path'] : '';
    if (isset($_SERVER['SCRIPT_NAME'][0]))
    {
        if (strpos($uri, $_SERVER['SCRIPT_NAME']) === 0)
        {
            $uri = (string) substr($uri, strlen($_SERVER['SCRIPT_NAME']));
        }
        elseif (strpos($uri, dirname($_SERVER['SCRIPT_NAME'])) === 0)
        {
            $uri = (string) substr($uri, strlen(dirname($_SERVER['SCRIPT_NAME'])));
        }
    }
    $uri = trim(filter_var($uri, FILTER_SANITIZE_URL), '/');
    $q_pieces = explode('/', $uri);

    /* @since 1.0.0 */
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