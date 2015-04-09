<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
if($controller == 'home'){
	include(INCLUDES.'helpdesk.inc.php');
}
$template_vars = array();
if($action == '404notfound'){
	$template_name = '404.html';
}else{
	if($settings['homepage'] == 'knowledgebase' && $settings['knowledgebase'] == 'yes'){
		$filename = CONTROLLERS.'knowledgebase_controller.php';
		include($filename);
	}elseif($settings['homepage'] == 'news' && $settings['news'] == 'yes'){
		$q = $db->query("SELECT * FROM ".TABLE_PREFIX."news WHERE public=1 ORDER BY date DESC LIMIT 5");
		while($r = $db->fetch_array($q)){
			$r['url'] = getUrl('news',$r['id'],array(strtourl($r['title'])));
			$news[] = $r;	
		}
		$template_vars['news'] = $news;
		$template_name = 'home_news.html';
	}else{
		$homepage = $db->fetchRow("SELECT * FROM ".TABLE_PREFIX."pages WHERE id='home'");
		$template_vars['homepage'] = $homepage;
		$template_name = 'home.html';
	}
}
$template_vars['error_msg'] = $error_msg;
$template = $twig->loadTemplate($template_name);
echo $template->render($template_vars);
$db->close();
exit;
?>