<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
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