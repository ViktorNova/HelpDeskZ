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
if($params[1] == 'getStaffForm'){
	if(is_numeric($params[2]) && $params[2] != 0){
		$agent = $db->fetchRow("SELECT *, COUNT(id) AS total FROM ".TABLE_PREFIX."staff WHERE id=".$db->real_escape_string($params[2]));
		$agent_department = unserialize($agent['department']);
		$agent_department = (is_array($agent_department)?$agent_department:array());
		if($agent['total'] == 0){
			die($LANG['ERROR_RETRIEVING_DATA']);	
		}
		$form_action = getUrl($controller,$action,array('staff','update_account'));
	}else{
		$form_action = getUrl($controller,$action,array('staff','add_account'));
		$agent_department = array();
	}
	$q = $db->query("SELECT id, name FROM ".TABLE_PREFIX."departments ORDER BY dep_order ASC");
	while($r = $db->fetch_array($q)){
		$departments[] = $r;	
	}
	$template_vars['departments'] = $departments;
	$template_vars['form_action'] = $form_action;
	$template_vars['agent'] = $agent;
	$template_vars['agent_department'] = $agent_department;
	$template_vars['timezone'] = $timezone;
	$template = $twig->loadTemplate('admin_staff_form.html');
	echo $template->render($template_vars);
	$db->close();
	exit;
}elseif($params[1] == 'update_account'){
	if(verifyToken('staff_account', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];
	}elseif(!is_numeric($input->p['staff_id'])){
		$error_msg = $LANG['INVALID_ACCOUNT'];
	}elseif($input->p['fullname'] == '' || $input->p['username'] == '' || $input->p['email'] == ''){
		$error_msg = $LANG['ONE_REQUIRED_FIELDS_EMPTY'];
	}elseif(validateEmail($input->p['email']) !== TRUE){
		$error_msg = $LANG['ENTER_A_VALID_EMAIL'];
	}elseif($input->p['password'] != '' && $input->p['password'] != $input->p['password2']){
		$error_msg = $LANG['PASSWORDS_DONOT_MATCH'];
	}elseif($input->p['password'] != '' && strlen($input->p['password']) < 6){
		$error_msg = $LANG['ENTER_PASSWORD_6_CHAR_MIN'];
	}elseif($staff['id'] == $input->p['staff_id'] && $input->p['status'] != 'Enable'){
		$error_msg = $LANG['YOU_CANNOT_LOCK_YOUR_ACCOUNT'];
	}else{
		$usr = $db->fetchRow("SELECT COUNT(id) AS total, id, username, email, password FROM ".TABLE_PREFIX."staff WHERE id=".$db->real_escape_string($input->p['staff_id']));
		if($usr['total'] == 0){
			$error_msg = $LANG['INVALID_ACCOUNT'];
		}else{
			if($usr['username'] != $input->p['username']){
				$chk = $db->fetchOne("SELECT COUNT(id) AS total FROM ".TABLE_PREFIX."staff WHERE username='".$db->real_escape_string($input->p['username'])."'");
				if($chk != 0){
					$error_msg = $LANG['USERNAME_TAKEN'];
				}
			}
			if($usr['email'] != $input->p['email']){
				$chk = $db->fetchOne("SELECT COUNT(id) AS total FROM ".TABLE_PREFIX."staff WHERE email='".$db->real_escape_string($input->p['email'])."'");
				if($chk != 0){
					$error_msg = $LANG['EMAIL_TAKEN'];
				}
			}
			if($error_msg == ''){
				if(is_array($input->p['department'])){
					$q = $db->query("SELECT id FROM ".TABLE_PREFIX."departments");
					while($r = $db->fetch_array($q)){
						if(in_array($r['id'], $input->p['department'])){
							$department_list[] = $r['id'];
						}
					}
				}
				$departmentlist = is_array($department_list)?$department_list:array();
				$departmentlist = serialize($departmentlist);
				if($input->p['password'] != ''){
					$password = sha1($input->p['password']);
				}else{
					$password = $usr['password'];	
				}
				$data = array(
								'username' => $input->p['username'],
								'fullname' => $input->p['fullname'],
								'email' => $input->p['email'],
								'timezone' => (in_array($input->p['timezone'],$timezone)?$input->p['timezone']:''),
								'admin' => ($input->p['admin']==1?1:0),
								'status' => ($input->p['status']=='Enable'?'Enable':'Disable'),
								'signature' => $input->p['signature'],
								'department' => $departmentlist,
								'password' => $password,
							);
				$db->update(TABLE_PREFIX."staff", $data, "id=".$db->real_escape_string($input->p['staff_id']));
				if($input->p['password'] != '' && $usr['id'] == $staff['id']){
					$cookie_time = time() + (60*60*8);
					$data = array('id' => $staff['id'], 'username' => $staff['username'], 'password' => $password, 'expires' => $cookie_time);
					$data = serialize($data);
					$data = encrypt($data);
					setcookie('stfhash', $data, $cookie_time, '/');
					$_SESSION['staff']['id'] = $staff['id'];
					$_SESSION['staff']['username'] = $staff['username'];
					$_SESSION['staff']['password'] = $password;

				}
				header('location:'.getUrl($controller,$action,array('staff','account_updated')));
				exit;
			}
		}
	}
}elseif($params[1] == 'add_account'){
	if(verifyToken('staff_account', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];
	}elseif($input->p['fullname'] == '' || $input->p['username'] == '' || $input->p['email'] == '' || $input->p['password'] == ''){
		$error_msg = $LANG['ONE_REQUIRED_FIELDS_EMPTY'];
	}elseif(validateEmail($input->p['email']) !== TRUE){
		$error_msg = $LANG['ENTER_A_VALID_EMAIL'];
	}elseif($input->p['password'] != $input->p['password2']){
		$error_msg = $LANG['PASSWORDS_DONOT_MATCH'];
	}elseif(strlen($input->p['password']) < 6){
		$error_msg = $LANG['ENTER_PASSWORD_6_CHAR_MIN'];
	}else{
		$chk = $db->fetchOne("SELECT COUNT(id) AS total FROM ".TABLE_PREFIX."staff WHERE username='".$db->real_escape_string($input->p['username'])."'");
		if($chk != 0){
			$error_msg = $LANG['USERNAME_TAKEN'];
		}
		$chk = $db->fetchOne("SELECT COUNT(id) AS total FROM ".TABLE_PREFIX."staff WHERE email='".$db->real_escape_string($input->p['email'])."'");
		if($chk != 0){
			$error_msg = $LANG['EMAIL_TAKEN'];
		}

		if($error_msg == ''){
			if(is_array($input->p['department'])){
				$q = $db->query("SELECT id FROM ".TABLE_PREFIX."departments");
				while($r = $db->fetch_array($q)){
					if(in_array($r['id'], $input->p['department'])){
						$department_list[] = $r['id'];
					}
				}
			}
			$departmentlist = is_array($department_list)?$department_list:array();
			$departmentlist = serialize($departmentlist);
			$password = sha1($input->p['password']);
			$data = array(
							'username' => $input->p['username'],
							'fullname' => $input->p['fullname'],
							'email' => $input->p['email'],
							'timezone' => (in_array($input->p['timezone'],$timezone)?$input->p['timezone']:''),
							'admin' => ($input->p['admin']==1?1:0),
							'status' => ($input->p['status']=='Enable'?'Enable':'Disable'),
							'signature' => $input->p['signature'],
							'department' => $departmentlist,
							'password' => $password,
						);
			$db->insert(TABLE_PREFIX."staff", $data);
			header('location:'.getUrl($controller,$action,array('staff','account_created')));
			exit;
		}
	}
}elseif($params[1] == 'remove_account'){
	if($staff['id'] == $params[2]){
		$error_msg = $LANG['YOU_CANNOT_REMOVE_YOURSELF'];
	}elseif(is_numeric($params[2])){
		$db->delete(TABLE_PREFIX."staff", "id=".$db->real_escape_string($params[2]));
		$db->delete(TABLE_PREFIX."login_log", "staff_id=".$db->real_escape_string($params[2]));
	}
		header('location:'.getUrl($controller,$action,array('staff','account_removed')));
		exit;
}
$order_list = array('id', 'username', 'fullname', 'email','admin','login','status');
$orderby = (in_array($params[1],$order_list)?$params[1]:'id');
$sortby = ($params[2] == 'desc'?'desc':'asc');
$q = $db->query("SELECT * FROM ".TABLE_PREFIX."staff ORDER BY {$orderby} {$sortby}");	
while($r = $db->fetch_array($q)){
	$accounts[] = $r;	
}
$template_vars['accounts'] = $accounts;
$template_vars['error_msg'] = $error_msg;
$template = $twig->loadTemplate('admin_staff.html');
echo $template->render($template_vars);
$db->close();
exit;
?>