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
if($params[1] == 'getTemplateForm'){
	$email = $db->fetchRow("SELECT *, COUNT(id) AS total FROM ".TABLE_PREFIX."emails WHERE id='".$db->real_escape_string($params[2])."'");
	if($email['total'] == 0){
		die($LANG['ERROR_RETRIEVING_DATA']);	
	}
	$form_action = getUrl($controller,$action,array('email_template','update_template'));
	$template_vars['form_action'] = $form_action;
	$template_vars['email'] = $email;
	$template = $twig->loadTemplate('admin_email_template_form.html');
	echo $template->render($template_vars);
	$db->close();
	exit;
}elseif($params[1] == 'update_template'){
	if(verifyToken('emails', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];		
	}elseif($input->p['subject'] == '' || $input->p['message'] == ''){
		$error_msg = $LANG['ENTER_DEPARTMENT_NAME'];
	}else{
		$data = array(
						'subject' => $input->p['subject'],
						'message' => $input->p['message'],
					);
		$db->update(TABLE_PREFIX."emails", $data, "id='".$db->real_escape_string($input->p['template_id'])."'");
		header('location:'.getUrl($controller,$action,array('email_template','template_updated')));
		exit;
	}
}

$q = $db->query("SELECT * FROM ".TABLE_PREFIX."emails ORDER BY orderlist ASC");	
while($r = $db->fetch_array($q)){
	$emails[] = $r;
}
$template_vars['emails'] = $emails;
$template_vars['error_msg'] = $error_msg;
$template = $twig->loadTemplate('admin_email_template.html');
echo $template->render($template_vars);
$db->close();
exit;	
?>