<?php
/**
 * alltosun.com  get_label_info.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-5-11 下午6:49:57 $
 * $Id$
 */
class Action
{
    private $config = array(
        'appid' => 'szdt'
    );

    public function index()
    {
        $app_id      = Request::Post('app_id' , '');
        $user_number = Request::Post('user_number' , '');

        if ( $this->config['appid'] != $app_id ) {
            $this->api_return('app_id不正确');
        }

        if ( !$user_number ) {
            $this->api_return('渠道码不存在');
        }

        $business_hall_info = _model('business_hall')->read(array('user_number' => $user_number));

        if (!$business_hall_info) {
            $this->api_return('渠道码不正确');
        }

        $label_ids_info = _model('rfid_label')->getFields('label_id' , array('business_hall_id' => $business_hall_info['id']));

        if (!$label_ids_info) {
            $str = $business_hall_info['title'].'标签ID不存在';
            $this->api_return($str);
        }

        $this->api_return($label_ids_info);
    }

    private function api_return( $data )
    {
        $info = array();

        if ( is_array($data) ) {
            $info = array(
                    'status'    =>  1,
                    'msg'       =>  'ok',
                    'data'      =>  $data
            );
        } else {
            $info = array(
                    'status'   =>  0,
                    'msg'      =>  $data,
                    'data'     =>  array(),
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
                'app_id'      => 'szdt',
                'user_number' => '1101111001252'
        );

        $res  = $curl -> post(AnUrl('rfid/api/get_label_info'), $data);

        an_dump($res);
        an_dump(json_decode($res , true));
    }
}