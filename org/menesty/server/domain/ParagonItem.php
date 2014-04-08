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
        return $this->round(($this->getMargin() / $this->getRetail()) * 100);
    }

    public function getRetail() {
        return $this->round($this->getPrice() * 1.02);
    }

    public function getPrice() {
        return $this->round($this->price / $this->getWatCof());
    }

    private function getWatCof() {
        return (double) $this->wat / (double) 100 + 1;
    }

    public function getMargin() {
        return $this->round($this->getRetail() - $this->getPrice());
    }

    public function getPriceWat() {
        return $this->round($this->price);
    }

    public function getPriceWatTotal() {
        return $this->round($this->getPriceWat() * $this->count);
    }

    public function getPriceTotal() {
        return $this->round($this->getPrice() * $this->count);
    }

    public function getTaxPay() {
        return $this->round(($this->getPriceWat() - $this->getPrice()) * $this->count);
    }


    private function round($value) {
        return round($value, 2, PHP_ROUND_HALF_UP);
    }

    public function format($value) {
        $valueStr = $value ."";

        $pointPos = strpos($valueStr, ".");
        $pos = $pointPos? strlen($valueStr) - $pointPos - 1 : 0;

        if($pos == 0)
            $valueStr .=".";

        for ($i = 4 - $pos; $i > 0; $i--)
            $valueStr .= "0";

        return $valueStr;
    }
}