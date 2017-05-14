<?php
    include './Template/header.php';
    
    $controller = \Controller\Player::instance();
?>
<section class="player videoPlayer">
    <h1><?php print $controller->media->title; ?></h1>
    <p><span>Beküldte:</span>&nbsp;<a href="Profil.<?php print $controller->media->user_id; ?>"><?php print $controller->media->user_id; ?></a></p>
    <video controls="controls" style="float:left;"<?=$controller->album ? " autoplay=\"autoplay\" onended=\"location.href='Player.Album.{$_GET['album']}." . ($_GET['p'] + 1)."'\"" : '' ?>>
        <?php
            foreach($controller->sources as $source) {
                ?><source src="Resource/Media/<?php print $source[0]; ?>" type="<?php print $source[1]; ?>"></source>
                <?php
            }
        ?>
    </video>
    <?php
        if(!empty($controller->albumContent))
            include __DIR__ . '/albumContent.php';
    ?>
    <div class="clear"></div>
    <p><?=empty($controller->media->description) ? 'Nincs leírás' : '<span>Leírás:</span> ' . $controller->media->description ?></p>
</section>
<?php
    include __DIR__ . '/comments.php';

    include './Template/footer.php';
?>
