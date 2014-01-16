<?php
include_once(Configuration::get()->getClassPath() . "/domain/WarehouseItem.php");

/**
 * User: Menesty
 * Date: 12/30/13
 * Time: 7:59 PM
 */
class WarehouseItemService
{
    public function clearByOrderId($orderId)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("delete from warehouse_item where orderId = ?");
        $st->bindParam(1, $orderId);
        $st->execute();
    }

    public function load()
    {
        $connection = Database::get()->getConnection();
        $st = $connection->query('SELECT `productId`,`productNumber`,`price`, sum(`count`) as `count` ,`weight`,`zestav`,`shortName`,`allowed`,`orderId`, `visible` from warehouse_item group by `productId` having sum(`count`)>0');
        $st->setFetchMode(PDO::FETCH_CLASS, 'WarehouseItem');
        return $st->fetchAll();
    }

    public
    function insertData(array $items)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("INSERT INTO warehouse_item (`productId`,`productNumber`,`price`,`count`,`weight`,`zestav`,`shortName`,`allowed`,`orderId`, `visible`) VALUES (:productId, :productNumber, :price, :count, :weight, :zestav, :shortName, :allowed, :orderId, :visible)");
        foreach ($items as $item) {
            $item->zestav = (int)$item->zestav;
            $item->visible = (int)$item->visible;
            $item->allowed = (int)$item->allowed;
            $st->execute((array)$item);
        }


    }
} 