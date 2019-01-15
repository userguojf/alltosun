<?php
/**
  * alltosun.com 亮屏内容widget screen_content.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年1月11日 上午10:17:06 $
  * $Id$
  */
class screen_content_widget
{
    /**
     * 计划任务 wangjf
     * 删除内容轮播的重复数据
     */
    public function delete_repeat_content_roll($per_page = 100)
    {
        $per_page = (int)$per_page;

        if (!$per_page) {
            $perPage = 100;
        }

        //执行进度标识
        $_id = '';
        $fc  = 0;

        $setting = (array)(_mongo('screen', 'screen_setting')->findOne(array('field' => 'delete_repeat_content_roll')));
        if ($setting) {
            $_id = $setting['value'];
            $fc  = $setting['fc'];
        }

        //拼装条件
        $filter = array(
                'day >=' => date('Ym01')
        );

        if ($_id) {
            $filter['_id >'] = $_id;
        }

        //$filter['_id >'] = new MongoDB\BSON\ObjectId('5a56d87af5780196c504d13f');
        //循环删除数据
        $list       = _mongo('screen', 'screen_content_click_record')->find(get_mongodb_filter($filter), array('limit' => $per_page));

        foreach ($list as $k => $v) {

            $v = (array)$v;

            $_id = $v['_id'];
            ++$fc;

            //删除条件
            $filter = array(
                    'business_id'      => $v['business_id'],
                    'device_unique_id' => $v['device_unique_id'],
                    'content_id'       => $v['content_id'],
                    'click_time'       => $v['click_time'],
            );

            //$filter         = get_mongodb_filter($filter);

            $record_list = _mongo('screen', 'screen_content_click_record')->find($filter);

            $delete_ids = array();
            foreach ($record_list as $kk => $vv) {

                $vv = (array)$vv;
                if ($kk == 0) {
                    continue;
                }

                $delete_ids[] = $vv['_id'];
            }

            if ($delete_ids){
                //p($delete_ids);
                _mongo('screen', 'screen_content_click_record')->deleteMany(get_mongodb_filter(array('_id' => $delete_ids)));
            }
        }

        //更新进度
        _mongo('screen', 'screen_setting')->updateOne(
                array('field' => 'delete_repeat_content_roll'),
                array('$set' => array('value' => $_id, 'update_time' =>date('Y-m-d H:i:s'), 'fc' => $fc)),
                array('upsert' => true)                   //不存在时创建
        );

        return true;
    }

    /**
     * 获取当前在线的内容id
     */
    public function get_online_content_id()
    {
        //为提升查询速度，先查出上线的内容
        $content_filter = array(
                'start_time  <= '   => date('Y-m-d H:i:s'),
                'end_time >= '      => date('Y-m-d H:i:s'),
                'status'            => 1
        );

        return _model('screen_content')->getFields('id', $content_filter, ' ORDER BY `id` DESC');
    }

