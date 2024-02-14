<?php

$publicPath = getcwd();

$uri = urldecode(
    parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?? ''
);

// This file allows us to emulate Apache's "mod_rewrite" functionality from the
// built-in PHP web server. This provides a convenient way to test a Laravel
// application without having installed a "real" web server software here.
if ($uri !== '/' && $uri != '/build/sw.js' && file_exists($publicPath.$uri)) {
    return false;
}

if ($uri == '/build/sw.js') {
    header('Service-Worker-Allowed: /');
    header('Content-Type: text/javascript');
    echo file_get_contents(__DIR__.'/public/build/sw.js');
    exit;
}

require_once $publicPath.'/index.php';
