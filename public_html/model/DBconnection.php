<?php
function getPDO() {
    try {
        require_once "config.php";
        $conn = new PDO("mysql:host=".SERVER.";dbname=".DBNAME.";charset=utf8", USERNAME, PASSWORD);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $exception) {
        return $exception->getMessage();
    }
}