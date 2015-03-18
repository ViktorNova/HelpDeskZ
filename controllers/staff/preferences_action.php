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