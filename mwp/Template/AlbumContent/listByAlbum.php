<?php
    include 'Template/header.php';
    
    $controller = \Controller\AlbumContent::instance();
?>
<section class="mediaCardList">
<?php
    if(empty($controller->album->title)) {
        $h1 = 'Album tartalma';
    } else {
        $h1 = $controller->album->title . ' album tartalma';
    }
    print("<h1>$h1</h1>");
    
    echo '<p><a href="AlbumContent.EndEdit">Szerkesztés vége</a></p>';

    if(isset($controller->mediaRemover))
        $mediaRemover = $controller->mediaRemover;
    
    foreach($controller->listResult as $media) {
        $media->id = dechex($media->id);
        while(strlen($media->id) < 8)
                $media->id = '0' . $media->id;
        include 'Template/mediaCard.php';
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