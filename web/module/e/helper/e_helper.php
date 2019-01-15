<?php
/**
  * alltosun.com 移动端帮助文件 e_helper.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2016-8-18 下午2:14:34 $
  * $Id$
  */
class e_helper
{
    /**
     * 查询指定日期数据
     * @param str $table
     * @param number $res_name
     * @param number $res_id
     * @param string $event
     * @return number
     */
    public static function show_home_num_by_time($res_name,  $time='', $event='click')
    {
        $num = 0;

        if (!$res_name) {
            return false;
        }

        $member_info = member_helper::get_member_info();

        if (!$time) {
            $time = date('Ymd');
        }
        $filter = array(
                'res_name' => $res_name,
                'event'    => $event,
                'time'     => $time
        );

        if ($member_info['res_name'] == 'group') {
            $table = 'stat_day';
        } else if ($member_info['res_name'] == 'province') {
            $table = 'stat_province_day';
            $filter['province_id'] = $member_info['res_id'];
        } else if ($member_info['res_name'] == 'city') {
            $table = 'stat_city_day';
            $filter['city_id'] = $member_info['res_id'];
        } else if ($member_info['res_name'] == 'area') {
            $table = 'stat_area_day';
            $filter['area_id'] = $member_info['res_id'];
        } else if ($member_info['res_name'] == 'business_hall') {
            $table = 'stat_business_hall_day';
            $filter['business_hall_id'] = $member_info['res_id'];
        } else {
            return false;
        }

        $info = _model($table)->read($filter);

        if ($info) {
            $num =  $info['num'];
        }

        return $num;
    }

    /**
     * 获取第一条图片数据
     */
    public static function get_first_img($imgs)
    {
        if (!$imgs) {
            return SITE_URL.'/images/null.jpg';
        }
        $img_arr = explode(',',$imgs);

        if (isset($img_arr[0]) && !empty($img_arr[0])) {
            $img = _uri('file', $img_arr[0], 'path');
        } else {
            $img = _uri('file', $imgs, 'path');
        }

        return _image($img);

    }

    /**
     * 解析时间
     */
    public static function analysis_time($time)
    {
        if (!$time) {
            return '未知';
        }

        //转化为时间戳
        $timestamp = strtotime($time);

        //计算时差
        $equation_time = time() - $timestamp;

        //30天前
        if ($equation_time >= 60*60*24*30) {
            $time_arr = explode(' ', $time);
            return $time_arr[0];
        }

        //计算天
        $date = floor($equation_time/(60*60*24));
        if ( $date > 0) {
            return $date.'天前';
        }

        //计算时
        $hour = floor($equation_time/(60*60));
        if ($hour > 0) {
            return $hour.'小时前';
        }

        //计算分
        $minute = floor($equation_time/60);
        if ($minute > 0) {
            return $minute.'分钟前';
        }

        return $equation_time.'秒前';


    }

    /**
     * 获取所属区域
     */
    public static function get_business_hall_area($business_id)
    {
        if (!$business_id) {
            return '未知';
        }

        //获取营业厅信息
        $business_info = _uri('business_hall', $business_id);
        if (!$business_info) {
            return '未知';
        }

        //获取省市区信息
        $province_name = _uri('province', $business_info['province_id'], 'name');
        $city_name     = _uri('city', $business_info['city_id'], 'name');
        $area_name     = _uri('area', $business_info['area_id'], 'name');

        $area_info = '';
        //拼接省市区信息
        if ($province_name) {
            $area_info.=$province_name;
        }

        if ($city_name) {
            $area_info.='-'.$city_name;
        }

        if ($area_name) {
            $area_info.='-'.$area_name;
        }

        if (!$area_info) {
            return '未知';
        }

        return $area_info;
    }

