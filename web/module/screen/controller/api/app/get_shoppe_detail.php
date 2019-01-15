<?php
/**
 * alltosun.com 通过柜台ID获取该柜台下的所有设备详情 get_shoppe_detail.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-9-4 上午11:26:20 $
 * $Id$
 */
class Action
{

    private $config = array(
            'appid' => 'szdt'
    );

    public function index()
    {
        $app_id    = Request::Post('app_id', '');
        $shoppe_id = Request::Post('shoppe_id', 0);

        $date      = Request::Post('date', date('Ymd'));

        if ( $this->config['appid'] != $app_id ) {
            $this->api_return('app_id不正确');
        }

        if ( !$shoppe_id ) {
            $this->api_return('请传柜台ID');
        }

        $screen_list = _model('screen_device')->getList(array('shoppe_id' => $shoppe_id, 'status' => 1));

        if (!$screen_list) {
            $this->api_return('未找到该柜台下设备信息');
        }

        $data = [];

        foreach ($screen_list as $k => $v) {
            $detail_data = screen_helper::get_screen_app_detail($v, $date);
            array_push($data, $detail_data);
        }

        $this->api_return($data);
    }

    private function api_return( $data )
    {
        $info = array();

        if ( is_array($data) ) {
            $info = array(
                    'errcode'  => 0,
                    'errormsg' => 'ok',
                    'data'     => $data
            );
        } else {
            $info = array(
                    'errcode'  => 1,
                    'errormsg' => $data,
                    'data'     => array(),
            );
        }

        echo json_encode($info);
        exit(0);
    }

    public function test()
    {
        $shoppe_id      = Request::get('shoppe_id', '');

        if (!$shoppe_id) {
            exit('shoppe_id不能为空');
        }
        require ROOT_PATH.'/helper/AnCurl.php';

        $curl = new AnCurl();

        $data = array(
                'app_id'        => 'szdt',
                'shoppe_id'   => $shoppe_id,
        );

        $res  = $curl -> post(AnUrl('screen/api/app/get_shoppe_detail'), $data);

        echo $res;
    }

}