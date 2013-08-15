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
 * Represensts an inotify queue
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
class Jm_Os_Inotify_Instance
{

    /**
     * The resource descriptor to the underlying inotify queue
     *
     * @resource(inotify_instance)
     */
    protected $fd;


    /**
     * A lookup table for looking up a watch by it's id
     *
     * @var array
     */
    protected $watches = array();


    /**
     * Lookup table to perform fast path -> watch lookups
     *
     * @var array
     */
    protected $watch_by_path = array();


    /**
     * @var Jm_Log
     */
    protected $log;


    /**
     *
     */
    public function __construct(Jm_Log $log = NULL) {
        if(!function_exists('inotify_init')) {
            // @codeCoverageIgnoreStart
            throw new Exception('inotify is not available on your system');
            // @codeCoverageIgnoreEnd
        }
        $this->fd = inotify_init();
        $this->log = $log;
    }



    /**
     * Adds a path to the watch pool
     *
     * @param string $path
     *
     * @return Jm_Os_Inotify_Watch
     *
     * @throws Jm_Os_Inotify_Exception
     */
    public function watch (
        $path = '',
        $options = IN_ALL_EVENTS
    ){
        // check file permission for $path
        if(!file_exists($path)) {
            throw new Jm_Filesystem_FileNotFoundException(
                'The file or directory \'' . $path . '\' was not found'
            );
        }

        if(!is_readable($path)) {
            throw new Jm_Filesystem_FileNotReadableException(
                'The file or directory \'' . $path . '\' isn\'t readable'
            );
        }

        // recursive tree watches and following is disabled per default
        $recursive = $follow = FALSE;

        // check if the recursive flags have been set
        if(($options & Jm_Os_Inotify::IN_X_RECURSIVE)
            === Jm_Os_Inotify::IN_X_RECURSIVE
        ) {

            $recursive = TRUE;

            // check if we should follow newly created directories.
            // following means that if a new directory is created inside
            // a watched directory a watch would been added for it.
            if(($options & Jm_Os_Inotify::IN_X_RECURSIVE_FOLLOW)
                === Jm_Os_Inotify::IN_X_RECURSIVE_FOLLOW
            ) {
                $follow = TRUE;
            }
        }

        // add the watch
        if($recursive === TRUE) {
            return $this->watchRecursive($path, $options, $follow);
        } else {
            $wd = @inotify_add_watch($this->fd(), $path, $options);
            $this->log("Watching {$path}");
            if($wd === FALSE) {
                $error = error_get_last();
                $msg = is_null($error) ? 'inotify_add_watch(): Unknown error'
                    : $error['message'];

                throw new Jm_Os_Inotify_Exception($msg);
            }
            $watch = new Jm_Os_Inotify_Watch($path, $options, $wd, $this);
            $this->watches[$wd] = $watch;
            $this->watch_by_path[$path] = $watch;
            return $watch;
        }
    }



    /**
     *
     */
    protected function watchRecursive($path, $options) {
        $stack = array($path);
        $rollback = false;
        $root = NULL;
        
        // remove that X_* flags from the options mask as it would 
        // otherwise lead to problems interpreting the event masks
        // returned by inotify_read()
        $_options = $options & ~Jm_Os_Inotify::IN_X_RECURSIVE;
        $_options = $_options & ~Jm_Os_Inotify::IN_X_RECURSIVE_FOLLOW;        
        
        do {
            $path = array_pop($stack);

            $wd = @inotify_add_watch($this->fd(), $path, $_options);
            $this->log("Watching {$path} (wd:$wd) (mask:$_options)",
                Jm_Log_Level::DEBUG);
            $watch = new Jm_Os_Inotify_Watch($path, $options, $wd, $this);
            if(is_null($root)) {
                $root = $watch;
            }
            $this->watches[$wd] = $watch;
            $this->watch_by_path[$path] = $watch;
            foreach(scandir($path) as $file) {
                if($file === '.' || $file === '..' || !is_dir($path . '/' . $file)) {
                    continue;
                }
                
                $stack []= $path . '/' . $file;
            }
        } while(!empty($stack));

        // throw away the events that have been generated by this function
        // (usage of scandir())
        $this->events();

        // return the root watch
        return $root;
    }


