<?php

class Jm_Os_Inotify_EventTest extends PHPUnit_Framework_TestCase
{


    public function testUsage() {
        // mock an inotify instance and a watch
        $inotifyInstance = $this->getMock('Jm_Os_Inotify_Instance');
        $watch = $this->getMockBuilder('Jm_Os_Inotify_Watch')
          ->disableOriginalConstructor()
          ->getMock();

        // configure the mocked objects
        $watch->expects($this->once())
          ->method('path')
          ->will($this->returnValue('/tmp'));

        $inotifyInstance->expects($this->once())
          ->method('findWatch')
          ->will($this->returnValue($watch));

        $event = new Jm_Os_Inotify_Event(array(
            'wd' => 1, 'mask' => 256, 'cookie' => 0, 'name' => 'test'
        ), $inotifyInstance);

        $this->assertEquals(1, $event->wd());
        $this->assertEquals(256, $event->mask()->raw());
        $this->assertEquals(0, $event->cookie());
        $this->assertEquals('test', $event->name());
        $this->assertEquals('/tmp/test', $event->fullpath());
    }


    /**
     * Tests whether the constructor will throw an InvalidArgumentException
     * if one of the required array keys of the parameter is missing
     *
     * @expectedException InvalidArgumentException
     * @dataProvider testInvalidArgumentExceptionDataProvider
     */
    public function testInvalidArgumentException($args) {
        // mock an inotify instance
        $inotifyInstance = $this->getMock('Jm_Os_Inotify_Instance');

        $class = new ReflectionClass('Jm_Os_Inotify_Event');
        $class->newInstance($args, $inotifyInstance);
    }


    /**
     * Data provider for the method above
     *
     * @return array
     */
    public function testInvalidArgumentExceptionDataProvider(){
        return array (
            array(array('mask' => 1, 'cookie' => 1, 'name' => 'test')),
            array(array('wd' => 1, 'cookie' => 1, 'name' => 'test')),
            array(array('wd' => 1, 'mask' => 1, 'name' => 'test')),
            array(array('wd' => 1, 'mask' => 1, 'cookie' => 1))
        );
    }
}

