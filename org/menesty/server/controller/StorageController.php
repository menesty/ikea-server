<?php
include_once(Configuration::get()->getClassPath() . "service/WarehouseService.php");
include_once(Configuration::get()->getClassPath() . "service/ParagonService.php");

/**
 * User: Menesty
 * Date: 1/17/14
 * Time: 1:42 PM
 */
class StorageController {
    public function __construct() {
    }

    private function readStreamData() {
        return file_get_contents('php://input');
    }

    public function load() {
        $warehouseItemService = new WarehouseService();
        echo json_encode($warehouseItemService->load());
    }

}