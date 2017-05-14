<?php

namespace Controller;

class Player implements \MVC\iController {

    public $album = false;

    // album lejátszása
    private function processAlbumQuery() {
        $this->album = true;

        $albumContentStmt = \MVC\DB::instance()->pMediaAndAlbumSelectByAlbumAndOrder(hexdec($_GET['album']), $_GET['p']);
        $this->albumContent =
                $albumContentStmt->fetchAll(\PDO::FETCH_OBJ | \PDO::FETCH_LAZY);
        $albumContentStmt->closeCursor();
        
        if(!count($this->albumContent)) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'A kért album üres'
            ));
            return '';
        }
        
        
        foreach($this->albumContent as $media) {
            $media->album_id = dechex($media->album_id);
                while (strlen($media->album_id) < 8)
            $media->album_id = '0' . $media->album_id;
            
            if($media->order == $_GET['p'])
                $selected = $media;
        }
        
        if(empty($selected)) {
            header('Location: Album.ByUser');
            exit;
        }
        
        return $this->processMediaQuery($selected);
    }

    // egy multimédiás anyag lejátszása
    private function processMediaQuery($media) {

        if(!$media) {
            \Controller\FrontController::instance()->setMessage(new \Util\Message(
                \Util\Message::INFO,
                'A keresett lap nem található!'
            ));
            return '';
        }
        
        $this->media = $media;
        
        $mediaId = dechex($media->id);
        while (strlen($mediaId) < 8)
            $mediaId = '0' . $mediaId;

        
        $path = $mediaId . '/' . $media->type;
        switch ($media->type) {
            case 'audio':
                $this->sources = array(
                    array($path . '.ogg', 'audio/ogg'),
                    array($path . '.mp3', 'audio/mpeg')
                );
                break;
            case 'video':
                $this->sources = array(
                    array($path . '.webm', 'video/webm'),
                    array($path . '.mp4', 'video/mp4')
                );
                break;
            case 'image':
                $this->sources = array($path . '.jpeg');
                break;
            default:
                $this->sources = array();
        }

        if ($media->type != 'audio') {
            $this->poster = $media->id . '/poster.jpeg';
        }

        if (empty($_GET['cp']) || ($cp = (int) $_get['cp']) < 0)
            $cp = 0;

        $commentsStmt = \MVC\DB::instance()->pMediaCommentSelectByMedia($media->id, 15, $cp);
        $this->comments = $commentsStmt->fetchAll(\PDO::FETCH_OBJ);
        $commentsStmt->closeCursor();

        return "Player/{$media->type}.php";
    }

    // kérés feldolgozása
    public function processQuery() {

        if (isset($_GET['id']) && preg_match('/[0-9a-fA-F]{8,8}/', $_GET['id'])) {
            $mediaStmt = \MVC\DB::instance()->pMediaSelectById(hexdec($_GET['id']));
            $media = $this->media = $mediaStmt->fetch(\PDO::FETCH_OBJ);
            $mediaStmt->closeCursor();
            
            return $this->processMediaQuery($media);
        }
        elseif(isset($_GET['album']) && preg_match('/[0-9a-fA-F]{8,8}/', $_GET['album']) && isset($_GET['p']) && (int)$_GET['p'] > 0) {
            // albumot is kérjük
            return $this->processAlbumQuery($_GET['album'], $_GET['p']);
        } else {
        }    

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