    /**
     * 获取设备的轮播内容
     */
    public function get_device_roll_content($device_info)
    {
        //查询所有在线内容
        $online_content_ids = $this->get_online_content_id();

        if (!$online_content_ids) {
            return array();
        }

        $filter = array(
                'content_id'      => $online_content_ids
        );

        //查询在线内容的发布
        $content_res_list = _model('screen_content_res')->getList($filter, ' ORDER BY `content_id` DESC ');

        $by_region        = array();
        $by_device        = array(); //机型宣传图
        $by_set_meal      = array();
        $content_list     = array(); //内容列表
        //根据归属地或者机型分组
        foreach (screen_content_config::$content_issuer_res_name_type as $region_k => $region_v) {
            //当前归属的内容
            $region_curr = array();
            //循环内容
            foreach ($content_res_list as $k => $v) {

                //验证投放归属地
                if (!$this->check_put_range($v, $device_info, $region_k)) {
                    continue;
                }

                //查询内容详情
                $content_info = _model('screen_content')->read($v['content_id']);
                if (!$content_info) {
                    continue;
                }

                //投放者信息
                $content_info['issuer_res_name'] = $v['issuer_res_name'];
                $content_info['issuer_res_id'] = $v['issuer_res_id'];

                //机型宣传图
                if ($content_info['type'] == 4) {
                    //验证机型范围
                    if ($this->check_put_device($v, $device_info)) {
                        $by_device[] = $content_info;
                    }
                    continue;
                //套餐图
                } else if ($content_info['type'] == 5) {
                    //验证机型范围
                    if ($this->check_put_device($v, $device_info)) {
                        $by_set_meal[] = $content_info;
                    }
                    continue;
                }

                //验证机型范围
                if ($v['phone_name'] && $v['phone_name'] != $device_info['phone_name']) {
                    continue;
                }

                if ($v['phone_version'] && $v['phone_version'] != $device_info['phone_version']) {
                    continue;
                }

                //区域
                if (count($region_curr) >= 2) {
                    continue;
                }

                //其他
                $region_curr[] = $content_info;
            }

            if ($region_curr) {
                $by_region = array_merge($by_region, $region_curr);
            }
        }
        //地区发布的普通内容完善
        $content_list = $region_content_list = $this->perfection_region_content($by_region);

        //机型宣传图内容完善
        $device_content_list = $this->perfection_device_content($by_device, $device_info);

        if ($device_content_list) {
            $content_list[] = $device_content_list;
        }
        //套餐图完善
        $set_meal_content = $this->perfection_set_meal_content($by_set_meal, $device_info);
        if ($set_meal_content) {
            //因App端不兼容type值为4以外的类型，所以type暂时定为1
            $set_meal_content['type'] = 1;
            //真实类型
            $set_meal_content['fact_type'] = 5;

            $content_list[] = $set_meal_content;
        }
        return $content_list;
    }

    /**
     * 完善区域发布的普通内容
     * @param unknown $region_content_list
     */
    public function perfection_region_content($region_content_list)
    {
        if (!$region_content_list) {
            return array();
        }
        //归属地单独处理
        foreach ($region_content_list as $k => $v) {
            //添加默认轮播间隔
            if ($v['type'] == 3) {

                //轮播间隔为0，则默认轮播间隔为10秒
                if ($v['roll_interval'] < 1) $v['roll_interval'] = 10;

            } else if ($v['type'] == 1) {

                //轮播间隔为0并且为静图，则默认轮播间隔为10秒
                if ($v['roll_interval'] < 1 && !screen_content_helper::is_animated_gif(UPLOAD_PATH.'/'.$v['link'])) {
                    $v['roll_interval'] = 10;
                }
                $v['link'] = _image($v['link']);
            //视频
            } else if ($v['type'] == 2) {
                $v['link'] = _widget('screen_content.video')->_video($v['link'], 1);
            }

            $region_content_list[$k] = $v;
        }

        return $region_content_list;
    }

    /**
     * 完善机型宣传图内容
     * @param unknown $device_content_list
     * @param unknown $device_info
     */
    public function perfection_device_content($device_content_list, $device_info)
    {
        if (!$device_content_list) {
            return array();
        }

        //机型图单独处理
        foreach ( $device_content_list as $k => $v ) {

            if ($v['type'] != 4){
                continue;
            }

            //需要自助合成机型信息的内容，务必验证昵称是否被审核
            if (!$v['is_specify'] && !screen_device_helper::nickname_is_verify($device_info['phone_name'], $device_info['phone_version'])) {
                continue;
            }

            //轮播间隔为0并且为静图，则默认轮播间隔为10秒
            if ($v['roll_interval'] < 1 && !screen_content_helper::is_animated_gif(UPLOAD_PATH.'/'.$v['link'])) {
                $v['roll_interval'] = 10;
            }

            $v['old_link'] = $v['link'];

            //查询营业厅处理后的图片链接地址
            $v['link'] = _widget('screen')->get_type4_new_image3($v, $device_info['device_unique_id']);
            //机型图只取一条
            return $v;
        }

    }

