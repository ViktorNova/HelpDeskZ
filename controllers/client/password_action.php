<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
	if($params[0] == 'save'){
		if(verifyToken('password', $input->p['csrfhash']) !== true){
			$error_msg = $LANG['CSRF_ERROR'];	
		}elseif(empty($input->p['current_password']) || empty($input->p['new_password']) || empty($input->p['new_password2'])){
			$error_msg = $LANG['ONE_REQUIRED_FIELD_EMPTY'];
		}else{
			if(sha1($input->p['current_password']) != $user['password']){
				$error_msg = $LANG['EXISTING_PASSWORD_INCORRECT'];
			}elseif($input->p['new_password'] != $input->p['new_password2']){
				$error_msg = $LANG['NEW_PASSWORDS_DO_NOT_MATCH'];
			}else{
				$new_password = sha1($input->p['new_password']);
				$data = array('password' => $new_password);
				$db->update(TABLE_PREFIX."users", $data, "id={$user['id']}");
				$_SESSION['user']['password'] = $new_password;
				header('location: '.getUrl('user_account','password', array('saved')));
				exit;	
			}
		}
	}
	$template_vars['user'] = $user;
	$template_vars['error_msg'] = $error_msg;
	$template = $twig->loadTemplate('user_password.html');
	echo $template->render($template_vars);
	$db->close();
	exit;
?>