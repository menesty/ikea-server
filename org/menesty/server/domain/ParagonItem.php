<?php

/**
 * User: Menesty
 * Date: 1/19/14
 * Time: 11:35 AM
 */
class ParagonItem {
    public $paragonId;
    public $productNumber;
    public $count;
    public $price;
    public $shortName;

    public $wat = 23;


    public function getRetailWat() {
        return $this->round($this->getRetail() * $this->getWatCof());
    }

    public function getMarginPercent() {
        return round(($this->getMargin() / $this->getRetail()) * 100);
    }

    public function getRetail() {
        return round($this->getPrice() * 1.02);
    }

    public function getPrice() {
        return round($this->price / $this->getWatCof());
    }

    private function getWatCof() {
        return (double) $this->wat / (double) 100 + 1;
    }

    public function getMargin() {
        return round($this->getRetail() - $this->getPrice());
    }

    public function getPriceWat() {
        return round($this->price);
    }

    public function getPriceWatTotal() {
        return $this->round($this->getPriceWat() * $this->count);
    }

    public function getPriceTotal() {
        return round($this->getPrice() * $this->count);
    }

    public function getTaxPay() {
        return round(($this->getPriceWat() - $this->getPrice()) * $this->count);
    }


    private function round($value) {
        return round($value, 2, PHP_ROUND_HALF_UP);
    }

    public function format($value) {
        $valueStr = $value ."";

        $pos = strlen($valueStr) - (strpos($valueStr, ".") + 1);

        for ($i = 4 - $pos; $i > 0; $i--)
            $valueStr .= "0";

        return $valueStr;
    }
}