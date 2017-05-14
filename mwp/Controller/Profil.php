<?php

namespace Controller;

class Profil implements \MVC\iController {

    const PAGINATOR = 16;

    // felhasználó szerint profil lekérése
    public function byUser() {

        $user = empty($_GET['user']) ? $_SESSION['authentication']->getId() : $_GET['user'];
        
        $own = empty($_GET['user']) || $_SESSION['authentication']->getId() == $_GET['user'];
        
        $userStmt = \MVC\DB::instance()->pUserSelectById($user);
        $user = $this->user = $userStmt->fetch(\PDO::FETCH_OBJ);
        $userStmt->closeCursor();
        
        if(!$user) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'A keresett felhasználó nem található!'
            ));
            return '';
        }
        
        if(isset($_GET['_m']) && $_GET['_m'] == 'Admin')
            $noteStmt = \MVC\DB::instance()->pUserNoteSelectByUser($user->id);
        else
            $noteStmt = \MVC\DB::instance()->pUserNoteNotExpiredSelectByUser($user->id);
        $this->notes = $noteStmt->fetchAll(\PDO::FETCH_OBJ | \PDO::FETCH_LAZY);
        $noteStmt->closeCursor();
        
        if($own) {    
            return 'Profil/ownProfil.php';
        } else {
            return 'Profil/userProfil.php';
        }
        
    }
    
    // jelszó változtatás
    public function changePassword() {
    
       if ($_POST['secret-1'] != $_POST['secret-2']) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'Sikertelen jelszóváltoztatás!',
                'A két jelszó nem egyezik. Ügyeljen arra, hogy a jelszóváltoztatás űrlapján a jelszó, és jelszó megerősítés mezőkbe ugyan az a szöbveg kerüljön!'
            ));
            return;
        }
        try {
            \MVC\DB::instance()->getDbh()->beginTransaction();
            $changeStmt = \MVC\DB::instance()->pUserPasswordChange($_SESSION['authentication']->getId(), $_POST['secret-1']);
            $changeStmt->closeCursor();
            \MVC\DB::instance()->getDbh()->commit();        
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'Sikeres jelszóváltoztatás!'
            ));        
        } catch(\PDOException $e) {
            \MVC\DB::instance()->getDbh()->rollBack();            
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'Sikertelen jelszóváltoztatás!',
                'Adatbázis hiba történt jelszóváltoztatás közben. Próbálkozzon később!'
            ));     
        }
    
    }
    
    // felhasználóhoz moderátori megjegyzés létrehozáse
    public function handleUserNoteCreate() {
       if (empty($_POST['reason']) || empty($_POST['type']) || !in_array($_POST['type'], array('note', 'warn', 'ban')) || empty($_GET['user'])) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'Sikertelen modárátori intézkedés!',
                'Az oldal nem megfelelő paramétereket kapott! Próbálja meg újra kitölteni az űrlapot!'
            ));
            return;
        }
        
        try {
            \MVC\DB::instance()->getDbh()->beginTransaction();
            $createStmt = \MVC\DB::instance()->fUserNoteCreate(
                $_GET['user'],
                $_SESSION['authentication']->getId(),
                $_POST['type'],
                $_POST['reason'],
                empty($_POST['date']) || empty($_POST['time']) ? null : $_POST['date'] . ' ' .$_POST['time']
            );
            $createStmt->closeCursor();
            \MVC\DB::instance()->getDbh()->commit();        
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'Sikeres moderátori intézkedés!'
            ));        
        } catch(\PDOException $e) {
            list($sqlState, $sqlCode, $sqlError) = \MVC\DB::instance()->getLastStmt()->errorInfo();
            \MVC\DB::instance()->getDbh()->rollBack();        
            if ($sqlCode == 1292 && $sqlState == 22007) 
                // rossz dátum formátum
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'Sikertelen modárátori intézkedés!',
                    'Ügyeljen a lejárati idő, és dátum helyes formátumára!'
                ));
            else
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'Sikertelen modárátori intézkedés!',
                    'Adatbázis hiba történt moderátori intézkedés közben. Próbálkozzon később! '
                ));     
        }
    
    }

    // kinevezés moderátorrá
    public function handleUserPromoteToModerator() {
       if(empty($_GET['user'])) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'Sikertelen modárátori intézkedés!',
                'Az oldal nem megfelelő paramétereket kapott! Próbálja meg újra kitölteni az űrlapot!'
            ));
            return;
        }
        
        try {
            \MVC\DB::instance()->getDbh()->beginTransaction();
            $modifyStmt = \MVC\DB::instance()->pUserPromoteToModerator($_GET['user']);
            $modifyStmt->closeCursor();
            \MVC\DB::instance()->getDbh()->commit();        
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'Sikeres moderátori intézkedés!'
            ));        
        } catch(\PDOException $e) {
            list($sqlState, $sqlCode, $sqlError) = \MVC\DB::instance()->getLastStmt()->errorInfo();
            \MVC\DB::instance()->getDbh()->rollBack();        
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'Sikertelen modárátori intézkedés!',
                'Adatbázis hiba történt moderátori intézkedés közben. Próbálkozzon később! '
            ));     
        }    
    }

    // kérés feldolgozása
    public function processQuery() {

        \Controller\FrontController::instance()->setActiveMenu(3);
        
        if(isset($_POST['ChangePassword']))
            $this->changePassword();

        // paraméterezett kérések feldolgozása
        if(empty($_GET['_m']))
            return $this->byUser();

        if($_GET['_m'] == 'Admin') {
            if($_SESSION['authentication']->getRole() ==  'user' ) {
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'Nincs jogosultsága a lap megtekintéséhez!'
                ));
                return '';
            }
            if(isset($_POST['UserNoteCreate'])) {
                $this->handleUserNoteCreate();
            }
            if(isset($_POST['UserPromoteToModerator'])) {
                $this->handleUserPromoteToModerator();
            }
            $this->byUser();
            return 'Profil/admin.php';
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
