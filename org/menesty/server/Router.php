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

            $method = new ReflectionMethod($controllerInstance, $action);

            $params = $this->getMethodPathParams($method, $controllerArg);
            $method->invokeArgs($controllerInstance, $this->getMethodArg($method, $params));
        } catch (Exception $e) {
            //init default IndexController
            echo $e->getMessage() . "<br />";
        }

    }

    private function getMethodArg(ReflectionMethod $method, $params){
        $args = array();

        foreach ($method->getParameters() as $param) {
            if (array_key_exists($param->getName(), $params))
                $args[] = $params[$param->getName()];
            else if ($param->isDefaultValueAvailable())
                $args[] = $param->getDefaultValue();
            else
                throw new Exception("Parameter ". $param->getName() ." not found");
        }

        return $args;
    }

    private function getMethodPathParams(ReflectionMethod $method, $arg){
        preg_match('/@Path(.*?)\n/', $method->getDocComment(), $annotations);

        $resArg = array();

        if (sizeof($annotations) > 1) {
            preg_match_all('/{(.*?)}/', $annotations[1], $params);

            if (sizeof($params) > 1)
                foreach ($params[1] as $data)
                    $resArg[$data] = array_shift($arg);
        }

        return $resArg;
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