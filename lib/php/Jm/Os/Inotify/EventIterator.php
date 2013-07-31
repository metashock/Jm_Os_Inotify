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
            $current = parent::current();
            if(!$current) {
                return FALSE;
            }

            // if a filter was passed, apply it
            if(is_null($this->filter)
            || $this->filter->valid(new Jm_Os_Inotify_Event(
                $current, $this->instance
            ))) {
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
        $event = new Jm_Os_Inotify_Event($current, $this->instance);
        $mask = $event->mask();

        if(!$mask->contains(IN_ISDIR)) {
            return $event;
        }

        switch(TRUE) {
            case $mask->contains(IN_CREATE):
            case $mask->contains(IN_MOVED_FROM):
                $parent = $this->instance->findWatch($event->wd());
                $watch = $this->instance->watch(
                    $event->fullpath(), $parent->options()
                );
                break;

            case $mask->contains(IN_DELETE):
            case $mask->contains(IN_MOVED_TO):
                $watch = $this->instance->findWatch($event->fullpath());
                $this->instance->unwatch($watch);
                break;
        }
            
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

