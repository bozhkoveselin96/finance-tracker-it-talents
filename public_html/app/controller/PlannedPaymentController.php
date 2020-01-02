<?php


namespace controller;


use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\planned_payments\PlannedPayment;
use model\planned_payments\PlannedPaymentDAO;

class PlannedPaymentController {
    public function add() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly or you are not logged in.';
        if (isset($_POST['add_planned_payment']) && isset($_SESSION['logged_user']) && isset($_POST['day_for_payment']) &&
            isset($_POST['amount']) && isset($_POST['account_id']) && isset($_POST['category_id'])) {
            $plannedPayment = new PlannedPayment($_POST['day_for_payment'], $_POST['amount'], $_POST['account_id'], $_POST['category_id']);
            $account = AccountDAO::getAccountById($plannedPayment->getAccountId());
            $category = CategoryDAO::getCategoryById($plannedPayment->getCategoryId(), $_SESSION['logged_user']);
            if (Validator::validateAmount($plannedPayment->getAmount()) &&
                Validator::validateDayOfMonth($plannedPayment->getDayForPayment()) &&
                $account->owner_id == $_SESSION['logged_user'] && $category && PlannedPaymentDAO::create($plannedPayment)) {
                $status = STATUS_CREATED;
                $response['target'] = 'planned_payment';
            }
        }
        header($status);
        return $response;
    }

    public function getAll() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'No planned payments available or you are not logged in.';
        if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_SESSION['logged_user'])) {
             $plannedPayments = PlannedPaymentDAO::getAll($_SESSION['logged_user']);
            if ($plannedPayments) {
                $status = STATUS_OK;
                $response['data'] = $plannedPayments;
            }
        }
        header($status);
        return $response;
    }
}