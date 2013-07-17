<?php

declare(ticks = 1);
pcntl_signal_dispatch();

require_once 'Jm/Autoloader.php';
Jm_Autoloader::singleton()->prependPath(dirname(__FILE__) . '/lib/php');

$options = IN_ISDIR  
 | IN_CLOSE_WRITE 
 | IN_DELETE 
 | IN_CREATE 
 | IN_MOVED_FROM 
 | IN_MOVED_TO 
 | IN_MOVE_SELF 
 | IN_ATTRIB
 | Jm_Os_Inotify::IN_X_RECURSIVE
;

function terminate($sig) {
    echo 'good bye!' . PHP_EOL;
    exit(1);
}

pcntl_signal(SIGTERM, 'terminate');

$instance = Jm_Os_Inotify::init();
$instance->watch(__DIR__, $options);

touch(__FILE__);

printf("waiting for events\n");
$events = $instance->wait(15);
printf("got %s events\n", count($events));

printf("now monitoring directory ... \n");
$instance->monitor(array(
    IN_CLOSE_WRITE => function($event, $instance) {
        printf("file %s was updated and closed\n", $event->fullpath());
    },
    IN_DELETE => function($event, $instance) {
        printf("file %s was deleted\n", $event->fullpath());
    }
));


/**
foreach($events as $event) {
    printf("%s \n-> %s watch(%s) cookie(%s)\n",
        $event->fullpath(),
        $event->mask(),
        $event->wd(),
        $event->cookie()
    );
}
*/

