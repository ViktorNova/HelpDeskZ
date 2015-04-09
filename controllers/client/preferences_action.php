<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
	if($params[0] == 'save'){
		if(verifyToken('preferences', $input->p['csrfhash']) !== true){
			$error_msg = $LANG['CSRF_ERROR'];	
		}else{
			$timezone_user = '';
			if(!empty($input->p['timezone'])){
				if(in_array($input->p['timezone'],$timezone)){
					$timezone_user = $input->p['timezone'];	
				}
			}
			$data = array('timezone' => $timezone_user);
			$db->update(TABLE_PREFIX."users", $data, "id={$user['id']}");
			header('location: '.getUrl('user_account','preferences', array('saved')));
			exit;
		}
	}
	$template_vars['timezone'] = $timezone;
	$template_vars['user'] = $user;
	$template_vars['error_msg'] = $error_msg;
	$template = $twig->loadTemplate('user_preferences.html');
	echo $template->render($template_vars);
	$db->close();
	exit;
?>