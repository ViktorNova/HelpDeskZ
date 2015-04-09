<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
if($params[1] == 'edit' && is_numeric($params[2])){
	$article_id = $db->real_escape_string($params[2]);
	$article = $db->fetchRow("SELECT *, COUNT(id) AS total FROM ".TABLE_PREFIX."articles WHERE id=$article_id");
	if($article['total'] == 0){
		header('location: '.getUrl($controller,$action,array('manage')));
		exit;	
	}else{
		if($params[3] == 'remove_attachment' && is_numeric($params[4])){
			$attachment_id = $db->real_escape_string($params[4]);
			$attachment = $db->fetchRow("SELECT enc, COUNT(id) AS total FROM ".TABLE_PREFIX."attachments WHERE id=$attachment_id");
			if($attachment['total'] != 0){
				removeAttachment($attachment_id,'article');
			}
			header('location: '.getUrl($controller,$action,array('manage','edit',$article_id,'attached_removed')));
			exit;	
		}elseif($params[3] == 'update'){
			if(verifyToken('article', $input->p['csrfhash']) !== true){
					$error_msg = $LANG['CSRF_ERROR'];
			}else{
				if($input->p['title'] == ''){
					$error_msg = $LANG['ARTICLE_HAS_NOT_TITLE'];	
				}elseif($input->p['content'] == ''){
					$error_msg = $LANG['ENTER_ARTICLE_CONTENT'];	
				}elseif(!is_numeric($input->p['category'])){
					$error_msg = $LANG['SELECT_CATEGORY'];
				}else{
					$uploaddir = UPLOAD_DIR.'articles/';		
					if($_FILES['file1']['error'] == 0){
						$ext = pathinfo($_FILES['file1']['name'], PATHINFO_EXTENSION);
						$filename = md5($_FILES['file1']['name'].time()).".".$ext;
						$fileuploaded[] = array('name' => $_FILES['file1']['name'], 'filetype' => $_FILES['file1']['type'], 'enc' => $filename, 'size' => formatBytes($_FILES['file1']['size']));
						$uploadedfile = $uploaddir.$filename;
						if (!move_uploaded_file($_FILES['file1']['tmp_name'], $uploadedfile)) {
							$error_msg = $LANG['ERROR_UPLOADING_FILE'];
						}
					}
					if($_FILES['file2']['error'] == 0){
						$ext = pathinfo($_FILES['file2']['name'], PATHINFO_EXTENSION);
						$filename = md5($_FILES['file2']['name'].time()).".".$ext;
						$fileuploaded[] = array('name' => $_FILES['file2']['name'], 'filetype' => $_FILES['file2']['type'], 'enc' => $filename, 'size' => formatBytes($_FILES['file2']['size']));
						$uploadedfile = $uploaddir.$filename;
						if (!move_uploaded_file($_FILES['file2']['tmp_name'], $uploadedfile)) {
							$error_msg .= $LANG['ERROR_UPLOADING_FILE'];
						}
					}
					if($_FILES['file3']['error'] == 0){
						$ext = pathinfo($_FILES['file3']['name'], PATHINFO_EXTENSION);
						$filename = md5($_FILES['file3']['name'].time()).".".$ext;
						$fileuploaded[] = array('name' => $_FILES['file3']['name'], 'filetype' => $_FILES['file3']['type'], 'enc' => $filename, 'size' => formatBytes($_FILES['file3']['size']));
						$uploadedfile = $uploaddir.$filename;
						if (!move_uploaded_file($_FILES['file3']['tmp_name'], $uploadedfile)) {
							$error_msg .= $LANG['ERROR_UPLOADING_FILE'];
						}
					}
					if($error_msg == ''){
						$data = array('title' => $input->p['title'],
										'content' => $input->p['content'],
										'category' => $input->p['category'],
										'author' => $staff['fullname'],
										'public' => ($input->p['public'] == 1?1:0),
									);
						$db->update(TABLE_PREFIX."articles", $data, "id=$article_id");
						if(is_array($fileuploaded)){
							foreach($fileuploaded as $f){
								$data = array('name' => $f['name'], 'enc' => $f['enc'], 'filesize' => $f['size'], 'article_id' => $article_id, 'filetype' => $f['filetype']);
								$db->insert(TABLE_PREFIX."attachments", $data);
							}
						}
						header('location: '.getUrl($controller,$action,array('manage','edit',$article_id,'updated')));
						exit;
					}
				}
		
			}
		}
		$article_title = ($input->p['title'] == ''?$article['title']:$input->p['title']);
		$article_cat = (!is_numeric($input->p['category'])?$article['category']:$input->p['category']);
		$article_content = ($input->p['content'] == ''?$article['content']:$input->p['content']);

		$q = $db->query("SELECT * FROM ".TABLE_PREFIX."attachments WHERE article_id=$article_id");
		while($r = $db->fetch_array($q)){
			$attachments[] = $r;	
		}
		$template_vars['error_msg'] = $error_msg;
		$template_vars['article_id'] = $article_id;
		$template_vars['article_title'] = $article_title;
		$template_vars['article_content'] = $article_content;
		$template_vars['article_cat'] = $article_cat;
		$template_vars['article'] = $article;
		$template_vars['attachments'] = $attachments;
		$template = $twig->loadTemplate('knowledgebase_editarticle.html');
		echo $template->render($template_vars);
		$db->close();
		exit;
	}
}
if(is_numeric($input->g['cat'])){
	$whereq = "WHERE category=".$db->real_escape_string($input->g['cat']);	
}
if($params[1] == 'page'){
	$page = (!is_numeric($params[2])?1:$params[2]);
}else{
	$page = 1;	
}



