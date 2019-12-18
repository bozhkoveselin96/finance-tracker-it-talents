<?php
require_once "DBconnection.php";

function getUserByEmail($email) {
    try {
        $conn = getPDO();
        $sql = "SELECT password, CONCAT(first_name, ' ', last_name) AS full_name FROM users WHERE email = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$email]);
        if ($stmt->rowCount() == 1) {
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return false;
    } catch (PDOException $exception) {
        return false;
    }
}