<?php

    // UTF-8 kódolású az oldal
    header('Content-Type: text/html; charset=utf-8');

    // konfiguráció betöltése
    include('portal.config.php');
    include('portal.autoload.php');

    try {
        // a front controller feldolgozza a kérést
        $view = \Controller\FrontController::instance()->processQuery();
        
        // nézet betöltése
        if(empty($view))
            include 'Template/empty.php';
        else
            include 'Template/' . $view;
        
    } catch(Exception $e) {
        include('Template\fatalError.php');
    }
?>