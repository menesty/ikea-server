<?php
/**
 * User: Menesty
 * Date: 12/28/13
 * Time: 7:28 PM
 */

class Router {

    public function delegate() {
        $route = (empty($_GET['route'])) ? '' : $_GET['route'];

        try {
            $controllerData = $this->getController($route);
            $controllerInstance = $controllerData[0];
            $controllerArg = $controllerData[1];
            $action = "defaultAction";
            if (sizeof($controllerArg) > 0 && method_exists($controllerInstance, $controllerArg[0]))
                $action = array_shift($controllerArg);

            if (!method_exists($controllerInstance, $action))
                throw new BadMethodCallException($action);



/*            $method = new ReflectionMethod($controllerInstance, $action);
preg_match_all('/@Path(.*?)\n/', $method->getDocComment(), $annotations);
var_dump($annotations);*/

            $controllerInstance->$action($controllerArg);
        } catch (Exception $e) {
            //init default IndexController
            echo $e->getMessage() . "<br />";
            echo $e;
        }

    }

    private function getController($route) {
        $pathParts = explode("/", $route);
        $currentPath = Configuration::get()->getControllerPath() . DIRECTORY_SEPARATOR;
        $instance = null;

        while ($val = array_shift($pathParts)) {
            $controllerName = ucfirst($val) . "Controller";
            $fileName = $currentPath . $controllerName . ".php";
            if (is_file($fileName)) {
                if (is_readable($fileName) == false)
                    throw new Exception("File with controller not accessible :" . $fileName);

                include_once($fileName);

                if (class_exists($controllerName)) {
                    $instance = new $controllerName;
                    break;
                } else
                    throw new Exception("Controller " . $controllerName . " not exist in file " . $fileName);

            } else if (is_dir($currentPath . $val))
                $currentPath .= $val . DIRECTORY_SEPARATOR;


        }
        if ($instance == null)
            throw new Exception("Controller not found");

        return array($instance, $pathParts);
    }
}