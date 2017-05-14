<?php

namespace Controller;

class Media implements \MVC\iController {

    const PAGINATOR = 8;
    
    public $paginator;
    public $mediaRemover;

    private $userId;

    private function ffmpegParseTime($time) {
        $time = explode(':', $time);
        return ($time[0] * 60 + $time[1] ) * 60 + $time[2];
    }
    
    // ffmpeg konvertálás állapotának lekérése
    private function ffmpegConvertStatus($file) {


        if (($content = @file_get_contents($file)) === false)
            return 0;

        $content = explode("\r", $content);
        $lineCount = count($content);

        $timeRegex = '\\d+:\\d\\d:\\d\\d\\.\\d+';
        $durationRegex = '/Duration: (' . $timeRegex . ')/';
        $positionRegex = '/time=(' . $timeRegex . ')/';

        // megkeressük azt a sort, amiben a videó hossza van tárolva, és kiszedjük belőle az időt
        for ($line = 0; $line < $lineCount; $line++) {
        $lineRaw = $content[$line];
            if (preg_match($durationRegex, $lineRaw, $durationMatches)) {
                $duration = $this->ffmpegParseTime($durationMatches[1]);
                break;
            }
        }
        
        // megkeressük a legutolsó státusz sort, és kiszedjük belőle az idót
        for ($line = $lineCount - 1; $line >= 0; $line--) {
            $lineRaw = $content[$line];
            if (preg_match($positionRegex, $lineRaw, $positionMatches)) {
                $position = $this->ffmpegParseTime($positionMatches[1]);
                break;
            }
        }

        // kiszámoljuk a folyamat állását
        if (isset($position) && isset($duration))
            return 100 * $position / $duration;
        else
            return 0;
    }

    
    // előnézeti kép készítés képből
    private function createThumb($name, $thumb, $nx, $ny) {

        if(!($source = imagecreatefromjpeg($name)))
            return false;

        $ox = imagesx($source);
        $oy = imagesy($source);
        if(!$ox || !$oy)
            return false;
        $oaspect = $ox / $oy;
        $naspect = $nx / $ny;

        $aspect = $oaspect - $naspect;
        if($aspect == 0) {
            $x = $nx; $y = $ny;
        } elseif($aspect > 0) {
            $x = $nx; $y = $nx / $oaspect;
        } else {
            $x = $ny * $oaspect; $y = $ny;
        }

        $dest = @imagecreatetruecolor($nx, $ny);
        if(!$dest)
            return false;
        
        if(!imagecopyresampled($dest, $source, ($nx - $x)/2, ($ny - $y)/2, 0, 0, $x, $y, $ox, $oy))
            return false;

        if(!imagejpeg($dest, $thumb))
            return false;

        if(!imagedestroy($dest))
            return false;

        if(!imagedestroy($source))
            return false;
            
        return true;
    }
    
    // kép konvertálás
    private function imageConvert($name, $dest) {
    
        $pi = pathinfo($name);
    
        switch($pi['extension'])
        {
            case 'jpg':
            case 'jpeg':
                return @copy($name, $dest);
            case 'png':
                $source = @imagecreatefrompng($name);
                break;
            case 'gif':
                $source = @imagecreatefromgif($name);            
            default:
                return false;
        }    
        if(!$source)
            return false;
        
        return @imagejpeg($source, $dest);
    }

