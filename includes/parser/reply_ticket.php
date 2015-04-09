<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
$code=trim(preg_replace("/\[/", "", $regs[0]));
$code=trim(preg_replace("/\]/", "", $code));
$code=str_replace("#","",$code);
$ticket_status = array(1 => $LANG['OPEN'], 2 => $LANG['ANSWERED'], 3 => $LANG['AWAITING_REPLY'], 4 => $LANG['IN_PROGRESS'], 5 => $LANG['CLOSED']);
$ticket = $db->fetchRow("SELECT COUNT(id) AS total, id, status, fullname, code, department_id, priority_id, subject FROM ".TABLE_PREFIX."tickets WHERE email='".$db->real_escape_string($from_email)."' AND code='".$db->real_escape_string($code)."'");
if($ticket['total'] != 0){
	$data = array(
					'ticket_id' => $ticket['id'],
					'date' => $datenow,
					'message' => $text,
					'ip' => $from_email,
				);
	$db->insert(TABLE_PREFIX."tickets_messages", $data);
	$message_id = $db->lastInsertId();
	if($ticket['status'] == '5' || $ticket['status'] == '2'){
		$status_name = $LANG['AWAITING_REPLY'];
		$addquery = ", status='3'";
	}else{
		$status_name = $ticket_status[$ticket['status']];	
	}
	$db->query("UPDATE ".TABLE_PREFIX."tickets SET last_update=".$datenow.", replies=replies+1, last_replier='{$ticket['fullname']}' {$addquery} WHERE id={$ticket['id']}");
	$db->update(TABLE_PREFIX."tickets", $data, "id={$tdetails['id']}");
	if(is_array($attachments)){
		$save_dir = UPLOAD_DIR;
		foreach($attachments as $attachment) {
		  // get the attachment name
		  $filename = $attachment->filename;
		  // write the file to the directory you want to save it in
		  if ($fp = fopen($save_dir.$filename, 'w')) {
			while($bytes = $attachment->read()) {
			  fwrite($fp, $bytes);
			}
			fclose($fp);
		  }
			
		  $filesize = @filesize(UPLOAD_DIR.$filename);
		  if($filesize){
			  $fileinfo = array('name' => $filename, 'size' => $filesize);
			  $fileverification = verifyAttachment($fileinfo);
			  if($fileverification['msg_code'] == 0){
				$ext = pathinfo($filename, PATHINFO_EXTENSION);
				$filename_encoded = md5($filename.time()).".".$ext;
				$data = array('name' => $filename, 'enc' => $filename_encoded, 'filesize' => $filesize, 'ticket_id' => $ticket['id'], 'msg_id' => $message_id, 'filetype' => $attachment->content_type);
				$db->insert(TABLE_PREFIX."attachments", $data);
				rename(UPLOAD_DIR.$filename, UPLOAD_DIR.'tickets/'.$filename_encoded);
			  }else{
				unlink(UPLOAD_DIR.$filename);
			  }
		  }
		}
	}
	/* Mailer */
	$fullname = $from_name;
	$email = $from_email;
	$department_name = $db->fetchOne("SELECT name FROM ".TABLE_PREFIX."departments WHERE id={$ticket['department_id']}");	
	$priority_name = $db->fetchOne("SELECT name FROM ".TABLE_PREFIX."priority WHERE id={$ticket['priority_id']}");	
	$data_mail = array(
	'id' => 'autoresponse',
	'to' => $fullname,
	'to_mail' => $email,
	'vars' => array('%client_name%' => $fullname, 
					'%client_email%' => $email, 
					'%ticket_id%' => $ticket['code'],
					'%ticket_subject%' => $ticket['subject'],
					'%ticket_department%' => $department_name,
					'%ticket_status%' => $status_name,
					'%ticket_priority%' => $priority_name,
					),
	);
	$mailer = new Mailer($data_mail);
}
?>