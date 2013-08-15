<?php

class Jm_Os_Inotify_InstanceTest extends PHPUnit_Framework_TestCase
{

    /**
     * Path to temporary test directory
     *
     * @var string
     */
    protected $path;
    
    
    /**
     * Another directory wihtin the test directory
     * 
     * @var type 
     */
    protected $subpath;


    /**
     * Creates the test folder and registers a shutdown handler for 
     * cleanup if regular cleanup won't run because of a syntax error
     */
    public function setUp() {
        for($trys = 0; $trys < 3; $trys++) {
            $this->path = sys_get_temp_dir() . '/phpunit_jm_os_inotify_' . uniqid();
            if(@mkdir($this->path, 0770)) {
                break;
            }
        }
        // pigs can fly
        if($trys === 3) {
            $this->markTestSkipped('Failed to create test directory');
        }
        
        mkdir($this->path . '/' . uniqid());

        // although it is not guranted that objects exists anymore when
        // shutdown is executed I observed this working when a fatal 
        // error happens
        register_shutdown_function(array($this, 'tearDown'));
    }


    /**
     * Recursively deletes the temporary test directory if it exists
     */
    public function tearDown() {
        if(!is_dir($this->path)) {
            return;
        }

        $stack = array($this->path);
        do {
            $dir = array_pop($stack);
            $stack[]= $dir;
            $files = array_diff(scandir($dir), array('.','..'));
            foreach ($files as $file) {
                if(is_dir("$dir/$file")) {
                    $stack[]= "$dir/$file";
                    continue 2;
                } else {
                    chmod("$dir/$file", 0600);
                    unlink("$dir/$file");
                }
            }
            rmdir($dir);
            array_pop($stack);
        } while (!empty($stack));
    }
    
   
    /**
     * Watches a directory and tests inotify events
     */ 
    public function testWatchDirectory() {
        $tempnam = tempnam($this->path, uniqid());
        $options = IN_ALL_EVENTS;
        $recursive = FALSE;
          
        $instance = Jm_Os_Inotify::init();        
        $watch = $instance->watch($this->path, $options, $recursive);

        // check if $watch has been properly initialized
        $this->assertTrue(is_object($watch));
        $this->assertEquals('Jm_Os_Inotify_Watch', get_class($watch));
        $this->assertEquals($this->path, $watch->path());
        $this->assertEquals($options, $watch->options());
        $this->assertTrue(is_int($watch->wd()));
        $this->assertNull($watch->parentwatch());
        $this->assertEquals($instance, $watch->inotifyInstance());

        $this->nonRecursiveTests($instance, $tempnam);

        // remove the watch
        $instance->unwatch($watch);
        // create a file into the test directory
        tempnam($this->path, uniqid());
        // one event with mask() IN_IGNORED is expected
        $events = $instance->events();
        $this->assertEquals(1, count($events));
        $this->assertTrue($events[0]->mask()->contains(IN_IGNORED));

        // from now on no further events are expected
        tempnam($this->path, uniqid());
        // one event with mask() IN_IGNORED is expected
        $events = $instance->events();
        $this->assertEquals(0, count($events));
    }


    /**
     * Watches a directory and tests inotify events
     */ 
    public function testWatchDirectoryRecursive() {
        $tempnam = tempnam($this->path, uniqid());
        $options = 
            IN_ALL_EVENTS 
          | Jm_Os_Inotify::IN_X_RECURSIVE
          | Jm_Os_Inotify::IN_X_RECURSIVE_FOLLOW;

        $recursive = FALSE;
        $instance = Jm_Os_Inotify::init();        
        $watch = $instance->watch($this->path, $options, $recursive);

        // check if $watch has been properly initialized
        $this->assertTrue(is_object($watch));
        $this->assertEquals('Jm_Os_Inotify_Watch', get_class($watch));
        $this->assertEquals($this->path, $watch->path());
        $this->assertEquals($options, $watch->options());
        $this->assertTrue(is_int($watch->wd()));
        $this->assertNull($watch->parentwatch());
        $this->assertEquals($instance, $watch->inotifyInstance());

        $this->nonRecursiveTests($instance, $tempnam);
        $this->recursiveTests($instance, $tempnam);
    }



