<?php

interface wx_ticket
{
    public function get($token);
}

function wx($name)
{
    if ( !$name ) {
        throw new Exception('name is empty.');
    }

    if ( !class_exists($name) ) {
        $path = __DIR__.'/'.$name.'.php';

        if ( !file_exists($path) ) {
            throw new Exception('file '.$name.'.php not found');
        }

        require $path;
    }

    static $ticket = null;

    if ( !$ticket ) {
        $ticket = new $name;
    }

    if ( !($ticket instanceof wx_ticket) ) {
        throw new Exception('class '.$name.' not implement wx_ticket.');
    }

    return $ticket;
}
