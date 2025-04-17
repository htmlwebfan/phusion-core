<?php
/* 
    Single entry point for all requests. It sets up autoloading and initializes the router.

    Autoloading: Uses spl_autoload_register to automatically load classes from core/, controllers/, and models/ when they’re instantiated.
    Routing: Instantiates the Router class and calls its dispatch method to handle the request.
*/
// ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$config = include __DIR__ . '/config.php';

if ($config['env'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
}
ini_set('log_errors', 1);
ini_set('error_log', $config['error_log']);

spl_autoload_register(function ($class) {
    $dirs = ['core', 'controllers', 'models'];
    foreach ($dirs as $dir) {
        $file = $dir . '/' . $class . '.php';
        if (file_exists($file)) {
            include $file;
            return;
        }
    }
});

$router = new Router();
$router->dispatch();