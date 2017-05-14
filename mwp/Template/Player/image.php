<?php
    include './Template/header.php';
    
    $controller = \Controller\Player::instance();
?>
<section class="player imagePlayer">
    <h1><?php print $controller->media->title; ?></h1>

    <p><span>Beküldte:</span>&nbsp;<a href="Profil.<?php print $controller->media->user_id; ?>"><?php print $controller->media->user_id; ?></a></p>
    <img src="Resource/Media/<?php print $controller->sources[0]; ?>" alt="<?=$controller->media->title ?>">
    <?php 
        if($controller->album) {
    ?>
    <script>
        setTimeout(function() {
            location.href = <?php echo "'Player.Album.{$_GET['album']}." . ($_GET['p'] + 1) . "'" ?>;
        }, 10000);
    </script>
    <?php
        }

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
