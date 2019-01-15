<?php

/**
 * alltosun.com 验证码类
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京共创阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2009-04-13 23:37:57 +0800 $
 * $Id: reCAPTCHA.php 203 2012-04-08 14:45:23Z gaojj $
*/

class reCAPTCHA
{
    private $publickey = null;
    private $privatekey = null;
    private $error = null;
    const RECAPTCHA_API_SERVER = 'http://api.recaptcha.net';
    const RECAPTCHA_API_SECURE_SERVER = 'https://api-secure.recaptcha.net';
    const RECAPTCHA_VERIFY_SERVER = 'http://api-verify.recaptcha.net/verify';

    public function __construct($publickey, $privatekey)
    {
        if (empty($publickey) || empty($privatekey))
        {
            throw new Exception("To use reCAPTCHA you must get an API key from http://recaptcha.net/api/getkey");
        }
        $this->publickey = $publickey;
        $this->privatekey = $privatekey;
    }

    public function check($remoteip = null, $recaptcha_challenge_field = null, $recaptcha_response_field = null)
    {
        $remoteip === null && $remoteip = $_SERVER["REMOTE_ADDR"];
        $recaptcha_challenge_field === null && $recaptcha_challenge_field = $_POST["recaptcha_challenge_field"];
        $recaptcha_response_field === null && $recaptcha_response_field = $_POST["recaptcha_response_field"];
        $array = array (
                    'privatekey' => $this->privatekey,
                    'remoteip' => $remoteip,
                    'challenge' => $recaptcha_challenge_field,
                    'response' => $recaptcha_response_field
        );
        $response = self::post(self::RECAPTCHA_VERIFY_SERVER, $array);
        $response = array_map('trim', explode("\n",trim($response)));
        $this->error = $response[1];

        return $response[0] == 'true';
    }

    private static function post($url, $data)
    {
        $data = http_build_query($data);
        $opts = array (
            'http' => array (
                'method'  => 'POST',
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n" .
                             "Content-Length: " . strlen($data) . "\r\n" .
                             "User-Agent: reCAPTCHA/PHP\r\n",
                'content' => $data
            )
        );

        $context = stream_context_create($opts);
        $html = @file_get_contents($url, false, $context);

        return $html;
    }

    public function generate($options = array(), $secure = false)
    {
        $server = $secure ? self::RECAPTCHA_API_SECURE_SERVER : self::RECAPTCHA_API_SERVER;
        $error = $this->error ? '&error=' . $this->error : '';
        $option = '';
        if ($options)
        {
            $option .= '<script type="text/javascript"> var RecaptchaOptions = {';
            foreach ($options as $key => $val)
            {
                $option .= "$key : '$val',";
            }
            $option = rtrim($option, ',');
            $option .= '};</script>';
        }
        return $option . '
               <script type="text/javascript" src="'. $server . '/challenge?k=' . $this->publickey . $error . '"></script>
               <noscript>
               <iframe src="'. $server . '/noscript?k=' . $this->publickey . $error . '" height="300" width="500" frameborder="0"></iframe><br/>
               <textarea name="recaptcha_challenge_field" rows="3" cols="40"></textarea>
               <input type="hidden" name="recaptcha_response_field" value="manual_challenge"/>
        </noscript>';
    }
}

?>