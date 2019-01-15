<?php
/**
  * alltosun.com 亮屏widget screen.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年6月30日 下午5:24:12 $
  * $Id$
  */
class screen_widget
{
    public function default_search_filter($member_info)
    {
        $filter = array();

        if ($member_info['res_name'] == 'business_hall') {
            $filter['business_id'] = $member_info['res_id'];
        } else if ($member_info['res_name'] == 'group') {
            return $filter;
        } else {
            $filter["{$member_info['res_name']}_id"] = $member_info['res_id'];
        }

        return $filter;

    }

    /**
     * 初始化搜索条件
     * @param unknown $member_info
     */
    public function init_filter($member_info, $search_filter)
    {
        $filter = $this->default_search_filter($member_info);
        //搜索判断
        if (!empty($search_filter['province_id'])) {
            $filter['province_id'] = $search_filter['province_id'];

            $province = array('province_id' => $search_filter['province_id']);
            Response::assign('where1' , $province);
        }
        if (!empty($search_filter['city_id'])) {
            $filter['city_id'] = $search_filter['city_id'];

            $city = array('city_id' => $search_filter['city_id']);
            Response::assign('where2' , $city);
        }

        if (!empty($search_filter['area_id'])) {
            $filter['area_id'] = $search_filter['area_id'];
        }

        if (!empty($search_filter['business_hall_title'])) {
            $search_filter['business_hall_title'] = trim($search_filter['business_hall_title']);
            $business_hall_id = _uri('business_hall', array('title' => $search_filter['business_hall_title']), 'id');
            if ($business_hall_id) {
                $filter['business_hall_id'] = $business_hall_id;
            }
        }
        return $filter;
    }

    /**
     * 根据营业厅member获取搜索数据
     */
    public function get_search_by_member($params = array())
    {

        if (isset($params['member_info']['res_name']) && $params['member_info']['res_name'] && isset($params['member_info']['res_id'])) {
            if ($params['member_info']['res_name'] != 'group' && !$params['member_info']['res_id']) {
                return false;
            }
            //查询省厅地区信息
            $region_info = business_hall_helper::get_region_by_member($params['member_info']['res_name'], $params['member_info']['res_id']);

            return $region_info;
        } else {
            return false;
        }

    }

    /**
     * 根据地区权限和设备获取内容 12-16号版本
     * @param unknown $filter
     */
    public function get_content_by_power($filter, $device_info, $table='screen_content_res')
    {

        //查询所有此权限下的发布内容, 包含机型的, 注：因为要使用 array_slice 取两条，所以一定要倒序查
        $content_res_list = _model($table)->getList($filter, ' ORDER BY `content_id` DESC ');

        $content_ids_region = array();  //地区的
        $content_ids_device = array();  //设备的

        foreach ($content_res_list as $k => $v) {

            //验证发布范围权限， 全国直接略过
            if ($v['res_name'] != 'group') {

                //验证省
                if ($v['res_name'] == 'province' && ($v['res_id'] != $device_info['province_id'])) {
                    continue;
                }

                //验证市
                if ($v['res_name'] == 'city' && ($v['res_id'] != $device_info['city_id'])) {
                    continue;
                }

                //验证区
                if ($v['res_name'] == 'area' && ($v['res_id'] != $device_info['area_id'])) {
                    continue;
                }

                //验证厅
                if ($v['res_name'] == 'business_hall' && ($v['res_id'] != $device_info['business_id'])) {
                    continue;
                }

            }

            //如果有发布品牌和发布型号
            if ($v['phone_name'] && $v['phone_version']) {
                if (($v['phone_name'] == $device_info['phone_name'] && $v['phone_version'] == $device_info['phone_version']) || ( $v['phone_name'] == 'all' && $v['phone_version'] == 'all' )) {

                    //查询内容详情
                    $content_info = _model('screen_content')->read($v['content_id']);
                    if (!$content_info) {
                        continue;
                    }

                    //是否为机型图
                    if ($content_info['type'] == 4) {
                        $content_ids_device[] = $v['content_id'];
                    } else {
                        $content_ids_region[] = $v['content_id'];
                    }
                }
                continue;
                //如果有发布品牌并且没有发布型号
            } else if ($v['phone_name'] && !$v['phone_version']) {
                if ($v['phone_name'] == $device_info['phone_name']) {
                    $content_ids_region[] = $v['content_id'];
                }
                continue;
                //按地区
            } else {
                $content_ids_region[] = $v['content_id'];
            }

        }

        //取前两条（集团、省、市、厅）
        if (count($content_ids_region) >= 2) {
            $content_ids_region = array_slice($content_ids_region, 0, 2);
        }

        //取前一条 （设备）  此处不能过滤， 因可能后期不符合权限
        //if (count($content_ids_device) >= 1) {
            $content_ids_device = $content_ids_device;
        //}

        return array('content_ids_region' => $content_ids_region, 'content_ids_device' => $content_ids_device);

    }

