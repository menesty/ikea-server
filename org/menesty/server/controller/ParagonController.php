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
    private $paragonService;

    public function __construct() {
        $this->warehouseService = new WarehouseService();
        $this->paragonService = new ParagonService();
    }

    public function executeExport() {
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
            $ids = array();
            foreach ($allowedParagons as $paragon) {
                $paragonService->createParagon($paragon);
                $ids[] = $paragon->id;

                foreach ($paragon->items as $item) {
                    $count = $item->count;
                    $item->count = $count * -1;

                    $this->warehouseService->exportItem($item);

                    if (!((boolean) $item->zestav))
                        $this->warehouseService->updateWeight($item->productId, $item->box, $item->weight);

                    $item->count = $count;
                    $item->paragonId = $paragon->id;

                    $paragonService->createParagonItem($item);
                }
            }

            $this->sendToEmail($ids);
        }
    }

    private function sendToEmail(array $ids) {
        $data = array();

        foreach ($ids as $id) {
            $items = $this->paragonService->loadParagonItems($id);
            $data[$id] = $this->paragonService->generateEpp($items);
        }

        $this->paragonService->sendParagonByEmail($data);
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
                    $paragonItem->zestav = $warehouseItem->zestav;

                    if(!((boolean) $paragonItem->zestav)) {
                        $paragonItem->weight = $item->weight;
                        $paragonItem->box = $item->box;
                        $paragonItem->productId = $item->productId;
                    }

                    $items[] = $paragonItem;

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

    public function defaultAction() {
        echo json_encode($this->paragonService->loadParagons());
    }

    /**
     * @Path({id}/{action})
     */
    public function details($id, $action = ""){
        $items = $this->paragonService->loadParagonItems($id);

        $action = strtolower($action);

        switch($action) {
            case "epp" :
                header('Content-Type: text/html; charset=ISO-8859-2');
                $data = $this->paragonService->generateEpp($items);

                echo mb_convert_encoding($data, "ISO-8859-2", "UTF-8");

                $this->paragonService->markDownloaded($id);

                break;
            case "email" :
                $this->sendToEmail(array($id));
                break;
            default :
                echo json_encode($items);
        }
    }

    /**
     * @Path({id})
     */
    public function cancel($id){
        $items = $this->paragonService->loadParagonItems($id);

        foreach($items as $item) {
            $this->warehouseService->deleteBy($item->productNumber, $item->count * -1, $item->price);
            $this->paragonService->deleteItemById($item->id);
        }

        $this->paragonService->deleteById($id);
    }

}