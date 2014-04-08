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

    public function executeExport() {
        //handle only post request
        $method = $_SERVER['REQUEST_METHOD'];

        if($method != "POST")
            return "";

        $data = $this->readStreamData();
        error_log("executeExport :" . $data . "\n", 3, "execute_export.log");

        $jsonData = json_decode($data);

        if (!is_object($jsonData) || !is_array($jsonData->paragons))
            return "";

        $warehouseService = new WarehouseService();
        $paragonService = new ParagonService();

        $paragons = (array)$jsonData->paragons;
        $driverId = $jsonData->driverId;

        foreach ($paragons as $paragon) {
            if (!property_exists($paragon, 'id') || $paragon->id == 0) {
                $paragon->driverId = $driverId;
                $price = 0;

                foreach ($paragon->items as $item)
                    $price += ((double)$item->price * (double)$item->count);

                $paragon->price = $price;

                $paragonService->createParagon($paragon);
            }

            foreach ($paragon->items as $item) {
                $count = $item->count;
                $item->count = $count * -1;
                $warehouseService->exportItem($item);

                $shortName = $warehouseService->loadShortName($item->productNumber);

                $paragonItem = new ParagonItem();
                $paragonItem->count = $count;
                $paragonItem->paragonId = $paragon->id;
                $paragonItem->price = $item->price;
                $paragonItem->productNumber = $item->productNumber;
                $paragonItem->shortName = $shortName;

                $paragonService->createParagonItem($paragonItem);
            }
        }
    }

    public function load() {
        $warehouseItemService = new WarehouseService();
        echo json_encode($warehouseItemService->load());
    }



}