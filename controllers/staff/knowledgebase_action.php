<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
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