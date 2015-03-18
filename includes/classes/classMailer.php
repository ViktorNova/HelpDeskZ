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
require_once INCLUDES.'PHPMailer/PHPMailerAutoload.php';
class Mailer
{
	function Mailer($data_mail){
		global $db, $settings;
		$this->data = $data_mail;
		$this->smtp_hostname = $settings['smtp_hostname'];
		$this->smtp_port = $settings['smtp_port'];
		$this->smtp_ssl = $settings['smtp_ssl'];
		$this->smtp_username = $settings['smtp_username'];
		$this->smtp_password = $settings['smtp_password'];
		$this->maildata = $db->fetchRow("SELECT subject, message FROM ".TABLE_PREFIX."emails WHERE id='{$this->data['id']}'");
		$this->company_name = $settings['site_name'];
		$this->helpdesk_url = $settings['site_url'];
		$this->setVars();
		$this->mail = new PHPMailer();
		if($settings['smtp'] == 'yes'){
			$this->mail->IsSMTP();
			$this->mail->SMTPAuth		= true;
			$this->mail->SMTPSecure		= $this->smtp_ssl;
			$this->mail->Host 			= $this->smtp_hostname;		
			$this->mail->Port			= $this->smtp_port;
			$this->mail->Username		= $this->smtp_username;
			$this->mail->Password		= $this->smtp_password;	
		}
		$this->mail->SetFrom($settings['email_ticket'], $this->company_name);
		$this->mail->AddReplyTo($settings['email_ticket'], $this->company_name);
		$this->mail->AddAddress($this->data['to_mail'], $this->data['from']);
		$this->mail->Subject = $this->mail_subject;
		$this->mail->ContentType = 'text/plain'; 
		$this->mail->IsHTML(false);
		$this->mail->Body = $this->mail_content;
		$this->mail->CharSet = 'UTF-8';
		if($this->data['attachement'] == 1){
			foreach($this->data['attachement_files'] as $v){
				$attachfiles.= UPLOAD_DIR.$this->data['attachement_type'].'/'.$v['enc'];
				$this->mail->addAttachment(UPLOAD_DIR.$this->data['attachement_type'].'/'.$v['enc'], $v['name']); 
			}
		}
		if(!$this->mail->Send()) {
			$data = array('error' => 'Error sending email: '.$this->mail->ErrorInfo);
			$db->insert(TABLE_PREFIX."error_log", $data);
		}
	}
	function setVars(){
		$vars = array_merge($this->data['vars'], array('%company_name%' => $this->company_name, '%helpdesk_url%' => $this->helpdesk_url));	
		$this->mail_subject = str_replace(array_keys($vars), array_values($vars), $this->maildata['subject']);
		$this->mail_content = str_replace(array_keys($vars), array_values($vars), $this->maildata['message']);
	}
}
?>