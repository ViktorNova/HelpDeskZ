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