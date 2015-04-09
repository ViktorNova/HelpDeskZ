<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
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