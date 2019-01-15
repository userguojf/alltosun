<?php
/**
  * alltosun.com 亮屏极光推帮助类 push_helper.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年10月11日 下午3:07:27 $
  * $Id$
  */
use JMessage\IM\Admin;

require MODULE_PATH.'/push/jpush/autoload.php';
use JPush\Client as JPush;

class push_helper
{

    private static  $app_key        = '53808187860f92c2aa0d789d';
    private static  $master_secret  = '33fba8e67a9f65a8e0e9417e';

    private static $push            = null;

    /**
     * 推送消息 注：标签和注册id只取一种
     * @param unknown $params 其他参数
     * @param array $tags 标签
     * @param array $registration_ids 注册id
     * @return boolean|unknown
     */
    public static function push_msg( $params, $registration_ids=array(), $tags=array() )
    {

        if (!is_array($params)) {
            $params =  array('title' => $params);
        }

        if (empty($params['title'])) {
            $params['title'] = '2';
        }

        //if (empty($params['msg'])) {
            //解决移动端接收不到的问题
            //$params['msg'] = 'success photo';
            $params['msg'] = (string)$params['title'];
        //}



        if (empty($params['extras'])) {
            $params['extras'] = array(
                    'time' => time()
            );
        }

        $client = new JPush(self::$app_key,self::$master_secret);

        $push = $client->push();

        //设置平台
        $push = $push->setPlatform('android');

        if ($tags) {
            //推送标签
            $push = $push->addTag($tags);
            //推送全部
            //$push = $push->addAllAudience();

        } else if ($registration_ids) {

            //推送 RegistrationId
            $push = $push->addRegistrationId($registration_ids);
        } else {
            //推送全部
            $push = $push->addAllAudience();

        }

        //推送
        $push = $push->message($params['msg'],[
                'title' => $params['title'] ,
                'content_type' => 'text' ,
                'extras' => $params['extras']
        ]);

        $res = $push->send();

        //验证返回消息
        return self::check_jpush_result($res);

    }

    /**
     * 获取极光推注册id标签
     * @param unknown $registration_id
     */
    public function get_tag($registration_id)
    {
        $client = new JPush(self::$app_key,self::$master_secret);

        // 查询指定设备的别名与标签
        $res = $client->device()->getDevices($registration_id);;

        return self::check_jpush_result($res);

    }

    /**
     * 绑定标签 目前绑定标签只有在亮屏一键开启的时候调用
     * @param unknown $registration_id 设备的极光推注册id
     * @param unknown $tag_ids 标签id列表
     */
    public static function binding_tag($registration_id, $tag_ids)
    {
        //先查出所有此设备待删除的标签，

        $exists_ids = _model('screen_device_tag_res')->getFields('id', array(
                'registration_id' => $registration_id
        ));

        $tag_arr = array();

        if (!is_array($tag_ids) || !$tag_ids) {
            return false;
        }

        foreach ($tag_ids as $v) {

            //查询标签信息
            $tag_info = _model('screen_device_tag')->read($v);
            if (!$tag_info) {
                continue;
            }

            //创建关联
            _model('screen_device_tag_res')->create(array(
                    'registration_id' => $registration_id,
                    'tag_id'          => $v
            ));

            $tag_arr[] = $tag_info['tag'];
        }

        //删除原有标签
        if ($tag_arr && $exists_ids) {
            $del_res = self::remove_tag_by_registration_id($registration_id, $exists_ids);
        }

        if ($tag_arr) {

            //向极光推添加标签
            $client = new JPush(self::$app_key,self::$master_secret);

            $res = $client->device()->addTags($registration_id, $tag_arr);

            return self::check_jpush_result($res);

        }

        return true;
    }

    /**
     * 移除标签
     * @param unknown $registration_id 要移除的注册id
     * @param unknown $tag_res_ids     要移除的标签id
     * @return boolean|unknown|boolean
     */
    public static function remove_tag_by_registration_id($registration_id, $tag_res_ids)
    {
        $tag_arr = array();

        foreach ($tag_res_ids as $v) {

            $tag_info = _uri( 'screen_device_tag_res', $v );

            if (!$tag_info) {
                continue;
            }

            $res = _model('screen_device_tag_res')->delete($v);

            if ($res !== false) {
                $tag = _uri('screen_device_tag', $tag_info['tag_id'], 'tag');

                if (!$tag) {
                    continue;
                }

                $tag_arr[] = $tag;
            }
        }

        if ($tag_arr) {
            $client = new JPush(self::$app_key,self::$master_secret);
            //删除极光推标签
            $res = $client->device()->removeTags($registration_id,  $tag_arr);
            return self::check_jpush_result($res);
        }

        return true;

    }

    /**
     * 解绑标签
     * @param unknown $registration_id 要移除的注册id
     * @param unknown $tag_res_ids     要移除的标签id
     * @return boolean|unknown|boolean
     */
    public static function unbind_tag($registration_id, $tag)
    {

        $client = new JPush(self::$app_key,self::$master_secret);

        if (!is_array($registration_id)) {
            $registration_id = array(
                    0 => $registration_id
            );
        }

        foreach ($registration_id as $v) {

            $tag_info = _uri('screen_device_tag', array('tag' => $tag));

            if (!$tag_info) {
                return false;
            }

            //删除极光推标签
            $res = $client->device()->removeTags($v,  $tag_info['tag']);

            $res_delete = array(
                    'tag_id'                => $tag_info['id'],
                    'registration_id'       => $v
            );

            _model('screen_device_tag_res')->delete($res_delete);
        }

        return true;

    }

