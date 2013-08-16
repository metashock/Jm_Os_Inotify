<?php

class Jm_Os_Inotify_EventIteratorTest extends PHPUnit_Framework_TestCase
{

    /**
     *
     */
    public function testUsage() {
        // mock everything we need in order to make the test
        // independend from other classes

        // we need an inotify instance first as the iterator
        // will internally call `Instance::findWatch()`
        $instance = $this->getMockBuilder('Jm_Os_Inotify_Instance')
            ->disableOriginalConstructor()
            ->getMock();

        $options = IN_ALL_EVENTS;

        // configure the instance in a way that it finds the
        // expected watches
        $instance->expects($this->any())
            ->method('findWatch')
/*            ->with(1) */
            ->will($this->returnValue(new Jm_Os_Inotify_Watch(
                'test', $options, 1, $instance 
            )));


        // event array that looks like events that will be returned by inotify_read().
        $events = array(
            array('wd' => 1, 'mask' => 1 | IN_ISDIR, 'cookie' => 0, 'name' => 'test'),
            array('wd' => 1, 'mask' => 1, 'cookie' => 0, 'name' => 'test'),
            array('wd' => 1, 'mask' => 1, 'cookie' => 0, 'name' => 'test'),
            array('wd' => 1, 'mask' => 1, 'cookie' => 0, 'name' => 'test')
        );

        $iterator = new Jm_Os_Inotify_EventIterator($events, $instance);
        foreach($iterator as $event) {
            //echo $event->mask() . PHP_EOL;
        }
    }


    /**
     * Tests that offsetGet is "disabled". The is a poor test
     * meant for code coverage only.
     *
     * @expectedException Exception
     */
    public function testOffsetGetException() {
        $it = $this->getMock('Jm_Os_Inotify_EventIterator');
        $it->offsetGet(0);
    }
}

