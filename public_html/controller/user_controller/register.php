<?php
require_once "../../model/DAOuser.php";
define("MIN_LENGTH_PASSWORD", 5);
define("MIN_LENGTH_NAME", 3);

if (isset($_POST["register"])) {
    $response = [];
    $response["status"] = false;
    $user = [
        "first_name" => $_POST["first_name"],
        "last_name" => $_POST["last_name"],
        "email" => $_POST["email"],
        "password" => $_POST["password"],
        "repeat_password" => $_POST["rpassword"]
    ];

    if (!empty($user["email"]) &&
        !getUserByEmail($user["email"]) &&
        !empty($user["password"]) &&
        mb_strlen($user["password"]) >= MIN_LENGTH_PASSWORD &&
        filter_var($user["email"] , FILTER_VALIDATE_EMAIL) &&
        !empty($user["first_name"]) &&
        !empty($user["last_name"]) &&
        mb_strlen($user["first_name"]) > MIN_LENGTH_NAME &&
        mb_strlen($user["last_name"]) > MIN_LENGTH_NAME &&
        strcmp($user["password"], $user["repeat_password"]) == 0) {
        if (addUser($user)) {
            $response["status"] = true;
        }
    }
    echo json_encode($response);
}