    /**
     * 获取并生成省标签
     * @param string $business_hall_id  省id
     */
    public static function get_province_tag($province_id)
    {
        if (!$province_id) {
            return false;
        }

        //标签生成规则是md5(res_name _ res_id)
        $filter = array(
                'res_name'  => 'province',
                'res_id'    => $province_id,
                'tag'       => substr(md5('province_'.$province_id), 8, 16)
        );

        $tag_info = _uri('screen_device_tag', $filter);

        if ($tag_info) {
            return $tag_info['id'];
        }

        $res = _model('screen_device_tag')->create($filter);

        if ($res === false) {
            return false;
        }

        return $res;
    }

    /**
     * 获取并生成城市标签id
     * @param string $city_id  市id
     */
    public static function get_city_tag($city_id)
    {
        if (!$city_id) {
            return false;
        }

        //标签生成规则是md5(res_name _ res_id)
        $filter = array(
                'res_name'  => 'city',
                'res_id'    => $city_id,
                'tag'       => substr(md5('city_'.$city_id), 8, 16)
        );

        $tag_info = _uri('screen_device_tag', $filter);

        if ($tag_info) {
            return $tag_info['id'];
        }

        $res = _model('screen_device_tag')->create($filter);

        if ($res === false) {
            return false;
        }

        return $res;
    }

    /**
     * 获取并生成区县标签id
     * @param array $area_id 区县id
     */
    public static function get_area_tag($area_id)
    {
        if (!$area_id) {
            return false;
        }

        //标签生成规则是md5(res_name _ res_id)
        $filter = array(
                'res_name'  => 'area',
                'res_id'    => $area_id,
                'tag'       => substr(md5('area_'.$area_id), 8, 16)
        );

        $tag_info = _uri('screen_device_tag', $filter);

        if ($tag_info) {
            return $tag_info['id'];
        }

        $res = _model('screen_device_tag')->create($filter);

        if ($res === false) {
            return false;
        }

        return $res;
    }

    /**
     * 获取并生成营业厅标签id
     * @param string $business_hall_id  营业厅id
     */
    public static function get_business_hall_tag($business_hall_id)
    {
        if (!$business_hall_id) {
            return false;
        }

        //标签生成规则是md5(res_name _ res_id)
        $filter = array(
                'res_name'  => 'business_hall',
                'res_id'    => $business_hall_id,
                'tag'       => substr(md5('business_hall_'.$business_hall_id),8,16)
        );

        $tag_info = _uri('screen_device_tag', $filter);

        if ($tag_info) {
            return $tag_info['id'];
        }

        $res = _model('screen_device_tag')->create($filter);

        if ($res === false) {
            return false;
        }

        return $res;

    }

    /**
     * 获取并生成机型标签id
     * @param string $business_hall_id  营业厅id
     * @param array $business_hall_info 营业厅详情 如果调用方已查到营业厅详情，为减少数据库操作，传递营业厅详情即可
     */
    public static function get_phone_name_version_tag($phone_name, $phone_version)
    {
        if (!$phone_name || !$phone_version) {
            return false;
        }

        //标签生成规则是md5(res_name _ res_id)
        $res_id =  $phone_name.'_'.$phone_version;
        $filter = array(
                'res_name'  => 'phone_name_version',
                'res_id'    => $res_id,
                'tag'       => substr(md5('phone_name_version_'.$res_id),8,16)
        );

        $tag_info = _uri('screen_device_tag', $filter);

        if ($tag_info) {
            return $tag_info['id'];
        }

        $res = _model('screen_device_tag')->create($filter);

        if ($res === false) {
            return false;
        }

        return $res;
    }



    /**
     * 验证极光推返回数据
     * @param unknown $res
     */
    public static function check_jpush_result($res)
    {

        if (empty($res['http_code']) || $res['http_code'] != 200) {
            return false;
        }

        if ( !empty($res['body']) ) {
            return $res['body'];
        }

        return true;
    }

    /**
     * 获取标签昵称
     */
    public static function get_tag_nickname($tag_res_name, $tag_res_id)
    {
        //厅
        if ($tag_res_name == 'business_hall') {
            $nickname = business_hall_helper::get_info_name($tag_res_name, $tag_res_id, 'title');
        //区域
        } else if (in_array($tag_res_name, array('city', 'province', 'area'))) {
            $nickname = business_hall_helper::get_info_name($tag_res_name, $tag_res_id, 'name');
        //设备品牌型号
        } else if ($tag_res_name == 'phone_name_version') {
            list($phone_name, $phone_version) = explode('_', $tag_res_id);
            $nickname_info = screen_device_helper::get_device_nickname($phone_name, $phone_version);
            if (!empty($nickname_info['name_nickname']) && !empty($nickname_info['version_nickname'])) {
                $nickname =  $nickname_info['name_nickname'].' '.$nickname_info['version_nickname'];
            } else {
                $nickname =  $phone_name.' '.$phone_version;
            }
        }

        return $nickname;
    }

    /**
     * 获取标签类型
     * @param ‘’
     */
    public static function get_res_name_list()
    {
        $list = array();
        $list = _model('screen_device_tag')->getAll('select distinct res_name,res_title  from screen_device_tag ');

        return $list;
    }


}
?>