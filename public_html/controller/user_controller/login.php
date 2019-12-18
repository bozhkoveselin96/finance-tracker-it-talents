<?php
require_once "../../model/DAOuser.php";
session_start();
define("MIN_LENGTH_PASSWORD", 5);
if (isset($_POST["login"])) {
    $email = $_POST["email"];
    $password = $_POST["password"];
    $response = [];
    $response["status"] = false;
    if (!empty($email) && !empty($password) &&
        mb_strlen($password) >= MIN_LENGTH_PASSWORD &&
        filter_var($email , FILTER_VALIDATE_EMAIL)) {
        $user = getUserByEmail($email);
        if ($user && password_verify($password, $user["password"])) {
            $_SESSION["logged_user"] = $user["id"];
            $response["status"] = true;
            $response["full_name"] = $user["full_name"];
        }
    }
    echo json_encode($response);
}