    /**
     * 获取引导记录
     */
    public static function get_guide_record($member_id = 0) {
        if (!$member_id) {
            return false;
        }

        //获取客户端ip
        $cli_ip = tools_helper::get_cli_ip();

        if (!$cli_ip) {
            return false;
        }

        //获取引导记录
        $guide_info = _uri('e_guide_record', array('member_id' => $member_id, 'ip' => $cli_ip));
//p($guide_info);exit;
        if (!empty($guide_info)) {
            return true;
        }
        return false;
    }
    /**
     * @param int    $days
     * @param string $member_user
     * @return bool
     */
    public static function un_activity_yyt($days , $member_user)
    {
        //判断
        if (!$days || !$member_user) return false;

        $time = date('Y-m-d H:i:s', time() - $days*3600*24);

        $business_hall_id = _model('business_hall')->getFields(
                            'id' ,
                            array(
                                'user_number' => $member_user,
                                'activity'    => 1
                                )
        );


        $info = _model('user_business_hall_num')->read(
                            array(
                                'business_hall_id'   => $business_hall_id,
                                'last_login_time >=' => $time
                            ), 'ORDER BY `last_login_time` DESC');
        if (!$info) {
            //说明不活跃的营业厅
            return false;
        } else {
            //说明为活跃的营业厅
            return true;
        }
    }

    /**
     * added by guojf
     * $url  str     请求的接口地址
     * $data array() 携带的数据
     */
    public static function wx_upload($url , $data)
    {
        $curl = curl_init();

        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);

        if (!empty($data)){
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
        }

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 10);

        $output = curl_exec($curl);
        curl_close($curl);

        return $output;
    }

    /**
     * 数据地图单点登录完整url
     * @param unknown $username 渠道编码
     * @param string $web  移动端-mobile  pc端-pc
     */
    public static function create_sjdt_login_url($username, $web='pc')
    {
        $login_info = e_config::$to_login_info['szdt'];
//         if ($username) {
//             $username = ;
//         }
        $token = md5(strtoupper($username).$login_info['app_key']);
        $url = $login_info['url'];
        $url = str_replace("{USERNAME}", $username, $url);
        $url = str_replace("{TOKEN}", $token, $url);
        $url = str_replace("{WEB}", $web, $url);
        return $url;
    }

    /**
     * 企业号数字地图接口检查秘钥
     * @param array $data
     * @return boolean
     */
    public static function check_secret($appid, $timestamp, $token)
    {
        //判断appId
        if ($appid != $this->secret['appid']) {
            //返回给数字地图
            self::return_dm_data(false, '参数错误:appid');
            return false;
        }

        //判断时间戳
        if (!$timestamp) {
            //返回给数字地图
            self::return_dm_data(false, '参数错误:timestamp');
            return false;
        }

        //判断token
        if ($token != md5($appid.'_'.$this->secret['appkey'].'_'.$timestamp)) {
            //返回给数字地图
            self::return_dm_data(false, '参数错误:token');
            return false;
        }

        return true;
    }

    /**
     * 企业号数字地图接口返回数据处理的具体信息
     * @param bool $bool     成功失败标志
     * @param string $errmsg 失败原因
     */
    public static function  return_dm_data($bool, $errmsg = '')
    {
        if ($bool) {
            $info = array(
                    'errcode' => 0,
                    'errmsg'  => 'created',
            );

            $error_ch = 'success';
            $error_eg = 'create';

        } else {
            $info = array(
                    'errcode' => 1,
                    'errmsg'  => $errmsg,
            );

            $error_ch = 'fail';
            $error_eg = json_encode($errmsg);
        }

        //本地记录
        qydev_helper::record_error_log('dm', $error_ch, $error_eg);

        echo json_encode($info);
//         exit(0);
    }

    /**
     * 检测是否登陆
     * @param int $member_id       登陆者id
     * @param string $content_url  控制器方法路径
     * @return boolean
     */
    public static function check_login($res_name, $module, $state = '')
    {
        if(!$module) return false;

        $url = urlencode(AnUrl($module,"?state=$state"));

        //登录地址（）
        $url = AnUrl('liangliang/e_login').'?redirect_url='.$url;

        if ( !$res_name || !in_array($res_name, screen_stat_config::$res_name_arr)) {
            Response::redirect($url);
            Response::flush();
            exit();
        }

    }
}