<?php

class Server
{
    private $serv;

    public function __construct($config)
    {

        if (!is_object($this->serv)) {
            $this->serv = new swoole_server(HOST, PORT);
        }

        $this->serv->set($config);

        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->serv->on('ManagerStart', array($this, 'onManagerStart'));
        $this->serv->on('Connect', array($this, 'onConnect'));
        $this->serv->on('Receive', array($this, 'onReceive'));
        $this->serv->on('Close', array($this, 'onClose'));
        $this->serv->start();
    }

    public function onStart($serv)
    {
        $this->print_log("Start");
        cli_set_process_title('rfid-server');
        swoole_set_process_name("rfid-server running tcp://0.0.0.0:8080 master:" . $serv->master_pid);
    }

    public function onManagerStart($serv)
    {
        $this->print_log("ManagerStart");
        swoole_set_process_name("rfid-server running tcp://0.0.0.0:8080 manager:" . $serv->manager_pid);
    }

    public function onWorkerStart($serv, $worker_id)
    {
        $this->print_log("WorkerStart");
        swoole_set_process_name("rfid-server running tcp://0.0.0.0:8080 worker:" . $serv->worker_pid);
    }

    public function onConnect($serv, $fd, $from_id)
    {
        $this->print_log("Client {$fd} connect");
    }

    public function onReceive(swoole_server $serv, $fd, $from_id, $data)
    {


        $trans = array("\r\n" => "\n");

        $data = iconv('ISO-8859-1', 'UTF-8', $data);
        $data = trim(strtr($data, $trans));

        file_put_contents('/data/log/swoole/common2_' . date('Ymd') . '.log', "\n" . $data . "\n" . date('Y-m-d H:i:s') . "-------------\n", FILE_APPEND);

        if ($data != 'SCAN...' && $data != 'SCAN OVER') {
            route::get_instance()->parse(trim($data), $serv, $fd);
        }

        //新规范
        if (strpos($data, 'action_id') !== false) {
            file_put_contents('/data/log/swoole/common_' . date('Ymd') . '.log', "-----------------\n{$data}\n\n" . date('Y-m-d H:i:s') . "\n-------------\n", FILE_APPEND);
        }

        if (!strpos($data, 'DURATION') && strpos($data, 'SN')) {
            file_put_contents('/data/log/swoole/meis_' . date('Ymd') . '.log', $data, FILE_APPEND);
        }

        $this->print_log("--------------------\n" . $data . "\n-------------------");

//             $data_arr = explode("\n", $data);
//             $serv->send($fd, $data_arr[0]."\n);

//             HandleData::get_instance()->data_save(trim($data));
    }

    public function onClose($serv, $fd, $from_id)
    {
        $this->print_log("Client {$fd} close connection");
    }

    public function print_log($msg)
    {
        echo $msg . ' ' . date('Y-m-d H:i:s') . "\n";
    }
}