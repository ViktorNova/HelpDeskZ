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
if($settings['news'] != 'yes'){
	header('location: '.getUrl());
	exit;	
}
if(is_numeric($action)){
	$news = $db->fetchRow("SELECT *, COUNT(id) as total FROM ".TABLE_PREFIX."news WHERE id=".$db->real_escape_string($action));
	if($news['total'] == 0){
		header('location: '.getUrl($controller));
		exit;	
	}else{
		$news['url'] = getUrl($controller,$news['id'],array(strtourl($news['title'])));
		$template = $twig->loadTemplate('news_article.html');
		$template_vars = array('news' => $news);
		echo $template->render($template_vars);
		$db->close();
		exit;	
	}
}
if($action == 'page'){
	$page = (!is_numeric($params[0]) || $params[0]<0?1:$params[0]);
}else{
	$page = 1;	
}
$max_results = $settings['news_page'];
$count = $db->fetchOne("SELECT COUNT(*) AS NUM FROM ".TABLE_PREFIX."news WHERE public=1");
$total_pages = ceil($count/$max_results);	
$page = ($page>$total_pages?$total_pages:$page);
$from = ($max_results*$page) - $max_results;
$q = $db->query("SELECT * FROM ".TABLE_PREFIX."news WHERE public=1 ORDER BY date DESC LIMIT $from, $max_results");
while($r = $db->fetch_array($q)){
	$r['url'] = getUrl($controller,$r['id'],array(strtourl($r['title'])));
	$news[] = $r;
}
$template = $twig->loadTemplate('news.html');
$template_vars = array('news' => $news, 'total_pages' => $total_pages, 'page' => $page);
echo $template->render($template_vars);
$db->close();
exit;	
?>