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

class Action
{


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
     */
    public function index()
    {
        $dev    = tools_helper::Get('dev', 'ot5a4dbf59e55b4');
        $date   = tools_helper::Get('date', date('Y-m-d H:i:s'));
        $app_id = $this->config['appid'];


        $params = array(
                'app_id' => $app_id,
                'dev'    => $dev,
                'date'   => $date
        );
        $url = 'http://mac.pzclub.cn/probe/api';
        $res = curl_post($url, $params);
        //p($params);
        p($res);
    }

    /**
     * RFID 接口
     */
    public function rfid()
    {
        $dev        = tools_helper::Get('dev', '16120803');
        $b_id       = tools_helper::Get('b_id', '46120');
        $start_time = tools_helper::Get('start_time', '');
        $end_time   = tools_helper::Get('end_time', '');

        $url = SITE_URL.'/probe/api/rfid';
        $params = array(
                'dev' => $dev,
                'b_id' => $b_id,
                'start_time' => $start_time,
                'end_time' => $end_time
        );
        p($params);
        $res = curl_post($url, $params);

        p($res);exit;
    }
}