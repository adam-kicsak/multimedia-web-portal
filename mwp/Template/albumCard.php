<div>
    <?php
        if(!empty($album->description)) 
            print("<div>{$album->description}</div>");
    ?>
    <a href="Player.Album.<?=$album->id ?>.1"></a>
    <p><a href="Player.Album.<?=$album->id ?>.1"><?=$album->title ?></a></p>
    <p><?php if(isset($ownAlbum) && $ownAlbum) { ?><a href="AlbumContent.<?=$album->id ?>">Szerkesztés</a>&nbsp;<a href="Album.Delete.<?=$album->id ?>">Törlés</a>&nbsp;<?php } ?><img src="Resource/Theme/album_mini.gif" alt=""></p>
</div>