<?php

namespace MVC;

interface iController {

    // k�r�s feldolgoz�sa
    function processQuery();

    // singleton elk�r�se
    static function instance();
}

?>