<?php


namespace controller;


use exceptions\BadRequestException;

class CurrencyController {

    public function currencyConverter($base_price, $from_currency, $to_currency) {

        if (!Validator::validateCurrency($from_currency) || !Validator::validateCurrency($to_currency)) {
            throw new BadRequestException("Not supported currency!");
        }
        $result = $this->getCurrencyRateAndConvert($base_price, $from_currency, $to_currency);
        return $result;
    }

    public function getCurrencyRateAndConvert($amount, $from_currency, $to_currency) {
        $req_url = "https://api.exchangerate-api.com/v4/latest/$from_currency";
        $response_json = file_get_contents($req_url);

        if(false !== $response_json) {
            $response_object = json_decode($response_json);
            $base_price = $amount;
            $converted_price = round(($base_price * $response_object->rates->$to_currency), 2);
            $convertedResult = "$base_price $from_currency - $converted_price $to_currency";
            return $convertedResult;
        }
        return false;
    }
}