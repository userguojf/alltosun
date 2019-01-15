<?php

class Action
{
    public function index()
    {
        require MODULE_PATH.'/probe/test/stress.php';
    }
}
