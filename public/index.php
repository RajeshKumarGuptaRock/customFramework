<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Core\Router;
use Core\Request;

$router = new Router();
require_once __DIR__ . '/../routes/web.php';

$router->dispatch(Request::getUrl(), Request::getMethod());

