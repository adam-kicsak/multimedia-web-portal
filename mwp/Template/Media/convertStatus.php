<?php
    include("Template/header.php");

    if(\Controller\FrontController::instance()->getMessage() instanceof \Util\Message)
        \Controller\FrontController::instance()->getMessage()->render();
        
    $controller = \Controller\Media::instance();
    
    if($controller->convertStatus->type == "video") {
        $p1c = "WEBM videó:";
        $p2c = "MP4 videó:";
    } else {
        $p1c = "OGG hang:";
        $p2c = "MP3 hang:";
    }

    $state = array();
    $phase = $controller->convertStatus->phase;
    $csip = round($controller->convertStatusInPercent);
    for($i=1; $i<=3; $i++)
        $state[$i] = $phase > $i ? 'Kész' : ($phase < $i ? 'Várakozik' : '<progress max="100" value="' . $csip . '">' . $csip . '%</progress>');

?>
<section>
    <h1>&quot;<?=$controller->convertStatus->file ?>&quot; konvertálása</h1>
    <table>
        <caption>Konvertálás Állapota</caption>
        <tbody>
            <tr>
                <td><?=$p1c ?></td>
                <td><?=$state[1] ?></td>
            </tr>
            <tr>
                <td><?=$p2c ?></td>
                <td><?=$state[2] ?></td>
            </tr>
<?php if($controller->convertStatus->type == "video") { ?>
            <tr>
                <td>Előnézeti kép:</td>
                <td><?=$state[3] ?></td>
            </tr>
<?php } ?>
        </tbody>
    </table>
    <p>Most már elnavigálhat, de akár be is zárhatja az oldalt. A konvertálás befejezését követően a fájok átmeneti tárolásra kerülnek. Ha legközelebb az "Új tartalom" menüpontra navigál, elmentheti a feltöltött anyagot, és megadhatja a metainformációkat is.
    <p>Az oldal 3 másodpercenként frissíti önmagát</p>
</section>
<?php include("Template/footer.php"); ?>