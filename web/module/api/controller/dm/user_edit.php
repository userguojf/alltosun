<?php
/**
 * alltosun.com  user_edit.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-7-14 下午12:33:31 $
 * $Id$
 */
class Action
{
    private $appids = array(
            'wifi_pzclub_awzdxhyadrtggbrd',//集团纠错平台
            'wifi_shujdt_awzdxhyadrtggbrd',//数字地图
            'wifi_qydev_xdfeafgklkjsldf'
    );

    private $from_where = array(
            'wifi_pzclub_awzdxhyadrtggbrd' => 1,//集团纠错平台
            'wifi_shujdt_awzdxhyadrtggbrd' => 2,//数字地图
            'wifi_qydev_xdfeafgklkjsldf' => 3 // wifi平台
    );

    //秘钥 复制的

    //电信营业厅部门下的部门ID（二级部门）
    private $department_ids = array(
            2,    //数字地图
            4,    //集团公司
            145,  //爱WIFI
            6,    //O2O事业部
            147,  //ibeacon
            211,   //一体化排队
            30274, // 测试用户
            30348, //手机亮屏
    );


    /**
     * 业务线的操作
     */
    private $operation = array(
            1 => 'create',
            2 => 'update'
    );

    //操作类型
    private $type       = '';
    private $api_log_id = '';
    private $errmsg_arr = [];

    private $province            = '';
    private $business_hall_title = '';
    private $user_number         = '';
    private $name                = '';
    private $phone               = '';
    private $depart_ids          = '';

    /**
     * 验证操作类型和secret
    */
    public function __construct()
    {
        //获取参数（weixin_id字段可为空）
        //操作类型
        $this->type = tools_helper::post('operation', '');
        $appid      = tools_helper::post('appid', '');
        $timestamp  = tools_helper::post('timestamp', '');
        $token      = tools_helper::post('token', '');

        //操作类型error 直接直接终止
        if (!$this->type || !in_array($this->type, $this->operation)) {
            $info = array(
                        'errcode'   => 1,
                        'operation' => $this->type,
                        'errmsg'    => '请传正确形式的操作类型',
                    );
            echo json_encode($info);
            exit();
        }

        //获取全部的参数
        $post = $_POST;

        //参数全部接收
        $param = array('app_id' => $appid, 'type' => $this->type,'param' => json_encode($post));
        //先存储全部的参数
        $this->api_log_id = $this->api_for_dm_log($param, '');
        //检查秘钥
        $this->check_secret($appid, $timestamp, $token);
    }

    /**
     * 检查秘钥
     * @param json   $post
     * @param int    $appid
     * @param string $timestamp
     * @param string $token
     * @param int    $id
     */
    private function check_secret($appid, $timestamp, $token)
    {
        //判断appId
        if (!in_array($appid, $this->appids)) $this->comprehensive(false, '参数错误:appid');
        //判断时间戳
        if (!$timestamp) $this->comprehensive(false,'参数错误:timestamp');
        //判断token
        if ($token != md5($appid.'_'. api_config::$appid_list_by_login[$appid] .'_'.$timestamp)) {
            $this->comprehensive(false, '参数错误:token');
        }
    }

    /**
     * 返回数据处理的具体信息并且终止程序
     * @param string $operation
     * @param bool   $result
     * @param string $errmsg
     */
    private function comprehensive($result, $errmsg)
    {
        //记录日志
        if ( !is_array($errmsg) ) {
            $this->api_for_dm_log(['response' => $errmsg],  $this->api_log_id);
        }

        $wx_info = '暂无微信返回信息';

        if (is_array($this->errmsg_arr) && isset($this->errmsg_arr['errcode'])) {
            // 微信返回信息赋值
            $wx_info = $this->errmsg_arr;

            // 重新赋值为字符串
            $errmsg = _widget('wework.errmsg')->get_errmsg($this->errmsg_arr['errcode']);
        }

        if ($result) {
            $info = array(
                    'errcode'   => 0,
                    'operation' => $this->type.'d', // (过去完成式)
                    'errmsg'    => 'ok',
                    'wifimsg'   => $wx_info
            );

        } else {
            $info = array(
                    'errcode'   => 1,
                    'operation' => $this->type,
                    'errmsg'    => $errmsg,
                    'wifimsg'   => $wx_info
            );
        }

        echo json_encode($info);
        exit();
    }

    /*
     * 记录日志
    */
    private function api_for_dm_log($info, $id='')
    {
        if (!$info) return false;

        if ($id) {
            return _model('qydev_api_dm_operation_log')->update($id, $info);
        } else {
            return _model('qydev_api_dm_operation_log')->create($info);
        }

        return true;
    }

