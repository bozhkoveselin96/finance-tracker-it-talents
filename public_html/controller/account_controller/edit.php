<?php
session_start();
require_once "../../model/DAOaccount.php";

if (isset($_POST["edit"])) {
    $user_id = $_POST["user_id"];
    $account_id = $_POST["account_id"];
    $new_name = $_POST["name"];
    $account = getAccountById($account_id);
    $response["status"] = false;

    if ($user_id == $_SESSION["logged_user"] &&
        $account["owner_id"] == $user_id &&
        editAccount($new_name, $account["id"]))
    {
        $response["status"] = true;
    }
    echo json_encode($response);
}