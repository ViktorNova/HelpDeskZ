<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
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