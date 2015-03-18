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
$getvar = $_SERVER['QUERY_STRING'];
/* Filter bar */
$totalartcicle = $db->fetchOne("SELECT COUNT(id) AS NUM FROM ".TABLE_PREFIX."articles WHERE category=0");
$data = array('id' => 0, 'pId' => 0, 'name' => '<span title="'.$LANG['ROOT_CATEGORY'].'">'.$LANG['ROOT_CATEGORY'].' '.($totalartcicle > 0?'<span class="fancytree_descr">('.$totalartcicle.')<span>':'').'<span>', 'open' => 'true', 'isParent' => 'true','url' => getUrl($controller,$action,array('manage'),'cat=0'), 'target' => '_parent'); 
$filter_bar = json_encode($data).',';
$q = $db->query("SELECT * FROM ".TABLE_PREFIX."knowledgebase_category ORDER BY parent ASC, position ASC");
while($r = $db->fetch_array($q)){
	$kb_category[$r['id']] = $r['name'];
	$totalartcicle = $db->fetchOne("SELECT COUNT(id) AS NUM FROM ".TABLE_PREFIX."articles WHERE category=".$r['id']);
	$nocheck =  ($r['parent'] == 0?'true':'false');
	$data = array('id' => $r['id'], 'pId' => $r['parent'], 'name' => '<span title="'.htmlspecialchars($r['name']).'">'.(strlen(htmlspecialchars($r['name']))>20?substr(htmlspecialchars($r['name']),0,17).'...':htmlspecialchars($r['name'])).' '.($totalartcicle > 0?'<span class="fancytree_descr">('.$totalartcicle.')<span>':'').'<span>', 'open' => 'true', 'isParent' => 'true','url' => getUrl($controller,$action,array('manage'),'cat='.$r['id']), 'target' => '_parent'); 
	$filter_bar .= json_encode($data).',';
}
$template_vars['filter_bar'] = $filter_bar;

function display_parent_cats($id, $list_children){
	if(is_array($list_children)){
		if(array_key_exists($id, $list_children)){
			foreach($list_children[$id] as $k){
				$k['name'] = '- '.$k['name'];
				$vars[] = $k;
				$vars2 = display_parent_cats($k['id'], $list_children);
				if(is_array($vars2)){
					foreach($vars2 as $m){
						$m['name'] = '- '.$m['name'];
						$vars[] = $m;
					}
				}
			}
			return $vars;
		}else{
			return false;	
		}
	}
}
$query = $db->query("SELECT * FROM ".TABLE_PREFIX."knowledgebase_category ORDER BY parent ASC, position ASC");
while($r = $db->fetch_array($query)){
	if($r['parent'] == 0){
		$list[$r['id']] = $r;	
	}else{
		$list_children[$r['parent']][] = $r;
	}
}
if(is_array($list)){
	foreach($list as $k => $v){
		$vars[] = $v;	
		$vars2 = display_parent_cats($k, $list_children);
		if(is_array($vars2)){
			foreach($vars2 as $m){
				$vars[] = $m;
			}
		}
	}
}
$selector = $vars;
$template_vars['selector'] = $selector;

if($params[0] == 'categories'){
	include(CONTROLLERS.'staff/params/knowledgebase_categories.php');
}elseif($params[0] == 'manage'){
	include(CONTROLLERS.'staff/params/knowledgebase_manage.php');	
}elseif($params[0] == 'article'){
	include(CONTROLLERS.'staff/params/knowledgebase_insertarticle.php');
}elseif($params[0] == 'preview'){
	include(CONTROLLERS.'staff/params/knowledgebase_preview.php');
}else{
	header('location: '.getUrl($controller,$action,array('manage')));
	exit;
}
?>