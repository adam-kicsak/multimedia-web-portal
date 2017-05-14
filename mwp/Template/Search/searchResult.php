<?php
    include 'Template/header.php';
    
    $controller = \Controller\Search::instance();
?>
<section class="<?=$controller->album ? 'mediaCardList albumCardList' : 'mediaCardList' ?>">
    <h1>Keresés eredménye</h1>
<?php
    if($controller->album)    {
        foreach($controller->searchResult as $album) {
            $album->id = dechex($album->id);
            while(strlen($album->id) < 8)
                    $album->id = '0' . $album->id;
            include 'Template/albumCard.php';
        }    
    } else {
        foreach($controller->searchResult as $media) {
            $media->id = dechex($media->id);
            while(strlen($media->id) < 8)
                    $media->id = '0' . $media->id;
            include 'Template/mediaCard.php';
        }
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