if($input->p['do'] == 'update'){
	if(verifyToken('knowledgebase', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];	
	}elseif(!is_array($input->p['kb_id'])){
		$error_msg = $LANG['NO_SELECT_ARTICLE'];	
	}else{
		foreach($input->p['kb_id'] as $k){
			if(is_numeric($k)){
				$kb_id = $db->real_escape_string($k);
				if($input->p['remove'] == 1){
					$db->delete(TABLE_PREFIX."articles", "id='$kb_id'");						
					removeAttachment($kb_id,'articles');			
				}else{
					if(array_key_exists($input->p['kb_category'],$kb_category)){
						$db->query("UPDATE ".TABLE_PREFIX."articles SET category='".$db->real_escape_string($input->p['kb_category'])."' WHERE id='$kb_id'");
					}
				}
			}
		}
		header('location: '.getUrl($controller,$action,array('page',$page,$orderby,$sortby),$getvar));
		exit;
	}
}


$order_list = array('title', 'author', 'date','category','views');
$orderby = (in_array($params[3],$order_list)?$params[3]:'id');
$sortby = ($params[4] == 'asc'?'asc':'desc');
$max_results = $settings['page_size'];
$count = $db->fetchOne("SELECT COUNT(*) AS NUM FROM ".TABLE_PREFIX."articles");
$total_pages = ceil($count/$max_results);	
$page = ($page>$total_pages?$total_pages:$page);
$from = ($max_results*$page) - $max_results;
$q = $db->query("SELECT * FROM ".TABLE_PREFIX."articles {$whereq} ORDER BY {$orderby} {$sortby} LIMIT $from, $max_results");
while($r = $db->fetch_array($q)){
	$r['category_name'] = ($r['category'] == 0?$LANG['ROOT_CATEGORY']:$kb_category[$r['category']]);
	$kb_article[] = $r;	
}
$template_vars['kb_category'] = $kb_category;
$template_vars['kb_article'] = $kb_article;
$template_vars['page'] = $page;
$template_vars['orderby'] = $orderby;
$template_vars['sortby'] = $sortby;
$template_vars['getvar'] = $getvar;
$template_vars['total_pages'] = $total_pages;
$template_vars['error_msg'] = $error_msg;
$template = $twig->loadTemplate('knowledgebase_manage.html');
echo $template->render($template_vars);
$db->close();
exit;
?>