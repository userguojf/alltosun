<?php
/**
 * alltosun.com 通讯录设置接收事件服务器 user_callback.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-5-8 上午11:14:09 $
 * $Id$
 */

include MODULE_PATH.'/wework/controller/weixin/WXBizMsgCrypt.php';

class Action
{

    //开发机和正式机的地址
    private $url              = '';

    private $corpId           = "wx1a1fb37c4adad916";
    private $agent_id         = 3;
    private $encodingAesKey   = "fJ7a14poEbamVDZ6B8HAiAtIfJQIAadgKVU75uRabCR";
    private $token            = "alltosun";

    private $sVerifyMsgSig    = '';
    private $sVerifyTimeStamp = '';
    private $sVerifyNonce     = '';

    private $to_user_name     = '';
    private $msg_encrypt      = '';
    private $timestamp        = '';

    public function __construct()
    {
        $this -> sVerifyMsgSig    = Request::get('msg_signature', '');
        $this -> sVerifyTimeStamp = Request::get('timestamp', '');
        $this -> sVerifyNonce     = Request::get('nonce', '');
    }


    public function index()
    {
        //首次加载验证
        if (Request::Get('echostr')) {
            $this->valid();
            exit;
        }

        //接口能力返回信息
        $this->parse_request();
    }

    /**
     * 获取参数和返回信息
     */
    public function parse_request()
    {
        if (ONDEV) {
            $request_data = file_get_contents('php://input');
        } else {
            $request_data = (isset($GLOBALS["HTTP_RAW_POST_DATA"]) && !empty($GLOBALS["HTTP_RAW_POST_DATA"])) ? $GLOBALS["HTTP_RAW_POST_DATA"] : '' ;
        }

        if (empty($request_data)) {
            return 'xml请求数据$request_data为空。';
        }

        $request = simplexml_load_string($request_data, 'SimpleXMLElement', LIBXML_NOCDATA);

        //解密
        $msg = '';
        $wxcpt   = new WXBizMsgCrypt($this -> token, $this -> encodingAesKey, $this -> corpId);

        $errCode = $wxcpt->DecryptMsg($this -> sVerifyMsgSig, $this -> sVerifyTimeStamp, $this -> sVerifyNonce, $request_data, $msg);

        if ($errCode == 0) {
            //操作返回的XML数据
//             $request = simplexml_load_string($msg);
 
            $array = wework_xml_helper::to_array( $msg );
            $Event = $array['Event'];
//             _model('wework_test_record')->create(array('content' => json_encode($array)));

            // 消息类型
            if ( 'event' == $array['MsgType'] ) {
                // subscribe user_id = $FromUserName
                if ( 'subscribe' == $Event ) {
                    wework_user_helper::loacal_create('work', $array['ToUserName']);
                    return true;
                }
                // unsubscribe
                if ( 'unsubscribe' == $Event ) {
                    wework_user_helper::unsubscribe( $array['ToUserName'] );
                    return true;
                }

                // 通讯录操作
                if ( 'change_contact' == $Event ) {
                    // change_contact

                    if ( 'create_user' == $array['ChangeType'] || 'update_user' == $array['ChangeType'] ) {
                        $filter = [];

                        $filter['user_id']     = $array['UserID'];

                        // 新的UserID，变更时推送（userid由系统生成时可更改一次）
                        //             $NewUserID  = AnFilter::filter_string(trim($request->NewUserID));

                        $filter['name']       = $array['Name'];
                        $filter['department'] = $array['Department'];
                        $filter['mobile']     = $array['Mobile'];
                        $filter['position']   = $array['Position'];
                        $filter['gender']     = $array['Gender'];
                        $filter['email']      = $array['Email'];

                        $filter['status']       = $array['Status'];
                        $filter['avatar']       = $array['Avatar'];
                        $filter['english_name'] = $array['EnglishName'];
                        $filter['isleader']     = $array['IsLeader'];
                        $filter['telephone']    = $array['Telephone'];

                        // 过滤
                        foreach ($filter as $k => $v) {
                            if ( !$v ) unset($filter[$k]);
                        }

                        $extattr               = $array['ExtAttr'];

                        if ( $extattr ) {
                            foreach ($extattr['Item'] as $key => $val) {
                                $filter[$val['Name']] = $val['Value'];
                            }
                        }

                        if ( 'create_user' == $array['ChangeType']  ) {
                            _model('wework_user')->create($filter);
                        } else {
                            _model('wework_user')->update(array('user_id' => $filter['user_id']), $filter);
                        }
                    }
                }
            }


        } else {
            print("ERR: " . $errCode . "\n\n");
        }
    }

    /**
     * 首次开启回调模式的验证
     */
    private  function valid()
    {
        $sEchoStr = '';

        $wxcpt          = new WXBizMsgCrypt($this -> token, $this -> encodingAesKey, $this -> corpId);
        $sVerifyEchoStr = Request::Get("echostr");
        $errCode        = $wxcpt->VerifyURL($this -> sVerifyMsgSig, $this -> sVerifyTimeStamp, $this -> sVerifyNonce, $sVerifyEchoStr, $sEchoStr);

        if ($errCode == 0) {
            // 验证URL成功，将sEchoStr返回
            echo $sEchoStr;
        } else {
            print("ERR: " . $errCode . "\n\n");
        }
    }

}