    public function __call($action= '', $param = array())
    {
        $this->province            = tools_helper::post('province', '');
        $this->business_hall_title = tools_helper::post('business_hall_title', '');
        $this->user_number         = tools_helper::post('user_number', '');
        $this->name                = tools_helper::post('name', '');
        $this->phone               = tools_helper::post('phone', '');
        $this->depart_ids          = tools_helper::post('depart_ids', '');

        if ( 'update' == $this->type ) {
            $this->update_user($this->phone);
            // 更新去掉数字地图的部门 已经返回程序终止到这里
        }

        //检验参数
        $this->check_param();
        //检查营业厅 (返回的是业务线的部门ID)
        $departid_arr = $this->check_base_info();

        //检查手机号、没关注了删除继续走流程     关注了更新返回了  
        $this->check_qydev_user_info();

        //创建成员信息
        $this->handle_user_info($departid_arr);

    }

    private function update_user($phone)
    {
        //1、查看本地
        $local_user_info = _model('wework_user')->read(array('mobile' => $phone));

        if ( !$local_user_info) {
            $this->comprehensive(false, '该手机号未创建，无更新操作');
        }

        // 是否关注的处理
        $user_info = wework_user_helper::get_user_info('work', $local_user_info['user_id']);

        //移除掉数字地图
        if (in_array(2, $user_info['department'])) {
            $key = array_search(2, $user_info['department']);
            unset($user_info['department'][$key]);
        }

        $department = '';
        //拼接数据（循环拼接部门ID）
        $str = '{
                "userid": "'.$local_user_info['user_id'].'",
                "department": [';

        if ($user_info['department']) {
            foreach ($user_info['department'] as $k => $v) {
                $department .= $v . ',';
            }
        } else {
            $department = 1513;
        }

        $department = trim($department, ',');
        $str .= $department;

        $str .= ']}';

        //更新成员信息
        $result = _widget('wework.user')->update_user_info($str);

        if (isset($result['errcode']) && $result['errcode']) {
            $this->errmsg_arr = $result['errmsg'];
            $this->comprehensive(false, $log_info, json_encode($result['errmsg']));
        }

        _model('wework_user')->update(
            array('user_id' => $local_user_info['user_id']),
            array('department' => $department)
        );

        //成功
        $this->comprehensive(true, json_encode($result));
    }

    /**
     * 检查这个手机号是否已经和其他营业厅绑定过(企业号用户信息)
     * @return boolean
     */
    private function check_qydev_user_info()
    {
        $qydev_user_info = array();

        //1、查看本地
        $local_user_info = _model('wework_user')->read(array('mobile' => $this->phone));

        if ($local_user_info) {
           // 是否关注的处理
            $user_info = wework_user_helper::get_user_info('work', $local_user_info['user_id']);

            // 激活状态: 1=已激活，2=已禁用，4=未激活
            if ( isset($user_info['status']) && (4 == $user_info['status'] || 2 == $user_info['status']) ) {
                // 没有关注删除
                $delete_info = wework_user_helper::delete('work', $local_user_info['user_id']);

                return true;
            }
            //移除掉待分配组
            if (in_array(1513, $user_info['department'])) {
                $key = array_search(1513, $user_info['department']);
                unset($user_info['department'][$key]);
            }

            $department = '';
            //拼接数据（循环拼接部门ID）
            $str = '{
                    "userid": "'.$local_user_info['user_id'].'",
                    "department": [';

                if ($user_info['department']) {
                    foreach ($user_info['department'] as $k => $v) {
                        $str .=  $v.',';
                        $department .= $v .',';
                    }
                }
                $str .= trim($this->depart_ids, ',');
                $department .= trim($this->depart_ids, ',');

                $str .= '],';
                $str .= '"extattr":{
                                    "attrs" : 
                                     [
                                         {"name":"analog_i","value":"'.$this->user_number.'"},
                                         {"name":"an_id","value":"'.$this->user_number.'"},
                                         {"name":"business","value":"'.$this->business_hall_title.'"},
                                     ]
                                   }';
            $str .= '}';

            //更新成员信息
            $result = _widget('wework.user')->update_user_info($str);

            if (isset($result['errcode']) && $result['errcode']) {
                $this->errmsg_arr = $result['errmsg'];
                $this->comprehensive(false, $log_info, json_encode($result['errmsg']));
            }

            _model('wework_user')->update(
                array('user_id' => $local_user_info['user_id']),
                array(
                    'department' => $department,
                    'an_id'    => $this->user_number,
                    'analog_i' => $this->user_number,
                    'business' => $this->business_hall_title
                )
            );

            //成功
            $this->comprehensive(true, json_encode($result));
        }

    }

    /**
     * 检查营业厅信息
     * 所属省  营业厅渠道码
     */
    private function check_base_info()
    {
        //负责项目的ID数组
        $project_depart = explode(',', $this->depart_ids);

        //1、查看省信息本地
        $province_info  = _model('province')->read(array('name' => $this->province));

        if (!$province_info) $this->comprehensive(false, '营业厅省名称不规范');

        //2、查看营业厅信息本地
        $yyt_info = _model('business_hall')->read(array('user_number' => $this->user_number));

        if ($yyt_info && $province_info['id'] != $yyt_info['province_id']) {
            $this->comprehensive(false, '营业厅与所属省不匹配');
        }

        //WiFi不存在的营业厅而是数字地图单独有的营业厅（纠错平台  那就是我们不存在的了）
        if (!$yyt_info && in_array(145, $project_depart)) {
            //没有爱WiFi就不创建        删除爱WiFi的部门ID
            $key = array_search(145, $project_depart);
            unset($project_depart[$key]);
        }

        //第一步 遍历二级部门ID
        foreach ($project_depart as $v) {
            if (!in_array($v, $this->department_ids)) {
                $this->comprehensive(false, '二级部门ID请与企业号二级部门ID保持一致');
            }
        }

        return $project_depart;
    }


    /**
     * 处理成员信息
     * @param string $yyt_depart_id_arr
     */
    private function handle_user_info($yyt_depart_id_arr)
    {
        //成员信息
        $params = [];

        //拼装创建成员信息
        $params['name']  = $this->name;
        $params['mobile'] = $this->phone;
        $params['analog_i']  = $this->user_number;
        $params['an_id']     = $this->user_number;
        $params['business_hall'] = $this->business_hall_title;

        //查看本地该渠道码 最后一条
        $local_user_info = _model('wework_user')->read(
                array('user_id LIKE' => "%".$this->user_number."%"),
                " ORDER BY `user_id` DESC "
        );

        if ($local_user_info) {
            /**
             *在渠道码相同的情况下添加新的成员      渠道码_02 渠道码_20
             */
            $user_name_arr = explode('_', $local_user_info['user_id']);

            $suffix = $user_name_arr[1] + 1;

            if (1 == strlen($suffix)) {
                $params['user_id'] = $this->user_number.'_'.'0'.$suffix;
            } else {
                $params['user_id'] = $this->user_number.'_'.$suffix;
            }

            //接口形式的部门ID
            $params['depart_ids']  = json_encode($yyt_depart_id_arr);

            //创建这个userId的成员信息
            $qydev_create_user_info = _widget('wework.user')->create_user_info($params);

            //创建企业号成员信息   微信接口有错误提示
            if (isset($qydev_create_user_info['errcode']) && $qydev_create_user_info['errcode']) {
                $this->errmsg_arr = $qydev_create_user_info['errmsg'];
                $this->comprehensive(false, json_encode($qydev_create_user_info['errmsg']));
            }

            //本地创建
            wework_user_helper::loacal_create('work', $params['user_id']);

            //成功返回给数字地图
            $this->comprehensive(true, json_encode($qydev_create_user_info));

        } else {
            /**
             * 该渠道码就不存在 新建成员信息
             */
            //继续补充用户信息
            $params['user_id']    = $this->user_number.'_01';
            $params['depart_ids'] = json_encode($yyt_depart_id_arr);

            //创建这个userId的成员信息
            $qydev_create_user_info = _widget('wework.user')->create_user_info($params);

            //创建企业号成员信息   微信接口有错误提示
            if (isset($qydev_create_user_info['errcode']) && $qydev_create_user_info['errcode']) {
                $this->errmsg_arr = $qydev_create_user_info['errmsg'];
                $this->comprehensive(false, json_encode($qydev_create_user_info['errmsg']));
            }

            //本地创建
            wework_user_helper::loacal_create('work', $params['user_id']);

            //成功返回给数字地图
            $this->comprehensive(true, json_encode($qydev_create_user_info));
        }
    }

    /**
     * 检查参数
     */
    private function check_param()
    {
        //所属省
        if (!$this->province) $this->comprehensive(false, '参数错误:province');
        //营业厅名称
        if (!$this->business_hall_title) $this->comprehensive(false, '参数错误:business_hall_title');
        //渠道码
        if (!$this->user_number) $this->comprehensive(false, '参数错误:user_number');
        //成员名
        if (!$this->name) $this->comprehensive(false, '参数错误:name');
        //成员手机号
        if (!$this->phone) $this->comprehensive(false, '参数错误:phone');
        //二级部门的ID用,连接的字符串
        if (!$this->depart_ids) $this->comprehensive(false, '参数错误:depart_ids');
    }

}