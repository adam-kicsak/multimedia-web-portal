<?php
    include 'Template/header.php';
    
    $controller = \Controller\Favorite::instance();
?>
<section class="mediaCardList">
<?php
    if($controller->user == '') {
        $h1 = 'Kedvencek';
    } elseif ($controller->user == $_SESSION['authentication']->getId()) {
        $h1 = 'Kedvenceim';
    } else {
        $h1 = $controller->user . ' kedvencei';
    }
    print("<h1>$h1</h1>");

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