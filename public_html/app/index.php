<?php
session_start();

spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    require_once $class;
});

define("MIN_LENGTH_PASSWORD", 8);
define("MIN_LENGTH_NAME", 3);
//8 symbols, one letter and one number
define("PASSWORD_PATTERN", "^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$^");

$controllerName = isset($_GET['target']) ? $_GET['target'] : '';
$methodName = isset($_GET['action']) ? $_GET['action'] : '';

$controllerClassName = '\\controller\\' . ucfirst($controllerName) . ucfirst('controller');

if(class_exists($controllerClassName)){
    $controller = new $controllerClassName();

    if(method_exists($controller, $methodName)){
        echo json_encode($controller->$methodName());
    } else{
        echo 'error';
    }
} else{
    echo 'error';
}