<?php

namespace Controller;

class Album implements \MVC\iController {

    const PAGINATOR = 16;
    
    public $paginator;

    // albumok listázása felhasználó szerint
    public function listByUser() {
    
        $this->user = $user = empty($_GET['user']) ? $_SESSION['authentication']->getId() : $_GET['user'];
        
        $this->own = $own = empty($_GET['user']) || $_SESSION['authentication']->getId() == $_GET['user'];
        
        if(!$own)
            unset($_SESSION['albumEdit']);

        if(isset($_SESSION['albumEdit'])){
            header("Location: AlbumContent.{$_SESSION['albumEdit']}");
        }
        
        $page = isset($_GET['page']) && $_GET['page'] >= 0 ? $_GET['page'] : 0;

        $listStmt = \MVC\DB::instance()->pAlbumSelectByUser($user, self::PAGINATOR, $page);
        $this->listResult = $listStmt->fetchAll(\PDO::FETCH_OBJ | \PDO::FETCH_LAZY);
        $listStmt->closeCursor();
        
        $foundStmt = \MVC\DB::instance()->ffound_rows();
        list($recordCount) = $foundStmt->fetch();
        $foundStmt->closeCursor();

        if(count($this->listResult))
            $this->paginator = new \Util\Paginator($recordCount, self::PAGINATOR, $page, "Album.ByUser.$user");
        else
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'Nincs találat',
                $own ? 'Ön még nem hozott létre albumokat.' : "$user még nem hozott létre albumokat"
            ));
    }
    
    // új album létrehozása
    public function handleCreate() {
       
       if(empty($_POST['title']) || empty($_POST['description'])) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'Album létrehozás sikertelen!',
                'Az oldal nem megfelelő paramétert kapott. Próbálkozzon újra!'
            ));  
            return;
        }

        try {
            \MVC\DB::instance()->getDbh()->beginTransaction();

            // album regisztrálása
            $acStmt = \MVC\DB::instance()->fAlbumCreate($_SESSION['authentication']->getId(), $_POST['title'], $_POST['description']);
            list($albumId) = $acStmt->fetch(\PDO::FETCH_NUM);
            $acStmt->closeCursor();

            $albumId = dechex($albumId);
            while (strlen($albumId) < 8)
                $albumId = '0' . $albumId;

            \MVC\DB::instance()->getDbh()->commit();
            
            header('Location: AlbumContent.' . $albumId);
            exit;
        } catch (\Exception $e) {
            // bármilyen hiba esetén változások visszavonása
            \MVC\DB::instance()->getDbh()->rollBack();
             \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'Album létrehozása sikertelen',
                'Adatbázis hiba történt! Próbálja meg később!'
            ));
       }
    }
    
    // album törlése
    public function handleDelete() {
         if(empty($_GET['album']) || !preg_match('/[0-9a-fA-F]{8,8}/', $_GET['album'])) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'Album törlése sikertelen!',
                'Az oldal nem megfelelő paramétert kapott. Próbálkozzon később!'
            ));  
            return;
        }

        try {
            $selectStmt = \MVC\DB::instance()->pAlbumSelectById(hexdec($_GET['album']));
            $album = $selectStmt->fetch(\PDO::FETCH_OBJ | \PDO::FETCH_LAZY);
            $selectStmt->closeCursor();

            if($_SESSION['authentication']->getId() != $album->user_id) {
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'Nincs jogosultsága a műveletet elvégeznie!',
                    'A kiválasztott album nem az öné!'
                ));
                return '';
            }

            \MVC\DB::instance()->getDbh()->beginTransaction();

            // album regisztrálása
            $adStmt = \MVC\DB::instance()->pAlbumDelete(hexdec($_GET['album']));
            $adStmt->closeCursor();

            \MVC\DB::instance()->getDbh()->commit();
            
            header('Location: Album.ByUser');
            exit;
        } catch (\Exception $e) {
            // bármilyen hiba esetén változások visszavonása
            \MVC\DB::instance()->getDbh()->rollBack();
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'Album törlése sikertelen',
                'Adatbázis hiba történt! Próbálja meg később!'
            ));
       }
    }

    // kérés feldolgozása
    public function processQuery() {

        // paraméterezett kérések feldolgozása
        if (empty($_GET['_m']))
            return '';
            
        if(isset($_POST['Create']))
            $this->handleCreate();

        \Controller\FrontController::instance()->setActiveMenu(6);

        switch ($_GET['_m']) {
            case 'ByUser':
                $this->listByUser();
                return 'Album/listByUser.php';
            case 'Create':
                return 'Album/create.php';
            case 'Delete':
                $this->handleDelete();
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
