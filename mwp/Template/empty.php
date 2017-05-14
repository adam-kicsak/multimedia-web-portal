<?php

    include 'Template\header.php';
    
    if(\Controller\FrontController::instance()->getMessage() instanceof \Util\Message)
        \Controller\FrontController::instance()->getMessage()->render();        
    
    include 'Template\footer.php';

?>