<?php

/**
 * Front controller
 *
 * PHP version 7.0
 */

/**
 * Composer
 */
require dirname(__DIR__) . '/vendor/autoload.php';


/**
 * Error and Exception handling
 */
error_reporting(E_ALL);
set_error_handler('Core\Error::errorHandler');
set_exception_handler('Core\Error::exceptionHandler');

/**
 * Sessions
 */
session_start();

/**
 * Routing
 */
$router = new Core\Router();

// Add the routes
$router->add('', ['controller' => 'Home', 'action' => 'index']);
$router->add('login', ['controller' => 'login', 'action' => 'open']);
$router->add('logout', ['controller' => 'logout', 'action' => 'destroy']);
$router->add('registration', ['controller' => 'registration', 'action' => 'open']);
$router->add('menu', ['controller' => 'menu', 'action' => 'open']);
$router->add('{controller}/{action}');

$router->dispatch($_SERVER['QUERY_STRING']);
