<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
if($params[0] == 'update_password'){
	if(verifyToken('preferences', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];	
	}elseif(empty($input->p['current_password']) || empty($input->p['new_password']) || empty($input->p['new_password2'])){
		$error_msg = $LANG['ONE_REQUIRED_FIELDS_EMPTY'];
	}elseif(sha1($input->p['current_password']) != $staff['password']){
		$error_msg = $LANG['EXISTING_PASSWORD_INCORRECT'];
	}elseif($input->p['new_password'] != $input->p['new_password2']){
		$error_msg = $LANG['NEW_PASSWORDS_DONOT_MATCH'];
	}else{
		$new_password = sha1($input->p['new_password']);
		$data = array('password' => $new_password);
		$db->update(TABLE_PREFIX."staff", $data, "id={$staff['id']}");
		$_SESSION['staff']['password'] = $new_password;
		header('location: '.getUrl($controller,$action, 'password_updated#ctab2'));
		exit;	
	}
}elseif($params[0] == 'update_profile'){
	if(verifyToken('preferences', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];
	}elseif(empty($input->p['fullname']) || empty($input->p['email'])){
		$error_msg = $LANG['ONE_REQUIRED_FIELDS_EMPTY'];
	}elseif(validateEmail($input->p['email']) !== TRUE){
		$error_msg = $LANG['ENTER_A_VALID_EMAIL'];
	}else{
		$chk = 0;	
		if($input->p['email'] != $staff['email']){
			$chk = $db->fetchOne("SELECT COUNT(id) AS NUM FROM ".TABLE_PREFIX."staff WHERE email='".$db->real_escape_string($input->p['email'])."'");
		}
		if($chk != 0){
			$error_msg = $LANG['EMAIL_ASSOCIATED_OTHER_ACCOUNT'];	
		}else{
			$data = array(
							'fullname' => $input->p['fullname'],
							'email' => $input->p['email'],
							'signature' => $input->p['signature'],
							'timezone' => (in_array($input->p['timezone'], $timezone)?$input->p['timezone']:''),
                            'newticket_notification' => ($input->p['newticket_notification'] == 1?1:0),
						);
			$db->update(TABLE_PREFIX."staff", $data, "id={$staff['id']}");
			header('location: '.getUrl($controller,$action, 'profile_updated'));
			exit;
		}
	}
}

$template_vars['error_msg'] = $error_msg;
$template_vars['timezone'] = $timezone;
$template = $twig->loadTemplate('preferences.html');
echo $template->render($template_vars);
$db->close();
exit;
?>