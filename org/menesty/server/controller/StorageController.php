<?php
include_once(Configuration::get()->getClassPath() . "service/WarehouseService.php");
include_once(Configuration::get()->getClassPath() . "domain/ParagonItem.php");

/**
 * User: Menesty
 * Date: 1/17/14
 * Time: 1:42 PM
 */
class StorageController {
    public function __construct() {
    }

    private function readStreamData() {
        return file_get_contents("tablet.data.json");//file_get_contents('php://input');
    }

    public function executeExport() {
        //handle only post request
       /* $method = $_SERVER['REQUEST_METHOD'];

        if($method != "POST")
            return "";*/

        $data = $this->readStreamData();
        error_log("executeExport :" . $data . "\n", 3, "execute_export.log");

        $jsonData = json_decode($data);

        if (!is_object($jsonData) || !is_array($jsonData->paragons))
            return;

        $warehouseService = new WarehouseService();
        $paragons = (array)$jsonData->paragons;
        $driverId = $jsonData->driverId;

        foreach ($paragons as $paragon) {
            if (!property_exists($paragon, 'id') || $paragon->id == 0) {
                $paragon->driverId = $driverId;
                $warehouseService->createParagon($paragon);
            }

            foreach ($paragon->items as $item) {
                $storeItem = $warehouseService->loadStoreItem($item->productNumber);
                $count = $item->count;
                $item->count = $count * -1;
                $warehouseService->exportItem($item);

                $paragonItem = new ParagonItem();
                $paragonItem->count = $count;
                $paragonItem->paragonId = $paragon->id;
                $paragonItem->price = $item->price;
                $paragonItem->productNumber = $item->productNumber;
                $paragonItem->shortName = $storeItem->shortName;

                $warehouseService->createParagonItem($paragonItem);
            }
        }
    }

    public function load() {
        $warehouseItemService = new WarehouseService();
        echo json_encode($warehouseItemService->load());
    }

}