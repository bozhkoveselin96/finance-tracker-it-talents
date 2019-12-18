<?php
require_once "DBconnection.php";

function createAccount(array $account) {
    try {
        $data = [];
        $data["name"] = $account["name"];
        $data["current_amount"] = $account["current_amount"];
        $data["owner_id"] = $account["owner_id"];

        $conn = getPDO();
        $sql = "INSERT INTO accounts(name, current_amount, owner_id, date_created)
                VALUES (?, ?, ?, CURRENT_DATE);";
        $stmt = $conn->prepare($sql);
        $stmt->execute($data);
        return true;
    } catch (PDOException $exception) {
        return false;
    }
}