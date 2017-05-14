<?php

namespace Controller;

class AlbumContent implements \MVC\iController {

    const PAGINATOR = 16;
    
    public $paginator;
    public $mediaRemover;

    // album tartalmának megjelenítése album szerint
    public function listByAlbum() {

        $page = isset($_GET['page']) && $_GET['page'] >= 0 ? $_GET['page'] : 0;
        
        $_SESSION['albumEdit'] = $_GET['album'];
        $albumId = hexdec($_GET['album']);

        $albumStmt = \MVC\DB::instance()->pAlbumSelectById($albumId);
        $this->album = $albumStmt->fetch(\PDO::FETCH_OBJ | \PDO::FETCH_LAZY);
        $albumStmt->closeCursor();
        
        $listStmt = \MVC\DB::instance()->pMediaSelectByAlbum($albumId, self::PAGINATOR, $page);
        $this->listResult =
                $listStmt->fetchAll(\PDO::FETCH_OBJ | \PDO::FETCH_LAZY);
        $listStmt->closeCursor();

        $foundStmt = \MVC\DB::instance()->ffound_rows();
            list($recordCount) = $foundStmt->fetch();
        $foundStmt->closeCursor();
        
        if(count($this->listResult))
            $this->paginator = new \Util\Paginator($recordCount, self::PAGINATOR, $page, "AlbumContent.$albumId");
        else
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'Nincs találat',
                'Az album még üres'
            ));    
    }
    
    // multimédiás anyag felvétele albumba
    public function handleAdd() {
        if(empty($_GET['media']) || !preg_match('/[0-9a-fA-F]{8,8}/', $_GET['media'])) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'A kijelölt albumba való felvétel sikertelen!',
                'Az oldal nem megfelelő paramétert kapott. Próbálkozzon újra!'
            ));  
            return;
        }
        
        if(empty($_SESSION['albumEdit']) || !preg_match('/[0-9a-fA-F]{8,8}/', $_SESSION['albumEdit'])) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'A kijelölt albumba való felvétel sikertelen!',
                'Nincs kijelölve album szerkesztésre! Válasszon ki egy albumot az albumok mnüponton, és próbálja meg újra!'
            ));  
            return;
        }        
    
        try {
            \MVC\DB::instance()->getDbh()->beginTransaction();
            $acaStmt = \MVC\DB::instance()->pAlbumCotentAdd(hexdec($_SESSION['albumEdit']), hexdec($_GET['media']), 1);
            $acaStmt->closeCursor();
            \MVC\DB::instance()->getDbh()->commit();
            if (!empty($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'Sikeresen felvéve a kijelölt albumba!'
            ));        
        } catch(\PDOException $e) {
            list($sqlState, $sqlCode, $sqlError) = \MVC\DB::instance()->getLastStmt()->errorInfo();
            \MVC\DB::instance()->getDbh()->rollBack();

            if ($sqlCode == 1582 && $sqlState == 23000) {
                // duplikált kulcsok
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'A kijelölt albumba való felvétel sikertelen!',
                    'A kiválasztott multimédiás anyag már az albumban van!'
                ));
            } else
                // egyéb hiba
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'A kijelölt albumba való felvétel sikertelen!',
                    'Adatbázis hiba történt. Próbálkozzon később!' 
                ));   
        }
    }

    // multimédiás anyag eltávolítása albumból
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
            $acrStmt = \MVC\DB::instance()->pAlbumContentRemove(hexdec($_SESSION['albumEdit']), hexdec($_GET['media']));
            $acrStmt->closeCursor();
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

        \Controller\FrontController::instance()->setActiveMenu(6);

        // paraméterezett kérések feldolgozása
        if (empty($_GET['_m'])) {
            $this->listByAlbum();
            return 'AlbumContent/listByAlbum.php';
        }

        switch ($_GET['_m']) {
            case 'Add':
                $this->handleAdd();
                return '';            
            case 'Remove':
                $this->handleRemove();
                return '';
            case 'EndEdit':
                unset($_SESSION['albumEdit']);
                header('Location: Album.ByUser');
                exit;
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
