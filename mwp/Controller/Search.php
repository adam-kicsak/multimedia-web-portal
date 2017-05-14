<?php

namespace Controller;


class Search implements \MVC\iController {

    const PAGINATOR = 16;

    public $paginator;
    
    public $album = false;
    
    private $searchParam;

    // keresési paraméterek mentése
    private function saveSearchParam() {
       $_SESSION['searchParam'] = $this->searchParam = array(
            'keyword' => $_POST['keyword'],
            'object' => $_POST['object'],
            'useTime' => isset($_POST['useTime']) ? $_POST['useTime'] : null,
            'timeUsing' => $_POST['timeUsing'],
            'time' => $_POST['time']
        );
    }

    // keresési paraméterek betöltése
    private function loadSearchParam() {
        if(isset($_SESSION['searchParam']))
            $this->searchParam = $_SESSION['searchParam'];
    }

    // keresési paraméterek törlése
    private function clearSearchParam() {
        unset($_SESSION['searchParam'], $this->searchParam);
    }

    // kérés feldolgozása
    public function processQuery() {

        \Controller\FrontController::instance()->setActiveMenu(1);

        if(isset($_POST['Search'])) {
            // keresés elvégzése, paraméterek mentése, és átírányítás
            $this->searchMode = true;
            $this->saveSearchParam();
            header('Location: Search.0');
            exit();
        } elseif(isset($_GET['page'])) {
            // lapozás használata, keresési változók a régiek maradnak
            $this->searchMode = true;
            $page = isset($_GET['page']) && $_GET['page'] >= 0 ? $_GET['page'] : 0;
        } else {
            // keresőform megjelenítése és eddigi paraméterek törlése
            $this->searchMode = false;
            $this->clearSearchParam();
            return 'Search/searchForm.php';
        }

        try {
            // idő használat kiegészítése
            $timeUsing = empty($this->searchParam['useTime']) ? 'unused' : $this->searchParam['timeUsing'];

            // az irásjelek eltávolítása a kulcsszó mezőből
            $strip= array(
                '!','"', '#', '$', '%', '&', "'", ')', '(', '*', '+', ',', '-',
                '.', '/', ':', ';', '<', '=', '>', '?', '@', '[', '\\', ']', '^',
                '`', '{', '|', '}', '~'
            );
            $kw = str_replace($strip, '', $this->searchParam['keyword']);
            $kwa = explode(' ', $kw);
            foreach($kwa as $k => $v) {
                if(empty($v))
                    unset($kwa[$k]);
            }

            // regex kifejezés készítése
            $keyword = '(' . implode('|', $kwa) . ')';

            if(empty($this->searchParam['object']) || $this->searchParam['object'] != 'album') {
                // keresés a multimédiás anyagok között
                $searchStmt = \MVC\DB::instance()->pMediaSearch(
                        $keyword,
                        $this->searchParam['object'],
                        $timeUsing,
                        $this->searchParam['time'],
                        self::PAGINATOR,
                        $page
                );
            } else {
                $this->album = true;
                // keresés az albumok között
                $searchStmt = \MVC\DB::instance()->pAlbumSearch(
                        $keyword,
                        $timeUsing,
                        $this->searchParam['time'],
                        self::PAGINATOR,
                        $page
                );
            }

            $this->searchResult = $searchStmt->fetchAll(\PDO::FETCH_OBJ | \PDO::FETCH_LAZY);
            $searchStmt->closeCursor();

            $this->searchParam = $this->searchParam;

            $foundStmt = \MVC\DB::instance()->ffound_rows();
                list($recordCount) = $foundStmt->fetch();
            $foundStmt->closeCursor();

            if(count($this->searchResult))
                $this->paginator = new \Util\Paginator($recordCount, self::PAGINATOR, $page, "Search");
            else
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::INFO,
                    'Nincs találat',
                    'A megadott paraméterekkel a keresésnek nincs eredménye. Próbálja meg más paraméterekkel a keresést!'
                ));


        } catch (\PDOException $e) {
            // hiba esetén hibajelentés
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'Nincs találat',
                    'Adatbázis hiba történt! Próbálkozzon később!' . $e->getMessage()
                ));
            return '';
        }

        // sikeres kereséskor ez eredmény kiírása
        return 'Search/searchResult.php';
    }

    /* singleton minta */

    private static $instance;
    private static $c = __CLASS__;

    private function __construct() {
        $this->loadSearchParam();
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