    /**
     * 套餐图完善
     */
    public function perfection_set_meal_content($set_meal, $device_info)
    {

        if (!$set_meal) {
            return array();
        }

        //套餐图单独处理
        foreach ( $set_meal as $k => $v ) {

            if ($v['type'] != 5){
                continue;
            }

            //获取套餐图
            $set_meal_info = screen_content_helper::get_set_meal($device_info, $v);

            if (!$set_meal_info) {
                continue;
            }

            //轮播间隔为0并且为静图，则默认轮播间隔为10秒
            if ($v['roll_interval'] < 1) {
                $v['roll_interval'] = 10;
            }

            $v['link'] = _image($set_meal_info['link']);

            //机型图只取一条
            return $v;
        }
        return array();
    }

    /**
     * 验证投放区域范围
     * @param unknown $res_info 内容投放详情
     * @param unknown $device_info //设备详情
     * @param unknown $region //归属地
     */
    public function check_put_range($res_info, $device_info, $region)
    {
        //此条发布不在指定的归属地范围
        if ($res_info['issuer_res_name'] != $region) {
            return false;
        }

        //此条发布不在此设备归属地范围
        if ($res_info['res_name'] != 'group') {
            //发布归属厅不在设备所在厅
            if ($res_info['res_name'] == 'business_hall') {
                if ($res_info['res_id'] != $device_info['business_id']) return false;
                //发布归属地不在设备归属地
            } else if ($res_info['res_id'] != $device_info[$res_info['res_name'].'_id']) {
                return false;;
            }
        }

        return true;
    }

    /**
     * 验证投放设备机型范围(验证机型宣传图)
     * @param unknown $res_info 内容投放详情
     * @param unknown $device_info //设备详情
     */
    public function check_put_device($res_info, $device_info)
    {
        //全部机型
        if ($res_info['phone_name'] == 'all' || $res_info['phone_version'] == 'all') {
            return true;
        //指定型号
        } else if ($res_info['phone_name'] && $res_info['phone_version'] && $res_info['phone_name'] == $device_info['phone_name'] && $res_info['phone_version'] == $device_info['phone_version']) {
            return true;
        //指定品牌
        } else if ($res_info['phone_name'] && !$res_info['phone_version'] && $res_info['phone_name'] == $device_info['phone_name']) {
            return true;
        }
        return false;
    }

    /**
     * 获取设备套餐底图
     * @param unknown $device_info 设备信息
     * @param unknown $region 归属地范围
     */
    public function get_set_meal($device_info, $region)
    {
        //套餐图
        $get_set_meal_filter = array(
                'start_time <=' => date('Y-m-d H:i:s'),
                'end_time >='   => date('Y-m-d H:i:s'),
                'type'          => 5,
                'status'        => 1,           //已上线
        );

        $get_set_meal_filter['res_name'] = $region;

        if ($region == 'group') {
            $get_set_meal_filter['res_id'] = 0;
        } else if ($region == 'business_hall') {
            $get_set_meal_filter['res_id'] = $device_info['business_id'];
        } else {
            $get_set_meal_filter['res_id'] = $device_info[$region.'_id'];
        }

        //查询套餐图
        return _model('screen_content')->getList($get_set_meal_filter, ' ORDER BY `id` DESC ');
    }

    /**
     * 生成缩略图
     * @param unknown $file_path
     * @param string $prefix 缩略图前缀   middle-中图
     * @return boolean
     */
    public function make_thumb($file_path, $prefix = 'middle') {

        if (empty($file_path) || empty($prefix)) return false;

        $file_path = UPLOAD_PATH.$file_path;
        $path_info = pathinfo($file_path);

        //是否为动图, 动图不处理
        $is_animated_gif = screen_content_helper::is_animated_gif($file_path);
        if ($is_animated_gif) {
            return false;
        }

        // 缩略图路径
        $thumb_path = $path_info['dirname'].'/'.$prefix.'_'.$path_info['basename'];

        $gd = new Gd($file_path);

        //等比缩一半
        $gd->scale(floor($gd->width/2), floor($gd->height/2));
        $gd->saveAs($thumb_path);
        return true;
    }
}