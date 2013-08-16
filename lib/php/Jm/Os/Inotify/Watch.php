<?php
/**
 * Jm_OS_Inotify
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
 * Represents a inotify_watch structure as a class. Provides static
 * functionality to lookup a watch by it's wd a little bit like 
 * inotify_find_watch in kernel. I miss this functionality exposed
 * to the userland.
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
class Jm_Os_Inotify_Watch
{
     /**
     *  @var string
     */
    protected $path;

    /**
     * Bitmask of options
     *
     * @var integer
     */
    protected $options;

    /**
     * Points to the underlying watch descriptor
     *
     * @var resource(inotify_watch)
     */ 
    protected $wd;


    /**
     * A "parent" watch. A watch for the parent folder if the watch was 
     * established by recursive tree watching. Will be NULL otherwise
     * 
     * @var Jm_Os_Inotify_Watch
     */
    protected $parent;


    /**
     * Constructor.
     *
     * @param string                 $path            Path to the file or 
     *                                                directory being watched
     * @param integer                $options         Integer bitmask 
     *                                                holding inotify options
     * @param integer                $wd              The id of the watch 
     *                                                (per inotify instance)
     * @param Jm_Os_Inotify_Instance $inotifyInstance The inotify queue
     * @param Jm_Os_Inotify_Watch    $parent          The parent watch. Used 
     *                                                with recursive watches
     *
     * @return Jm_Os_Inotify_Watch
     *
     * @throws InvalidArgumentException if one of the arguments type mismatch
     */
    public function __construct (
        $path,
        $options,
        $wd,
        Jm_Os_Inotify_Instance $inotifyInstance,
        Jm_Os_Inotify_Watch $parent = NULL
    ) {
        Jm_Util_Checktype::check('string', $path);
        Jm_Util_Checktype::check('integer', $options);
        Jm_Util_Checktype::check('integer', $wd);
        $this->path = $path;
        $this->wd = $wd;
        $this->options = $options;
        $this->inotifyInstance = $inotifyInstance;
        $this->parent = $parent;
    }


    /**
     * Returns the path to the watched file or directory
     *
     * @return string
     */
    public function path() {
        return $this->path;
    }


    /**
     * Returns the options bitmask
     *
     * @return integer
     */
    public function options() {
        return $this->options;
    }


    /**
     * Returns the watch id (per inotify instance)
     *
     * @return integer
     */
    public function wd() {
        return $this->wd;
    }

    
    /**
     * Returns the inotify instance this watch belongs to.
     *
     * @return Jm_Os_Inotify_Instance
     */
    public function inotifyInstance() {
        return $this->inotifyInstance;
    }


    /**
     * Returns the parent watch, if exists. Otherwise NULL.
     * The method is called parentwatch() instead of just parent()
     * because parent is a reserved word in PHP.
     *
     * @return Jm_OS_Inotify_Watch|NULL 
     */
    public function parentwatch() {
        return $this->parent;
    }
}

