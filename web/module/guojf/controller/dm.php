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
 * $Date: 2018-1-30 下午3:36:51 $
 * $Id$
 */

class Action
{
    public function no_use()
    {

        $id = tools_helper::get('id', '0');
        $limit = 10;

        $list = _model('screen_tui_yyt_record')->getList(
                array(
                        'id >'   => $id,
                ),
                " LIMIT {$limit} "
        );
// p($list);
// exit();
        if ( !$list ) {
            p('暂无信息');
            exit();
        }

        foreach ( $list as $k => $v ) {
            $id = $v['id'];
            // 跳转页面需要

            $b_info = _model('business_hall')->read(array('user_number' => $v['user_number']));

            $param = array(
                    'province' => screen_helper::get_field_info('province', $b_info['province_id'], 'name'),
                    'title'    => $b_info['title'],
                    'user_number' => $b_info['user_number'],
                    'name'        => $b_info['contact'],
                    'phone'       => $b_info['contact_way'],
                    'depart_ids'  => '2,145'
            );

            $api_result = $this->apply_dm_api($param);
        }

        echo "<script>window.location.href = '". AnUrl("guojf/dm?id=$id")."'</script>";
    }

    public function index()
    {
//         $url = 'http://mac.pzclub.cn/api/dm/user_edit';
        $url = AnUrl('api/dm/user_edit');

        $appid     = 'wifi_shujdt_awzdxhyadrtggbrd';
        $app_key   = 'd1cb99814ddc2d11cdd8c099b6e5c6e8';
        $timestamp = time();

        $data = array(
                'operation'   => 'create',
                'appid'       => $appid,
                'timestamp'   => $timestamp,
                'token'       => md5($appid.'_'.$app_key.'_'.$timestamp),
                'province'    => '澳门',
                'business_hall_title' => '澳门市级管理员',//易联讯达滨河皓月园合作厅
                'user_number' => 'aomens_yyt',//1101001011531
                'name'        => '杨冰玉',
                'phone'       => '18811135105',//'15701651914',
                'depart_ids'  => '2，145', //'2,145'
        );
// p($data);
// p($url);
        $response_info = curl_post($url, $data);
p($response_info);
//         return  json_decode($response_info, true);
    }
}