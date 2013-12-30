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
    }

    public function update() {
        $rawData = file_get_contents('php://input');

        file_put_contents("test.txt", $rawData);
        //json_decode($rawData);

        echo __METHOD__ . "<br />";
    }
} 