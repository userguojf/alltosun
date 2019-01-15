<?php
/**
 * alltosun.com  meal_put.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2018年1月16日 下午4:41:42 $
 * $Id$
 */

class meal_put_widget
{

    private $member_id          = 0;
    private $business_hall_ids  = array();
    private $area_id            = 0;
    private $city_id            = 0;
    private $province_id        = 0;
    private $content_id         = 0;
    private $phone_name         = '';
    private $phone_version      = '';
    private $ranks              = false;
    private $member_info        = array();
    private $per_page           = 10;

    /**
     * 发布内容
     */
    public function put_content()
    {
        //初始化（接收参数）
        $this->put_content_init();

        //内容验证
        $check = $this->put_content_check();
        if ($check != 'ok') {
            return $check;
        }

        $init_filter = array();

        $result = 0;

        //机型投放
        if ($this->phone_name && $this->phone_version) {
            $init_filter['phone_name']       = $this->phone_name;
            $init_filter['phone_version']    = $this->phone_version;

            //品牌投放
        } else if ($this->phone_name && !$this->phone_version) {
            $init_filter['phone_name']       = $this->phone_name;
        }

        $init_filter['content_id']       = $this->content_id;
        $init_filter['ranks']            =  $this->ranks;

        //营业厅投放
        if ($this->business_hall_ids && !in_array(0,$this->business_hall_ids)) {
            //发布成功返回各厅发布后的res_id数组, 否则返回字符串
            $result = $this->business_hall_put($init_filter);
            if (is_string($result)) {
                return $result;
            }
        //区投放
        } else if ($this->business_hall_ids && in_array(0,$this->business_hall_ids) && $this->area_id) {
            $result = $this->area_put($init_filter);
            if (!$result) {
                return '请求失败';
            }
        //市投放
        } else if (!$this->area_id && $this->city_id) {
            $result = $this->city_put($init_filter);
            if (!$result) {
                return '请求失败';
            }
        //省投放
        } else if (!$this->city_id && $this->province_id) {
            $result = $this->province_put($init_filter);
            if (!$result) {
                return '请求失败';
            }

        //全国投放
        } else if (!$this->province_id ) {
            $result = $this->group_put($init_filter);
            if (!$result) {
                return '请求失败';
            }
        }
        //////////////////////////////////////////////// 推送 START ////////////////////////////////////////////////
        $registration_ids = array();
        //推送
       /* if (is_array($result)) {
            foreach ($result as $v) {
             //推送
             $this->push_by_content_res(_uri('screen_meal_res', $v), '3');
            }
        } else {
            $this->push_by_content_res(_uri('screen_meal_res', $result), '3');
        }*/

        //////////////////////////////////////////////// 推送 END ////////////////////////////////////////////////
        return 'ok';
    }

    /**
     * 发布内容初始化
     */
    private function put_content_init()
    {
        $this->member_id         = member_helper::get_member_id();
        $this->business_hall_ids = tools_helper::post('business_hall_id', array());
        $this->area_id           = tools_helper::post('area_id', -1);
        $this->city_id           = tools_helper::post('city_id', -1);
        $this->province_id       = tools_helper::post('province_id', -1);
        $this->content_id        = tools_helper::post('content_id', 0);
        $this->phone_name        = tools_helper::post('phone_name', '');
        $this->phone_version     = tools_helper::post('phone_version', '');
    }

