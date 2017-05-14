<?php
    include './Template/header.php';
    
    if(\Controller\FrontController::instance()->getMessage() instanceof \Util\Message)
        \Controller\FrontController::instance()->getMessage()->render();
?>
<form class="genericForm genericFormFields" action="" method="post">
    <fieldset>
        <legend>Regisztrációs űrlap</legend>

        <label>Felhasználónév: <input type="text" name="id" required="required" pattern="[a-zA-Z0-9]{6,30}"></label>
        <p>Ez a név fog megjelenni a feltöltött tartalmak mellett, és a profilban. A felhasználónév csak az angol ábécé betűíből és számokból állhatnak, maximum 30 karakter hosszan.</p>

        <label>E-mail cím: <input type="email" name="email" required="required"></label>
        <p>Az e-mail cím nem publikus, a többi felhasználó számára rejtett</p>

        <label>Jelszó: <input type="password" name="secret-1" required="required" pattern=".{6,}"></label>
        <p>A jelszó a későbbi azonosításhoz szükséges, ügyeljen arra, hogy elég bonyolult legyen, és legalább 6 karakter hosszú</p>

        <label>Jelszó megerősítése: <input type="password" name="secret-2" required="required" pattern=".{6,}"></label>
        <p>Biztonsági okokból írja be ide mégegyszer a választott jelszavát</p>

        <input type="submit" name="Register" value="Regisztráció">
    </fieldset>
</form>
<?php

    include './Template/footer.php';
?>