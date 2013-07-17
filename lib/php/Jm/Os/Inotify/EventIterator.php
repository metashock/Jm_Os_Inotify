<?php

/**
 *
 */
class Jm_Os_Inotify_EventIterator extends ArrayIterator
{

    /**
     * @var Jm_Os_Inotify_Instance
     */
    protected $instance;


    /**
     *
     */
    public function __construct(
        array $currents,
        Jm_Os_Inotify_Instance $instance,
        Jm_Os_Inotify_EventFilter $filter = NULL
    ) {
        parent::__construct($currents);
        $this->instance = $instance;
        $this->filter = $filter;
    }


    /**
     * @return boolean
     */
    public function valid(){
        do {
            $current = $this->current();
            if(!$current) {
                return FALSE;
            }

            // if a filter was passed, apply it
            if(is_null($this->filter)
            || $this->filter->valid($current)) {
                return TRUE;    
            }
            
            // try the next item if neceassary
            $this->next();
        } while (TRUE);
    }


    /**
     * @return Jm_Os_Inotify_Event
     */
    public function current() {
        $current = parent::current();
        if(is_null($current)) {
            return NULL;
        }

        $event = new Jm_Os_Inotify_Event($current, $this->instance);
        $mask = $event->mask();

        // IN_CREATE will be triggered if a file or a directory was created within
        // a watched directory.
        if ($mask->contains(IN_CREATE) && $mask->contains(IN_ISDIR)) {
            $watch = new Jm_Os_Inotify_Watch($filename);
        }

        // IN_DELETE will be triggered if a file or a directory will be 
        // deleted within a watched directory
        if($mask->contains(IN_DELETE) && $mask->contains(IN_ISDIR)) {
            Jm_Os_Inotify::byPath($filename)->remove();
        }

        /* if (($mask & IN_DELETE_SELF) === IN_DELETE_SELF) {
        }
        if (($mask & IN_MOVE_SELF) === IN_MOVE_SELF) {
        } */
        return $event;
    }


    /**
     *
     */
    public function offsetGet($index) {
        $arrayEvent = parent::offsetGet($index);
        return new Jm_Os_Inotify_Event($arrayEvent, $this->instance);
    }

}

