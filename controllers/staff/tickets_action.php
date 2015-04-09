<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
//Query String
$getvar = $_SERVER['QUERY_STRING'];
//Ticket Status
$ticket_status = array(1 => $LANG['OPEN'], 2 => $LANG['ANSWERED'], 3 => $LANG['AWAITING_REPLY'], 4 => $LANG['IN_PROGRESS'], 5 => $LANG['CLOSED']);
$template_vars['ticket_status'] = $ticket_status;
//Status Color
$statuscolor = array('1' => '#008000',
					'2' => '#b84764',
					'3' => '#ff8000',
					'4' => '#53a9ff',
					'5' => '#333333',						
					);

//Departments
$q = $db->query("SELECT * FROM ".TABLE_PREFIX."departments");
$departments = array();
while($r = $db->fetch_array($q)){
	if(in_array($r['id'],$staff_departments)){
		$departments[$r['id']] = $r['name'];
	}else{
		$exceptiondep_query .= " AND department_id!={$r['id']}";
	}
}
$template_vars['departments'] = $departments;
//Priority
$q = $db->query("SELECT * FROM ".TABLE_PREFIX."priority");
while($r = $db->fetch_array($q)){
	$priority[$r['id']] = $r;
}
$template_vars['priority'] = $priority;

//Filter bar
$filter_query = ($exceptiondep_query == ''?'':'WHERE '.substr($exceptiondep_query, 4));
$totalticket = $db->query("SELECT status, department_id, COUNT(id) as total FROM ".TABLE_PREFIX."tickets {$filter_query} GROUP by status, department_id");
while($r = $db->fetch_array($totalticket)){
	$total_dep[$r['department_id']][$r['status']] = $r['total'];
}
$searchurl = getUrl($controller,'tickets',null,'do=search&');
foreach($departments as $dep_id => $dep_name){
	$data = array('id' => $dep_id, 'pId' => 0, 'name' => '<strong>'.$dep_name.'</strong>', 'open' => 'true', 'isParent' => 'true','url' => $searchurl.'department_id='.$dep_id, 'target' => '_parent');
	
	$data1 = array('id' => $dep_id.'1', 'pId' => $dep_id, 'name' => $LANG['OPEN'].' '.($total_dep[$dep_id]['1'] != ''?'<span class="fancytree_descr">( '.$total_dep[$dep_id]['1'].' )</span>':''), 'url' => $searchurl.'status=1&department_id='.$dep_id, 'target' => '_parent');
	
	$data2 = array('id' => $dep_id.'2', 'pId' => $dep_id, 'name' => $LANG['AWAITING_REPLY'].' '.($total_dep[$dep_id]['3'] != ''?'<span class="fancytree_descr">( '.$total_dep[$dep_id]['3'].' )</span>':''), 'url' => $searchurl.'status=3&department_id='.$dep_id, 'target' => '_parent');
	
	$data3 = array('id' => $dep_id.'3', 'pId' => $dep_id, 'name' => $LANG['IN_PROGRESS'].' '.($total_dep[$dep_id]['4'] != ''?'<span class="fancytree_descr">( '.$total_dep[$dep_id]['4'].' )</span>':''), 'url' => $searchurl.'status=4&department_id='.$dep_id, 'target' => '_parent');
	
	$data4 = array('id' => $dep_id.'4', 'pId' => $dep_id, 'name' => $LANG['ANSWERED'].' '.($total_dep[$dep_id]['2'] != ''?'<span class="fancytree_descr">( '.$total_dep[$dep_id]['2'].' )</span>':''), 'url' => $searchurl.'status=2&department_id='.$dep_id, 'target' => '_parent');
	
	$data5 = array('id' => $dep_id.'5', 'pId' => $dep_id, 'name' => $LANG['CLOSED'].' '.($total_dep[$dep_id]['5'] != ''?'<span class="fancytree_descr">( '.$total_dep[$dep_id]['5'].' )</span>':''), 'url' => $searchurl.'status=5&department_id='.$dep_id, 'target' => '_parent');
	$filter_bar .= json_encode($data).','.json_encode($data1).','.json_encode($data2).','.json_encode($data3).','.json_encode($data4).','.json_encode($data5).',';
}
$template_vars['filter_bar'] = $filter_bar;

if($params[0] == 'view' && is_numeric($params[1])){
	include(CONTROLLERS.'staff/params/tickets_view.php');
}elseif($params[0] == 'canned'){
	include(CONTROLLERS.'staff/params/tickets_canned.php');
}

$search_query = '';
if($input->g['do'] == 'search'){
	if(array_key_exists($input->g['status'],$ticket_status)){
		$search_query .= "status='".$db->real_escape_string($input->g['status'])."' AND ";
	}else{
		$search_query .= "status!='test' AND ";
	}

	if(array_key_exists($input->g['department_id'],$departments) && in_array($input->g['department_id'], $staff_departments)){
		$search_query .= "department_id='".$db->real_escape_string($input->g['department_id'])."' AND ";
	}
	if(array_key_exists($input->g['priority_id'],$priority)){
		$search_query .= "priority_id='".$db->real_escape_string($input->g['priority_id'])."' AND ";
	}
	if(!empty($input->g['date_from'])){
		$daterange = daterange($input->g['date_from']);
		if($daterange != ''){
			$date_from = $daterange[0];
			$search_query .= "date>='$date_from' AND ";
		}	
	}
	if(!empty($input->g['date_to'])){
		$daterange = daterange($input->g['date_to']);
		if($daterange != ''){
			$date_to = $daterange[1];	
			$search_query .= "date<='$date_to' AND ";
		}	
	}
	if(!empty($input->g['criteria_value'])){
		switch($input->g['criteria']){
			case 'code':
			$search_query .= "code='".$db->real_escape_string($input->g['criteria_value'])."' AND ";
			break;
			case 'subject':
			$search_query .= "subject LIKE '%".$db->real_escape_string($input->g['criteria_value'])."%' AND ";
			break;
			case 'name':
			$search_query .= "fullname LIKE '%".$db->real_escape_string($input->g['criteria_value'])."%' AND ";
			break;
			case 'email':
			$search_query .= "email='".$db->real_escape_string($input->g['criteria_value'])."' AND ";
			break;
		}
	}
}

