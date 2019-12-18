<?php
require_once "DBconnection.php";

function getUserByEmail($email) {
    try {
        $conn = getPDO();
        $sql = "SELECT id, password, CONCAT(first_name, ' ', last_name) AS full_name FROM users WHERE email = ?";
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

function addUser($user) {
    try {
        $data = [];
        $data[] = $user['email'];
        $data[] = $user['password'];
        $data[] = $user['first_name'];
        $data[] = $user['last_name'];

        $conn = getPDO();
        $sql = "INSERT INTO users(email, password, first_name, last_name, last_login, date_created)
                VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, CURRENT_DATE)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($data);
        return true;
    } catch (PDOException $exception) {
        return false;
    }
}