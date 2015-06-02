<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
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
                        hdz_registerAccount(array('fullname' => $xml->user->fullname,
                                                    'email' => $xml->user->email,
                                                    'password' => $input->p['password']), FALSE, TRUE);

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
                hdz_loginAccount($input->p['email'], $cookie_time);
				header('location: '.getUrl('view_tickets'));
				exit;
			}
		}
	}	
include(CONTROLLERS.'home_controller.php');
exit;
?>