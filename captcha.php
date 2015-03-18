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
// Create a 300x100 image
$im = imagecreatetruecolor(180, 40);
/******************/
//Letras
$letras = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ123456789'; 
$letraA = $letras[rand(0, strlen($letras)-1)]; 
$letraB = $letras[rand(0, strlen($letras)-1)]; 
$letraC = $letras[rand(0, strlen($letras)-1)]; 
$letraD = $letras[rand(0, strlen($letras)-1)]; 
$letraE = $letras[rand(0, strlen($letras)-1)]; 
$tuner = "$letraA$letraB$letraC$letraD$letraE";
$_SESSION['captcha'] = $tuner;
//Tamaño de letra entre 12 y 24
$size1 = rand(25,29);
$size2 = rand(25,29);
$size3 = rand(25,29);
$size4 = rand(25,29);
$size5 = rand(25,29);

$mov_vertical_1 = rand(30,40);
$mov_vertical_2 = rand(30,40);
$mov_vertical_3 = rand(30,40);
$mov_vertical_4 = rand(30,40);
$mov_vertical_5 = rand(30,40);

$mov_horizontal_1 = rand(10,20);
$mov_horizontal_2 = rand(50,60);
$mov_horizontal_3 = rand(90,100);
$mov_horizontal_4 = rand(120,130);
$mov_horizontal_5 = rand(150,160);

$mov_giratorio_1 = rand(-15,15);
$mov_giratorio_2 = rand(-15,15);
$mov_giratorio_3 = rand(-15,15);
$mov_giratorio_4 = rand(-15,15);
$mov_giratorio_5 = rand(-15,15);

//Color
$color1 = imagecolorallocate($im, rand(0,164), rand(0,84), rand(0,105));
$color2 = imagecolorallocate($im, rand(0,164), rand(0,84), rand(0,105));
$color3 = imagecolorallocate($im, rand(0,164), rand(0,84), rand(0,105));
$color4 = imagecolorallocate($im, rand(0,164), rand(0,84), rand(0,105));

/*****************/

$path = dirname(__FILE__).'/';
$im = imagecreatefrompng($path.'images/captcha.png');
$font_file = $path.'includes/captcha.ttf';

imagefttext($im, $size1, $mov_giratorio_1, $mov_horizontal_1, $mov_vertical_1, $color1, $font_file, $letraA);
imagefttext($im, $size2, $mov_giratorio_2, $mov_horizontal_2, $mov_vertical_2, $color2, $font_file, $letraB);
imagefttext($im, $size3, $mov_giratorio_3, $mov_horizontal_3, $mov_vertical_3, $color3, $font_file, $letraC);
imagefttext($im, $size4, $mov_giratorio_4, $mov_horizontal_4, $mov_vertical_4, $color4, $font_file, $letraD);
imagefttext($im, $size5, $mov_giratorio_5, $mov_horizontal_5, $mov_vertical_5, $color5, $font_file, $letraE);

// Output image to the browser
header('Content-Type: image/png');

imagepng($im);
imagedestroy($im);
?>
