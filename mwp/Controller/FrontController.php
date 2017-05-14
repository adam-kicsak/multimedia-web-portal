<?php

namespace Controller;

class FrontController implements \MVC\iFrontController {

    public function processQuery() {
    
        // ha a vezérlő paramétere nincs beállítva, vagy nem megfeleő a vezérlő neve,
        // akkor az Index vezérlő kerül beállításra
        if(empty($_GET['_c']) || !preg_match('/[A-Za-z_][A-Za-z0-9_]{0,63}/', $_GET['_c'])) {
            $_GET['_c'] = 'Index';
        }
        $controllerClassName = '\\Controller\\' . $_GET['_c'];
        
        // controller osztály ellenőrzése
        if(!in_array('MVC\\iController', class_implements($controllerClassName)))
            throw new \MVC\ControllerNotFoundException(
                "$controllerClassName nem egy vezérlő!");

        // ha minden sikeres, akkor a munkamenet indítása
        session_start();

        // azonosítás létrehozása ha kell
        if(empty($_SESSION['authentication']))
            $_SESSION['authentication'] = new \Util\Authentication();
            
        // vezérlők szűrése bejelentkezés alapján
        if(!$_SESSION['authentication']->isAuthenticated() && !in_array($_GET['_c'], array('Index', 'Search', 'User', 'Player'))) {
            $this->setMessage( new \Util\Message(
                \Util\Message::ERROR,
                'Nincs jogosultsága megtekinteni a keresett lapot!',
                'A keresett lap megtekintéséhez be kell jelentkeznie!'
            ));
            return '';
        }

        // kérés kezelése, nézet meghatározása
        try {
            return $controllerClassName::instance()->processQuery();
        } catch(\PDOException $e) {
            $this->setMessage( new \Util\Message(
                \Util\Message::ERROR,
                'A kérés feldolgozása sikertelen!',
                'Adatbázis hiba történt! Próbálkozzon késsőb!'
            ));            
            return '';
        }
    
    }
    
    private $message;
    private $activeMenu = 1;

    function setMessage(\Util\Message $message) {
        $this->message = $message;
    }

    function getMessage() {
        return $this->message;
    }

    function setActiveMenu($menu) {
        $this->activeMenu = $menu;
    }

    function getActiveMenu() {
        return $this->activeMenu;
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