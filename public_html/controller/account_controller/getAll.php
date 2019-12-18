<?php
session_start();
require_once "../../model/DAOaccount.php";
if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_SESSION['logged_user']) && isset($_GET['user_id'])) {
    $response = [];
    $response['status'] = false;
    $user_id = $_GET['user_id'];
    if ($user_id == $_SESSION['logged_user']) {
        $accounts = getMyAccounts($user_id);
        if ($accounts) {
            $response['status'] = true;
            $response['data'] = $accounts;
        }
    }
    echo json_encode($response);
}