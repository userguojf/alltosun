<?php
/**
 * alltosun.com 设备升级下发消息 upgrade.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-4-19 上午11:44:25 $
 * $Id$
 */

class Action
{
    public function __call($action = '', $param = array())
    {
        $version       = '2.4.2';
        $province_name = '北京';

        $province_info = _model('province')->read(array('name' => $province_name));

        if ( !$province_info ) return '省信息不存在';

//         $filter['version_no']  = $version;
        $filter['province_id'] = $province_info['id'];
        $filter['status']      = 1;

        $list = _model('screen_device')->getList($filter);

        $stat = $yyt_ids = [];

        foreach ($list as $k => $v) {
            if ( $v['version_no'] == $version ) {
                if ( !isset($stat[$v['business_id']]['up']) ) {
                    $stat[$v['business_id']]['up'] = 1;
                } else {
                    ++ $stat[$v['business_id']]['up'];
                }
            } else {
                if ( !isset($stat[$v['business_id']]['un_up']) ) {
                    $stat[$v['business_id']]['un_up'] = 1;
                } else {
                    ++ $stat[$v['business_id']]['un_up'];
                }
            }
        }

        foreach ($stat as $k => $v) {
            array_push($yyt_ids, $k);
//             $yyt_info = _model('business_hall')->read(array('id' => $k));
//             $v['un_up']= isset($v['un_up']) ? $v['un_up'] : 0 ;
//             $v['up'] = isset($v['up']) ? $v['up']: 0 ;
//             p($yyt_info['title'].' |  未升级数量：'. $v['un_up'] . ' | 升级设备：'.$v['up']);
        }

        if ( !$yyt_ids ) return '暂无未升级的营业厅';

        $user_number_arr = _model('business_hall')->getFields('user_number',
                array('id' => $yyt_ids));

        $unique_id_arr =_model('public_contact_user')->getFields('unique_id',
                array('user_number' => $user_number_arr));

        if ( !$user_number_arr || !$unique_id_arr ) return '未找到对应营业厅';

        $touser = '';

        foreach ($unique_id_arr as $k => $v) {
            $touser = $touser.'|'.$v;
        }

        // 发消息  最多支持1000个
        $this->send_msg(trim($touser, '|'));

//         return true;
    }

    public function send_msg($touser)
    {
        p($touser);
        if ( !$touser ) return false;
exit();
// 注意给那个应用发消息
// 标题  内容
        $params = '{
            "touser": "'. $touser .'",
            "msgtype": "news",
            "agentid": 21,
            "news": {
                 "articles":[
                {
               "title": "亮靓安装指南",
               "description": "你的亮靓还在烧屏？？？
更新亮靓V2.4版，烧屏什么的，不存在的。
点击左下角牛角标→软件升级，下载安装，多任务界面锁定，重启亮屏自启动即可。
",
               "url": "https://qy.weixin.qq.com/cgi-bin/show?uin=NTA2MDEzNzUz&videoid=1014_f6302dbe4e0c4818b84195acd414e164",
               "picurl": "'. SITE_URL .'/images/install_video.jpg'.'"
                }
            ]
            }
        }';

        $info = _widget ( 'qydev.send_msg' )->send_message ($touser, $params );

        if ( isset($info['errmsg']) && $info['errmsg'] == 'ok' ) {
            $this->record('install', $business_hall_id, $touser);
        }

        return true;
    }
}