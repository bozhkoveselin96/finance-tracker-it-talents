<?php
require_once "config.php";
function getPDO() {
    try {
        $conn = new PDO("mysql:host=$server;dbname=$dbname;charset=utf8", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $exception) {
        return $exception->getMessage();
    }
}