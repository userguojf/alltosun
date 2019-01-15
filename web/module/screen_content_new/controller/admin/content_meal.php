<?php

/**
 * alltosun.com  content_meal.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2018年1月16日 下午4:34:56 $
 * $Id$
 */

class Action
{
    private $per_page = 10;
    private $member_id  = 0;
    private $member_res_name = '';
    private $member_res_id   = 0;
    private $ranks           = 0;
    private $time;

    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $this->time        = date('Y-m-d H:i:s');
        $member_info = member_helper::get_member_info($this->member_id);

        if ($member_info) {
            $this->member_res_name = $member_info['res_name'];
            $this->member_res_id   = $member_info['res_id'];
            $this->ranks           = $member_info['ranks'];

            Response::assign('member_info', $member_info);
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

    public function __call($action = '', $params = array())
    {
        // 内容展示必须符合各省的条件
        $search_filter = Request::Get('search_filter', array());
        $order_dir     = Request::Get('order_dir', 'order');

        $default_value  = array(
                    //各省投放列表start
                    'province'      => '',
                    'city'          => '',
                    'area'          => '',
                    //end
                    'business_hall' => '',
                    'type'          => 1,
                    'search_type'   => 0,
                    'put_type'      => 1
                );

        $search_filter  = set_search_filter_default_value($search_filter, $default_value);

        if (isset($search_filter['title']) && $search_filter['title']) {
            $filter['title'] = $search_filter['title'];
        }
        
        //组装条件
        if ($search_filter['put_type'] == 1) {
            $filter['res_name'] = $this->member_res_name;
            $filter['res_id']   = $this->member_res_id;

        } else if ($search_filter['put_type'] == 2) {
                $province_id      = city_helper::get_province_id($this->member_res_name, $this->member_res_id);

                $content_res_filter['province_id'] = array(0,$province_id);

                $content_res_filter['ranks <'] = $this->ranks;

                $content_ids        = _model('screen_meal_res')->getFields('content_id', $content_res_filter);

                array_unique($content_ids);

                if (empty($content_ids)) {
                    $filter['id'] = 0;
                } else {
                    $filter['id'] = $content_ids;
                }
        } else {
                $province_id      = city_helper::get_province_id($this->member_res_name, $this->member_res_id);

                $content_res_filter['province_id'] = array($province_id);
                $content_res_filter['ranks >'] = $this->ranks;

                if ($this->member_res_name == 'group') {
                    $content_res_filter= array('ranks >' => 1);
                }

                $content_ids        = _model('screen_meal_res')->getFields('content_id', $content_res_filter);
                array_unique($content_ids);

                if (empty($content_ids)) {
                    $filter['id'] = 0;
                } else {
                    $filter['id'] = $content_ids;
                }
        }

        if ($search_filter['search_type'] == 0) {
            $filter['status <']        = 2;
        } elseif ($search_filter['search_type'] == 1) {
            $filter['start_time <=']   = $this->time;
            $filter['end_time >']      = $this->time;
            $filter['status']          = 1;

        } elseif ($search_filter['search_type'] == 2) {
            $filter['end_time <=']     = $this->time;
            $filter['status <']        = 2;

        } elseif ($search_filter['search_type'] == 3) {
            $filter['start_time >']   = $this->time;
            $filter['status <']        = 2;

        } elseif ($search_filter['search_type'] == 4) {
            $filter['start_time <=']   = $this->time;
            $filter['end_time >']     = $this->time;
            $filter['status']          = 0;
        } elseif ($search_filter['search_type'] == 5) {
            $filter['status']          = 2;
        }

        //各省投放列表start
        if ($search_filter['province']) {
            $province_ids = screen_cotnent_helper::get_search_ids('province' ,$search_filter['province'],'focus');
            if (empty($province_ids)) {
                $filter['id'] = 0;
            } else {
                $filter['id'] = $province_ids;
            }

        }
        if ($search_filter['city']) {
            $city_ids = screen_cotnent_helper::get_search_ids('city' ,$search_filter['city'],'focus');
            //p($city_ids);
            if (empty($city_ids)) {
                $filter['id'] = 0;
            } else {
                $filter['id'] = $city_ids;
            }
        }
        if ($search_filter['area']) {
            $area_ids = screen_cotnent_helper::get_search_ids('area' ,$search_filter['area'],'focus');
            if (empty($area_ids)) {
                $filter['id'] = 0;
            } else {
                $filter['id'] = $area_ids;
            }
        }
        if (!empty($search_filter['province'])) {
            $province = array('province_id' => $search_filter['province']);
            Response::assign('where1' , $province);
        }
        if (!empty($search_filter['city'])) {
            $city = array('city_id' => $search_filter['city']);
            Response::assign('where2' , $city);
        }

        if ($search_filter['business_hall']) {
            $business_ids = screen_content_new_helper::get_search_business(trim($search_filter['business_hall']),'focus');
            if (empty($business_ids)) {
                $filter['id'] = 0;
            } else {
                $filter['id'] = $business_ids;
            }
        }

        //end
        $content_list  = array();
        $pop_count     = 0;
        $click_count   = 0;
        $run_time      = 0;
        
        $content_count = _model('screen_content_meal')->getTotal($filter);

        if ($content_count) {
            $pager = new Pager($this->per_page);
            $content_list = _model('screen_content_meal')->getList($filter, ' ORDER BY `id` DESC '.$pager->getLimit());

            if ($pager->generate($content_count)) {
                Response::assign('pager', $pager);
            }
        }

        $start_date = '';
        $end_date   = '';
        
        if (!empty($search_filter['start_date']) && !empty($search_filter['end_date'])) {
            $start_date = $search_filter['start_date'];
            $end_date   = $search_filter['end_date'];
        }
        
        foreach ($content_list as $k => $v) {
            $pop_count   += screen_stat_helper::get_meal_stat_num($v['id'], 1, $start_date, $end_date);
            $click_count += screen_stat_helper::get_meal_stat_num($v['id'], 2, $start_date, $end_date);
            $run_time    += screen_stat_helper::get_meal_stat_num($v['id'], 2, $start_date, $end_date, 'run_time');
            
        }
        
        Response::assign('start_date', $start_date);
        Response::assign('end_date', $end_date);
        
        Response::assign('pop_count', $pop_count);
        Response::assign('click_count', $click_count);
        Response::assign('run_time', $run_time);
        Response::assign('content_list', $content_list);
        Response::assign('search_filter', $search_filter);
        Response::assign('order_dir', $order_dir);
        Response::display("admin/content_meal/content_meal.html");
    }

    /**
     * 删除信息
     * @return string
     */
    public function delete()
    {
        $id = Request::getParam('id');
        if (empty($id)) {
            return '请选择您要操作的信息';
        }
        $id = explode(',', trim($id, ','));
        foreach ($id as $v) {
            $content_info = _uri('screen_content_meal', $v);
            if (!$content_info) {
                continue;
            }
            if ($content_info['status'] != 1) {
                _model('screen_content_meal')->delete($id);
            }

            _model('screen_content_meal')->update($id, array('status'=>0));
        }
        return 'ok';
    }

    /**
     * 彻底删除
     * @return string
     */
    public function thorough_delete()
    {
        $id = Request::getParam('id');
        if (empty($id)) {
            return '请选择您要操作的信息';
        }

        $content_info = _uri('screen_content_meal', $id);
        if (!$content_info) {
            return '您要删除的信息不存在';
        }

        if ($content_info['status'] != 0) {
            return '您要删除的信息不在回收站';
        }

        _model('screen_content_meal')->delete($id);
        return 'ok';
    }

    /**
     * 还原信息
     * @return string
     */
    public function recover()
    {
        $id = Request::getParam('id');
        if (empty($id)) {
            return '请选择您要操作的信息';
        }

        $content_info = _uri('screen_content_meal', $id);
        if (!$content_info) {
            return '您要删除的信息不存在';
        }

        if ($content_info['status'] != 0) {
            return '您要删除的信息不在回收站';
        }

        _model('screen_content_meal')->update($id, array('status'=>1));

        return 'ok';
    }

    public function add()
    {

        Response::display("admin/content_meal/content_meal_add.html");
    }

    public function edit()
    {
        $id          = Request::Get('id', 0);
        $search_type = Request::Get('search_type', 1);

        if (!$id) {
            return '请选择您要操作的信息';
        }

        $content_info = _uri('screen_content_meal', $id);

        if (!$content_info || $content_info['status'] == 2) {
            return '您操作的信息不存在';
        }

        $type = 0;  // 1-静态 2-动态

//         //链接
//         if ($content_info['type'] == 3) {
//             $type = 1;
//             //图片、机型宣传图、视频
//         } else if ($content_info['type'] == 1 || $content_info['type'] == 4){
//             $link_path = UPLOAD_PATH.'/'.$content_info['link'];
//             //是否为动图
//             if (screen_content_helper::is_animated_gif($link_path)){
//                 $type = 2;
//             } else {
//                 $type = 1;
//             }

//         } else if ($content_info['type'] == 2) {
//             $type = 2;
//         }

//         $content_info['is_roll_num_disabled']       = true;
//         $content_info['is_roll_interval_disabled']  = true;

//         // 1-静态 2-动态
//         if ($type == 1) {
//             $content_info['is_roll_interval_disabled']       = false;
//         } else if ($type == 2){
//             $content_info['is_roll_num_disabled']  = false;
//         }

        Response::assign('search_type', $search_type);
        Response::assign('content_info', $content_info);
        Response::display("admin/content_meal/content_meal_add.html");
    }

    public function save()
    {
        $content_id    = Request::Post('id', 0);
        $content       = Request::Post('content', array());
        $put_type    = Request::Post('put_type', 2);
        $search_type = Request::Post('search_type', 0);


        //单独验证
        if (empty($content['start_time']) || !$content['start_time']) {
            return '开始时间不能为空';
        }

        if (empty($content['end_time']) || !$content['end_time']) {
            return '结束时间不能为空';
        }

        if (empty($content['type']) || !$content['type']) {
            return '请选择内容类型';
        }

        if ($content['type'] == 1) {
            $content['ext_link'] = '';
        } else {
            $content['content'] = '';
        }
        // 执行上传
        $link = false;

        $type = $content['type'];

        if ($link) {
            $content['link'] = $link;
        }

        //修改
        if ($content_id) {
            $content_info = _uri('screen_content_meal', $content_id);

            if (!$content_info) {
                return '对不起，该信息不存在';
            }

            _model('screen_content_meal')->update($content_id, $content);
        } else {

            $content['res_name']  = $this->member_res_name;
            $content['res_id']  = $this->member_res_id;
            $content['member_id'] = $this->member_id;

            //非宣传图则默认发布
            if ($type != 4) {
                $content['status']    = $put_type==0?$put_type:1;   //默认发布 -wangjf
            }

            $content_id = _model('screen_content_meal')->create($content);

        }

        if ($put_type == 1) {
            //如果是设备宣传图则强制进行投放
                $this->put_group_by_id($content_id, $this->member_res_name , $this->member_res_id ,$this->ranks, 'screen_meal_res');
        //
        } elseif ($put_type == 2) {
            Response::redirect(AnUrl("screen_content/admin/content_meal/put?id={$content_id}"));
        }

//         if ($put_type) {
//             push_helper::push_msg('2');
//         }

        return array('操作成功', 'success', AnUrl("screen_content/admin/content_meal?search_filter[search_type]={$search_type}"));
    }

    public function put() {
        $content_id = Request::Get('id' , 0);

        if (!$content_id) {
            return '参数不合法！';
        }

        $content_info = _uri('screen_content_meal', $content_id);

        if (!$content_info) {
            return '内容不能修改!';
        }

        if ($this->member_res_name == 'group') {
            $province_list = array_to_option(city_helper::get_province_list(),'name');

            Response::assign('province_list', $province_list);
        } elseif ($this->member_res_name == 'province') {
            $city_list = array_to_option(city_helper::get_city_list_by_province_id($this->member_res_id),'name');

            Response::assign('province_id', $this->member_res_id);
            Response::assign('city_list', $city_list);
        } elseif ($this->member_res_name == 'city') {
            $area_list = array_to_option(city_helper::get_area_list_by_city_id($this->member_res_id), 'name');

            $city_info = _uri('city', $this->member_res_id);

//             if (Request::Get('test' , 0)) {
//                 p($area_list);
//             }

            Response::assign('city_info', $city_info);
            Response::assign('province_id', $city_info['province_id']);
            Response::assign('city_id', $this->member_res_id);
            Response::assign('area_list', $area_list);

        } elseif ($this->member_res_name == 'area') {
            $business_hall_list = array_to_option(city_helper::get_business_hall_list_by_area_id($this->member_res_id));

            $area_info = _uri('area', $this->member_res_id);

            Response::assign('area_info', $area_info);
            Response::assign('province_id', $area_info['province_id']);
            Response::assign('city_id', $area_info['city_id']);
            Response::assign('area_id', $this->member_res_id);
            Response::assign('business_hall_list', $business_hall_list);
        } else {
            $business_hall_info = _uri('business_hall', $this->member_res_id);

            Response::assign('province_id', $business_hall_info['province_id']);
            Response::assign('city_id', $business_hall_info['city_id']);
            Response::assign('area_id', $business_hall_info['area_id']);

            Response::assign('business_hall_info', $business_hall_info);
        }

        $filter['status'] = 1;

        //查询所有的机型
        $phone_names = _model('screen_device')->getList($filter, " GROUP BY `phone_name`");
        //p($phone_names);exit;

        //投放列表
        $content_list = _model('screen_meal_res')->getList(array('content_id' => $content_id));

        Response::assign('phone_names', $phone_names);
        Response::assign('content_list', $content_list);
        Response::assign('content_id', $content_id);
        Response::assign('content_info', $content_info);
        Response::display('admin/content_meal/put.html');
    }
    
    public function detail()
    {
        $content_meal_id  = Request::Get('content_meal_id', 0);
        if (!$content_meal_id) {
            return '请选择内容';
        }
        
        $meal_info = _uri('screen_content_meal', $content_meal_id);
        if (!$meal_info) {
            return '内容不存在或已被删除';
        }
        
        Response::assign('meal_info', $meal_info);
        
        Response::display('admin/content_meal/meal_detail.html');
    }
    
    //点击量的详情页
    public function click_detail()
    {
        $id            = Request::get('id' , 0);
        $table         = Request::get('table' , '');
        $field         = Request::get('field' ,'');

        $date          = Request::get('date' , 0);
        $province_id   = Request::get('province_id' ,0);
        $city_id       = Request::get('city_id' ,0);
        $area_id       = Request::get('area_id' , 0);

        $res_name    = Request::get('res_name','');
        $res_id      = Request::get('res_id',0);
        $put_type    = Request::get('put_type',0);

        $business_hall_id   = Request::get('business_hall_id' ,0);

        $table_info = screen_helper::get_click_details($id,$table,$field,$date,$province_id,$city_id,$area_id,$business_hall_id,$res_name,$res_id,$put_type);

        if ($date) {
            Response::assign('date' , $date);
        }
        if ($province_id) {
            Response::assign('province_id' , $province_id);
        }
        if ($city_id) {
            Response::assign('city_id' , $city_id);
        }
        if ($area_id) {
            Response::assign('area_id' , $area_id);
        }
        if ($table) {
            Response::assign('table' , $table);
        }
        if ($res_id==0 || $res_id) {
            Response::assign('res_id' , $res_id);
        }
        if ($res_name) {
            Response::assign('res_name' , $res_name);
        }
        if ($put_type) {
            Response::assign('put_type' , $put_type);
        }
        Response::assign('id' , $id);
        Response::assign('field' , $field);

        Response::assign('table_info' , $table_info);
        //点击量编译模板判断
        if ($res_name == 'group') {
            Response::display('admin/group_click_detail.html');
        } else if ($res_name == 'province') {
            Response::display('admin/province_click_detail.html');
        } else if ($res_name == 'city') {
            Response::display('admin/city_click_detail.html');
        } else if ($res_name == 'area') {
            Response::display('admin/area_click_detail.html');
        } else if ($res_name == 'business_hall') {
            Response::display('admin/business_click_detail.html');
        }

    }
    
    /**
     * 投放指定范围
     */
    public static function put_group_by_id($screen_content_id, $res_name ,$res_id, $ranks, $table='screen_meal_res')
    {
        //$screen_content_id
        if (!$screen_content_id || !$res_name || !$ranks) {
            return false;
        }
    
        $filter = array('content_id' => $screen_content_id);
    
        //删除已经投放的记录
        _model($table)->delete($filter);
    
        //重新全国投放
        $filter['res_name']    = $res_name;
        $filter['res_id']      = $res_id;
        $filter['ranks']       = $ranks;
    
        $info = _uri($res_name ,$res_id);
    
        if ($ranks == 2) {
            $filter['province_id'] = $info['id'];
        } else if ($ranks > 2) {
            $filter['province_id'] = $info['province_id'];;
        }
    
        if ( $ranks == 3)  {
            $filter['city_id'] = $info['id'];
        } else if ($ranks > 3) {
            $filter['city_id'] = $info['city_id'];;
        }
    
        if ( $ranks == 4) {
            $filter['area_id'] = $info['id'];
        } else if ($ranks > 4) {
            $filter['area_id'] = $info['area_id'];;
        }
    
        $member_info = member_helper::get_member_info();
        if ($member_info) {
            $filter['issuer_res_name']  = $member_info['res_name'];
            $filter['issuer_res_id']    = $member_info['res_id'];
        }
        
        _model($table)->create($filter);
    
        return true;
    }
}
?>