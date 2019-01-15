<?php

class Action
{
    public function index()
    {
        $data = Request::Post('data', '');
        
        $fp = fopen('./data.txt', 'a');
        fwrite($fp, $data."\n".date('YmdH:i:s'));
        fclose($fp);

            $url = "http://wifi.pzclub.cn/probe/dev/lierda";

            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, array('data' => $data));
            curl_setopt($ch, CURLOPT_TIMEOUT, 5);
            curl_exec($ch);
            curl_close($ch);

        exit(json_encode(array('info' => 'ok')));
    }
}