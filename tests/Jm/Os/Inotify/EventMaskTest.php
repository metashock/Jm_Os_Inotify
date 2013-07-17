<?php


class Jm_Os_Inotify_EventMaskTest extends PHPUnit_Framework_TestCase
{

    /**
     * Lookup table that contains all possible flags and their
     * human readable representation
     */
    protected $possibleFlags = array(
           'IN_ACCESS' => IN_ACCESS,
           'IN_ATTRIB' => IN_ATTRIB,
           'IN_CLOSE_WRITE' => IN_CLOSE_WRITE,
           'IN_CLOSE_NOWRITE' => IN_CLOSE_NOWRITE,
           'IN_CREATE' => IN_CREATE,
           'IN_DELETE' => IN_DELETE,
           'IN_DELETE_SELF' => IN_DELETE_SELF,
           'IN_MODIFY' => IN_MODIFY,
           'IN_MOVE_SELF' => IN_MOVE_SELF,
           'IN_MOVED_FROM' => IN_MOVED_FROM,
           'IN_MOVED_TO' => IN_MOVED_TO,
           'IN_OPEN' => IN_OPEN,
           'IN_ISDIR' => IN_ISDIR,
           'IN_UNMOUNT' => IN_UNMOUNT,
           'IN_Q_OVERFLOW' => IN_Q_OVERFLOW,
           'IN_IGNORED' => IN_IGNORED
    );


    /**
     * Just a simple constructor test. Tests the raw() getter as well
     */
    public function testConstruct() {
        $mask = new Jm_Os_Inotify_EventMask(0);
        $this->assertEquals(0, $mask->raw());
    }


    /**
     * Tests if __toString() returns a proper result by setting every
     * flag and checking if the output contains the proper flag name.
     */
    public function testToString() {
        foreach($this->possibleFlags as $key => $value) {
            $mask = new Jm_Os_Inotify_EventMask($value);
            $this->assertEquals($key, $mask . '');
        }
    }


    /**
     * Tests if the contains() method works by setting each flag
     * and test if contains() returns true after that
     */
    public function testContains() {
        foreach($this->possibleFlags as $key => $value) {
            $mask = new Jm_Os_Inotify_EventMask($value);
            $this->assertTrue($mask->contains($value));
        }
    }
}

