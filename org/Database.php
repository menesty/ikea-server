<?php

/**
 * User: Menesty
 * Date: 12/30/13
 * Time: 5:54 PM
 */
class Database
{

    private $connection;

    public function __construct()
    {
        $connectionUrl = Configuration::get()->getDbDriver() . ":" . Configuration::get()->getDbName();
        $this->connection = new PDO($connectionUrl, Configuration::get()->getDbUser(), Configuration::get()->getDbPassword());

    }
} 