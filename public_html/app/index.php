<?php
use exceptions\NotFoundException;
use exceptions\UnauthorizedException;
use exceptions\BaseException;
session_start();
require_once 'defines.php';
set_exception_handler("handleExceptions");

spl_autoload_register(function ($class) {
    $class = str_replace('\\', DIRECTORY_SEPARATOR, $class) . '.php';
    if (file_exists($class)) {
        require_once $class;
    } else {
        throw new NotFoundException('Not Found!');
    }
});

function handleExceptions($exception) {
    $status = $exception instanceof BaseException ? $exception->getStatusCode() : 500;
    $message = $exception->getMessage();
    $object = new stdClass();
    $object->message = $message;
    $object->status = $status;
    http_response_code($status);
    echo json_encode($object);
}

$controllerName = isset($_GET['target']) ? $_GET['target'] : '';
$methodName = isset($_GET['action']) ? $_GET['action'] : '';

if (!isset($_SESSION['logged_user']) && array_search($methodName, $white_list_not_logged) === false) {
    throw new UnauthorizedException("Please log in.");
}

$controllerClassName = '\\controller\\' . ucfirst($controllerName) . ucfirst('controller');

if(class_exists($controllerClassName)){
    $controller = new $controllerClassName();

    if(method_exists($controller, $methodName)){
        echo json_encode($controller->$methodName());
    } else{
        throw new NotFoundException('Not Found');
    }
} else{
    throw new NotFoundException('Not Found');
}