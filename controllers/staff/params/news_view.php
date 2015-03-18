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
if(is_numeric($params[1])){
	$news = $db->fetchRow("SELECT *, COUNT(id) as total FROM ".TABLE_PREFIX."news WHERE id=".$db->real_escape_string($params[1]));
	if($news['total'] == 0){
		header('location: '.getUrl($controller,$action,array('view')));
		exit;	
	}else{
		$view_single = 1;
		$template_vars['view_single'] = $view_single;
		$template_vars['news'] = $news;
		$template = $twig->loadTemplate('news.html');
		echo $template->render($template_vars);
		$db->close();
		exit;
	}
}
if($params[1] == 'page'){
	$page = (!is_numeric($params[2]) || $params[2]<0?1:$params[2]);
}else{
	$page = 1;	
}
$max_results = 5;
$count = $db->fetchOne("SELECT COUNT(*) AS NUM FROM ".TABLE_PREFIX."news");
$total_pages = ceil($count/$max_results);	
$page = ($page>$total_pages?$total_pages:$page);
$from = ($max_results*$page) - $max_results;
$q = $db->query("SELECT * FROM ".TABLE_PREFIX."news ORDER BY date DESC LIMIT $from, $max_results");
while($r = $db->fetch_array($q)){
	$news_result[] = $r;	
}
$template_vars['page'] = $page;
$template_vars['total_pages'] = $total_pages;
$template_vars['news_result'] = $news_result;
$template_vars['view_single'] = $view_single;
$template = $twig->loadTemplate('news.html');
echo $template->render($template_vars);
$db->close();
exit;
?>