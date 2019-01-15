<?php
/**
 * alltosun.com
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2018年1月11日:
 * Id
 */
require_once ROOT_PATH."/helper/PHPExcel.php";
require_once ROOT_PATH."/helper/PHPExcel/Writer/Excel2007.php";
class Action
{
    private $member_id  = 0;
    private $res_name   = '';
    private $res_id     = 0;
    private $qydev_user_id = '';
    private $member_info   = array();

    public function __construct()
    {
        //目前 模拟是  能进来肯定是登录了
        $this->member_id = member_helper::get_member_id();
        //获取企业号成员userid
        $this->qydev_user_id = qydev_helper::get_qydev_user_id();

        $this->member_info = member_helper::get_member_info($this->member_id);

        $yyt_name = member_helper::get_title_info($this->member_id);

        $this->res_name = $this->member_info['res_name'];
        $this->res_id   = $this->member_info['res_id'];
        $this->ranks    = $this->member_info['ranks'];

        Response::assign('yyt_name', $yyt_name);
        Response::assign('curr_member_ranks', $this->ranks);
        Response::assign('member_info', $this->member_info);
    }


    //首页数据分析
    public function __call($action = '', $param = array())
    {
        //企业号安装发消息进入统计
        $data_type = tools_helper::Get('data_type', 1);

        //登录地址（）
        $url = AnUrl('liangliang/e_login').'?redirect_url='.AnUrl('screen_dm');

        if (is_weixin() && !$this->member_id ) {
            qydev_helper::check_qydev_auth($url);
        }

        // 企业号失败重定向
        if ( !$this->member_id ) {
            e_helper::check_login($this->res_name, 'screen_dm');
        }

        //非厅权限则跳转至集团、省、市界面
        if ($this->res_name != 'business_hall') {
            Response::redirect(AnUrl('screen_dm/stat'));
            Response::flush();
            exit;
        }

        //接收变量-- today 今日  weekday 周  somedays 自定义时间
        $time_type  = tools_helper::get('time_type', 'today');
        $start_time = tools_helper::get('start_time', '');
        $end_time   = tools_helper::get('end_time', '');
        $filter     = [];
        $date       = date('Ymd');
        $start_date = $date;
        $end_date   = $date;

        //处理 开始 AND 结束 时间
        if ('weekday' == $time_type) {
            $start_date = date('Ymd', time() - 7 * 24 * 3600);;

        } else if ('somedays' == $time_type) {
            Response::assign('start_time', $start_time);
            Response::assign('end_time', $end_time);

            $start_date = date('Ymd' , strtotime($start_time));
            $end_date   = date('Ymd' , strtotime($end_time));

            if ($start_date > $end_date) {
                return '非法操作：开始时间大于结束时间';
            }
        }

        //组装filter 条件
        if ('today' == $time_type) {
            $filter['day'] = (int)$date;
        } else {
            $filter['day >'] = $start_date;
            $filter['day <='] = $end_date;
        }

        //分配后端日期  供前台使用
        Response::assign('start_date', $start_date);
        Response::assign('end_date', $end_date);

        //权限控制 分配不同的管理界面
        if ($this->member_info['res_name'] == 'group') {

        } else if ($this->member_info['res_name'] == 'province') {
            $filter['province_id'] = (int)$this->member_info['res_id'];

        } else if ($this->member_info['res_name'] == 'city') {
            $filter['city_id']     = (int)$this->member_info['res_id'];

        } else if ($this->member_info['res_name'] == 'business_hall') {
            $filter['business_id'] = (int)$this->member_info['res_id'];
            //兼容字段的问题
            $this->member_info['res_name'] = 'business';
        }
        //类型
        if ($data_type == 1) {
            $field = 'experience_time';
        } else if($data_type == 2) {
            $field = 'action_num';
        }

        $match =get_mongodb_filter($filter);
        if ( in_array($time_type, array('weekday', 'somedays')) ) {
                $aggregate_filter = array(
                    array(
                            '$match' => $match
                    ),
                    array(
                            '$group' => array(
                                            '_id'              => array('device_unique_id' => '$device_unique_id'),
                                            'device_unique_id' => array('$first' => '$device_unique_id'),
                                             $field  => array('$sum' => '$'.$field),
                                    )
                    ),
                    array(
                            '$sort' => array($field => -1)
                    )
                );

            $experience_info = _mongo('screen','screen_device_stat_day')->aggregate($aggregate_filter);
        } else {
            $experience_info = _mongo('screen', 'screen_device_stat_day')->find($filter, array('sort' => array($field => -1)));
        }
        $pic_data             = array();
        $want_experience_data = [];

        $new_data = array();
//         foreach ($experience_info as $k => $v) {
//             $device_info= _uri('screen_device' ,
//                     array('device_unique_id' => $v['device_unique_id'], 'status' => 1));

//             if (!$device_info) {
//                 continue;
//             }

//             $new_data[$device_info['device_nickanme_id']][] =
//         }

        foreach ($experience_info as $k => $v) {
            $device_info= _uri('screen_device' ,
                    array('device_unique_id' => $v['device_unique_id'], 'status' => 1));

            if (!$device_info) {
                continue;
            }
            if($field == 'experience_time'){
                $experience_time = screen_helper::format_timestamp_text($v[$field]);
                $want_experience_data[$k][$field]  = $experience_time;
            }else{
                $want_experience_data[$k][$field]  = $v[$field];

            }

            $want_experience_data[$k]['device_unique_id'] = $v['device_unique_id'];
//             $want_experience_data[$k]['date']            = $v['day'];

            if ($device_info['phone_name_nickname']) {
                $want_experience_data[$k]['phone_name'] = $device_info['phone_name_nickname'];
            } else {
                $want_experience_data[$k]['phone_name'] = $device_info['phone_name'];
            }

            //判断手机的型号
            if ($device_info['phone_version_nickname']) {
                $want_experience_data[$k]['phone_version'] = $device_info['phone_version_nickname'];
            } else {
                $want_experience_data[$k]['phone_version'] = $device_info['phone_version'];
            }

            //饼状图目前只展示6个
            if ($k > 5) {
                continue;
            }
            $color = screen_config::$pie_chart_color[$k];
            //饼状图的数据
//             $pic_data  .= '{';
//             $pic_data  .= 'value:'.$v[$field].', name: "'.$device_info['phone_name'].' '.$device_info['phone_version'];
//             $pic_data  .= '},';
            $pic_data[] =array(
                    'value' => $v[$field],
                    'name' => $device_info['phone_name'].' '.$device_info['phone_version'],
            );
        }

        Response::assign('pic_color', json_encode(screen_config::$pie_chart_color));
        //饼状图
        if ($pic_data) {
            Response::assign('pic_data', json_encode($pic_data));
        } else {
            //Response::assign('pic_data', "{value : 100, name:'暂无数据'}");
            $arr[] = array(
                    'value' => 100,
                    'name' => '暂无数据',
            );
            Response::assign('pic_data', json_encode($arr));
        }

        //时间类型
        Response::assign('time_type', $time_type);
        //所有事用到的时间
        Response::assign('date', $date);
        //年月日
        Response::assign('year', substr($date, 0, 4));
        Response::assign('month', substr($date, 4, 2));
        Response::assign('day', substr($date, 6, 2));
        //类型
        Response::assign('data_type',$data_type);

        Response::assign('start_year', substr($start_date, 0, 4));
        Response::assign('start_month', substr($start_date, 4, 2));
        Response::assign('start_day', substr($start_date, 6, 2));

        Response::assign('end_year', substr($end_date, 0, 4));
        Response::assign('end_month', substr($end_date, 4, 2));
        Response::assign('end_day', substr($end_date, 6, 2));

        Response::assign('year', substr($date, 0, 4));
        Response::assign('month', substr($date, 4, 2));
        Response::assign('day', substr($date, 6, 2));
        //饼状图信息
        Response::assign('pie_chart_info' , array_slice($want_experience_data, 0, 6));
        Response::assign('pie_chart_color', screen_config::$pie_chart_color);

        Response::assign('list', $want_experience_data);
        Response::display('data_stat2.html');
    }

