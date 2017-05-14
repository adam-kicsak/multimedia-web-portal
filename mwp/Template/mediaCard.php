<div draggable="true" data-id="<?=$media->id ?>">
    <?php
        if(!empty($media->description)) 
            print("<div>{$media->description}</div>");
    ?>
    <a href="Player.<?=$media->id ?>"><img src="<?= $media->type != 'audio' ? "Resource/Media/{$media->id}/poster.jpeg" : 'Resource/Theme/no_thumb.gif' ?>" alt="Nincs kép"></a>
    <p><a href="Player.<?=$media->id ?>"><?=$media->title ?></a></p>
    <p><?php if(isset($mediaRemover)) echo '<a href="', $mediaRemover , $media->id, '">Eltávolít</a>&nbsp;'; ?><img src="Resource/Theme/<?=$media->type ?>_mini.gif" alt=""></p>
</div>
