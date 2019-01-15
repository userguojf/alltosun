<?php

class Action{

    public function index()
    {

        $filename= "http://WEB01/swoole/meis_".date('Ymd').".log"; //文件名
        $date=date("Ymd-H:i:m");
        Header( "Content-type:  application/octet-stream ");
        Header( "Accept-Ranges:  bytes ");
        Header( "Accept-Length: " .filesize($filename));
        header( "Content-Disposition:  attachment;  filename= {$date}.log");
        echo file_get_contents($filename);
        readfile($filename);


    }
}