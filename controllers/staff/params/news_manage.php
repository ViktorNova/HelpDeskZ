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
if($params[1] == 'edit' && is_numeric($params[2])){
	$news_id = $db->real_escape_string($params[2]);
	$news = $db->fetchRow("SELECT *, COUNT(id) AS total FROM ".TABLE_PREFIX."news WHERE id=$news_id");
	if($news['total'] == 0){
		header('location: '.getUrl($controller,$action,array('manage')));
		exit;	
	}else{
		if($params[3] == 'update'){
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
									'public' => ($input->p['public'] == 1?1:0),
								);
					$db->update(TABLE_PREFIX."news", $data, "id=$news_id");
					header('location: '.getUrl($controller,$action,array('manage','edit',$news_id,'updated')));
					exit;
				}
		
			}
		}
		
		$news_title = ($input->p['title'] == ''?$news['title']:$input->p['title']);
		$news_content = ($input->p['content'] == ''?$news['content']:$input->p['content']);
		$template_vars['news'] = $news;
		$template_vars['news_id'] = $news_id;
		$template_vars['news_title'] = $news_title;
		$template_vars['news_content'] = $news_content;
		$template_vars['error_msg'] = $error_msg;
		$template = $twig->loadTemplate('news_edit.html');
		echo $template->render($template_vars);
		$db->close();
		exit;
	}
}
if($input->p['do'] == 'update'){
	if(verifyToken('news', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];	
	}elseif(!is_array($input->p['news_id'])){
		$error_msg = $LANG['NO_SELECT_TICKET'];	
	}else{
		foreach($input->p['news_id'] as $k){
			if(is_numeric($k)){
				$news_id = $db->real_escape_string($k);
				if($input->p['remove'] == 1){
					$db->delete(TABLE_PREFIX."news", "id='$news_id'");	
				}
			}
		}
		header('location: '.getUrl($controller,$action,array('page',$page,$orderby,$sortby),$getvar));
		exit;
	}
}
if($params[1] == 'page'){
	$page = (!is_numeric($params[2])?1:$params[2]);
}else{
	$page = 1;	
}
$order_list = array('title', 'author', 'date', 'public');
$orderby = (in_array($params[3],$order_list)?$params[3]:'date');
$sortby = ($params[4] == 'asc'?'asc':'desc');
$max_results = $settings['page_size'];
$count = $db->fetchOne("SELECT COUNT(*) AS NUM FROM ".TABLE_PREFIX."news");
$total_pages = ceil($count/$max_results);	
$page = ($page>$total_pages?$total_pages:$page);
$from = ($max_results*$page) - $max_results;
$q = $db->query("SELECT * FROM ".TABLE_PREFIX."news {$whereq} ORDER BY {$orderby} {$sortby} LIMIT $from, $max_results");
while($r = $db->fetch_array($q)){
	$news_result[] = $r;	
}
$template_vars['news_result'] = $news_result;
$template_vars['orderby'] = $orderby;
$template_vars['sortby'] = $sortby;
$template_vars['page'] = $page;
$template_vars['total_pages'] = $total_pages;
$template_vars['getvar'] = $getvar;
$template_vars['error_msg'] = $error_msg;
$template = $twig->loadTemplate('news_manage.html');
echo $template->render($template_vars);
$db->close();
exit;
?>