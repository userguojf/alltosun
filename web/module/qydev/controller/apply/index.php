<?php
/**
 * alltosun.com 企业号新人申请 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-11-20 上午10:00:39 $
 * $Id$
 */
class Action 
{

    public function __call($arction = '', $prarm = array())
    {
        Response::display ( 'apply/apply_detail.html' );
    }

    public function mid()
    {
        Response::display ( 'apply/mid.html' );
    }

    public function save()
    { 
        $info = tools_helper::post ( 'param', array() );

        if (! isset ( $info ['user_name'] ) || ! $info ['user_name']) {
            return array (
                    'errcode' => 100001,
                    'errmsg' => '请输入姓名' 
            );
        }
        if (! isset ( $info ['user_phone'] ) || ! $info ['user_phone'] || strlen($info['user_phone']) != 11) {
            return array (
                    'errcode' => 100001,
                    'errmsg' => '请输入正确的手机号' 
            );
        } else {
            $qydev_info = _model('public_contact_user')->read(array('user_phone' => $info['user_phone']));

            if ( $qydev_info ) {
                return array (
                        'errcode' => 60104,
                        'errmsg'  => '提示：您已经在企业号中，如未关注，请微信搜索“企业号电信智慧门店运营”关注'
                );
            }

        }
        if (! isset ( $info ['code'] ) || ! $info ['code']) {
            return array (
                    'errcode' => 100001,
                    'errmsg'  => '请输入验证码'
            );
        } else {
            //检查验证码
            $result = $this->verify_code($info ['user_phone'], $info ['code']);

            if ($result['errcode']) {
                return $result;
            }
        }

        if (! isset ( $info ['user_number'] ) || ! $info ['user_number']) {
            return array (
                    'errcode' => 100001,
                    'errmsg' => '请输入渠道码' 
            );
        } else {
            //根据渠道码判断营业厅信息
            $b_info = _model('business_hall')->read(array('user_number' => $info['user_number']));

            //渠道码不存在的和不是营业厅级别
            if (!$b_info || isset($b_info['type']) && !in_array($b_info['type'], array(4, 5))) {
                return array (
                        'errcode' => 100001,
                        'errmsg' => '渠道码错误，请核实渠道码'
                );
            }
        }

        if (! isset ( $info ['depart_id'] ) || ! $info ['depart_id']) {
            return array (
                    'errcode' => 100001,
                    'errmsg' => '请选择申请权限' 
            );
        }
        //验证码已存其他表
        unset($info['code']);

        $apply_info = _model('qydev_apply')->read(array('user_phone' => $info['user_phone']));

        if ( $apply_info ) {
            _model('qydev_apply')->update($apply_info['id'], 
                    array(
                        'user_number' => $info['user_number'],
                        'user_name' => $info['user_name'],
                        'user_phone' => $info['user_phone'],
                        'depart_id' => $info['depart_id'],
                    )
            );
        } else {
            //记录申请的ID
            $apply_info['id'] = _model('qydev_apply')->create($info);
        }

        //调用数字地图的企业号接口
        $param = array(
                'province_id'         => focus_helper::get_field_info($b_info['province_id'], 'province', 'name'),
                'business_hall_title' => $b_info['title'],
                'user_number'         => $b_info['user_number'],
                'name'                => $info['user_name'],
                'phone'               => $info['user_phone'],
                'depart_ids'          => trim($info['depart_id'], ',')
        );

        $api_result = $this->apply_dm_api($param);
// p($api_result);exit();
        if ( !$api_result['errcode'] ) {
            $code = 0;
            //更新已经创建成功的
            _model('qydev_apply')->update($apply_info['id'], array('status' => 1));
        } else {
            $code = 100001;
            //设置session
// p($api_result);exit();
            $_SESSION['qydev_apply_tip'] = $api_result['errmsg'];
        }

        return array (
                'errcode' => $code,
                'errmsg'  => 'applied',
        );
    }

