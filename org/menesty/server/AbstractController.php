<?php
/**
 * Created by IntelliJ IDEA.
 * User: Menesty
 * Date: 4/2/14
 * Time: 10:21 AM
 */

class AbstractController {

    protected function readStreamData() {
        return file_get_contents('php://input');
    }

} 