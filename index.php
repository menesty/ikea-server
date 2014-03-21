<?php
error_reporting(E_ALL);
include_once("Configuration.php");
include_once("org/menesty/server/DigestAuthentication.php");

$digestAuthentication = new DigestAuthentication();
$digestAuthentication->auth();

include_once("org/menesty/server/Router.php");
include_once("org/menesty/server/Database.php");

$router = new Router();
$router->delegate();

?>