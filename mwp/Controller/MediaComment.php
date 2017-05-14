<?php

namespace Controller;

class MediaComment implements \MVC\iController {

    // multimédiás anyaghoz megjegyzés beküldése
    public function handleAdd() {
        if(!$_SESSION['authentication']->isAuthenticated() ) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'Nincs jogosultsága a műveletet elvégeznie!',
                'Megjegyzés írásához be kell jelentkeznie!'
            ));
            return '';
        }

        if(empty($_POST['media']) || !preg_match('/\d+/', $_POST['media']) || empty($_POST['comment'])) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'A megjegyzés beküldése sikertelen!',
                'Az oldal nem megfelelő paramétert kapott. Próbálja meg újra!'
            )); 
            return;
        }
    
        try {
            \MVC\DB::instance()->getDbh()->beginTransaction();
            $createStmt = \MVC\DB::instance()->fMediaCommentAdd($_SESSION['authentication']->getId(), $_POST['media'], $_POST['comment']);
            $createStmt->closeCursor();
            \MVC\DB::instance()->getDbh()->commit();
            if (!empty($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'Megjegyzés sbeküldése sikeres!'
            ));        
        } catch(\PDOException $e) {
            list($sqlState, $sqlCode, $sqlError) = \MVC\DB::instance()->getLastStmt()->errorInfo();
            \MVC\DB::instance()->getDbh()->rollBack();
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'A megjegyzés beüldés sikertelen!',
                'Adatbázis hiba történt. Próbálkozzon később!'
            ));   
        }
    }

    // megjegyzés eltvolítása
     public function handleRemove() {
    
        print 'kettő<br>';
    
        if(empty($_GET['comment']) || !preg_match('/\d+/', $_GET['comment'])) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'A kijelölt megjegyzés eltávolítása sikertelen!',
                'Az oldal nem megfelelő paramétert kapott. Próbálkozzon újra!'
            ));  
            return;
        }
        
        try {
        
            $selectStmt = \MVC\DB::instance()->pMediaCommentSelectById($_GET['comment']);
            $comment = $selectStmt->fetch(\PDO::FETCH_OBJ | \PDO::FETCH_LAZY);
            $selectStmt->closeCursor();
            
            if($_SESSION['authentication']->getId() != $comment->user_id && $_SESSION['authentication']->getRole() == 'user') {
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'Nincs jogosultsága a műveletet elvégeznie!',
                    'A kiválasztott megjegyzés nem az öné!'
                ));
                return '';
            }

            \MVC\DB::instance()->getDbh()->beginTransaction();
            
            $removeStmt = \MVC\DB::instance()->pMediaCommentRemove($_GET['comment'], $_SESSION['authentication']->getId() == $comment->user_id ? 'author' : 'moderator');
            $removeStmt->closeCursor();
            \MVC\DB::instance()->getDbh()->commit();
            if (!empty($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'A kijelölt megjegyzés sikeresen eltávolítva!'
            ));        
        } catch(\PDOException $e) {
            list($sqlState, $sqlCode, $sqlError) = \MVC\DB::instance()->getLastStmt()->errorInfo();
            \MVC\DB::instance()->getDbh()->rollBack();
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'A kijelöltmegjegyzés eltávolítása sikertelen!',
                'Adatbázis hiba történt. Próbálkozzon később!'
            ));   
        }
    }

    
    // kérés feldolgozása
    public function processQuery() {

        \Controller\FrontController::instance()->setActiveMenu(0);

        if(isset($_POST['Add'])) {
            $this->handleAdd();
        }
        
        var_dump($_GET);

        // paraméterezett kérések feldolgozása
        if(empty($_GET['_m']))
            return '';


            
        switch ($_GET['_m']) {
            case 'Remove':
                $this->handleRemove();
                return '';
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
