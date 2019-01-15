<?php
/**
 * alltosun.com  e.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-3-27 下午8:55:56 $
 * $Id$
 */

class e_widget
{
    //access_token
    private $access_token     = NULL;
   
    //1、消息型应用
    //应用ID
    private $new_agent_id     = 3;

    //new类型
    private $new_title        = '您有一条用户反馈信息';

    //注：链接的地址有拼接 因为要免登陆
    private $new_url           = 'http://201512awifi.alltosun.net/e/qydev';

    /**
     * 注：消息型应用
     * $member    营业厅的账号
     * $type      发送消息的类型
     */
    public function send_msg($touser ,  $path='' , $text)
    {
        if (!ONDEV) {
            $this->new_url = 'http://wifi.pzclub.cn/e/qydev';
        }

        $params = array();

        $params['data'] = "{
                            \"touser\"  : \"$touser\",
                            \"msgtype\" : \"news\",
                            \"agentid\" : $this->new_agent_id,
                            \"news\": {
                                \"articles\":[
                                    {
                                        \"title\"       : \"$this->new_title\",
                                        \"description\" : \"$text\",
                                        \"url\"         : \"$this->new_url?tab=spitslot&menu_stat=msg\",
                                        \"picurl\"      : \"$path\"
                                    }
                                    ]
                            }
                        }";

        //发送数据 失败才有返回值
        $info = _widget('qydev.send_msg')->send_message($params);

        if ($info) {
            _model('qydev_error_log')->create(array('res_name'=>'send_msg','error_ch'=>$info));
        }
    }
}