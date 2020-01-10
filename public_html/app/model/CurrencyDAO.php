<?php


namespace model;


class CurrencyDAO {
    public function currencyConverter($amount, $from_currency, $to_currency) {
        $req_url = "model/$from_currency.json";
        $response_json = file_get_contents($req_url);
        $getLastUpdate = json_decode($response_json);
        if (strtotime($getLastUpdate->date) != strtotime(date("Y-m-d"))) {
            file_put_contents("model/BGN.json", file_get_contents('https://api.exchangerate-api.com/v4/latest/BGN'));
            file_put_contents("model/EUR.json", file_get_contents('https://api.exchangerate-api.com/v4/latest/EUR'));
            file_put_contents("model/USD.json", file_get_contents('https://api.exchangerate-api.com/v4/latest/USD'));
        }

        $req_url = "model/$from_currency.json";
        $response_json = file_get_contents($req_url);

        if(false !== $response_json) {
            $response_object = json_decode($response_json);
            $base_price = $amount;
            return round(($base_price * $response_object->rates->$to_currency), 2);
        }
        return false;
    }
}