<?php
session_start();
define("MIN_LENGTH_PASSWORD", 8);
if (isset($_POST["login"])) {
    if (empty($_POST["email"]) || empty($_POST["password"])) {

    } elseif ($_POST["password"] < MIN_LENGTH_PASSWORD) {

    } elseif (filter_var($_POST["email"] , FILTER_VALIDATE_EMAIL)) {

    } elseif (!exists_user($_POST["email"])) {

    } else {
        $user = exists_user($_POST["email"]);
        if (password_verify($user["password"], $_POST["password"])) {
            $_SESSION["logged_user"] = $user;
        } else {

        }
    }
}