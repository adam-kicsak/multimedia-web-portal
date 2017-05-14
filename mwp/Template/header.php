<!DOCTYPE HTML>
<html>
    <head>
        <title>MWP5<?php
            // modelben lévő cím megjelenítése
            if (isset($model->title))
                print ' - ' . $model->title;
        ?></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <link rel="stylesheet" href="Resource/Theme/main.css">
        <script type="text/javascript" src="Resource/JavaScript/dnd.js"></script>
        <?php
            // modelben lévő extra meta tagok megjelenítése
            if (isset($model->meta) && is_array($model->meta))
                foreach ($model->meta as $name => $content) {
                    echo '<meta name="', $name, '" content="', $content, '">';
                }
                
            include('Template/menuActiveStyle.php');
        ?>
    </head>
    <body onload="setDragEvent();">
        <header>

            <div>
                <h1>Multimédiás<br>portál rendszer</h1>
            </div>

            <div>
                <form class="searchInHeader" action="Search" method="post">
                    <input type="text" name="keyword" placeholder="Keresendő kulcsszavak" required="required" />
                    <input type="submit" name="Search" value="Keress" />
                </form>
            </div>

            <div><?php include('Template/authentication.php'); ?></div>

            <div></div>

            <div>
                <nav><?php
                    include('Template/menu.php');
                ?></nav>
            </div>

        </header>
<?php if($_SESSION['authentication']->isAuthenticated()) { ?>
        <div id="dropArea">
            <p id="dropFavorite">Kedvencekbe!</p>
            <p id="dropAlbum">Kijelölt albumba!</p>
        </div>
<?php } ?>
        <div id="content">