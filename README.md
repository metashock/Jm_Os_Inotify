# Jm_Os_Inotify

Inotify (inode notify) is a Linux kernel subsystem that acts to extend filesystems to notice changes to the filesystem, and report those changes to applications. 

The [pecl inotify extension]() makes those linux API available to the PHP userland. This package adds the following functionality:

- support recursive directory watches
- provide the full path to files (inotify itself only supports basename)



## Installation

To install `Jm_Os_Inotify` you can use the PEAR installer or get a tarball and install the files manually.

___
### Using the PEAR installer

If you haven't discovered my pear channel yet you'll have to do it. Also you should issue a channel update:

    pear channel-discover metashock.de/pear
    pear channel-update metashock

After this you can install Hexdump. The following command will install the lastest stable version:

    pear install -a metashock/Jm_Os_Inotify

If you want to install a specific version or a beta version you'll have to specify this version on the command line. For example:

    pear install -a metashock/Jm_Os_Inotify-0.1.0


## Documentation
___
### Including the API to your project

The package uses the `Jm_Autoloader` as it's autoloader. All you'll have to do to execute the examples from this documentation is to `require` the autoloader:

```<?php
require_once 'Jm/Autoloader.php';
```

If you have installed the package using pear or composer then you won't have to worry about the existance of this class. It had been installed as a dependency of `Jm_Os_Inotify`. If `require_once` fails, something went wrong with the installation.

___
### Overview

This package as the inotify extension itself introduces three important classes:

- `Jm_Os_Inotify_Instance`

An instance represents a set of of watches and a queue storing events that occur on those watches. Typical functionality of an instance is to register or unregister watches and provide the functionality to read events.

 The queue works like pipe, if an event was once read then it will be removed from the pipe. 

- `Jm_Os_Inotify_Watch`

A watch is related to a file or a directory in file system we wish to obtain change notifications about. Also a watch stores information about what type of events should be received and some other options. A watch belongs to one and only one inotify instance.

- `Jm_Os_Inotify_Event`

An inotify event represents a certain action which took place in the file system. It is related to a watch and further to a inotify instance.


## Examples

___
### Basic example

```php

// create a test file
touch('test.file');

// create an intotify instance
$in = Jm_Os_Inotify::init();

// add a watch. we are interested in when the file gets deleted
$in->watch('test.file', IN_DELETE);

// now delete the file to trigger an event
unlink('test.file');

// get that event
$events = $in->events();

// now iterate trough events
foreach($events as $event) {
    printf("the following event occured on %s: %s\n",
        $event->filename(),
        $event->mask()
    );
}
```
___
### Recursive Directory Watching

```php

// create an intotify instance
$in = Jm_Os_Inotify::init();

// add a watch. we are interested in when the file gets deleted
$in->watch('/path/to/your/directory',
    IN_ALL_EVENTS | Jm_Os_Inotify::IN_X_RECURSIVE
);

// create a sub directory
$sub = '/path/to/your/directory/sub';
mkdir($sub);

// create a file in that sub directory
touch($sub);

// get that event
$events = $in->events();

// now iterate trough events
foreach($events as $event) {
    printf("the following event occured on %s: %s\n",
        $event->filename(),
        $event->mask()
    );
}
```
___
### Waiting for events using a timeout

```php

// create an intotify instance
$in = Jm_Os_Inotify::init();

// add a watch. we are interested in when the file gets deleted
$in->watch('/path/to/your/directory',
    IN_ALL_EVENTS | Jm_Os_Inotify::IN_X_RECURSIVE
);

// wait 5 seconds for events
$events = $in->wait(5, 0);

// now iterate trough events
foreach($events as $event) {
    printf("the following event occured on %s: %s\n",
        $event->filename(),
        $event->mask()
    );
}
```
___
### phptail

This is a trivial implementation of the tail -f command.

```php
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
    IN_CLOSE_WRITE => function($e) use ($fd) {
        echo fgets($fd);
    }   
));
```

