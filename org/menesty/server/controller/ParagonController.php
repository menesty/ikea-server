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

    /**
     * @Method(POST, GET)
     */
    public function executeExport() {
        //lock table paragon for read
        $this->paragonService->lock(array("paragon", "warehouse", "warehouse_item_weight", "warehouse AS w",
            "paragon_item", "warehouse_item as item"), array("write", "write", "write", "read", "write", "read"));

        $data = $this->readStreamData();

        $json = json_decode($data);

        if (!is_object($json) || !is_array($json->paragons))
            return "";

        $logData = date("Y-m-d H:i:s") . " : \n\r" . $data . "\n\r";
        error_log($logData , 3, "execute_export.log");


        $paragons = (array)$json->paragons;
        $driverId = $json->driverId;
        $actionId = $json->actionId;

        //check if product is available
        $allowedParagons = $this->validatePragons($driverId, $actionId, $paragons);

        if (sizeof($allowedParagons) > 0) {
            $ids = array();

            foreach ($allowedParagons as $paragon) {
                $this->paragonService->createParagon($paragon);
                $ids[] = $paragon->id;

                foreach ($paragon->items as $item) {
                    $count = $item->count;
                    $item->count = $count * -1;

                    $this->warehouseService->exportItem($item);

                    if (!((boolean) $item->zestav) && !is_null($item->weight))
                        $this->warehouseService->updateWeight($item->productId, $item->box, $item->weight);

                    $item->count = $count;
                    $item->paragonId = $paragon->id;

                    $this->paragonService->createParagonItem($item);
                }
            }

            if (!Configuration::get()->isDevMode())
                $this->sendToEmail($ids);
        }

        $this->paragonService->unlock();
    }

    private function sendToEmail(array $ids) {
        $data = array();
        $paragonIndex = 1;

        foreach ($ids as $id) {
            $paragon = $this->paragonService->loadById($id);
            $items = $this->paragonService->loadParagonItems($id);
            $data[("p" . $paragonIndex . "_" . round($paragon->price))] = $this->paragonService->generateEpp($items);
            $paragonIndex++;
        }

        $this->paragonService->sendParagonByEmail($data);
    }

    private function validatePragons($driverId, $actionId, $paragons) {
        $allowedParagons = array();

        foreach ($paragons as $paragon) {
            $currentParagon = new Paragon();
            $currentParagon->driverId = $driverId;
            $currentParagon->userId = $paragon->userId;
            $currentParagon->orderId = $paragon->orderId;
            $currentParagon->actionId = $actionId;

            $price = 0;
            $items = array();

            foreach ($paragon->items as $item) {
                $warehouseItem = $this->warehouseService->loadStoreItem($item->productNumber, $item->price);

                if ($warehouseItem && $warehouseItem->count >= $item->count) {
                    $paragonItem = new ParagonItem();
                    $paragonItem->count = $item->count;
                    $paragonItem->price = $item->price;
                    $paragonItem->productNumber = $item->productNumber;
                    $paragonItem->shortName = $warehouseItem->shortName;
                    $paragonItem->zestav = $warehouseItem->zestav;

                    if(!((boolean) $paragonItem->zestav)) {
                        if ((boolean)$item->checked)
                            $paragonItem->weight = $item->weight;
                        else
                            $paragonItem->weight = null;

                        $paragonItem->box = $warehouseItem->box;
                        $paragonItem->productId = $warehouseItem->productId;
                    }

                    $items[] = $paragonItem;

                    $price += ((double)$item->price * (double)$item->count);
                } else
                    error_log($currentParagon->orderId . " :" . json_encode($item), 3, "paragon_skip_items.log");
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
        return $this->paragonService->loadParagons();
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

                $this->paragonService->markDownloaded($id);

                return mb_convert_encoding($data, "ISO-8859-2", "UTF-8");
                break;
            case "email" :
                $this->sendToEmail(array($id));
                break;
            default :
                return $items;
        }
    }

    /**
     * @Path({id})
     */
    public function cancel($id){
        $items = $this->paragonService->loadParagonItems($id);

        foreach ($items as $item) {
            $this->warehouseService->deleteBy($item->productNumber, $item->count * -1, $item->price);
            $this->paragonService->deleteItemById($item->id);
        }

        $this->paragonService->deleteById($id);

        return true;
    }

    /**
     * @Path({actionId})
     */
    public function cancelByActionId($actionId){
        $paragons = $this->paragonService->getByActionId($actionId);

        if (is_array($paragons)) {
            foreach ($paragons as $paragon)
                $this->cancel($paragon->id);
            return true;
        }

        return false;
    }

    /**
     * @Path({actionId})
     */
    public function check($actionId) {
        $paragon = $this->paragonService->getByActionId($actionId);

        return is_object($paragon);
    }

}