if($search_query){
	$search_query = substr($search_query,0,-5);	
}else{
	$search_query = "status!='5' AND status!='2'";
}
			
if($params[0] == 'page'){
	$page = (!is_numeric($params[1])?1:$params[1]);
}else{
	$page = 1;	
}

$order_list = array('code', 'subject', 'last_replier', 'replies', 'priority_id', 'last_update', 'department_id', 'status');
$orderby = (in_array($params[2],$order_list)?$params[2]:'last_update');
$sortby = ($params[3] == 'asc'?'asc':'desc');

if($input->p['do'] == 'update'){
	if(verifyToken('tickets', $input->p['csrfhash']) !== true){
		$error_msg = $LANG['CSRF_ERROR'];	
	}elseif(!is_array($input->p['ticket_id'])){
		$error_msg = $LANG['NO_SELECT_TICKET'];	
	}else{
		foreach($input->p['ticket_id'] as $k){
			if(is_numeric($k)){
				$ticketid = $db->real_escape_string($k);
				if($input->p['remove'] == 1){
					$db->delete(TABLE_PREFIX."tickets", "id='$ticketid'");						
					$db->delete(TABLE_PREFIX."tickets_messages", "ticket_id='$ticketid'");		
					removeAttachment($ticketid,'tickets');			
				}else{
					if(array_key_exists($input->p['department'],$departments)){
						$db->query("UPDATE ".TABLE_PREFIX."tickets SET department_id='".$db->real_escape_string($input->p['department'])."' WHERE id='$ticketid'");
					}
					if(array_key_exists($input->p['status'],$statuscolor)){
						$db->query("UPDATE ".TABLE_PREFIX."tickets SET status='".$db->real_escape_string($input->p['status'])."' WHERE id='$ticketid'");
					}
					if(array_key_exists($input->p['priority'],$priority)){
						$db->query("UPDATE ".TABLE_PREFIX."tickets SET priority_id='".$db->real_escape_string($input->p['priority'])."' WHERE id='$ticketid'");
					}
				}
			}
		}
		header('location: '.getUrl($controller,$action,array('page',$page,$orderby,$sortby),$getvar));
		exit;
	}
}


$max_results = $settings['page_size'];
$count = $db->fetchOne("SELECT COUNT(*) AS NUM FROM ".TABLE_PREFIX."tickets WHERE {$search_query} {$exceptiondep_query}");
$total_pages = ceil($count/$max_results);	
$page = ($page>$total_pages?$total_pages:$page);
$from = ($max_results*$page) - $max_results;
$q = $db->query("SELECT * FROM ".TABLE_PREFIX."tickets WHERE {$search_query} {$exceptiondep_query} ORDER BY {$orderby} {$sortby} LIMIT $from, $max_results");
$todayis = time();

while($r = $db->fetch_array($q)){
	$trzebra = ($trzebra == ''?1:'');
	$timeleft = $todayis-$r['last_update'];
	$overdue = ($timeleft >=($settings['overdue_time']*60*60) && ($r['status'] == '1' || $r['status'] == '2' || $r['status'] == '4')?1:0);
	if($overdue == 1){
		$colortime = '#ff0000';
		$tdclass = 'troverdue';
	}else{
		$colortime = '#8bb467';
		$tdclass = ($trzebra == 1?'trzebra':'');					
	}
	
	$days = floor($timeleft/86400);
	$hours = floor($timeleft/3600)-($days*24);
	$minutes = floor($timeleft/60)-($days*24*60)-($hours*60);
	$seconds = $timeleft-($days*24*60*60)-($hours*60*60)-($minutes*60);
	$r['lastupdate'] = ($days?$days.'d ':'').($hours?$hours.'h ':'').($minutes?$minutes.'m ':'').($days?'':$seconds.'s');
	$r['priority'] = $priority[$r['priority_id']]['name'];
	$r['priority_color'] = $priority[$r['priority_id']]['color'];
	$r['color_time'] = $colortime;
	$r['td_class'] = $tdclass;
	$tickets[] = $r;
}
$template_vars['total_tickets'] = $count;
$template_vars['tickets'] = $tickets;
$template_vars['total_pages'] = $total_pages;
$template_vars['page'] = $page;
$template_vars['orderby'] = $orderby;
$template_vars['sortby'] = $sortby;
$template_vars['getvar'] = $getvar;
$template_vars['error_msg'] = $error_msg;
$template_vars['statuscolor'] = $statuscolor;
$template = $twig->loadTemplate('tickets.html');
echo $template->render($template_vars);
$db->close();
exit;
?>