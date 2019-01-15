<?php
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
    private $mobile  = 15701651914;
    private $user_id = 'E103E2';
    private $pwd     = '9Z082X';
    private $url     = '';

    private $access_token_key = 'access_token_key';
    private $access_token = '';
    private $app_key = 'GlH49M2FCzWTajwR0CfTKFPV8n8X';

    public function __construct()
    {
//         $this->get_mc__token();
    }

    private function get_mc_token()
    {
        global $mc_wr;

        $this->access_token = $mc_wr->get($this->access_token_key);

        if ( !$this->access_token ) {
            
        }

        return $this->access_token;
    }

    public function get_token()
    {
        // 互呼
//         $app_key = 'RbH4ws3ECRB0dRI9T83rHedEAawa';
        // 语音
        $app_key = $this->app_key;

        $url  = 'https://117.78.29.66/rest/fastlogin/v1.0?';
        $url .= 'app_key='.$app_key.'&username=CaaS_Test_01';

        $ch = curl_init();

        //对方要求header头
        $headers = array(
                "Accept: application/json",
                'application/x-www-form-urlencoded; charset=UTF-8',
                'Authorization:CaaS2.0?'
        );

        //设置cURL允许执行的最长毫秒数。
//         curl_setopt($ch, CURLOPT_TIMEOUT_MS,2000);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // 用来指定连接端口
        curl_setopt($ch, CURLOPT_PORT, '10443');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE );
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $r =  curl_exec($ch);
//         p(curl_getinfo($ch));
         p($r);
    }

    public function phone()
    {
        $token = 'MTUyNTk0NzI1NDQxNjI0ODM2MDM0Nzg3ODQzOTMxMDU0MDA1NDYxNTQyNzg2QHRydXN0b21wMjQxLmh1YXdlaWNhYXMuY29t';

        $url  = 'https://117.78.29.67/rest/httpsessions/click2Call/v2.0?';
        $url .= 'app_key=RbH4ws3ECRB0dRI9T83rHedEAawa';
        $url .= '&access_token='.$token;
        $url .= '&format=json';

        $data = [
            "bindNbr"=>"+8678880000103", 
            "displayNbr"=>"+8696500", 
            "callerNbr"=>"+8615701651914", 
            "displayCalleeNbr"=>"+8696500",
            "calleeNbr"=>"+8615701651914", 
        ];

        $json = json_encode($data);
//         echo $json;exit();

        $ch = curl_init();

        //对方要求header头
        $headers = array(
                'Content-Type:application/json; charset=UTF-8',
                'Authorization:CaaS2.0?'
        );

        //设置cURL允许执行的最长毫秒数。
        //         curl_setopt($ch, CURLOPT_TIMEOUT_MS,2000);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // 用来指定连接端口
        curl_setopt($ch, CURLOPT_PORT, '10443');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $r =  curl_exec($ch);
        //         p(curl_getinfo($ch));
        p($r);
    }

    /**
     * {
    "access_token": "MTUyNjAyMzkxNDMzMDgxMTY0NTA4NzgxOTAxMjc1NDYzNTg4MzMzMjA4MTg0QHRydXN0b21wMjQxLmh1YXdlaWNhYXMuY29t",
    "refresh_token": "MTUyNjAyMzkxNDMzMDE2ODA4NjA0NzkxMjMzMDA1MjU0NDI5ODE3NjU5MDc5QHRydXN0b21wMjQxLmh1YXdlaWNhYXMuY29t",
    "resultcode": "0",
    "expires_in": "172800",
    "resultdesc": "Succeed"
}
     */
    public function index()
    {

        $token = 'MTUyNjAyMzkxNDMzMDgxMTY0NTA4NzgxOTAxMjc1NDYzNTg4MzMzMjA4MTg0QHRydXN0b21wMjQxLmh1YXdlaWNhYXMuY29t';

        $url  = 'https://117.78.29.66/rest/httpsessions/callnotify/v2.0?';
        $url .= 'app_key=GlH49M2FCzWTajwR0CfTKFPV8n8X';
        $url .= '&access_token='.$token;
        $url .= '&format=json';
// p($url);exit();
        $data = [
            "bindNbr"      => "+8678880005669",
            "displayNbr"   => "+8696512",
            "calleeNbr"    => "+8615701651914",
            "playInfoList" => [
              ['templateId' => 'test_template01_kuaidi', 'templateParas' => ['1', '天通中苑二区']]
                        ]
        ];
  
        $json = json_encode($data);
//         echo $json;
//         exit();
// '{
//     "bindNbr": "+8678880005669",
//     "displayNbr": "+8696512",
//     "calleeNbr": "+8615701651914",
//     "playInfoList": [
//         {
//             "templateId": "test_template01_kuaidi",
//             "templateParas": [
//                 "1",
//                 "天通中苑二区"
//             ]
//         }
//     ]
// }';
//         $json = '{ 
//             "bindNbr":"+8678880005669", 
//             "displayNbr":"+8696512", 
//             "calleeNbr":"+8615701651914", 
//             "playInfoList":
//                 [
//         {"templateId":"test_template01_kuaidi", "templateParas":["3","人民公园正门"]}
//                 ], 
//            }';

        $ch = curl_init();

        //对方要求header头
        $headers = array(
                'Content-Type:application/json; charset=UTF-8',
        );

        //设置cURL允许执行的最长毫秒数。
        //         curl_setopt($ch, CURLOPT_TIMEOUT_MS,2000);

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        // 用来指定连接端口
        curl_setopt($ch, CURLOPT_PORT, '10443');

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($ch, CURLOPT_POST, TRUE );
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $r =  curl_exec($ch);
        //         p(curl_getinfo($ch));
        p($r);

    }
}
?>