<?php
/**
*	jcbarreno.b@gmail.com
*
*/

#if (isset($_COOKIE['tour'])) {
	#setcookie("tour", true, date(),"/");
#}else{
	#echo 'x';
#}



date_default_timezone_set('America/Guatemala');
header('p3p: CP="NOI ADM DEV PSAi COM NAV OUR OTR STP IND DEM"');
session_start();
define('DS', DIRECTORY_SEPARATOR);
define('PATH_ROOT', dirname(__DIR__));
define('PATH_CACHE', PATH_ROOT.DS.'cache');
define('PATH_CONFIG', PATH_ROOT.DS.'config');
define('PATH_LOG', PATH_ROOT.DS.'logs');
define('PATH_SRC', PATH_ROOT.DS.'src');
define('PATH_TEMPLATES', PATH_ROOT.DS.'templates');
define('PATH_VENDOR', PATH_ROOT.DS.'vendor');
define('PATH_WEB', PATH_ROOT.DS.'web');
define('PATH_TEMPLATES_WEB', PATH_WEB.DS.'templates');
require_once PATH_VENDOR.DS.'autoload.php';
require_once PATH_CONFIG.DS.'clib.php';
require_once PATH_SRC.DS.'models'.DS.'model.master.php';
$app = require PATH_SRC.DS.'bootstrap.php';
require PATH_SRC.DS.'controllers'.DS.'controllers.php';
$app['debug'] = false;
$app->run();