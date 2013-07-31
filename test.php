<?php

require_once 'Jm/Autoloader.php';
Jm_Autoloader::singleton()->prependPath('lib/php/');

$in = Jm_Os_Inotify::init();
$watch = $in->watch('.', IN_ALL_EVENTS
    | Jm_Os_Inotify::IN_X_RECURSIVE
    | Jm_Os_Inotify::IN_X_RECURSIVE_FOLLOW
);

$in->monitor(array(
    IN_CLOSE_WRITE=> function($e) {
        echo "Wrote " . $e->fullpath() . PHP_EOL;
    },
    IN_DELETE=> function($e) {
        echo "Deleted " . $e->fullpath() . PHP_EOL;
    },
));