    /**
     * 亮屏状态
     */
    public function device()
    {

        // 微信授权失败后手动登录标志
        $state       = tools_helper::get('state', '');
        // 权限不同权限不同
        $is_auth     = tools_helper::get('is_auth', 0);
        $user_number = tools_helper::get('user_number', '');

        if ( $is_auth && $user_number ) {
            $member_info = _model('member')->read(array('member_user' => $user_number));

            if ( $member_info ) {
                $this->member_info = $member_info;
                $this->member_id   = $member_info['id'];
                $this->res_name    = $member_info['res_name'];
                $this->res_id      = $member_info['res_id'];
            }
        }


        //获取企业号用户ID
        $qyde_user_id = qydev_helper::get_qydev_seession_user_id();

        $url = AnUrl('liangliang/e_login').'?redirect_url='.urlencode(AnUrl('screen_dm/device'));
        // 不是手动登录
        if ( is_weixin() && !$this->member_id ) {
            wework_helper::check_wework_auth($url, 21);
        }

        //非厅权限则跳转至集团、省、市界面
        if ($this->res_name != 'business_hall') {
            Response::redirect(AnUrl('screen_dm/status'));
            Response::flush();
            exit;
        }

        $date   = tools_helper::get('date', date('Ymd'));
        $status = tools_helper::get('status', 3);
        $filter = $record_filter = $stat_filter = array();


        // 读消息的记录表
        if ( $state && in_array($state, array('install', 'check', 'offline')) ) {
            $record_filter = array (
                    'res_name'         => $state,
                    'business_hall_id' => $this->member_info['res_id'],
                    'touser'           => $qyde_user_id ? $qyde_user_id : 'wx_error',
                    'date'             => date("Ymd"),
                    'month'            => date('Ym'),
                    'type'             => 1
            );
            _model ( 'screen_qydev_msg_record' )->create ( $record_filter );
        }

        if ( $state == 'install' ) {
            $order = 'add_time';
            // 今日在线量
            $status = 1;
        } else if ( $state == 'check' ) {
            $order = 'day';
            // 今日离线量
            $status = 0;
        } else if ( $state == 'offline' ) {
            $order = 'offline_days';
            // 今日离线量
            $status = 0;
        }else if($state == 'all'){
            $order = 'day';
            $status = 2;
        }else {
//             $order = 'offline_days';
            $order = 'day';
            // 今日离线量
            $status = 0;
        }

        if ( time() < strtotime($date) ) {
            $date = date('Ymd');
        }

        // 只要上架状态
        $filter['status'] = 1;

        //权限
        if ($this->member_info['res_name'] == 'province') {
            $filter['province_id'] = $this->member_info['res_id'];
        } else if ($this->member_info['res_name'] == 'city') {
            $filter['city_id']     = $this->member_info['res_id'];
        } else if ($this->member_info['res_name'] == 'area') {
            $filter['area_id']     = $this->member_info['res_id'];
        } else if ($this->member_info['res_name'] == 'business_hall') {
            $filter['business_id'] = $this->member_info['res_id'];
        }

        //所有设备
        $device_list =  _model('screen_device')->getList($filter);
        //在线、离线的数量
        $all_num = $online_num = $offline_num = 0;
        //离线在线的数据
        $online_list = $offline_list = array();


        foreach ($device_list as $k => &$v) {
            //在线数据查询添加条件  时间和device_unique_id
            $stat_filter['day']              = $date;
            $stat_filter['device_unique_id'] = $v['device_unique_id'];
            $device_online_stat_info = _model('screen_device_online_stat_day')->read($stat_filter);
            $v['offline_days']= '';
            //有设备，但在线无数据
            if ( !$device_online_stat_info ) {
                ++$offline_num;
                $v['offline_days'] = screen_helper::by_device_unique_id_get_offline_time($device_online_stat_info, $v['device_unique_id']);

                array_push( $offline_list, $v );
                continue;
            } else {
                if ( (strtotime($device_online_stat_info['update_time'])+ 1800) < time() ) {
                    ++$offline_num;
                    $v['is_online']   = '离线';
                    $v['online_time'] = screen_helper::by_device_unique_id_get_offline_time($device_online_stat_info, $v['device_unique_id']);
                    array_push($offline_list, $v);
                } else {
                    ++$online_num;
                    $v['is_online']   = '在线';
                    $v['online_time'] = screen_helper::format_timestamp_text($device_online_stat_info['online_time']);
                    array_push($online_list, $v);
                }
            }
        }
        //第一次展示如果没有离线设备  默认查看在线设备
        if($status == 3 && !$offline_list){
            $status = 1;
        }
        
        // 倒序
        $list = screen_helper::myself_sort($device_list, $order, 'desc');

        //年月日
        Response::assign('year', substr($date, 0, 4));
        Response::assign('month', substr($date, 4, 2));
        Response::assign('day', substr($date, 6, 2));
        //状态数量
        Response::assign('status' , $status);
        Response::assign('all_num' , count($list));
        Response::assign('online_num' , $online_num);
        Response::assign('offline_num' , $offline_num);
        Response::assign('list' , $list);
        Response::assign('online_list' , $online_list);
        Response::assign('offline_list' , $offline_list);
        Response::assign('date' , $date);

        Response::display('device2.html');
    }



