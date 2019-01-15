<?php
/**
 * alltosun.com  dm.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-5-11 下午3:53:08 $
 * $Id$
 */
/**
 * 
 * {
    "access_token": "MTUyNTk0NTk3ODU1NjQ3MDQ4MTYwNzY4MDU5MTM5NTM5MDE3MDA3NzEyMzE1QHRydXN0b21wMjQxLmh1YXdlaWNhYXMuY29t",
    "refresh_token": "MTUyNTk0NTk3ODU1NjE4MDU0OTcwODk0MDM1ODczMzI5MzUxODYwOTE5ODgzQHRydXN0b21wMjQxLmh1YXdlaWNhYXMuY29t",
    "resultcode": "0",
    "expires_in": "172800",
    "resultdesc": "Succeed"
}
{
    "access_token": "MTUyNTk0NzI1NDQxNjI0ODM2MDM0Nzg3ODQzOTMxMDU0MDA1NDYxNTQyNzg2QHRydXN0b21wMjQxLmh1YXdlaWNhYXMuY29t",
    "refresh_token": "MTUyNTk0NzI1NDQxNjE0MTE1NTM5MzYwOTg0MDcxNzc3NjIyNTg5ODM3NTY4QHRydXN0b21wMjQxLmh1YXdlaWNhYXMuY29t",
    "resultcode": "0",
    "expires_in": "172800",
    "resultdesc": "Succeed"
}
 *
 */

class Action
{
    private $username = 'CaaS_Test_01';
    private $pwd      = '9Z082X';

    private $app_key  = 'GlH49M2FCzWTajwR0CfTKFPV8n8X';

    private $access_token = '';

    private $log_id = 0;

    public function __construct()
    {
        // 记录所有的参数
        $content = json_encode($_POST);
        $this->log_id =_model('wework_test_record')->create(array('content' => $content));

        // 获取access_token
        $this->access_token = $this->get_access_token();
    }

    private function get_mc_token()
    {
      
    }

    public function get_access_token()
    {
        $info = _model('voice_access_token')->read(array('id' => 4));
// p($info);

        $time = time() - 60 * 60 * 24;

        if ( $info && $info['expires_in'] > time() ) {
            return $info['access_token'];
        }

        $url  = 'https://117.78.29.67/rest/fastlogin/v1.0?';
        $url .= 'app_key='.$this->app_key.'&username='. $this->username;

        $ch = curl_init();

        // 对方要求header头 
        // 空格就调不通
        $headers = array(
                "Accept: application/json",
                'application/x-www-form-urlencoded; charset=UTF-8',
                'Authorization:CaaS2.0?'
        );

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_PORT, '10443');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $json = curl_exec($ch);
        $result =  json_decode($json, true);

        // 记录
        $this->msg_reocrd($json);

        if ( !isset($result['resultcode']) || $result['resultcode'] ) {
            api_helper::return_data(1, 'access_token接口', $result);
        }

        if ( $info ) {
            _model('voice_access_token')->update($info['id'], 
            array(
                        'access_token' => $result['access_token'],
                        'refresh_token' => $result['refresh_token'],
                        'resultcode' => $result['resultcode'],
                        'expires_in' => time() + $result['expires_in'],
                        'resultdesc' => $result['resultdesc'],
                )
            );
        } else {
            
            _model('voice_access_token')->create(
                    array(
                            'access_token' => $result['access_token'],
                            'refresh_token' => $result['refresh_token'],
                            'resultcode' => $result['resultcode'],
                            'expires_in' => time() + $result['expires_in'],
                            'resultdesc' => $result['resultdesc'],
                    )
            );
        }

        return $result['access_token'];
    }

    public function __call($action = '', $param = array())
    {

        api_helper::check_token('post');

        $phone       = tools_helper::post('phone', '');
        $user_number = tools_helper::post('user_number', '');

        $num     = tools_helper::post('num', 1);
        $minute  = tools_helper::post('minute', 30);

        if (!$phone || !$user_number) { //|| !$minute
            // 记录
            $this->msg_reocrd('有参数有空值');

            api_helper::return_data(1, '有参数为空');
        }

        $bindNbr    = '+8678880005669';
        $displayNbr = '+8696512';
        $templateId = "hudong_01";

        $phone = '+86'.$phone;

        $url  = 'https://117.78.29.66/rest/httpsessions/callnotify/v2.0?';

        $url .= 'app_key='.$this->app_key;
        $url .= '&access_token='.$this->access_token;
        $url .= '&format=json';

        $data = [
            "bindNbr"      => $bindNbr,
            "displayNbr"   => $displayNbr,
            "calleeNbr"    => $phone,
            "playInfoList" => [
//             'ttsContent' => '互动阳光郭剑峰'
              ['templateId' => $templateId , 'templateParas' => [$num, $minute]]
                        ]
        ];

        $json = json_encode($data);

        $ch = curl_init();

        $headers = array(
                'Content-Type:application/json; charset=UTF-8',
        );

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_PORT, '10443');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $json = curl_exec($ch);

        // 记录
        $this->msg_reocrd($json);

        $result = json_decode($json, TRUE );

        if ( !isset($result['resultcode']) || $result['resultcode'] ) {
            api_helper::return_data(1, '语音通知接口', $result);
        }

//         _model('wework_test_record')->update(
//             array('id' => $this->log_id), 
//             array('result' => $json)
//         );

        api_helper::return_data();
    }

    public function msg_reocrd($json)
    {
        _model('wework_test_record')->update(
            array('id' => $this->log_id),
            array('result' => $json)
        );
    }
    
}
?>