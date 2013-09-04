<?php
/**
 * Trivial tail -f implementation example
 *
 * usage: php example.tail.php /var/log/logfile
 */
require_once 'Jm/Autoloader.php';

if(!isset($argv[1])) {
    die('usage: php example.tail.php /path/to/logfile');
}

$filename = $argv[1];

// open the file and seek to the end
$fd = fopen($filename, 'r');
fseek($fd, 0, SEEK_END);

// init inotify and add a watch
$in = Jm_Os_Inotify::init();
$in->watch($argv[1], IN_CLOSE_WRITE);

// monitor the file and print new lines
$in->monitor(array(
    IN_CLOSE_WRITE => function($e) use ($fd, $filename) {
        clearstatcache();
        if(ftell($fd) > filesize($filename)) {
            echo "!File was truncated\n";
            fseek($fd, 0, SEEK_END);
        } else {
            echo fgets($fd);
        }
    }
));

