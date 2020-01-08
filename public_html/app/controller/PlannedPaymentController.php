<?php


namespace controller;


use exceptions\BadRequestException;
use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\planned_payments\PlannedPayment;
use model\planned_payments\PlannedPaymentDAO;

class PlannedPaymentController {
    public function add() {
        $response = [];
        if (isset($_POST['add_planned_payment']) && isset($_POST['day_for_payment']) &&
            isset($_POST['amount']) && isset($_POST['account_id']) && isset($_POST['category_id'])) {
            $plannedPaymentDAO = new PlannedPaymentDAO();
            $accountDAO = new AccountDAO();
            $categoryDAO = new CategoryDAO();
            $account = $accountDAO->getAccountById($_POST['account_id']);
            $category = $categoryDAO->getCategoryById($_POST['category_id'], $_SESSION['logged_user']);
            $plannedPayment = new PlannedPayment($_POST['day_for_payment'], $_POST['amount'], $account, $category);

            if (!Validator::validateAmount($plannedPayment->getAmount())) {
                throw new BadRequestException("Amount must be between 0 and" . MAX_AMOUNT . "inclusive");
            } elseif (!Validator::validateDayOfMonth($plannedPayment->getDayForPayment())) {
                throw new BadRequestException("Day must be have valid day from current month");
            }
            if ($account && $category && $account->getOwnerId() == $_SESSION['logged_user']) {
                $plannedPaymentDAO->create($plannedPayment);
                $response['target'] = 'planned_payment';
            }
        }
        return $response;
    }

    public function getAll() {
        $response = [];
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
                $plannedPaymentsDAO = new PlannedPaymentDAO();
                $plannedPayments = $plannedPaymentsDAO->getAll($_SESSION['logged_user']);
                $response['data'] = $plannedPayments;
        }
        return $response;
    }
}