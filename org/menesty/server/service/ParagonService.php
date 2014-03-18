<?php
/**
 * Created by IntelliJ IDEA.
 * User: Menesty
 * Date: 3/17/14
 * Time: 5:25 PM
 */
include_once(Configuration::get()->getClassPath() . "domain/ParagonItem.php");
include_once(Configuration::get()->getClassPath() . "domain/Paragon.php");


class ParagonService {

    public function createParagon(&$paragon) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("INSERT INTO paragon (`driver_id`,`counterparty_id`,`createdDate`,`order_id`, `price`) VALUES (:driver_id, :userId, CURDATE(), :order_id, :price)");
        $data = array("driver_id" => $paragon->driverId, "userId" => $paragon->userId, "order_id" => $paragon->orderId, "price" => $paragon->price);
        $st->execute($data);
        $paragon->id = $connection->lastInsertId();
    }

    public function createParagonItem(&$paragonItem) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("INSERT INTO paragon_item (`paragonId`,`productNumber`,`count`,`price`,`shortName`) VALUES (:paragonId, :productNumber, :count, :price, :shortName)");
        $st->execute((array)$paragonItem);
    }

    public function loadParagons(){
        $connection = Database::get()->getConnection();
        $st = $connection->query("select * from paragon");
        $st->setFetchMode(PDO::FETCH_CLASS, 'Paragon');
        return $st->fetchAll();
    }

} 