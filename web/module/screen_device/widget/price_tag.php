<?php
/**
  * alltosun.com 电子价签widget price_tag.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年2月8日 下午12:45:18 $
  * $Id$
  */
class price_tag_widget
{
    private $api_url;

    public function __construct()
    {
        if (ONDEV) {
            $this->api_url = 'http://test.360sides.com/smart';
        } else {
            $this->api_url = 'http://smartxcx.360sides.com';
        }
    }
    /**
     * 添加电子价签品牌型号
     * @param unknown $device_info
     */
    public function add_brand($device_info)
    {

        if (!$device_info) {
            return false;
        }

        //查询视图编码
        $user_number = business_hall_helper::get_business_hall_info($device_info['business_id'], 'user_number');

        if (!$user_number) {
            return false;
        }

        //查询设备昵称
        $device_nickname_info = screen_device_helper::get_device_nickname_info(array('phone_name' => $device_info['phone_name'], 'phone_version' => $device_info['phone_version']));

        if (!$device_nickname_info) {
            return false;
        }
        $params = array(
                'univiewcode' => $user_number,
                'name'        => $device_nickname_info['phone_name'],
                'model'       => $device_nickname_info['phone_version'],
                'nickname'    => $device_nickname_info['name_nickname'],
                'modelNickname'    => $device_nickname_info['version_nickname'],
        );

        $res = curl_post($this->api_url.'/productModel/addBrand', $params);

        $this->record_api_log('priceTagAddBrand', json_encode($params), $res, $device_info['device_unique_id']);

        return $res;

    }

    /**
     * 更新电子价签品牌型号
     * @param unknown $device_info
     */
    public function update_brand($device_info)
    {

        if (!$device_info) {
            return false;
        }

        //查询视图编码
        $user_number = business_hall_helper::get_business_hall_info($device_info['business_id'], 'user_number');

        if (!$user_number) {
            return false;
        }

        //查询设备昵称
        $device_nickname_info = screen_device_helper::get_device_nickname_info(array('phone_name' => $device_info['phone_name'], 'phone_version' => $device_info['phone_version']));

        if (!$device_nickname_info) {
            return false;
        }
        $params = array(
                'univiewcode' => $user_number,
                'name'        => $device_nickname_info['phone_name'],
                'model'       => $device_nickname_info['phone_version'],
                'nickname'    => $device_nickname_info['name_nickname'],
                'modelNickname'    => $device_nickname_info['version_nickname'],
        );

        $res = curl_post($this->api_url.'/productModel/updateBrand', $params);

        $this->record_api_log('priceTagUpdateBrand', json_encode($params), $res, $device_info['device_unique_id']);

        return $res;
    }

    /**
     * 删除电子价签品牌型号
     */
    public function delete_Brand($device_info)
    {
        if (!$device_info) {
            return false;
        }

        //查询视图编码
        $user_number = business_hall_helper::get_business_hall_info($device_info['business_id'], 'user_number');

        if (!$user_number) {
            return false;
        }

        $params = array(
                'univiewcode' => $user_number,
                'name'        => $device_info['phone_name'],
                'model'       => $device_info['phone_version'],
        );

        $res = curl_post($this->api_url.'/productModel/delBrand', $params);

        $this->record_api_log('priceTagDeleteBrand', json_encode($params), $res, $device_info['device_unique_id']);

        return $res;
    }

    /**
     * 记录接口日志
     */
    private function record_api_log($res_name , $request, $response, $device_unique_id)
    {
        $response_arr       = json_decode($response, true);
        $response_code      = empty($response_arr['status']['code']) ? 0 : $response_arr['status']['code'];

        $new_log = array(
                'res_name'          => $res_name,
                'response_code'     => $response_code,
                'request_body'      => $request,
                'response_body'     => $response,
                'device_unique_id'  => $device_unique_id
        );

        _model('screen_api_log')->create($new_log);

        return true;
    }
}