    /**
     * 添加或更新动作记录
     * @param unknown $params
     */
    public function add_action_record($params)
    {

        $filter                         = array();
        $filter['device_unique_id']     = $params['device_unique_id'];

        //获取设备信息
        $phone_info = screen_device_helper::get_device_info_by_device($params['device_unique_id']);
        //$phone_info         = _model('screen_device')->read($filter);

        if (!$phone_info) {
            return false;
        }

        //查询未完成动作
        $not_end_filter = array(
                'device_unique_id' => $phone_info['device_unique_id'],
                'type'      => 1,   //1- 未完成
                'status'    => array('$gt' => 0)
        );

        $action_id = 0;

        //拿起
        if ($params['type'] == 1) {

            $add_time                       = date('Y-m-d H:i:s', $params['experience_time']);

            //查询当前时间是否发生过动作
            $is = (array)(_mongo('screen', 'screen_action_record')->findOne(get_mongodb_filter(array(
                    'device_unique_id' => $phone_info['device_unique_id'],
                    'add_time'         => $add_time
            ))));

            if ($is) {
                return false;
            }

            $new_action                     = $not_end_filter;
            $new_action['province_id']      = (int)$phone_info['province_id'];
            $new_action['city_id']          = (int)$phone_info['city_id'];
            $new_action['area_id']          = (int)$phone_info['area_id'];
            $new_action['business_id']      = (int)$phone_info['business_id'];
            $new_action['mac']              = $phone_info['mac'];
            $new_action['type']             = 1;
            $new_action['experience_time']  = 0;
            $new_action['device_unique_id'] = $phone_info['device_unique_id'];
            $new_action['status']           = 1;
            $new_action['day']              = (int)date('Ymd', $params['experience_time']);
            $new_action['add_time']         = $add_time;
            $new_action['update_time']      = $add_time;
            //创建
            $res = _mongo('screen', 'screen_action_record')->insertOne($new_action);


            $action_id = $res->getInsertedId();

            //放下
        } else if ($params['type'] == 2) {

            $update_time = date('Y-m-d H:i:s', $params['experience_time']);

            //查询当前时间是否发生过动作
            $is = (array)(_mongo('screen', 'screen_action_record')->findOne(get_mongodb_filter(array(
                    'device_unique_id' => $phone_info['device_unique_id'],
                    'update_time'      => $update_time
            ))));

            if ($is) {
                return false;
            }

            $not_end = (array)(_mongo('screen', 'screen_action_record')->findOne($not_end_filter, array('limit' => 1, 'sort' => array('_id' => -1))));

            if (!$not_end) {
                return false;
            }

            $experience_time = $params['experience_time'] - strtotime($not_end['add_time']);

            //如果体验时长大于3600秒 或者小于 0
            if ($experience_time > 3600 || $experience_time < 0) {
                $res = _mongo('screen', 'screen_action_record')->deleteOne(array('_id' => $not_end['_id']));
                return false;
            }

            $update_record = array(
               '$set' => array(
                    'experience_time'   => $experience_time,
                    'update_time'       => $update_time,
                    'type'              => 2
               )
            );

            //更新
            _mongo('screen', 'screen_action_record')->updateOne(array('_id' =>$not_end['_id']), $update_record);
            $action_id = $not_end['_id'];
        }

        return $action_id;
    }

    /**
     * 添加或更新动作记录 接口3 使用
     * @param unknown $params
     */
    public function add_action_record3($params)
    {

        //获取设备信息
        $phone_info = screen_device_helper::get_device_info_by_device($params['device_unique_id']);
//         $phone_info         = _model('screen_device')->read(array('device_unique_id' => $params['device_unique_id'], 'status' => 1));

        if (!$phone_info) {
            return false;
        }

        $add_time = date('Y-m-d H:i:s', $params['add_time']);
        //查询当前时间是否发生过动作
        $is = (array)(_mongo('screen', 'screen_action_record')->findOne(get_mongodb_filter(array(
                'device_unique_id' => $phone_info['device_unique_id'],
                'add_time'         => $add_time
        ))));

        if ($is) {
            return false;
        }

        $new_data = array(
                'province_id'      => (int)$phone_info['province_id'],
                'city_id'          => (int)$phone_info['city_id'],
                'area_id'          => (int)$phone_info['area_id'],
                'business_id'      => (int)$phone_info['business_id'],
                'mac'              => '',
                'type'             => 2,
                'experience_time'  => (int)floor($params['experience_time']), //拿起到放下的体验时间
                'device_unique_id' => $params['device_unique_id'],
                'status'           => 1,
                'day'              => (int)date('Ymd', $params['add_time']),
                'add_time'         => $add_time, //拿起时间
                'update_time'      => date('Y-m-d H:i:s', $params['add_time'] + $params['experience_time']) //放下时间
        );

        //创建
        $res = _mongo('screen', 'screen_action_record')->insertOne($new_data);
        return $res->getInsertedId();

    }