    /**
     * 发布内容验证
     */
    private function put_content_check()
    {
        if (!$this->member_id) {
            return '请登录！';
        }

        $this->member_info    = member_helper::get_member_info($this->member_id);

        if (!$this->member_info) {
            return '用户不存在';
        }

        $this->ranks          = $this->member_info['ranks'];

        if (empty($this->business_hall_ids)) {
            return '营业厅信息不存在';
        }

        $content_info = _model('screen_content_meal')->read($this->content_id);

        if (!$this->content_id || !$content_info) {
            return '内容不存在';
        }

        if ($this->area_id == -1) {
            return '请选择区域！';
        }

        if ($this->city_id == -1) {
            return '请选择城市！';
        }

        if ($this->province_id == -1) {
            return '请选择省份！';
        }

        //验证专属机型图是否指定机型
        if (!$this->phone_name || !$this->phone_version || $this->phone_name == 'all' || $this->phone_version == 'all') {
            return '此内容为专属机型内容，请指定专属机型';
        }

        if (!$this->phone_name && $content_info['type'] == 4) {
            $this->phone_name = 'all';
        }

        if (!$this->phone_version && $content_info['type'] == 4) {
            $this->phone_version = 'all';
        }

        $device_filter = array(
                'phone_name' => $this->phone_name,
                'phone_version' => $this->phone_version
        );

        //验证全国是否发放过 注：无论投放到哪，都需验证是否 全国投放
        $res = $this->is_content_put($this->content_id, 'group', 0, $device_filter);
        if ($res === true) {
            return '该内容已经全国投放，如需单独投放请删除全国投放权限！';
        } else if ($res == 2) {
            return '该内容已经全国全机型投放，如需单独投放请删除全国全机型投放权限！';
        }

        //验证本省是否投放
        if (!in_array(0,$this->business_hall_ids) || $this->area_id || $this->city_id || $this->province_id) {
            $res = $this->is_content_put($this->content_id, 'province', $this->province_id, $device_filter);
            if ($res === true) {
                return '该内容已经在本省投放，如需单独投放请删除省份投放权限！';
            } else if ($res == 2) {
                return '该内容已经在本省全机型投放，如需单独投放请删除省份全机型投放权限！';
            }
        }

        //验证本市是否投放
        if (!in_array(0,$this->business_hall_ids) || $this->area_id || $this->city_id) {
            //查看是否发放过 --城市
            $res = $this->is_content_put($this->content_id, 'city', $this->city_id, $device_filter);
            if ($res === true) {
                return '该内容已经在本市投放，如需单独投放请删除城市投放权限！';
            } else if ($res == 2) {
                return '该内容已经在本市全机型投放，如需单独投放请删除城市全机型投放权限！';
            }

        }
        //区、厅 执行
        if (!in_array(0,$this->business_hall_ids) || $this->area_id) {

            //查看是否发放过 --区县
            $res = $this->is_content_put($this->content_id, 'area', $this->area_id, $device_filter);

            if ($res === true) {
                return '该内容已经在本区县投放，如需单独投放请删除区县投放权限！';
            } else if ($res == 2) {
                return '该内容已经在本区县全机型投放，如需单独投放请删除区县全机型投放权限！';
            }

        }

        return 'ok';
    }

    /**
     * 营业厅发布
     */
    private function business_hall_put($filter)
    {

        $filter['province_id']  = $this->province_id;
        $filter['city_id']      = $this->city_id;
        $filter['area_id']      = $this->area_id;
        $filter['res_name']     = 'business_hall';
        $new_res_id             = array();  //发布后的res_id

        $count = count($this->business_hall_ids);

        if ($count > 1) {
            //先删除存在的厅发布信息
            $exits_ids = _model('screen_meal_res')->getFields(array(
                    'content_id'        => $this->content_id,
                    'province_id'       => $this->province_id,
                    'city_id'           => $this->city_id,
                    'area_id'           => $this->area_id,
                    'phone_name'        => $this->phone_name,
                    'phone_version'     => $this->phone_version
            ));

            if ($exits_ids) {
                _model('screen_meal_res')->delete($exits_ids);
            }
        }

        foreach ($this->business_hall_ids as $v) {
            if ($v) {

                $filter['res_id']           = $v;

                if (_uri('screen_meal_res', $filter)) {
                    return '该内容已经投放，无需重复投放！';
                }

                $filter['issuer_res_name']  = $this->member_info['res_name'];  //发布者res_name
                $filter['issuer_res_id']    = $this->member_info['res_id'];    //发布者res_id
                $new_res_id[] = _model('screen_meal_res')->create($filter);
            }
        }

        return $new_res_id;
    }

    /**
     * 区发布
     */
    private function area_put($filter)
    {

        $filter['province_id']  = $this->province_id;
        $filter['city_id']      = $this->city_id;
        $filter['area_id']      = $this->area_id;
        $filter['res_name']     = 'area';
        $filter['res_id']       = $this->area_id;

        $delete_filter = array(
                'content_id'    => $this->content_id,
                'province_id'   => $this->province_id,
                'city_id'       => $this->city_id,
                'area_id'       => $this->area_id,
                'phone_name'    => $this->phone_name,
                'phone_version' => $this->phone_version
        );

        $exits_ids = _uri('screen_meal_res', $delete_filter, 'id');

        if ($exits_ids) {
            _model('screen_meal_res')->delete($exits_ids);
        }

        $filter['issuer_res_name']  = $this->member_info['res_name'];  //发布者res_name
        $filter['issuer_res_id']    = $this->member_info['res_id'];    //发布者res_id

        return _model('screen_meal_res')->create($filter);
    }

    /**
     * 市发布
     */
    private function city_put($filter)
    {
        $filter['province_id']  = $this->province_id;
        $filter['city_id']      = $this->city_id;
        $filter['res_name']     = 'city';
        $filter['res_id']       = $this->city_id;

        $delete_filter = array(
                'content_id'    => $this->content_id,
                'province_id'   => $this->province_id,
                'city_id'       => $this->city_id,
                'phone_name'    => $this->phone_name,
                'phone_version' => $this->phone_version
        );

        $exits_ids = _uri('screen_meal_res', $delete_filter, 'id');

        if ($exits_ids) {
            _model('screen_meal_res')->delete($exits_ids);
        }

        $filter['issuer_res_name']  = $this->member_info['res_name'];  //发布者res_name
        $filter['issuer_res_id']    = $this->member_info['res_id'];    //发布者res_id

        return _model('screen_meal_res')->create($filter);

    }

