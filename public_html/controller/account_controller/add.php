<?php
session_start();
require_once "../../model/DAOaccount.php";
define("MIN_LENGTH_NAME", 3);

if (isset($_POST['add_account'])) {
    $user_id = $_SESSION['logged_user'];
    $account_name = $_POST['name'];
    $current_amount = $_POST['current_amount'];

    $response = [];
    $response['status'] = false;
    if (mb_strlen($account_name) >= MIN_LENGTH_NAME && is_numeric($current_amount) && $current_amount >= 0) {
        $account = [];
        $account['name'] = $account_name;
        $account['current_amount'] = $current_amount;
        $account['owner_id'] = $user_id;
        if (createAccount($account)) {
            $response['status'] = true;
            $response['target'] = 'addaccount';
        }
    }
    echo json_encode($response);
}