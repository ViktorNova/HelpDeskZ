<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
if($params[1] == 'publish'){
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
				$fileuploaded[] = array('name' => $_FILES['file1']['name'], 'enc' => $filename, 'size' => formatBytes($_FILES['file1']['size']), 'filetype' => $_FILES['file1']['type']);
				$uploadedfile = $uploaddir.$filename;
				if (!move_uploaded_file($_FILES['file1']['tmp_name'], $uploadedfile)) {
					$error_msg = $LANG['ERROR_UPLOADING_FILE'];
				}
			}
			if($_FILES['file2']['error'] == 0){
				$ext = pathinfo($_FILES['file2']['name'], PATHINFO_EXTENSION);
				$filename = md5($_FILES['file2']['name'].time()).".".$ext;
				$fileuploaded[] = array('name' => $_FILES['file2']['name'], 'enc' => $filename, 'size' => formatBytes($_FILES['file2']['size']), 'filetype' => $_FILES['file2']['type']);
				$uploadedfile = $uploaddir.$filename;
				if (!move_uploaded_file($_FILES['file2']['tmp_name'], $uploadedfile)) {
					$error_msg .= $LANG['ERROR_UPLOADING_FILE'];
				}
			}
			if($_FILES['file3']['error'] == 0){
				$ext = pathinfo($_FILES['file3']['name'], PATHINFO_EXTENSION);
				$filename = md5($_FILES['file3']['name'].time()).".".$ext;
				$fileuploaded[] = array('name' => $_FILES['file3']['name'], 'enc' => $filename, 'size' => formatBytes($_FILES['file3']['size']), 'filetype' => $_FILES['file3']['type']);
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
								'date' => time(),
								'public' => ($input->p['public'] == 1?1:0),
							);
				$db->insert(TABLE_PREFIX."articles", $data);
				$article_id = $db->lastInsertId();
				if(is_array($fileuploaded)){
					foreach($fileuploaded as $f){
						$data = array('name' => $f['name'], 'enc' => $f['enc'], 'filesize' => $f['size'], 'article_id' => $article_id, 'filetype' => $f['filetype']);
						$db->insert(TABLE_PREFIX."attachments", $data);
					}
				}
				header('location: '.getUrl($controller,$action,array('article','published')));
				exit;
			}
		}

	}
}


$template_vars['error_msg'] = $error_msg;
$template = $twig->loadTemplate('knowledgebase_article.html');
echo $template->render($template_vars);
$db->close();
exit;
?>