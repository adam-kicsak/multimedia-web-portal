<?php

namespace Controller;

class Favorite implements \MVC\iController {

    const PAGINATOR = 16;
    
    public $paginator;
    public $mediaRemover;

    // kedvencek listázása felhasználó szerint
    public function listByUser() {

        $user = empty($_GET['user']) ? $_SESSION['authentication']->getId() : $_GET['user'];
        
        $own = empty($_GET['user']) || $_SESSION['authentication']->getId() == $_GET['user'];

        $page = isset($_GET['page']) && $_GET['page'] >= 0 ? $_GET['page'] : 0;

        $listStmt = \MVC\DB::instance()->pFavoriteSelectByUser($user, self::PAGINATOR, $page);
        $this->listResult =
                $listStmt->fetchAll(\PDO::FETCH_OBJ | \PDO::FETCH_LAZY);
        $listStmt->closeCursor();
        $this->user = $user;

        $foundStmt = \MVC\DB::instance()->ffound_rows();
            list($recordCount) = $foundStmt->fetch();
        $foundStmt->closeCursor();
        
        if($own)
            $this->mediaRemover = 'Favorite.Remove.';
        
        if(count($this->listResult))
            $this->paginator = new \Util\Paginator($recordCount, self::PAGINATOR, $page, "Favorite.ByUser.$user");
        else
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'Nincs találat',
                $own ? 'Önnek még nincsenek kedvencei.' : "$user még nem vett fel kedvenceket"
            ));    
    }
    
    // multimédiás anyag felvétele kedvencek közé
    public function handleAdd() {
        if(empty($_GET['media']) || !preg_match('/[0-9a-fA-F]{8,8}/', $_GET['media'])) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'A kedvencek közé való felvétel sikertelen!',
                'Az oldal nem megfelelő paramétert kapott. Próbálja meg újra!'
            )); 
            return;
        }
    
        try {
            \MVC\DB::instance()->getDbh()->beginTransaction();
            $createUserStmt = \MVC\DB::instance()->pFavoriteAdd($_SESSION['authentication']->getId(), hexdec($_GET['media']));
            $createUserStmt->closeCursor();
            \MVC\DB::instance()->getDbh()->commit();
            if (!empty($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'Sikeresen felvéve a kedvencek közé!'
            ));        
        } catch(\PDOException $e) {
            list($sqlState, $sqlCode, $sqlError) = \MVC\DB::instance()->getLastStmt()->errorInfo();
            \MVC\DB::instance()->getDbh()->rollBack();

            if ($sqlCode == 1582 && $sqlState == 23000) {
                // duplikált kulcsok
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'A kedvencek közé való felvétel sikertelen!',
                    'A kiválasztott multimédiás anyag már a kedvencek között van!'
                ));
            } else
                // egyéb hiba
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'A kedvencek közé való felvétel sikertelen!',
                    'Adatbázis hiba történt. Próbálkozzon később!'
                ));   
        }
    }

    // multimédiás anyag eltávolítása kedvencek közül
    public function handleRemove() {
        if(empty($_GET['media']) || !preg_match('/[0-9a-fA-F]{8,8}/', $_GET['media'])) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'A kijelölt multimédiás anyag eltávolítása a kedvencek közül sikertelen!',
                'Az oldal nem megfelelő paramétert kapott. Próbálja meg újra!'
            ));  
            return;
        }
    
        try {
            \MVC\DB::instance()->getDbh()->beginTransaction();
            $createUserStmt = \MVC\DB::instance()->pFavoriteRemove($_SESSION['authentication']->getId(), hexdec($_GET['media']));
            $createUserStmt->closeCursor();
            \MVC\DB::instance()->getDbh()->commit();
            if (!empty($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'A kijelölt multimédiás anyag sikeresen eltávolítva a kedvencek közül!'
            ));        
        } catch(\PDOException $e) {
            list($sqlState, $sqlCode, $sqlError) = \MVC\DB::instance()->getLastStmt()->errorInfo();
            \MVC\DB::instance()->getDbh()->rollBack();
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'A kijelölt multimédiás anyag eltávolítása a kedvencek közül sikertelen!',
                'Adatbázis hiba történt. Próbálkozzon később!'
            ));   
        }
    }
    
    // kérés feldolgozása
    public function processQuery() {

        \Controller\FrontController::instance()->setActiveMenu(5);

        // paraméterezett kérések feldolgozása
        if (empty($_GET['_m']))
            return '';

        switch ($_GET['_m']) {
            case 'ByUser':
                $this->listByUser();
                return 'Favorite/listByUser.php';
            case 'Add':
                $this->handleAdd();
                return '';            
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
