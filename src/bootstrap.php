<?php
use Silex\Application;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use \Doctrine\Common\Cache\ApcCache;
use \Doctrine\Common\Cache\ArrayCache;
use Silex\Provider\FormServiceProvider;
use Symfony\Component\HttpFoundation\Response;


$app        = new Application();
$confp      = new Lib('conn');
$prefix     =$confp->_v('PREFIX');

$app->register(new FormServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());


$app->register(new Silex\Provider\DoctrineServiceProvider(), array(
    'dbs.options'  => array(
      'mysql_silex' =>array(
          'driver'        => $confp->_v('DRIVER'),
          'host'          => $confp->_v('HOST'),
          'dbname'        => $confp->_v('DBNAME'),
          'user'          => $confp->_v('USER'),
          'password'      => $confp->_v('PASSWORD'),
          'charset'       => $confp->_v('CHARSET'),
          'driverOptions' => array(1002 => 'SET NAMES utf8'),
      ),
   ),   
));


$app->register(new Silex\Provider\ModelsServiceProvider(), array(
  'models.path' => __DIR__ .DS.'models'.DS
));

$master   = new ModelMaster($app,$prefix);


/*
$templates  = new modelTemplatesManager( $app, $prefix );
$template=$templates->_getTemplateActive(); 
*/
$app->register(new TwigServiceProvider(), array(
    'twig.path'    => array(PATH_TEMPLATES_WEB.DS.'home'.DS),
    #'twig.options' => array('cache' => PATH_CACHE.'/twig'), 
));
$app['twig'] = $app->share($app->extend('twig', function($twig, $app) {
    return $twig;
}));

$path=str_replace($_SERVER['DOCUMENT_ROOT'], '', PATH_TEMPLATES_WEB);
$app['source']=$path.DS;



$app->error(function (\LogicException $e, $code) {
$error_logic = error_get_last();
$error_logic = "[date: ".date('r')."] [type: ".$error_logic['type']."] [pid: ".getmypid()."] [client: ".$_SERVER['HTTP_HOST'].":".$_SERVER['SERVER_PORT']."] PHP ".$error_logic['message']." ".$error_logic['file']." on line ".$error_logic['line']."\n";
error_log($error_logic, 3, PATH_ROOT."/logs/errors.log");
});

$app->error(function (\Exception $e, $code) use ($app) {
  switch ($code) {
  case 404:
    $message = 'Lo sentimos, la pagina No existe 404';
    #return $app->redirect('./error');
  break;

  default:
    $message = 'Lo sentimos, la pagina No existe other';
    return $app->redirect('./error');
  break;
  }
  echo $message;
  exit;
});

return $app;
