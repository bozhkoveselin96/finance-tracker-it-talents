<?php


namespace controller;


use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\planned_payments\PlannedPayment;
use model\planned_payments\PlannedPaymentDAO;

class PlannedPaymentController {
    public function add() {
        $response = [];
        $status = STATUS_BAD_REQUEST;
        if (isset($_POST['add_planned_payment']) && isset($_SESSION['logged_user']) && isset($_POST['day_for_payment']) &&
            isset($_POST['amount']) && isset($_POST['account_id']) && isset($_POST['category_id'])) {
            $plannedPayment = new PlannedPayment($_POST['day_for_payment'], $_POST['amount'], $_POST['account_id'], $_POST['category_id']);
            $account = AccountDAO::getAccountById($plannedPayment->getAccountId());
            $category = CategoryDAO::getCategoryById($plannedPayment->getCategoryId(), $_SESSION['logged_user']);
            if (Validator::validateAmount($plannedPayment->getAmount()) && $account->owner_id == $_SESSION['logged_user'] &&
                $category && PlannedPaymentDAO::create($plannedPayment)) {
                $status = STATUS_CREATED;
                $response['target'] = 'planned_payment';
            }
        }
        header($status);
        return $response;
    }

    public function getAll() {
        $response = [];
        $status = STATUS_BAD_REQUEST;
        if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_SESSION['logged_user'])) {
            $response['data'] = PlannedPaymentDAO::getAll($_SESSION['logged_user']);
            $status = STATUS_OK;
        }
        header($status);
        return $response;
    }
}