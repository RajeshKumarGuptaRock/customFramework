<?php
namespace Core;

class Request {
    public static function getUrl() {
        //$actual_full_URL = (empty($_SERVER['HTTPS']) ? 'http' : 'https') . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
        $actual_link = "$_SERVER[REQUEST_URI]";
        return $actual_link ?? '/';
    }

    public static function getMethod() {
        return $_SERVER['REQUEST_METHOD'];
    }
}

