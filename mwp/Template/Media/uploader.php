<?php 
    include("Template/header.php");
 
     if(\Controller\FrontController::instance()->getMessage() instanceof \Util\Message)
        \Controller\FrontController::instance()->getMessage()->render();
 ?>
<form enctype="multipart/form-data" action="Media.NewContent" method="post" class="genericForm">
    <fieldset>
        <legend>Fájl feltöltése</legend>

        <label>Jelölje ki a fájlt a számítógépén: <input type="file" name="mediaFile" required="required" accept="image/jpeg, image/gif, image/png, audio/mpeg, audio/vorbis, video/mpeg, video/mp4, video/quicktime, video/x-msvideo, video/webm, video/x-flv"></label>
        <p>A portál által az alábbi felsorolt típusú kép-, hang- és videóformátum támogatott:  </p>
        <ul>
            <li>jpg, jpeg, gif, png</li>
            <li>mp3, ogg</li>
            <li>mpg, mpeg, mp4, avi, qt, mov, flv, webm</li>
        </ul>
        <p>A fájl maximum 50Mbyte nagy lehet! A feltöltés alatt ne navigáljon el, és ne lépjen ki a böngészőből, mert akkor a feltöltés megszakad!</p>

        <input type="submit" name="NewContent" value="Feltölt!">
    </fieldset>
    <input type="hidden" name="MAX_FILE_SIZE" value="50M">
</form>
<?php
    include("Template/footer.php"); 
?>