    /**
     * Plays around with the test directory to generate some events
     */
    protected function nonRecursiveTests(Jm_Os_Inotify_Instance $instance, $tempnam) {
        // create a file
        $file = tempnam($this->path, uniqid());
        // this should trigger  the following events:
        $expectedMasks = array(IN_CREATE, IN_OPEN, IN_CLOSE_WRITE, IN_ATTRIB);
        foreach($instance->events() as $event) {
            $expected = array_shift($expectedMasks);
            $this->assertEquals($expected, $event->mask()->raw());
            $this->assertEquals($file, $event->fullpath());
        }

        
        $fd = fopen($file, 'r+');
        $expectedMasks = array(IN_OPEN);
        foreach($instance->events() as $event) {
            $expected = array_shift($expectedMasks);
            $this->assertEquals($expected, $event->mask()->raw());
            $this->assertEquals($file, $event->fullpath());
        }
        
       
        fwrite($fd, 'test');
        $expectedMasks = array(IN_MODIFY);
        foreach($instance->events() as $event) {
            $expected = array_shift($expectedMasks);
            $this->assertEquals($expected, $event->mask()->raw());
            $this->assertEquals($file, $event->fullpath());
        }

        fclose($fd);
        $expectedMasks = array(IN_CLOSE_WRITE);
        foreach($instance->events() as $event) {
            $expected = array_shift($expectedMasks);
            $this->assertEquals($expected, $event->mask()->raw());
            $this->assertEquals($file, $event->fullpath());
        }

        rename($file, $tempnam);
        $expectedMasks = array(
            IN_MOVED_FROM, IN_MOVED_TO
        );

        foreach($instance->events() as $event) {
            $expectedMask = array_shift($expectedMasks);
            $this->assertEquals($expectedMask, $event->mask()->raw());
            if($event->mask()->contains(IN_MOVED_FROM)) {
                $expectedPath = $file;
            } else {
                $expectedPath = $tempnam;
            }

            $this->assertEquals(
                $expectedPath, $event->fullpath()
            );
        }
    }


    /**
     *
     */
    protected function recursiveTests(Jm_Os_Inotify_Instance $instance, $tempnam) {
        
        // create a file in sub path. this should generated the expected events
        $file = tempnam($this->subpath, uniqid());
        // this should trigger  the following events:
        $expectedMasks = array(IN_CREATE, IN_OPEN, IN_CLOSE_WRITE, IN_ATTRIB);
        foreach($instance->events() as $event) {
            $expected = array_shift($expectedMasks);
            $this->assertEquals($expected, $event->mask()->raw());
            $this->assertEquals($file, $event->fullpath());
        }

        
        $fd = fopen($file, 'r+');
        $expectedMasks = array(IN_OPEN);
        foreach($instance->events() as $event) {
            $expected = array_shift($expectedMasks);
            $this->assertEquals($expected, $event->mask()->raw());
            $this->assertEquals($file, $event->fullpath());
        }
        
       
        fwrite($fd, 'test');
        $expectedMasks = array(IN_MODIFY);
        foreach($instance->events() as $event) {
            $expected = array_shift($expectedMasks);
            $this->assertEquals($expected, $event->mask()->raw());
            $this->assertEquals($file, $event->fullpath());
        }

        fclose($fd);
        $expectedMasks = array(IN_CLOSE_WRITE);
        foreach($instance->events() as $event) {
            $expected = array_shift($expectedMasks);
            $this->assertEquals($expected, $event->mask()->raw());
            $this->assertEquals($file, $event->fullpath());
        }

        rename($file, $tempnam);
        $expectedMasks = array(
            IN_MOVED_FROM, IN_MOVED_TO
        );

        foreach($instance->events() as $event) {
            $expectedMask = array_shift($expectedMasks);
//            $this->assertEquals($expectedMask, $event->mask()->raw());
            if($event->mask()->contains(IN_MOVED_FROM)) {
                $expectedPath = $file;
            } else {
                $expectedPath = $tempnam;
            }

            $this->assertEquals(
                $expectedPath, $event->fullpath()
            );
        }
    }


    /**
     * 
     */
    public function testWait() {
        $in = Jm_Os_Inotify::init();
        $in->watch($this->path);
        foreach($in->wait(0, 1) as $e) {
            var_dump($e);
        }
    }



    /**
     * @expectedException Jm_FileSystem_FileNotFoundException
     */
    public function testWatch_FileNotFoundException() {
        $in = Jm_Os_Inotify::init();
        $in->watch(uniqid());        
    }


    /**
     * @expectedException Jm_FileSystem_FileNotReadableException
     */
    public function testWatch_FileNotReadableException() {
        $filename = $this->path . '/' . uniqid();
        // create test file and make it readonly
        touch($filename);
        chmod($filename, 0);

        $in = Jm_Os_Inotify::init();
        $in->watch($filename);
    }


    /**
     * Tests if watch() will throw the proper exception if the
     * maximum of watches per user is reached. This test will take
     * quite long, so I disbabled it by default
     *
     * @expectedException Jm_Os_Inotify_Exception
     */
    public function testWatchLimit() {
        // comment next line to exexute this test
        $this->markTestSkipped('Takes too long :)');
        $in = Jm_Os_Inotify::init();
        $limit = (integer) file_get_contents('/proc/sys/fs/inotify/max_user_watches');
        for($i = 0; $i < $limit + 1; $i++) {
            $filename = $this->path . '/' . $i;
            // create test file and make it readonly
            touch($filename);
            $in->watch($filename);
        }
    }
}

