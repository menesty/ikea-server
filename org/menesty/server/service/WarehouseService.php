<?php
include_once(Configuration::get()->getClassPath() . "/domain/WarehouseItem.php");

/**
 * User: Menesty
 * Date: 12/30/13
 * Time: 7:59 PM
 */
class WarehouseService {
    public function clear() {
        //clear only with orderId
        $connection = Database::get()->getConnection();
        $connection->prepare("delete from warehouse where productNumber in (select productNumber from warehouse_item where orderId > 0 )")->execute();
        $connection->prepare("delete from warehouse_item where orderId > 0")->execute();
    }

    public function deleteBy($productNumber, $count, $price) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("DELETE FROM warehouse where `productNumber` = :productNumber and `count` = :count and `price` = :price limit 1");
        $st->execute(array("productNumber" => $productNumber, "count" => $count, "price" =>$price));
        return $st->rowCount() > 0;
    }

    public function exportItem($item) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("INSERT INTO warehouse (`productNumber`,`count`,`price`) VALUES (:productNumber, :count, :price)");
        $st->execute(array("productNumber" => $item->productNumber, "count" => $item->count, "price" =>$item->price));
    }

    public function loadStoreItem($productNumber, $price) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare('SELECT item.`productId`, w.`productNumber`,w.`price`, sum(w.`count`) as `count` ,item.`weight`, item.`zestav`,item.`shortName`,w.`allowed`,item.`orderId`, w.`visible`, item.`box` from warehouse w left join warehouse_item item on(w.`productNumber` = item.`productNumber`) where w.`productNumber` = :productNumber and w.`price` = :price and w.visible = 1 and w.allowed = 1  group by w.`productNumber`, w.`visible`, w.`allowed`, w.`price` having sum(w.`count`) > 0 limit 1;');
        $st->bindParam("productNumber", $productNumber);
        $st->bindParam("price", $price);
        $st->setFetchMode(PDO::FETCH_CLASS, 'WarehouseItem');
        $st->execute();
        return $st->fetch();
    }

    public function loadShortName($productNumber) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare('SELECT shortName FROM warehouse_item where productNumber = :productNumber limit 1');
        $st->bindParam("productNumber", $productNumber);
        $st->setFetchMode(PDO::FETCH_COLUMN, 0);
        $st->execute();
        return $st->fetch();
    }

    public function load() {
        $connection = Database::get()->getConnection();
        $st = $connection->query('SELECT item.`productId`, w.`productNumber`,w.`price`, sum(w.`count`) as `count` ,if(IFNULL(wiw.weight, -1) > 0, wiw.weight, item.`weight`) as weight, item.`zestav`,item.`shortName`,w.`allowed`,item.`orderId`, w.`visible`, if(IFNULL(wiw.weight, -1) > 0, true, false) as checked from warehouse w left join warehouse_item item on(w.`productNumber` = item.`productNumber`) left join warehouse_item_weight wiw on (item.productId=wiw.productId and item.box=wiw.box) group by w.`productNumber`, w.`visible`, w.`allowed`, w.`price` having sum(w.`count`)>0');
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
        $st = $connection->prepare("INSERT INTO warehouse_item (`productId`,`productNumber`,`weight`,`zestav`,`shortName`,`orderId`,`box`) VALUES (:productId, :productNumber, :weight, :zestav, :shortName, :orderId, :box)");
        $countSt = $connection->prepare("select count(productNumber) from  warehouse_item where productNumber = :productNumber");

        foreach ($items as $item) {
            //check if information already exist
            $countSt->bindParam("productNumber", $item->productNumber);
            $countSt->execute();
            $row = $countSt->fetch(PDO::FETCH_NUM);

            if ((int)$row[0] == 0) {
                $box = 1;

                if (preg_match('/\((\d+)\)/', $item->productNumber, $result))
                    $box = $result[1];

                $data = array("zestav" => (int)$item->zestav, "productId" => $item->productId, "productNumber" => $item->productNumber, "weight" => $item->weight, "shortName" => $item->shortName, "orderId" => $item->orderId, "box" => $box);
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

    public function updateWeight($productId, $box, $weight) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare('SELECT count(productId) FROM warehouse_item_weight where productId = :productId and box = :box');
        $st->execute(array("productId" => $productId, "box" => $box));
        $result = $st->fetch(PDO::FETCH_NUM);

        if ((int)$result[0] == 0)
            $st = $connection->prepare("INSERT INTO warehouse_item_weight (`productId`,`box`,`weight`) VALUES (:productId, :box, :weight)");
        else
            $st = $connection->prepare("update warehouse_item_weight set `weight` = :weight where `productId` = :productId and `box` = :box");

        $st->execute(array("productId" => $productId, "box" => $box, "weight" => $weight));
    }
} 