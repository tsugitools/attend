<?php
require_once "../config.php";

if ( \Tsugi\Core\Tool::handleConfig() ) return;

use \Tsugi\Core\LTIX;

$launch = LTIX::requireData();
$app = new \Tsugi\Silex\Application($launch);
$app['debug'] = true;

$app->get('/', 'AppBundle\\Attend::get')->bind('main');

$app->post('/', 'AppBundle\\Attend::post');

$app->run();
