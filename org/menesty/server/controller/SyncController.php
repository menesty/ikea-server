<?php
include_once(Configuration::get()->getClassPath() . "service/WarehouseService.php");

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
        return file_get_contents('php://input');
    }

    public function update() {
        $data = $this->readStreamData();
        error_log("update :" . $data . "\n", 3, "server_update.log");
        $jsonData = json_decode($this->readStreamData());

        if (!is_array($jsonData))
            return;

        $warehouseItemService = new WarehouseService();
        //clear order data if exist in db
        if (sizeof($jsonData) > 0)
            $warehouseItemService->clear();

        $warehouseItemService->insertData($jsonData);
    }
} 