<?php
/**
 * alltosun.com  message.php
 * ============================================================================
 * 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: sunxs (QQ:3762820) $
 * $Date:  2014-6-30 上午11:54:09 $
 * $Id$
 * ps:目前本方法用于发送站内消息使用
*/
//require_once ROOT_PATH.'/send_message/config/open189_config.php';
// require_once MODULE_PATH.'/send_message/config/open189_config.php';
require_once ROOT_PATH.'/helper/AnCurl.php';
class message_widget
{
    private $user_id;
    private $company_id;
    private $per_page = 10;


    public function get_access_token()
    {
        global $mc_wr;
        $access_token = $mc_wr->get('dx_access_token');

        if ($access_token) {
            return $access_token;
        }

        //获取TokenApi
        $tokenAPI = "https://oauth.api.189.cn/emp/oauth2/v3/access_token";
        $params = array();

        $params['app_id']        = Config::get('open189_aid');;
        $params['app_secret']    = Config::get('open189_akey');
        $params['grant_type']    = 'client_credentials';

        $curl = new  AnCurl();
        $token_result = $curl->post($tokenAPI,http_build_query($params));

        $token_info=json_decode($token_result,true);

        if (isset($token_info['res_code']) &&  $token_info['res_code'] == 0) {
            $mc_wr->set('dx_access_token', $token_info['access_token'],3600*40);
            return $token_info['access_token'];
        }

        return false;
    }

    public function send_message($params, $type = 0)
    {
        $appid        = Config::get('open189_aid');
        $access_token = self::get_access_token();

        $sendsms_url  = "http://api.189.cn/v2/emp/templateSms/sendSms";

        if (!isset($params['tel']) || !$params['tel']) {
            return false;
        }

        if (!isset($params['content']) || !$params['content']) {
            return false;
        }

        $request_params = array();

        $request_params['acceptor_tel']     = $params['tel'];
        $request_params['template_param']   = $params['content'];
        $request_params['template_id']      = $params['template_id'];
        $request_params['grant_type']       = 'authorization_code';
        $request_params['app_id']           = $appid;
        $request_params['access_token']     = $access_token;
        $request_params['timestamp']        = date('Y-m-d H:i:s');

        $curl = new AnCurl();
        $result = $curl->post($sendsms_url,http_build_query($request_params));
// $result = curl_post($sendsms_url, $request_params);
        
        $result_info = json_decode($result,true);

        //记录日
        $message_log =  array(
                'phone'     => $params['tel'],
                'temp_id'   => $params['template_id'],
                'content'   => json_encode($params),
                'result'    => json_encode($result_info),
                'type'      => $type
            );

        $message_id = _model('message_log')->create($message_log);

        if(isset($result_info['res_code']) && !empty($result_info['res_code'])) {
//             _widget('email')->mail('短信下发失败', '日志id'.$message_id);

            return array('info' => 'error','msg'=> '短信下发失败');
        } else {
            return array('info' => 'ok','msg'=> '短信下发成功');
        }
    }
    /**
     * 商品 库存不足、用户已经付款、商品申请退款  调用本方法
     * @param array $params
     * @param goods_id  商品id   type  提示类型 1、商品库存不足，请及时备货 2、用户已经付款，请及时发货 3、商品申请售后,请及时处理
     * @return string
     * add  sunxs 2015-06-03
     */
    public function send_order_message($params=array())
    {
        if(empty($params['goods_id']) || empty($params['type'])) {
            return  false;
        }
        $store_id = _uri('goods',array('id'=>$params['goods_id']),'store_id');
        $filter=array(
            'goods_id'           => $params['goods_id'],            'type'               => $params['type'],
            'store_id'           => $store_id,        );

        _model('message')->create($filter);
        return 'ok';
    }

    public function rest_message($params,$id)
    {
        $appid        = Config::get('open189_aid');
        $access_token = self::get_access_token();
        $sendsms_url  = "http://api.189.cn/v2/emp/templateSms/sendSms";

        if (!isset($params['tel']) || !$params['tel']) {
            return false;
        }

        if (!isset($params['content']) || !$params['content']) {
            return false;
        }

        $request_params = array();
        $update_time=date('Y-m-d H:i:s');
        $request_params['acceptor_tel']     = $params['tel'];
        $request_params['template_param']   = $params['content'];
        $request_params['template_id']      = $params['template_id'];
        $request_params['grant_type']       = 'authorization_code';
        $request_params['app_id']           = $appid;
        $request_params['access_token']     = $access_token;
        $request_params['timestamp']        = date('Y-m-d H:i:s');

        $curl = new AnCurl();
        $result = $curl->post($sendsms_url,http_build_query($request_params));
        $result_info = json_decode($result,true);

        //修改日志

        $message_log =  array(
                'phone'     => $params['tel'],
                'temp_id'   => $params['template_id'],
                'content'   => json_encode($params),
                'result'       => json_encode($result_info),
                'res_code'  =>$result_info['res_code'],
                'add_time' =>$update_time
        );

        $message_id = _model('message_log')->update($id,$message_log);

        if(isset($result_info['res_code']) && !empty($result_info['res_code'])) {
            _widget('email')->mail('短信下发失败', '日志id'.$message_id);

            return array('info' => 'error','msg'=> '短信下发失败');
        } else {
            return array('info' => 'ok','msg'=> '短信下发成功');
        }
    }
}