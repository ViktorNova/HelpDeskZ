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
$q = $db->query("SELECT * FROM ".TABLE_PREFIX."news ORDER BY date DESC LIMIT 3");
while($r = $db->fetch_array($q)){
	$lastestnews[] = $r;	
}
$q= $db->query("SELECT * FROM ".TABLE_PREFIX."login_log WHERE staff_id=".$staff['id']." ORDER BY date DESC LIMIT 10");
while($r = $db->fetch_array($q)){
	$login_log[] = $r;	
}
$q = $db->query("SELECT * FROM ".TABLE_PREFIX."departments");
while($r = $db->fetch_array($q)){
	if(in_array($r['id'],$staff_departments)){
		$departments[$r['id']] = $r['name'];
	}else{
		$exceptiondep_query .= " AND department_id!={$r['id']}";
	}
}
$tickets_summary = $db->fetchRow("SELECT (SELECT COUNT(id) FROM ".TABLE_PREFIX."tickets WHERE status='1' {$exceptiondep_query}) AS open, (SELECT COUNT(id) FROM ".TABLE_PREFIX."tickets WHERE status='2' {$exceptiondep_query}) as answered, (SELECT COUNT(id) FROM ".TABLE_PREFIX."tickets WHERE status='3' {$exceptiondep_query}) as awaiting_reply, (SELECT COUNT(id) FROM ".TABLE_PREFIX."tickets WHERE status='4' {$exceptiondep_query}) as in_progress, (SELECT COUNT(id) FROM ".TABLE_PREFIX."tickets WHERE status='5' {$exceptiondep_query}) as closed");
$template_vars['lastestnews'] = $lastestnews;
$template_vars['login_log'] = $login_log;
$template_vars['tickets_summary'] = $tickets_summary;
$template = $twig->loadTemplate('dashboard.html');
echo $template->render($template_vars);
$db->close();
exit;
?>