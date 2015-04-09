<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
include(INCLUDES.'helpdesk.inc.php');
$template_vars = array();
if($action == 'submit'){
	if(verifyToken('lost_password', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];	
	}elseif(validateEmail($input->p['email']) !== TRUE){
		$error_msg = $LANG['INVALID_EMAIL_ADDRESS'];
	}elseif($settings['use_captcha']){
		if(strtoupper($input->p['captcha']) != $_SESSION['captcha']){
			$error_msg = $LANG['INVALID_CAPTCHA_CODE'];
			unset($_SESSION['captcha']);		
		}
	}
	
	
	if(!isset($error_msg)){
		$user = $db->fetchRow("SELECT COUNT(id) AS total, id, fullname, email FROM ".TABLE_PREFIX."users WHERE email='".$db->real_escape_string($input->p['email'])."'");
		if($user['total'] == 0){
			$error_msg = $LANG['EMAIL_WAS_NOT_FOUND'];	
		}else{
			$new_password = md5($user['id'].$user['email'].time());
			$new_password = substr($new_password, 3, 7);
						/* Mailer */
						$data_mail = array(
						'id' => 'lost_password',
						'to' => $user['fullname'],
						'to_mail' => $user['email'],
						'vars' => array('%client_name%' => $user['fullname'], '%client_email%' => $user['email'], '%client_password%' => $new_password),
						);
						$mailer = new Mailer($data_mail);
			$db->query("UPDATE ".TABLE_PREFIX."users SET password='".sha1($new_password)."' WHERE id={$user['id']}");
			header('location: '.getUrl('lost_password','confirmation'));
			exit;
		}
	}
}
$template_vars['error_msg'] = $error_msg;
$template = $twig->loadTemplate('lost_password.html');
echo $template->render($template_vars);
$db->close();
exit;
?>