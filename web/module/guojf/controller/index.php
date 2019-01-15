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
    private $obj = 0;

    public function index()
    {

       if ( [1] != [2]) {
           return 123;
       }
        exit();
        p( strtotime('2018-08-02') - strtotime('2018-08-01') - 3600*24 );

//         foreach ($array as &$val) {
//             p($val);
//             $array[1] = 1;
//         }

//         $array = [0, 1, 2];
//         foreach ($array as $k => $val) {
//             p($val);
//             unset($array[1]);
//         }
//         P($array);
    }

    public function count()
    {
       $list1 = _model('screen_device')->getList(
               array(
                      'day >=' => 20180601,
                      'day <=' => 20180630,
       ));
       $list2 = _model('screen_device')->getList(
               array(
                       'day >=' => 20180701,
                       'day <=' => 20180731,
               ));
       p(count($list1));
       p(count($list2));
    }
    public function dele_happening()
    {
        _model('screen_daily_behave_happening_record')->delete(array(1 => 1));
    }
    
    public function mgdb()
    {
        p(date('Y-m-d H:i:s', time() - 30 * 60));
        $filter = [
            'business_id'      => (int)458091,
            'device_unique_id' => 'fc64ba905fa1',
        ];

        $sort = [
            'limit' => 1,
            'sort' => ['_id' => -1]
        ];

        $list = _mongo('screen', 'screen_device_online')->findOne($filter, $sort);

//         $list = $list->toArray();
        p($list);
        p($list['_id']);
    }
    public  function index1()
    {
        $stat_info = $info  = array(
                'province_id'  => 1,
                'city_id'      => 17,
                'area_id'      => 120,
                'business_id'  => 46120,
                'device_unique_id' => 'ALLTOSUN',
                'day'          => (int)date("Ymd"),
        );

        $info['ceshi'] = 'ceshi';

        $online_id = _mongo('screen', 'screen_device_online')->insertOne($info);
exit();
        if ( 20180208 > 20180209 ) {
            echo 1;
        } else {
            echo 2;
        }
        exit();
        $b = '1d9';
        echo $b;
        echo '<br>';
        echo ++$b;

        echo '<br>';
        $a = 1;
        $b = &$a;
        $b =  $a ++;

        echo $a,$b;

        echo '<br>';
        $a = 1;
        echo $a  +  $a ++;

        echo '<br>';
        $j = 1;
        $i = &$j;
//         $i = $j ++;
//         $array[$i] = $j ++ ;
echo $i,$j;
// p($array);
        echo '<br>';
        if ( $res = $this->foo() ) {
            echo '777';
        } else {
            echo '666';
        }
        exit();

        $param = [];
        $param['province_id'] = 1;
        $param['city_id'] = 17;
        $param['area_id'] = 120;
        $param['business_id'] = 46204;
        $param['res_link'] = '/2018/01/19/20180119113246000000_1_112865_61.png';

        $param['phone_name'] = '海信';
        $param['phone_version'] = 'H10';
        $param['retail_price'] = '256G/6199';
        $param['contract_price'] = '64G/1399';
        $param['recommended_position'] = '169不限量';
        
        $param['retail_price'] = '6199';
        $param['contract_price'] = '1399';
        $param['recommended_position'] = '169不限量';
        
        $param['selling_point_1'] = '2.5D高清';
        $param['selling_point_2'] = '弧面屏';
        $param['selling_point_3'] = '前置2000万';
        $param['selling_point_4'] = '柔光自拍';
        $param['selling_point_5'] = '急速快充';
        $param['selling_point_6'] = '持久续航';
        $param['param_1'] = '5.5英寸屏';
        $param['param_2'] = '3500mAh电池容量';
        $param['param_3'] = '';//'8核/高通骁龙430';
        $param['param_4'] = '';//'64G内存';
        $param['param_5'] = '';//'2000万前置摄像头';
        $param['param_6'] = '';//1200万后置摄像头';
 
//         _model('screen_content_set_meal')->create($param);
//         exit();
        $res = screen_photo_helper::screen_ps($param);

    }

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