<?php
    if($_SESSION['authentication']->isAuthenticated()) {
        print('<div class="authenticationDiv"><p>Bejelentkezve mint: <strong>' . $_SESSION['authentication']->getId(). '</strong><br>Rang: <strong>');
        switch($_SESSION['authentication']->getRole()) {
            case 'user':
                print('Felhasználó');
                break;
            case 'moderator':
                print('Moderátor');
                break;
            case 'administrator':
                print('Adminisztrátor');
                break;
        }

        print('</strong></p><p><a href="User.ClearAuthentication">Kijelentkezés</a></p></div>');
    } else {
?>
<form action="User" method="post" class="authenticationForm">
    <input type="email" name="email" placeholder="E-mail cím" required="required"><br>
    <input type="password" name="secret" placeholder="Jelszó" required="required" pattern=".{6,}">
    <input type="submit" name="Authenticate" value="Belépes">
</form>
<?php
    }

?>
