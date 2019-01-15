<?php
/**
 * alltosun.com
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 雷健雄 (leijx@alltosun.com) $
 * $Date: 2016-7-31 下午3:23:47 $
 * $Id: $
 */
class Action
{
    private $app_id = 'af93510d';
    private $app_key = '2e76277aae45';
    private $business_code = '';
    private $is_mobile = '';
    private $default_url = '';
    private $member_info = array();

    /**
     * 管理员登陆接口
     * @author leijx
     */
    public function login()
    {
        // 请求时间
        $timestamp      = Request::getParam('timestamp', '');
        // 加密token
        $token          = Request::getParam('token', '');
        // 营业厅openid
        $merchantopenid = Request::getParam('merchantopenid', '');
        $app_id         = Request::getParam('appid', '');
        // 营业厅账户-EN (渠道编码)
        $account        = Request::getParam('account', '');
        // 营业厅账户-CN
        $merchantname   = Request::getParam('merchantname', '');
        // 登录行为（为什么登录进来）
        $action         = Request::getParam('action', '');
        // 营业厅渠道编码
        $business_code  = Request::getParam('business_code', '');
        // 是否是移动版
        $is_mobile      = Request::getParam('is_mobile', 0);

        $this->business_code = $business_code;
        $this->is_mobile = $is_mobile;
        //清除上次登录时记录的member_admin_me
        member_helper::remember_me_expire();

        $app_key = $this->app_key;

        if ($is_mobile) {
            $default_url = SITE_URL.'/liangliang/e_login';
        } else {
            $default_url = SITE_URL.'/liangliang';
        }

        $this->$default_url = $default_url;

        //数字地图接入
        if ($app_id ){
            if (isset(api_config::$appid_list_by_login[$app_id])) {
                $app_key = api_config::$appid_list_by_login[$app_id];
            } else {

                Response::redirect($default_url);
                Response::flush();
                exit;
            }
        } else {
            //兼容原有的
            $app_id = $this->app_id;
        }

        // 验证token
        if (md5($app_id.'_'.$app_key.'_'.$timestamp) != $token) {
            Response::redirect($default_url);
            Response::flush();
            exit;
        }

        // 登录检测
        if (!$account) {
            Response::redirect($default_url);
            Response::flush();
            exit;
        }

        $member_info = _model('member')->read(array('member_user' => $account));
        if (!$member_info) {
            Response::redirect($default_url);
            Response::flush();
            exit;
        }

        $this->member_info = $member_info;

        // 设置登录状态
        member_helper::remember_me_set($member_info);

        if ( $action && $action == 'probe' ) {
            $this->probe();
        //RFID 单点
        } else if ($action && $action == 'rfid') {
            $this->rfid();
        } else if ($action && $action == 'screen') {

            $device_code  = Request::getParam('device_code', '');

            //edited by guojf
            if ($is_mobile || is_mobile()) {


                if (!$device_code) {
                    Response::redirect(SITE_URL.'/screen_dm');
                } else {

                    $device_info = screen_device_helper::get_device_info_by_device($device_code);
                    //$device_info = _model('screen_device')->read(array('device_unique_id' => $device_code));

                    if (!$device_info) {
                        Response::redirect(SITE_URL.'/screen_dm');
                    } else {
                        $date = date('Ymd');

                        $url = SITE_URL.'/screen_dm/detail?device_unique_id='.$device_code.'&date='.$date;
                        Response::redirect($url);
                    }
                }

                Response::flush();
                exit;
            }

            $url = SITE_URL.'/screen_stat/admin/experience_stat/detail?';

            //如果身份是营业厅权限
           if ($member_info['res_name'] != 'business_hall') {
               if (!$business_code) {
                   $url .= 'business_id=0&';
               } else {
                   $b_info = business_hall_helper::get_business_hall_info(array('user_number' => $business_code));
                   if (!$b_info) {
                       $url .= 'business_id=0&';
                   } else {
                       $url .= 'business_id='.$b_info['id'].'&';
                   }

               }

            }

            if ($device_code) {
                $url.= 'device_code='.$device_code;
            }

            $url = rtrim($url, '&');
            $url .= '&from_type=sso_login';

            Response::redirect($url);
            Response::flush();
            exit;

        } else if ($action && $action == 'redirect_uri') {

            //p($_COOKIE, $_SESSION);exit;
            //指定登录后的地址
            $redirect_uri   = Request::Get('redirect_uri', '');
            if ($redirect_uri) {
                Response::redirect($redirect_uri);
                Response::flush();
                exit;
            }

        }

        Response::redirect($default_url);
        Response::flush();
        exit;
    }

    /**
     * 探针相关单点登录
     */
    private function probe()
    {
        $member_info    = $this->member_info;
        $business_code  = $this->business_code;
        $business_id    = 0;

        $to_go = tools_helper::Get('to_go', 0);

        //单点到哪，1-排队平台的停留详情页
        if ($to_go == 1 && $this->is_mobile) {
            $start_time = tools_helper::Get('start_time', '');
            $end_time   = tools_helper::Get('end_time', '');
            $mac        = tools_helper::Get('mac', '');
            $toiletId   = tools_helper::Get('toiletId', '');
            Response::redirect(AnUrl("e/admin/probe/remain?mac={$mac}&toiletId={$toiletId}&start_time={$start_time}&end_time={$end_time}"));
            Response::flush();
            exit;
        }

        if ($member_info['res_name'] != 'business_hall') {
            if ($business_code) {
                $b_info = business_hall_helper::get_business_hall_info(array('user_number' => $business_code));
                $business_id = $b_info['id'];
            }

        } else {
            $business_id = $member_info['res_id'];
        }

        //没有营业厅id
        if (!$business_id) {
            Response::redirect($this->default_url);
        }

        if ( is_mobile() || $is_mobile ) {

            $is_index = tools_helper::Get('is_index', 0);
            if ($is_index) {
                Response::redirect(AnUrl('e/admin/probe'));
            } else {

                Response::redirect(AnUrl('e/admin/probe/mac_list?date='.date('Ymd').'&business_id='.$business_id));
            }
        } else {
            Response::redirect(AnUrl('probe_record/admin/hour?business_id='.$business_id));

        }

        Response::flush();
        exit ;
    }

    /**
     * rfid单点
     */
    private function rfid()
    {

        $label_id       = Request::getParam('label_id', '');
        $redirect_uri   = Request::Get('redirect_uri', '');

        //如果有回调url
        if ($redirect_uri) {
            Response::redirect($redirect_uri);
            Response::flush();
            exit;
        }

        //移动端
        if ( is_mobile() || $is_mobile ) {
            $url = SITE_URL.'/e/admin/rfid/stat/stat_detail?';
        } else {
            $url = SITE_URL.'/rfid/admin/stat/record?';
        }

        //如果身份是营业厅权限
        if ($this->member_info['res_name'] != 'business_hall') {
            if (!$this->business_code) {
                $url .= 'business_id=0&';
            } else {
                $b_info = business_hall_helper::get_business_hall_info(array('user_number' => $this->business_code));
                if (!$b_info) {
                    $url .= 'business_id=0&';
                } else {
                    $url .= 'business_id='.$b_info['id'].'&';
                }

            }

        }

        $url .= 'from_type=sso_login&';

        if ($label_id) {
            $url.= 'label_id='.$label_id;
        }


        Response::redirect($url);
        Response::flush();
        exit;
    }

    // 返回接口数据
    private function return_json_data($r)
    {
        exit(json_encode($r));
    }

}
?>