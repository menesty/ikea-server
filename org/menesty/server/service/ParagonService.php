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

    public function createParagonItem(&$item) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("INSERT INTO paragon_item (`paragonId`,`productNumber`,`count`,`price`,`shortName`) VALUES (:paragonId, :productNumber, :count, :price, :shortName)");
        $st->execute(array("paragonId" => $item->paragonId, "productNumber" => $item->productNumber, "count" => $item->count, "price" => $item->price, "shortName" => $item->shortName));
    }

    public function loadParagons(){
        $connection = Database::get()->getConnection();
        $st = $connection->query("select * from paragon order by id desc ");
        $st->setFetchMode(PDO::FETCH_CLASS, 'Paragon');

        return $st->fetchAll();
    }

    public function markDownloaded($id) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("update paragon set downloaded = 1 where `id` = ?");
        $st->execute(array($id));

        return $st->rowCount() > 0;
    }

    public function loadParagonItems($id) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("select * from paragon_item where `paragonId` = ?");
        $st->setFetchMode(PDO::FETCH_CLASS, 'ParagonItem');
        $st->execute(array($id));

        return $st->fetchAll();
    }

    public function deleteItemById($id){
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("DELETE FROM paragon_item where `id` = ?");
        $st->execute(array($id));

        return $st->rowCount() > 0;
    }

    public function deleteById($id) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("DELETE FROM paragon where `id` = ?");
        $st->execute(array($id));

        return $st->rowCount() > 0;
    }

    public function sendParagonByEmail(array $paragons){
        include_once(Configuration::get()->getLibPath() . "mail/class.phpmailer.php");

        $mail = new PHPMailer(true);
        $mail->IsSMTP();

        try {
            $mail->SMTPDebug  = false;                     // enables SMTP debug information (for testing)
            $mail->SMTPAuth   = true;                  // enable SMTP authentication
            $mail->SMTPSecure = "ssl";                 // sets the prefix to the servier
            $mail->Host       = "smtp.gmail.com";      // sets GMAIL as the SMTP server
            $mail->Port       = 465;                   // set the SMTP port for the GMAIL server

            $emailAccount = Configuration::get()->getEmailAccount();

            $mail->Username   = $emailAccount[0];  // GMAIL username
            $mail->Password   = $emailAccount[1];            // GMAIL password

            $mail->AddAddress('urbano_rider@yahoo.ca', 'Urbano');

            $mail->SetFrom($emailAccount[0], 'Tablet Facture');

            $mail->Subject = 'Paragons generated from system';
            $mail->AltBody = 'Driver pragon!'; // optional - MsgHTML will create an alternate automatically
            $mail->Body = 'Driver pragon!';

            foreach ($paragons as $key => $value)
                $mail->AddStringAttachment($value, "paragon_" . $key . ".epp"); // attachment

            $mail->Send();
            echo "Message Sent OK</p>\n";
        } catch (phpmailerException $e) {
            echo $e->errorMessage(); //Pretty error messages from PHPMailer
        } catch (Exception $e) {
            echo $e->getMessage(); //Boring error messages from anything else!
        }
    }

    public function generateEpp($items){
        $totalPrice = 0;

        foreach($items as $item)
            $totalPrice += $item->price * $item->count;

        $totalItem = new ParagonItem();
        $totalItem->price = $totalPrice;
        $totalItem->count = 1;

        ob_start();
echo <<< EOT
[INFO]\r
"1.05",3,1250,"Subiekt GT","#29","13-05-05","Orijana Trading Poland sp. z o.o.","Warszawa","01-207","Grzybowska 85c","795-248-80-01","MAG","Główny","Magazyn główny",,1,20140314134842,20140314134842,"Szef",20140314134842,"Polska","PL",,0\r
\r
[NAGLOWEK]\r
EOT;
printf("\"PA\",1,0,583,,,\"583/03\",,,,,,,,,,,,\"Detal\",\"Sprzedaø detaliczna\",\"Warszawa\",20140314000000,20140314000000,,3,0,\"Detaliczna\",%s,%s,%s,368.4900,,0.0000,,20140314000000,%s,%s,0,0,1,0,\";Szef\",,,0.0000,0.0000,\"PLN\",1.0000,,,,,0,0,0,,0.0000,,0.0000,,,0\r\n", $totalItem->format($totalItem->getPrice()), $totalItem->format($totalItem->getTaxPay()), $totalItem->format($totalItem->getPriceWat()),/****/ $totalItem->format($totalItem->getPriceWat()), $totalItem->format($totalItem->getPriceWat()));
echo <<< EOT

[ZAWARTOSC]\r

EOT;
$index=1;
foreach ($items as $item)
printf("%s,1,\"%s\",1,0,0,1,0.0000,0.0000,\"szt.\",%s,%s,0.0000,%s,%s,23.0000,%s,%s,%s,%s,,   \r\n", $index++, $item->productNumber, $item->count, $item->count, $item->format($item->getPrice()), $item->format($item->getPriceWat()), $item->format($item->getPriceWatTotal()), $item->format($item->getTaxPay()), $item->format($item->getPriceTotal()), $item->format($item->getPriceWatTotal()));
echo <<< EOT
\r
[NAGLOWEK]\r
"TOWARY"\r
\r
[ZAWARTOSC]\r

EOT;
foreach ($items as $item)
printf("1,%s,,,%s,%s,%s,,,\"szt . \",\"23\",23.0000,\"23\",23.0000,0.0000,0.0000,,0,,,,0.0000,0,,,0,\"szt . \",0.0000,0.0000,,0,,0,0,,,,,,,,\r\n",$item->productNumber, $item->shortName, $item->shortName, $item->shortName);
echo <<< EOT
\r
[NAGLOWEK]\r
"CENNIK"\r
\r
[ZAWARTOSC]\r

EOT;
foreach($items as $item) {
    printf("\"%s\",\"Detaliczna\",%s,%s,2.0000,%s,%s\r\n", $item->productNumber, $item->format($item->getRetail()), $item->format($item->getRetailWat()), $item->format($item->getMarginPercent()), $item->format($item->getMargin()));
    printf("\"%s\",\"Hurtowa\",%s,%s,2.0000,%s,%s\r\n", $item->productNumber, $item->format($item->getRetail()), $item->format($item->getRetailWat()), $item->format($item->getMarginPercent()), $item->format($item->getMargin()));
    printf("\"%s\",\"Specjalna\",%s,%s,0.0000,0.0000,0.0000\r\n", $item->productNumber, $item->format($item->getPrice()), $item->format($item->getPriceWat()));
}
echo <<< EOT
\r
[NAGLOWEK]\r
"GRUPYTOWAROW"\r
\r
[ZAWARTOSC]\r

EOT;
foreach($items as $item)
    printf("\"%s\",\"Podstawowa\",\r\n", $item->productNumber);
echo <<< EOT
\r
[NAGLOWEK]\r
"CECHYTOWAROW"\r
\r
[ZAWARTOSC]\r
\r
EOT;
        $page = ob_get_contents();
        ob_end_clean();
        return $page;
    }

}
