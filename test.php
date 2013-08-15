<?php

require_once 'Jm/Autoloader.php';
require_once '../phishd/lib/php/Phish/PhpFileInfo.php';

Jm_Autoloader::singleton()->prependPath('lib/php/');

$log = new Jm_Log();
$log->attach(new Jm_Log_ConsoleObserver());

$in = Jm_Os_Inotify::init($log);
$watch = $in->watch('.', IN_ALL_EVENTS
#    | Jm_Os_Inotify::IN_X_RECURSIVE
#    | Jm_Os_Inotify::IN_X_RECURSIVE_FOLLOW
);

$in->monitor(array(
    IN_CLOSE_WRITE=> function($e) {
        if(preg_match('~\.php$~', $e->fullpath())) {
            echo "Wrote " . $e->fullpath() . PHP_EOL;
            echo new Phish_PhpFileInfo($e->fullpath()) . '';
        }
    },
    IN_DELETE=> function($e) {
        if(preg_match('~\.php$~', $e->fullpath())) {
            echo "Deleted " . $e->fullpath() . PHP_EOL;
        }
    },
));

