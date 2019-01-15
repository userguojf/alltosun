#!/usr/bin/env php
<?php
class Action
{
    public function __call($action = '', $param = array())
    {
        if (PHP_SAPI !== 'cli') {
            echo 'Warning: Vbot should be invoked via the CLI version of PHP, not the '.PHP_SAPI.' SAPI'.PHP_EOL;
        }

        require  MODULE_PATH.'/wxbot/controller/src/bootstrap.php';

        $command = new \Hanson\Vbot\Commands\Command();

        $command->run();
    }
}