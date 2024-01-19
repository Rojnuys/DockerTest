<?php

session_start();

define('ROOT', dirname(__FILE__));

require_once 'vendor/autoload.php';
require_once ROOT . '/components/Router.php';
require_once ROOT . '/components/Redirect.php';

$router = new Router();
$router->run();