    /**
     * 内容管理价格管理
     */
    public function screen_price(){

        // 微信授权失败后手动登录标志
        $state        = tools_helper::get('state', '');
        //获取企业号用户ID
        $qyde_user_id = qydev_helper::get_qydev_seession_user_id();

        //验证是否登陆
        $module = 'screen_dm/screen_price';
        e_helper::check_login($this->res_name, $module,$state);

        // 不是手动登录
        //         if (is_weixin() &&  ( !$this->member_id || !$qyde_user_id ) ) {
        //             qydev_helper::check_qydev_auth($url);
        //         }

//         if ( is_weixin() && !$this->member_id ) {
//             qydev_helper::check_qydev_auth($url);
//         }

        $date   = tools_helper::get('date' , date('Ymd'));
        $status = tools_helper::get('status', 0);
        $state  = tools_helper::get('state', '');

        $filter = $record_filter = $stat_filter = array();

        // 只要上架状态
        $filter['status'] = 1;

        //权限
        if ($this->member_info['res_name'] == 'province') {
            $filter['province_id'] = $this->member_info['res_id'];
        } else if ($this->member_info['res_name'] == 'city') {
            $filter['city_id']     = $this->member_info['res_id'];
        } else if ($this->member_info['res_name'] == 'area') {
            $filter['area_id']     = $this->member_info['res_id'];
        } else if ($this->member_info['res_name'] == 'business_hall') {
            $filter['business_id'] = $this->member_info['res_id'];
        }

        //所有设备
        $list =  _model('screen_device')->getList($filter);
        $content_info = array();
        //获取可以修改价格的设备  机型合成图
        foreach ($list as $k => $v){
            $content_info = $this->get_type4_content_by_device($v['business_id'], $v['phone_name'], $v['phone_version']);
            if($content_info){
               //$list['content_res_id'] = $content_info['content_res_id'];
            }else{
               unset($list[$k]);
           }
        }
        //状态数量
        Response::assign('status' , $status);
        Response::assign('list' , $list);
        Response::assign('num' , count($list));
        Response::assign('date' , $date);
        Response::display('details2.html');
    }
    

    /**
     * 添加内容页面
     */
    public function add()
    {
        $is_edit = 0;
        $all_checked = 1;
        if ($this->res_name == 'group') {
            $phone_version_list = _model('screen_device')->getList("GROUP BY phone_version ");
            Response::assign('group_id', $this->res_id);

        } elseif ($this->res_name == 'province') {
            $phone_version_list = _model('screen_device')->getList(array('province_id'=>$this->res_id), "GROUP BY phone_version");
            Response::assign('province_id', $this->res_id);
        } elseif($this->res_name == 'city'){
            $phone_version_list = _model('screen_device')->getList(array('city_id'=>$this->res_id), "GROUP BY phone_version");
            Response::assign('city_id', $this->res_id);
        }elseif ($this->res_name == 'area'){
            $phone_version_list = _model('screen_device')->getList(array('area_id'=>$this->res_id), "GROUP BY phone_version");
            Response::assign('area_id', $this->res_id);
        }else{
            $phone_version_list = _model('screen_device')->getList(array('business_id'=>$this->res_id), "GROUP BY phone_version");
            Response::assign('business_id', $this->res_id);
        }
        //var_dump($phone_version_list);exit;
        Response::assign('is_edit', $is_edit);
        Response::assign('all_checked', $all_checked);
        Response::assign('phone_version_list', $phone_version_list);
        Response::display("add_content.html");
    }


