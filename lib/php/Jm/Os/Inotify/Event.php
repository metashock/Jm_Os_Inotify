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
        array $eventArray,
        Jm_Os_Inotify_Instance $inotifyInstance
    ){
        if(!isset($eventArray['wd'])) {
            throw new InvalidArgumentException(
                '$eventArray was expected to be an assoc array with the ' .
                'keys: wd, mask, cookie, name. Missing the key: wd'
            );
        }

        if(!isset($eventArray['mask'])) {
            throw new InvalidArgumentException(
                '$eventArray was expected to be an assoc array with the ' .
                'keys: wd, mask, cookie, name. Missing the key: mask'
            );
        }

        if(!isset($eventArray['cookie'])) {
            throw new InvalidArgumentException(
                '$eventArray was expected to be an assoc array with the ' .
                'keys: wd, mask, cookie, name. Missing the key: cookie'
            );
        }

        if(!isset($eventArray['name'])) {
            throw new InvalidArgumentException(
                '$eventArray was expected to be an assoc array with the ' .
                'keys: wd, mask, cookie, name. Missing the key: name'
            );
        }

        $this->wd = $eventArray['wd'];
        $this->mask = new Jm_Os_Inotify_EventMask($eventArray['mask']);
        $this->cookie = $eventArray['cookie'];
        $this->name = $eventArray['name'];
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


    public function name() {
        return $this->name;
    }


    /**
     * @TODO refactor this. The fullpath should be generated once the event is created.
     * Currently I think this class should be only a simple data containter w/o business logic
     *
     * @throws Jm_Os_Inotify_Exception
     */
    public function fullpath() {
        $watch = $this->inotifyInstance->findWatch($this->wd());
        if(is_null($watch)) {
            throw new Jm_Os_Inotify_Exception('A watch with descriptor: ' . $this->wd()
                . ' was not found.');
        }
        return $watch->path() . '/' . $this->name();
    }
}

