<?php
require_once "../config.php";

use \Tsugi\Core\LTIX;

$launch = LTIX::requireData();
$app = new \Tsugi\Silex\Application($launch);
$app['debug'] = true;

$app->get('/', 'AppBundle\\Attend::get')->bind('main');

$app->post('/', 'AppBundle\\Attend::post');

$app->run();
