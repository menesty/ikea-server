<?php
include_once(Configuration::get()->getClassPath() . "AbstractController.php");
include_once(Configuration::get()->getClassPath() . "service/WarehouseService.php");

/**
 * User: Menesty
 * Date: 12/28/13
 * Time: 6:13 PM
 */
class SyncController extends AbstractController {

    public function __construct() {
        echo __FILE__ . "<br />";
    }

    /**
     * @Method(POST)
     * @Path({clean})
     */
    public function update($clean = false) {
        $data = $this->readStreamData();
        error_log("update :" . $data . "\n", 3, "server_update.log");
        $jsonData = json_decode($this->readStreamData());

        if (!is_array($jsonData))
            return;

        $warehouseItemService = new WarehouseService();

        if ($clean)
            $warehouseItemService->clear();

        $warehouseItemService->insertData($jsonData);
    }

    public function defaultAction(){

    }

}