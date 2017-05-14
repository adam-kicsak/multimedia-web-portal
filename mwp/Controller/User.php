<?php

namespace Controller;

class User implements \MVC\iController {


    // azonosítás elvégzése
    private function authenticate() {
        $result = $_SESSION['authentication']->authenticate($_POST['email'], $_POST['secret']);
        if ($result) {
            if (!empty($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }
        }
        return $result;
    }

    // kijelentkezés - azonosítás törtlése
    private function clearAuthentication() {
        $_SESSION['authentication']->clearAuthentication();
        if (!empty($_SERVER['HTTP_REFERER'])) {
            header('Location: ' . $_SERVER['HTTP_REFERER']);
        } else {
            header('Location: Index');
        }
        exit();
    }

    // felhasználó regisztrációja
    public function handleRegister() {
        if ($_POST['secret-1'] != $_POST['secret-2']) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'Sikertelen regisztráció!',
                'A két jelszó nem egyezik. Ügyeljen arra, hogy a regisztrációs űrlapon a jelszó, és jelszó megerősítés mezőkbe ugyan az a szöbveg kerüljön!'
            ));
            return;
        }
        try {
            \MVC\DB::instance()->getDbh()->beginTransaction();
            $createUserStmt = \MVC\DB::instance()->pUserCreate($_POST['id'], $_POST['email'], $_POST['secret-1']);
            $createUserStmt->closeCursor();
            \MVC\DB::instance()->getDbh()->commit();
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'Sikeres regisztráció!',
                'Most már bejelentkezhet a megadott e-mail címmel és jelszóval.'
            ));
        } catch (\PDOException $e) {
            list($sqlState, $sqlCode, $sqlError) = \MVC\DB::instance()->getLastStmt()->errorInfo();
            \MVC\DB::instance()->getDbh()->rollBack();

            if ($sqlCode == 1582 && $sqlState == 23000) {
                // duplikált kulcsok
                if (strpos($sqlError, 'email'))
                    \Controller\FrontController::instance()->setMessage(new \Util\Message(
                        \Util\Message::ERROR,
                        'Sikertelen regisztráció!',
                        'Az e-mail címmel már létezik regisztráció! Ha már regisztrált, akkor lépjen be a megadott e-mail címmel, és jelszóval.'
                    ));
                else
                     \Controller\FrontController::instance()->setMessage(new \Util\Message(
                        \Util\Message::ERROR,
                        'Sikertelen regisztráció!',
                        'A kiválasztott felhasználónév foglalt! Válasszon egy másikat!'
                    ));
            } else
            // egyéb hiba
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'Sikertelen regisztráció!',
                    'Adatbázis hiba történt regisztráció közben. Próbálkozzon később!'
                ));        
        }
    }

    // kérés feldolgozása
    public function processQuery() {

        // űrlapkérések feldolgozása
        if (isset($_POST['Authenticate'])) {
            if ($this->authenticate())
                return 'User/authenticated.php';
            else {
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'Sikertelen azonosítás',
                    'A megadott felhasználónév és/vagy jelszó nem megfelelő.'
                ));
            }   
        } elseif (isset($_POST['Register'])) {
            $this->handleRegister();
        }
        
        // paraméterezett kérések feldolgozása
        if (empty($_GET['_m']))
            return '';

        switch ($_GET['_m']) {
            case 'Register':
               \Controller\FrontController::instance()->setActiveMenu(2);
                return 'User/register.php';
            case 'ClearAuthentication':
                $this->clearAuthentication();
                break;
        }
        return '';
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
