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
 * An event iterator is used as the return type of the methods 
 * *_Inotify_Instance::events() and *_Inotify_Instance::wait(). This is
 * because if recursive directory watching is desired the framework 
 * needs to know about certain types of events happened in order to
 * remove watches if a folder has been deleted or moved away or if
 * a new folder has been created in the monitored tree.
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
class Jm_Os_Inotify_EventIterator extends ArrayIterator
{

    /**
     * The related inotify instance
     * 
     * @var Jm_Os_Inotify_Instance
     */
    protected $instance;


    /**
     * Contains watches that have to be removed after
     * iteration over all events
     *
     * @var array
     */
    protected $garbage;


    /** 
     * It is important that only one time iteration over events is possible
     * 
     * @var boolean
     */
    protected $processed;


    /**
     * Constructor
     *
     * @param array $events                    events obtained by inotify_read()
     * @param Jm_Os_Inotify_Instance $instance The related instance
     */
    public function __construct(
        array $events,
        Jm_Os_Inotify_Instance $instance
    ) {
        parent::__construct($events);
        $this->instance = $instance;
        $this->garbage = array();
        $this->processed = FALSE;
    }


    /**
     * Internally called by the php interpreter on every foreach loop.
     * 
     * @return Jm_Os_Inotify_Event
     */
    public function current() {

        $current = parent::current();
        $watch = $this->instance->findWatch($current['wd']);
        $path = $watch->path();

        // if this event is related to file we use the 
        // name property
        if(!($current['mask'] & IN_ISDIR)) {
            $path .= '/' . $current['name'];
        }

        $event = new Jm_Os_Inotify_Event(
            $current['wd'],
            $current['mask'],
            $current['cookie'],
            $path,
            $this->instance
        );

        $mask = $event->mask();

        if($watch && !($watch->options()
            & Jm_Os_Inotify::IN_X_RECURSIVE)
        ) {
            return $event;
        }

        switch(TRUE) {
            case $mask->contains(IN_CREATE)
                && $event->mask()->contains(IN_ISDIR) :
                $watch = $this->instance->watch(
                    $event->fullpath(), $watch->options()
                        // it must be sure that we'll receive this
                        // events to keep the watch list up to date
                        | IN_DELETE_SELF | IN_MOVED_TO
                );
                break;

            case $mask->contains(IN_DELETE_SELF):
                $this->garbage []= $watch;
                break;
        }
            
        return $event;
    }


    /**
     * The method cleans up unused watches after all events had 
     * been iterated over. Also the method makes sure that 
     * iteration over events is only one time possible.
     *
     * @return boolean
     */
    public function valid() {
        if($this->processed === TRUE) {
            return FALSE;
        }

        $valid = parent::valid();
        if($valid === FALSE) {
            foreach($this->garbage as $watch) {
                $this->instance->cleanupDeletedWatch($watch);
            }
            $this->processed = TRUE;
        }
        return $valid;
    }


    /**
     * This method throws an exception, that's all. This is because
     * the method current creates or removes watches. If offsetGet could
     * be called as well it would be unclear which method should do 
     * the watch removal / creational work. Also it must be safe that
     * no action will take place twice. Too much problems for the moment.
     * That's why "disabled"
     *
     * @param integer $index Index of the element of interest
     *
     * @return void Pigs can fly...
     * 
     * @throws Exception
     */
    public function offsetGet($index) {
        throw new Exception('Not implemented');
    }
}
