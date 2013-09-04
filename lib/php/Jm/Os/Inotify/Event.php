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
 * @package   Jm_Os_Inotify
 * @author    Thorsten Heymann <thorsten@metashock.de>
 * @copyright 2013 Thorsten Heymann <thorsten@metashock.de>
 * @license   BSD-3 http://www.opensource.org/licenses/BSD-3-Clause
 * @version   GIT: $$GITVERSION$$
 * @link      http://www.metashock.de/
 * @since     0.1.0
 */
/**
 * An event represents an action which happened on a file or directory.
 * The event class provides information about which type of action
 * happened and which file system object was affected.
 *
 * @category  Os
 * @package   Jm_Os_Inotify
 * @author    Thorsten Heymann <thorsten@metashock.de>
 * @copyright 2013 Thorsten Heymann <thorsten@metashock.de>
 * @license   BSD-3 http://www.opensource.org/licenses/BSD-3-Clause
 * @version   GIT: $$GITVERSION$$
 * @link      http://www.metashock.de/
 * @since     0.1.0
 */
class Jm_Os_Inotify_Event
{

    /**
     * A watch descriptor
     *
     * @resource
     */
    protected $wd;

    /**
     * A bit mask of events
     * 
     * @var Jm_Os_Inotify_EventMask
     */
    protected $mask;


    /**
     * A unique integer that connects related events. Currently this 
     * is only used for rename events, and allows the resulting pair of 
     * IN_MOVE_FROM and IN_MOVE_TO events to be connected by the application.
     *
     * @var integer
     */
    protected $cookie;


    /**
     * The name of a file
     * 
     * @var string name
     */
    protected $name;


    /**
     * The instance related to this event
     *
     * @var Jm_Os_Inotify_Instance
     */
    protected $inotifyInstance;


    /**
     * Constructor
     * 
     * @param integer $wd     The watch descriptor
     * @param integer $mask   Bitmask that contains information about
     *                        the type of action happened
     * @param integer $cookie TODO document
     * @param string  $path   Path to the file or directory
     * @param Jm_Os_Inotify_Instance $inotifyInstance The inotify instance
     *
     * @return Jm_Os_Inotify_Event
     */
    public function __construct(
        $wd, $mask, $cookie, $path,
        Jm_Os_Inotify_Instance $inotifyInstance
    ) {
        Jm_Util_Checktype::check('integer', $wd);
        Jm_Util_Checktype::check('integer', $mask);
        Jm_Util_Checktype::check('integer', $cookie);
        Jm_Util_Checktype::check('string', $path);
        $this->wd = $wd;
        $this->mask = new Jm_Os_Inotify_Flags($mask);
        $this->cookie = $cookie;
        $this->path = $path;
        $this->inotifyInstance = $inotifyInstance;
    }


    /**
     * Returns the watch descriptor for the event. You can call:
     *
     *    $event->inotifyInstance()->findWatch($event->wd())
     * 
     * to obtain the related watch object. But note that the watch
     * object may no longer exist as it has been removed from the instance
     * or the related file system object was deleted.
     *
     * @return integer
     */
    public function wd() {
        return $this->wd;
    }


    /**
     * Returns the bitmask representing the the type of action happened
     * and further options.
     *
     * @see TODO
     *
     * @return Jm_Os_Inotify_Flags
     */
    public function mask() {
        return $this->mask;
    }


    /**
     * TODO document
     *
     * @return integer
     */
    public function cookie() {
        return $this->cookie;
    }


    /**
     * Returns the path to the file or directory
     * which is related to the event.
     *
     * @return string
     */
    public function fullpath() {
        return $this->path;
    }
}
