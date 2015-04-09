<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
if($params[1] == 'publish'){
	if(verifyToken('news', $input->p['csrfhash']) !== true){
			$error_msg = $LANG['CSRF_ERROR'];
	}else{
		if($input->p['title'] == ''){
			$error_msg = $LANG['ARTICLE_HAS_NOT_TITLE'];	
		}elseif($input->p['content'] == ''){
			$error_msg = $LANG['ENTER_ARTICLE_CONTENT'];	
		}else{
			$data = array('title' => $input->p['title'],
							'content' => $input->p['content'],
							'author' => $staff['fullname'],
							'date' => time(),
							'public' => ($input->p['public'] == 1?1:0),
						);
			$db->insert(TABLE_PREFIX."news", $data);
			header('location: '.getUrl($controller,$action,array('insert','published')));
			exit;
		}

	}
}
$template_vars['error_msg'] = $error_msg;
$template = $twig->loadTemplate('news_insert.html');
echo $template->render($template_vars);
$db->close();
exit;
?>