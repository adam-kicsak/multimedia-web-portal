<?php

    // ---- autoload configuration

    // ha egy osztály nem található, ilyen kivétel keletkezik
    class MissingClassException extends Exception {
    }

    // osztálybetöltő
    function __autoload($className) {
        // a \ jelek cseréje / jelre, és a kiterjesztés hozzáfűzése az osztály nevéhez
        $classFile = str_replace('\\', '/', $className) . '.php';

        // a fájl betöltése, ha sikertelen, akkor kivételt dob
        if(!include $classFile)
            throw new \MissingClassException("A '$classFile' fájl nem tölthető be"); 
    }

?>