    /**
     * 省发布
     */
    private function province_put($filter)
    {
        $filter['province_id']  = $this->province_id;
        $filter['res_name']     = 'province';
        $filter['res_id']       = $this->province_id;

        $delete_filter = array(
                'content_id'    => $this->content_id,
                'province_id'   => $this->province_id,
                'phone_name'    => $this->phone_name,
                'phone_version' => $this->phone_version
        );

        $exits_ids = _uri('screen_meal_res', $delete_filter, 'id');

        if ($exits_ids) {
            _model('screen_meal_res')->delete($exits_ids);
        }

        $filter['issuer_res_name']  = $this->member_info['res_name'];  //发布者res_name
        $filter['issuer_res_id']    = $this->member_info['res_id'];    //发布者res_id

        return _model('screen_meal_res')->create($filter);

    }

    /**
     * 集团发布
     */
    private function group_put($filter)
    {
        $filter['res_name']     = 'group';
        $filter['res_id']       = 0;

        $delete_filter = array(
                'content_id'    => $this->content_id,
                'phone_name'    => $this->phone_name,
                'phone_version' => $this->phone_version
        );

        $exits_ids = _uri('screen_meal_res', $delete_filter, 'id');

        if ($exits_ids) {
            _model('screen_meal_res')->delete($exits_ids);
        }

        $filter['issuer_res_name']  = $this->member_info['res_name'];  //发布者res_name
        $filter['issuer_res_id']    = $this->member_info['res_id'];    //发布者res_id

        return _model('screen_meal_res')->create($filter);
    }

    /**
     * 查询本焦点图是否成功投放
     * @param int $screen_content_id
     * @param str $res_name
     * @param int $res_id
     */
    public function is_content_put($content_id , $res_name, $res_id = 0, $device_info, $table='screen_meal_res')
    {

        if (!$content_id || !$res_name) {
            return true;
        }

        $filter['content_id']       = $content_id;
        $filter['res_name']         = $res_name;
        $filter['res_id']           = $res_id;

        $content_info = _uri('screen_content_meal', $content_id);

        if (!$content_info) {
            return false;
        }

        if ($content_info['type'] == 4) {

            //先查询本焦点图是否已全机型投放
            $filter['phone_name'] = 'all';
            $filter['phone_version'] = 'all';

            $info = _uri($table, $filter);

            if ($info) {
                return 2;
            }

            if (isset($device_info['phone_name']) && isset($device_info['phone_version'])) {
                $filter['phone_name'] = $device_info['phone_name'];
                $filter['phone_version'] = $device_info['phone_version'];
            }
        }

        $info = _uri($table, $filter);

        if (!$info) {
            return false;
        }

        return true;
    }

    /**
     *
     * 根据内容res推送设备
     * @param unknown $content_res
     * @param unknown $param
     *
     * 由 get_put_registration_ids() 方法改编
     */
    public function push_by_content_res($content_res, $param)
    {

        $jpush = new JiGuangPush(push_config::$conf['manage_appkey'], push_config::$conf['manage_master_secret']);

        //不存在关联则全部推送
        if ( !is_array($content_res) || !$content_res ) {
            return $jpush->push_all($param);
        }

        $tags = array();

        //指定省
        if ($content_res['res_name'] == 'province') {
            $tag_id = push_helper::get_province_tag($content_res['res_id']);
            $tags[] = _uri('screen_device_tag', $tag_id, 'tag');
            //指定市
        } else if ($content_res['res_name'] == 'city') {
            $tag_id = push_helper::get_city_tag($content_res['res_id']);
            $tags[] = _uri('screen_device_tag', $tag_id, 'tag');

            //指定区， 暂不支持
        } else if ($content_res['res_name'] == 'area') {
            $tag_id = push_helper::get_area_tag($content_res['res_id']);
            $tags[] = _uri('screen_device_tag', $tag_id, 'tag');

            //指定厅
        } else if ($content_res['res_name'] == 'business_hall') {
            $tag_id = push_helper::get_business_hall_tag($content_res['res_id']);
            $tags[] = _uri('screen_device_tag', $tag_id, 'tag');
            //全部
        }

        //指定机型
        if ($content_res['phone_name'] && $content_res['phone_name'] != 'all' &&  $content_res['phone_version'] && $content_res['phone_version'] != 'all') {
            $tag_id = push_helper::get_phone_name_version_tag($content_res['phone_name'], $content_res['phone_version']);
            $tags[] = _uri('screen_device_tag', $tag_id, 'tag');
        }

        //全国
        if (!$tags && $content_res['res_name'] == 'group') {
            return $jpush->push_all($param);
        }

        return $jpush->push_tag_and($tags, $param);
    }