    /**
     * 添加内容
     */
    public function save()
    {
        $content_id     = Request::Post('id', 0);
        $content        = Request::Post('content', array());
        $phone          = Request::Post('phone', array());
        $put_type       = Request::Post('put_type', 2);
        $search_type    = Request::Post('search_type', 0);
        $is_all         = Request::Post('is_all', 0);
        $put_flage         = Request::Post('put_flage', 0);//0 发布改变了投放机型 1 不发布只修改内容
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
            if (!empty($_FILES['img_link']['tmp_name'])) {
                $link = upload_file($_FILES['img_link'],false, 'focus');
                //生成缩略图
                _widget('screen_content')->make_thumb($link);
            }

            //宣传图//机型宣传图价格的处理（可选）
            if ( $content['type'] == 4 ) {
                if (!isset($content['font_color_type']) || !$content['font_color_type']) {
                    return '请选择字体颜色';
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
                    return array($link_info['msg']);
                }

                $link = $link_info['file'];
            }
        }else if ($content['type'] == 5) {

            //只有添加的时候可以导入套餐信息
            if (!$content_id) {
                //套餐信息excel数据导入
                $data = $this->upload_set_meal();
                if (!is_array($data)) {
                    return $data;
                }
                //获取上传的手机名和型号
                foreach ($data as $k => $v){
                    $phone[$k]['name']=$v['phone_name'];
                    $phone[$k]['version']=$v['phone_version'];
                }

            }
            $link = Request::Post('set_meal', '');

        }

        $type = $content['type'];

        if ($link) {
            $content['link'] = $link;
        }
        //修改
        if ($content_id) {
            $content_info = _uri('screen_content', $content_id);
            if (!$content_info) {
                return '对不起，该信息不存在';
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

             _model('screen_content')->update($content_id, $content);
            $param = array(
                    'business_hall_ids' => array(0),
                    'province_id'      => 0,
                    'city_id'      => 0,
                    'area_id'      => 0,
                    'content_id'   => $content_id,
                    'phone_name'   => '',
                    'phone_version' => '',
            );
            if ($this->member_info['res_name'] == 'business_hall') {

                $business_hall = _uri('business_hall', $this->res_id);
                if (!$business_hall) {
                    return '非法营业厅';
                }
                $param['business_hall_ids'] = array($business_hall['id']);
                $param['area_id']        = $business_hall['area_id'];
                $param['city_id']        = $business_hall['city_id'];
                $param['province_id']    = $business_hall['province_id'];

            } else if ($this->member_info['res_name'] == 'area') {
                $area_info = _uri('area', $this->res_id);
                if (!$area_info) {
                    return '非法归属地';
                }
                $param['area_id']        = $area_info['id'];
                $param['city_id']        = $area_info['city_id'];
                $param['province_id']    = $area_info['province_id'];

            } else if ($this->member_info['res_name'] == 'city') {
                $city_info = _uri('city', $this->res_id);
                if (!$city_info) {
                    return '非法归属地';
                }
                $param['city_id']        = $city_info['id'];
                $param['province_id']    = $city_info['province_id'];

            } else if ($this->member_info['res_name'] == 'province') {
                $province_info = _uri('province', $this->res_id);
                if (!$province_info) {
                    return '非法归属地';
                }
                $param['province_id']        = $province_info['id'];
            }
            if (!$is_all) {
                //获取推送参数
                foreach ($phone as $k => $v){
                    $param['phone_name']     = $v['name'];
                    $param['phone_version']  = $v['version'];
                    //某一机型全部有删除因为会把每个机型加一遍
                    $phone_flag = _model('screen_content_res')->read(array('content_id'=>$content_id,'phone_name' => $v["name"],'phone_version'=>''));
                    if($phone_flag){
                        _model('screen_content_res')->delete(array('content_id'=>$content_id,'phone_name'=>$param["phone_name"],'phone_version'=>''));
                    }
                    $result = _widget('screen_dm.put')->put_content($param);
                    if($result != 'ok'){
                        return $result;
                    }
                }
            } else {
                _model('screen_content_res')->delete(array('content_id' => $content_id));
                $result = _widget('screen_dm.put')->put_content($param);
                if($result != 'ok'){
                    return $result;
                }
            }
        } else {

            if (!$content['link']) {
                return '请上传或输入发布内容';
            }
            $content['res_name']  = $this->res_name;
            $content['res_id']  = $this->res_id;
            $content['member_id'] = $this->member_id;

            //非宣传图则默认发布
//             if ($type != 4) {
//                 $content['status']    = $put_type==0?$put_type:1;   //默认发布 -wangjf
//             }

           //投放的营业厅列表
           $business_hall_ids = array();
           //投放的内容
           $content_id = _model('screen_content')->create($content);

           //设置套餐参数
           if ($type == 5) {
               $is_all = 0;
               $this->save_set_meal($data, $content_id);
           }
           if (!$content_id) {
               return '内容添加失败';
           }

           $param = array(
                   'business_hall_ids' => array(0),
                   'province_id'      => 0,
                   'city_id'      => 0,
                   'area_id'      => 0,
                   'content_id'   => $content_id,
                   'phone_name'   => '',
                   'phone_version' => '',
           );
           if ($this->member_info['res_name'] == 'business_hall') {

               $business_hall = _uri('business_hall', $this->res_id);
               if (!$business_hall) {
                   return '非法营业厅';
               }
               $param['business_hall_ids'] = array($business_hall['id']);
               $param['area_id']        = $business_hall['area_id'];
               $param['city_id']        = $business_hall['city_id'];
               $param['province_id']    = $business_hall['province_id'];

           } else if ($this->member_info['res_name'] == 'area') {
               $area_info = _uri('area', $this->res_id);
               if (!$area_info) {
                   return '非法归属地';
               }
               $param['area_id']        = $area_info['id'];
               $param['city_id']        = $area_info['city_id'];
               $param['province_id']    = $area_info['province_id'];

           } else if ($this->member_info['res_name'] == 'city') {
               $city_info = _uri('city', $this->res_id);
               if (!$city_info) {
                   return '非法归属地';
               }
               $param['city_id']        = $city_info['id'];
               $param['province_id']    = $city_info['province_id'];

           } else if ($this->member_info['res_name'] == 'province') {
               $province_info = _uri('province', $this->res_id);
               if (!$province_info) {
                   return '非法归属地';
               }
               $param['province_id']        = $province_info['id'];
           }

           if (!$is_all) {
               //获取推送参数
               foreach ($phone as $k => $v){
                   $param['phone_name']     = $v['name'];
                   $param['phone_version']  = $v['version'];
                   $result = _widget('screen_dm.put')->put_content($param);
                   if($result != 'ok'){
                       return $result;
                   }
               }
           } else {
               $result = _widget('screen_dm.put')->put_content($param);
           }

           if($result != 'ok'){
               return $result;
           }
            //更新发布状态 (已在 widget: 中推送 )
            _model('screen_content')->update($content_id, array('status' => 1));
            //直接合成套餐图
            if($type == 5){
                $this->compose_set_meal_photo_all($content_id);
            }
        }

        Response::redirect(AnUrl("screen_dm/screen_content?search_filter[search_type]=0"));
    }


