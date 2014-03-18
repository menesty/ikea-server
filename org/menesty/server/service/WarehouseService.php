<?php
include_once(Configuration::get()->getClassPath() . "/domain/WarehouseItem.php");

/**
 * User: Menesty
 * Date: 12/30/13
 * Time: 7:59 PM
 */
class WarehouseService {
    public function clearByOrderId($orderId) {
        $connection = Database::get()->getConnection();
        $connection->prepare("delete from warehouse_item")->execute();
        $connection->prepare("delete from warehouse")->execute();
    }

    public function exportItem($item) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("INSERT INTO warehouse (`productNumber`,`count`,`price`) VALUES (:productNumber, :count, :price)");
        $st->execute((array)$item);
    }

    public function loadStoreItem($productNumber) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare('SELECT item.`productId`, w.`productNumber`,w.`price`, sum(w.`count`) as `count` ,item.`weight`, item.`zestav`,item.`shortName`,w.`allowed`,item.`orderId`, w.`visible` from warehouse w left join warehouse_item item on(w.`productNumber` = item.`productNumber`) where w.`productNumber` = :productNumber and w.visible = 1 and w.allowed = 1  group by w.`productNumber`, w.`visible`, w.`allowed`, w.`price` having sum(w.`count`) > 0 limit 1;');
        $st->bindParam("productNumber", $productNumber);
        $st->setFetchMode(PDO::FETCH_CLASS, 'WarehouseItem');
        return $st->fetch();
    }

    public function load() {
        $connection = Database::get()->getConnection();
        $st = $connection->query('SELECT item.`productId`, w.`productNumber`,w.`price`, sum(w.`count`) as `count` ,item.`weight`, item.`zestav`,item.`shortName`,w.`allowed`,item.`orderId`, w.`visible` from warehouse w left join warehouse_item item on(w.`productNumber` = item.`productNumber`) group by w.`productNumber`, w.`visible`, w.`allowed`, w.`price` having sum(w.`count`)>0');
        $st->setFetchMode(PDO::FETCH_CLASS, 'WarehouseItem');
        return $st->fetchAll();
    }

    public function insertData(array $items) {
        $connection = Database::get()->getConnection();
        $connection->beginTransaction();
        $this->insertWarehouse($items);
        $this->insertWarehouseItem($items);
        $connection->commit();
    }

    private function insertWarehouseItem(array &$items) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("INSERT INTO warehouse_item (`productId`,`productNumber`,`weight`,`zestav`,`shortName`,`orderId`) VALUES (:productId, :productNumber, :weight, :zestav, :shortName, :orderId)");
        $countSt = $connection->prepare("select count(productNumber) from  warehouse_item where productNumber = :productNumber");

        foreach ($items as $item) {
            //check if information already exist
            $countSt->bindParam("productNumber", $item->productNumber);
            $countSt->execute();
            $row = $countSt->fetch(PDO::FETCH_NUM);

            if ((int)$row[0] == 0) {
                $data = array("zestav" => (int)$item->zestav, "productId" => $item->productId, "productNumber" => $item->productNumber, "weight" => $item->weight, "shortName" => $item->shortName, "orderId" => $item->orderId);
                $st->execute($data);
            }
        }
    }

    private function insertWarehouse(array &$items) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("INSERT INTO warehouse (`productNumber`,`count`,`price`,`visible`,`allowed`) VALUES (:productNumber, :count, :price, :visible, :allowed)");
        foreach ($items as $item) {
            $data = array("visible" => (int)$item->visible, "allowed" => (int)$item->allowed, "productNumber" => $item->productNumber, "count" => $item->count, "price" => $item->price);
            $st->execute($data);
        }
    }
} 