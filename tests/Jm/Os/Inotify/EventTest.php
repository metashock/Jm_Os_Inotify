<?php

class Jm_Os_Inotify_EventTest extends PHPUnit_Framework_TestCase
{


    public function testGetSet() {
        // mock an inotify instance and a watch
        $inotifyInstance = $this->getMock('Jm_Os_Inotify_Instance');
        $event = new Jm_Os_Inotify_Event(1, 256, 0, 'test', $inotifyInstance);

        $this->assertEquals(1, $event->wd());
        $this->assertEquals(256, $event->mask()->raw());
        $this->assertEquals(0, $event->cookie());
        $this->assertEquals('test', $event->fullpath());
    }


    /**
     * Tests whether the constructor will throw an InvalidArgumentException
     * if one of the required array keys of the parameter is missing
     *
     * @expectedException InvalidArgumentException
     * @dataProvider testInvalidArgumentExceptionDataProvider
     */
    public function testInvalidArgumentException($wd, $mask, $cookie, $path) {
        // mock an inotify instance
        $inotifyInstance = $this->getMock('Jm_Os_Inotify_Instance');

        $class = new ReflectionClass('Jm_Os_Inotify_Event');
        $class->newInstance($wd, $mask, $cookie, $path, $inotifyInstance);
    }


    /**
     * Data provider for the method above
     *
     * @return array
     */
    public function testInvalidArgumentExceptionDataProvider(){
        return array (
            array('1', 1, 1, 'test'),
            array(1, '1', 1, 'test'),
            array(1, 1, '1', 'test'),
            array(1, 1, 1, 1)
        );
    }
}

