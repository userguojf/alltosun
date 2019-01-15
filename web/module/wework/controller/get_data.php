<?php
/**
 * alltosun.com 数据表的准备工作 get_depart.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-5-3 下午5:18:03 $
 * $Id$
 */

class Action
{
    private $access_token = '';

    public function __construct()
    {
//         if ( !ONDEV ) {
//             echo '正式环境不允许';
//             exit();
//         }
        $this->access_token = _widget('wework.token')->get_access_token('work');

        if (!$this->access_token) {
            return false;
        }
    }

    public function  depart()
    {
        set_time_limit(0);
//         exit();
        $id = 1;

        $url = wework_config::$get_department_list_url."access_token=".$this->access_token;

        $url .= "&id=".$id;

        $json_info = curl_get($url);
        $info      = json_decode($json_info,true);

        foreach ($info['department'] as $v) {
            $filter = [
                'name' => $v['name'],
                'depart_id' => $v['id'],
                'pid'       => $v['parentid']
            ];
            _model('wework_department')->create($filter);
        }
    }

    public function user1()
    {
        $department_id = 1;
        $fetch_child   = 1;
        $status        = 1;

        $url = wework_config::$deaprt_user_detail_url.'access_token='.$this->access_token;
        $url .= '&department_id='.$department_id.'&fetch_child='.$fetch_child;

        $info = json_decode(curl_get($url) , true);
//         p($info);exit();

        foreach ($info['userlist'] as $v) {
            _model('use_test_record')->create(array('content' => json_encode($v)));
        }
        p('完成');
        exit();
        
    }

    public function user()
    {

        $page = tools_helper::Get('page', 1);

        $limit = 10;
        $page_num = ( $page - 1) * $limit;

        $list = _model('use_test_record')->getList(
                array(1 => 1), " LIMIT $page_num, $limit ");

        if ( !$list ) {
            echo '结束';
            exit();
        }

        foreach ($list as $k => $v) {
            // 下次数据的地方
            $v = json_decode($v['content'], true);

            $filter = array (
                    'user_id' => $v['userid'], 
                    'name'    => $v['name'], 
                    'department' => implode(',', $v['department']), 
                    'position'   => $v['position'], 
                    'mobile' => $v['mobile'], 
                    'gender' => $v['gender'], 
                    'email'  => $v['email'], 
                    'avatar' => $v['avatar'], 
                    'status' => $v['status'], 
                    'enable' => $v['enable'], 
                    'isleader'     => $v['isleader'], 
                    'english_name' => $v['english_name'], 
                    'telephone' => $v['telephone'], 
                    'qr_code'   => $v['qr_code'], 
            );

            foreach ($v['extattr'] as $key => $val) {
                foreach ( $val as $value ) {
                    $filter[$value['name']] = $value['value'];
                }
            }

            $res = _model('wework_user')->create($filter);
        }

        $page = $page + 1;

        echo "<script>window.location.href = 'http://201711awifiprobe.alltosun.net/wework/get_data/user?page={$page}'</script>";
        Response::flush();
        exit();
    }

    // 
    public function edit()
    {
        $arr = array( 
                1 => '马岩岩', 
                2 => '申小宁', 
                3 => '郭剑峰'
            );

        foreach ( $arr as $k => $v) {
            $info = _model('wework_user')->read(array('name' => $v));

            $id = $info['id'];
            unset($info['id']);

            $update = _model('wework_user')->read(array('id' => $k));
            unset($update['id']);

            _model('wework_user')->update(array('id' => $k), $info);
            _model('wework_user')->update(array('id' => $id), $update);
        }
    }


    public function department_line()
    {
        $id = Request::Get('id', 1);

        if ( $id == 9 ) return '完成';

        $business_line = array(
                '1' => 2, // 数字地图
                '2' => 4, //集团公司
                '3' => 145, //爱WIFI
                '4' => 6, //O2O事业部
                '5' => 147,   //ibeacon
                '6' => 211,   //一体化排队
                '7' => 30274, //测试用户
                '8' => 30348  //亮屏管理
        );

        $department_id = $business_line[$id];
        $fetch_child   = 1;
        $status        = 1;

        $url  = wework_config::$get_department_list_url.'access_token='.$this->access_token;
        $url .= '&id='.$department_id;

        $info = json_decode(curl_get($url) , true);

        if ( !isset($info['errcode']) || $info['errcode'] ) return '接口错误';

        foreach ($info['department'] as $v) {
            $department_info = _model('wework_department')->read(array('depart_id' => $v['id']));

            if ( !$department_info ) continue;

            $type = $business_line[$id];

            _model('wework_department')->update($department_info['id'], array('type' => $type));
        }

        ++ $id;
        echo "<script>window.location.href = '". AnUrl('wework/get_data/department_line') ."?id={$id}'</script>";
        exit();
    }
    
    public function delete()
    {
        exit();
        $obj = Request::Get('obj', '');

        $table = array(
                'depart' => 'wework_department',
                'user'   => 'wework_user',
                'test'   => 'wework_test_record',
        );

        if ( !isset($table[$obj]) ) return false;

        $res = _model($table[$obj])->getAll( " TRUNCATE TABLE `$table[$obj]` " );
        p($res);
    }
}