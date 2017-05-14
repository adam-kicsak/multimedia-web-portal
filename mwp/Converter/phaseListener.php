<?php

    // Egy könyvtárral feljebb lesz a munkakönyvtár
    chdir('..');

    // konfiguráció betöltése
    include('portal.config.php');
    include('portal.autoload.php');

    if ($argc == 3) {

        $user_id = $argv[1];
        $phase = (int) $argv[2];

        if (empty($user_id) || empty($phase)) {
            exit;
        }

        if (!preg_match('/[a-zA-Z0-9]{6,30}/', $user_id) || $phase < 1 || $phase > 255) {
            exit;
        }

        \MVC\DB::instance()->getDbh()->beginTransaction();
        try {
            \MVC\DB::instance()->pConvertStateModify($user_id, $phase);
            \MVC\DB::instance()->getDbh()->commit();
        } catch (PDOException $e) {
            \MVC\DB::instance()->getDbh()->rollBack();
        }
    }
?> 