    /**
     * 内容投放列表
     */
    public function screen_content()
    {

        // 内容展示必须符合各省的条件
        $search_filter = Request::Get('search_filter', array());
        $state        = tools_helper::get('state', '');
        $order         = " ORDER BY `view_order` ASC,`add_time` DESC ";
        $limit         = 3;
        //控制器方法路径
        $module  = 'screen_dm/screen_content';
        //检测是否登陆
        e_helper::check_login($this->res_name, $module,$state);


        $filter =array();
        $default_value  = array(
                //各省投放列表start
                'type'          => 1,
                'search_type'   => 0,
                'put_type'      => 1
        );

        $search_filter  = set_search_filter_default_value($search_filter, $default_value);

        //组装条件
        if ($search_filter['put_type'] == 1) {
            $filter['res_name'] = $this->res_name;
            $filter['res_id']   = $this->res_id;

        } else if ($search_filter['put_type'] == 2) {
            $province_id      = city_helper::get_province_id($this->res_name, $this->res_id);

            $content_res_filter['province_id'] = array(0,$province_id);

            $content_res_filter['ranks <'] = $this->ranks;

            $content_ids        = _model('screen_content_res')->getFields('content_id', $content_res_filter);

            array_unique($content_ids);

            if (empty($content_ids)) {
                $filter['id'] = 0;
            } else {
                $filter['id'] = $content_ids;
            }
        } else {
            $province_id      = city_helper::get_province_id($this->res_name, $this->res_id);

            $content_res_filter['province_id'] = array($province_id);
            $content_res_filter['ranks >'] = $this->ranks;

            if ($this->res_name == 'group') {
                $content_res_filter= array('ranks >' => 1);
            }

            $content_ids        = _model('screen_content_res')->getFields('content_id', $content_res_filter);
            array_unique($content_ids);

            if (empty($content_ids)) {
                $filter['id'] = 0;
            } else {
                $filter['id'] = $content_ids;
            }
        }

        if ($search_filter['search_type'] == 0) {
            $filter['status <']        = 2;
        } elseif ($search_filter['search_type'] == 1) {
            $filter['start_time <=']   = $this->time;
            $filter['end_time >']      = $this->time;
            $filter['status']          = 1;

        } elseif ($search_filter['search_type'] == 2) {
            $filter['end_time <=']     = $this->time;
            $filter['status <']        = 2;

        } elseif ($search_filter['search_type'] == 3) {
            $filter['start_time >']   = $this->time;
            $filter['status <']        = 2;

        } elseif ($search_filter['search_type'] == 4) {
            $filter['start_time <=']   = $this->time;
            $filter['end_time >']     = $this->time;
            $filter['status']          = 0;
        } elseif ($search_filter['search_type'] == 5) {
            $filter['status']          = 2;
        }



        //end
        $content_list = array();
        $content_count = _model('screen_content')->getTotal($filter);

        if ($content_count) {
            $content_list = _model('screen_content')->getList($filter, $order.' LIMIT ' .$limit);

            Response::assign('content_list', $content_list);
        }

        //后加没有机型不允许添加
        if ($this->res_name == 'group') {
            $phone_version_list = _model('screen_device')->getList("GROUP BY phone_version ");


        } elseif ($this->res_name == 'province') {
            $phone_version_list = _model('screen_device')->getList(array('province_id'=>$this->res_id), "GROUP BY phone_version");
        } elseif($this->res_name == 'city'){
            $phone_version_list = _model('screen_device')->getList(array('city_id'=>$this->res_id), "GROUP BY phone_version");
        }elseif ($this->res_name == 'area'){
            $phone_version_list = _model('screen_device')->getList(array('area_id'=>$this->res_id), "GROUP BY phone_version");
        }else{
            $phone_version_list = _model('screen_device')->getList(array('business_id'=>$this->res_id), "GROUP BY phone_version");
        }

        Response::assign('allnum', $content_count);
        Response::assign('filter', $filter);
        Response::assign('put_type', $search_filter['put_type']);
        Response::assign('phone_version_list', $phone_version_list);
        Response::assign('search_filter', $search_filter);
        Response::display('detail_content.html');
    }


