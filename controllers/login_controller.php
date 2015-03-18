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
include(INCLUDES.'helpdesk.inc.php');
	if($input->p['do'] == 'login'){
		if(verifyToken('login', $input->p['csrfhash']) !== true){
			$error_msg = $LANG['CSRF_ERROR'];	
		}elseif(validateEmail($input->p['email']) !== true || empty($input->p['password'])){
			$error_msg = $LANG['INVALID_EMAIL_OR_PASSWORD'];
		}else{
			if($settings['loginshare'] == 1){
				$xmlurl = $settings['loginshare_url'];
				$postfields = "email=".urlencode($input->p['email'])."&password=".urlencode($input->p['password'])."&ip=".urlencode($_SERVER['REMOTE_ADDR']);
				$ch = curl_init(); 
				curl_setopt($ch,CURLOPT_URL,$xmlurl);
				curl_setopt($ch,CURLOPT_RETURNTRANSFER,true);
				curl_setopt($ch,CURLOPT_HEADER, false);
				curl_setopt($ch, CURLOPT_POST, 3);
				curl_setopt($ch, CURLOPT_POSTFIELDS, $postfields);   
				$output=curl_exec($ch);
				curl_close($ch);
				libxml_use_internal_errors(true);
				$xml = simplexml_load_string($output);
				if($xml !== false){
					if($xml->result == 1 && !empty($xml->user->fullname) && !empty($xml->user->email)){
						$data = array('fullname' => $xml->user->fullname, 'email' => $xml->user->email, 'password' => sha1($input->p['password']));
						$chk = $db->fetchOne("SELECT COUNT(id) AS NUM FROM ".TABLE_PREFIX."users WHERE email='".$db->real_escape_string($input->p['email'])."'");
						
						if($chk == 0){
							$db->insert(TABLE_PREFIX."users", $data);
						}else{
							$db->update(TABLE_PREFIX."users", $data, "email='".$db->real_escape_string($input->p['email'])."'");
						}
					}
				}
			}

			$password = sha1($input->p['password']);
			$chk = $db->fetchOne("SELECT COUNT(id) AS NUM FROM ".TABLE_PREFIX."users WHERE email='".$db->real_escape_string($input->p['email'])."' AND password='$password'");
			if($chk == 0){
				$error_msg = $LANG['INVALID_EMAIL_OR_PASSWORD'];
			}else{
				if($input->p['remember'] == 1){
					$cookie_time = 48;
				}else{
					$cookie_time = 1;						
				}
				$user = $db->fetchRow("SELECT * FROM ".TABLE_PREFIX."users WHERE email='".$db->real_escape_string($input->p['email'])."'");
				$cookie_time = time() + (60*60*$cookie_time);
				$data = array('id' => $user['id'], 'email' => $user['email'], 'password' => $user['password'], 'expires' => $cookie_time);
				$data = serialize($data);
				$data = encrypt($data);

				setcookie('usrhash', $data, $cookie_time, '/');
				$_SESSION['user']['id'] = $user['id'];
				$_SESSION['user']['email'] = $user['email'];
				$_SESSION['user']['password'] = $user['password'];
				header('location: '.getUrl('view_tickets'));
				exit;
			}
		}
	}	
include(CONTROLLERS.'home_controller.php');
exit;
?>