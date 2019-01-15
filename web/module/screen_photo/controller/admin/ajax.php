<?php
/**
  * alltosun.com ajax.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年1月15日 下午5:47:00 $
  * $Id$
  */
class Action
{
    /**
     * 根据营业厅获取所有品牌
     */
    public function get_phone_name_by_business_hall()
    {
        $business_id = tools_helper::Post('business_id', 0);

        if (!$business_id) {
            return array('info' => 'ok', 'data' => array());
        }

        $filter = array(
                'business_id' => $business_id,
        );

        $device_list = screen_device_helper::get_device_list_by_filter($filter);

        $new_list = array();
        $phone_names = array();
        foreach ($device_list as $k => $v) {

            if (empty($phone_names[$v['phone_name']])) {
                $tmp = array();
                $tmp['phone_name']          = $v['phone_name'];
                $tmp['phone_name_nickname'] = $v['phone_name_nickname'];

                $phone_names[$v['phone_name']] = 1;
                $new_list[] = $tmp;
            }
        }

        return array('info' => 'ok', 'data' => $new_list);

    }

    /**
     * 根据营业厅获取所有机型
     */
    public function get_phone_version_by_business_hall()
    {
        $business_id    = tools_helper::Post('business_id', 0);
        $phone_name     = tools_helper::Post('phone_name', '');

        if (!$business_id || !$phone_name) {
            return array('info' => 'ok', 'data' => array());
        }

        $filter = array(
                'business_id' => $business_id,
                'phone_name'  => $phone_name
        );

        $device_list = screen_device_helper::get_device_list_by_filter($filter);

        $new_list = array();
        $phone_versions = array();
        foreach ($device_list as $k => $v) {

            if (empty($phone_versions[$v['phone_version']])) {
                $tmp = array();
                $tmp['phone_version']          = $v['phone_version'];
                $tmp['phone_version_nickname'] = $v['phone_version_nickname'];

                $phone_versions[$v['phone_version']] = 1;
                $new_list[] = $tmp;
            }
        }

        return array('info' => 'ok', 'data' => $new_list);

    }

    /**
     * 查询套餐id
     * @return string[]|number[]|string[]|mixed[]
     */
    public function get_set_meal_id()
    {
        $business_id    = tools_helper::Post('business_id', 0);
        $phone_name     = tools_helper::Post('phone_name', '');
        $phone_version     = tools_helper::Post('phone_version', '');

        if (!$business_id || !$phone_name || !$phone_version) {
            return array('info' => 'ok', 'data' => 0);
        }

        //查询套餐
        $filter = array(
                'business_id' => $business_id,
                'phone_name'  => $phoen_name,
                'phone_version' => $phone_version
        );

        $set_meal_info = _model('screen_content_set_meal')->read($filter, ' ORDER BY `id` DESC LIMIT 1 ');

        if (!$set_meal_info) {
            return array('info' => 'ok', 'data' => 0);
        }

        return array('info' => 'ok', 'data' => $set_meal_info['id']);
    }

    /**
     * 合成套餐图片
     */
    public function compose_set_meal_photo()
    {
        $set_meal_id = tools_helper::Post('set_meal_id', 0);

        if (!$set_meal_id) {
            return array('info' => 'fail', 'msg' => '套餐信息不存在');
        }

        $set_meal = _model('screen_content_set_meal')->read(array('id' => $set_meal_id));

        if (!$set_meal) {
            return array('info' => 'fail', 'msg' => '套餐信息不存在');
        }

        if ($set_meal['status'] != 0) {
            return array('info' => 'fail', 'msg' => '此套餐图属于“'.screen_photo_config::$status[$set_meal['status']].'”类型');
        }

        $set_meal['res_link'] = STATIC_URL.$set_meal['res_link'];
        //合图
        $link = screen_photo_helper::screen_ps($set_meal);

        $status = 1;
        if (!$link) {
            //更新状态
            $status = 2;
        }
        _model('screen_content_set_meal')->update($set_meal_id, array('status' => $status, 'link' => $link));

        if ($link) {
            $link = _image($link);
        }
        return array('info' => 'ok', 'data' => array('status' => $status, 'status_text' => screen_photo_config::$status[$status],  'link' => $link));
    }

    /**
     * 批量合成套餐图片
     */
    public function compose_set_meal_photo_all()
    {
        $set_meal_id = tools_helper::Post('set_meal_id', array());

        if (!$set_meal_id) {
            return array('info' => 'fail', 'msg' => '套餐信息不存在');
        }

        $set_meal_list = _model('screen_content_set_meal')->getList(array('id' => $set_meal_id));

        if (!$set_meal_list) {
            return array('info' => 'fail', 'msg' => '套餐信息不存在');
        }

        foreach ($set_meal_list as $k => $v) {
            if ($v['status'] != 0) {
                continue;
            }

            $v['res_link'] = STATIC_URL.$v['res_link'];
            //合图
            $link = screen_photo_helper::screen_ps($v);

            $status = 1;
            if (!$link) {
                //更新状态
                $status = 2;
            }
            _model('screen_content_set_meal')->update($v['id'], array('status' => $status, 'link' => $link));
        }

        return 'ok';
    }

}
