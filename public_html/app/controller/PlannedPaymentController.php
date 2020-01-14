<?php


namespace controller;


use exceptions\BadRequestException;
use exceptions\ForbiddenException;
use interfaces\Deletable;
use interfaces\Editable;
use exceptions\MethodNotAllowedException;
use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\planned_payments\PlannedPayment;
use model\planned_payments\PlannedPaymentDAO;
use model\transactions\Transaction;
use model\transactions\TransactionDAO;

class PlannedPaymentController implements Editable, Deletable {
    public function add() {
        if (isset($_POST['add_planned_payment']) && isset($_POST['day_for_payment']) &&
            isset($_POST['amount']) && isset($_POST['account_id']) && isset($_POST['category_id'])) {

            if (!Validator::validateAmount($_POST["amount"])) {
                throw new BadRequestException("Amount must be between 0 and " . MAX_AMOUNT . " inclusive!");
            } elseif (!Validator::validateDayOfMonth($_POST["day_for_payment"])) {
                throw new BadRequestException("Day must be have valid day from current month!");
            } elseif (!Validator::validateCurrency($_POST["currency"])) {
                throw new BadRequestException(MSG_SUPPORTED_CURRENCIES);
            }

            $plannedPaymentDAO = new PlannedPaymentDAO();
            $accountDAO = new AccountDAO();
            $categoryDAO = new CategoryDAO();
            $account = $accountDAO->getAccountById($_POST['account_id']);
            $category = $categoryDAO->getCategoryById($_POST['category_id'], $_SESSION['logged_user']);
            $plannedPayment = new PlannedPayment($_POST['day_for_payment'], $_POST['amount'], $_POST["currency"], $account, $category);

            if ($account && $category && $account->getOwnerId() == $_SESSION['logged_user']) {
                $plannedPaymentDAO->create($plannedPayment);
                return new ResponseBody("Planned payment added successfully!", $plannedPayment);
            }
        }
        throw new MethodNotAllowedException("Method not allowed!");
    }

    public function getAll() {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $plannedPaymentsDAO = new PlannedPaymentDAO();
            $plannedPayments = $plannedPaymentsDAO->getAll($_SESSION['logged_user']);
            return new ResponseBody(null, $plannedPayments);
        }
        throw new MethodNotAllowedException("Method not allowed!");
    }

    public function edit() {
        if (isset($_POST["edit"])) {
            $planned_payment_DAO = new PlannedPaymentDAO();
            $planned_payment = $planned_payment_DAO->getPlannedPaymentById($_POST["planned_payment_id"]);

            if (!$planned_payment) {
                throw new ForbiddenException("This planned payment is not yours.");
            } elseif ($planned_payment->getAccount()->getOwnerId() != $_SESSION['logged_user']) {
                throw new ForbiddenException("This account is not yours.");
            } elseif (!Validator::validateDayOfMonth($_POST["day_for_payment"])) {
                throw new BadRequestException("Day must be have valid day from current month.");
            } elseif (!Validator::validateAmount($_POST['amount'])) {
                throw new BadRequestException("Amount must be between 0 and " . MAX_AMOUNT . " inclusive");
            } elseif (!Validator::validateStatusPlannedPayment($_POST['status'])) {
                throw new BadRequestException("Status must be Active or Not active.");
            }

            $planned_payment->setStatus($_POST['status']);
            $planned_payment->setDayForPayment($_POST['day_for_payment']);
            $planned_payment->setAmount($_POST['amount']);

            $planned_payment_DAO->editPlannedPayment($planned_payment);
            return new ResponseBody("Planned payment edited successfully!", $planned_payment);
        }
        throw new MethodNotAllowedException("Method not allowed!");
    }

    public function delete(){
        if ($_POST["delete"]) {
            $planned_payment_DAO = new PlannedPaymentDAO();
            $planned_payment = $planned_payment_DAO->getPlannedPaymentById($_POST["planned_payment_id"]);

            if ($planned_payment && $planned_payment->getAccount()->getOwnerId() == $_SESSION['logged_user']) {
                $planned_payment_DAO->deletePlannedPayment($planned_payment);
                return new ResponseBody("Planned payment deleted successfully!", $planned_payment);
            } else {
                throw new ForbiddenException("This transaction is not yours.");
            }
        }
        throw new MethodNotAllowedException("Method not allowed!");
    }

    public function executePlannedPayments() {
        $current_day = date("d");
        $last_day =  date("t", strtotime($current_day));
        $isLastDay = false;
        if ($current_day == $last_day) {
            $isLastDay = true;
        }

        $time_event = date("Y-m-d H:i:s");
        $planned_paymentsDAO = new PlannedPaymentDAO();
        $planned_payments = $planned_paymentsDAO->getPlannedPaymentsForToday($isLastDay);

        $transactionDAO = new TransactionDAO();
        /** @var PlannedPayment $planned_payment */
        foreach ($planned_payments as $planned_payment) {
            /** @var Transaction $transaction */
            $transaction = new Transaction(
                $planned_payment->getAmount(),
                $planned_payment->getAccount(),
                $planned_payment->getCurrency(),
                $planned_payment->getCategory(),
                "Planned payment",
                $time_event);
            if (!$transactionDAO->getSimilarTransaction($planned_payment)) {
                $transactionDAO->create($transaction);
            }
        }
    }
}