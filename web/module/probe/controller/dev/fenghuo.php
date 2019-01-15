<?php

/**
 * 获取烽火设备数据控制器
 *
 * @author  wangl
 */

// load func.php
probe_helper::load('func');

//wangjf add 引入设备接口
probe_helper::load('device', 'interface');

class Action
{
    /**
     * 参数pid
     *
     * @var Int
     */
    private $pid = 10020;

    /**
     * 设备编号
     *
     * @var Array
     */
    private $devs = array(12374, 12375);

    private $path = '';

    public function __construct()
    {
        $this -> path = MODULE_PATH.'/probe/widget/data';
    }

    /**
     * 自定义错误处理
     *
     * @param   Int     错误码
     * @param   String  错误信息
     * @param   String  错误文件
     * @param   Int     错误行号
     * @return  Bool
     */
    public function err($errno, $errstr, $errfile = '', $errline = 0)
    {
        if ( strpos($errstr, 'mysql_connect') !== false ) {
            return true;
        }

        $str  = $errstr;

        if ( $errfile ) {
            $str .= ' in '.$errfile;
        }

        if ( $errline ) {
            $str .= ':'.$errline;
        }

        echo $str;

        probe_helper::write_log('fenghuo', $str);

        if ( $errno == E_ERROR || E_USER_ERROR ) {
            exit(-1);
        } else {
            return true;
        }
    }

    /**
     * 获取烽火设备数据
     *
     * @return  String
     */
    public function index()
    {
        set_time_limit(0);
        set_error_handler(array($this, 'err'));

        $path = __DIR__.'/log';

        if ( file_exists($path) ) {
            unlink($path);
        }

        if ( file_exists($this -> path) ) {
            unlink($this -> path);
        }

        _widget('probe')->fenghuo();
    }

    /**
     * 显示数据
     *
     * @return  String
     */
    public function show_data()
    {
        $empty = Request::Get('empty', 0);

        if ( $empty ) {
            $fp = fopen($this -> path, 'w');
            fclose($fp);
        }

        an_dump(file_get_contents($this -> path));
    }

    public function storage_data()
    {
        if ( !file_exists($this -> path) ) {
            echo 'data文件不存在';
            exit(-1);
        }

        $seek = Request::Get('seek', 0);

        set_time_limit(0);

        set_error_handler(array($this, 'err'));

        $fp = fopen($this -> path, 'r');

        if ( !$fp ) {
            return '打开文件失败';
        }

        if ( $seek ) {
            fseek($fp, $seek);

            $len = $seek;
        } else {
            $len = 0;
        }

        $n = 0;

        while ( ($row = fgets($fp)) !== false ) {
            if ( $n >= 1000 ) {
                break;
            }
            $n ++;

            $len += strlen($row);

            $row = trim($row, "\n");

            $ary = explode('|', $row);

            if ( empty($ary[1]) || empty($ary[2]) ) {
                continue;
            }

            try {
                $storage -> storage_mac(probe_helper::mac_decode($ary[1]), $ary[3], array(
                    'dev'   =>  $ary[4],
                    'time'  =>  $ary[2]
                ));
            } catch ( Exception $e ) {
                an_dump($e -> getMessage());
            }
        }

        if ( feof($fp) ) {
            echo '文件已读完<br />';
        } else {
            $url = SITE_URL.'/probe/dev/fenghuo/storage_data?seek='.$len;

            echo '<script>';
            echo "window.location.href = '{$url}'";
            echo '</script>';
        }
    }

    /**
     *
     *
     *
     *
     */
    public function test()
    {
        $fp = fsockopen('tcp://ds101.navroom.com', 88, $errno, $errstr, 3);

        // 判断是否打开
        if ( !is_resource($fp) ) {
            trigger_error('打开套接字失败', E_USER_ERROR);
        }

        // 请求体
        $content    = 'pid=10020&cmd=c';
        // 请求头
        $header     = "NDTP/1.0 HELLO\r\nContent-Length: ".strlen($content)."\r\n\r\n";
        // 发送请求
        $len        = fwrite($fp, $header.$content);
        $res        = '';

        while ( ($r = fgets($fp)) !== false ) {
            $res .= $r;
        }

        an_dump($res);
    }

    public function repair()
    {
        $id    = Request::Get('id', 0);
        $type  = Request::Get('type', 'hour');
        $b_id  = 46270;
        $db    = get_db($b_id, $type);
        $sql   = "SELECT * FROM `{$db -> table}` WHERE `id` > {$id} ORDER BY `id` ASC LIMIT 1000 ";
        $list  = $db -> getAll($sql);
        $diff  = 8 * 3600;

        $last_id = 0;

        foreach ( $list as $k => $v ) {
            $frist_time = $v['frist_time'] - $diff;
            $up_time    = $v['up_time'] - $diff;
            $date       = (int)date('Ymd', $frist_time);

            $update = array(
                'frist_time'    =>  $frist_time,
                'up_time'       =>  $up_time,
            );

            if ( $v['date'] != $date ) {
                $update['date'] = $date;
            }

            if ( !empty($v['time_line']) ) {
                $time_line = '';
                $ary       = explode(',', $v['time_line']);

                foreach ( $ary as $key => $val ) {
                    $arr        = explode(':', $val);
                    $arr[0]     = $arr[0] - $diff;
                    $time_line .= $arr[0].':'.$arr[1].',';
                }
                $update['time_line'] = trim($time_line, ',');
            }

            $db -> update(array('id' => $v['id']), $update);

            if ( $v['id'] > $last_id ) {
                $last_id = $v['id'];
            }
        }

        $url = '';

        if ( $last_id === 0 ) {
            /*if ( $type == 'hour' ) {
                $url = SITE_URL.'/probe/dev/fenghuo/repair?type=day';
            } else {
            }*/
        } else {
            $url = SITE_URL.'/probe/dev/fenghuo/repair?id='.$last_id.'&type='.$type;
        }

        if ( $url ) {
            echo '<script>';
            echo "window.location.href = '{$url}'";
            echo '</script>';
        } else {
            echo '执行完毕';
        }
    }

    public function repair_date()
    {
        $id    = Request::Get('id', 0);
        $type  = Request::Get('type', 'hour');
        $b_id  = 46270;
        $db    = get_db($b_id, $type);
        $sql   = "SELECT * FROM `{$db -> table}` WHERE `id` > {$id} ORDER BY `id` ASC LIMIT 1000 ";
        $list  = $db -> getAll($sql);
        $diff  = 8 * 3600;

        $last_id = 0;

        foreach ( $list as $k => $v ) {
            $update = array(
                'date'    =>  (int)date('Ymd', $v['frist_time'])
            );

            $db -> update(array('id' => $v['id']), $update);

            if ( $v['id'] > $last_id ) {
                $last_id = $v['id'];
            }
        }

        $url = '';

        if ( $last_id === 0 ) {
            if ( $type == 'hour' ) {
                $url = SITE_URL.'/probe/dev/fenghuo/repair_date?type=day';
            } else {
            }
        } else {
            $url = SITE_URL.'/probe/dev/fenghuo/repair_date?id='.$last_id.'&type='.$type;
        }

        if ( $url ) {
            echo '<script>';
            echo "window.location.href = '{$url}'";
            echo '</script>';
        } else {
            echo '执行完毕';
        }
    }

}