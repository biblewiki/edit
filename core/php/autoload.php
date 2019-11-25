<?php

/**
 * Lädt die benötigten Dateien selbst anhand der Klassenamen
 * Dateinamen müssen mit Klassennamen übereinstimmen
 */
spl_autoload_register(function ($className) {
    $path = sprintf('%1$s%2$s%3$s.php',
        // %1$s: get absolute path
        realpath(dirname(__FILE__)),
        // %2$s: / or \ (depending on OS)
        DIRECTORY_SEPARATOR,
        // %3$s: don't wory about caps or not when creating the files
//        strtolower(
            // replace _ by / or \ (depending on OS)
            str_replace('_', DIRECTORY_SEPARATOR, $className)
//        )
    );

    // Wenn die Datei existiert  einbinden
    if (file_exists($path)) {
        require $path;
        
    // Wenn die Datei nicht existiert, Fehler ausgeben
    } else {var_dump($path);
        throw new Exception(
            sprintf('Class with name %1$s not found. Looked in %2$s.',
                $className,
                $path
            )
        );
    }
});