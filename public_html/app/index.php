<?php
session_start();

spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($class)) {
        require_once $class;
    } else {
        header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
        die();
    }
});

define("MIN_LENGTH_PASSWORD", 8);
define("MIN_LENGTH_NAME", 3);
//8 symbols, one letter and one number
define("PASSWORD_PATTERN", "^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d]{8,}$^");

define("STATUS_OK", $_SERVER["SERVER_PROTOCOL"] . " 200 OK");
define("STATUS_CREATED", $_SERVER["SERVER_PROTOCOL"] . " 201 Created");
define("STATUS_BAD_REQUEST", $_SERVER["SERVER_PROTOCOL"] . " 400 Bad Request");
define("STATUS_FORBIDDEN", $_SERVER["SERVER_PROTOCOL"] . " 403 Forbidden");
define("STATUS_NOT_FOUND", $_SERVER["SERVER_PROTOCOL"] . " 404 Not Found");

$controllerName = isset($_GET['target']) ? $_GET['target'] : '';
$methodName = isset($_GET['action']) ? $_GET['action'] : '';

$controllerClassName = '\\controller\\' . ucfirst($controllerName) . ucfirst('controller');

if(class_exists($controllerClassName)){
    $controller = new $controllerClassName();

    if(method_exists($controller, $methodName)){
        echo json_encode($controller->$methodName());
    } else{
        header(STATUS_BAD_REQUEST);
    }
} else{
    header(STATUS_NOT_FOUND);
}