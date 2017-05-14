<?php
    reset($controller->albumContent);
    $first = current($controller->albumContent);
?>
<nav>
    <ol start="<?=$first->order ?>">
    <?php
        foreach($controller->albumContent as $media) {
            echo '<li><a href="Player.Album.', $media->album_id, '.', $media->order, '">' . $media->title . '</a></li>';
        }
    ?>
    </ol>
</nav>