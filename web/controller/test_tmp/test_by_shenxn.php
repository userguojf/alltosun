<?php
/**
 * alltosun.com  test_by_shenxn.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (shenxn@alltosun.com) $
 * $Date: 2016-6-4 下午3:05:33 $
 * $Id$
 */
class Action
{
    public function test3(){
        $dev = probe_helper::mac_decode('9c:da:3e:8f:c3:89');

       $list= _model('probe_1_17_129_110687_hour','awifi_probe')->getList(['mac' => $dev]);

        p($list);
    }

    public function test7()
    {
        $data = file_get_contents('http://mac.pzclub.cn/images/data/a.txt');
        
        $data = explode( chr(10) , $data);
        
        foreach ($data as $v) {
//             p($v);
$info = trim($v,"\n");
$info = trim($info,"\0");
//             $info = json_decode($info,true);
            p($info);
//             exit();

        }
//         $data = json_decode($data,true);
//         p($data);

    }
    
    
    public function test8()
    {
            $data = file_get_contents('http://mac.pzclub.cn/images/data/a.txt');
        
        $data = explode( chr(10) , $data);
        
        foreach ($data as $v) {
//             p($v);
$info = trim($v,"\n");
$info = trim($info,"\0");
            $info = json_decode($info,true);

            foreach ($info['device_list'] as $vv) {
                $a = explode('|', $vv);
                
                if ($a[0] == 'c4:0b:cb:1d:06:66'){
                    $time = date('Y-m-d H:i:s',$a[2]);
                    echo "MAC:{$a[0]}, 信号：{$a[1]},时间 {$time}<br/>";
                }
                }
            }
    }

    public function test9()
    {
        $data = file_get_contents('http://mac.pzclub.cn/images/data/b.txt');
    
        $data = explode( chr(10) , $data);
    
        foreach ($data as $v) {
            //             p($v);
            $info = trim($v,"\n");
            $info = trim($info,"\0");
            $info = json_decode($info,true);
    
            foreach ($info['device_list'] as $vv) {
                $a = explode('|', $vv);
    
                if ($a[0] == '74:d2:1d:0f:9c:2e'){
                    $time = date('Y-m-d H:i:s',$a[2]);
                    echo "MAC:{$a[0]}, 信号：{$a[1]},时间 {$time}<br/>";
                }
            }
        }
    }
    
    public function test11()
    {
        $data = file_get_contents('http://mac.pzclub.cn/images/data/c.txt');
    
        $data = explode( chr(10) , $data);
    
        foreach ($data as $v) {
            //             p($v);
            $info = trim($v,"\n");
            $info = trim($info,"\0");
            $info = json_decode($info,true);
    
            foreach ($info['device_list'] as $vv) {
                $a = explode('|', $vv);
    
                if ($a[0] == '00:ae:fa:7d:f9:53'){
                    $time = date('Y-m-d H:i:s',$a[2]);
                    echo "MAC:{$a[0]}, 信号：{$a[1]},时间 {$time}<br/>";
                }
            }
        }
    }
    
    public function test2()
    {
//         wework_user_helper::loacal_create(1, 1);
//         $list = _model('public_contact_user')->getList(['update_time' => '0000-00-00 00:00:00']);
        
//         foreach ($list  as $v) {
//             _model('public_contact_user')->update($v['id'] , ['update_time' => $v['add_time']]);
//         }

//         exit();
//         $data = file_get_contents('http://201711awifiprobe.alltosun.net/images/data/test2.csv');

//         $data = explode(chr(10) ,  $data);
        
//         foreach ($data as $v) {
//             $info = explode(',', $v);
            
//             _model('paidui_data')->create(['province'=> $info[0], 'city' => $info[1] , 'area' => $info[2] ]);
//         }

//         $list = _model('paidui_data') -> getList([1 => 1]);

//         $a = array();
//         foreach ($list as $v) {
//             $info = _model('city')->read(array('name' => "{$v['city']}"));
            
//             $k = $v['city'];
//             if (!$info) {
//                 $a[] = $k;
//             }
//         }
//         unset($a[0]);
//         $a = array_unique($a);
//         p($a);
//         p(count($a));
    }
    
