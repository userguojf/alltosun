<?php
/**
 * alltosun.com  myself test index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-11-4 上午11:49:40 $
 * $Id$
 */
class Action
{
    private $obj = NULL;

    public function index()
    {
        $url = 'https://open.work.weixin.qq.com/wwopen/mpnews?mixuin=scSLDgAABwBHsNcOAAAUAA&mfid=WW0309-QrZIWAAABwAyZ2vV48uZjwUrTUj25&idx=0&sn=6f32ca7eb1a6fcbe5637f0ac1727c75c';
        $user_number = 'admin';
        $type        = 4;
        $url = screen_msg_helper::short_url_cache($url, $user_number, $type);

        p($url);
    }

    public  function index1()
    {}

    public function foo()
    {
        return true;
    }
//     $arr = array(
//             "rid"=>"865334031764944",
//             "sign"=>"6dc4a4bbf7f185d7c9efe5a645008235",
//             "source"=>"1002",
//             "device_unique_id"=>"5425eac0a58f",
//             "user_number"=>"3214324",
//             "time"=>"1515665954790",
//             "info"=>[
//             ["auto_start"=>1,"auto_start_time"=>"1515665484"],
//             ["auto_start"=>1,"auto_start_time"=>"1515665484"]
//             ],
//             "key"=>"alltosun2016",
//             "version"=>"2.0.2"
//         );
//     public function install()
//     {
//         $res = _widget('screen.install_send_msg')->send_msg('1101021002051_13', 2, 5, 46120);
//         p($res);
//     }

    
//     public function check()
//     {
//         $res = _widget('screen.first_instatll_check_send_msg')->send_msg('1101021002051_13', 2, 46120);;
//         p($res);
//     }
    
//     public function offline()
//     {
//         $res = _widget('screen.offline_send_msg')->send_msg('1101021002051_13', 2, 46120);
//         p($res);
//     }
//     public function index()
//     {
    	
//     }

//     public function export_business_device()
//     {
//         $date     = tools_helper::get('date', date('Ymd', time() -24*3600));
//         $res_name = tools_helper::get('res_name', 'group');
//         $res_id   = tools_helper::get('res_id', 0);

//         $res = screen_stat_helper::export_busienss_device($date, $res_name, $res_id);
//         p($res);
//     }

//     public function test()
//     {
//         $filter = array(
//                 array(
//                         '$match' => array('content_id' => 2 , 'day' => 20171205)
//                 ),
//                 array(
//                         '$group' => array(
//                                 '_id'              => array('content_id' => '$content_id'),
//                                 'experience_time'  => array('$sum' => '$action_num'),
//                         )
//                 ),
//                 array('$project' => array('experience_time' => 1))
//         );
//         $action_num_arr = _mongo('screen', 'screen_content_click_stat_day')->aggregate( $filter );

//         $arr = $action_num_arr -> toArray();
//         p($arr[0]['experience_time']);
//         //p($arrr);
//     }
}