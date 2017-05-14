<section class="comments">
    <h1>Megjegyzések</h1>
<?php

    if(!count($controller->comments)) {
        print '<p>Még nincsenek megjegyzések<p>';
    }

    $media = &$controller->media;

    foreach($controller->comments as $c) {
        echo '<div>';
        switch($c->removed) {
            case 'no':
                echo '<p>', nl2br($c->comment), '</p>';
                break;
            case 'author':
                echo '<p class="removed">A megjeygyzés a szerző által eltávolítva</p>';
                break;
            case 'moderator':
                echo '<p class="removed">A megjeygyzés egy moderátor által eltávolítva</p>';
                break;
        }
        
        echo '<p><span>Beküldte:</span> ', '<a href="Profil.', $c->user_id, '">', $c->user_id, '</a>', ', <span>beküldve:</span> ',  $c->created . '</p>';
        if($c->removed == 'no' && $_SESSION['authentication']->isAuthenticated() && ($_SESSION['authentication']->getId() == $c->user_id || $_SESSION['authentication']->getRole() != 'user'))
            echo '<p class="remove"><a href="MediaComment.Remove.', $c->id , '">Eltávolít</a></p>';
        echo '</div>';
    }
?>
<form class="genericForm genericFormFields commentForm" action="MediaComment" method="post">
    <fieldset>
        <legend>Megjegyzés írása</legend>

        <textarea cols="40" rows="5" name="comment" required="true"></textarea>

        <input type="submit" name="Add" value="Beküld">
    </fieldset>
    <input type="hidden" name="media" value="<?=$media->id ?>">
</form>
</section>