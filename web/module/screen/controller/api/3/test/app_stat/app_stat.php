<?php

/**
 * alltosun.com  app_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2018年1月25日 下午6:44:27 $
 * $Id$
 */

class Action
{
    public function add_stat()
    {
        $test = array(
            array(
                'record_time'=> "2018-01-25 17:10:00",
                'content'    => array(
                        array(
                            "app_name"=>"QQ",
                            "run_time"=>"333",
                            "open_count"=>"11"
                        ),
                        array(
                            "app_name"=>"wechat",
                            "run_time"=>"333",
                            "open_count"=>"11"
                        ),
                       array(
                            "app_name"=>"jnytd",
                            "run_time"=>"333",
                            "open_count"=>"2"
                       ),
                ),
                
            ),
            
            array(
                'record_time'=> "2018-01-23 16:10:00",
                'content'    => array(
                    array(
                        "app_name"=>"QQ",
                        "run_time"=>"333",
                        "open_count"=>"11"
                    ),
                    array(
                        "app_name"=>"wechat",
                        "run_time"=>"333",
                        "open_count"=>"11"
                    ),
                    array(
                        "app_name"=>"jnytd",
                        "run_time"=>"333",
                        "open_count"=>"2"
                    ),
                ),
            
            ),
        );
//         p(json_encode($test));
//         exit;
        $user_number  = tools_helper::get('user_number', '1101081002052');
        $device_unique_id         = tools_helper::get('device_unique_id', 'ecd09f3f22f4');
        $info               = '[
                                    {
                                        "type":1,
                                        "record_time":"2018-01-25",
                                        "content":[
                                            {
                                                "app_name":"QQ",
                                                "run_time":"333",
                                                "open_count":"11"
                                            },
                                            {
                                                "app_name":"wechat",
                                                "run_time":"333",
                                                "open_count":"21"
                                            }
                                        ]
                                    },
                                    {
                                        "type":2,
                                        "record_time":"",
                                        "content":[
                                            {
                                                "app_name":"QQ",
                                                "run_time":"333",
                                                "open_count":"333"
                                            },
                                            {
                                                "app_name":"wechat",
                                                "run_time":"333",
                                                "open_count":"333"
                                            }
                                        ]
                                    },
                                    {
                                        "type":3,
                                        "record_time":"",
                                        "content":[
                                            {
                                                "app_name":"QQ",
                                                "run_time":"333",
                                                "open_count":"333"
                                            },
                                            {
                                                "app_name":"wechat",
                                                "run_time":"333",
                                                "open_count":"333"
                                            }
                                        ]
                                    },
                                    {
                                        "type":4,
                                        "record_time":"",
                                        "content":[
                                            {
                                                "app_name":"QQ",
                                                "run_time":"333",
                                                "open_count":"333"
                                            },
                                            {
                                                "app_name":"wechat",
                                                "run_time":"333",
                                                "open_count":"333"
                                            }
                                        ]
                                    }
                                ]';
    
        //$info =  '{"content":[{"app_name":"亮靓","open_count":77,"run_time":47766817},{"app_name":"系统桌面","open_count":64,"run_time":2282089},{"app_name":"应用时间统计","open_count":4,"run_time":326282},{"app_name":"浏览器","open_count":1,"run_time":144763},{"app_name":"安全中心","open_count":36,"run_time":96281},{"app_name":"应用商店","open_count":8,"run_time":89597},{"app_name":"系统更新","open_count":2,"run_time":38024},{"app_name":"软件包安装程序","open_count":1,"run_time":36604},{"app_name":"设置","open_count":12,"run_time":27390},{"app_name":"Android Processes","open_count":2,"run_time":19577},{"app_name":"TimeManager","open_count":1,"run_time":17334},{"app_name":"系统用户界面","open_count":3,"run_time":6341},{"app_name":"授权管理","open_count":2,"run_time":806},{"app_name":"相机","open_count":0,"run_time":0},{"app_name":"通讯录与拨号","open_count":0,"run_time":0},{"app_name":"RxTools","open_count":0,"run_time":0},{"app_name":"短信","open_count":0,"run_time":0},{"app_name":"电话","open_count":0,"run_time":0},{"app_name":"用户反馈","open_count":0,"run_time":0},{"app_name":"通话管理","open_count":0,"run_time":0}],"record_time":1522207464,"type":1}}';

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();
    
        //$post_data['user_number'] = $user_number;
        // 加密
        $post_data['sign']              = api_helper::encode_sign($post_data);
    
        $post_data['user_number']       = $user_number;
        $post_data['device_unique_id']  = $device_unique_id;
        $post_data['info']              = $info;
        
        an_dump($post_data);

        $api_url = SITE_URL.'/screen/api/3/app_stat/app_stat/add_stat';
        $res = an_curl($api_url, $post_data, 0, 0);
        echo $res;
    
        an_dump(json_decode($res, true));
    }
}