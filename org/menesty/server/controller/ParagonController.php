<?php
/**
 * User: Menesty
 * Date: 4/2/14
 * Time: 10:19 AM
 */
include_once(Configuration::get()->getClassPath() . "AbstractController.php");
include_once(Configuration::get()->getClassPath() . "service/WarehouseService.php");
include_once(Configuration::get()->getClassPath() . "service/ParagonService.php");

class ParagonController extends AbstractController {
    private $warehouseService;

    public function __construct() {
        $this->warehouseService = new WarehouseService();
    }

      public function executeExport(){
          if ($_SERVER['REQUEST_METHOD'] != "POST")
              throw new Exception($_SERVER['REQUEST_METHOD'] . " method not supported");

          $data = $this->readStreamData();

          $json = json_decode($data);

          if (!is_object($json) || !is_array($json->paragons))
              return "";

          $paragonService = new ParagonService();

          $paragons = (array)$json->paragons;
          $driverId = $json->driverId;

          //check if product is available
          $allowedParagons = $this->validatePragons($driverId, $paragons);

          if (sizeof($allowedParagons) > 0) {
               foreach ($allowedParagons as $paragon){
                   $paragonService->createParagon($paragon);

                   foreach ($paragon->items as $item) {
                       $count = $item->count;
                       $item->count = $count * -1;

                       $this->warehouseService->exportItem($item);

                       $item->count = $count;

                       $paragonService->createParagonItem($paragonItem);
                   }
               }
          }


      }

    private function validatePragons($driverId, $paragons) {
        $allowedParagons = array();

        foreach ($paragons as $paragon) {
            $currentParagon = new Paragon();
            $currentParagon->driverId = $driverId;
            $currentParagon->userId = $paragon->userId;
            $currentParagon->orderId = $paragon->orderId;

            $price = 0;
            $items = array();

            foreach ($paragon->items as $item) {
                $warehouseItem = $this->warehouseService->loadStoreItem($item->productNumber);

                if ($warehouseItem && $warehouseItem->count >= $item->count) {
                    $paragonItem = new ParagonItem();
                    $paragonItem->count = $item->count;
                    $paragonItem->price = $item->price;
                    $paragonItem->productNumber = $item->productNumber;
                    $paragonItem->shortName = $warehouseItem->shortName;
                    $items[] = $warehouseItem;

                    $price += ((double)$item->price * (double)$item->count);
                }
            }

            if (sizeof($items) > 0) {
                $currentParagon->price = $price;
                $currentParagon->items = $items;
                $allowedParagons[] = $currentParagon;
            }
        }

        return $allowedParagons;
    }
}