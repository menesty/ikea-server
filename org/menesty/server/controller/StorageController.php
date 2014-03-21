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
                   $price += (double)$item->price;

                $paragon->price = $price;

                $paragonService->createParagon($paragon);
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

                $paragonService->createParagonItem($paragonItem);
            }
        }
    }

    public function load() {
        $warehouseItemService = new WarehouseService();
        echo json_encode($warehouseItemService->load());
    }

    public function paragons() {
        $paragonService = new ParagonService();
        echo json_encode($paragonService->loadParagons());
    }

    /**
     * @Path({id}/{action})
     */
    public function paragon($id, $action){
        $paragonService = new ParagonService();

        $items = $paragonService->loadParagonItems($id);

        $action = strtolower($action);
        switch($action) {
            case "epp" :
                header('Content-Type: text/html; charset=ISO-8859-2');
                $data = $paragonService->generateEpp($items);
                echo mb_convert_encoding($data, "ISO-8859-2", "UTF-8");
                break;
            default :
                echo json_encode($items);
        }
    }

}