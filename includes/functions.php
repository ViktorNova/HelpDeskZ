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
function daterange($date){ //mm/dd/yyyy
	$date1e = explode("/",$date);
	$daterange = array();
	$date_regex = '#^(0[1-9]|1[012])[\/\/.](0[1-9]|[12][0-9]|3[01])[\/\/.](19|20)\d\d$#';
	if (preg_match($date_regex, $date)){
		$daterange[0] = mktime(0,0,0,$date1e[0],$date1e[1],$date1e[2]);
		$daterange[1] = mktime(23,59,59,$date1e[0],$date1e[1],$date1e[2]);
		return $daterange;	
	}
}
function error_message($msg){
	echo '<div class="error_box">'.$msg.'</div>';
}	
function success_message($msg){
	echo '<div class="success_box">'.$msg.'</div>';
}
function getToken($token_name){
	$newtoken = 1;	
	if(isset($_SESSION['token'][$token_name]['token'])) {
		$token_age = time() - $_SESSION['token'][$token_name]['time'];
		if($token_age < 600){
			$newtoken = 0;
			$token = $_SESSION['token'][$token_name]['token'];
		}
	}
	if($newtoken == 1){
		$token = md5(uniqid(rand(), TRUE));
		$token_time = time();
		$_SESSION['token'][$token_name] = $token;
		$_SESSION['token'][$token_name] = array('token'=>$token, 'time'=>$token_time);
	}
	return $token;
}
function verifyToken($token_name, $token){
   if(!isset($_SESSION['token'][$token_name])) {
       return false;
   }
   if ($_SESSION['token'][$token_name]['token'] != $token) {
       return false;
   }
   $token_age = time() - $_SESSION['token'][$token_name]['time'];
   if($token_age >= 600){
	  unset($_SESSION['token'][$token_name]);
	  return false;
   }
   return true;
}
function validateEmail($email) {
	if(!preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]{2,6}$/i", $email)) {
		return false;
	}else {
		return true;
	}
}
function encrypt($string){
	$salt = 'WEujixru894SD41';
	$key = md5($salt);
	$iv = md5(md5($key));
	$output = mcrypt_encrypt(MCRYPT_RIJNDAEL_256, md5($key), $string, MCRYPT_MODE_CBC, $iv);
	$output = base64_encode($output);
	return $output;
}
function decrypt($string){
	$salt = 'WEujixru894SD41';
	$key = md5($salt);
	$iv = md5(md5($key));
	$output = mcrypt_decrypt(MCRYPT_RIJNDAEL_256, md5($key), base64_decode($string), MCRYPT_MODE_CBC, $iv);
	$output = rtrim($output, "");
	return $output;
}

function removeAttachment($id,$type=null){
	global $db;
	if($type == 'ticket'){
		$attachment = $db->fetchOne("SELECT enc FROM ".TABLE_PREFIX."attachments WHERE id=".$id);
		$db->delete("attachments", "id=".$id);
		$dirfile = UPLOAD_DIR.'tickets/'.$attachment;		
		@unlink($dirfile);
	}elseif($type == 'article'){
		$attachment = $db->fetchOne("SELECT enc FROM ".TABLE_PREFIX."attachments WHERE id=".$id);
		$db->delete("attachments", "id=".$id);
		$dirfile = UPLOAD_DIR.'articles/'.$attachment;		
		@unlink($dirfile);
	}elseif($type == 'msg'){
		$q = $db->query("SELECT id, enc	FROM ".TABLE_PREFIX."attachments WHERE msg_id=".$id);
		while($r = $db->fetch_array($q)){
			$dirfile = UPLOAD_DIR.'tickets/'.$r['enc'];
			@unlink($dirfile);
			$db->delete(TABLE_PREFIX."attachments", "msg_id=".$id);
		}
	}elseif($type == 'tickets'){
		$q = $db->query("SELECT id, enc	FROM ".TABLE_PREFIX."attachments WHERE ticket_id=".$id);
		while($r = $db->fetch_array($q)){
			$dirfile = UPLOAD_DIR.'tickets/'.$r['enc'];
			@unlink($dirfile);
			$db->delete(TABLE_PREFIX."attachments", "ticket_id=".$id);
		}
	}elseif($type == 'articles'){
		$q = $db->query("SELECT id, enc	FROM ".TABLE_PREFIX."attachments WHERE article_id=".$id);
		while($r = $db->fetch_array($q)){
			$dirfile = UPLOAD_DIR.'articles/'.$r['enc'];
			@unlink($dirfile);
			$db->delete(TABLE_PREFIX."attachments", "article_id=".$id);
		}
	}
	
}

