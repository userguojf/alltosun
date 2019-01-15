<?php
/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (shenxn@alltosun.com) $
 * $Date: 2015-12-22 下午2:41:43 $
 * $Id$
 */

class Action
{
    /**
     * 添加一条发布关联
     */
    public function add_res()
    {
        $content_id = tools_helper::post('content_id', 0);

        if ( !$content_id ) {
            return '发布内容不存在';
        }

        //发布内容
        $result = _widget('screen_content.put')->put_content();

        if ($result != 'ok') {
            return $result;
        }

        //更新发布状态 (已在 widget:screen_content.put 中推送 )
        _model('screen_content')->update($content_id, array('status' => 1));

        //push_helper::push_msg(2);

        return 'ok';
    }

    /**
     * 添加一条发布关联
     */
    public function add_meal_res()
    {
        $content_id = tools_helper::post('content_id', 0);

        if ( !$content_id ) {
            return '发布内容不存在';
        }

        //发布内容
        $result = _widget('screen_content.meal_put')->put_content();

        if ($result != 'ok') {
            return $result;
        }

        //更新发布状态 (已在 widget:screen_content.put 中推送 )
        _model('screen_content_meal')->update($content_id, array('status' => 1));

        //push_helper::push_msg(2);

        return 'ok';
    }
    /**
     * 套餐图发布关联
     * @param unknown $content_info
     */
    public function add_res_type5($content_info)
    {
        $business_hall_ids = tools_helper::post('business_hall_ids', array());
        $area_id           = tools_helper::post('area_id', -1);
        $city_id           = tools_helper::post('city_id', -1);
        $province_id       = tools_helper::post('province_id', -1);
        $content_id        = tools_helper::post('content_id', 0);

        $content_info = _model('screen_content')->read($content_id);
        if (!$content_info) {
            return '发布内容不存在';
        }

        if ($content_info['type'] != 5) {
            return '非套餐图无法发布';
        }

        $default = array(
                    'business_hall_ids' => $business_hall_ids,
                    'province_id'      => $province_id,
                    'city_id'      => $city_id,
                    'area_id'      => $area_id,
                    'content_id'   => $content_info['id'],
                    'phone_name'   => '',
                    'phone_version' => '',
        );

        //默认值
        if (!$default['business_hall_ids']) {
            $default['business_hall_ids'] = array(0);
        }

        //查询内容的套餐列表
        $set_meal_list = screen_content_helper::get_content_set_meal($content_info);

        if (!$set_meal_list) {
            return '暂无合适的套餐信息';
        }
        //更新发布状态
        _model('screen_content')->update($content_id, array('status' => 1));

        foreach ($set_meal_list as $k => $v) {
            //查询机型
            $device_nickname = screen_device_helper::get_device_nickname_info(
                            array(
                                    'name_nickname'     => $v['phone_name'],
                                    'version_nickname'  => $v['phone_version']
                            )
                    );
            //不存在
            if (!$device_nickname) {
                continue;
            }

            $param = $default;
            $param['phone_name']    = $device_nickname['phone_name'];
            $param['phone_version'] = $device_nickname['phone_version'];
//p($param);continue;
            //发布内容
            $result = _widget('screen_content.put')->put_content($param);
            if ($result != 'ok') {
                return $result;
            }
        }

        return 'ok';
    }

    /** 删除内容发布content_res关联的方法
     * @return string
     * @throws AnException
     */
    public function delete_res() {
        $table         = Request::Post('table', '');
        $id            = Request::Post('id', 0);

        if (empty($id)) {
            return '请选择您要操作的信息';
        }

        $info = _uri($table, $id);
        if (!$info) {
            return '您要删除的信息不存在';
        }

        _model($table)->delete($id);

/////////////////////////////// 推送 START ///////////////////////////////
        if ($table == 'screen_content_res') {
            //推送
            _widget('screen_content.put')->push_by_content_res($info, '2');
        }
/////////////////////////////// 推送 END ///////////////////////////////


        return 'ok';
    }

    public function delete_meal_res() {
        $table         = Request::Post('table', '');
        $id            = Request::Post('id', 0);

        if (empty($id)) {
            return '请选择您要操作的信息';
        }

        $info = _uri($table, $id);
        if (!$info) {
            return '您要删除的信息不存在';
        }

        _model($table)->delete($id);
        /////////////////////////////// 推送 START ///////////////////////////////
        //推送内容
        $result = _widget('screen_content.meal_put')->push_by_content_res($info, '3');
        /////////////////////////////// 推送 END ///////////////////////////////
        return 'ok';
    }

    public function update_res_status()
    {
        $content_id = Request::Post('id', 0);
        $status = Request::Post('status', 0);

        if (!$content_id) {
            return '信息错误';
        }

        $info = _uri('screen_content',$content_id);

        if (!$info) {
            return '内容不存在';
        }

        //设备宣传图发布，则验证是否发布到设备
        if ($info['type'] == 4 && $status == 1) {
            $info = _model('screen_content_res')->read(array('content_id' => $info['id'], 'phone_name != ' => ''));
            if (!$info) {
                return '发布失败，请在编辑页投放到指定设备';
            }
        }

        //先获取所有的发布，必须要在删除之前获取
        $res_list = _model('screen_content_res')->getList(array('content_id' => $info['id']));

        if ($status == 2) {
            //删除内容
            _model('screen_content')->delete($content_id);
            //删除发布
            _model('screen_content_res')->delete(array('content_id' => $content_id));
        } else {
            //修改状态
            _model('screen_content')->update($content_id,array('status' => $status));
        }

//////////////////////////////////////////////////////// 推送发布 start ////////////////////////////////////////////////////////////////

        foreach ( $res_list as $k => $v ){
            //推送
            _widget('screen_content.put')->push_by_content_res($v, '2');
        }

//////////////////////////////////////////////////////// 推送发布end /////////////////////////////////////////
        return 'ok';
    }

    public function update_meal_res_status()
    {
        $content_id = Request::Post('id', 0);
        $status = Request::Post('status', 0);

        if (!$content_id) {
            return '信息错误';
        }

        $info = _uri('screen_content_meal',$content_id);

        if (!$info) {
            return '内容不存在';
        }

        //设备宣传图发布，则验证是否发布到设备
        if ($info['type'] == 4 && $status == 1) {
            $info = _model('screen_meal_res')->read(array('content_id' => $info['id'], 'phone_name != ' => ''));
            if (!$info) {
                return '发布失败，请在编辑页投放到指定设备';
            }
        }

        //必须要先查出来套餐内容
        $res_list = _model('screen_meal_res')->getList(array('content_id' => $info['id']));

        if ($status == 2) {
            //删除内容
            _model('screen_content_meal')->delete($content_id);
            //删除发布
            _model('screen_meal_res')->delete(array('content_id' => $content_id));
        } else {
            //修改状态
            _model('screen_content_meal')->update($content_id,array('status' => $status));
        }

        $registration_ids = array();

        /////////////////////////////// 推送 START ///////////////////////////////
        foreach ( $res_list as $k => $v ){
            //推送内容
            $result = _widget('screen_content.meal_put')->push_by_content_res($v, '3');
        }
        //////////////////////////////////////////////////////// 推送发布end /////////////////////////////////////////
        return 'ok';
    }


}