<?php

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
     * @see http://man7.org/linux/man-pages/man7/inotify.7.html#cookie
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
     *
     */
    protected $inotifyInstance;


    /**
     * @return Jm_Os_Inotify_Event
     */
    public function __construct(
        $wd, $mask, $cookie, $path,
        Jm_Os_Inotify_Instance $inotifyInstance
    ){
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


    public function wd() {
        return $this->wd;
    }


    public function mask() {
        return $this->mask;
    }


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

