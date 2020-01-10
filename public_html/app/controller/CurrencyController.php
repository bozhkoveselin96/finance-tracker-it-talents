<?php


namespace controller;


use exceptions\BadRequestException;

class CurrencyController {

    public function currencyConverter($base_price, $from_currency, $to_currency) {
        return $this->getCurrencyRateAndConvert($base_price, $from_currency, $to_currency);
    }

    private function getCurrencyRateAndConvert($amount, $from_currency, $to_currency) {
        $req_url = "https://api.exchangerate-api.com/v4/latest/$from_currency";
        $response_json = file_get_contents($req_url);

        if(false !== $response_json) {
            $response_object = json_decode($response_json);
            $base_price = $amount;
            return round(($base_price * $response_object->rates->$to_currency), 2);
        }
        return false;
    }
}