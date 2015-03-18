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
if($params[1] == 'save'){
	if(verifyToken('canned', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];	
	}elseif($input->p['title'] == ''){
		$error_msg = $LANG['ENTER_THE_TITLE'];	
	}else{
		$total = $db->fetchOne("SELECT COUNT(id) AS NUM FROM ".TABLE_PREFIX."canned_response");
		$data = array('title' => $input->p['title'],
					'message' => $input->p['message'],
					'position' => $total+1,
					);
		$db->insert(TABLE_PREFIX."canned_response", $data);
		header('location: '.getUrl($controller, $action, array('canned','saved')));
		exit;	
	}
}elseif($params[1] == 'editMsg'){
	if(verifyToken('canned', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];	
	}elseif($input->p['title'] == ''){
		$error_msg = $LANG['ENTER_THE_TITLE'];
	}elseif(!is_numeric($input->p['msgid'])){
		$error_msg = $LANG['INVALID_ID'];
	}else{
		$data = array('title' => $input->p['title'],
						'message' => $input->p['message'],
						);
		$db->update(TABLE_PREFIX."canned_response", $data, "id=".$db->real_escape_string($input->p['msgid']));
		header('location: '.getUrl($controller, $action, array('canned','updated')));
		exit;
	}
}elseif($params[1] == 'GetCannedForm'){
	if(is_numeric($params[2]) && $params[2] != 0){
		$canned = $db->fetchRow("SELECT *, COUNT(id) as total FROM ".TABLE_PREFIX."canned_response WHERE id=".$db->real_escape_string($params[2]));
		if($canned['total'] == 0){
			die($LANG['ERROR_RETRIEVING_DATA']);	
		}
		$form_action = getUrl($controller,$action,array('canned','editMsg'));
	}else{
		$form_action = getUrl($controller,$action,array('canned','save'));
	}
	$template_vars['canned'] = $canned;
	$template_vars['form_action'] = $form_action;
	$template = $twig->loadTemplate('form_canned.html');
	echo $template->render($template_vars);
	$db->close();
	exit;
}elseif($params[1] == 'move'){
	if(is_numeric($params[3])){
		$canned = $db->fetchRow("SELECT COUNT(id) as total, id, position FROM ".TABLE_PREFIX."canned_response WHERE id=".$db->real_escape_string($params[3]));
		$last_position = $db->fetchOne("SELECT position FROM ".TABLE_PREFIX."canned_response ORDER BY position DESC LIMIT 1");
		if($canned['total'] != 0){
			if($params[2] == 'up' && $canned['position'] > 1){
				$old_position = $canned['position'];
				$new_position = $old_position - 1;
				$db->query("UPDATE ".TABLE_PREFIX."canned_response SET position=$old_position WHERE position={$new_position}");
				$db->query("UPDATE ".TABLE_PREFIX."canned_response SET position=$new_position WHERE id={$canned['id']}");
			}elseif($params[2] == 'down' && $canned['position'] < $last_position){
				$old_position = $canned['position'];
				$new_position = $old_position + 1;
				$db->query("UPDATE ".TABLE_PREFIX."canned_response SET position=$old_position WHERE position={$new_position}");
				$db->query("UPDATE ".TABLE_PREFIX."canned_response SET position=$new_position WHERE id={$canned['id']}");
			}
		}
		header('location: '.getUrl($controller, $action, 'canned'));
		exit;
	}
}elseif($params[1] == 'remove'){
	if(is_numeric($params[2])){
		$db->delete(TABLE_PREFIX."canned_response", "id=".$db->real_escape_string($params[2]));
	}
	header('location: '.getUrl($controller, $action, array('canned','removed')));
	exit;
}
$page = (!is_numeric($params[2])?1:$params[2]);
$max_results = $settings['page_size'];
$count = $db->fetchOne("SELECT COUNT(*) AS NUM FROM ".TABLE_PREFIX."canned_response");
$total_pages = ceil($count/$max_results);	
$page = ($page>$total_pages?$total_pages:$page);
$from = ($max_results*$page) - $max_results;
$q = $db->query("SELECT * FROM ".TABLE_PREFIX."canned_response ORDER BY position LIMIT $from, $max_results");
while($r = $db->fetch_array($q)){
	$canned_responses[] = $r;
}
$template_vars['canned_responses'] = $canned_responses;
$template_vars['error_msg'] = $error_msg;
$template_vars['total_pages'] = $total_pages;
$template_vars['page'] = $page;
$template_vars['orderby'] = $orderby;
$template_vars['sortby'] = $sortby;
$template_vars['getvar'] = $getvar;
$template_vars['last_position'] = $last_position = $db->fetchOne("SELECT position FROM ".TABLE_PREFIX."canned_response ORDER BY position DESC LIMIT 1");
$template = $twig->loadTemplate('canned.html');
echo $template->render($template_vars);
$db->close();
exit;
?>