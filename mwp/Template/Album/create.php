<?php
    include './Template/header.php';

?>
<form class="genericForm genericFormFields" action="" method="post">
    <fieldset>
        <legend>Album létrehozása</legend>

        <label>Cím: <input type="text" name="title" required="required"></label>
        <p>Ez a cím fog megjelenni az albumok listáján</p>

        <label>Leírás: <textarea name="description" cols="40" rows="10"></textarea></label>
        <p>Ebbe a mezőbe egy bővebb leírást írhat, melyben részletezheti az album tartalmát</p>

        <input type="submit" name="Create" value="Létrehozás">
    </fieldset>
</form>
<?php

    include './Template/footer.php';
?>