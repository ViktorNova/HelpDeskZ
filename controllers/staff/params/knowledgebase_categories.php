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




if($params[1] == 'GetKBCategoryForm'){
	if(is_numeric($params[2]) && $params[2] != 0){
		$category = $db->fetchRow("SELECT *, COUNT(id) AS total FROM ".TABLE_PREFIX."knowledgebase_category WHERE id=".$db->real_escape_string($params[2]));
		$template_vars['category'] = $category;
		if($category['total'] == 0){
			die($LANG['ERROR_RETRIEVING_DATA']);	
		}
		$form_action = getUrl($controller,$action,array('categories','editData'));
		$cat_position = $category['position'];
	}else{
		$form_action = getUrl($controller,$action,array('categories','newCategory'));
		$cat_position = $db->fetchOne("SELECT position FROM ".TABLE_PREFIX."knowledgebase_category ORDER BY position DESC LIMIT 1")+1;
	}
	$template_vars['cat_position'] = $cat_position;
	
	$template_vars['form_action'] = $form_action;
	$template = $twig->loadTemplate('form_kbcategory.html');
	echo $template->render($template_vars);
	$db->close();
	exit;
}elseif($params[1] == 'editData'){
	if(is_numeric($input->p['catID'])){
		$chk = $db->fetchRow("SELECT COUNT(id) AS total, parent FROM ".TABLE_PREFIX."knowledgebase_category WHERE id=".$db->real_escape_string($input->p['catID']));
		if($chk['total'] != 0){
			$data = array('name' => $input->p['title'],
						'parent' => ($input->p['parent'] == $input->p['catID']?$chk['parent']:$input->p['parent']),
						'position' => $input->p['position'],
						'public' => ($input->p['public'] == '1'?1:0),
						);
			$db->update(TABLE_PREFIX."knowledgebase_category", $data, "id=".$db->real_escape_string($input->p['catID']));
		}
		header('location: '.getUrl($controller, $action, array('categories','category_updated')));
		exit;	
	}
}elseif($params[1] == 'newCategory'){
	if($input->p['title'] == ''){
		$error_msg = $LANG['ENTER_THE_TITLE'];	
	}else{
		$data = array('name' => $input->p['title'],
						'position' => $input->p['position'],
						'parent' => $input->p['parent'],
						'public' => $input->p['public'],
						);
		$db->insert(TABLE_PREFIX."knowledgebase_category", $data);
		header('location: '.getUrl($controller, $action, array('categories','category_added')));
		exit;	
	}
}elseif($params[1] == 'RemoveData'){
	if(is_numeric($params[2])){
		$db->delete(TABLE_PREFIX."knowledgebase_category", "id=".$db->real_escape_string($params[2]));
		$db->delete(TABLE_PREFIX."knowledgebase_category", "parent=".$db->real_escape_string($params[2]));
	}
	header('location: '.getUrl($controller, $action, array('categories','category_removed')));
	exit;	
}

$newarticleurl = getUrl($controller,$action,array('article',''));
$query = $db->query("SELECT * FROM ".TABLE_PREFIX."knowledgebase_category ORDER BY parent ASC, position ASC");
$data = array('id' => 0, 'pId' => 0, 'name' => '<span title="'.$LANG['ROOT_CATEGORY'].'">'.$LANG['ROOT_CATEGORY'].'</span> <span class="folder_add" title="'.$LANG['INSERT_CATEGORY'].'" onclick="showKBCategoryForm(0);"></span> <span class="article_add" title="'.$LANG['INSERT_ARTICLE'].'" onclick="location.href=\''.$newarticleurl.'0\';"></span>', 'open' => 'true', 'isParent' => 'true'); 
$category_nodes = json_encode($data).',';
while($r = $db->fetch_array($query)){
	$nocheck =  ($r['parent'] == 0?'true':'false');
	$data = array('id' => $r['id'], 'pId' => $r['parent'], 'name' => '<span  onclick="showKBCategoryForm('.$r['id'].', '.$r['parent'].');" title="'.htmlspecialchars($r['name']).'">'.htmlspecialchars($r['name']).'</span> <span class="folder_add" title="'.$LANG['INSERT_CATEGORY'].'" onclick="showKBCategoryForm(0,'.$r['id'].');"></span> <span class="article_add" title="'.$LANG['INSERT_ARTICLE'].'" onclick="location.href=\''.$newarticleurl.$r['id'].'\';"></span>', 'open' => 'true', 'isParent' => 'true');
	$category_nodes .= json_encode($data).',';
}
$template_vars['error_msg'] = $error_msg;
$template_vars['category_nodes'] = $category_nodes;
$template = $twig->loadTemplate('knowledgebase_categories.html');
echo $template->render($template_vars);
$db->close();
exit;
?>