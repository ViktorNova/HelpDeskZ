<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
$getvar = $_SERVER['QUERY_STRING'];
if($params[1] == 'GetUsersForm'){
	if(is_numeric($params[2]) && $params[2] != 0){
		$users = $db->fetchRow("SELECT *, COUNT(id) as total FROM ".TABLE_PREFIX."users WHERE id=".$db->real_escape_string($params[2]));
		if($users['total'] == 0){
			die($LANG['ERROR_RETRIEVING_DATA']);	
		}
		$form_action = getUrl($controller,$action,array('manage','editUser'));
	}else{
		$form_action = getUrl($controller,$action,array('manage','addUser'));
	}
	$template_vars['users'] = $users;
	$template_vars['form_action'] = $form_action;
	$template_vars['timezone'] = $timezone;
	$template = $twig->loadTemplate('form_users.html');
	echo $template->render($template_vars);
	$db->close();
	exit;
}elseif($params[1] == 'editUser'){
	if(verifyToken('users', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];	
	}elseif($input->p['fullname'] == ''){
		$error_msg = $LANG['ENTER_FULLNAME'];
	}elseif(validateEmail($input->p['email']) !== true){
		$error_msg = $LANG['ENTER_A_VALID_EMAIL'];
	}elseif(!is_numeric($input->p['userid'])){
		$error_msg = $LANG['INVALID_ID'];
	}elseif($input->p['password'] != '' && strlen($input->p['password']) < 6){
		$error_msg = $LANG['ENTER_PASSWORD_6_CHAR_MIN'];
	}else{
		$chk = $db->fetchOne("SELECT COUNT(id) AS NUM FROM ".TABLE_PREFIX."users WHERE email='".$db->real_escape_string($input->p['email'])."' AND id!='".$db->real_escape_string($input->p['userid'])."'");
		if($chk != 0){
			$error_msg = $LANG['EMAIL_ASSOCIATED_OTHER_ACCOUNT'];	
		}else{
			if(in_array($input->p['timezone'],$timezone)){
				$timezone_user = $input->p['timezone'];	
			}else{
				$timezone_user = '';	
			}
			$data = array('fullname' => $input->p['fullname'],
							'email' => $input->p['email'],
							'timezone' => $timezone_user,
							);
			if($input->p['password'] != ''){
				$data2 = array('password' => sha1($input->p['password']));
				$data = array_merge($data, $data2);	
			}
			$db->update(TABLE_PREFIX."users", $data, "id=".$db->real_escape_string($input->p['userid']));
			header('location: '.getUrl($controller, $action, array('manage'), $getvar));
			exit;
		}
	}
}elseif($params[1] == 'addUser'){
	if(verifyToken('users', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];	
	}elseif($input->p['fullname'] == ''){
		$error_msg = $LANG['ENTER_FULLNAME'];
	}elseif(validateEmail($input->p['email']) !== true){
		$error_msg = $LANG['ENTER_A_VALID_EMAIL'];
	}elseif(strlen($input->p['password']) < 6){
		$error_msg = $LANG['ENTER_PASSWORD_6_CHAR_MIN'];
	}else{
		$chk = $db->fetchOne("SELECT COUNT(id) AS NUM FROM ".TABLE_PREFIX."users WHERE email='".$db->real_escape_string($input->p['email'])."'");
		if($chk != 0){
			$error_msg = $LANG['EMAIL_ASSOCIATED_OTHER_ACCOUNT'];	
		}else{
			if(in_array($input->p['timezone'],$timezone)){
				$timezone_user = $input->p['timezone'];	
			}else{
				$timezone_user = '';	
			}
			$data = array('fullname' => $input->p['fullname'],
							'email' => $input->p['email'],
							'timezone' => $timezone_user,
							'password' => sha1($input->p['password']),
							);
			$db->insert(TABLE_PREFIX."users", $data);
			header('location: '.getUrl($controller, $action, array('manage'), $getvar));
			exit;
		}
	}
}
if($params[1] == 'page'){
	$page = (!is_numeric($params[2])?1:$params[2]);
}else{
	$page = 1;	
}
$order_list = array('id', 'fullname', 'email');
$orderby = (in_array($params[3],$order_list)?$params[3]:'id');
$sortby = ($params[4] == 'asc'?'asc':'desc');

if($input->p['do'] == 'update'){
	if(verifyToken('users', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];	
	}elseif(!is_array($input->p['user_id'])){
		$error_msg = $LANG['NO_SELECT_TICKET'];	
	}else{
		foreach($input->p['user_id'] as $k){
			if(is_numeric($k)){
				$user_id = $db->real_escape_string($k);
				if($input->p['remove'] == 1){
					$db->delete(TABLE_PREFIX."users", "id='$user_id'");	
				}else{
					if($input->p['suspend'] == 1){
						$db->update(TABLE_PREFIX."users", array("status" => 1), "id=$user_id");	
					}elseif($input->p['suspend'] == 0){
						$db->update(TABLE_PREFIX."users", array("status" => 0), "id=$user_id");		
					}
				}
			}
		}
		header('location: '.getUrl($controller,$action,array('manage','page',$page,$orderby,$sortby),$getvar));
		exit;
	}
}

$max_results = $settings['page_size'];
$count = $db->fetchOne("SELECT COUNT(*) AS NUM FROM ".TABLE_PREFIX."users");
$total_pages = ceil($count/$max_results);	
$page = ($page>$total_pages?$total_pages:$page);
$from = ($max_results*$page) - $max_results;
$q = $db->query("SELECT * FROM ".TABLE_PREFIX."users {$whereq} ORDER BY {$orderby} {$sortby} LIMIT $from, $max_results");
while($r = $db->fetch_array($q)){
	$users[] = $r;	
}
$template_vars['users'] = $users;
$template_vars['orderby'] = $orderby;
$template_vars['sortby'] = $sortby;
$template_vars['page'] = $page;
$template_vars['total_pages'] = $total_pages;
$template_vars['getvar'] = $getvar;
$template_vars['error_msg'] = $error_msg;
$template = $twig->loadTemplate('users_manage.html');
echo $template->render($template_vars);
$db->close();
exit;
?>