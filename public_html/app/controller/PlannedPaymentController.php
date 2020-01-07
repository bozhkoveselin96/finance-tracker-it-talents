<?php


namespace controller;


use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\planned_payments\PlannedPayment;
use model\planned_payments\PlannedPaymentDAO;

class PlannedPaymentController {
    public function add() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly.';
        if (isset($_POST['add_planned_payment']) && isset($_POST['day_for_payment']) &&
            isset($_POST['amount']) && isset($_POST['account_id']) && isset($_POST['category_id'])) {
            try {
                $plannedPaymentDAO = new PlannedPaymentDAO();
                $accountDAO = new AccountDAO();
                $categoryDAO = new CategoryDAO();
                $account = $accountDAO->getAccountById($_POST['account_id']);
                $category = $categoryDAO->getCategoryById($_POST['category_id'], $_SESSION['logged_user']);
                $plannedPayment = new PlannedPayment($_POST['day_for_payment'], $_POST['amount'], $account, $category);
                if (Validator::validateAmount($plannedPayment->getAmount()) && $account && $category &&
                    Validator::validateDayOfMonth($plannedPayment->getDayForPayment()) &&
                    $account->getOwnerId() == $_SESSION['logged_user']) {
                    $plannedPaymentDAO->create($plannedPayment);
                    $status = STATUS_CREATED;
                    $response['target'] = 'planned_payment';
                }
            } catch (\Exception $exception) {
                $status = STATUS_ACCEPTED . 'Not created. Please try again';
            }
        }
        header($status);
        return $response;
    }

    public function getAll() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'No planned payments available.';
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            try {
                $plannedPaymentsDAO = new PlannedPaymentDAO();
                $plannedPayments = $plannedPaymentsDAO->getAll($_SESSION['logged_user']);
                $status = STATUS_OK;
                $response['data'] = $plannedPayments;
            } catch (\Exception $exception) {
                $status = STATUS_ACCEPTED . 'Something went wrong. Please try again';
            }
        }
        header($status);
        return $response;
    }
}