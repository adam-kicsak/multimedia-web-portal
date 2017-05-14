<?php
    if(\Controller\FrontController::instance()->getActiveMenu()) {
?><style type="text/css">
    header nav ul li:nth-child(<?php echo \Controller\FrontController::instance()->getActiveMenu(); ?>) {
    padding-bottom: 2px;
    background: #e0ffe0;
    background: -moz-linear-gradient(top, green, #e0ffe0);
    background: -webkit-gradient(linear, center top, center bottom, color-stop(0, green), color-stop(1, #e0ffe0));
    color: black;
}
</style>

<?php } ?>