    public function test4()
    {
        //         $data = file_get_contents('http://201711awifiprobe.alltosun.net/images/data/test2.csv');
    
        //         $data = explode(chr(10) ,  $data);
    
        //         foreach ($data as $v) {
        //             $info = explode(',', $v);
    
        //             _model('paidui_data')->create(['province'=> $info[0], 'city' => $info[1] , 'area' => $info[2] ]);
        //         }
    
        //         p($data);
        $list = _model('paidui_data') -> getList([1 => 1]);
    
        $a = array();
        foreach ($list as $v) {
            $c = trim($v['area']);
            $info = _model('area')->read(array('name' => "{$c}"));
 
            if (!$info) {
                $a[] = $v['area'];
            }
        }
        unset($a[0]);
        
        $a = array_unique($a);
        p($a);
        p(count($a));
    }

    public function test1()
    {
        $template_id = 91553794;
        
        //发短信验证码
        $content =  array(
            'param1' => '1',
            'param2' => '测试厅店',
            'param3' => 'http://test.pzclub.cn',
        );

        $params['tel']         = 18310925147;
        $params['content']     = json_encode($content);
        $params['template_id'] = $template_id;
        
        $msg_res = _widget('message')->send_message($params, 2);
        p($msg_res);
    }

    public function check_device_mac()
    {
        $filter = array(
            'mac'            => 193897492395173,
            'business_id'    => 46120
        );

        $list = _model('screen_device')->getList(array('business_id' => 46120));

        foreach ($list as $v) {
            p($v);
            $info = screen_device_helper::check_device_mac($v['mac'],$v['business_id']);
            p($info);
            echo '=======<br />=======';
        }
    }

    public function try_data($params)
    {
        //screen_everyday_offline_record
        $time               = time();
        $start_time = date('Y-m-d',$time - 3600 * 24 * 8).' 00:00:00';
        $end_time   = date('Y-m-d',$time - 3600 * 24).' 23:59:59';

        isset($params['start_time']) && $params['start_time'] ? $start_time = $params['start_time'] : '';
        isset($params['end_time']) && $params['end_time'] ? $end_time   = $params['start_time'] : '';

        $filter =  array(
            'add_time >=' => $start_time,
            'add_time <=' => $end_time
        );

        $sql  = " SELECT `business_hall_id`,`date`,count(*) as offline_num ";
        $sql .= " FROM `screen_everyday_offline_record` ";
        $sql .= " WHERE add_time >='{$start_time}' AND add_time <='{$end_time}' ";
        $sql .= " GROUP BY `business_hall_id`,`date` ORDER BY `id` ASC ";

        $list = _model('screen_everyday_offline_record')->getAll($sql);

        $data = $cache_data = $device_filter = array();

        //组装数据
        foreach ($list as $k => $v) {
            $data[$v['business_hall_id']][$v['date']]['offline_num'] = $v['offline_num'];

            //查询营业厅总数截止到当前时间点数据总数
            $device_filter['business_id'] = $v['business_hall_id'];
            $device_filter['add_time <='] = $end_time;
            $device_filter['status']      = 1;

            $device_install_count = _model('screen_device')->getTotal($device_filter);

            $data[$v['business_hall_id']][$v['date']]['install_num']  = $device_install_count;
            $data[$v['business_hall_id']][$v['date']]['install_rate'] = number_format($v['offline_num']/$device_install_count,2);
            $data[$v['business_hall_id']]['offline_total']            = $v['offline_num'];
        }

        return $data;
    }

    public function try_data1($params){
        p(screen_api2_helper::get_screen_info_by_device_unique_id('xxx'));
    }

