<?php
include 'Vcode.php';

class Action{
    public function __call($action = '', $params = array())
    {
        $vcode = new Vcode();
        $vcode->start();
    }
}
?>