    /**
     * 添加或更新动作记录 接口4 使用
     * @param unknown $params
     */
    public function add_action_record4($params)
    {

        //获取设备信息
        $phone_info = screen_device_helper::get_device_info_by_device($params['device_unique_id']);
        //         $phone_info         = _model('screen_device')->read(array('device_unique_id' => $params['device_unique_id'], 'status' => 1));
        if (!$phone_info) {
            return false;
        }

        $table = 'screen_action_record';

        //wangjf add 23点-07点之间的数据记录为夜间
        $h = (int)(date('H', $params['add_time']));
        if ($h < 7 || $h >= 23) {
            $table = 'screen_action_record_night';
        }

        $add_time = date('Y-m-d H:i:s', $params['add_time']);
        //查询当前时间是否发生过动作
        $is = (array)(_mongo('screen', $table)->findOne(get_mongodb_filter(array(
                'device_unique_id' => $phone_info['device_unique_id'],
                'add_time'         => $add_time
        ))));

        if ($is) {
            return false;
        }

        $new_data = array(
                'province_id'      => (int)$phone_info['province_id'],
                'city_id'          => (int)$phone_info['city_id'],
                'area_id'          => (int)$phone_info['area_id'],
                'business_id'      => (int)$phone_info['business_id'],
                'mac'              => '',
                'type'             => 2,
                'experience_time'  => (int)floor($params['experience_time']), //拿起到放下的体验时间
                'device_unique_id' => $params['device_unique_id'],
                'status'           => 1,
                'day'              => (int)date('Ymd', $params['add_time']),
                'add_time'         => $add_time, //拿起时间
                'update_time'      => date('Y-m-d H:i:s', $params['add_time'] + $params['experience_time']), //放下时间
                'data_add_time'      => date('Y-m-d H:i:s'), //放下时间
        );

        //创建
        $res = _mongo('screen', $table)->insertOne($new_data);

        //不进行统计
        if ($table == 'screen_action_record_night') {
            return false;
        }
        return $res->getInsertedId();

    }

    /**
     * 获取处理后的机型宣传图
     * 接口 2.0
     */
    public function get_type4_new_image($content_info, $device_unique_id)
    {

        //查询device信息
        $device_info = _uri('screen_device', array('device_unique_id' => $device_unique_id));

        if (!$device_info){

            if ($content_info['new_link']) {
                return _image($content_info['new_link']);
            }

            return _image($content_info['link']);
        }

        $filter = array(
                'device_unique_id'  => $device_unique_id,
                'content_id'        => $content_info['id'],
                'business_hall_id'  => $device_info['business_id']
        );

        $info = _model('screen_show_pic')->read($filter);

        if (!$info) {

            if ($content_info['new_link']) {
                return _image($content_info['new_link']);
            }

            //没有处理后的
            return _image($content_info['link']);
        }

        return _image($info['link']);

    }

    /**
     * 获取处理后的机型宣传图
     * 接口 3 版本用到
     */
    public function get_type4_new_image3($content_info, $device_unique_id)
    {

        //查询device信息
        $device_info = _uri('screen_device', array('device_unique_id' => $device_unique_id));

        if (!$device_info){

            if ($content_info['new_link']) {
                return _image($content_info['new_link']);
            }

            return _image($content_info['link']);
        }

        $link = screen_content_helper::app_screen_roll_get_image($device_unique_id, $content_info);

        if (!$link) {

            $link = $content_info['link'];
        }

        return _image($link);

    }

