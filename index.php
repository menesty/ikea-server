<?php
include_once("org/menesty/server/DigestAuthentication.php");

$digestAuthentication = new DigestAuthentication();
$digestAuthentication->auth();


echo dirname(__FILE__);

//http://habrahabr.ru/post/31270/
//http://www.php.net/manual/en/function.error-log.php

class Router
{

    private $controllerPath;

    public function setPath($path){

    }


}

?>