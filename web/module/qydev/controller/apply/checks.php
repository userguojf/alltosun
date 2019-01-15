<?php
/**
 * alltosun.com  checks.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-11-9 下午5:18:46 $
 * $Id$
 */

class Action
{
    private $access_token = '';
    private $user_id      = '';

    public function __construct()
    {
        $this->access_token = _widget('qydev.token')->get_access_token();
    }

    public function __call($action = '', $param = array())
    {
        
        Response::display('apply/apply_start.html');
    }

    public function handle()
    {
        $code = tools_helper::Get('code' , '') ;

        if (!$code) {
            return '企业号二次验证失败';
        }

        if (!$this->access_token) {
            return '由于网络问题，请刷新重试';
        }

        //接口地址
        $url  = qydev_config::$get_user_id_url.'access_token='.$this->access_token.'&codeo='.$code;
        //请求
        $user_info_json = curl_get($url);

        $user_info_arr  = json_decode($user_info, true);

        if (isset($arr_user_info['UserId']) && $arr_user_info['UserId']) {
            //企业号通讯录的账号   赋值
            $this->user_id   = $arr_user_info['UserId'];
        } else {
            return '微信企业号接口更新，请更新程序';
        }
        
        //关注
        //请求接口
        $info     = curl_get('https://qyapi.weixin.qq.com/cgi-bin/user/authsucc?access_token='.$this->access_token.'&userid='.$this->user_id);

        p($info);
        //Response::display('check.html');
    }
}