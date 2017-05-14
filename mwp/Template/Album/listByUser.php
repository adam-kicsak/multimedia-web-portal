<?php
    include 'Template/header.php';
    
    $controller = \Controller\Album::instance();
?>
<section class="mediaCardList albumCardList">
<?php
    if($controller->user == '') {
        $h1 = 'Albumok';
    } elseif ($controller->user == $_SESSION['authentication']->getId()) {
        $h1 = 'Saját albumok';
    } else {
        $h1 = $controller->user . ' albumai';
    }
    print("<h1>$h1</h1>");
    
    if($controller->user == $_SESSION['authentication']->getId())
        echo '<p><a href="Album.Create">Új album létrehozása</a></p>';

    $ownAlbum = $controller->own;

    foreach($controller->listResult as $album) {
        $album->id = dechex($album->id);
        while(strlen($album->id) < 8)
                $album->id = '0' . $album->id;
        include 'Template/albumCard.php';
    }
?>
    <div></div>
</section>
<?php

    if($controller->paginator instanceof \Util\Paginator)
        $controller->paginator->printPagelinks();
        
    if(\Controller\FrontController::instance()->getMessage() instanceof \Util\Message)
        \Controller\FrontController::instance()->getMessage()->render();
    
    include 'Template/footer.php';
?>