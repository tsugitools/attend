<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;
use \Tsugi\Core\Settings;
use \Tsugi\Util\Net;

$launch = LTIX::requireData();
// if ( count($_POST) > 0 ) { var_dump($launch); die(); }
$app = new \Tsugi\Silex\Application($launch);
$app['debug'] = true;

$app->get('/', 'AppBundle\\Attend::get')->bind('main');

$app->post('/', 'AppBundle\\Attend::post');

$app->run();
