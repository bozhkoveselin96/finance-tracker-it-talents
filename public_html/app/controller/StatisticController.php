<?php


namespace controller;


use exceptions\BadRequestException;
use model\StatisticDAO;

class StatisticController {
    public function getIncomesOutcomes() {
        $response = [];
        $statisticDAO = new StatisticDAO();

        if (isset($_GET['from_date']) && Validator::validateDate($_GET['from_date']) &&
            isset($_GET['to_date']) && Validator::validateDate($_GET['to_date'])) {
            $transactions = $statisticDAO->getTransactionsSum($_SESSION['logged_user'], $_GET['from_date'], $_GET['to_date']);
        } else {
            $transactions = $statisticDAO->getTransactionsSum($_SESSION['logged_user']);
        }
        $response[] = $transactions[0];
        $response[] = $transactions[1];
        return $response;
    }

    public function getSumByCategory() {
        $response = [];
        $statisticDAO = new StatisticDAO();

        if (isset($_GET['from_date']) && Validator::validateDate($_GET['from_date']) &&
            isset($_GET['to_date']) && Validator::validateDate($_GET['to_date'])) {
            $from_date = $_GET['from_date'];
            $to_date = $_GET['to_date'];
            $sumsByCategory = $statisticDAO->getTransactionsByCategory($_SESSION['logged_user'], $from_date, $to_date);
        } else {
            $sumsByCategory = $statisticDAO->getTransactionsByCategory($_SESSION['logged_user']);
        }
        if (isset($_GET['category_type'])) {
            $categoryType = $_GET['category_type'];
            foreach ($sumsByCategory as $item) {
                if ($item->type == $categoryType) {
                    $response['data'][] = $item;
                }
            }
        }

        return $response;
    }

    public function getDataForTheLastXDays() {
        $statisticDAO = new StatisticDAO();
        $howManyDays = intval($_GET['days']);
        $data = $statisticDAO->getForTheLastXDays($_SESSION['logged_user'], $howManyDays);

        $days = [];
        for ($i = 0; $i < $howManyDays; $i++) {
            $date = date('j.m', strtotime('-'.$i.' days', time()));
            $days[$date] = ['outcome'=>0, 'income'=>0];
        }
        foreach ($data as $value) {
            if ($value->category == 1) {
                $days[$value->date]['income'] = $value->sum;
            } else {
                $days[$value->date]['outcome'] = $value->sum;
            }
        }

        return array_reverse($days);
    }
}
