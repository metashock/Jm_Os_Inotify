<?php

class Jm_Os_Inotify_WatchTest extends PHPUnit_Framework_TestCase
{

    public function setUp() {
        $dir = sys_get_temp_dir();
        $this->queue = new Jm_Os_Inotify_Instance(
            // ... todo options
        );
    }


    public function testConstructor() {
        
    }
}

