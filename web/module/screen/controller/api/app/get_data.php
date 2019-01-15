<?php
/**
 * alltosun.com 数字地图获取亮屏数据接口改版 get_data_v1.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-9-2 下午12:19:39 $
 * $Id$
 */

class Action
{

    private $config = array(
            'appid' => 'szdt'
    );

    public function index()
    {
        $app_id           = Request::Post('app_id', '');
        $device_unique_id = Request::Post('device_code', '');

        $date        = Request::Post('date', date('Ymd'));

        if ( $this->config['appid'] != $app_id ) {
            $this->api_return('app_id不正确');
        }

        if ( !$device_unique_id ) {
            $this->api_return('请传设备唯一ID');
        }

        $screen_info = screen_device_helper::get_device_info_by_device($device_unique_id);
        //$screen_info = _model('screen_device')->read(array('device_unique_id' => $device_unique_id));

        if (!$screen_info) {
            $this->api_return('该亮屏APP设备已经不存在');
        }

        //返回信息的数组
        $data = [];

        //返回信息 1手机信息和所属柜台
        $data['shoppe_id']     = $screen_info['shoppe_id'];
        $data['phone_name']    = $screen_info['phone_name'];
        $data['phone_version'] = $screen_info['phone_version_nickname'] ? $screen_info['phone_version_nickname'] : $screen_info['phone_version'];

        //在线时长
        $device_info = _model('screen_device_online_stat_day')->read(array('device_unique_id' => $device_unique_id, 'day' => $date));

        //体验次数
        $screen_data = _model('screen_device_stat_day')->read(array('device_unique_id' => $device_unique_id, 'day' => $date));

        //返回信息 2在线数据
        if ($device_info) {
            $is_online = (strtotime($device_info['update_time']) + 1800) > time() ? true : false;

            //更新在线状态
            if (!$is_online) {
                _model('screen_device_online_stat_day')->update(array('id' => $device_info['id']), array('is_online' => 0));
            }

            $data['is_online']   = $is_online;
            $data['online_time'] = $device_info['online_time'];

        } else {
            $data['is_online']   = false;
            $data['online_time'] = 0;
        }

        //返回信息 3体验数据
        if ($screen_data) {
            $data['action_num']      = $screen_data['action_num'];
            $data['experience_time'] = $screen_data['experience_time'];
        } else {
            $data['action_num']      = 0;
            $data['experience_time'] = 0;
        }

        $this->api_return($data);
    }

    private function api_return( $data )
    {
        $info = array();

        if ( is_array($data) ) {
            $info = array(
                    'status' => 1,
                    'msg'    => 'ok',
                    'data'   => $data
            );
        } else {
            $info = array(
                    'status' => 0,
                    'msg'    => $data,
                    'data'   => array(),
            );
        }

        echo json_encode($info);
        exit(0);
    }

    public function test()
    {
       $device_unique_id      = Request::get('device_code', '');

        if (!$device_unique_id) {
            exit('device_unique_id不能为空');
        }
        require ROOT_PATH.'/helper/AnCurl.php';

        $curl = new AnCurl();

        $data = array(
                'app_id'        => 'szdt',
                'device_code'   => $device_unique_id,
        );

        $res  = $curl -> post(AnUrl('screen/api/app/get_data'), $data);

        echo $res;
//         an_dump(json_decode($res , true));
    }

}