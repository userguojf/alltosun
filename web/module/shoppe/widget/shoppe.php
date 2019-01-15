<?php
/**
  * alltosun.com 柜台widget shoppe.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年8月23日 下午4:15:59 $
  * $Id$
  */
class shoppe_widget
{

    public $dm_domain_name = '';
    public $Awifi_domain_name = '';
    public function __construct()
    {
        if (ONDEV) {
            //数字地图演示环境
            $this->dm_domain_name = 'http://market-mng-temp.obaymax.com';

            //数字地图测试环境
            //$this->dm_domain_name = 'http://market-mng-test.obaymax.com';

            //数字地图开发环境
            //$this->dm_domain_name = 'http://market-mng-dev.obaymax.com';

            //Awifi线下地址
            $this->awifi_domain_name = 'http://201512awifi.alltosun.net';
        } else {
            //数字地图正式环境
            $this->dm_domain_name = 'http://dm.pzclub.cn/api';
            //Awifi正式环境
            $this->awifi_domain_name = 'http://wifi.pzclub.cn';
        }
    }

    /**
     * 删除专柜
     * @param unknown $filter
     * 数字地图接口文档 http://market-mng-dev.obaymax.com/swagger-ui.html
     * 数字地图接口 http://xxx/awifi/shoppe/delete
     */
    public function delete_shoppe($filter, $from)
    {

        $shoppe_info = _uri('rfid_shoppe', $filter);

        if (!$shoppe_info) {
            return false;
        }

        $res = _model('rfid_shoppe')->update($filter, array('status' => 0));

        if ($res === false) {
            return $res;
        }

        //通知Awifi
//         if ($from != 1) {

//             $url = $this->awifi_domain_name.'/api/mac/shoppe/delete';

//             $curl_res = curl_post($url, json_encode(array('id' => $shoppe_info['id'])));
//             //记录日志
//             shoppe_helper::write_api_log('wifi_shoppe_delete', $curl_res, json_encode(array('id' => $shoppe_info['id'])));
//         }

        //通知数字地图
        if ($from != 2) {
            $time = time();
            $params = array(
                    'appid'         => shoppe_config::$dm_api_config['appid'],
                    'token'         => shoppe_helper::generate_dm_api_token($time),
                    'timestamp'     => $time,
                    'shoppeId'      => $shoppe_info['id']
            );

            $url = $this->dm_domain_name.'/awifi/shoppe/delete';

            $curl_result = shoppe_helper::dm_curl_post($url, json_encode($params));

            //记录日志
            shoppe_helper::write_api_log('szdt_shoppe_delete', $curl_result, json_encode($params));
        }

        return $res;

    }


    /**
     * 获取专柜列表
     * @param unknown $filter
     */
    public function get_shoppe_list($filter)
    {
        return _model('rfid_shoppe')->getList($filter);
    }

    /**
     * 添加专柜
     * @param unknown $new_data
     * 数字地图接口文档 http://market-mng-dev.obaymax.com/swagger-ui.html
     */
    public function add_shoppe($new_data, $from=1)
    {

        $result = _model('rfid_shoppe')->create($new_data);

        if (!$result) {
            return $result;
        }

        //通知Awifi
//         if ($from != 1) {

//             $url = $this->awifi_domain_name.'/api/mac/shoppe/add';

//             $shoppe_info = _model('rfid_shoppe')->read($result);

//             if ($shoppe_info) {

//                 $curl_res = curl_post($url, json_encode($shoppe_info));
//                 //记录日志
//                 shoppe_helper::write_api_log('wifi_shoppe_add', $curl_res, json_encode($shoppe_info));
//             }
//         }

        //通知数字地图
        if ($from != 2) {
            //通知数字地图添加
            $time = time();

            $user_number = _uri('business_hall', $new_data['business_id'], 'user_number');

            if (!$user_number) {
                return $result;
            }

            $params = array(
                    'appid'         => shoppe_config::$dm_api_config['appid'],
                    'timestamp'     => $time,
                    'token'         => shoppe_helper::generate_dm_api_token($time),
                    'channelCode'   => $user_number,
                    'phoneName'     => $new_data['phone_name'],
                    'shoppeName'    => $new_data['shoppe_name'],
                    'shoppeId'      => $result
            );

            $url = $this->dm_domain_name.'/awifi/shoppe/create';

            $curl_result = shoppe_helper::dm_curl_post($url, json_encode($params));

            shoppe_helper::write_api_log('szdt_shoppe_add', $curl_result, json_encode($params));
        }

        return $result;

    }

    public function update_shoppe($filter, $new_data)
    {
        return _model('rfid_shoppe')->update($filter, $new_data);
    }
}