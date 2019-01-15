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
        if (!$content_id) {
            return '发布内容不存在';
        }

        //发布内容
        $result = _widget('screen_content_new.put')->put_content();

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
     * 已废弃
     */
    public function add_meal_res()
    {
        $content_id = tools_helper::post('content_id', 0);

        if (!$content_id) {
            return '发布内容不存在';
        }

        //发布内容
        $result = _widget('screen_content_new.meal_put')->put_content();

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
        $area_id = tools_helper::post('area_id', -1);
        $city_id = tools_helper::post('city_id', -1);
        $province_id = tools_helper::post('province_id', -1);
        $content_id = tools_helper::post('content_id', 0);

        $content_info = _model('screen_content')->read($content_id);
        if (!$content_info) {
            return '发布内容不存在';
        }

        if ($content_info['type'] != 5) {
            return '非套餐图无法发布';
        }

        $default = array(
            'business_hall_ids' => $business_hall_ids,
            'province_id' => $province_id,
            'city_id' => $city_id,
            'area_id' => $area_id,
            'content_id' => $content_info['id'],
            'phone_name' => '',
            'phone_version' => '',
        );

        //默认值
        if (!$default['business_hall_ids']) {
            $default['business_hall_ids'] = array(0);
        }

        //查询内容的套餐列表
        $set_meal_list = screen_content_new_helper::get_content_set_meal($content_info);

        if (!$set_meal_list) {
            return '暂无合适的套餐信息';
        }
        //更新发布状态
        _model('screen_content')->update($content_id, array('status' => 1));

        foreach ($set_meal_list as $k => $v) {
            //查询机型
            $device_nickname = screen_device_helper::get_device_nickname_info(
                array(
                    'name_nickname' => $v['phone_name'],
                    'version_nickname' => $v['phone_version']
                )
            );
            //不存在
            if (!$device_nickname) {
                continue;
            }

            $param = $default;
            $param['phone_name'] = $device_nickname['phone_name'];
            $param['phone_version'] = $device_nickname['phone_version'];
//p($param);continue;
            //发布内容
            $result = _widget('screen_content_new.put')->put_content($param);
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
    public function delete_res()
    {
        $table = Request::Post('table', '');
        $id = Request::Post('id', 0);

        if (empty($id)) {
            return '请选择您要操作的信息';
        }

        $info = _uri($table, $id);
        if (!$info) {
            return '您要删除的信息不存在';
        }

        _model($table)->delete($id);

/////////////////////////////// 推送 START ///////////////////////////////
       /* if ($table == 'screen_content_res') {
            //推送
            _widget('screen_content.put')->push_by_content_res($info, '2');
        }*/
/////////////////////////////// 推送 END ///////////////////////////////


        return 'ok';
    }

    public function delete_meal_res()
    {
        $table = Request::Post('table', '');
        $id = Request::Post('id', 0);

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

    public function update_meal_res_status()
    {
        $content_id = Request::Post('id', 0);
        $status = Request::Post('status', 0);

        if (!$content_id) {
            return '信息错误';
        }

        $info = _uri('screen_content_meal', $content_id);

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
            _model('screen_content_meal')->update($content_id, array('status' => $status));
        }

        $registration_ids = array();

        /////////////////////////////// 推送 START ///////////////////////////////
        foreach ($res_list as $k => $v) {
            //推送内容
            $result = _widget('screen_content.meal_put')->push_by_content_res($v, '3');
        }
        //////////////////////////////////////////////////////// 推送发布end /////////////////////////////////////////
        return 'ok';
    }

    // ajax存储发布的数据
    public function save_data()
    {
        $content_id = Request::Post('id', 0);
        $content = Request::Post('content', array());
        $put_type = Request::Post('put_type', 2);
        $link_src = Request::Post('link_src','');
        //单独验证
        if (empty($content['title']) || !$content['title']) {
            return '标题不能为空';
        }

        if (empty($content['start_time']) || !$content['start_time']) {
            return '开始时间不能为空';
        }

        if (empty($content['end_time']) || !$content['end_time']) {
            return '结束时间不能为空';
        }

        if (empty($content['type']) || !$content['type']) {
            return '请选择内容类型';
        }

        // 执行上传
        $link = false;

        //图片 和 宣传图
        if ($content['type'] == 1 || $content['type'] == 4) {
            $link = $link_src;

            if (!empty($_FILES['img_link']['tmp_name'])) {
                $link = upload_file($_FILES['img_link'], false, 'focus');
                //生成缩略图
                _widget('screen_content')->make_thumb($link);
            }

            //宣传图//机型宣传图价格的处理（可选）
            if ($content['type'] == 4) {
                if (!isset($content['font_color_type']) || !$content['font_color_type']) {
                    return ['code' => 1, 'info' => '请选择字体颜色'];
                }

                if (isset($content['is_specify']) && !in_array($content['is_specify'], array(0, 1))) {
                    unset($content['is_specify']);
                }

                if (isset($content['price']) && $content['price']) {
                    //给原图片压价格操作
                    $new_link = screen_helper::compose_screen_image($link, $content['price'], $content['font_color_type']);

                    if ($new_link) {
                        $content['new_link'] = $new_link;
                    }
                }
            }

            //视频
        } else if ($content['type'] == 2) {
            if (!empty($_FILES['video_link']['tmp_name'])) {
                $link_info = _widget('screen_content.video')->upload_video('video_link');
                if ($link_info['errno'] != 0) {
                    return ['code' => 1, 'info' => $link_info['msg']];
                }
                $link = $link_info['file'];
            }
            //套餐底图
        } else if ($content['type'] == 5) {
            $link = Request::Post('set_meal', '');
            //缩略图
            //_widget('screen_content')->make_thumb($link);
        }

        $type = $content['type'];

        if ($link) {
            $content['link'] = $link;
        }

        //修改
        if ($content_id) {
            $content_info = _uri('screen_content', $content_id);

            if (!$content_info) {
                return ['code' => 1, 'info' => '对不起，该信息不存在'];
            }

            if ($content['type'] == 4) {
                //图片变化就全改了 eidted by guojf
                if ($content['link']) {
                    //原有的
                    screen_helper::update_show_pic_info($content_id, $content['link'], $content['font_color_type']);

                    if (isset($content['price']) && $content['price']) {
                        //给原图片压价格操作
                        $new_link = screen_helper::compose_screen_image($content['link'], $content['price'], $content['font_color_type']);

                        if ($new_link) {
                            $content['new_link'] = $new_link;
                        }
                    }

                } else {
                    //价格存在       价格改变颜色改变     宣传图必须改变
                    if ($content['price'] && ($content_info['price'] != $content['price'] || $content_info['font_color_type'] != $content['font_color_type'])) {
                        //给原图片压价格操作
                        $new_link = screen_helper::compose_screen_image($content_info['link'], $content['price'], $content['font_color_type']);

                        if ($new_link) {
                            $content['new_link'] = $new_link;
                        }
                    }

                    //价格不存在
                    if (!$content['price']) {
                        $content['new_link'] = '';
                    }
                }

            }

            if (!$content['link']) {
                unset($content['link']);
                unset($content['type']);
            }

            $res = _model('screen_content')->update($content_id, $content);

            //设置套餐参数
            if ($type == 5) {
                // $res = screen_content_meal_helper::delete_set_meal_by_content_id($content_id);
                _model('screen_content_set_meal')->delete(array('content_id' => $content_id));

                // if ($res != true) {
                //     return ['code' => 1, 'info' => '服务器内部错误'];
                // }
                $res = $this->save_set_meal($content_id);
                if ($res != 'ok') {
                    return $res;
                }
            }
            return 'ok';

        } else {

            if (!$content['link']) {
                return ['code' => 1, 'info' => '请上传或输入发布内容'];
            }

            $member_id = member_helper::get_member_id();
            $member_info = member_helper::get_member_info($member_id);
            $content['res_name'] = $member_info['res_name'];
            $content['res_id'] = $member_info['res_id'];
            $content['member_id'] = $member_info['id'];
            //非宣传图则默认发布
            if ($type != 4) {
                $content['status'] = $put_type == 0 ? $put_type : 1;   //默认发布 -wangjf
            }
            $content_id = _model('screen_content')->create($content);

            //设置套餐参数
            if ($type == 5) {
                $res = $this->save_set_meal($content_id);
                if ($res != 'ok') {
                    return $res;
                }
            }
            return ['code' => 0, 'info' => $content_id];
        }


    }

    // 发送ajax获取上级城市
    public function get_area_path()
    {
        $res_name = Request::Post('res_name', '');
        $res_id = Request::Post('res_id', '');
        if (!$res_name) {
            return '未知地区';
        }

        if ($res_name == 'group') {
            $name = '全国';
        }

        if ($res_name == 'province') { //省

            //图文推送表中res_id有可能为空

            if (!$res_id) {
                return '全省->全市->全厅';
            }

            $name = _uri('province', $res_id, 'name');

        }

        if ($res_name == 'city') { //市
            $city_info = _uri('city', $res_id);
            if (empty($city_info)) {
                return '城市';
            }

            $province_name = _uri('province', $city_info['province_id'], 'name');
            $name = $province_name . '->' . $city_info['name'];
        }

        if ($res_name == 'area') { //区

            $area_info = _uri('area', $res_id);
            if (empty($area_info)) {
                return '地区';
            }

            $province_name = _uri('province', $area_info['province_id'], 'name');
            $city_name = _uri('city', $area_info['city_id'], 'name');
            $name = $province_name . '->' . $city_name . '->' . $area_info['name'];
        }

        if ($res_name == 'business_hall') { //厅

            if ($res_id) {

                $business_hall_info = _uri('business_hall', $res_id);

                if (empty($business_hall_info)) {

                    return '全厅';

                }

                $province_name = _uri('province', $business_hall_info['province_id'], 'name');

                $city_name = _uri('city', $business_hall_info['city_id'], 'name');

                $area_name = _uri('area', $business_hall_info['area_id'], 'name');

                $name = $province_name . '->' . $city_name . '->' . $area_name . '->' . $business_hall_info['title'];
            }


        }

        return $name;
    }

    /**
     * 保存套餐信息
     * 表单上传的方式
     * @param unknown $content_id 内容id
     */
    private function save_set_meal($content_id)
    {
        $set_meal_info = tools_helper::Post('set_meal_info', array());

        $tmp_data = array();

        //查询内容
        $content_info = _model('screen_content')->read($content_id);

        if (!$content_info) {
            return false;
        }

        //$set_meal_info['phone_version'] 是个数组，格式为 [144,14,24]
        if (empty($set_meal_info['phone_version']) || count($set_meal_info['phone_version']) < 1) {
            return '请选择机型';
        }

        $nickname_ids = array_unique($set_meal_info['phone_version']);

        //查询机型
        $nickname_list = _model('screen_device_nickname')->getList($nickname_ids);

        if (empty($set_meal_info['retail_price']) || (int)($set_meal_info['retail_price']) == 0) {
            return '零售价不能为0';
        }

        //零售价
        $tmp_data['retail_price'] = $set_meal_info['retail_price'];

        //推荐档位
        if (!empty($set_meal_info['recommended_position'])) {
            $tmp_data['recommended_position'] = $set_meal_info['recommended_position'];
        }

        //卖点1
        if (empty($set_meal_info['selling_point_1'])) {
            return '卖点1不能为空';
        }

        $tmp_data['selling_point_1'] = $set_meal_info['selling_point_1']; //默认为一三五，二四六为另一行

        //卖点2
        if (empty($set_meal_info['selling_point_2'])) {
            return '卖点2不能为空';
        }

        $tmp_data['selling_point_3'] = $set_meal_info['selling_point_2']; //默认为一三五，二四六为另一行

        //卖点3
        if (empty($set_meal_info['selling_point_3'])) {
            return '卖点3不能为空';
        }

        $tmp_data['selling_point_5'] = $set_meal_info['selling_point_3']; //默认为一三五，二四六为另一行

        //设备参数1
        if (empty($set_meal_info['param_1'])) {
            return '参数1不能为空';
        }

        $tmp_data['param_1'] = $set_meal_info['param_1'];

        //设备参数2
        if (empty($set_meal_info['param_2'])) {
            return '参数1不能为空';
        }

        $tmp_data['param_2'] = $set_meal_info['param_2'];

        //设备参数3
        if (empty($set_meal_info['param_3'])) {
            return '参数3不能为空';
        }

        $tmp_data['param_3'] = $set_meal_info['param_3'];

        //设备参数4
        if (empty($set_meal_info['param_4'])) {
            return '参数4不能为空';
        }

        $tmp_data['param_4'] = $set_meal_info['param_4'];

        //设备参数5
        if (empty($set_meal_info['param_5'])) {
            return '参数5不能为空';
        }

        $tmp_data['param_5'] = $set_meal_info['param_5'];

        //设备参数6
        if (empty($set_meal_info['param_6'])) {
            return '参数6不能为空';
        }

        $tmp_data['param_6'] = $set_meal_info['param_6'];
        $tmp_data['res_link'] = $content_info['link'];
        $tmp_data['content_id'] = $content_info['id'];
        $tmp_data['link'] = '';
        $member_id = member_helper::get_member_id();
        $member_info = member_helper::get_member_info($member_id);
        $res_name = $member_info['res_name'];
        $res_id = $member_info['res_id'];

        $tmp_data['issuer_res_name'] = $res_name;
        $tmp_data['issuer_res_id'] = $res_id;

        foreach ($nickname_list as $k => $v) {
            if ($v['status'] == 0) {
                return $v['name_nickname'] . ' ' . $v['version_nickname'] . ' 审核未通过';
            }

            $new_data = $tmp_data;
            $new_data['phone_name'] = $v['name_nickname'];
            $new_data['phone_version'] = $v['version_nickname'];
            $new_data['device_nickname_id'] = $v['id'];

            //创建
            _model('screen_content_set_meal')->create($new_data);

        }
        return true;
    }
    
    /** 删除内容发布content_res关联的方法
     * @return string
     * @throws AnException
     */
    public function upload_file()
    {
        $file = Request::Post('data', '');
        if (!$file) {
            return array('errcode' => 'no', 'errmsg' => 'accept');
        }

        $file = str_replace('data:image/jpeg;base64,', '', $file);
        $file = str_replace('data:image/png;base64,', '', $file);

        $file = base64_decode($file);

        $path = '/'.date('Y/m/d').'/screen_content_add_'.time().'.jpg';

        $res = 0;
        if ( !file_exists(ROOT_PATH . $path) ) {
            $res = file_put_contents(ROOT_PATH .'/upload'.$path, $file);
        } else {
            return array('errcode' => 'no', 'errmsg' => '由于网络问题，请刷新重试');
        }

        if (!$res) {
            return array('errcode' => 'no', 'errmsg' => '上传失败，请刷新重试');
        }

        $member_id   = member_helper::get_member_id();
        $member_info = member_helper::get_member_info($member_id);

        $id = _model('screen_content_customize_pic')->create(array(
            'link' => $path,
            'res_id' => $member_info['res_id'],
            'res_name' => $member_info['res_name'],
             'date'    => date('Ymd')
        ));

        return array('errcode' => 'ok', 'errmsg' => '上传完成', 'srcval' => $path, 'use_id' => $id);
    }

    public function update_make_pic_status()
    {
        $content_id = Request::Post('id', 0);
        $status = Request::Post('status', 0);

        if (!$content_id) {
            return '信息错误';
        }

        $info = _uri('screen_content_make_pic_record', $content_id);

        if (!$info)  return '内容不存在';

        if ($status == 2) {
            //删除内容
            _model('screen_content_make_pic_record')->delete($content_id);
        } else {
            //修改状态
            _model('screen_content_make_pic_record')->update($content_id, array('status' => $status));
        }

        return 'ok';
    }
}