    public function edit()
    {
        $id          = Request::Get('id', 0);
        $search_type = Request::Get('search_type', 1);
        //是否是修改
        $is_edit     = 1;
        //是否是全部投放
        $all_checked = 0;
        if (!$id) {
            return '请选择您要操作的信息';
        }

        $content_info = _uri('screen_content', $id);
        if (!$content_info || $content_info['status'] == 2) {
            return '您操作的信息不存在';
        }

        $type = 0;  // 1-静态 2-动态

        //链接
        if ($content_info['type'] == 3) {
            $type = 1;
            //图片、机型宣传图、视频
        } else if ($content_info['type'] == 1 || $content_info['type'] == 4){
            $link_path = UPLOAD_PATH.'/'.$content_info['link'];
            //是否为动图
            if (screen_content_helper::is_animated_gif($link_path)){
                $type = 2;
            } else {
                $type = 1;
            }

        } else if ($content_info['type'] == 2) {
            $type = 2;
        }

        $content_info['is_roll_num_disabled']       = true;
        $content_info['is_roll_interval_disabled']  = true;

        // 1-静态 2-动态
        if ($type == 1) {
            $content_info['is_roll_interval_disabled']       = false;
        } else if ($type == 2){
            $content_info['is_roll_num_disabled']  = false;
        }
        if ($this->res_name == 'group') {
            $phone_version_list = _model('screen_device')->getList("GROUP BY phone_version ");
            Response::assign('group_id', $this->res_id);

        } elseif ($this->res_name == 'province') {
            $phone_version_list = _model('screen_device')->getList(array('province_id'=>$this->res_id), "GROUP BY phone_version");
            Response::assign('province_id', $this->res_id);
        } elseif($this->res_name == 'city'){
            $phone_version_list = _model('screen_device')->getList(array('city_id'=>$this->res_id), "GROUP BY phone_version");
            Response::assign('city_id', $this->res_id);
        }elseif ($this->res_name == 'area'){
            $phone_version_list = _model('screen_device')->getList(array('area_id'=>$this->res_id), "GROUP BY phone_version");
            Response::assign('area_id', $this->res_id);
        }else{
            $phone_version_list = _model('screen_device')->getList(array('business_id'=>$this->res_id), "GROUP BY phone_version");
            Response::assign('business_id', $this->res_id);
        }

        //已选机型
        $edit_phone_version_list = _model('screen_content_res')->getFields('phone_version',array('content_id' =>$id));
        $edit_phone_name_list = _model('screen_content_res')->getFields('phone_name',array('content_id' =>$id));
        $edit_phone_name_list2 = _model('screen_content_res')->getList(array('content_id' =>$id));
        $checked_phone = $checked_phone2 = $version_list = array();
        //如果投放
        if($edit_phone_name_list2){
            foreach ($edit_phone_name_list2 as $k => $v){
                //该型号全部机型
                if($v['phone_name'] && !$v['phone_version']){
                    $checked_phone = $this->get_version_list($v['phone_name']);
               }else if($v['phone_name'] && $v['phone_version']){
                    $checked_phone_tmp = $this->get_version_list_by_version($v['phone_name'], $v['phone_version']);
                    $checked_phone2[] = $checked_phone_tmp;
               }

            }
            if($checked_phone && $checked_phone2){
                foreach ($checked_phone2 as $k => $v){
                    array_push($checked_phone,$v);
                }
            }
            if(!$checked_phone && $checked_phone2){
                foreach ($checked_phone2 as $k => $v){
                    $checked_phone[] = $v;
                }
            }

            //获取所有选择型号
            foreach($checked_phone as $k => $v){
                $version_list[] = $v['phone_version'];
            }
        }

        $version_list = array_unique($version_list);
        sort($version_list);
        $num = count($version_list);

        $all = _model('screen_content_res')->getList(array('content_id' =>$id,'phone_version' =>'','phone_name' =>''));
        if($all){
            $num = 1;
            $all_checked = 1;
        }
//         if($num == 1){
//             if($edit_phone_version_list[0] == '' || $edit_phone_version_list[0] == 'all'){
//                 $all_checked = 1;
//             }
//         }
        Response::assign('is_edit', $is_edit);
        Response::assign('num', $num);
        Response::assign('all_checked', $all_checked);
        Response::assign('search_type', $search_type);
        Response::assign('version_list', $version_list);
        Response::assign('phone_version_list', $phone_version_list);
        Response::assign('edit_phone_name_list', $edit_phone_name_list);
        Response::assign('edit_phone_version_list', $edit_phone_version_list);
        Response::assign('content_info', $content_info);

        
        Response::display("edit_content.html");
    }