    /**
     * Removes a watch. Note that $watch will be set to NULL
     * after calling this metod
     *
     * @return Jm_Os_Inotify_Instance
     */
    public function unwatch(Jm_Os_Inotify_Watch $watch) {
        $this->log("Removing watch {$watch->path()} (wd:{$watch->wd()})",
            Jm_Log_Level::DEBUG
        );
        $ret = @inotify_rm_watch($this->fd(), $watch->wd());
        // @codeCoverageIgnoreStart
        if($ret === FALSE) {
            $error = error_get_last();
            if(is_null($error)) {
                $message = 'inotify_rm_watch(): Unknown error';
            } else {
                $message = $error['message'];
            }
            throw new Jm_Os_Inotify_Exception($message);
        }
        // @codeCoverageIgnoreEnd
        unset($this->watches[$watch->wd()]);
        unset($this->watch_by_path[$watch->path()]);
        return $this; 
    }


    /**
     * Returns events in blocking mode. The method will return
     * only if events will happen. You can specify a timeout in seconds 
     * and or milliseconds. If both have been omitted the method will
     * forever until some events occur.
     *
     * @param integer $sec   Timeout in seconds
     * @param inteher $usec  Timeout in microseconds
     *
     * @return Jm_Os_Inotify_EventIterator
     *
     * @throws Exception if one out of stream_set_blocking, stream_select
     * or inotify_read fails
     */
    public function wait($sec = NULL, $usec = NULL) {
        $read = array($this->fd);
        $dummy = array(); 
        stream_set_blocking($this->fd(), 1);
        $ret = stream_select($read, $dummy, $dummy, $sec, $usec);

        switch(TRUE) {
            // an error has occured
            case $ret = FALSE :
                $error = error_get_last();
                if(is_null($error)) {
                    $msg = 'stream_select(): Unknown error';
                } else {
                    $msg = $error['message'];
                }
                throw new Exception(__METHOD__ . ': ' . $msg);

            // the timeout has been reached
            case $ret === 0 :
                return new Jm_Os_Inotify_EventIterator(array(), $this);

            // read an return events
            default :
                return $this->events();
        }
    }


    /**
     * Returns events in non blocking mode
     * 
     * @return Jm_Os_Inotify_EventIterator
     */ 
    public function events() {
        $ret = @stream_set_blocking($this->fd(), 0);
        // @codeCoverageIgnoreStart
        if($ret === FALSE) {
            $error = error_get_last();
            if(is_null($error)) {
                $msg = 'stream_set_blocking(): Unknown error';
            } else {
                $msg = $error['message'];
            }
            throw new Exception(__METHOD__ . ': ' . $msg);
        }
        // @codeCoverageIgnoreEnd

        $events = @inotify_read($this->fd());
        // @codeCoverageIgnoreStart
        if($events === FALSE) {
            $error = error_get_last();
            // if error_get_last() returns false as inotify_read()
            // the cause is the unblocking read operation
            if(!is_null($error)) {
                $msg = $error['message'];
                throw new Exception(__METHOD__ . ': ' . $msg);
            }
        }
        // @codeCoverageIgnoreEnd

        if(!$events) {
            $events = array();
        } 
        return new Jm_Os_Inotify_EventIterator($events, $this);
    }


    /**
     *
     */
    public function monitor(
        array $callbacks,
        $updateSec = NULL,
        $updateUsec = NULL
    ) {
        while(TRUE) {
            foreach($this->wait($updateSec, $updateUsec) as $event) {
                if(!isset($callbacks[$event->mask()->raw()])) {
                    continue;
                }

                call_user_func(
                    $callbacks[$event->mask()->raw()],
                    $event,
                    $this
                );
            }
        }   
    }


    /**
     * Performs a lookup for watches either by their wd or by theirs path.
     * Note that wds are unique per inotify instance only. That's why 
     * the method is part of this class rather than part of the watch class.
     *
     * If no watch matches the given criteria NULL is returned.
     *
     * @param integer|string $search If it is an integer it will be
     *                               interpreted as a wd if it is a
     *                               it will be interpreted as a path
     *
     * @return Jm_Os_Inotify_Watch
     */
    public function findWatch($search) {
        if(is_int($search)) {
            if(!isset($this->watches[$search])) {
                return NULL;
            } else {
                return $this->watches[$search];
            }
        } else {
            if(!isset($this->watch_by_path [$search])) {
                return NULL;
            } else {
                return $this->watch_by_path[$search];
            }
        }
    }


    /**
     *
     */
    protected function log($message) {
        if(!is_null($this->log)) {
            $this->log->debug($message);
        }
        return $this;
    }


    /**
     * Returns the descriptor to the underlying inotify queue
     *
     * @return resource(inotify_instance)
     */
    public function fd() {
        return $this->fd;
    }
}