function verifyAttachment($filename){
	global $db;	
	$namepart = explode('.', $filename['name']);
	$totalparts = count($namepart)-1;
	$file_extension = $namepart[$totalparts];
	if(!ctype_alnum($file_extension)){
		$msg_code = 1;
	}else{
		$filetype = $db->fetchRow("SELECT count(id) AS total, size FROM ".TABLE_PREFIX."file_types WHERE type='".$db->real_escape_string($file_extension)."'");
		if($filetype['total'] == 0){
			$msg_code = 2;
		}elseif($filename['size'] > $filetype['size'] && $filetype['size'] > 0){
			$msg_code = 3;
			$misc = formatBytes($filetype['size']);
		}else{	
			$msg_code = 0;
		}
	}
	$data = array('msg_code' => $msg_code, 'msg_extra' => $misc);
	return $data;
}

function strtourl($str) {
	$str = html_entity_decode($str);
	$clean = iconv('UTF-8', 'ASCII//TRANSLIT', $str);
	$clean = preg_replace("/[^a-zA-Z0-9\/_|\" -]/", '', $clean);
	$clean = strtolower(trim($clean, '-'));
	$clean = preg_replace("/[\/_| -]+/", '-', $clean);
	return $clean;
}

function formatBytes($bytes, $precision = 2) { 
    $base = log($bytes) / log(1024);
    $suffixes = array('B', 'KB', 'MB');

    return round(pow(1024, $base - floor($base)), $precision) ." ". $suffixes[floor($base)]; 
} 
function getUrl($controller=null,$action=null,$params=null,$getvar=null){
	global $settings;
	if($controller == ''){
		$url = $settings['site_url'];
	}else{
		if($action != ''){
			if(is_array($params)){
				foreach($params as $v){
					if($settings['permalink'] == 1){
						$param .= '/'.$v;
					}else{
						$param .= '&param[]='.$v;
					}
				}
			}elseif($params != ''){
				if($settings['permalink'] == 1){
					$param .= '/'.$params;
				}else{
					$param .= '&param[]='.$params;
				}
			}
		}
		if($settings['permalink'] == 1){
			$url = $settings['site_url'].'/'.$controller.($action != ''?'/'.$action.$param:'');
			if($getvar){
				$url = $url.'?'.$getvar;
			}
		}else{
			$url = $settings['site_url'].'/?v='.$controller.($action != ''?'&action='.$action.$param:'');
			if($getvar){
				$url = $url.'&'.$getvar;
			}
		}
	}
	return $url;
}
function clientLogout(){
	setcookie('usrhash','',time()-5,'/');
	unset($_SESSION['user']);
	header('location: '.getUrl());
	exit;	
}
function staffLogout(){
	global $controller;
	setcookie('stfhash','',time()-5,'/');
	unset($_SESSION['staff']);
	header('location: '.getUrl($controller));
	exit;	
}
function displayDate($date){
	global $settings;
	$dateformat = date("{$settings['date_format']}", $date);
	return $dateformat;
}
function ticketpaginator($total_pages,$page,$url){
	  if($total_pages && $total_pages > 1){
		echo '<div class="paginator">';
			
			if($page != 1){
				if($page-2>1){
				echo '<a href="'.str_replace('#page#',1,$url).'">&laquo;</a> ';
				}
				echo '<a href="'.str_replace('#page#',($page-1),$url).'">&lt;</a> ';
			}
		  for($i=($page>3?$page-2:1);$i<=($page+2>=$total_pages?$total_pages:$page+2);$i++){
				if($i == $page){
					echo '<span class="page_current">'.$i.'</span> ';
				}else{
				echo '<a href="'.str_replace('#page#',$i,$url).'">'.$i.'</a> ';
				}
		  }
			if($page != $total_pages){
				echo '<a href="'.str_replace('#page#',($page+1),$url).'">&gt;</a> ';
				if($page+2<$total_pages){
				echo '<a href="'.str_replace('#page#',$total_pages,$url).'">&raquo;</a> ';
				}
			}
		echo '</div>';
	  }
}
?>