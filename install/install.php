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
error_reporting(E_ALL & ~E_NOTICE);
session_start();
define('HELPDESKZ_VERSION' , '1.0');
define('HELPDESKZ_PATH', dirname(dirname(__FILE__)).'/');
require_once HELPDESKZ_PATH.'includes/classes/classMysql.php';
require_once HELPDESKZ_PATH.'includes/classes/classMysqli.php';
require_once HELPDESKZ_PATH.'includes/classes/classInput.php';
$input = new Input_Cleaner();
function helpdeskz_getQuery($db_prefix, $admin_user, $admin_password){
	$query = array();
	$query[] = "CREATE TABLE `".$db_prefix."articles` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `title` varchar(200) NOT NULL,
	  `content` text,
	  `category` int(11) DEFAULT '0',
	  `author` varchar(250) NOT NULL,
	  `date` int(11) NOT NULL,
	  `views` int(11) NOT NULL DEFAULT '0',
	  `public` int(1) NOT NULL DEFAULT '1',
	  PRIMARY KEY (`id`),
	  KEY `category` (`category`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."attachments` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(200) NOT NULL,
	  `enc` varchar(200) NOT NULL,
	  `filetype` varchar(200) DEFAULT NULL,
	  `article_id` int(11) NOT NULL DEFAULT '0',
	  `ticket_id` int(11) NOT NULL DEFAULT '0',
	  `msg_id` int(11) NOT NULL DEFAULT '0',
	  `filesize` varchar(100) DEFAULT NULL,
	  PRIMARY KEY (`id`),
	  KEY `article_id` (`article_id`),
	  KEY `ticket_id` (`ticket_id`),
	  KEY `msg_id` (`msg_id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."canned_response` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `title` varchar(255) DEFAULT NULL,
	  `message` text,
	  `position` int(11) NOT NULL DEFAULT '1',
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."custom_fields` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `type` varchar(100) NOT NULL,
	  `title` varchar(250) NOT NULL,
	  `value` text,
	  `required` int(1) NOT NULL DEFAULT '0',
	  `display` int(11) NOT NULL DEFAULT '1',
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."departments` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `dep_order` int(11) NOT NULL DEFAULT '0',
	  `name` varchar(255) NOT NULL,
	  `type` int(2) NOT NULL DEFAULT '0',
	  `autoassign` int(1) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."emails` (
	  `id` varchar(255) NOT NULL,
	  `orderlist` smallint(2) NOT NULL,
	  `name` varchar(255) NOT NULL,
	  `subject` varchar(255) NOT NULL,
	  `message` text NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."error_log` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `error` text,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."file_types` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `type` varchar(10) DEFAULT NULL,
	  `size` varchar(100) NOT NULL DEFAULT '0',
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."knowledgebase_category` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(200) NOT NULL,
	  `position` int(11) NOT NULL,
	  `parent` int(11) NOT NULL DEFAULT '0',
	  `public` int(2) NOT NULL DEFAULT '1',
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."login_attempt` (
	  `ip` varchar(200) NOT NULL,
	  `attempts` int(2) NOT NULL DEFAULT '0',
	  `date` int(11) NOT NULL DEFAULT '0',
	  UNIQUE KEY `ip` (`ip`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."login_log` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `date` int(11) NOT NULL,
	  `staff_id` int(11) NOT NULL DEFAULT '0',
	  `username` varchar(100) NOT NULL,
	  `fullname` varchar(255) NOT NULL,
	  `ip` varchar(255) NOT NULL,
	  `agent` varchar(255) NOT NULL,
	  PRIMARY KEY (`id`),
	  KEY `date` (`date`),
	  KEY `staff_id` (`staff_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."news` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `title` varchar(200) NOT NULL,
	  `content` text,
	  `author` varchar(250) NOT NULL,
	  `date` int(11) NOT NULL,
	  `public` int(1) NOT NULL DEFAULT '1',
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."pages` (
	  `id` varchar(255) NOT NULL,
	  `title` varchar(255) DEFAULT NULL,
	  `content` text,
	  UNIQUE KEY `home` (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."priority` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `name` varchar(255) NOT NULL,
	  `color` varchar(10) NOT NULL DEFAULT '#000000',
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."settings` (
	  `field` varchar(255) DEFAULT NULL,
	  `value` varchar(255) DEFAULT NULL,
	  UNIQUE KEY `field` (`field`)
	) ENGINE=MyISAM DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."staff` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `username` varchar(255) NOT NULL,
	  `password` varchar(255) NOT NULL,
	  `fullname` varchar(100) NOT NULL,
	  `email` varchar(255) DEFAULT NULL,
	  `login` int(11) NOT NULL DEFAULT '0',
	  `last_login` int(11) NOT NULL DEFAULT '0',
	  `department` text,
	  `timezone` varchar(255) DEFAULT NULL,
	  `signature` mediumtext,
	  `avatar` varchar(200) DEFAULT NULL,
	  `admin` int(1) NOT NULL DEFAULT '0',
	  `status` enum('Enable','Disable') NOT NULL DEFAULT 'Enable',
	  PRIMARY KEY (`id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."tickets` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `code` varchar(255) NOT NULL,
	  `department_id` int(11) NOT NULL DEFAULT '0',
	  `priority_id` int(11) NOT NULL DEFAULT '0',
	  `user_id` int(11) NOT NULL DEFAULT '0',
	  `fullname` varchar(255) NOT NULL,
	  `email` varchar(255) NOT NULL,
	  `subject` varchar(255) NOT NULL,
	  `api_fields` text,
	  `date` int(11) NOT NULL DEFAULT '0',
	  `last_update` int(11) NOT NULL DEFAULT '0',
	  `status` smallint(2) NOT NULL DEFAULT '1',
	  `previewcode` varchar(12) DEFAULT NULL,
	  `replies` int(11) NOT NULL DEFAULT '0',
	  `last_replier` varchar(255) DEFAULT NULL,
	  `custom_vars` text,
	  PRIMARY KEY (`id`),
	  KEY `code` (`code`)
	) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."tickets_messages` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `ticket_id` int(11) NOT NULL DEFAULT '0',
	  `date` int(11) NOT NULL DEFAULT '0',
	  `customer` int(2) NOT NULL DEFAULT '1',
	  `name` varchar(255) DEFAULT NULL,
	  `message` text,
	  `ip` varchar(255) DEFAULT NULL,
	  `email` varchar(200) DEFAULT NULL,
	  PRIMARY KEY (`id`),
	  KEY `ticket_id` (`ticket_id`)
	) ENGINE=MyISAM  DEFAULT CHARSET=latin1;";
	$query[] = "CREATE TABLE `".$db_prefix."users` (
	  `id` int(11) NOT NULL AUTO_INCREMENT,
	  `salutation` int(1) NOT NULL DEFAULT '0',
	  `fullname` varchar(250) NOT NULL,
	  `email` varchar(250) NOT NULL,
	  `password` varchar(150) NOT NULL,
	  `timezone` varchar(200) DEFAULT NULL,
	  `status` int(1) NOT NULL DEFAULT '1',
	  PRIMARY KEY (`id`),
	  KEY `email` (`email`)
	) ENGINE=InnoDB  DEFAULT CHARSET=latin1;";		
	
	$query[] = "INSERT INTO `".$db_prefix."departments` (`id`, `dep_order`, `name`, `type`, `autoassign`) VALUES(1, 1, 'General', 0, 1);";
	$query[] = "INSERT INTO `".$db_prefix."emails` (`id`, `orderlist`, `name`, `subject`, `message`) VALUES
('staff_reply', 5, 'Staff Reply', '[#%ticket_id%] %ticket_subject%', '%message%\n\n\nTicket Details\n---------------\n\nTicket ID: %ticket_id%\nDepartment: %ticket_department%\nStatus: %ticket_status%\nPriority: %ticket_priority%\n\n\nHelpdesk: %helpdesk_url%'),
('autoresponse', 4, 'New Message Autoresponse', '[#%ticket_id%] %ticket_subject%', 'Dear %client_name%,\n\nYour reply to support request #%ticket_id% has been noted.\n\n\nTicket Details\n---------------\n\nTicket ID: %ticket_id%\nDepartment: %ticket_department%\nStatus: %ticket_status%\nPriority: %ticket_priority%\n\n\nHelpdesk: %helpdesk_url%'),
('new_ticket', 3, 'New ticket creation', '[#%ticket_id%] %ticket_subject%', 'Dear %client_name%,\n\nThank you for contacting us. This is an automated response confirming the receipt of your ticket. One of our agents will get back to you as soon as possible. For your records, the details of the ticket are listed below. When replying, please make sure that the ticket ID is kept in the subject line to ensure that your replies are tracked appropriately.\n\n		Ticket ID: %ticket_id%\n		Subject: %ticket_subject%\n		Department: %ticket_department%\n		Status: %ticket_status%\n                Priority: %ticket_priority%\n\n\nYou can check the status of or reply to this ticket online at: %helpdesk_url%\n\nRegards,\n%company_name%'),
('new_user', 1, 'Welcome email registration', 'Welcome to %company_name% helpdesk', 'This email is confirmation that you are now registered at our helpdesk.\n\nRegistered email: %client_email%\nPassword: %client_password%\n\nYou can visit the helpdesk to browse articles and contact us at any time: %helpdesk_url%\n\nThank you for registering!\n\n%company_name%\nHelpdesk: %helpdesk_url%'),
('lost_password', 2, 'Lost password confirmation', 'Lost password request for %company_name% helpdesk', 'We have received a request to reset your account password for the %company_name% helpdesk (%helpdesk_url%).\n\nYour new passsword is: %client_password%\n\nThank you,\n\n\n%company_name%\nHelpdesk: %helpdesk_url%');
";
	$query[] = "INSERT INTO `".$db_prefix."file_types` (`id`, `type`, `size`) VALUES
(1, 'gif', '0'),
(2, 'png', '0'),
(3, 'jpeg', '0'),
(4, 'jpg', '0'),
(5, 'ico', '0'),
(6, 'doc', '0'),
(7, 'docx', '0'),
(8, 'xls', '0'),
(9, 'xlsx', '0'),
(10, 'ppt', '0'),
(11, 'pptx', '0'),
(12, 'txt', '0'),
(13, 'htm', '0'),
(14, 'html', '0'),
(15, 'php', '0'),
(16, 'zip', '0'),
(17, 'rar', '0'),
(18, 'pdf', '0');";
	$query[] = "INSERT INTO `".$db_prefix."pages` (`id`, `title`, `content`) VALUES
('home', 'Welcome to the support & center', '<div class=\"introductory_display_texts\">\r\n<table style=\"height: 38px;\" width=\"100%\" cellspacing=\"4\">\r\n<tbody>\r\n<tr>\r\n<td style=\"vertical-align: top;\">\r\n<p><strong>New to HelpDeskZ?</strong></p>\r\n<ul>\r\n<li>If you are a customer, then you can login to our support center using the same login details that you use in your client panel.</li>\r\n<li>If you are <strong>not</strong> a customer, then you can submit a ticket, after this process you will receive a password to login to our support center.</li>\r\n</ul>\r\n</td>\r\n<td style=\"width: 50%; vertical-align: top;\">\r\n<p><strong>Do you need help?</strong></p>\r\n<ul>\r\n<li>Visit our knowledgebase at <a title=\"knowledgebase\" href=\"knowledgebase\">yoursite.com/knowledgebase</a></li>\r\n<li>Submit a&nbsp;<a href=\"submit_ticket\">support ticket</a> in English or Spanish.</li>\r\n</ul>\r\n</td>\r\n</tr>\r\n</tbody>\r\n</table>\r\n</div>');";
	$query[] = "INSERT INTO `".$db_prefix."priority` (`id`, `name`, `color`) VALUES
(1, 'Low', '#8A8A8A'),
(2, 'Medium', '#000000'),
(3, 'High', '#F07D18'),
(4, 'Urgent', '#E826C6'),
(5, 'Emergency', '#E06161'),
(6, 'Critical', '#FF0000');";
	$query[] = "INSERT INTO `".$db_prefix."settings` (`field`, `value`) VALUES
('use_captcha', '1'),
('email_ticket', 'support@mysite.com'),
('site_name', 'HelpDeskz Support Center'),
('site_url', 'http://".str_replace("/install/install.php", "",$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'])."'),
('windows_title', 'HelpDeskZ Support Center'),
('show_tickets', 'DESC'),
('ticket_reopen', '0'),
('tickets_page', '20'),
('timezone', 'America/Lima'),
('ticket_attachment', '1'),
('permalink', '0'),
('loginshare', '0'),
('loginshare_url', 'http://yoursite.com/loginshare/'),
('date_format', 'd F Y h:i a'),
('page_size', '25'),
('login_attempt', '3'),
('login_attempt_minutes', '5'),
('overdue_time', '72'),
('knowledgebase_columns', '2'),
('knowledgebase_articlesundercat', '2'),
('knowledgebase_articlemaxchar', '200'),
('knowledgebase_mostpopular', 'yes'),
('knowledgebase_mostpopulartotal', '4'),
('knowledgebase_newest', 'yes'),
('knowledgebase_newesttotal', '4'),
('knowledgebase', 'yes'),
('news', 'yes'),
('news_page', '4'),
('homepage', 'knowledgebase'),
('email_piping', 'yes'),
('smtp', 'no'),
('smtp_hostname', 'smtp.gmail.com'),
('smtp_port', '587'),
('smtp_ssl', 'tls'),
('smtp_username', 'mail@gmail.com'),
('smtp_password', 'password'),
('tickets_replies', '10'),
('helpdeskz_version', '".HELPDESKZ_VERSION."'),
('closeticket_time', '72'),
('client_language', 'english'),
('staff_language', 'english'),
('client_multilanguage', '0'),
('maintenance', '0');";

	$query[] = "INSERT INTO `".$db_prefix."staff` (`id`, `username`, `password`, `fullname`, `email`, `login`, `last_login`, `department`, `timezone`, `signature`, `avatar`, `admin`, `status`) VALUES
(1, '".$admin_user."', '".sha1($admin_password)."', 'Administrator', 'support@mysite.com', 0, 0, 'a:1:{i:0;s:1:\"1\";}', '', 'Best regards,\r\nAdministrator', NULL, 1, 'Enable');";
	return $query;
}

function helpdeskz_saveConfigFile($db_host, $db_name, $db_user, $db_password, $db_prefix, $db_type){
	$content = '<?php
	$config[\'Database\'][\'dbname\'] = \''.$db_name.'\';
	$config[\'Database\'][\'tableprefix\'] = \''.$db_prefix.'\';
	$config[\'Database\'][\'servername\'] = \''.$db_host.'\';
	$config[\'Database\'][\'username\'] = \''.$db_user.'\';
	$config[\'Database\'][\'password\'] = \''.str_replace("'","\'", $db_password).'\';
	$config[\'Database\'][\'type\'] = \''.$db_type.'\';
	?>';
	if ( ! file_put_contents(HELPDESKZ_PATH . 'includes/config.php', $content) )
	{
		return false;
	}else{
		return true;	
	}
}
function helpdeskz_header(){
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>HelpDeskZ v<?php echo HELPDESKZ_VERSION;?> Installation</title>
<link href="install.css" type="text/css" rel="stylesheet" />
</head>

<body>
<div id="wrapper">
	<div id="logo"></div>
	<div class="login_box">
<?
}
function helpdeskz_footer(){
?>
	</div>
    <div class="footer">
    	Helpdesk Software Powered by HelpDeskZ
    </div>
</div>
</body>
</html>
<?	
exit;
}

function helpdeskz_agreement(){
	helpdeskz_header();
?>
<h3>License agreement</h3>

<p><b>Summary:</b></p>

<ul>
<li>The script is provided &quot;as is&quot;, without any warranty. Use at your own risk.<br />&nbsp;</li>
<li>If you wish to remove the &quot;Powered by&quot; links a <a href="http://www.helpdeskz.com/contact" target="_blank">license is required</a>.</li>
</ul>
<p><b>The entire agreement:</b></p>

<p align="center"><textarea style="width:99%; height:300px">
LICENSE AGREEMENT

The &quot;script&quot; is all files included with the HelpDeskZ distribution archive as well as all files produced as a result of the installation scripts. Evolution Script S.A.C. (&quot;Author&quot;,&quot;HelpDeskZ&quot;) is the author and copyrights owner of the script. The &quot;Licensee&quot; (&quot;you&quot;) is the person downloading or using the Licensed version of script. &quot;User&quot; is any person using or viewing the script with their HTML browser.

&quot;Powered by&quot; link is herein defined as an anchor link pointing to HelpDeskZ website and/or script webpage, usually located at the bottom of the script and visible to users of the script without looking into source code.

&quot;Copyright headers&quot; is a written copyright notice located in script source code and normally not visible to users.

This License may be modified by the Author at any time. The new version of the License becomes valid when published on HelpDeskZ website. You are encouraged to regularly check back for License updates.

THIS SCRIPT IS PROVIDED &quot;AS IS&quot; AND ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL EVOLUTION SCRIPT S.A.C. BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SCRIPT, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.

Using this code, in part or full, to create derivative work, new scripts or products is expressly forbidden. Obtain permission before redistributing this software over the Internet or in any other medium.

REMOVING POWERED BY LINKS
You are not allowed to remove or in any way edit the &quot;Powered by&quot; links in this script without purchasing a License. You can purchase a License at
https://www.helpdeskz.com/contact

If you remove the Powered by links without purchasing a License and paying the licensee fee, you are in a direct violation of United States of America and International copyright laws. Your License to use the scripts is immediately terminated and you must delete all copies of the entire program from your web server. Evolution Script S.A.C. may, at any time, terminate this License agreement if Evolution Script S.A.C. determines, that this License agreement has been breached.

Under no circumstance is the removal of copyright headers from the script source code permitted.

TRADEMARK POLICY

HelpDeskZ is a US registered trademark of Evolution Script S.A.C.. Certain usages of the Trademark are fine and no specific permission from the author is needed:

- there is no commercial intent behind the use

- what you are referring to is in fact HelpDeskZ. If someone is confused into thinking that what isn't HelpDeskZ is in fact HelpDeskZ, you are probably doing something wrong

- there is no suggestion (through words or appearance) that your project is approved, sponsored, or affiliated with HelpDeskZ or its related projects unless it actually has been approved by and is accountable to the author

Permission from the author is necessary to use the HelpDeskZ trademark under any circumstances other than those specifically permitted above. These include:

- any commercial use

- use on or in relation to a software product that includes or is built on top of a product supplied by author, if there is any commercial intent associated with that product

- use in a domain name or URL

- use for merchandising purposes, e.g. on t-shirts and the like

- use of a name which includes the letters HelpDeskZ in relation to computer hardware or software.

- services relating to any of the above

If you wish to have permission for any of the uses above or for any other use which is not specifically referred to in this policy, please contact me and I'll let you know as soon as possible if your proposed use is permissible. Note that due to the volume of mail I receive, it may take some time to process your request. Permission may only be granted subject to certain conditions and these may include the requirement that you enter into an agreement with me to maintain the quality of the product and/or service which you intend to supply at a prescribed level.

While there may be exceptions, it is very unlikely that we will approve Trademark use in the following cases:

- use of a Trademark in a company name

- use of a Trademark in a domain name which has a commercial intent. The commercial intent can range from promotion of a company or product, to collecting revenue generated by advertising

- the calling of any software or product by the name HelpDeskZ (or another related Trademark), unless that software or product is a substantially unmodified HelpDeskZ product

- use in combination with any other marks or logos. This include use of a Trademark in a manner that creates a "combined mark," or use that integrates other wording with the Trademark in a way that the public may think of the use as a new mark (for example Club HelpDeskZ or HelpDeskZBooks, or in a way that by use of special fonts or presentation with nearby words or images conveys an impression that the two are tied in some way)

- use in combination with any product or service which is presented as being Certified or Official or formally associated with me or my products or services

- use in a way which implies an endorsement where that doesn't exist, or which attempts to unfairly or confusingly capitalise on the goodwill or brand of the project

- use of a Trademark in a manner that disparages HelpDeskZ and is not clearly third-party parody

- on or in relation to a software product which constitutes a substantially modified version of a product supplied by HelpDeskZ.com, that is to say with material changes to the code, or services relating to such a product

- in a title or metatag of a web page whose sole intention or result is to influence search engine rankings or result listings, rather than for discussion, development or advocacy of the Trademarks

OTHER

This License Agreement is governed by the laws of United States of America.

If any provision hereof shall be held illegal, invalid or unenforceable, in whole or in part, such provision shall be modified to the minimum extent necessary to make it legal, valid and enforceable, and the legality, validity and enforceability of all other provisions of this Agreement shall not be affected thereby. No delay or failure by either party to exercise or enforce at any time any right or provision hereof shall be considered a waiver thereof or of such party's right thereafter to exercise or enforce each and every right and provision of this Agreement.
</textarea></p>
    	<form method="post" action="./install.php">
	<p><b>Do you agree to the License agreement and all the terms incorporated therein?</b> <font color="#FF0000"><i>(required)</i></font></b></p>
    
	<input type="hidden" name="license" value="agree" />
	<input type="button" onClick="location.href='/';" value="NO, I DO NOT AGREE (Cancel setup)" />
	&nbsp;
	<input type="submit" value="YES, I AGREE (Click to continue) &raquo;" />

        </form>
<?
	helpdeskz_footer();
}

function helpdeskz_checksetup(){
	$error_msg = array();
    if ( function_exists('version_compare') && version_compare(PHP_VERSION,'5.0.0','<') ){
		$error_msg[] = 'PHP version <b>5.0+</b> required, you are using: <b>' . PHP_VERSION . '</b>';
	}
	if ( ! function_exists('mysql_connect') && ! function_exists('mysqli_connect') ){
		$error_msg[] = 'MySQL is disabled.';
	}
	if ( ! is_writable(HELPDESKZ_PATH . 'includes/config.php') )
	{
		// -> try to CHMOD it
		if ( function_exists('chmod') )
		{
			@chmod(HELPDESKZ_PATH . 'includes/config.php', 0666);
		}

		// -> test again
		if ( ! is_writable(HELPDESKZ_PATH . 'includes/config.php') )
		{
			$error_msg[] = 'File <strong>includes/config.php</strong> is not writable by PHP.';
		}
	}
	
    $attach_dir = HELPDESKZ_PATH . 'uploads';
	if ( ! file_exists($attach_dir) )
	{
	    @mkdir($attach_dir, 0755);
	}
	
	if ( is_dir($attach_dir) )
    {
	    if ( ! is_writable($attach_dir) )
	    {
			@chmod($attach_dir, 0777);
			if ( ! is_writable($attach_dir) )
			{
				$error_msg[] = '>Folder <strong>/uploads</strong> is not writable by PHP.';
		   	}
	    }
	}
	else
	{
		$error_msg[] = 'Folder <strong>/uploads</strong> is missing.';
	}
	
    $attach_dir = HELPDESKZ_PATH . 'uploads/articles';
	if ( ! file_exists($attach_dir) )
	{
	    @mkdir($attach_dir, 0755);
	}
	
	if ( is_dir($attach_dir) )
    {
	    if ( ! is_writable($attach_dir) )
	    {
			@chmod($attach_dir, 0777);
			if ( ! is_writable($attach_dir) )
			{
				$error_msg[] = '>Folder <strong>/uploads/articles</strong> is not writable by PHP.';
		   	}
	    }
	}
	else
	{
		$error_msg[] = 'Folder <strong>/uploads/articles</strong> is missing.';
	}
	
    $attach_dir = HELPDESKZ_PATH . 'uploads/tickets';
	if ( ! file_exists($attach_dir) )
	{
	    @mkdir($attach_dir, 0755);
	}
	
	if ( is_dir($attach_dir) )
    {
	    if ( ! is_writable($attach_dir) )
	    {
			@chmod($attach_dir, 0777);
			if ( ! is_writable($attach_dir) )
			{
				$error_msg[] = '>Folder <strong>/uploads/tickets</strong> is not writable by PHP.';
		   	}
	    }
	}
	else
	{
		$error_msg[] = 'Folder <strong>/uploads/tickets</strong> is missing.';
	}
	
    if ( count($error_msg) ){
		helpdeskz_header();
		echo '<h3>Check Setup</h3>';
		echo '<div class="error_box">';
        foreach ($error_msg as $err)
        {
        	echo $err.'<br>';
        }
		echo '</div>';
		helpdeskz_footer();	
	}else{
		helpdeskz_database();	
	}
}

function helpdeskz_database($error_msg =null){
	helpdeskz_header();
	if($error_msg !== null){
		echo '<div class="error_box">'.$error_msg.'</div>';	
	}
	?>
    <h3>Database settings</h3>
	<form action="install.php" method="post">
	<table>
	<tr>
	<td width="200">Database Host:</td>
	<td><input type="text" name="db_host" value="<?php echo ($_POST['db_host'] == ''?'localhost':htmlspecialchars($_POST['db_host']));?>" size="40" autocomplete="off" /></td>
	</tr>
	<tr>
	<td width="200">Database Name:</td>
	<td><input type="text" name="db_name" value="<?php echo htmlspecialchars($_POST['db_name']);?>" size="40" autocomplete="off" /></td>
	</tr>
	<tr>
	<td width="200">Database User (login):</td>
	<td><input type="text" name="db_user" value="<?php echo htmlspecialchars($_POST['db_user']);?>" size="40" autocomplete="off" /></td>
	</tr>
	<tr>
	<td width="200">User Password:</td>
	<td><input type="text" name="db_password" value="<?php echo htmlspecialchars($_POST['db_password']);?>" size="40" autocomplete="off" /></td>
	</tr>
    <tr>
    <td width="200">Table prefix:</td>
    <td><input type="text" name="db_prefix" value="<?php echo ($_POST['db_prefix'] == ''?'hdz_':htmlspecialchars($_POST['db_prefix']));?>" size="40" autocomplete="off" /></td>
    </tr>
    <tr>
    <td width="200">Use:</td>
    <td><select name="sql_type">
    <option value="mysql">MySQL</option>
    <option value="mysqli">MySQLi</option>
    </select>
    </td>
    </tr>
    </table>
    <h3>HelpDeskZ login details</h3>

    <p>Username and password you will use to login into HESK administration.</p>
		<table>
		<tr>
		<td width="200">Choose a Username:</td>
		<td><input type="text" name="admin_user" value="<?php echo isset($_POST['admin_user']) ? htmlspecialchars($_POST['admin_user']) : 'Administrator'; ?>" size="40" autocomplete="off" /></td>
		</tr>
		<tr>
		<td width="200">Choose a Password:</td>
		<td><input type="text" name="admin_password" value="<?php echo isset($_POST['db_password']) ? htmlspecialchars($_POST['admin_password']) : ''; ?>" size="40" autocomplete="off" /></td>
		</tr>
		<tr>
        	<td></td>
            <td><input type="hidden" name="license" value="agree" />
            <input type="hidden" name="settings" value="install" />
            <input type="submit" name="btn" value="Install HelpDeskZ" /></td>
        </tr>
		</table>
    </form>
    <?
	helpdeskz_footer();	
}

function helpdeskz_completed(){
	helpdeskz_header();	
?>
	<h3>Installation Completed</h3>
    <p>Installation has been successfully completed, <strong>do not forget to remove</strong> <strong style="color:red">/install</strong> folder</p>
<?
	helpdeskz_footer();	
}
if($input->p['license'] == 'agree'){
	if($input->p['settings'] == 'install'){
		if($input->p['sql_type'] == 'mysqli'){
			$db = new MySQLIDB;
		}else{
			$db = new MySQLDB;
		}
		$error_msg = $db->testconnect($input->p['db_name'], $input->p['db_host'], $input->p['db_user'], $input->p['db_password']);
		if($error_msg != ''){
			helpdeskz_database($error_msg);
		}elseif($input->p['admin_user'] == '' || $input->p['admin_password'] == ''){
			helpdeskz_database('Enter the HelpDeskZ login details.');
		}else{

			$db->connect($input->p['db_name'], $input->p['db_host'], $input->p['db_user'], $input->p['db_password'], $input->p['db_prefix']);
			$query = helpdeskz_getQuery($input->p['db_prefix'], $input->p['admin_user'], $input->p['admin_password']);
			foreach($query as $q){
				$db->query($q);
			}
			helpdeskz_saveConfigFile($input->p['db_host'], $input->p['db_name'], $input->p['db_user'], $input->p['db_password'], $input->p['db_prefix'], $input->p['sql_type']);
			header('location: install.php?result=completed');
			exit;
		}
	}
	helpdeskz_checksetup();
}else{
	if($input->g['result'] == 'completed'){
		helpdeskz_completed();
	}else{
		helpdeskz_agreement();
	}
}
?>