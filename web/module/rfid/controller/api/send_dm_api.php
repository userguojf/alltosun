<?php
/**
 * alltosun.com  send_dm_api.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-5-15 下午2:47:28 $
 * $Id$
 */
class Action
{
    public function index()
    {
        $id = Request::get('id' , 0);

        $param = array();

        if (!$id) {
            return array('code' => 400 , 'info' => '由于网络原因，请刷新页面');
        }

        $rfid_info = _model('rfid_label')->read(array('id' => $id));

        if (!$rfid_info) {
            return array('code' => 400 , 'info' => '数据已经不存在，可能被其他操作删除');
        }

        $user_number = _uri('business_hall' , array('id' => $rfid_info['business_hall_id']) , 'user_number');

        if (!$user_number) {
            return array('code' => 400 , 'info' => '营业厅渠道码未找到');
        }

        $param = array(
                'user_number' => $user_number,
                'label_id'    => $rfid_info['label_id']
        );

        $response_info = rfid_helper::send_dm_data($param);

        if (200 == $response_info['code']) {
            _model('rfid_label')->update($id , array('response_code' => 200 , 'response_body' => $response_info['body']));

            return array('code' => 200);
        }

        if (400 == $response_info['code']) {
            return array('code' => 200 , 'info' => '请求数字地图接口失败');
        }
    }
}