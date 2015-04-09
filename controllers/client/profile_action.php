<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
	$salutation = array('','Mr.','Ms.','Mrs.','Dr.');
	if($params[0] == 'save'){
		if(verifyToken('profile', $input->p['csrfhash']) !== true){
			$error_msg = $LANG['CSRF_ERROR'];	
		}elseif(empty($input->p['fullname']) || empty($input->p['email'])){
			$error_msg = $LANG['ONE_REQUIRED_FIELD_EMPTY'];
		}elseif(validateEmail($input->p['email']) !== TRUE){
			$error_msg = $LANG['INVALID_EMAIL_ADDRESS'];
		}else{
			$chk = 0;	
			if($input->p['email'] != $user['email']){
				$chk = $db->fetchOne("SELECT COUNT(id) AS NUM FROM ".TABLE_PREFIX."users WHERE email='".$db->real_escape_string($input->p['email'])."'");
			}
			if($chk != 0){
				$error_msg = $LANG['EMAIL_ASSOCIATED_OTHER_ACCOUNT'];	
			}else{
				$salutation_user = (array_key_exists($input->p['salutation'],$salutation)?$input->p['salutation']:0);
				$email_user = $input->p['email'];
				if($email_user != $user['email']){
					$_SESSION['user']['email'] = $email_user;
				}
				$data = array('salutation' => $salutation_user, 'email' => $email_user, 'fullname' => $input->p['fullname']);
				$db->update(TABLE_PREFIX."users", $data, "id={$user['id']}");
				header('location: '.getUrl('user_account','profile', array('saved')));
				exit;	
			}
		}
	}
	$template_vars['salutation'] = $salutation;
	$template_vars['user'] = $user;
	$template_vars['error_msg'] = $error_msg;
	$template = $twig->loadTemplate('user_profile.html');
	echo $template->render($template_vars);
	$db->close();
	exit;
?>