<?php

namespace MVC;

interface iFrontController extends iController {

    function setMessage(\Util\Message $message);

    function getMessage();

    function setActiveMenu($menu);

    function getActiveMenu();

}

?>