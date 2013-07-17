<?php

class Jm_Os_Inotify_EventFilter
{
        
    protected $closure;

    public function __construct(Closure $closure) {
        $this->closure = $closure;
    }

    public function valid(Jm_Os_Inotify_Event $event) {
        return $this->closure->__invoke($event);
    }
}
