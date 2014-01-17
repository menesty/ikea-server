<?php

/**
 * Created by IntelliJ IDEA.
 * User: Menesty
 * Date: 1/17/14
 * Time: 1:42 PM
 */
class StorageController
{
    public function __construct()
    {
        echo __FILE__ . "<br />";
    }

    private function readStreamData()
    {
        return Configuration::get()->isDevMode() ? file_get_contents("input.update.json") : file_get_contents('php://input');
    }

    public function executeExport(){
         $rawData = $this->readStreamData();
    }
} 