    public function try_data2()
    {
        set_time_limit(0);

        $page = Request::Get('page', 1);

        $num = 1000;
        $pre_page = ($page - 1 ) * $num;
        $list = _model('screen_everyday_offline_record')->getList(array('device_nickname_id' => 0), " ORDER BY `id` ASC LIMIT {$pre_page},{$num} ");

        if (!$list) {
            echo '数据导出完毕';
            exit();
        }

        foreach ($list as $v) {
            
            if ($v['device_nickname_id']) {
                continue;
            }

            $info = _uri('screen_device',array('device_unique_id' => $v['device_unique_id']));

            if (!$info) {
                continue;
            }

           _model('screen_everyday_offline_record')->update($v['id'], array(
               'device_nickname_id' => $info['device_nickname_id'],
               'phone_name'  => $info['phone_name'],
               'phone_version' => $info['phone_version']
           ));
        }

        ++$page;
        echo "<script>window.location.href = '".AnUrl("test_by_shenxn/try_data2?page={$page}")."'</script>";
        exit();
    }
    
    public function data_upload()
    {
//         $data = [
//             ['1101001051286','北京', '北京','朝阳区','博翼鑫讯北苑易事达合作厅','北京市朝阳区北苑家园秋实街1号易事达购物休闲广场一层C01','张莎','18010099513','1'],
//             ['1101052001430','北京', '北京','朝阳区','华信通十里河合作厅','北京市朝阳区十里河瑞安大厦底商（十里河桥东北角）','罗能平','010-87366153','2'],
//             ['1101082001417','北京', '北京','海淀区','星球通五道口合作厅','海淀区五道口华清嘉园1号楼底商','刘永','18901118255','2'],
//             ['1101052001971','北京', '北京','朝阳区','国美西坝河专项厅','北京市朝阳区北三环东路7号','王卉','18910877667','1'],
//             ['1101052001812','北京', '北京','朝阳区','迪信通双井专项厅','朝阳区广渠门外大街8号（大中电器西侧）','石宇','18911660166','4'],
//             ['1101001011202','北京', '北京','朝阳区','苏宁慈云寺专项厅','北京市朝阳区慈云寺苏宁生活广场','王帆','18901363573','1'],
//             ['1101062001955','北京', '北京','丰台区','大中洋桥专项厅','北京丰台洋桥桥东南海户里1号','陈丽哲','18911063663','1'],
//             ['1100001014382','北京', '北京','西城区','四维通联黄寺大街合作厅','北京市西城区黄寺大街23号院1号楼1013','李风亭','15330090088','1'],
//             ['1101001011340','北京', '北京','通州区','畅捷东方物资学院路合作厅','北京市通州区物资学院路6号','王贝','13311100032','1'],
//             ['1101001011246','北京', '北京','朝阳区','易联讯达双井百安居合作厅','北京市朝阳区广渠路31号百安居一层','王洋','17710364441','1']
//         ];

                $data = [
                    ['1101052001408','北京', '北京','朝阳区','北京华信通朝外大街合作厅','朝阳区朝外大街丙10号（蓝岛大厦西侧）','李梦齐','15301088876','1'],
    ];
        foreach ($data as $k => $v) {
            $info = _model('business_hall')->read(['user_number' => $v[0]]);
            
            if ($info) {
                continue;
            }

            $area_info = _uri('area',['name' => $v[3]]);
            
            if (!$area_info) {
                p($area_info);
                continue;
            }

            p($area_info);

            $filter = array(
                'title'       => $v[4],
                'type'        => 4,
                'area_id'     => $area_info['id'],
                'city_id'     => $area_info['city_id'],
                'province_id' => $area_info['province_id'],
                'user_number' => $v[0],
                'address'     => $v[5],
                'store_level' => $v[8],
                'contact'     => $v[6],
                'contact_way' => $v[7],
                'wifi_res_id' => 999990+$k
            );
            p($filter);
            $business_id = _model('business_hall')->create($filter);

            $member_info = _model('member')->read(
                array('res_name'=> 'business_hall',
                    'res_id'      => $business_id
                )
            );

            if ($member_info) {
                continue;
            }

            $member_id = _model('member')->create(
                array(
                    'member_user' => $v[0],
                    'member_pass' => md5('Awifi@123'),
                    'res_name'    => 'business_hall',
                    'res_id'      => $business_id,
                    'ranks'       => 5,
                    'hash'       => uniqid()
                )
                );

            _model('group_user')->create(
                array(
                    'member_id'  => $member_id,
                    'group_id'   => 26,
                )
                );
        }
    }
    public function test5()
    {
        member_helper::remember_me_expire();
    }
}