    /**
     * 根据发布内容获取要发布的极光推注册id
     * @param unknown $content_res
     */
    public function get_put_registration_ids($content_res)
    {

        if ( !is_array($content_res) || !$content_res ) {
            return 'all';
        }

        $registration_ids_device = false;
        $registration_ids_region = false;

        //指定机型
        if ($content_res['phone_name'] && $content_res['phone_version']) {

            $tag_id = push_helper::get_phone_name_version_tag($content_res['phone_name'], $content_res['phone_version']);

            $registration_ids_device = _model('screen_device_tag_res')->getFields('registration_id', array('tag_id' => $tag_id));

        //指定品牌, 暂不支持
        } else if ($content_res['phone_name'] && !$content_res['phone_version']) {

        }

        //指定省, 暂不支持
        if ($content_res['res_name'] == 'province') {

        //指定市
        } else if ($content_res['res_name'] == 'city') {
            $tag_id = push_helper::get_city_tag($content_res['res_id']);
            $registration_ids_region = _model('screen_device_tag_res')->getFields('registration_id', array('tag_id' => $tag_id));

            //本市下没有
            if (!$registration_ids_region) {
                return false;
            }

        //指定区， 暂不支持
        } else if ($content_res['res_name'] == 'area') {
            $tag_id = push_helper::get_area_tag($content_res['res_id']);
            $registration_ids_region = _model('screen_device_tag_res')->getFields('registration_id', array('tag_id' => $tag_id));

            //本区下没有
            if (!$registration_ids_region) {
                return false;
            }

        //指定厅
        } else if ($content_res['res_name'] == 'business_hall') {

            $tag_id = push_helper::get_business_hall_tag($content_res['res_id']);
            $registration_ids_region = _model('screen_device_tag_res')->getFields('registration_id', array('tag_id' => $tag_id));

            //本厅下没有
            if (!$registration_ids_region) {
                return false;
            }

        //全部
        } else {

        }

        $registration_ids = array();

        //指定地区机型
        if ($registration_ids_region && $registration_ids_device) {
            //取交集
            $registration_ids = array_intersect($registration_ids_region, $registration_ids_device);
        //指定地区
        } else if ($registration_ids_region) {
            $registration_ids = $registration_ids_region;
        //指定机型
        } else if ($registration_ids_device) {
            $registration_ids = $registration_ids_device;
        }

        return $registration_ids;
    }


    /**
     * 分局发布内容获取应发布的极光推标签
     * @param unknown $content_res
     */
    public function get_put_tags($content_res)
    {
        if ( !is_array($content_res) || !$content_res ) {
            return 'all';
        }

        $tab_id = 0;
        //指定机型（最次优）
        if ($content_res['phone_name'] && $content_res['phone_version']) {

            $tag_id = push_helper::get_phone_name_version_tag($content_res['phone_name'], $content_res['phone_version']);

        //指定品牌, 暂不支持
        } else if ($content_res['phone_name'] && !$content_res['phone_version']) {
            //不支持的推送暂时全部推送
            $tag_id = 0;
        }

        //指定省, 暂不支持
        if ($content_res['res_name'] == 'province') {
            //不支持的推送暂时全部推送
            $tag_id = 0;
        //指定市
        } else if ($content_res['res_name'] == 'city') {

            $tag_id = push_helper::get_city_tag($content_res['res_id']);

        //指定区
        } else if ($content_res['res_name'] == 'area') {
            $tag_id = push_helper::get_area_tag($content_res['res_id']);
        //指定厅
        } else if ($content_res['res_name'] == 'business_hall') {
            $tag_id = push_helper::get_business_hall_tag($content_res['res_id']);

        //全部
        } else {
            //全部推送
            $tag_id = 0;
        }

        $tag = '';

        if ($tag_id && $tag_info = _model('screen_device_tag')->read($tag_id)) {
            $tag = $tag_info['tag'];
        }

        return $tag;
    }

    /**
     * 调取各资源投放情况
     * @param array $params
     * @return array
     */
    public function get_list($params)
    {
        $res = '';
        if (isset($params['res']) && $params['res']) {
            $res = $params['res'];
        }
        if (!$res) {
            return array();
        }

        $key = '';
        if (isset($params['field']) && $params['field']) {
            $key = $params['field'];
        }
        $value = '';
        if (isset($params['value']) && $params['value']) {
            $value = $params['value'];
        }

        if (!$key || !$value) {
            return array();
        }

        $filter = array(
                $key.'' => (int)$value
        );

        $res_list = _model($res)->getList($filter, ' ORDER BY `id` ASC LIMIT '.$this->per_page);
        //var_dump($filter, $res_list);

        return $res_list;
    }
}