    /**
     * 获取版本列表
     */
    public function get_version_list($phone_name)
    {
        $filter = array(
                'phone_name' => $phone_name,
                'status'     => 1
        );

        $version_list = _model('screen_device')->getList($filter, " GROUP BY `phone_version`");

        return $version_list;
    }


    /**
     * 获取版本列表
     */
    public function get_version_list_by_version($phone_name,$phone_version)
    {
        $filter = array(
                'phone_name' => $phone_name,
                'phone_version' => $phone_version,
                'status'     => 1
        );

        $version_list = _model('screen_device')->read($filter, " GROUP BY `phone_version`");
        if(empty($version_list)){
            //从昵称表里取
            $nickname_info = _model('screen_device_nickname')->read(array('name_nickname' => $phone_name,'version_nickname'=>$phone_version));
            $filter=array(
                    'phone_name' => $nickname_info['phone_name'],
                    'phone_version' => $nickname_info['phone_version'],
                    'status'     => 1
            );
            $version_list = _model('screen_device')->read($filter, " GROUP BY `phone_version`");
        }
        return $version_list;
    }
    /**
     * 根据设备信息获取宣传内容
     */
    public function get_type4_content_by_device($business_id, $phone_name, $phone_version)
    {

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
    //终端体验详情
    public function detail()
    {
        $date             = Request::get('date' , '');
        $start_date       = Request::get('start_date' , '');
        $end_date         = Request::get('end_date' , '');
        $device_unique_id = Request::get('device_unique_id' , '');
        //         $time        = Request::get('time' , '');

        $filter = [];

        $date = (int) date('Ymd', strtotime($date));

        //搜索  和 今天
        if ($date) {
            $filter['day'] = $date;
        }

        //搜索同一天
        if ($start_date && $end_date && $start_date == $end_date ) {
            //覆盖上面
            $filter['day'] = (int)$start_date;
        }

        //一段时间  AND 近7天
        if ($start_date && $end_date && $start_date != $end_date) {
            //             $filter['day >='] = $start_date;
            //             $filter['day <='] = $end_date;
            //             unset($filter['day']);
            $filter['day'] = array('$gte' => (int)$start_date, '$lte' => (int)$end_date);
        }

        if (!$device_unique_id) {
            return '未找到设备';
        }

        $filter['device_unique_id'] = $device_unique_id;

        //权限
        if ($this->member_info['res_name'] == 'province') {
            $filter['province_id'] = (int)$this->member_info['res_id'];

        } else if ($this->member_info['res_name'] == 'city') {
            $filter['city_id']     = (int)$this->member_info['res_id'];

        } else if ($this->member_info['res_name'] == 'area') {
            $filter['area_id']     = (int)$this->member_info['res_id'];

        } else if ($this->member_info['res_name'] == 'business_hall') {
            $filter['business_id'] = (int)$this->member_info['res_id'];
        }

        //type=2位一次动作完成
        $filter['type'] = (int)2;

        $device_experience_list = array();

        //$device_experience_list = _model('screen_action_record')->getList($filter);

        $mg_device_experience_list = _mongo('screen', 'screen_action_record')->find($filter);

        $device_experience_list = $mg_device_experience_list->toArray();

        //总时间体验
        $total_time = 0;
        foreach ($device_experience_list as $k => $v) {
            $total_time += $v['experience_time'];
        }

        //处理时间戳
        $time = screen_helper::format_timestamp_text($total_time);

        Response::assign('time' , $time);
        Response::assign('date' , $date);
        Response::assign('device_unique_id' , $device_unique_id);

        //
        if ($start_date && $end_date && $start_date != $end_date) {
            Response::assign('start_date', date('Y年n月j日', strtotime($start_date)));
            Response::assign('end_date', date('Y年n月j日', strtotime($end_date)));
        } else if ($start_date && $end_date && $start_date == $end_date) {
            Response::assign('everyday' , date('Y年n月j日', strtotime($start_date)));
        } else {
            Response::assign('everyday' , date('Y年n月j日', strtotime($date)));
        }

        Response::assign('list' , $device_experience_list);
        Response::display('detail.html');
    }

    /**
     * 保存套餐信息
     */
    public function save_set_meal($data, $content_id)
    {
        //查询内容
        $content_info = _model('screen_content')->read($content_id);

        if (!$content_info) {
            return false;
        }

        foreach ($data as $v) {

            $v['res_link']  = $content_info['link'];
            $v['content_id'] = $content_info['id'];
            $v['link']      = '';
            $v['issuer_res_name'] = $this->res_name;
            $v['issuer_res_id']   = $this->res_id;

            _model('screen_content_set_meal')->create($v);
        }

        return true;
    }


    /**
     * 上传套餐信息
     */
    public function upload_set_meal()
    {
        if (!isset($_FILES['set_meal_data']['name']) || !$_FILES['set_meal_data']['name']) {
            return '请选择上传的Excel文件';
        }
        $file = $_FILES['set_meal_data'];

        if (!$file['name']) {
            return '请选择上传的Excel文件';
        }

        $allow_type = Config::get('allow_type');

        $upload_path = UPLOAD_PATH;

        $fail_msg = check_upload($file, 0, 1);

        if ($fail_msg) {
            return $fail_msg;
        }

        $ext = substr($file['name'], strrpos($file['name'], '.')+1);

        if (!in_array(strtolower($ext), $allow_type)) {
            return '文件格式错误';
        }


        if (empty($fail_msg)) {
            $file_path = an_upload($file['tmp_name'], $ext);
        }

        $file_path = ROOT_PATH.'/upload'.$file_path;

        require_once MODULE_CORE.'/helper/reader.php';

        if (!file_exists($file_path)) {
            return '文件格式不正确';
        }

        $phpexcel = new Spreadsheet_Excel_Reader();
        $phpexcel->setOutputEncoding('CP936');
        $phpexcel->read($file_path);//正式机
        $results = $phpexcel->sheets[0]['cells'];
        $cols = $phpexcel->sheets[0]['numCols'];
        $rows = $phpexcel->sheets[0]['numRows'];

        //Excel第行 需要去掉
        array_shift($results);

        $data = array();

        foreach ($results as $k => $v) {
            //状态， 默认0
            $status = 0;
            //转码
            for($i = 1; $i <= $cols; $i ++) {
                //卖点不判断为空
                if (!isset($v[$i]) ||  !$v[$i]) {
                    $rows = $k + 1;
                    //return "第{$rows}行{$i}列存在空项或参数不全";

                }
                if (!isset($v[$i]) || !$v[$i]) {
                    $v[$i] = '';
                }

                $v[$i] = iconv('GB2312', 'UTF-8//TRANSLIT//IGNORE', $v[$i]);
                $v[$i] = trim($v[$i]);

                //卖点
                if ($i < 9 && !$v[$i]) {
                    $status = 3;
                }

            }

            if (count($v) != 14) {
                return '参数条数不正确';
            }

            //查询机型信息
            $device_nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v[1], 'version_nickname' => $v[2]));

            if ( !$device_nickname ) {
                //return "暂未查到 ”{$v['3']} {$v['4']}“ 设备信息";
                $status = 4;
            }

            $new_data = array(
                    'phone_name'    => $v['1'],
                    'phone_version' => $v['2'],
                    'retail_price'  => $v['3'],  //零售价
                    'contract_price' => $v['4'],  //合约价
                    'recommended_position' => $v['5'], //推荐档位
                    'selling_point_1' => '', //买点1
                    'selling_point_2' => '', //买点1
                    'selling_point_3' => '', //买点1
                    'selling_point_4' => '', //买点1
                    'selling_point_5' => '', //买点1
                    'selling_point_6' => '', //买点1
                    'param_1'       => $v['9'], //设备参数1
                    'param_2'       => $v['10'], //设备参数2
                    'param_3'       => $v['11'], //设备参数3
                    'param_4'       => $v['12'], //设备参数4
                    'param_5'       => $v['13'], //设备参数5
                    'param_6'       => $v['14'], //设备参数6
                    'status'        => $status
            );


            //卖点
            $selling_point = explode("\n", $v['6']);
            if (isset($selling_point[1])) {
                $new_data['selling_point_1'] = $selling_point[0];
                $new_data['selling_point_2'] = $selling_point[1];
            } else {
                $new_data['selling_point_1'] = $selling_point[0];
            }

            //卖点
            $selling_point = explode("\n", $v['7']);
            if (isset($selling_point[1])) {
                $new_data['selling_point_3'] = $selling_point[0];
                $new_data['selling_point_4'] = $selling_point[1];
            } else {
                $new_data['selling_point_3'] = $selling_point[0];
            }

            //卖点
            $selling_point = explode("\n", $v['8']);
            if (isset($selling_point[1])) {
                $new_data['selling_point_5'] = $selling_point[0];
                $new_data['selling_point_6'] = $selling_point[1];
            } else {
                $new_data['selling_point_5'] = $selling_point[0];
            }

            //查询营业厅
            $data[] = $new_data;
        }
        return $data;
    }

    /**
     * 范围投放
     */
    public function region_range_put($content_id)
    {
        $param = array(
                'province_id' => 0,
                'city_id'     => 0,
                'area_id'     => 0,
                'business_hall_ids' => array(0),
                'phone_name'  => '',
                'phone_version' => '',
                'content_id'  => $content_id
        );

        //省
        if ($this->member_res_name == 'province') {
            $param['province_id'] = $this->member_res_id;
            //市
        } else if ($this->member_res_name == 'city') {
            $city_info = _uri('city', $this->member_res_id);
            if (!$city_info) {
                return false;
            }

            $param['city_id']       = $this->member_res_id;
            $param['province_id']   = $city_info['province_id'];
            //区
        } else if ($this->member_res_name == 'area') {
            $area_info = _uri('area', $this->member_res_id);
            if (!$area_info) {
                return false;
            }
            $param['area_id']       = $this->member_res_id;
            $param['city_id']       = $area_info['city_id'];
            $param['province_id']   = $area_info['province_id'];
        } else if ($this->member_res_name == 'business_hall') {
            $business_hall_info = _model('business_hall')->read($this->member_res_id);
            if (!$business_hall_info) {
                return false;
            }
            $param['business_hall_ids']   = array($this->member_res_id);
            $param['area_id']       = $business_hall_info['area_id'];
            $param['city_id']       = $business_hall_info['city_id'];
            $param['province_id']   = $business_hall_info['province_id'];
        }

        return _widget('screen_content.put')->put_content($param);

    }

    /**
     * 合套餐图
     * @param unknown $content_id
     * @return string[]|boolean
     */
    public function compose_set_meal_photo_all($content_id)
    {
        $set_meal_list = _model('screen_content_set_meal')->getList(array('content_id' => $content_id));
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
        return true;
    }
}