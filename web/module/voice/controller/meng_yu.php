<?php
/**
 * Created by PhpStorm.
 * Date: 2017-9-5
 * Time: 10:15
 * @功能概要：语音API接口调用DEMO
 * @公司名称： ShenZhen Montnets Technology CO.,LTD.
 */
////////////////////////////////////////////////////////////////////////////////////
//公共参数(帐号,密码,url地址)
require_once('lib/VoiceSendConn.php');

class Action
{
    private $mobile = 15701651914;
    private $user_id = 'E103E2';
    private $pwd     = '9Z082X';
    private $url     = 'http://api02.monyun.cn:7901/voice/v2/std/';
    public function index()
    {
        //南方语音节点url地址
        // $url = 'http://api01.monyun.cn:7901/voice/v2/std/';
        //北方语音节点url地址
        //$url = 'http://api02.monyun.cn:7901/voice/v2/std/';

        $VoiceSendConn = new VoiceSendConn($this->url);

        $data=array();

        //设置账号(必填)
        $data['userid'] = $this->user_id;
        //设置密码（必填.填写明文密码,如:1234567890）
        $data['pwd']    = $this->pwd;
        ///////////////////////////////////////////////////////////////////////////////////

        /*
        * 模板发送 接口调用
        */
        // 设置手机号码 此处只能设置一个手机号码(必填)
        $data['mobile']  = $this->mobile;
        // 消息类型(必填,默认填1)
        $data['msgtype'] = '3';

        // 语音模版ID(必填)  // 语音模版编号
        $data['tmplid'] = '';
        // 语音验证码内容(必填,且只能是数字) //语音内容：
        $data['content'] = '';//'123456';
        // 回拨显示号码(可选)
        $data['exno']='';
        // 用户自定义流水编号(可选)
        $data['custid']='';

        try {
            $result = $VoiceSendConn->templateSend($data);
            if ($result['result'] === 0) {
                print_r("模板语音发送成功！");
            } else {
                print_r("模板语音发送失败，错误码：" . $result['result']);
            }
        }catch (Exception $e) {
            print_r($e->getMessage());//输出捕获的异常消息，请根据实际情况，添加异常处理代码
            return false;
        }

        /*
         * 查询剩余金额或条数
         */
         /*
         
        try {
            $result = $VoiceSendConn->getBalance( $data);
            if ($result['result'] === 0) {
                if ($result['chargetype'] === 0) {
                    print_r("查询成功，当前计费模式为条数计费,剩余条数为：" . $result['balance']);
                } else if ($result['chargetype'] === 1) {
                    print_r("查询成功，当前计费模式为金额计费,剩余金额为：" . $result['money']."元");
                } else {
                    print_r("未知的计费类型");
                }
            } else {
                print_r("查询余额失败，错误码：" . $result['result']);
            }
        }catch (Exception $e) {
            print_r($e->getMessage());//输出捕获的异常消息，请根据实际情况，添加异常处理代码
            return false;
        }
        
        */
        
        
        /*
         * 获取状态报告
         */
         /*
         
        try {
            $result = $VoiceSendConn->getRpt($data);//获取状态报告
            if($result['result']===0)
            {
                print_r("获取状态报告成功");
                print_r($result['rpts']);//输出状态报告信息
            }
            else
            {
                print_r("获取状态报告失败，错误码：" .$result['result']);
            }
        }catch (Exception $e) {
            print_r($e->getMessage());//输出捕获的异常消息，请根据实际情况，添加异常处理代码
            return false;
        }
        
        */
        
    }
}
?>