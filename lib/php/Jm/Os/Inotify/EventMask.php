<?php

class Jm_Os_Inotify_EventMask
{

    protected $integer;


    public function __construct($integer) {
        $this->integer = $integer;
    }


    public function raw() {
        return $this->integer;
    }


    public function contains($flag) {
        return ($this->integer & $flag) === $flag;
    }


    /**
     * Returns the string representation of the event mask
     *
     * @reutrn string
     */
    public function __toString() {
        $flags = array();
        
        if(($this->integer & IN_ACCESS) === IN_ACCESS){
            $flags []= 'IN_ACCESS';
        }   

        if(($this->integer & IN_MODIFY) === IN_MODIFY){
            $flags []= 'IN_MODIFY';
        }   

        if(($this->integer & IN_ATTRIB) === IN_ATTRIB){
            $flags []= 'IN_ATTRIB';
        }   

        if(($this->integer & IN_CLOSE_WRITE) === IN_CLOSE_WRITE){
            $flags []= 'IN_CLOSE_WRITE';
        }   

        if(($this->integer & IN_CLOSE_NOWRITE) === IN_CLOSE_NOWRITE){
            $flags []= 'IN_CLOSE_NOWRITE';
        }   

        if(($this->integer & IN_OPEN) === IN_OPEN){
            $flags []= 'IN_OPEN';
        }   

        if(($this->integer & IN_MOVED_TO) === IN_MOVED_TO){
            $flags []= 'IN_MOVED_TO';
        }   

        if(($this->integer & IN_MOVED_FROM) === IN_MOVED_FROM){
            $flags []= 'IN_MOVED_FROM';
        }   

        if(($this->integer & IN_CREATE) === IN_CREATE){
            $flags []= 'IN_CREATE';
        }   

        if(($this->integer & IN_DELETE) === IN_DELETE){
            $flags []= 'IN_DELETE';
        }   

        if(($this->integer & IN_DELETE_SELF) === IN_DELETE_SELF){
            $flags []= 'IN_DELETE_SELF';
        }   

        if(($this->integer & IN_MOVE_SELF) === IN_MOVE_SELF){
            $flags []= 'IN_MOVE_SELF';
        }

        if(($this->integer & IN_UNMOUNT) === IN_UNMOUNT){
            $flags []= 'IN_UNMOUNT';
        }   

        if(($this->integer & IN_Q_OVERFLOW) === IN_Q_OVERFLOW){
            $flags []= 'IN_Q_OVERFLOW';
        }   

        if(($this->integer & IN_IGNORED) === IN_IGNORED){
            $flags []= 'IN_IGNORED';
        }   

        if(($this->integer & IN_ISDIR) === IN_ISDIR){
            $flags []= 'IN_ISDIR';
        } 

        return implode(' | ', $flags); 
    }
}

