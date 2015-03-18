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
class Input_Cleaner
{
	var $cleaned_vars = array();
    public function __construct()
	{
		$this->test = 'asas';
		if(function_exists('get_magic_quotes_runtime') && get_magic_quotes_runtime())
			set_magic_quotes_runtime(false);
			
			if(get_magic_quotes_gpc()) {
				$this->array_stripslashes($_POST);
				$this->array_stripslashes($_GET);
				$this->array_stripslashes($_COOKIES);
			}
			$this->frm = $_POST;
			$this->frmg = $_GET;
			$this->cookie = $_COOKIE;
			while (list ($kk, $vv) = each ($this->frm)){
				if (is_array ($vv)){
					$vv_cleaned = $vv;
				}else{
				  $vv = trim ($vv);
				  $vv_cleaned = htmlspecialchars(trim($vv));
				}
				$this->p[$kk] = $vv;
				$this->pc[$kk] = $vv_cleaned;
				$this->r[$kk] = $vv;
				$this->rc[$kk] = $vv_cleaned;
			}
			while (list ($kk, $vv) = each ($this->frmg)){
				if (is_array ($vv)){
					$vv_cleaned = $vv;
				}else{
				  $vv = trim ($vv);
				  $vv_cleaned = htmlspecialchars(trim($vv));
				}
				$this->g[$kk] = $vv;
				$this->gc[$kk] = $vv_cleaned;
				$this->r[$kk] = $vv;
				$this->rc[$kk] = $vv_cleaned;
			}
			while (list ($kk, $vv) = each ($this->cookie)){
				if (is_array ($vv)){
				}else{
				  $vv = trim ($vv);
				  $vv_cleaned = htmlspecialchars(trim($vv));
				}
				$this->c[$kk] = $vv;
				$this->cc[$kk] = $vv_cleaned;
			}
			
	}
	
	function array_stripslashes(&$array) {
		if(is_array($array))
			while(list($key) = each($array))
				if(is_array($array[$key]))
					$this->array_stripslashes($array[$key]);
				else
					$array[$key] = stripslashes($array[$key]);
	}
}
?>