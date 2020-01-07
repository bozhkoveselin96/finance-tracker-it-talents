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

function handleExceptions(Exception $exception) {
    $status = $exception instanceof BaseException ? $exception->getStatusCode() : 500;
    $message = $exception->getMessage();
    $object = new stdClass();
    $object->message = $message;
    $object->status = $status;
    echo json_encode($object);
    http_response_code($status);
}

set_exception_handler("handleExceptions");

define("MIN_LENGTH_PASSWORD", 8);
define("MIN_LENGTH_NAME", 3);
//8 symbols, one letter and one number
define("PASSWORD_PATTERN", "^(?=.*[A-Za-z])(?=.*\d)[a-zA-Z0-9,.;\^!@#$%&*()+=:_'\s-]{8,}$^");
define("MAX_AMOUNT", 10000000);
define("NO_AVATAR_URL", 'avatars' . DIRECTORY_SEPARATOR . 'no-avatar.png');

define("STATUS_OK", $_SERVER["SERVER_PROTOCOL"] . " 200 OK ");
define("STATUS_CREATED", $_SERVER["SERVER_PROTOCOL"] . " 201 Created ");
define("STATUS_ACCEPTED", $_SERVER["SERVER_PROTOCOL"] . " 202 ");


$controllerName = isset($_GET['target']) ? $_GET['target'] : '';
$methodName = isset($_GET['action']) ? $_GET['action'] : '';

if ($controllerName != 'user' && ($methodName != 'login' || $methodName != 'register') && !isset($_SESSION['logged_user'])) {
    header(STATUS_UNAUTHORIZED . 'Please log in.');
    die();
}

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