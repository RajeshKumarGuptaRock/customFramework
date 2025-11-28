<?php

$router->add('GET', '/', 'HomeController@index');
$router->add('GET', '/about','HomeController@about');
$router->add('GET', '/privacy','HomeController@privacy');

