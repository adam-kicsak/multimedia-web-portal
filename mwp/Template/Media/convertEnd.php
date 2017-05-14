<?php
    include './Template/header.php';

    $controller = \Controller\Media::instance();
    $fc = \Controller\FrontController::instance();
    
    if($fc->getMessage() instanceof \Util\Message)
        $fc->getMessage()->render();
        
    if(!($fc->getMessage() instanceof \Util\Message) || $fc->getMessage()->getType() != \Util\Message::INFO)
    {
        
?>
<form class="genericForm genericFormFields" action="" method="post">
    <fieldset>
        <legend>Tartalomregisztrációs űrlap</legend>

        <label>Fájl: <input type="text" readonly="readonly" value="<?=$controller->convertStatus->file ?>"></label>

        <label>Cím: <input type="text" name="title" required="required"></label>
        <p>Ez a cím fog megjelenni a multimédiás anyag fölött a lejátszó oldalon</p>

        <label>Leírás: <textarea name="description" cols="40" rows="10"></textarea></label>
        <p>Ebbe a mezőbe egy bővebb leírást írhat, melyben részletezheti a multimédiás anyag tartalmát</p>

        <label>Kereshetőség: <div><input type="checkbox" name="searchable" checked="checked" value="1"></div></label>
        <p>A kereshetőség meghatározza, hogy keresés útján is, vagy csak személeys profilbóll érhető el a videó</p>

        <input type="submit" name="NewContentCommit" value="Mentés">
    </fieldset>
</form>
<?php

    } else {

?>
<p>
    <a href="User.NewContet">Új tartalom feltöltése</a><br>
    <a href="Player.<?=$controller->medaiId ?>">Ugrás a lejátszóra</a><br>
</p>
<?php
    }

    include './Template/footer.php';
?>