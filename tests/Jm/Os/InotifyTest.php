<?php


class Jm_Os_InotifyTest extends PHPUnit_Framework_TestCase
{
    /**
     * Tests that init() will return an instance of
     * Jm_Os_Inotify_Instance. Not much to test here.
     */
    public function testInit() {
        $instance = Jm_Os_Inotify::init();
        $this->assertTrue(is_object($instance));
        $this->assertEquals('Jm_Os_Inotify_Instance',
            get_class($instance));
    }
}

