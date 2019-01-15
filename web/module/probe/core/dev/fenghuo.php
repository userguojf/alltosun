<?php
/**
 * 烽火设备获取数据类
 *
 * @author  wangl
 */

class fenghuo implements device
{
    /**
     * 烽火pid
     *
     * @var Int
     */
    private $pid = 10020;

    /**
     * 构造函数
     *
     * @return  Obj
     */
    public function __construct()
    {

    }

    public function storage()
    {

    }

    /**
     * 设置烽火pid
     *
     * @param   Int pid
     * @return  Bool
     */
    public function set_pid($pid)
    {
        if ( !is_numeric($pid) ) {
            return false;
        }

        $this -> pid = $pid;

        return true;
    }

    /**
     * 获取烽火历史数据
     *
     * @param   String  设备编号
     * @param   Int     偏移
     * @return  Array
     */
    public function record($dev, $offset)
    {
        if ( !$dev ) {
            return 0;
        }

        // 请求体
        $content = 'pid='.$this -> pid.'&cmd=dh&box='.$dev.'&offset='.$offset;
        // 发请求
        $res     = $this -> request($content);

        if ( !$res ) {
            return 0;
        }

        // 计算位长
        $len = strlen($res);
        // 计算有多少数据量
        $num = (int)($len / 8 / 16);
        // 最终返回数据
        $data= array();

        $path = MODULE_PATH.'/probe/widget/data';

        $fp   = fopen($path, 'a+');

        $max_id = 0;

        for ( $i = 0; $i < $num; $i ++ ) {
            $str = substr($res, $i * (16 * 8), 16 * 8);

            if ( !$str ) {
                continue;
            }

            $ary = unpack('lid/H12mac/ltime/srssi', $str);

            // 矫正时区
            $ary['time'] -= 8 * 3600;

            $str = "{$ary['id']}|{$ary['mac']}|{$ary['time']}|{$ary['rssi']}|{$dev}\n";

            fwrite($fp, $str);
            // $data[$i] = unpack('lid/H12mac/ltime/srssi', $str);

            if ( $ary['id'] > $max_id ) {
                $max_id = $ary['id'];
            }
        }

        fclose($fp);

        return $max_id;
    }

    /**
     * 发起请求
     *
     * @param   String  请求内容
     * @return  String  返回内容
     */
    private function request($content)
    {
        $debug = Request::Get('debug', 0);

        $fp = fsockopen('tcp://ds101.navroom.com', 88, $errno, $errstr, 3);

        // 判断是否打开
        if ( !is_resource($fp) ) {
            trigger_error('打开套接字失败', E_USER_ERROR);
        }

        // 请求头
        $header     = "NDTP/1.0 HELLO\r\nContent-Length: ".strlen($content)."\r\n\r\n";
        // 发送请求
        $len        = fwrite($fp, $header.$content);

        if ( $len < 1 ) {
            trigger_error('没有返回内容', E_USER_ERROR);
        }

        $row = fgets($fp);
        $num = sscanf($row, "NDTP/1.0 %d %s", $code, $status);

        if ( $num != 2 || $code != 200 || $status != 'OK' ) {
            trigger_error('返回：'.$row, E_USER_ERROR);
        }

        $is_eof = false;
        while ( true ) {
            $row = fgets($fp);

            if ( $row === false ) {
                $is_eof = true; break;
            }

            if ( $row == "\r\n" ) {
                break;
            }
        }

        if ( $is_eof ) {
            trigger_error('没有响应体', E_USER_ERROR);
        }

        $data = '';
        while ( ($row = fgets($fp)) !== false ) {
            $data .= $row;
        }

        return $data;
    }
}