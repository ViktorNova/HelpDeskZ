<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
function display_parent_cats($parent_category,$level){
	global $parent_cat, $kbselector;
	$level = $level;
	$nextlevel = $level+1;
	for($i=1;$i<=$level;$i++){
		$spaces .= "&nbsp; &nbsp;";	
	}
	if(is_array($parent_category)){
		foreach($parent_category as $parent){
			$selector .= '<optgroup label="'.$spaces.$parent['name'].'">';	
			if(is_array($kbselector[$parent['id']])){
				foreach($kbselector[$parent['id']] as $kb){
					$selector .= $kb;	
				}
			}
			if(is_array($parent_cat[$parent['id']])){
				$selector .= display_parent_cats($parent_cat[$parent['id']],$nextlevel);	
			}
			$selector .= '</optgroup>';
		}
	}
	return $selector;
}
	$ticket_status = array(1 => $LANG['OPEN'], 2 => $LANG['ANSWERED'], 3 => $LANG['AWAITING_REPLY'], 4 => $LANG['IN_PROGRESS'], 5 => $LANG['CLOSED']);
	$ticketid = $db->real_escape_string($params[1]);
	$ticket = $db->fetchRow("SELECT *, count(id) as total FROM ".TABLE_PREFIX."tickets WHERE id=$ticketid");
	if($ticket['total'] == 0 || !array_key_exists($ticket['department_id'],$departments)){
		$error_msg = $LANG['TICKET_NOT_FOUND'];
	}else{
		if($params[2] == 'update'){
			if(verifyToken('ticket', $input->p['csrfhash']) !== true){
				$error_msg = $LANG['CSRF_ERROR'];		
			}elseif(!array_key_exists($input->p['department'],$departments)){
				$error_msg = $LANG['INVALID_DEPARTMENT'];		
			}elseif(!array_key_exists($input->p['status'],$ticket_status)){
				$error_msg = $LANG['INVALID_STATUS'];		
			}elseif(!array_key_exists($input->p['priority'],$priority)){
				$error_msg = $LANG['INVALID_PRIORITY'];		
			}else{
				$data = array('department_id' => $input->p['department'], 
							'status' => $input->p['status'],
							'priority_id' => $input->p['priority']);
				$db->update(TABLE_PREFIX."tickets", $data, "id=$ticketid");
				header('location: '.getUrl($controller, $action, array('view',$ticketid,'updated')));
				exit;
			}
		}elseif($params[2] == 'reply'){
			if(verifyToken('ticket', $input->p['csrfhash']) !== true){
				$error_msg = $LANG['CSRF_ERROR'];		
			}elseif(empty($input->p['message'])){
				$error_msg = $LANG['ENTER_YOUR_MESSAGE'];
			}else{
				$uploaddir = UPLOAD_DIR.'tickets/';		
				if($_FILES['attachment']['error'] == 0){
					$ext = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
					$filename = md5($_FILES['attachment']['name'].time()).".".$ext;
					$fileuploaded[] = array('name' => $_FILES['attachment']['name'], 'enc' => $filename, 'size' => formatBytes($_FILES['attachment']['size']), 'filetype' => $_FILES['attachment']['type']);
					$uploadedfile = $uploaddir.$filename;
					if (!move_uploaded_file($_FILES['attachment']['tmp_name'], $uploadedfile)) {
						$error_msg = $LANG['ERROR_UPLOADING_FILE'];
					}
				}	
			}		
			if($error_msg == ''){
				$datenow = time();
				$data = array('ticket_id' => $ticketid, 
							'date' => $datenow,
							'customer' => '0',
							'name' => $staff['fullname'],
							'message' => $input->p['message']."\n\n".$staff['signature'],
							'ip' => $_SERVER['REMOTE_ADDR'],
							'email' => $staff['email'],
							);
				$db->insert(TABLE_PREFIX.'tickets_messages',$data);
				$message_id = $db->lastInsertId();
				$status = ($ticket['status'] == '4'?'4':'2');
				$db->query("UPDATE ".TABLE_PREFIX."tickets SET last_update='$datenow', status='$status', replies=replies+1, last_replier='".$db->real_escape_string($staff['fullname'])."' WHERE id=$ticketid");
				if(is_array($fileuploaded)){
					foreach($fileuploaded as $f){
					$data = array('name' => $f['name'], 'enc' => $f['enc'], 'filesize' => $f['size'], 'ticket_id' => $ticketid, 'msg_id' => $message_id, 'filetype' => $f['filetype']);
					$db->insert(TABLE_PREFIX."attachments", $data);
					}
				}
				/* Mailer */
				$data_mail = array(
				'id' => 'staff_reply',
				'to' => $ticket['fullname'],
				'to_mail' => $ticket['email'],
				'attachement' => (is_array($fileuploaded)?1:0),
				'attachement_type' => 'tickets',
				'attachement_files' => $fileuploaded,
				'vars' => array('%client_name%' => $ticket['fullname'], 
								'%client_email%' => $ticket['email'], 
								'%ticket_id%' => $ticket['code'],
								'%ticket_subject%' => $ticket['subject'],
								'%ticket_department%' => $departments[$ticket['department_id']],
								'%ticket_status%' => $ticket_status[$status],
								'%ticket_priority%' => $priority[$ticket['priority_id']]['name'],
								'%message%' => $input->p['message']."\n\n".$staff['signature'],
								),
								
				);
				$mailer = new Mailer($data_mail);
				header('location: '.getUrl($controller, $action, array('view',$ticketid,'replied')));
				exit;	
			}
		}elseif($params[2] == 'GetQuote'){
			if(is_numeric($params[3])){
				$message = $db->fetchOne("SELECT ".TABLE_PREFIX."message FROM tickets_messages WHERE id=".$db->real_escape_string($params[3]));
				$message = wordwrap($message, 65, "\n");
				$message2 = html_entity_decode($message);	
				foreach(explode("\n", $message2) as $line) {
					echo "> ".$line."\n";
				}
			}
			exit;
		}elseif($params[2] == 'GetMsg'){
			if(is_numeric($params[3])){
				$message = $db->fetchRow("SELECT id, message, ticket_id FROM ".TABLE_PREFIX."tickets_messages WHERE id=".$db->real_escape_string($params[3]));
				$template_vars['message'] = $message;
				$template = $twig->loadTemplate('form_editpost.html');
				echo $template->render($template_vars);
				$db->close();
				exit;
			}else{
				die($LANG['ERROR_RETRIEVING_DATA']);	
			}
		}elseif($params[2] == 'editMsg'){
			if(is_numeric($input->p['msgid'])){
				$db->query("UPDATE ".TABLE_PREFIX."tickets_messages SET message='".$db->real_escape_string($input->p['message'])."' WHERE id=".$db->real_escape_string($input->p['msgid']));
			}
			header('location:'.getUrl($controller,$action,array('view',$ticket['id'],'MsgEdited')));
			exit;
		}elseif($params[2] == 'RemoveMsg'){
			if(is_numeric($params[3])){
				$chk = $db->fetchOne("SELECT COUNT(id) AS NUM FROM ".TABLE_PREFIX."tickets_messages WHERE id=".$db->real_escape_string($params[3]));
				if($chk != 0){
					$db->delete(TABLE_PREFIX."tickets_messages", "id=".$db->real_escape_string($params[3]));
					removeAttachment($params[3],'msg');
					if($ticket['replies']>0){
						$db->query("UPDATE ".TABLE_PREFIX."tickets SET replies=replies-1 WHERE id={$ticket['id']}");	
					}
				}
			}
			exit;
		}elseif($params[2] == 'attachment'){
			if(!is_numeric($params['3'])){
				$filename = CONTROLLERS.'home_controller.php';
				$action = '404notfound';
				include($filename);
				exit;
			}else{
				$attachment = $db->fetchRow("SELECT *, COUNT(id) AS total FROM ".TABLE_PREFIX."attachments WHERE id=".$db->real_escape_string($params[3])." AND ticket_id=".$params[1]." AND msg_id=".$params[4]);
				if($attachment['total'] == 0){
					$filename = CONTROLLERS.'home_controller.php';
					$action = '404notfound';
					include($filename);
					exit;
				}else{
					header("Content-disposition: attachment; filename=".$attachment['name']);
					header("Content-type: ".$attachment['filetype']);
					readfile(UPLOAD_DIR.'tickets/'.$attachment['enc']);	
					exit;
				}
			}
		}elseif($params[2] == 'remove_attachment'){
			$attachment = $db->fetchRow("SELECT id, COUNT(id) AS total FROM ".TABLE_PREFIX."attachments WHERE id=".$db->real_escape_string($params[3])." AND ticket_id=".$params[1]." AND msg_id=".$params[4]);
			if($attachment['total'] != 0){
				removeAttachment($attachment['id'],'ticket');
			}
			header('location:'.getUrl($controller,$action,array('view',$ticket['id'],'AttachmentRemoved')));
			exit;	
		}elseif($params[2] == 'getKB'){
			if(is_numeric($params[3])){
				$kb = $db->fetchOne("SELECT content FROM ".TABLE_PREFIX."articles WHERE id=".$db->real_escape_string($params[3]));
				$kb = html_entity_decode(strip_tags($kb));
				$kb = urldecode($kb);
				echo $kb;
			}
			exit;
		}
		$canned_q = $db->query("SELECT id, title, message FROM ".TABLE_PREFIX."canned_response ORDER BY position ASC");
		while($r = $db->fetch_array($canned_q)){
			$r['message'] = html_entity_decode($r['message']);
			$cannedList[$r['id']] = $r;	
		}
		$canned_encoded = json_encode($cannedList);
		$page_title = '[#'.$ticket['code'].']: '.$ticket['subject'];
 		$page = (!is_numeric($params[2])?1:$params[2]);
		$max_results = $settings['tickets_page'];
		$count = $db->fetchOne("SELECT COUNT(*) AS NUM FROM ".TABLE_PREFIX."tickets_messages WHERE ticket_id=$ticketid");
		$total_pages = ceil($count/$max_results);	
		$page = ($page>$total_pages?$total_pages:$page);
		$from = ($max_results*$page) - $max_results;
		$tickets_query = $db->query("SELECT * FROM ".TABLE_PREFIX."tickets_messages WHERE ticket_id=$ticketid ORDER BY date DESC LIMIT $from, $max_results");
		while($r = $db->fetch_array($tickets_query)){
			$attachments = $db->fetchRow("SELECT *, COUNT(id) AS total FROM ".TABLE_PREFIX."attachments WHERE msg_id={$r['id']}");
			$r['attachments'] = $attachments;
			$ticket_messages[] = $r;	
		}
		$template_vars['ticket_messages'] = $ticket_messages;
		$template_vars['total_pages'] = $total_pages;
		$template_vars['page'] = $page;

		$query = $db->query("SELECT id, title, category FROM ".TABLE_PREFIX."articles ORDER BY category ASC");
		while($r = $db->fetch_array($query)){
			$kbselector[$r['category']][] = '<option value="'.$r['id'].'">'.$r['title'].'</option>';	
		}
		$selector = '<optgroup label="Root Category">';
		if(is_array($kbselector[0])){
			foreach($kbselector[0] as $kb){
				$selector .= $kb;	
			}
		}
		$selector .= '</optgroup>';
		
		$query = $db->query("SELECT * FROM ".TABLE_PREFIX."knowledgebase_category ORDER BY parent ASC, position ASC");
		while($r = $db->fetch_array($query)){
			if($r['parent'] != 0){
				$parent_cat[$r['parent']][] = $r;
			}else{
				$main_cat[] = $r;	
			}
		}
		if(is_array($main_cat)){
			foreach($main_cat as $k){
					$total = 0;
					$list_selector = '<optgroup label="&nbsp; '.$k['name'].'">';
					if(is_array($kbselector[$k['id']])){
						foreach($kbselector[$k['id']] as $kb){
							$list_selector .= $kb;
							$total = $total+1;
						}
					}
					$list_selector .= '</optgroup>';
					
					if(is_array($parent_cat[$k['id']])){
						$parentlist = display_parent_cats($parent_cat[$k['id']],1);
					}else{
						$parentlist = '';	
					}
					if($total > 0 || $parentlist != ''){
						$selector .= $list_selector;
						$selector .= $parentlist;
					}
					
			}
		}
		$template_vars['ticket'] = $ticket;
		$template_vars['canned_encoded'] = $canned_encoded;
		$custom_vars = unserialize($ticket['custom_vars']);
		$custom_vars = (is_array($custom_vars)?$custom_vars:array());
		$customq = $db->query("SELECT * FROM ".TABLE_PREFIX."custom_fields ORDER BY display ASC");
		while($r = $db->fetch_array($customq)){
			if(array_key_exists($r['id'],$custom_vars)){
				$r['exits'] = 1;
				if(is_array($custom_vars[$r['id']])){
					$r['value'] = unserialize($r['value']);
					foreach($custom_vars[$r['id']] as $k => $v){
						$r['values'][] = $r['value'][$k];
					}
				}else{
					if($r['type'] == 'checkbox' || $r['type'] == 'radio' || $r['type'] == 'select'){
						$values = unserialize($r['value']);
						$r['values'] =  $values[$custom_vars[$r['id']]];	
					}else{
						$r['values'] = htmlentities($custom_vars[$r['id']]);	
					}	
				}
			}
			$custom_fieldsdb[] = $r;
	
		}
		$template_vars['custom_fieldsdb'] = $custom_fieldsdb;
		$template_vars['custom_vars'] = $custom_vars;
		$template_vars['cannedList'] = $cannedList;
		$template_vars['selector'] = $selector;
		$template_vars['error_msg'] = $error_msg;
		$template = $twig->loadTemplate('view_ticket.html');
		echo $template->render($template_vars);
		$db->close();
		exit;
	}
?>