<?php

/**
 * User: Menesty
 * Date: 12/27/13
 * Time: 11:57 PM
 */
class Configuration {
    private static $instance;

    private $siteRoot;

    private $libPath;

    private $dbHost = "localhost";

    private $dbDriver = "mysql";

    private $dbName = "u532766986_ikea";

    private $dbUser = "u532766986_ikea";

    private $dbPassword = "7qwEWsAQ8M";

    private $controllerPath;

    private $authUser = "desktop";

    private $authPassword = "ikea-desktop";

    private $emailAccount = array ("o.maks.78.len@gmail.com", "LenMaks78");

    const DEV_MODE = "dev";

    const PROD_MODE = "prod";

    private $mode = Configuration::DEV_MODE;

    private $db = null;

    private function __construct() {
        $this->siteRoot = $_SERVER["DOCUMENT_ROOT"];
        $this->classPath = $this->siteRoot . DIRECTORY_SEPARATOR . "org" . DIRECTORY_SEPARATOR . "menesty" . DIRECTORY_SEPARATOR . "server" . DIRECTORY_SEPARATOR;
        $this->controllerPath = $this->classPath . DIRECTORY_SEPARATOR . "controller";
        $this->libPath =  $this->siteRoot . DIRECTORY_SEPARATOR . "lib" . DIRECTORY_SEPARATOR;

    }

    public function getEmailAccount(){
        return $this->emailAccount;
    }

    public function getClassPath() {
        return $this->classPath;
    }

    public function getLibPath() {
        return $this->libPath;
    }

    public function isDevMode() {
        return $this->mode == Configuration::DEV_MODE;
    }

    public static function get() {
        if (!self::$instance)
            self::$instance = new Configuration();

        return self::$instance;
    }

    public function getAuthUser() {
        return $this->authUser;
    }

    public function getAuthPassword() {
        return $this->authPassword;
    }

    public function getSiteRoot() {
        return $this->siteRoot;
    }

    public function getDbHost() {
        return $this->dbHost;
    }

    public function getDbUser() {
        return $this->dbUser;
    }

    public function getDbName() {
        return $this->dbName;
    }

    public function getDbPassword() {
        return $this->dbPassword;
    }

    public function getControllerPath() {
        return $this->controllerPath;
    }

    public function getDbDriver() {
        return $this->dbDriver;
    }

}