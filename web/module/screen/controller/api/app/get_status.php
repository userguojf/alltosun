<?php
/**
 * alltosun.com  get_status.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-6-30 下午3:14:15 $
 * $Id$
 */

class Action
{
    private $config = array(
            'appid' => 'szdt'
    );

    public function index()
    {
        $app_id      = Request::Post('app_id', '');
        $user_number = Request::Post('business_code', '');

        $date        = Request::Post('date', date('Ymd'));

        if ( $this->config['appid'] != $app_id ) {
            $this->api_return('app_id不正确');
        }

        if ( !$user_number ) {
            $this->api_return('请传设备渠道码');
        }

        $yyt_info = _model('business_hall')->read(array('user_number' => $user_number));

        if (!$yyt_info) {
            $this->api_return('该渠道码不存在');
        }

        $device_info = _model('screen_device')->getList(array('business_id' => $yyt_info['id'], 'status' => 1));

        if (!$device_info) {
            $this->api_return('该营业厅并没有设备安装亮屏APP');
        }

        $data = [];
        foreach ($device_info as $k => $v) {
            $device_info = _model('screen_device_online_stat_day')->read(array('device_unique_id' => $v['device_unique_id'], 'day' => $date));

            if (!$device_info) {
                $data[$k]['device_code'] = $v['device_unique_id'];
                $data[$k]['is_online']   = false;
            } else {
                $is_online = (strtotime($device_info['update_time']) + 1800) > time() ? true : false;

                $data[$k]['device_code'] = $v['device_unique_id'];
                $data[$k]['is_online']   = $is_online;
            }
        }

        $this->api_return($data);
    }

    private function api_return( $data )
    {
        $info = array();

        if ( is_array($data) ) {
            $info = array(
                    'status' =>  1,
                    'msg'    =>  'ok',
                    'data'   =>  $data
            );
        } else {
            $info = array(
                    'status' =>  0,
                    'msg'    =>  $data,
                    'data'   =>  array(),
            );
        }

        echo json_encode($info);
        exit(0);
    }

    public function test()
    {
        require ROOT_PATH.'/helper/AnCurl.php';

        $curl = new AnCurl();

        $data = array(
                'app_id'        => 'szdt',
                'business_code' => '1101081002052',
        );

        $res  = $curl -> post(AnUrl('screen/api/app/get_status'), $data);

        echo $res;
    }
}