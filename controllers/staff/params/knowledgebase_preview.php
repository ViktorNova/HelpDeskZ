<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
$main_url = getUrl($controller,$action,array('preview',''));
$template_vars['main_url'] = $main_url;

if($params[1] == 'article' && is_numeric($params[2])){
	$article = $db->fetchRow("SELECT *, COUNT(id) AS total FROM ".TABLE_PREFIX."articles WHERE id=".$db->real_escape_string($params[2]));
	if($article['total'] == 0){
		header('location: '.$main_url);
		exit;
	}else{
		if($params[3] == 'attachment' && is_numeric($params[4])){
			$attachment = $db->fetchRow("SELECT *, COUNT(id) AS total FROM ".TABLE_PREFIX."attachments WHERE id=".$db->real_escape_string($params[4])." AND article_id=".$article['id']);

			if($attachment['total'] == 0){
				$filename = CONTROLLERS.'home_controller.php';
				$action = '404notfound';
				include($filename);
				exit;
			}else{
				header("Content-disposition: attachment; filename=".$attachment['name']);
				header("Content-type: ".$attachment['filetype']);
				readfile(UPLOAD_DIR.'articles/'.$attachment['enc']);	
				exit;
			}
		}
		$q = $db->query("SELECT * FROM ".TABLE_PREFIX."attachments WHERE article_id=".$article['id']);
		while($r = $db->fetch_array($q)){
			$attachments[] = $r;
		}
		$template_vars['attachments'] = $attachments;
		$template_vars['article'] = $article;
		$attachment_url = getUrl($controller,$action,array('preview','article',$article['id'],'attachment',''));
		$template_vars['attachment_url'] = $attachment_url;
		$template = $twig->loadTemplate('knowledgebase_preview_article.html');
		echo $template->render($template_vars);
		$db->close();
		exit;
	}
}
if(is_numeric($params[1])){
	$cat_id = $params[1];
}else{
	$cat_id = 0;
}

$article_url = getUrl($controller,$action,array('preview','article',''));
$template_vars['article_url'] = $article_url;

$q = $db->query("SELECT * FROM ".TABLE_PREFIX."knowledgebase_category WHERE parent=".$db->real_escape_string($cat_id)." ORDER BY position ASC");	
while($r = $db->fetch_array($q)){
	$r['total_articles'] = $db->fetchOne("SELECT COUNT(id) AS total FROM ".TABLE_PREFIX."articles WHERE category=".$r['id']);
	if($r['total_articles'] != 0){
		$aq = $db->query("SELECT id, title FROM ".TABLE_PREFIX."articles WHERE category=".$r['id']." ORDER BY date DESC LIMIT {$settings['knowledgebase_articlesundercat']}");
		$article_list = array();
		while($ra = $db->fetch_array($aq)){
			$article_list[] = $ra;
		}
		$r['article_list'] = $article_list;
	}
	$kb_cat[] = $r;	
}
$template_vars['kb_cat'] = $kb_cat;

$q = $db->query("SELECT * FROM ".TABLE_PREFIX."articles WHERE category=".$db->real_escape_string($cat_id)." ORDER BY date DESC");
while($r = $db->fetch_array($q)){
	$r['content'] = (strlen(strip_tags($r['content'])) > $settings['knowledgebase_articlemaxchar']?substr(strip_tags($r['content']), 0, ($settings['knowledgebase_articlemaxchar']-3)).'...':strip_tags($r['content']));
	$articles[] = $r;
}
$template_vars['articles'] = $articles;

$q = $db->query("SELECT id, title, category FROM ".TABLE_PREFIX."articles ORDER BY views DESC LIMIT {$settings['knowledgebase_mostpopulartotal']}");
while($r = $db->fetch_array($q)){
	$popular_articles[] = $r;	
}
$template_vars['popular_articles'] = $popular_articles;
$q = $db->query("SELECT id, title, category FROM ".TABLE_PREFIX."articles ORDER BY date DESC LIMIT {$settings['knowledgebase_newesttotal']}");
while($r = $db->fetch_array($q)){
	$newest_articles[] = $r;	
}
$template_vars['newest_articles'] = $newest_articles;
$template = $twig->loadTemplate('knowledgebase_preview.html');
echo $template->render($template_vars);
$db->close();
exit;
?>