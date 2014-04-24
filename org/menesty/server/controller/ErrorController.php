<?php
/**
 * Created by IntelliJ IDEA.
 * User: Menesty
 * Date: 4/24/14
 * Time: 11:02 AM
 */
include_once(Configuration::get()->getClassPath() . "AbstractController.php");

class ErrorController extends AbstractController {

    /**
     * @Path({version})
     */
    public function defaultAction($version = "desktop") {
        $data = $this->readStreamData();
        $data =  $version . ":\n\r". $data;
        error_log($data . "\n\r", 3, "application_errors.log");
    }
} 