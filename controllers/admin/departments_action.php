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
if($params[1] == 'getDepartmentForm'){
	if(is_numeric($params[2]) && $params[2] != 0){
		$department = $db->fetchRow("SELECT *, COUNT(id) AS total FROM ".TABLE_PREFIX."departments WHERE id=".$db->real_escape_string($params[2]));
		if($department['total'] == 0){
			die($LANG['ERROR_RETRIEVING_DATA']);	
		}
		$dep_order = $department['dep_order'];
		$form_action = getUrl($controller,$action,array('departments','update_department'));
	}else{
		$dep_order = $db->fetchOne("SELECT dep_order FROM ".TABLE_PREFIX."departments ORDER BY dep_order DESC LIMIT 1")+1;
		$form_action = getUrl($controller,$action,array('departments','add_department'));
	}
	$template_vars['form_action'] = $form_action;
	$template_vars['dep_order'] = $dep_order;
	$template_vars['department'] = $department;
	$template = $twig->loadTemplate('admin_departments_form.html');
	echo $template->render($template_vars);
	$db->close();
	exit;
}elseif($params[1] == 'update_department'){
	if(verifyToken('departments', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];		
	}elseif($input->p['name'] == ''){
		$error_msg = $LANG['ENTER_DEPARTMENT_NAME'];
	}else{
		if($input->p['autoassign'] == 1){
			$db->query("UPDATE ".TABLE_PREFIX."departments SET autoassign=0");
		}
		$data = array(
						'name' => $input->p['name'],
						'dep_order' => (!is_numeric($input->p['dep_order'])?1:$input->p['dep_order']),
						'type' => ($input->p['type'] == 1?1:0),
						'autoassign' => ($input->p['autoassign'] == 1?1:0),
					);
		$db->update(TABLE_PREFIX."departments", $data, "id=".$db->real_escape_string($input->p['department_id']));
		header('location:'.getUrl($controller,$action,array('departments','department_updated')));
		exit;
	}
}elseif($params[1] == 'add_department'){
	if(verifyToken('departments', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];		
	}elseif($input->p['name'] == ''){
		$error_msg = $LANG['ENTER_DEPARTMENT_NAME'];
	}else{
		if($input->p['autoassign'] == 1){
			$db->query("UPDATE ".TABLE_PREFIX."departments SET autoassign=0");
		}
		$data = array(
						'name' => $input->p['name'],
						'dep_order' => (!is_numeric($input->p['dep_order'])?1:$input->p['dep_order']),
						'type' => ($input->p['type'] == 1?1:0),
						'autoassign' => ($input->p['autoassign'] == 1?1:0),
					);
		$db->insert(TABLE_PREFIX."departments", $data);
		header('location:'.getUrl($controller,$action,array('departments','department_added')));
		exit;
	}
}elseif($params[1] == 'delete_department'){
	if(is_numeric($params[2]) && $params[2] > 1){
		$db->delete(TABLE_PREFIX."departments", "id=".$db->real_escape_string($params[2]));
		$db->update(TABLE_PREFIX."tickets", array('department_id' => 1), "department_id=".$db->real_escape_string($params[2]));
	}
		header('location:'.getUrl($controller,$action,array('departments','department_removed')));
		exit;
}elseif($params[1] == 'move_department'){
	if(is_numeric($params[3])){
		$department = $db->fetchRow("SELECT COUNT(id) as total, id, dep_order FROM ".TABLE_PREFIX."departments WHERE id=".$db->real_escape_string($params[3]));
		$last_position = $db->fetchOne("SELECT dep_order FROM ".TABLE_PREFIX."departments ORDER BY dep_order DESC LIMIT 1");
		if($department['total'] != 0){
			if($params[2] == 'up' && $department['dep_order'] > 1){
				$old_position = $department['dep_order'];
				$new_position = $old_position - 1;
				$db->query("UPDATE ".TABLE_PREFIX."departments SET dep_order=$old_position WHERE dep_order={$new_position}");
				$db->query("UPDATE ".TABLE_PREFIX."departments SET dep_order=$new_position WHERE id={$department['id']}");
			}elseif($params[2] == 'down' && $department['dep_order'] < $last_position){
				$old_position = $department['dep_order'];
				$new_position = $old_position + 1;
				$db->query("UPDATE ".TABLE_PREFIX."departments SET dep_order=$old_position WHERE dep_order={$new_position}");
				$db->query("UPDATE ".TABLE_PREFIX."departments SET dep_order=$new_position WHERE id={$department['id']}");
			}
		}
		header('location: '.getUrl($controller, $action, array('departments')));
		exit;
	}
}
$order_list = array('dep_order', 'name', 'type', 'tickets','users');
$orderby = (in_array($params[1],$order_list)?$params[1]:'dep_order');
$sortby = ($params[2] == 'desc'?'desc':'asc');
$q = $db->query("SELECT ".TABLE_PREFIX."departments.*, (SELECT COUNT(*) FROM ".TABLE_PREFIX."tickets WHERE department_id=".TABLE_PREFIX."departments.id) as tickets , (SELECT COUNT(*) FROM ".TABLE_PREFIX."staff WHERE department LIKE CONCAT('%\"',".TABLE_PREFIX."departments.id,'\"%')) as users  FROM ".TABLE_PREFIX."departments ORDER BY {$orderby} {$sortby}");	
while($r = $db->fetch_array($q)){
	$departments[] = $r;
}
$template_vars['departments'] = $departments;
$template_vars['last_position'] = $db->fetchOne("SELECT dep_order FROM ".TABLE_PREFIX."departments ORDER BY dep_order DESC LIMIT 1");
$template_vars['error_msg'] = $error_msg;
$template = $twig->loadTemplate('admin_departments.html');
echo $template->render($template_vars);
$db->close();
exit;	
?>