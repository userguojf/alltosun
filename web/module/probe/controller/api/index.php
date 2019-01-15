<?php
/**
 * alltosun.com 探针接口 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2017-4-7 上午11:08:23 $
*/

// load trait
probe_helper::load('stat', 'trait');

class Action
{
    use stat;

    /**
     * config
     *
     * @var Array
     */
    private $config = array(
        'appid'    =>  'szdt',
    );

    /**
     * 数字地图接口
     *
     * @return  String
     */
    public function index()
    {
        // 参数
        $app_id = Request::Post('app_id', '');
        $dev    = Request::Post('dev', '');
        $date   = Request::Post('date', date('Y-m-d'));

        //转换小写
        $dev    = strtolower($dev);

        if ( !$app_id ) {
            $this->api_return('没有app_id');
        }

        if ( $this->config['appid'] != $app_id ) {
            $this->api_return('app_id不正确');
        }

        if ( !$dev ) {
            $this->api_return('没有设备编号');
        }

        if ( !$date ) {
            $this -> api_return('没有时间');
        }

        try {

            $data = $this -> api_szdt($app_id, $dev, $date);

            $this -> api_return($data);
        } catch(Exception $e) {
            $this -> api_return($e -> getMessage());
        }
    }

    /**
     * rfid数据接口
     *
     * @return  String
     */
    public function rfid()
    {
        // 参数
        $start = Request::Post('start_time', '');
        $end   = Request::Post('end_time', '');
        $b_id  = Request::Post('b_id', 0);
        $dev   = Request::Post('dev', '');

        // 请求参数
        $request = 'request: '.json_encode($_POST);

        if ( !$b_id ) {
            echo json_encode(array('info' => '没有营业厅ID'));
            exit(-1);
        }

        if ( !$end ) {
            echo json_encode(array('info' => '没有end_time'));
            exit(-1);
        }

        if ( !is_numeric($end) ) {
            $end = strtotime($end);
        }

        $dev = strtolower($dev);

        try {
            $data = $this -> api_rfid($b_id, $dev, $start, $end);

            echo json_encode(array('info' => 'ok', 'data' => $data));
        } catch (Exception $e) {
            echo json_encode(array('info' => $e -> getMessage()));
        }

        exit(-1);
    }

    /**
     * 测试接口
     *
     * @return  String
     */
    public function rfid_test()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, 'http://201512awifiprobe.alltosun.net/probe/api/rfid');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('dev' => '16120803', 'b_id' => 46120, 'start_time' => time(), 'end_time' => time()));

        $r = curl_exec($ch);
an_dump($r);
        curl_close($ch);
    }

    /**
     * 接口返回
     *
     * @param   String|Array    返回数据
     */
    private function api_return($data)
    {
        if ( is_array($data) ) {
            $array = array(
                'status'    =>  1,
                'msg'       =>  'ok',
                'data'      =>  $data
            );
        } else {
            $array = array(
                'status'   =>  0,
                'msg'      =>  $data,
                'data'     =>  array(),
            );
        }
        echo json_encode($array);
        exit(0);
    }

    public function szdt_test()
    {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, SITE_URL.'/probe/api');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, array('dev' => '16120803', 'app_id' => 'szdt', 'date' => date('Y-m-d')));

        $r = curl_exec($ch);
an_dump($r);
        curl_close($ch);
    }
}