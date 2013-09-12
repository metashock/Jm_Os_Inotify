<?php
/**
 * Jm_Os_Inotify
 *
 * Copyright (c) 2013, Thorsten Heymann <thorsten@metashock.de>.
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 *
 *   * Redistributions of source code must retain the above copyright
 *     notice, this list of conditions and the following disclaimer.
 *
 *   * Redistributions in binary form must reproduce the above copyright
 *     notice, this list of conditions and the following disclaimer in
 *     the documentation and/or other materials provided with the
 *     distribution.
 *
 *   * Neither the name Thorsten Heymann nor the names of his
 *     contributors may be used to endorse or promote products derived
 *     from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS
 * FOR A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE
 * COPYRIGHT OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER
 * CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT
 * LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN
 * ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE
 * POSSIBILITY OF SUCH DAMAGE.
 *
 * PHP Version >= 5.1.2
 *
 * @category  Os
 * @package   Jm\Os\Inotify
 * @author    Thorsten Heymann <thorsten@metashock.de>
 * @copyright 2013 Thorsten Heymann <thorsten@metashock.de>
 * @license   BSD-3 http://www.opensource.org/licenses/BSD-3-Clause
 * @version   GIT: $$GITVERSION$$
 * @link      http://www.metashock.de/
 * @since     0.1.0
 */
/**
 * Wrapper class for the bitmasks passed to *_Inotify_Instance::watch()
 * and returned by *_Inotify_Event::mask(). Provides a `__toString()`
 * method to make it easy to visualize the flags set in the bitmask. 
 * Also provides the method contains($flag) to make the check whether
 * a certain flag is set more readable and short.
 *
 * @category  Os
 * @package   Jm\Os\Inotify
 * @author    Thorsten Heymann <thorsten@metashock.de>
 * @copyright 2013 Thorsten Heymann <thorsten@metashock.de>
 * @license   BSD-3 http://www.opensource.org/licenses/BSD-3-Clause
 * @version   GIT: $$GITVERSION$$
 * @link      http://www.metashock.de/
 * @since     0.1.0
 */
class Jm_Os_Inotify_Flags
{

    /**
     * The raw integer value of the bitmask
     *
     * @var integer
     */
    protected $integer;


    /**
     * Constructor
     *
     * @param integer $integer The bitmask
     */
    public function __construct($integer) {
        $this->integer = $integer;
    }


    /**
     * Returns the raw bitmask
     *
     * @return integer
     */
    public function raw() {
        return $this->integer;
    }


    /**
     * Checks whether a certain flag in the bitmask is set or not
     *
     * @param integer $flag The flag to be checked
     *
     * @return boolean
     */
    public function contains($flag) {
        return ($this->integer & $flag) === $flag;
    }


    /**
     * Returns the string representation of the event mask
     *
     * @return string
     */
    public function __toString() {
        $flags = array();

        $possibleFlags = array(
            IN_ACCESS => 'IN_ACCESS',
            IN_ATTRIB => 'IN_ATTRIB',
            IN_CLOSE_WRITE => 'IN_CLOSE_WRITE',
            IN_CLOSE_NOWRITE => 'IN_CLOSE_NOWRITE',
            IN_CREATE => 'IN_CREATE',
            IN_DELETE => 'IN_DELETE',
            IN_DELETE_SELF => 'IN_DELETE_SELF',
            IN_MODIFY => 'IN_MODIFY',
            IN_MOVE_SELF => 'IN_MOVE_SELF',
            IN_MOVED_FROM => 'IN_MOVED_FROM',
            IN_MOVED_TO => 'IN_MOVED_TO',
            IN_OPEN => 'IN_OPEN',
            IN_ISDIR => 'IN_ISDIR',
            IN_UNMOUNT => 'IN_UNMOUNT',
            IN_Q_OVERFLOW => 'IN_Q_OVERFLOW',
            IN_IGNORED => 'IN_IGNORED',
            /* add_watch specifc options */
            IN_ONLYDIR => 'IN_ONLYDIR',
            IN_DONT_FOLLOW => 'IN_DONT_FOLLOW',
            IN_MASK_ADD => 'IN_MASK_ADD',
            IN_ONESHOT => 'IN_ONESHOT',
            /* extension */
            Jm_Os_Inotify::IN_X_RECURSIVE => 'IN_X_RECURSIVE'
        );

        foreach($possibleFlags as $flag => $name) {       
            if(($this->integer & $flag) === $flag){
                $flags []= $name;
            }   
        }

        return implode(' | ', $flags); 
    }
}