    /**
     * 提交申请的处理页
     */
    public function handle()
    {
        $errcode = tools_helper::get('errcode', 0);

        Response::assign('errcode', $errcode);
        Response::display ('apply/apply_handle.html');
    }

    /**
     * 提交申请的失败页
     */
    public function error()
    {
        $errmsg = "<p>对不起，由于网络原因审核失败！</p>
                   <p>请您重新申请！</p>";

        if (isset($_SESSION['qydev_apply_tip']) && $_SESSION['qydev_apply_tip']) {
            $errmsg = "<p>".$_SESSION['qydev_apply_tip']."</p>
                   <p>请核实后重新申请！</p>";
        } else {
            // 托词
            $errmsg = "<p>微信接口调用返回超时</p>
                   <p>请核实后重新申请！</p>";
        }

        Response::assign('errmsg', $errmsg);
        Response::display ('apply/apply_error.html');
    }

    /**
     * 提交申请的成功页
     */
    public function success()
    {
        Response::display ('apply/apply_success.html');
    }

    /**
     * 验证验证码
     * @param string $phone
     * @param string $code
     * @return boolean
     */
    public function verify_code($phone, $code)
    {
        if (! $phone)
            return array (
                    'errcode' => 100001,
                    'errmsg' => '请输入正确的手机号' 
            );
        if (! $code)
            return array (
                    'errcode' => 100001,
                    'errmsg' => '请输入正确的验证码' 
            );

        $verifycode_info = _model ( 'qydev_verifycode' )->read ( array (
                'phone' => $phone,
                'expire_time >=' => date ( 'Y-m-d H:i:s' ) 
        ), 'ORDER BY `id` DESC ' );

        if (! $verifycode_info || $code != $verifycode_info ['code']) {
            return array (
                    'errcode' => 100001,
                    'errmsg' => '验证码输入错误' 
            );
        }

        return array (
                'errcode' => 0,
                'errmsg'  => 'ok' 
        );
    }

    /**
     * 发验证码短信
     * 发验证码 之前验证是否已经加入了public_contact_user
     * @return multitype:number string |Ambigous <multitype:number, multitype:number string >
     */
    public function send_code()
    {
        $phone = tools_helper::post ( 'phone', 0 );

        if (! $phone || strlen ( $phone ) != 11) {
            return array (
                    'errcode' => 100001,
                    'errmsg' => '请输入正确的手机号' 
            );
        }

//         $qydev_apply_info = _model('qydev_apply')->read(array('user_phone' => $phone, 'status' => 1));

//         if ( $qydev_apply_info ) {
//             return array (
//                     'errcode' => 60104,
//                     'errmsg'  => '已经审核通过了'
//             );
//         }

        //验证是否在企业号存在
        $qydev_info = _model('public_contact_user')->read(array('user_phone' => $phone));

        if ( $qydev_info ) {
            return array (
                    'errcode' => 60104,
                    'errmsg'  => 'mobile existed: '.$qydev_info['unique_id']
            );
        }

 //         exit('终止');
        return qydev_helper::send_code ( $phone );
    }

    public function apply_dm_api($param)
    {
        $url = AnUrl('api/dm/user');

        $appid     = 'wifi_shujdt_awzdxhyadrtggbrd';
        $app_key   = 'd1cb99814ddc2d11cdd8c099b6e5c6e8';
        $timestamp = time();

        $data = array(
                'operation'   => 'create',
                'appid'       => $appid,
                'timestamp'   => $timestamp,
                'token'       => md5($appid.'_'.$app_key.'_'.$timestamp),
                'province'    => $param['province_id'],
                'business_hall_title' => $param['business_hall_title'],
                'user_number' => $param['user_number'],
                'name'        => $param['name'],
                'phone'       => $param['phone'],
                'weixin_id'   => '',
                'depart_ids'  => $param['depart_ids'],//'2,145'
        );

        $response_info = curl_post($url, $data);

        return  json_decode($response_info, true);
    }
}