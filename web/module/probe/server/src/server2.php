<?php

class Server
{   
    private $serv;

    public function __construct($config) {
        
        if (!is_object($this->serv)) {
            $this->serv = new swoole_server(HOST, 8085, SWOOLE_PROCESS, SWOOLE_SOCK_UDP);
        }

        $this->serv->set($config);

        $this->serv->on('Start', array($this, 'onStart'));
        $this->serv->on('WorkerStart', array($this, 'onWorkerStart'));
        $this->serv->on('ManagerStart', array($this, 'onManagerStart'));
        $this->serv->on('Packet', array($this, 'onPacket'));

        $this->serv->start();
    }

    public function onStart( $serv ) {
        cli_set_process_title('probe-server');
        swoole_set_process_name(" probe-server running tcp://0.0.0.0:8081 master:" . $serv->master_pid);
    }

    public function onManagerStart( $serv , $manager_id) {
        swoole_set_process_name(" probe-server running tcp://0.0.0.0:8081 manager:" . $serv->manager_pid);
    }

    public function onWorkerStart( $serv , $worker_id) {
        swoole_set_process_name(" probe-server running tcp://0.0.0.0:8081 worker:" . $serv->worker_pid);
    }

    public function onPacket( swoole_server $serv, $data ,$clientinfo) {

            echo "-----\n".mb_detect_encoding($data)."\n-----\n";
            print_r(json_decode($data,true));

            echo $data.' '.date('Y-m-d H:i:s');

            $url = "http://wifi.pzclub.cn/probe/dev/zhongke";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);
    }
}