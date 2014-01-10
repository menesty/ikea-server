<?php

/**
 * User: Menesty
 * Date: 12/28/13
 * Time: 6:13 PM
 */
class SyncController {

    public function __construct() {
        echo __FILE__ . "<br />";
    }

    public function view() {
        $jsonRawData = file_get_contents("test.txt");

        var_dump(json_decode($jsonRawData));

        //before update
    }

    private function readStreamData(){
        return Configuration::get()->isDevMode() ? file_get_contents("test.txt") : file_get_contents('php://input');
    }

    public function update() {
        $rawData = $this->readStreamData();

        $jsonData = json_decode($rawData);
        if(is_array($jsonData) && sizeof($jsonData) > 0) {
          //clear data related to orderId
          $jsonData[0]->orderId;


        }
        //file_put_contents("test.txt", $rawData);
        //json_decode($rawData);

        echo __METHOD__ . "<br />";
    }
} 