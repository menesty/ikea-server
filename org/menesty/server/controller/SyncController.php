<?php
include_once(Configuration::get()->getClassPath() . "/service/WarehouseItemService.php");

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
        var_dump(json_decode($this->readStreamData()));

        //before update
    }

    private function readStreamData() {
        return Configuration::get()->isDevMode() ? file_get_contents("input.update.json") : file_get_contents('php://input');
    }

    public function update() {
        $warehouseItemService = new WarehouseItemService();
        $rawData = $this->readStreamData();

        $jsonData = json_decode($rawData);

        //clear order data if exist in db
        if (is_array($jsonData) && sizeof($jsonData) > 0)
            $warehouseItemService->clearByOrderId($jsonData[0]->orderId);

        $warehouseItemService->insertData($jsonData);

        echo __METHOD__ . "<br />";
    }
} 