    // feltöltés feldolgozása
    private function handleNewContent() {

        if (empty($_FILES['mediaFile']) || $_FILES['mediaFile']['error']) {
            $this->uploadResult = "Fájl feltöltési hiba, vagy nem lett fájlt kiválasztva!";
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'Fájl feltöltési hiba, vagy nem lett fájl kiválasztva!',
                'Ellenőrizze, hogy a fájl nem nagyobb-e 50MB-nál, majd próbálkozzon újra!'
            )); 
            return;
        }

        try {
            \MVC\DB::instance()->getDbh()->beginTransaction();

            // ha egy konvertálás folyamatban van, akkor nem végzünk újabb feltöltést, hiba kiírása
            $csStmt = \MVC\DB::instance()->pConvertStateSelectByUser($this->userId);
            if ($csStmt->rowCount()) {
                $csStmt->closeCursor();
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'Már egy konvertálás folyamatban van!',
                    'Várja meg a konvertálás végét, majd próbálkozzon újra!'
                )); 
                return;
            }
            $csStmt->closeCursor();

            // a feltöltött fájl információinak lekérése
            $pi = pathinfo($_FILES['mediaFile']['name']);

            // fájltípus megállapítása mime vagy kiterjesztés alapján
            switch ($_FILES['mediaFile']['type']) {
                case 'image/jpeg':
                case 'image/gif':
                case 'image/png':
                    $type = 'image';
                    break;
                case 'audio/mpeg':
                case 'audio/vorbis':
                    $type = 'audio';
                    break;
                case 'video/mpeg':
                case 'video/mp4':
                case 'video/quicktime':
                case 'video/x-msvideo':
                case 'video/webm':
                case 'video/x-flv':
                    $type = 'video';
                    break;
                default:
                    if(in_array($pi['extension'], array('jpg', 'jpeg', 'gif', 'png'))) {
                        $type = 'image';
                    } elseif (in_array($pi['extension'], array('mp3', 'ogg'))) {
                        $type = 'audio';
                    } elseif (in_array($pi['extension'], array('flv', 'webm', 'avi', 'qt', 'mov', 'mpg', 'mpeg', 'mp4'))) {
                        $type = 'video';
                    } else {
                        \Controller\FrontController::instance()->setMessage(new \Util\Message(
                            \Util\Message::ERROR,
                            'Fájl feltöltési hiba történt!',
                            'A fájl formátuma nem megfelelő! A portál csak a feltöltő űrlapon felsorolt típusokat támogatja!'
                        ));
                        return;    
                    }
            }

            $csStmt = \MVC\DB::instance()->pConvertStateCreate($this->userId, $_FILES['mediaFile']['name'], $type);
            $csStmt->closeCursor();
   

            // ha a feltöltött fájl elérhető, akkor az ideiglenes helyről kimásoljuk
            if (is_uploaded_file($_FILES['mediaFile']['tmp_name'])) {
                @mkdir("Converter/{$this->userId}");
                $dest = "Converter/{$this->userId}/input.{$pi['extension']}";
                if (!move_uploaded_file($_FILES['mediaFile']['tmp_name'], $dest)) {
                    \Controller\FrontController::instance()->setMessage(new \Util\Message(
                        \Util\Message::ERROR,
                        'Fájl feltöltési hiba történt!',
                        'Próbálkozzon újra!'
                    )); 
                    return;
                }
            } else {
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'Fájl feltöltési hiba történt!',
                    'Próbálkozzon újra!'
                )); 
                return;
            }

            // konvartálás indítása
            switch($type) {
                case 'video':
                    $command = "start /b /dConverter videoConvert.bat {$this->userId} input.{$pi['extension']} 640x360 120x90 00:00:05";
                    pclose(popen($command, 'r'));
                    break;
                case 'audio':
                    $command = "start /b /dConverter audioConvert.bat {$this->userId} input.{$pi['extension']}";
                    pclose(popen($command, 'r'));
                    break;
                case 'image':
                    \MVC\DB::instance()->pConvertStateModify($this->userId, 255);
                    $convert = $this->imageConvert("Converter/{$this->userId}/input.{$pi['extension']}", "Converter/{$this->userId}/image.jpeg");
                    if($convert)
                        $convert = $convert && $this->createThumb("Converter/{$this->userId}/image.jpeg", "Converter/{$this->userId}/poster.jpeg", 120, 90);
                        
                    if(!$convert) {
                        \Controller\FrontController::instance()->setMessage(new \Util\Message(
                            \Util\Message::ERROR,
                            'A konvertálás sikertelen!',
                            'Próbálkozzon később!'
                        ));  
                        return;
                    }
            
            }

            \MVC\DB::instance()->getDbh()->commit();

        } catch (\PDOException $e) {
            // PDO hiba
            \MVC\DB::instance()->getDbh()->rollBack();
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'A konvertálás elkezdése sikertelen!',
                'Adatbázis hiba történt. Próbálkozzon később!'
            ));   
        }
    }

    // új tartalom űrlap, vagy konvertálás állapotának megjelenítése
    private function newContent() {

        try {
            // aktuális konvertálás lekérése
            $csStmt = \MVC\DB::instance()->pConvertStateSelectByUser($this->userId);
            if (!$csStmt->rowCount()) {
                // ha nincs, akkor a feltöltő form kiíratása
                $csStmt->closeCursor();
                return 'Media/uploader.php';
            }

            $cs = $csStmt->fetch(\PDO::FETCH_OBJ | \PDO::FETCH_LAZY);
            $csStmt->closeCursor();

            if ($cs->phase != 255) {
                // ha van konvertálás folyamatban, akkor státusz kiírása
                // és 3 másodpercenkénti frissítés kényszerítése
                $this->convertStatus = $cs;
                $this->convertStatusInPercent =
                        $this->ffmpegConvertStatus("Converter/{$cs->user_id}/err.txt");
                header("Refresh: 3; url=Media.NewContent");
                return 'Media/convertStatus.php';
            } else {
                // ha befejeződött a konvartálás, akkor a véglegesítő form mutatása
                header("Location: Media.NewContent.Commit");
                exit();
            }
        } catch (\PDOException $e) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'A kijelölt multimédiás anyag eltávolítása sikertelen!',
                'Adatbázis hiba történt. Próbálkozzon később!'
            ));   
        }
    }

    // véglegesítés
    public function handleNewContentCommit() {

        try {
            \MVC\DB::instance()->getDbh()->beginTransaction();

            // konvertálás státuszának lekérése
            $csStmt = \MVC\DB::instance()->pConvertStateSelectByUser($this->userId);
            if ($csStmt->rowCount()) {
                $cs = $csStmt->fetch(\PDO::FETCH_OBJ | \PDO::FETCH_LAZY);
            }
            $csStmt->closeCursor();

            // a multimédiás anyag regisztrálása
            $mcStmt = \MVC\DB::instance()->fMediaCreate($cs->type, $_POST['title'], $_POST['description'], $cs->user_id, empty($_POST['searchable']) ? 0 : 1);
            list($mediaId) = $mcStmt->fetch(\PDO::FETCH_NUM);
            $mcStmt->closeCursor();

            // fájlnév >> id
            $mediaId = dechex($mediaId);
            while (strlen($mediaId) < 8)
                $mediaId = '0' . $mediaId;
                
            // fájlnevek összeállítása
            switch($cs->type) {
                case 'image':
                    $files = array("image.jpeg", "poster.jpeg");
                    break;
                case 'audio':
                    $files = array("audio.mp3", "audio.ogg");
                    break;
                case 'video':
                    $files = array('video.mp4', 'video.webm', 'poster.jpeg');
            }
            
            // fájlok áthelyezése
            $rename = @is_dir("Resource/Media/{$mediaId}") || @mkdir("Resource/Media/{$mediaId}");
            foreach($files as $name) {
                if(!$rename)
                    break;
                $rename = $rename && @rename("Converter/{$cs->user_id}/$name", "Resource/Media/$mediaId/$name");
            }

            // másolási hiba esetén kivétel
            if (!$rename) {
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'A multimédiás anyag véglegesítése sikertelen!',
                    'A konvertált fájlok nem elérhetőek. Próbálkozzon újra!'
                )); 
                return;
            }

            // konvertálásállapot törlése
            \MVC\DB::instance()->pConvertStateDelete($cs->user_id);

            // mentés
            \MVC\DB::instance()->getDbh()->commit();

            // átirányítás, jelzés
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'A véglegesítése sikeres!'
            )); 

            $this->medaiId = $mediaId;
            header("Refresh: 5; url=Player.$mediaId");
            return "Media/convertEnd.php";
        } catch (\Exception $e) {
            // bármilyen hiba esetén változások visszavonása
            \MVC\DB::instance()->getDbh()->rollBack();
            foreach($files as $name)
                @rename("Resource/Media/$mediaId/$name", "Converter/{$cs->user_id}/$name");
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'A multimédiás anyag véglegesítése sikertelen!',
                'Adatbázis hiba történt. Próbálkozzon később!'
            )); 
        }
    }

    // véglegesítő űrlap megjelenítése
    public function newContentCommit() {
        try {

            // Folyamatban lévő konvertálás lekérése
            $csStmt = \MVC\DB::instance()->pConvertStateSelectByUser($this->userId);
            if ($csStmt->rowCount()) {
                $cs = $csStmt->fetch(\PDO::FETCH_OBJ | \PDO::FETCH_LAZY);
            }
            $csStmt->closeCursor();

            if (empty($cs) || $cs->phase != 255) {
                // ha nincs konvertálás, vagy még folyamatban van egy, akkor átirányítás az új tartalom menüre
                header('Location: Media.NewContent');
                exit();
            }
            // ellenkező esetben model paraméter beállítása és a véglegesétési űrlap megjelenítése
            $this->convertStatus = $cs;
            return "Media/convertEnd.php";
        } catch (\PDOException $e) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'A multimédiás anyag véglegesítése sikertelen!',
                'Adatbázis hiba történt. Próbálkozzon később!'
            ));
            return;
        }
    }

    // multimédiás anyagok listázása felhasználó szerint
    public function listByUser() {

        $user = empty($_GET['user']) ? $_SESSION['authentication']->getId() : $_GET['user'];

        $own = empty($_GET['user']) || $_SESSION['authentication']->getId() == $_GET['user'];
        
        $page = isset($_GET['page']) && $_GET['page'] >= 0 ? $_GET['page'] : 0;

        $listStmt = \MVC\DB::instance()->pMediaSelectByUser($user, self::PAGINATOR, $page);
        $this->listResult =
                $listStmt->fetchAll(\PDO::FETCH_OBJ | \PDO::FETCH_LAZY);
        $listStmt->closeCursor();
        $this->user = $user;

        $foundStmt = \MVC\DB::instance()->ffound_rows();
            list($recordCount) = $foundStmt->fetch();
        $foundStmt->closeCursor();
        
        if($own || $_SESSION['authentication']->getRole() != 'user')
            $this->mediaRemover = 'Media.Remove.';

        if(count($this->listResult))
            $this->paginator = new \Util\Paginator($recordCount, self::PAGINATOR, $page, "Media.ByUser.$user");
        else
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'Nincs találat',
                $own ? 'Önnek még nincsenek feltöltései.' : "$user még nem töltött fel tartalmat."
            ));
    }
    
    // multimédiás anyag eltávolítása
    public function handleRemove() {
    
        if(empty($_GET['media']) || !preg_match('/[0-9a-fA-F]{8,8}/', $_GET['media'])) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'A kijelölt multimédiás anyag eltávolítása sikertelen!',
                'Az oldal nem megfelelő paramétert kapott. Próbálkozzon újra!'
            ));  
        }
        
        try {
        
            $selectStmt = \MVC\DB::instance()->pMediaSelectById(hexdec($_GET['media']));
            $media = $selectStmt->fetch(\PDO::FETCH_OBJ | \PDO::FETCH_LAZY);
            $selectStmt->closeCursor();
            
            if($_SESSION['authentication']->getId() != $media->user_id && $_SESSION['authentication']->getRole() == 'user') {
                \Controller\FrontController::instance()->setMessage(new \Util\Message(
                    \Util\Message::ERROR,
                    'Nincs jogosultsága a műveletet elvégeznie!',
                    'A kiválasztott multimédiás tartalom nem az öné!'
                ));
                return '';
            }

            \MVC\DB::instance()->getDbh()->beginTransaction();
            $removeStmt = \MVC\DB::instance()->pMediaRemove(hexdec($_GET['media']), $_SESSION['authentication']->getId() == $media->user_id ? 'author' : 'moderator');
            $removeStmt->closeCursor();
            \MVC\DB::instance()->getDbh()->commit();
            if (!empty($_SERVER['HTTP_REFERER'])) {
                header('Location: ' . $_SERVER['HTTP_REFERER']);
                exit();
            }
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'A kijelölt multimédiás anyag sikeresen eltávolítva!'
            ));        
        } catch(\PDOException $e) {
            list($sqlState, $sqlCode, $sqlError) = \MVC\DB::instance()->getLastStmt()->errorInfo();
            \MVC\DB::instance()->getDbh()->rollBack();
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::ERROR,
                'A kijelölt multimédiás anyag eltávolítása sikertelen!',
                'Adatbázis hiba történt. Próbálkozzon később!'
            ));   
        }
    }

    // kérés feldolgozása
    public function processQuery() {

        $this->userId = $_SESSION['authentication']->getId();

        if (isset($_POST['NewContent'])) {
            $this->handleNewContent();
        } elseif (isset($_POST['NewContentCommit'])) {
            return $this->handleNewContentCommit();
        }

        // paraméterezett kérések feldolgozása
        if (empty($_GET['_m']))
            return '';

        switch ($_GET['_m']) {
            case 'NewContent':
                \Controller\FrontController::instance()->setActiveMenu(2);
                return $this->newContent();
            case 'NewContent.Commit':
                \Controller\FrontController::instance()->setActiveMenu(2);
                return $this->newContentCommit();
            case 'ByUser':
                \Controller\FrontController::instance()->setActiveMenu(4);
                $this->listByUser();
                return 'Media/listByUser.php';
            case 'Remove':
                \Controller\FrontController::instance()->setActiveMenu(4);
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
