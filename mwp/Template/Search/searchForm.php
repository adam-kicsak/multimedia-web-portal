<?php
    include 'Template/header.php';
?>
<form class="genericForm searchForm " method="post" action="Search">
    <fieldset>
        <legend>Keresés</legend>

        <div>
            <label>
                Keresett objektumok:&nbsp;
                <select name="object">
                    <option value="image">Képek</option>
                    <option value="audio">Hangok</option>
                    <option value="video">Videók</option>
                    <option value="album">Albumok</option>
                </select>
            </label>
        </div>

        <div>
            <div><input type="checkbox" name="useTime" value="1"/></div>
            <div>
                <label>
                    Időszak:&nbsp;
                    <select name="timeUsing">
                        <option value="younger">Újabb</option>
                        <option value="elder">Régebbi</option>
                    </select>
                </label>
                <label>
                    mint&nbsp;
                    <select name="time">
                        <option value="1d">Egy nap</option>
                        <option value="3d">Három nap</option>
                        <option value="1w">Egy hét</option>
                        <option value="2w">Két hét</option>
                        <option value="1m">Egy hónap</option>
                        <option value="3m">Három hónap</option>
                        <option value="1y">Egy év</option>
                    </select>
                </label>
            </div>
            <div></div>
        </div>
        <div>
            <input type="text" name="keyword" placeholder="Keresendő kulcsszavak" required="required" />
            <input type="submit" name="Search" value="Keress" />
        </div>

    </fieldset>
</form>
<?php
    include 'Template/footer.php';
?>
