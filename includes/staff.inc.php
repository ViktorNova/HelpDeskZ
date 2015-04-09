<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.0
 */
$staff_status = 0;
if(is_array($_SESSION['staff'])){
	$staff = $db->fetchRow("SELECT * FROM ".TABLE_PREFIX."staff WHERE id=".$db->real_escape_string($_SESSION['staff']['id']));
	if(is_array($staff) && $staff['username'] == $_SESSION['staff']['username'] && $staff['password'] == $_SESSION['staff']['password']){
		$staff_status = 1;
	}else{
		staffLogout();
	}
}elseif(isset($_COOKIE['stfhash'])){
	$data = decrypt($_COOKIE['stfhash']);
	$data = unserialize($data);
	if(is_array($data) && is_numeric($data['expires']) && $data['expires'] > time()){
		$staff = $db->fetchRow("SELECT * FROM ".TABLE_PREFIX."staff WHERE id=".$db->real_escape_string($data['id']));
		if(is_array($staff) && $staff['username'] == $data['username'] && $staff['password'] == $data['password']){
			$_SESSION['staff']['id'] = $staff['id'];
			$_SESSION['staff']['username'] = $staff['username'];
			$_SESSION['staff']['password'] = $staff['password'];
			$staff_status = 1;
		}else{
			staffLogout();
		}
	}else{
		staffLogout();
	}
}
if($staff_status == 1){
	$staff_departments = unserialize($staff['department']);
	$staff_departments = (is_array($staff_departments)?$staff_departments:array());	
}

//Autoclose Ticket
if($_SESSION['cron'] < time()){
	$dateleft = time() - (60*60*$settings['closeticket_time']);
	$db->query("UPDATE ".TABLE_PREFIX."tickets SET status=5 WHERE status=2 AND last_update<=$dateleft");
	$next_cron = time()+7200;
	$_SESSION['cron'] = $next_cron;

}
include(INCLUDES.'language/staff/'.$settings['staff_language'].'.php');

/* Template Loader */
include(INCLUDES.'Twig/Autoloader.php');
Twig_Autoloader::register();
$loader = new Twig_Loader_Filesystem(ROOTPATH.'views/staff');
$twig = new Twig_Environment($loader);
$twig->addGlobal('controller', $controller);
$twig->addGlobal('action', $action);
$twig->addGlobal('params', $params);
$twig->addGlobal('settings', $settings);
$twig->addGlobal('LANG', $LANG);
$twig->addGlobal('input', $input);
$twig->addGlobal('staff', $staff);
$twig->addFunction('success_message', new Twig_Function_Function('success_message'));
$twig->addFunction('getUrl', new Twig_Function_Function('getUrl'));
$twig->addFunction('getToken', new Twig_Function_Function('getToken'));
$twig->addFunction('error_message', new Twig_Function_Function('error_message'));
$twig->addFunction('displayDate', new Twig_Function_Function('displayDate'));
$twig->addFunction('ticketpaginator', new Twig_Function_Function('ticketpaginator'));
$twig->addFunction('formatBytes', new Twig_Function_Function('formatBytes'));
$twig->addFilter('is_array', new Twig_Filter_Function('is_array'));
$twig->addFilter('is_numeric', new Twig_Filter_Function('is_numeric'));
$twig->addFilter('print_r', new Twig_Filter_Function('print_r'));
$template_vars = array();
?>