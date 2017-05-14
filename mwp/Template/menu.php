<?php
if ($_SESSION['authentication']->isAuthenticated()) {
?>
<ul>
    <li><a href="Search">Keresés</a></li>
    <li><a href="Media.NewContent">Új tartalom</a></li>
    <li><a href="Profil">Profil</a></li>
    <li><a href="Media.ByUser">Feltöltések</a></li>
    <li><a href="Favorite.ByUser">Kedvencek</a></li>
    <li><a href="Album.ByUser">Albumok</a></li>
</ul> 
<?php
} else {
?>
<ul>
    <li><a href="Search">Keresés</a></li>
    <li><a href="User.Register">Regisztráció</a></li>
</ul>

<?php
}
?>
   