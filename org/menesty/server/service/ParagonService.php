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
        $st = $connection->prepare("INSERT INTO paragon (`driver_id`,`counterparty_id`,`createdDate`,`order_id`, `price`) VALUES (:driver_id, :userId, NOW(), :order_id, :price)");
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

    public function loadParagonItems($id) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("select * from paragon_item where `paragonId` = ?");
        $st->setFetchMode(PDO::FETCH_CLASS, 'ParagonItem');
        $st->execute(array($id));
        return $st->fetchAll();
    }

    public function generateEpp($items){
        ob_start();
echo <<< EOT
[INFO]
"1.05",3,1250,"Subiekt GT","#29","13-05-05","Orijana Trading Poland sp. z o.o.","Warszawa","01-207","Grzybowska 85c","795-248-80-01","MAG","Główny","Magazyn główny",,1,20140314134842,20140314134842,"Szef",20140314134842,"Polska","PL",,0

[NAGLOWEK]
"PA",1,0,583,,,"583/03",,,,,,,,,,,,"Detal","Sprzedaø detaliczna","Warszawa",20140314000000,20140314000000,,3,0,"Detaliczna",375.9100,86.4600,462.3700,368.4900,,0.0000,,20140314000000,462.3700,462.3700,0,0,1,0,";Szef",,,0.0000,0.0000,"PLN",1.0000,,,,,0,0,0,,0.0000,,0.0000,,,0

[ZAWARTOSC]

EOT;
$index=1;
foreach ($items as $item)
printf("%s,1,\"%s\",1,0,0,1,0.0000,0.0000,\"szt.\",%s,%s,0.0000,%s,%s,23.0000,%s,%s,%s,%s,,   \n", $index++, $item->productNumber, $item->count, $item->count, $item->format($item->getPrice()), $item->format($item->getPriceWat()), $item->format($item->getPriceWatTotal()), $item->format($item->getTaxPay()), $item->format($item->getPriceTotal()), $item->format($item->getPriceWatTotal()));
echo <<< EOT

[NAGLOWEK]
"TOWARY"

[ZAWARTOSC]

EOT;
foreach ($items as $item)
printf("1,%s,,,%s,%s,%s,,,\"szt . \",\"23\",23.0000,\"23\",23.0000,0.0000,0.0000,,0,,,,0.0000,0,,,0,\"szt . \",0.0000,0.0000,,0,,0,0,,,,,,,,",$item->productNumber, $item->shortName, $item->shortName, $item->shortName);
echo <<< EOT

[NAGLOWEK]
"CENNIK"

[ZAWARTOSC]

EOT;
foreach($items as $item) {
    printf("\"%s\",\"Detaliczna\",%s,%s,2.0000,%s,%s\n", $item->productNumber, $item->format($item->getRetail()), $item->format($item->getRetailWat()), $item->format($item->getMarginPercent()), $item->format($item->getMargin()));
    printf("\"%s\",\"Hurtowa\",%s,%s,2.0000,%s,%s\n", $item->productNumber, $item->format($item->getRetail()), $item->format($item->getRetailWat()), $item->format($item->getMarginPercent()), $item->format($item->getMargin()));
    printf("\"%s\",\"Specjalna\",%s,%s,0.0000,0.0000,0.0000\n", $item->productNumber, $item->format($item->getPrice()), $item->format($item->getPriceWat()));
}
echo <<< EOT

[NAGLOWEK]
"GRUPYTOWAROW"

[ZAWARTOSC]

EOT;
foreach($items as $item)
    printf("\"%s\",\"Podstawowa\",\n", $item->productNumber);
echo <<< EOT

[NAGLOWEK]
"CECHYTOWAROW"

[ZAWARTOSC]
EOT;
        $page = ob_get_contents();
        ob_end_clean();
        return $page;
    }

}
