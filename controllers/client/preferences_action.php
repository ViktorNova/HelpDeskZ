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
	if($params[0] == 'save'){
		if(verifyToken('preferences', $input->p['csrfhash']) !== true){
			$error_msg = $LANG['CSRF_ERROR'];	
		}else{
			$timezone_user = '';
			if(!empty($input->p['timezone'])){
				if(in_array($input->p['timezone'],$timezone)){
					$timezone_user = $input->p['timezone'];	
				}
			}
			$data = array('timezone' => $timezone_user);
			$db->update(TABLE_PREFIX."users", $data, "id={$user['id']}");
			header('location: '.getUrl('user_account','preferences', array('saved')));
			exit;
		}
	}
	$template_vars['timezone'] = $timezone;
	$template_vars['user'] = $user;
	$template_vars['error_msg'] = $error_msg;
	$template = $twig->loadTemplate('user_preferences.html');
	echo $template->render($template_vars);
	$db->close();
	exit;
?>