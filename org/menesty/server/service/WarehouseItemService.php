<?php

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
        $st = $connection->prepare("delete from warehouse_item where orderId = ?1");
        $st->bindParam(1, $orderId);
        $st->execute();
    }

    public function insertData(array $items)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("INSERT INTO warehouse_item (`productId`,`productNumber`,`price`,`count`,`weight`,`zestav`,`shortName`, `allowed`,`orderId`,`invoicePdf`, `visible`) VALUES (:productId, :productNumber, :price, :count, :weight, :zestav, :shortName, :allowed, :orderId, :invoicePdf, :visible)");
        foreach ($items as $item)
            $st->execute((array)$item);


    }
} 