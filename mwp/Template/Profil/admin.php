<?php

    include 'Template/header.php';
    
    $controller = \Controller\Profil::instance();
    
    $roles = array(
        'user' => 'Felhasználó',
        'moderator' => 'Moderátor',
        'administrator' => 'Adminisztrátor'
    );
            
    if(\Controller\FrontController::instance()->getMessage() instanceof \Util\Message)
        \Controller\FrontController::instance()->getMessage()->render();
?>
<section>
    <h1><?=$controller->user->id ?> felhasználó adminisztrálása</h1>
<?php if($controller->user->role == 'user' && $_SESSION['authentication']->getRole() == 'administrator') { ?>
    <form class="genericForm genericFormFields" action="" method="post">
        <fieldset>
            <legend>Kinevezés moderátornak</legend>

            <p>Egy felhasználó moderátornak való kinevezése után a felhasználónak joga van eltávolítani bármilyen multimédiás anyagot, és a hozzájuk tartozó megjegyzést</p>

            <input type="submit" name="UserPromoteToModerator" value="Kinevezés moderátornak">
        </fieldset>
    </form>
<?php } ?>
    <form class="genericForm genericFormFields" action="" method="post">
        <fieldset>
            <legend>Moderátori megjegyzés írása</legend>

            <label>Típus:
                <select name="type">
                    <option value="note">Megjegyzés</option>
                    <option value="warn">Figyelmeztetés</option>
                    <option value="ban">Kitiltás</option>
                </select>
            </label>

            <label>Intézkedés oka: <textarea name="reason" cols="40" rows="10" required="required"></textarea></label>
            
            <label>Lejárati dátum: <input type="date" name="date"></label>    
            
            <p>A dátum és / vagy az idő meg nem adása azzal a következménnye jár, hogy az intézkedés soha nem jár le! a dátum formátuma: ÉÉÉÉ-HH-NN</p>
            <label>Lejárati idő: <input type="time" name="time"></label>
         
            <p>Az idő formátuma: óó:pp</p>
            <input type="submit" name="UserNoteCreate" value="Létrehozás">
         </fieldset>
    </form>
</section>
<?php
    if(count($controller->notes)) {
?><section class="userNoteCardList">
    <h1><?=$controller->user->id ?> felhasználó eddigi moderátori megjegyzései</h1>
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