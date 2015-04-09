<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
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