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
include(__DIR__.'/Google/autoload.php');
if($settings['googleoauth'] != 1)
{
    header('location: '.getUrl());
    exit;
}

$client_id = $settings['googleclientid'];
$client_secret = $settings['googleclientsecret'];
$redirect_uri = getUrl().'/googleOAuth/';

$client = new Google_Client();
$client->setClientId($client_id);
$client->setClientSecret($client_secret);
$client->setRedirectUri($redirect_uri);
$client->addScope('email');
$client->addScope('profile');
if (isset($_GET['code'])) {
    $client->authenticate($_GET['code']);
    $_SESSION['access_token'] = $client->getAccessToken();
    $redirect = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['PHP_SELF'];
    header('Location: ' . filter_var($redirect, FILTER_SANITIZE_URL));
}
if (isset($_SESSION['access_token']) && $_SESSION['access_token']) {
    $client->setAccessToken($_SESSION['access_token']);
} else {
    $authUrl = $client->createAuthUrl();
}

if ($client->getAccessToken()) {
    $_SESSION['access_token'] = $client->getAccessToken();
    //$token_data = $client->verifyIdToken()->getAttributes();
    $objOAuthService = new Google_Service_Oauth2($client);
    $userData = $objOAuthService->userinfo->get();
}


if (strpos($client_id, "googleusercontent") == false) {
    echo missingClientSecretsWarning();
    exit;
}
if (isset($authUrl)) {
    header('location: '.$authUrl);
    exit;
}

if (isset($userData)) {
    $userData = $objOAuthService->userinfo->get();
    $data = array('fullname' => $userData->givenName.' '.$userData->familyName, 'email' => $userData->email);
    $user_id = hdz_registerAccount($data);
    hdz_loginAccount($userData->email, 48);
    unset($_SESSION['access_token']);
    header('location: '.getUrl('view_tickets'));
    exit;
}