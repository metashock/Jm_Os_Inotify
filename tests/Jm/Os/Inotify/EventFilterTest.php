<?php

class Jm_Os_Inotify_EventFilterTest extends PHPUnit_Framework_TestCase
{


    public function testUsage() {
        
        // mock an instance of inotify as it is required as an
        // argument to the Jm_Os_Inotify_Event constructor
        $instance = $this->getMockBuilder('Jm_Os_Inotify_Instance')
            ->getMock();       
        
        // create a filter using a simple anonymous function
        $filter = new Jm_Os_Inotify_EventFilter(function($current) {
            if($current->name() === 'test') {
                return TRUE;
            } else {
                return FALSE;
            }
        });

        $event = new Jm_Os_Inotify_Event(array(
            'wd' => 1, 'mask' => 1, 'cookie' => 0, 'name' => 'test'
        ), $instance);
        $this->assertTrue($filter->valid($event));

        $event = new Jm_Os_Inotify_Event(array(
            'wd' => 1, 'mask' => 1, 'cookie' => 0, 'name' => 'not-test'
        ), $instance);        
        $this->assertFalse($filter->valid($event));
    }
    
}

