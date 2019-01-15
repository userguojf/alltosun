<?php
/**
  * alltosun.com 邮件发送 email.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年1月30日 下午5:00:10 $
  * $Id$
  */

class email_widget
{
//     /**
//      * 邮件提醒
//      */
//     public function thumb_mail($title)
//     {
//         $to[0]  = 'wangdk@alltosun.com';
//         $header= 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/html; charset=UTF-8' . "\r\n";
//         $title = 'make_wookmark_thumb '.$title;
//         $content = date('Y-m-d H:i:s');
//         $this->send_email($to, $title, $content);
//     }

    /**
     * 发送邮件函数
     * @param string $email 邮箱地址·
     * @param array $search 被替换的内容
     * @param array $replace 替换的内容
     */
    public  function send_email($email, $title = '', $content = '')
    {

       // 实例化phpmailer
        $mail = new PHPMailer();
        // 设置发送邮件的协议：SMTP
        $mail->IsSMTP();
        // 发送邮件的服务器
        $mail->Host = "smtp.189.cn";
        // 打开SMTP
        $mail->SMTPAuth = true;
        // SMTP账户
        $mail->Username = "01058507421@189.cn";
        //是否使用HTML格式
        $mail->IsHTML(true);
        // SMTP密码
        $mail->Password = "china@vnet8.cn";
        $mail->From = 'pzclub@189.cn';
        $mail->FromName = "电信Awifi";
        if (is_array($email)) {
        	foreach ($email as $v) {
        	    $mail->AddAddress("$v", "");
        	}
        } else {
        	    $mail->AddAddress("$email", "");
        }
        //设置字符集编码
        $mail->CharSet = "UTF-8";
        $mail->Subject = "=?UTF-8?B?".base64_encode($title)."?=";
        //邮件内容（可以是HTML邮件）
        $mail->Body = $content;
        $mail->AltBody ="text/html";

        if (!$mail->Send()) {
            return false;
        } else {
            return true;
        }
    }


}
?>