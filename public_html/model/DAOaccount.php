<?php
require_once "DBconnection.php";

function createAccount(array $account) {
    try {
        $data = [];
        $data[] = $account["name"];
        $data[] = $account["current_amount"];
        $data[] = $account["owner_id"];

        $conn = getPDO();
        $sql = "INSERT INTO accounts(name, current_amount, owner_id, date_created)
                VALUES (?, ?, ?, CURRENT_DATE);";
        $stmt = $conn->prepare($sql);
        $stmt->execute($data);
        return true;
    } catch (PDOException $exception) {
        return $exception;
    }
}

function getMyAccounts($user_id) {
    try {
        $conn = getPDO();
        $sql = "SELECT id, name, current_amount FROM accounts WHERE owner_id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $exception) {
        return false;
    }
}

function editAccount($new_name, $account_id) {
    try {
        $conn = getPDO();
        $sql = "UPDATE accounts SET name = ? WHERE account_id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$new_name, $account_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $exception) {
        return false;
    }
}