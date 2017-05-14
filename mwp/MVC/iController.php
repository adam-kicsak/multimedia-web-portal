<?php

namespace MVC;

interface iController {

    // kérés feldolgozása
    function processQuery();

    // singleton elkérése
    static function instance();
}

?>