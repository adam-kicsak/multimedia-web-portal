<?php

namespace Controller;

class Index implements \MVC\iController {


    // kérés feldolgozása
    public function processQuery() {

        header('Location: ' . ($_SESSION['authentication']->isAuthenticated() ? 'Profil' : 'Search'));
        exit();
    }

    /* singleton minta */

    private static $instance;
    private static $c = __CLASS__;

    private function __construct() {

    }

    public static function instance() {
        if (!(self::$instance instanceof self::$c)) {
            self::$instance = new self::$c();
        }
        return self::$instance;
    }

    /* singleton minta vége */
}

?>
