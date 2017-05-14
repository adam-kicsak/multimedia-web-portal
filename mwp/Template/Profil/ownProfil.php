<?php
    include 'Template/header.php';
    
    $controller = \Controller\Profil::instance();
?>
<section class="profil">
    <h1>Profilom</h1>
    <p><span>Választott felhasználónév:</span> <?=$controller->user->id ?></p>
    <p><span>Választott e-mail cím:</span> <?=$controller->user->email ?></p>
<?php
    if(\Controller\FrontController::instance()->getMessage() instanceof \Util\Message)
        \Controller\FrontController::instance()->getMessage()->render();
?>
    <form class="genericForm genericFormFields" action="" method="post">
       <fieldset>
            <legend>Jelszó változtatása</legend>

            <label>Új jelszó: <input type="password" name="secret-1" required="required" pattern=".{6,}"></label>
            <p>A jelszó a későbbi azonosításhoz szükséges, ügyeljen arra, hogy elég bonyolult legyen, és legalább 6 karakter hosszú</p>

            <label>Új jelszó megerősítése: <input type="password" name="secret-2" required="required" pattern=".{6,}"></label>
            <p>Biztonsági okokból írja be ide mégegyszer a választott jelszavát</p>

            <input type="submit" name="ChangePassword" value="Jelszó megváltoztatása">
        </fieldset>
    </form>
</section>
<?php
    if(count($controller->notes)) {
?>
<section class="userNoteCardList">
    <h1>Figyelmeztetéseim</h1>
<?php
    foreach($controller->notes as $userNote) {
        include 'Template/userNoteCard.php';
    }
?>
</section>
<?php
    }
    
    include 'Template/footer.php';
?>