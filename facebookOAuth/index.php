<?php
/**
 * @package HelpDeskZ
 * @website: http://www.helpdeskz.com
 * @community: http://community.helpdeskz.com
 * @author Evolution Script S.A.C.
 * @since 1.0.2
 */
include('../includes/global.php');
include(INCLUDES.'helpdesk.inc.php');
include(__DIR__.'/facebook.php');
if($settings['facebookoauth'] != 1)
{
    header('location: '.getUrl());
    exit;
}

$facebook = new Facebook(array(
    'appId'  => $settings['facebookappid'],
    'secret' => $settings['facebookappsecret'],
));

$user = $facebook->getUser();

if ($user) {
    try {
        // Proceed knowing you have a logged in user who's authenticated.
        $user_profile = $facebook->api('/me');
        if(!isset($user_profile['email']))
        {
            $params = array(
                'scope' => 'email',
                'redirect_uri' => getUrl().'/facebookOAuth/',
                'auth_type' => 'rerequest',
            );
            $loginUrl = $facebook->getLoginUrl($params);
            header('location: '.$loginUrl);
            exit;
        }else{
            $data = array('fullname' => $user_profile['first_name'].' '.$user_profile['last_name'], 'email' => $user_profile['email']);
            $user_id = hdz_registerAccount($data);
            hdz_loginAccount($user_profile['email'], 48);
            unset($_SESSION['access_token']);
            header('location: '.getUrl('view_tickets'));
            exit;
        }
    } catch (FacebookApiException $e) {
        $params = array(
            'scope' => 'email',
            'redirect_uri' => getUrl().'/facebookOAuth/',
        );
        $loginUrl = $facebook->getLoginUrl($params);
        header('location: '.$loginUrl);
        exit;
    }
}else{
    $params = array(
        'scope' => 'email',
        'redirect_uri' => getUrl().'/facebookOAuth/',
    );
    $loginUrl = $facebook->getLoginUrl($params);
    header('location: '.$loginUrl);
    exit;
}