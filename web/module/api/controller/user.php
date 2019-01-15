<?php
/**
 * alltosun.com 提供给数字地图添加企业号成员信息的接口 user.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-8-9 上午11:40:17 $
 * $Id$
 */

/**
 * 说明：
 * 1、企业号各个业务线只能操作成员有该业务线的成员信息
 * 2、删除成员信息：各个业务线移除本业务线的部门ID，如果只有本部门的成员，会被移动到待分配组
 * 3、添加成员信息：如果成员不存在，那么就创建包含本业务线的成员信息；如果存在该成员信息就添加该业务线的部门ID
 * 4、更新成员信息：更新成员姓名、手机号、微信号
 * 5、目前如果有解绑某手机号的渠道码，那么只能删除该手机号绑定的渠道码再重新绑定到其他渠道码
 * 6、成员ID后缀是根据拉取信息的顺序 创建（测试推算）
 * 
 * @author 郭剑峰
 *
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
    );

    /**
     * 业务线的操作
     */
    private $operation = array(
            1 => 'create',
            2 => 'update',
            3 => 'delete'
    );

    //操作类型
    private $type       = '';
    //日志记录ID
    private $api_log_id = '';
    //
    private $from_key   = '';
    //是否是厅长
    private $user_type  = 0;
    //额外字段(没有就为0)
    private $extra      = 0;

    private $from_id     = '';
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
        //赋值
        $this->from_key = $appid;
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
        if (!in_array($appid, $this->appids)) {
            $log_info = array('response' => '参数错误:appid');
            $this->comprehensive(false, $log_info, '参数错误:appid');
        }
        //判断时间戳
        if (!$timestamp) {
            $log_info = array('response' => '参数错误:timestamp');
            $this->comprehensive(false, $log_info, '参数错误:timestamp');
        }
        //判断token
        if ($token != md5($appid.'_'. api_config::$appid_list_by_login[$appid] .'_'.$timestamp)) {
            $log_info = array('response' => '参数错误:token');
            $this->comprehensive(false, $log_info, '参数错误:token');
        }
    }

    /**
     * 返回数据处理的具体信息并且终止程序
     * @param string $operation
     * @param bool   $result
     * @param string $errmsg
     */
    private function comprehensive($result, $log_info, $errmsg)
    {
        //记录日志
        $this->api_for_dm_log($log_info,  $this->api_log_id);

        $wx_info = '暂无微信返回信息';

        if (is_array($errmsg) && isset($errmsg['errcode'])) {
            // 微信返回信息赋值
            $wx_info = $errmsg;

            // 重新赋值为字符串
            $errmsg = _widget('qydev.errmsg')->get_errmsg($errmsg['errcode']);
        }

        if ($result) {
            $info = array(
                    'errcode'   => 0,
                    'operation' => $this->type.'d', //因为都是以e结尾(过去完成式)
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
        if (!$info) {
            return false;
        }

        if ($id) {
            return _model('qydev_api_dm_operation_log')->update($id, $info);
        } else {
            return _model('qydev_api_dm_operation_log')->create($info);
        }
    }

    public function __call($action= '', $param = array())
    {
        $province            = tools_helper::post('province', '');
        $business_hall_title = tools_helper::post('business_hall_title', '');
        $user_number         = tools_helper::post('user_number', '');
        $name                = tools_helper::post('name', '');
        $phone               = tools_helper::post('phone', '');
        $weixin_id           = tools_helper::post('weixin_id', '');
        $depart_ids          = tools_helper::post('depart_ids', '');

        //纠错平台厅长
        $this->user_type = tools_helper::post('user_type', 0);
        //额外的字段（发奖为1）
        $this->extra    = tools_helper::post('extra', 0);

        //检验参数
        $this->check_params($province, $business_hall_title, $user_number, $name, $phone, $depart_ids);

        $this->from_id = $depart_ids;

        //检查营业厅 (返回的是业务线的部门ID)
        $project_depart_id_arr = $this->check_yyt_info($business_hall_title, $user_number, $province, $depart_ids);

        //检查手机号和渠道码信息
        $qydev_user_info = $this->check_qydev_user_info($user_number, $phone);

        //各业务线的营业厅ID
        $yyt_depart_id_arr = $this->handle_department_info($business_hall_title, $province, $project_depart_id_arr);

        //创建（成员信息不存在）e
        if ('create' == $this->type && !$qydev_user_info) {
            //创建成员信息
            $this->handle_user_info($name, $phone, $weixin_id, $business_hall_title, $user_number, $yyt_depart_id_arr);
        }

        //更新/删除（成员信息不存在）
        if ('create' != $this->type && !$qydev_user_info) {
            $log_info = array('response' => '操作成员信息不存在');
            $this->comprehensive(false, $log_info, '操作成员信息不存在');
        }

        //更新/删除（是否在本业务线）
        if ('create' != $this->type && $qydev_user_info && !in_array($yyt_depart_id_arr[0], $qydev_user_info['department'])) {
            $log_info = array('response' => '该成员信息不包含您的业务线，无权操作');
            $this->comprehensive(false, $log_info, '该成员信息不包含您的业务线，无权操作');
        }

        //创建\删除（成员信息存在）
        if ('update' != $this->type && $qydev_user_info) {
            $this->qydev_user_operation($qydev_user_info, $yyt_depart_id_arr[0]);
        }

        //更新 （人名 或者 微信号）
        if ('update' == $this->type && $qydev_user_info) {
            $this->qydev_user_update($name, $weixin_id, $qydev_user_info['userid']);
        }

    }

    /**
     * 检查这个手机号是否已经和其他营业厅绑定过(企业号用户信息)
     * @param array  $phone
     * @param string $user_number
     * @return boolean
     */
    private function check_qydev_user_info($user_number, $phone)
    {
        $qydev_user_info = array();

        //1、查看本地
        $local_user_info = _model('public_contact_user')->read(array('user_phone' => $phone));

        if ($local_user_info && $local_user_info['user_number'] != $user_number) {
            $log_info = array('response' => '该手机号与其他营业厅渠道码已绑定');
            $this->comprehensive(false, $log_info, '该手机号与其他营业厅渠道码已绑定');
        }

        //此渠道、手机号是否同时存在
        $special_user_info = _model('public_contact_user')->read(array('user_number' => $user_number, 'user_phone' => $phone));

        if ($special_user_info) {
            //获取企业号的成员信息（主要需要的是部门ID，其他信息不能随意更改，因为可能会影响其他的业务线）
            $qydev_user_info = _widget('qydev.user')->get_user_info($special_user_info['unique_id']);

            if (isset($qydev_user_info['errcode']) && $qydev_user_info['errcode']) {
                $log_info = array('response' => json_encode($qydev_user_info['errcode']));
                $this->comprehensive(false, $log_info, $qydev_user_info['errmsg']);
            }

            //保留原有的成员信息（更新）
            $this->api_for_dm_log(array('old_user_info' => json_encode($qydev_user_info)) ,$this->api_log_id);
        } 

        return $qydev_user_info;
    }

    /**
     * 检查营业厅信息
     * @param string $business_hall_title
     * @param string $user_number
     * @param string $province
     * @param string $depart_ids
     * @return multitype:
     */
    private function check_yyt_info($business_hall_title, $user_number, $province, $depart_ids)
    {
        //负责项目的ID数组
        $project_depart = explode(',', $depart_ids);

        //1、查看省信息本地
        $province_info  = _model('province')->read(array('name' => $province));

        if (!$province_info) {
            $log_info = array('response' => '营业厅省信息不规范');
            $this->comprehensive(false, $log_info, '营业厅省信息不规范');
        }

        //2、查看营业厅信息本地
        $yyt_info = _model('business_hall')->read(array('user_number' => $user_number));

        if ($yyt_info && $business_hall_title != $yyt_info['title']) {
            $log_info = array('response' => '营业厅渠道码和营业厅名称不匹配');
            $this->comprehensive(false, $log_info, '营业厅渠道码和营业厅名称不匹配');
        }

        if ($yyt_info && $province_info['id'] != $yyt_info['province_id']) {
            $log_info = array('response' => '营业厅与所属省不匹配');
            $this->comprehensive(false, $log_info, '营业厅与所属省不匹配');
        }

        //WiFi不存在的营业厅而是数字地图单独有的营业厅（纠错平台  那就是我们不存在的了）
        if (!$yyt_info && in_array(145, $project_depart)) {
            //没有爱WiFi就不创建        删除爱WiFi的部门ID
            $key = array_search(145, $project_depart);
            unset($project_depart[$key]);
        }

        return $project_depart;
    }

    /**
     * 处理部门信息(创建使用)
     * @param string $business_hall_title
     * @param string $province
     * @param string $project_depart
     * @return multitype:
     */
    private function handle_department_info($business_hall_title, $province, $project_depart_id_arr)
    {
        //这是需要的部门ID
        $ids = [];

        //第一步 遍历二级部门ID
        foreach ($project_depart_id_arr as $v) {

            if (!in_array($v, $this->department_ids)) {
                $errstr   = '二级部门ID请与企业号二级部门ID保持一致';
                $log_info = array('response' => $errstr);
                $this->comprehensive(false, $log_info, $errstr);
            }

            //营业厅信息和省信息
            $yyt_params = $pro_params = [];

            //第二步 查看省三级部门信息
            $local_pro_depart = _model('public_contact_department')->read(array('parent_id'=>$v , 'name'=>$province));

            //创建负责业务线下的省部门
            if (!$local_pro_depart) {
                //
                if ('create' != $this->type) {
                    $log_info = array('response' => '操作的成员信息省部门不存在');
                    $this->comprehensive(false, $log_info, '操作的成员信息省部门不存在');
                }

                $pro_params['parent_id'] = $v;
                $pro_params['name']      = $province;

                //第三步  企业号创建   省级部门
                $pro_depart_info = _widget('qydev.department')->create_department($pro_params);

                //创建失败
                if (isset($pro_depart_info['errcode']) && $pro_depart_info['errcode']) {
                    $log_info = array('response' => json_encode($pro_depart_info['errmsg']));
                    $this->comprehensive(false, $log_info, $pro_depart_info['errmsg']);
                }

                //本地同时创建 省级部门
                $this->create_local_department($pro_depart_info['id'], $province, $v);

//                 //第四步  创建营业厅部门
//                 $yyt_params['parent_id'] = $pro_depart_info['id'];
//                 $yyt_params['name']      = $business_hall_title;

//                 //省级部门没有，营业厅级部门肯定不存在
//                 $yyt_depart_info = _widget('qydev.department')->create_department($yyt_params);

//                 //创建失败
//                 if (isset($yyt_depart_info['errcode']) && $yyt_depart_info['errcode']) {
//                     $log_info = array('response' => json_encode($yyt_depart_info['errmsg']));
//                     $this->comprehensive(false, $log_info, $yyt_depart_info['errmsg']);
//                 }

//                 //本地同时创建 营业厅级部门
//                 $this->create_local_department($yyt_depart_info['id'], $business_hall_title, $pro_depart_info['id']);

                array_push($ids, $pro_depart_info['id']);
            } else {
                //第三步  查看有没有营业厅部门
//                 $local_yyt_depart = _model('public_contact_department')->read(array('parent_id' => $local_pro_depart['department_id'] , 'name' => $business_hall_title));

//                 if (!$local_yyt_depart) {
//                     //
//                     if ('create' != $this->type) {
//                         $log_info = array('response' => '操作的成员信息营业厅级部门不存在');
//                         $this->comprehensive(false, $log_info, '操作的成员信息营业厅级部门不存在');
//                     }

//                     //创建营业厅部门
//                     $yyt_params['parent_id'] = $local_pro_depart['department_id'];
//                     $yyt_params['name']      = $business_hall_title;

//                     //第四步  企业号创建 营业厅级部门
//                     $yyt_depart_info = _widget('qydev.department')->create_department($yyt_params);

//                     //创建失败
//                     if (isset($yyt_depart_info['errcode']) && $yyt_depart_info['errcode']) {
//                         $log_info = array('response' => json_encode($yyt_depart_info['errmsg']));
//                         $this->comprehensive(false, $log_info, $yyt_depart_info['errmsg']);
//                     }

//                     //本地同时创建 营业厅部门
//                     $this->create_local_department($yyt_depart_info['id'], $business_hall_title, $local_pro_depart['department_id']);

//                     array_push($ids, $yyt_depart_info['id']);
//                 } else {
//                     array_push($ids, $local_yyt_depart['department_id']);
//                 }
                array_push($ids, $local_pro_depart['department_id']);
            }
        }

        if (!$ids) {
            $log_info = array('response' => '部门ID获取失败');
            $this->comprehensive(false, $log_info, '部门ID获取失败');
        }

        //企业号最小（营业厅级的部门）部门的ID数组
        return $ids;
    }

    /**
     * 处理成员信息
     * @param string $name
     * @param string $phone
     * @param string $weixin_id
     * @param string $user_number
     * @param string $yyt_depart_id_arr
     */
    private function handle_user_info($name, $phone, $weixin_id, $business_hall_title, $user_number, $yyt_depart_id_arr)
    {
        //成员信息
        $params = [];

        //拼装创建成员信息
        $params['user_name']  = $name;
        $params['user_phone'] = $phone;
        $params['weixin_id']  = $weixin_id;
        $params['analog_id']  = $user_number;
        $params['an_id']      = $user_number;
        $params['business_hall'] = $business_hall_title;

        //查看本地该渠道码 最后一条
        $local_user_info = _model('public_contact_user')->read(array('user_number' => $user_number), " ORDER BY `unique_id` DESC ");

        if ($local_user_info) {
            /**
             *在渠道码相同的情况下添加新的成员      渠道码_02 渠道码_20
             */
            $user_name_arr = explode('_', $local_user_info['unique_id']);

            $suffix = $user_name_arr[1] + 1;

            if (1 == strlen($suffix)) {
                $params['user_id'] = $user_number.'_'.'0'.$suffix;
            } else {
                $params['user_id'] = $user_number.'_'.$suffix;
            }

            //接口形式的部门ID
            $params['depart_ids']  = json_encode($yyt_depart_id_arr);

            //创建这个userId的成员信息
            $qydev_create_user_info = _widget('qydev.user')->create_user_info($params);

            //创建企业号成员信息   微信接口有错误提示
            if (isset($qydev_create_user_info['errcode']) && $qydev_create_user_info['errcode']) {
                $log_info = array('response' => json_encode($qydev_create_user_info['errmsg']));
                $this->comprehensive(false, $log_info, $qydev_create_user_info['errmsg']);
            }

            //本地创建
            $this->create_local_user($user_number, $name, $phone, $params['user_id']);

            //成功返回给数字地图
            $log_info = array('response' => json_encode($qydev_create_user_info));
            $this->comprehensive(true, $log_info, $qydev_create_user_info);

        } else {
            /**
             * 该渠道码就不存在 新建成员信息
             */
            //继续补充用户信息
            $params['user_id']    = $user_number.'_01';
            $params['depart_ids'] = json_encode($yyt_depart_id_arr);

            //创建这个userId的成员信息
            $qydev_create_user_info = _widget('qydev.user')->create_user_info($params);
// p($qydev_create_user_info);exit();
            //创建企业号成员信息   微信接口有错误提示
            if (isset($qydev_create_user_info['errcode']) && $qydev_create_user_info['errcode']) {
                $log_info = array('response' => json_encode($qydev_create_user_info['errmsg']));
                $this->comprehensive(false, $log_info,$qydev_create_user_info['errmsg']);
            }

            //本地创建
            $this->create_local_user($user_number, $name, $phone, $params['user_id']);

            //成功返回给数字地图
            $log_info = array('response' => json_encode($qydev_create_user_info));
            $this->comprehensive(true, $log_info, $qydev_create_user_info);
        }
    }

    /**
     * 企业号成员部门移除（只是更新成员信息接口；只针对单独的业务线）
     * @param string $qydev_user_info
     * @param int    $yyt_id
     */
    private function qydev_user_operation($qydev_user_info, $yyt_id)
    {
        if ('create' == $this->type) {
            if (in_array($yyt_id, $qydev_user_info['department'])) {
                $log_info = array('response' => '该成员信息已存在，并包含所添加部门');
                $this->comprehensive(false, $log_info, '该成员信息已存在，并包含所添加部门');
            }

            //移除掉待分配组
            if (in_array(1513, $qydev_user_info['department'])) {
                $key1 = array_search(1513, $qydev_user_info['department']);
                unset($qydev_user_info['department'][$key1]);
            }

            //
            array_push($qydev_user_info['department'], $yyt_id);

        } else if ('delete' == $this->type) {
            $key = array_search($yyt_id, $qydev_user_info['department']);

            if ($key !== false) {
                unset($qydev_user_info['department'][$key]);
            } else {
                $log_info = array('response' => '原成员部门信息不包含移除的部门');
                $this->comprehensive(false, $log_info, '原成员部门信息不包含移除的部门');
            }
        }

       //拼接数据（循环拼接部门ID）
        $str = '{
                    "userid"     : "'.$qydev_user_info['userid'].'", 
                    "department" : [';

                    if ($qydev_user_info['department']) {
                        foreach ($qydev_user_info['department'] as $k => $v) {
                                $str .=  $v.',';
                        }
                    } else {
                        $str .= 1513;
                    }

                    $str .= ']
                }';

        //更新成员信息
        $result = _widget('qydev.user')->update_user_info($str);

        if (isset($result['errcode']) && $result['errcode']) {
            $log_info = array('response' => json_encode($result['errmsg']));
            $this->comprehensive(false, $log_info, $result['errmsg']);
        }

        //成功
        $log_info = array('response' => json_encode($result));
        $this->comprehensive(true, $log_info, $result);
    }

    /**
     * 企业号成员姓名和微信号更新
     * @param string $name
     * @param string $weixin_id
     * @param int    $qydev_user_id
     */
    private function qydev_user_update($name, $weixin_id, $qydev_user_id)
    {
    
        //拼接数据(因为只是更新手机号或者是微信号所以只拼接手机号和微信号)
        $str = '{
                    "userid"   : "'.$qydev_user_id.'",
                    "name"       : "'.$name.'",
                    "weixinid" : "'.$weixin_id.'",
                 }';

        //更新成员信息
        $result = _widget('qydev.user')->update_user_info($str);

        if (isset($result['errcode']) && $result['errcode']) {
            $log_info = array('response' => json_encode($result['errmsg']));
            $this->comprehensive(false, $log_info, $result['errmsg']);
        }

        //成功
        $log_info = array('response' => json_encode($result));
        $this->comprehensive(true, $log_info, $result);
    }
    /**
     * 检查参数
     * @param string $province
     * @param string $business_hall_title
     * @param string $user_number
     * @param string $name
     * @param string $phone
     * @param string $depart_ids
     */
    private function check_params($province, $business_hall_title, $user_number, $name, $phone, $depart_ids)
    {
        //所属省
        if (!$province) {
            $log_info = array('response' => '参数错误:province');
            $this->comprehensive(false, $log_info, '参数错误:province');
        }
        //营业厅名称
        if (!$business_hall_title) {
            $log_info = array('response' => '参数错误:business_hall_title');
            $this->comprehensive(false, $log_info, '参数错误:business_hall_title');
        }
        //渠道码
        if (!$user_number) {
            $log_info = array('response' => '参数错误:user_number');
            $this->comprehensive(false, $log_info, '参数错误:user_number');
        }
        //成员名
        if (!$name) {
            $log_info = array('response' => '参数错误:name');
            $this->comprehensive(false, $log_info, '参数错误:name');
        }
        //成员手机号
        if (!$phone) {
            $log_info = array('response' => '参数错误:phone');
            $this->comprehensive(false, $log_info, '参数错误:phone');
        }
        //二级部门的ID用,连接的字符串
        if (!$depart_ids) {
            $log_info = array('response' => '参数错误:depart_ids');
            $this->comprehensive(false, $log_info, '参数错误:depart_ids');
        }
    }

    /**
     * 本地创建部门（目前信息不全）
     * @param int $depart_id
     * @param string $name
     * @param int $parent_id
     * @return boolean
     */
    private function create_local_department($depart_id , $name , $parent_id )
    {
        _model('public_contact_department')->create(
            array(
                'department_id' => $depart_id,
                'name'          => $name,
                'parent_id'     => $parent_id)
        );

        return true;
    }

    /**
     * 本地创建成员信息（目前信息不全）
     * @param unknown $user_number
     * @param unknown $phone
     * @param unknown $user_id
     */
    private function create_local_user($user_number, $user_name, $phone, $user_id )
    {
        //来源
        $api_from = $this->from_where[$this->from_key];

        //本地创建
        _model('public_contact_user')->create(
            array(
                    //'type'        => 4,
                    'user_number' => $user_number,
                    'user_name'   => $user_name,
                    'user_phone'  => $phone,
                    'from_id'     => $this->from_id,
                    'api_from'    => $api_from,
                    'user_type'   => $this->user_type,
                    'unique_id'   => $user_id,
                    'extra'       => $this->extra,
                    'analog_id'   => $user_number,
                    'an_id'       => $user_number
            )
        );

        return true;
    }
}