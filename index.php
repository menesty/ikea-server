<?php
include_once("Configuration.php");
include_once("org/menesty/server/DigestAuthentication.php");

$digestAuthentication = new DigestAuthentication();
$digestAuthentication->auth();

include_once("org/menesty/server/Router.php");


$router = new Router();
$router->delegate();

//http://habrahabr.ru/post/31270/
//http://www.php.net/manual/en/function.error-log.php



?>