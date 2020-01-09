<?php


namespace controller;


use exceptions\BadRequestException;
use model\StatisticDAO;

class StatisticController {
    public function getIncomesOutcomes() {
        $statisticDAO = new StatisticDAO();

        if (!empty($_GET['daterange'])) {
            $daterange = explode(" - ", $_GET['daterange']);
            if (count($daterange) != 2) {
                throw new BadRequestException("Please select valid daterange.");
            }
            $from_date = date_format(date_create($daterange[0]), "Y-m-d");
            $to_date = date_format(date_create($daterange[1]), "Y-m-d");
            if (!Validator::validateDate($from_date) || !Validator::validateDate($to_date)) {
                throw new BadRequestException("Not valid dates.");
            }
            $transactions = $statisticDAO->getTransactionsSum($_SESSION['logged_user'], $from_date, $to_date);
        } else {
            $transactions = $statisticDAO->getTransactionsSum($_SESSION['logged_user']);
        }
        return new ResponseBody(null, $transactions);
    }

    public function getSumByCategory() {
        $statisticDAO = new StatisticDAO();

        if (!empty($_GET['daterange'])) {
            $daterange = explode(" - ", $_GET['daterange']);
            if (count($daterange) != 2) {
                throw new BadRequestException("Please select valid daterange.");
            }
            $from_date = date_format(date_create($daterange[0]), "Y-m-d");
            $to_date = date_format(date_create($daterange[1]), "Y-m-d");
            if (!Validator::validateDate($from_date) || !Validator::validateDate($to_date)) {
                throw new BadRequestException("Not valid dates.");
            }
            $sumsByCategory = $statisticDAO->getTransactionsByCategory($_SESSION['logged_user'], $from_date, $to_date);
        } else {
            $sumsByCategory = $statisticDAO->getTransactionsByCategory($_SESSION['logged_user']);
        }
        $response = [];
        if (isset($_GET['category_type'])) {
            $categoryType = $_GET['category_type'];
            foreach ($sumsByCategory as $item) {
                if ($item->type == $categoryType) {
                    $response[] = $item;
                }
            }
        } else {
            $response = $sumsByCategory;
        }

        return new ResponseBody(null, $response);
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

        return new ResponseBody(null, array_reverse($days));
    }
}
