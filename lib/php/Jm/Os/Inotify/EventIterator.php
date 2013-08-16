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
        array $events,
        Jm_Os_Inotify_Instance $instance
    ) {
        parent::__construct($events);
        $this->instance = $instance;
    }



    /**
     * @return Jm_Os_Inotify_Event
     */
    public function current() {
        $current = parent::current();
       
        $watch = $this->instance->findWatch($current['wd']);

        if($current['mask'] & IN_ISDIR) {
            $path = $watch->path();
        } else {
            $path = $watch->path() . '/' . $current['name'];
        }

        $event = new Jm_Os_Inotify_Event(
            $current['wd'],
            $current['mask'],
            $current['cookie'],
            $path,
            $this->instance
        );
        $mask = $event->mask();

        // @TODO think about checking for IN_X_RECURSIVE_FOLLOW
        // here or drop the flag completely
        if($watch && !($watch->options() & Jm_Os_Inotify::IN_X_RECURSIVE)) {
            return $event;
        }

        // if it is a file no further action must be done
        // @TODO hasn't the watch not to be removed if the file was deleted
        // or moved out of scope?
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
                $this->instance->unwatch($watch);
                break;
        }
            
        return $event;
    }


    /**
     * This method throws an exception, that's all. This is because
     * the method current creates or removes watches. If offsetGet could
     * be called as well it would be unclear which method should do 
     * the watch removal / creational work. Also it must be safe that
     * no action will take place twice. Too much problems for the moment.
     * That's why "disabled"
     *
     * @return void Pigs can fly...
     * 
     * @throws Exception
     */
    public function offsetGet($index) {
        throw new Exception('Not implemented');
    }
}