    /**
     * 根据设备信息获取宣传内容
     */
    public function get_type4_content_by_device($business_id, $phone_name, $phone_version, $device_info=array())
    {

        if ( $device_info ) {
            $content_list = _widget('screen_content')->get_device_roll_content($device_info);
            foreach ($content_list as $k => $v) {
                if ($v['type'] == 4) {
                    $v['link'] = $v['old_link'];
                    return $v;
                }
            }

            return array();
        }

        if (!$business_id || !$phone_name || !$phone_version) {
            return false;
        }

        $business_info = _uri('business_hall', $business_id);

        if ( !$business_info ){
            return false;
        }

        //先获取所有在线内容
        $content_filter = array(
                'type'              => 4,
                'start_time <='     => date('Y-m-d H:i:s'),
                'end_time >='       => date('Y-m-d H:i:s'),
                'status'            => 1
        );

        $content_ids = _model('screen_content')->getFields('id', $content_filter);

        if (!$content_ids){
            return array();
        }

        //发布类型，根据四级管理权限倒序
        $put_type = array_reverse(screen_content_config::$content_put_type, true);

        $phone_name = array(
                'all', $phone_name
        );

        $phone_version = array(
                'all', '', $phone_version
        );

        foreach ($put_type as $k => $v) {

            //获取宣传内容
            $content_res_filter = array(
                    'content_id' => $content_ids,
                    'res_name'   => $k,
                    'phone_name'    => $phone_name,
                    'phone_version' => $phone_version
            );

            if ($k == 'business_hall') {
                $content_res_filter['res_id'] = $business_id;
            } else if ($k != 'group'){
                $content_res_filter["{$k}_id"] = $business_info["{$k}_id"];
            }

            $content_res_info = _model('screen_content_res')->read($content_res_filter, ' ORDER BY `content_id` DESC ');

            if ($content_res_info) {
                //需要返回content_res 表的 id
                $content_info =  _uri('screen_content', $content_res_info['content_id']);
                $content_info['content_res_id'] = $content_res_info['id'];
                return $content_info;
            }
        }

        return array();

    }

    /**
     * 更新show_pic_info 状态信息
     * 计划任务执行
     */
    public function update_screen_show_pic_info()
    {
//         noted by  guojf
        // 查询图片
//         $show_pic_list = _model('screen_show_pic')->getList(array('status' => 1) , ' ORDER BY `id` DESC LIMIT 10 ');

//         foreach ($show_pic_list as $v) {
//             $content_info = screen_content_helper::get_content_info($v['content_id']);

//             if (!$content_info) {
//                 continue;
//             }

//             $link = screen_helper::compose_screen_image(
//                 $content_info['link'],
//                 $v['price'],
//                 $content_info['font_color_type']
//                 );

//             if (!$link) {
//                 continue;
//             }

//             //更新
//             _model('screen_show_pic')->update($v['id'], array('link' => $link ,'status' => 0));
//         }

        push_helper::push_msg('2');
        return true;
    }

    /**
     * 接口中上传文件数据的代理方法
     * 调用此方法首先需要在 txt_upload_widget中的 $array_res_name 属性中添加对应的授权资源
     * 接口4 中使用
     * @author 王敬飞 (wangjf@alltosun.com)
     * @date 2018年4月27日下午3:00:33
     */
    public function upload_file_date($upload_name, $res_name)
    {

        if (empty($_FILES[$upload_name]['tmp_name'])) {
            return array('code'=> 1003, 'msg' => '请上传文件');
        }

        $cli_file_name = $_FILES[$upload_name]['name'];

        //截取数据信息
        $file_info = explode('_', substr($cli_file_name, 0, strpos($cli_file_name, '.')));
        if (empty($file_info[2])) {
            return array('code'=> 1003, 'msg' => '非法的文件');
        }

        //设备信息
        $device_unique_id   = $file_info[0];
        $data_start_time    = $file_info[1];
        $data_end_time      = $file_info[2];

        //查询统计是否重合
        $filter = array(
                'device_unique_id'      => $device_unique_id,
                'data_start_time <='    => $data_start_time,
                'data_end_time >'       => $data_start_time,
                'res_name'              => $res_name,
        );

        $file_info = _model('screen_file_data_record')->read($filter);

        if ($file_info) {
            return array('code'=> 1003, 'msg' => '起始日期'.$data_start_time.'的数据已统计，不能重复统计');
        }

        //生成新文件
        $txt_link = _widget('screen.txt_upload')->upload_txt($upload_name, $res_name);

        if (!isset($txt_link['errno'])) {
            return array('code'=> 1003, 'msg' => '网路异常');
        }

        if ($txt_link['errno'] != 0) {
            return array('code'=> 1003, 'msg' => $txt_link['msg']);
        }

        //记录数据库
        _model('screen_file_data_record')->create(array(
                'link'              => $txt_link['file'],
                'device_unique_id'  => $device_unique_id,
                'data_start_time'   => $data_start_time,
                'data_end_time'     => $data_end_time,
                'cli_file_name'     => $cli_file_name,
                'res_name'          => $res_name,
        ));

        return true;
    }

}