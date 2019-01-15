<?php

/**
 * alltosun.com 内容管理 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Wangjf (wangjf@alltosun.com) $
 * $Date: Jun 13, 2014 6:02:25 PM $
 * $Id$
 */
require_once ROOT_PATH."/helper/PHPExcel.php";
require_once ROOT_PATH."/helper/PHPExcel/Writer/Excel2007.php";
class Action
{
    private $per_page = 10;
    private $member_id  = 0;
    private $member_res_name = '';
    private $member_res_id   = 0;
    private $member_info;
    private $ranks           = 0;
    private $time;

    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $this->time        = date('Y-m-d H:i:s');
        $this->member_info = member_helper::get_member_info($this->member_id);

        if ($this->member_info) {
            $this->member_res_name = $this->member_info['res_name'];
            $this->member_res_id   = $this->member_info['res_id'];
            $this->ranks           = $this->member_info['ranks'];

            Response::assign('member_info', $this->member_info);
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

    public function __call($action = '', $params = array())
    {
        // 内容展示必须符合各省的条件
        $search_filter = Request::Get('search_filter', array());
        $filter = array();
        //自身投放
        if (empty($search_filter['put_type']) || $search_filter['put_type'] == 1) {
            $filter['res_name'] = $this->member_res_name;
            $filter['res_id']   = $this->member_res_id;
            $search_filter['put_type'] = 1;
        //上级投放
        } else if ($search_filter['put_type'] == 2) {
            //省
            if ($this->member_res_name == 'province') {
                $filter['res_name'] = 'group';
                $filter['res_id']   = 0;
            //市
            } else if ($this->member_res_name == 'city') {
                $province_id = city_helper::get_province_id($this->member_res_name, $this->member_res_id);
                $filter['res_name'] = array('group', 'province');
                $filter['res_id'] = array(0, $province_id);
            } else if ($this->member_res_name == 'area') {
                $area_info = business_hall_helper::get_info_name($this->member_res_name, $this->member_res_id);
                $province_id = $area_info['province_id'];
                $city_id     = $area_info['city_id'];
                $filter['res_name'] = array('group', 'province', 'city');
                $filter['res_id'] = array(0, $province_id, $city_id);
            } else if ($this->member_res_name == 'business_hall') {
                $business_hall_info = business_hall_helper::get_info_name($this->member_res_name, $this->member_res_id);
                $province_id = $business_hall_info['province_id'];
                $city_id     = $business_hall_info['city_id'];
                $area_id     = $business_hall_info['area_id'];
                $filter['res_name'] = array('group', 'province', 'city', 'area');
                $filter['res_id']   = array(0, $province_id, $city_id, $area_id);
            }
        //下级投放
        } else {
            $content_res_filter = array();
            //集团
            if ($this->member_res_name == 'group') {
                $filter['res_name'] = array('province', 'city', 'area', 'business_hall');
                $content_res_filter = array();
                $region_filter = _widget('screen')->init_filter($this->member_info, $search_filter);
                //指定地区搜索
                if ($region_filter) {
                    //指定营业厅
                    if (!empty($region_filter['business_hall_id'])) {
                        $content_res_filter['res_name'] = 'business_hall';
                        $content_res_filter['res_id'] = $region_filter['business_hall_id'];
                        unset($region_filter['business_hall_id']);
                    //指定区域
                    } else {
                        $content_res_filter['res_name'] = $filter['res_name'];
                    }

                    $content_res_filter = array_merge($region_filter, $content_res_filter);
                    //过滤内容
                    $content_ids =  array_unique(_model('screen_content_res')->getFields('content_id', $content_res_filter));
                    if (empty($content_ids)) {
                        $filter['id'] = 0;
                    } else {
                        $filter['id'] = $content_ids;
                    }
                }

            //省市区
            } else {
                //查询所有厅id
                $business_ids = _model('screen_content')->getFields('res_id', array('res_name' => 'business_hall'));

                if ($this->member_res_name == 'province') {
                    //查询所有city_id
                    $city_ids = _model('city')->getFields('id', array('province_id' => $this->member_res_id));
                    //查询所有区id
                    $area_ids = _model('area')->getFields('id', array('province_id' => $this->member_res_id));
                    //查询所有厅id
                    $business_ids = _model('business_hall')->getFields('id', array(
                            'province_id' => $this->member_res_id,
                            'id' => $business_ids
                    ));

                    $content_ids_by_city = _model('screen_content')->getFields('id', array('res_name' => 'city', 'res_id' => $city_ids));
                    $content_ids_by_area = _model('screen_content')->getFields('id', array('res_name' => 'area', 'res_id' => $area_ids));
                    $content_ids_by_business = _model('screen_content')->getFields('id', array('res_name' => 'business_hall', 'res_id' => $business_ids));
                    $content_ids = array_merge($content_ids_by_city, $content_ids_by_area, $content_ids_by_business);

                } else if ($this->member_res_name == 'city') {
                    //查询所有区id
                    $area_ids = _model('area')->getFields('id', array('city_id' => $this->member_res_id));
                    //查询所有厅id
                    $business_ids = _model('business_hall')->getFields('id', array(
                            'city_id' => $this->member_res_id,
                            'id' => $business_ids
                    ));

                    $content_ids_by_area = _model('screen_content')->getFields('id', array('res_name' => 'area', 'res_id' => $area_ids));
                    $content_ids_by_business = _model('screen_content')->getFields('id', array('res_name' => 'business_hall', 'res_id' => $business_ids));
                    $content_ids = array_merge($content_ids_by_area, $content_ids_by_business);

                } else if ($this->member_res_name == 'area') {
                    //查询所有厅id
                    $business_ids = _model('business_hall')->getFields('id', array(
                            'area_id' => $this->member_res_id,
                            'id' => $business_ids
                    ));

                    $content_ids = _model('screen_content')->getFields('id', array('res_name' => 'business_hall', 'res_id' => $business_ids));
                }
                $content_ids =  array_unique($content_ids);
                if (empty($content_ids)) {
                    $filter['id'] = 0;
                } else {
                    $filter['id'] = $content_ids;
                }
            }

        }
        //全部
        if (empty($search_filter['search_type']) || $search_filter['search_type'] == 0) {
            $filter['status <']        = 2;
            $search_filter['search_type'] = 0;
            //在线
        } elseif ($search_filter['search_type'] == 1) {
            $filter['start_time <=']   = $this->time;
            $filter['end_time >=']      = $this->time;
            $filter['status']          = 1;
            //过期
        } elseif ($search_filter['search_type'] == 2) {
            $filter['end_time <=']     = $this->time;
            $filter['status <']        = 2;
            //未开始
        } elseif ($search_filter['search_type'] == 3) {
            $filter['start_time >']   = $this->time;
            $filter['end_time >']   = $this->time;
            $filter['status <']        = 2;
            //已下线
        } elseif ($search_filter['search_type'] == 4) {
            //$filter['end_time <']   = $this->time;
            $filter['status']          = 0;
        }

        if (!empty($search_filter['title'])) {
            $filter['title LIKE'] = '%'.$search_filter['title'].'%';
        }

        //end
        $count = _model('screen_content')->getTotal($filter);

        if ($count) {
            $pager = new Pager($this->per_page);
            $content_list = _model('screen_content')->getList($filter, ' ORDER BY `id` DESC '.$pager->getLimit());

            Response::assign('content_list', $content_list);

            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
        }
        Response::assign('count', $count);
        Response::assign('search_filter', $search_filter);
        Response::display("admin/content_list.html");
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
            $content_info = _uri('screen_content', $v);
            if (!$content_info) {
                continue;
            }
            if ($content_info['status'] != 1) {
                _model('screen_content')->delete($id);
            }

            _model('screen_content')->update($id, array('status'=>0));
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

        $content_info = _uri('screen_content', $id);
        if (!$content_info) {
            return '您要删除的信息不存在';
        }

        if ($content_info['status'] != 0) {
            return '您要删除的信息不在回收站';
        }

        _model('screen_content')->delete($id);
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

        $content_info = _uri('screen_content', $id);
        if (!$content_info) {
            return '您要删除的信息不存在';
        }

        if ($content_info['status'] != 0) {
            return '您要删除的信息不在回收站';
        }

        _model('screen_content')->update($id, array('status'=>1));

        return 'ok';
    }

    public function add()
    {
        Response::display("admin/add.html");
    }

    /**
     * 套餐图
     * jquery  load 所用
     */
    public function set_meal_add()
    {
        $filter = _widget('screen')->default_search_filter($this->member_info);

        $content_id = tools_helper::Get('content_id', 0);

        if (!$filter) {
            $filter = array(1=>1);
        }

        //查询本归属下的机型
        $device_nickname_ids = _model('screen_device')->getFields('device_nickname_id', $filter, ' GROUP BY `phone_name`');

        $device_nicknames = _model('screen_device_nickname')->getList(array('id' => $device_nickname_ids, 'status' => 1));

        $set_meal_info =  array();
        //卖点图
        if ($content_id) {

            $content_info = _uri('screen_content', $content_id);

            if (!$content_info || $content_info['status'] == 2) {
                $set_meal_list = array();
            } else {
                //查询此内容下的所有关联信息
                $set_meal_list = screen_content_meal_helper::get_set_meal_list(array('content_id' => $content_info['id']));
            }
            //p($set_meal_list);
            //合并套餐信息
            foreach ( $set_meal_list as $k => $v ) {
                if (!$set_meal_info) {
                    $set_meal_info = array(
                            'retail_price' => $v['retail_price'], //零售价
                            'recommended_position' => $v['recommended_position'], //推荐套餐
                            'selling_point_1' => $v['selling_point_1'], //卖点1
                            'selling_point_2' => $v['selling_point_3'], //卖点1
                            'selling_point_3' => $v['selling_point_5'], //卖点1
                            'param_1'           => $v['param_1'], //推荐价格
                            'param_2'           => $v['param_2'],//推荐价格
                            'param_3'           => $v['param_3'],//推荐价格
                            'param_4'           => $v['param_4'],//推荐价格
                            'param_5'           => $v['param_5'],//推荐价格
                            'param_6'           => $v['param_6'],//推荐价格
                            'phones'            => [
                                    [
                                            'phone_name' => $v['phone_name'],
                                            'phone_version' => $v['phone_version'],
                                            'device_nickname_id' => $v['device_nickname_id']
                                    ]
                            ]
                    );
                } else {
                    $set_meal_info['phones'][] = [
                            'phone_name' => $v['phone_name'],
                            'phone_version' => $v['phone_version'],
                            'device_nickname_id' => $v['device_nickname_id']
                    ];
                }
            }
        }
        Response::assign('set_meal_info', $set_meal_info);
        Response::assign('device_nicknames', $device_nicknames);
        Response::display('admin/set_meal_add.html');
    }

    public function edit()
    {
        $id          = Request::Get('id', 0);
        $search_type = Request::Get('search_type', 1);

        if (!$id) {
            return '请选择您要操作的信息';
        }

        $content_info = _uri('screen_content', $id);

        if (!$content_info || $content_info['status'] == 2) {
            return '您操作的信息不存在';
        }

        $type = 0;  // 1-静态 2-动态

        //链接
        if ($content_info['type'] == 3) {
            $type = 1;
            //图片、机型宣传图、视频
        } else if ($content_info['type'] == 1 || $content_info['type'] == 4){
            $link_path = UPLOAD_PATH.'/'.$content_info['link'];
            //是否为动图
            if (screen_content_helper::is_animated_gif($link_path)){
                $type = 2;
            } else {
                $type = 1;
            }
        //视频和卖点图
        } else if ($content_info['type'] == 2 || $content_info['type'] == 5) {
            $type = 2;
        }

        $content_info['is_roll_num_disabled']       = true;
        $content_info['is_roll_interval_disabled']  = true;

        // 1-静态 2-动态
        if ($type == 1) {
            $content_info['is_roll_interval_disabled']       = false;
        } else if ($type == 2){
            $content_info['is_roll_num_disabled']  = false;
        }
        //var_dump($content_info);
        Response::assign('search_type', $search_type);
        Response::assign('content_info', $content_info);
        Response::display("admin/add.html");
    }

    public function save()
    {
        $content_id    = Request::Post('id', 0);
        $content       = Request::Post('content', array());
        $put_type    = Request::Post('put_type', 2);
        $search_type = Request::Post('search_type', 0);
        //单独验证
        if (empty($content['title']) || !$content['title']) {
            return '标题不能为空';
        }

        if (empty($content['start_time']) || !$content['start_time']) {
            return '开始时间不能为空';
        }

        if (empty($content['end_time']) || !$content['end_time']) {
            return '结束时间不能为空';
        }

        if (empty($content['type']) || !$content['type']) {
            return '请选择内容类型';
        }


        // 执行上传
        $link = false;

        //图片 和 宣传图
        if ($content['type'] == 1 || $content['type'] == 4) {
            if (!empty($_FILES['img_link']['tmp_name'])) {

                $link = upload_file($_FILES['img_link'],false, 'focus');
                //生成缩略图
                _widget('screen_content')->make_thumb($link);
            }

            //宣传图//机型宣传图价格的处理（可选）
            if ( $content['type'] == 4 ) {
                if (!isset($content['font_color_type']) || !$content['font_color_type']) {
                    return '请选择字体颜色';
                }

                if (isset($content['is_specify']) && !in_array($content['is_specify'], array(0, 1))) {
                    unset($content['is_specify']);
                }

                if (isset($content['price']) && $content['price']) {
                    //给原图片压价格操作
                    $new_link = screen_helper::compose_screen_image($link, $content['price'], $content['font_color_type']);

                    if ($new_link) {
                        $content['new_link'] = $new_link;
                    }
                }

            }

            //视频
        } else if ($content['type'] == 2) {
            if (!empty($_FILES['video_link']['tmp_name'])) {
                $link_info = _widget('screen_content.video')->upload_video('video_link');
                if ($link_info['errno'] != 0) {
                    return array($link_info['msg']);
                }

                $link = $link_info['file'];
            }
            //套餐底图
        } else if ($content['type'] == 5) {

            $link = Request::Post('set_meal', '');
            //缩略图
            //_widget('screen_content')->make_thumb($link);
        }

        $type = $content['type'];

        if ($link) {
            $content['link'] = $link;
        }

        //修改
        if ($content_id) {
            $content_info = _uri('screen_content', $content_id);

            if (!$content_info) {
                return '对不起，该信息不存在';
            }

            if ($content['type'] == 4) {
                //图片变化就全改了 eidted by guojf
                if ($content['link']) {
                    //原有的
                    screen_helper::update_show_pic_info($content_id, $content['link'], $content['font_color_type']);

                    if (isset($content['price']) && $content['price']) {
                        //给原图片压价格操作
                        $new_link = screen_helper::compose_screen_image($content['link'], $content['price'], $content['font_color_type']);

                        if ($new_link) {
                            $content['new_link'] = $new_link;
                        }
                    }

                } else {
                    //价格存在       价格改变颜色改变     宣传图必须改变
                    if ($content['price'] && ($content_info['price'] != $content['price'] || $content_info['font_color_type'] != $content['font_color_type'])) {
                        //给原图片压价格操作
                        $new_link = screen_helper::compose_screen_image($content_info['link'], $content['price'], $content['font_color_type']);

                        if ($new_link) {
                            $content['new_link'] = $new_link;
                        }
                    }

                    //价格不存在
                    if (!$content['price']) {
                        $content['new_link'] = '';
                    }
                }

            }

            if (!$content['link']) {
                unset($content['link']);
                unset($content['type']);
            }

            _model('screen_content')->update($content_id, $content);

            //设置套餐参数
            if ($type == 5) {
                $res = screen_content_meal_helper::delete_set_meal_by_content_id($content_id);

                if ( $res != true ) {
                    return '服务器内部错误';
                }

                $res = $this->save_set_meal($content_id);
                if ($res != 'ok') {
                    return $res;
                }
            }

        } else {

            if (!$content['link']) {
                return '请上传或输入发布内容';
            }

            $content['res_name']  = $this->member_res_name;
            $content['res_id']  = $this->member_res_id;
            $content['member_id'] = $this->member_id;

            //非宣传图则默认发布
            if ($type != 4) {
                $content['status']    = $put_type==0?$put_type:1;   //默认发布 -wangjf
            }

            $content_id = _model('screen_content')->create($content);

            //设置套餐参数
            if ($type == 5) {
                $res = $this->save_set_meal($content_id);
                if ($res != 'ok') {
                    return $res;
                }
            }
        }

        //全范围投放
        if ($put_type == 1) {
            //如果是设备宣传图则强制进行投放
            if ($type == 4) {
                Response::redirect(AnUrl("screen_content/admin/put?id={$content_id}"));
                return true;
                //如果是套餐图则强制进行投放
            }
            //范围投放
            $res = $this->region_range_put($content_id, $type);

            if ($res != 'ok') {
                return '发布失败';
            }

            //部分投放
        } else if ($put_type == 2) {
            Response::redirect(AnUrl("screen_content/admin/put?id={$content_id}"));
        }

        return array('操作成功', 'success', AnUrl("screen_content/admin?search_filter[search_type]={$search_type}"));
    }

    /**
     * 范围投放
     */
    public function region_range_put($content_id, $type=0 )
    {
        $param = array(
                'province_id' => 0,
                'city_id'     => 0,
                'area_id'     => 0,
                'business_hall_ids' => array(0),
                'phone_name'  => '',
                'phone_version' => '',
                'content_id'  => $content_id
        );

        //省
        if ($this->member_res_name == 'province') {
            $param['province_id'] = $this->member_res_id;
            //市
        } else if ($this->member_res_name == 'city') {
            $city_info = _uri('city', $this->member_res_id);
            if (!$city_info) {
                return false;
            }

            $param['city_id']       = $this->member_res_id;
            $param['province_id']   = $city_info['province_id'];
            //区
        } else if ($this->member_res_name == 'area') {
            $area_info = _uri('area', $this->member_res_id);
            if (!$area_info) {
                return false;
            }
            $param['area_id']       = $this->member_res_id;
            $param['city_id']       = $area_info['city_id'];
            $param['province_id']   = $area_info['province_id'];
        } else if ($this->member_res_name == 'business_hall') {
            $business_hall_info = _model('business_hall')->read($this->member_res_id);
            if (!$business_hall_info) {
                return false;
            }
            $param['business_hall_ids']   = array($this->member_res_id);
            $param['area_id']       = $business_hall_info['area_id'];
            $param['city_id']       = $business_hall_info['city_id'];
            $param['province_id']   = $business_hall_info['province_id'];
        }

        //卖点套餐图, 查出机型 wangjf add 2018-06-07 套餐图可能是多条
        if ($type == 5) {
            //查询套餐详情
            $set_meal_list = _model('screen_content_set_meal')->getList(array('content_id' => $content_id));
            foreach ( $set_meal_list as $k => $set_meal_info ) {
                if (empty($set_meal_info['phone_name']) || empty($set_meal_info['phone_version'])) {
                    continue;
                }

                //查询品牌型号
                $nickname_info = screen_device_helper::get_device_nickname_info($set_meal_info['device_nickname_id']);

                if (!$nickname_info) {
                    continue;
                }


                $param['phone_name']    = $nickname_info['phone_name'];
                $param['phone_version'] = $nickname_info['phone_version'];

                _widget('screen_content.put')->put_content($param);
            }

            return true;

        } else {
            return _widget('screen_content.put')->put_content($param);
        }



    }

    /**
     * 排序
     */
    public  function view_order(){
        $data = Request::getParam('data', array());

        if (empty($data)) {
            return false;
        }

        foreach ($data as $k=>$v) {
            _model('screen_content')->update(array('id' => $v),array('view_order' => $k+1));
        }

        return;
    }

    /**
     * 保存套餐信息
     * 表单上传的方式
     * @param unknown $content_id 内容id
     */
    private function save_set_meal($content_id)
    {
        $set_meal_info = tools_helper::Post('set_meal_info', array());

        $tmp_data = array();

        //查询内容
        $content_info = _model('screen_content')->read($content_id);

        if (!$content_info) {
            return false;
        }

        //$set_meal_info['phone_version'] 是个数组，格式为 [144,14,24]
        if (empty($set_meal_info['phone_version']) || count($set_meal_info['phone_version']) < 1) {
            return '请选择机型';
        }

        $nickname_ids = array_unique($set_meal_info['phone_version']);

        //查询机型
        $nickname_list = _model('screen_device_nickname')->getList($nickname_ids);

        if (empty($set_meal_info['retail_price']) || (int)($set_meal_info['retail_price']) == 0) {
            return '零售价不能为0';
        }

        //零售价
        $tmp_data['retail_price'] = $set_meal_info['retail_price'];

        //推荐档位
        if (!empty($set_meal_info['recommended_position'])) {
            $tmp_data['recommended_position'] = $set_meal_info['recommended_position'];
        }

        //卖点1
        if (empty($set_meal_info['selling_point_1'])) {
            return '卖点1不能为空';
        }

        $tmp_data['selling_point_1'] = $set_meal_info['selling_point_1']; //默认为一三五，二四六为另一行

        //卖点2
        if (empty($set_meal_info['selling_point_2'])) {
            return '卖点2不能为空';
        }

        $tmp_data['selling_point_3'] = $set_meal_info['selling_point_2']; //默认为一三五，二四六为另一行

        //卖点3
        if (empty($set_meal_info['selling_point_3'])) {
            return '卖点3不能为空';
        }

        $tmp_data['selling_point_5'] = $set_meal_info['selling_point_3']; //默认为一三五，二四六为另一行

        //设备参数1
        if (empty($set_meal_info['param_1'])) {
            return '参数1不能为空';
        }

        $tmp_data['param_1'] = $set_meal_info['param_1'];

        //设备参数2
        if (empty($set_meal_info['param_2'])) {
            return '参数1不能为空';
        }

        $tmp_data['param_2'] = $set_meal_info['param_2'];

        //设备参数3
        if (empty($set_meal_info['param_3'])) {
            return '参数3不能为空';
        }

        $tmp_data['param_3'] = $set_meal_info['param_3'];

        //设备参数4
        if (empty($set_meal_info['param_4'])) {
            return '参数4不能为空';
        }

        $tmp_data['param_4'] = $set_meal_info['param_4'];

        //设备参数5
        if (empty($set_meal_info['param_5'])) {
            return '参数5不能为空';
        }

        $tmp_data['param_5'] = $set_meal_info['param_5'];

        //设备参数6
        if (empty($set_meal_info['param_6'])) {
            return '参数6不能为空';
        }

        $tmp_data['param_6'] = $set_meal_info['param_6'];
        $tmp_data['res_link']   = $content_info['link'];
        $tmp_data['content_id'] = $content_info['id'];
        $tmp_data['link']      = '';
        $tmp_data['issuer_res_name'] = $this->member_res_name;
        $tmp_data['issuer_res_id']   = $this->member_res_id;
        $tmp_data['issuer_res_id']   = $this->member_res_id;

        foreach ($nickname_list as $k => $v) {
            if ($v['status'] == 0) {
                return  $v['name_nickname'].' '.$v['version_nickname'].' 审核未通过';
            }

            $new_data = $tmp_data;
            $new_data['phone_name']     = $v['name_nickname'];
            $new_data['phone_version']  = $v['version_nickname'];
            $new_data['device_nickname_id']  = $v['id'];

            //创建
            _model('screen_content_set_meal')->create($new_data);

        }
        return true;
    }

    /**
     * 保存套餐信息
     * Excel文件上传的方式
     */
//     public function save_set_meal($data, $content_id)
//     {
//         //查询内容
//         $content_info = _model('screen_content')->read($content_id);

//         if (!$content_info) {
//             return false;
//         }

//         foreach ($data as $v) {

//             $v['res_link']  = $content_info['link'];
//             $v['content_id'] = $content_info['id'];
//             $v['link']      = '';
//             $v['issuer_res_name'] = $this->member_res_name;
//             $v['issuer_res_id']   = $this->member_res_id;

//             _model('screen_content_set_meal')->create($v);
//         }

//         return true;
//     }

    public function put() {
        $content_id = Request::Get('id' , 0);

        if (!$content_id) {
            return '参数不合法！';
        }

        $content_info = screen_content_helper::get_content_info($content_id);

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
        $content_list = _model('screen_content_res')->getList(array('content_id' => $content_id));
        foreach ($content_list as $k => $v) {
            if ($v['phone_name'] && $v['phone_version']) {

                if ($v['phone_name'] == 'all' && $v['phone_version'] == 'all') {
                    $v['phone_name'] = '全部品牌';
                    $v['phone_version'] = '全部型号';
                } else if ($v['phone_name'] == 'all') {
                    $name_nickname = screen_device_helper::get_device_nickname_info(array('phone_name' => $v['phone_name']), 'name_nickname');
                    $v['phone_name'] = $name_nickname ? $name_nickname : $v['phone_name'];
                    $v['phone_version'] = '全部型号';
                } else {
                    $nickname = screen_device_helper::get_device_nickname($v['phone_name'], $v['phone_version']);
                    if ($nickname) {}
                }
                if ($v['phone_version'] == 'all') {
                    $v['phone_version'] = '全部型号';
                } else {
                    $name_nickname = screen_device_helper::get_device_nickname_info(array('phone_name' => $v['phone_name'], 'phone_version' => $v['phone_version']), 'name_nickname');
                    $v['phone_name'] = $name_nickname ? $name_nickname : $v['phone_name'];
                }
            }
        }

        Response::assign('phone_names', $phone_names);
        Response::assign('content_list', $content_list);
        Response::assign('content_id', $content_id);
        Response::assign('content_info', $content_info);
        Response::display('admin/put.html');
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
     * 上传套餐信息
     */
    public function upload_set_meal()
    {
        if (!isset($_FILES['set_meal_data']['name']) || !$_FILES['set_meal_data']['name']) {
            return '请选择上传的Excel文件';
        }

        $file = $_FILES['set_meal_data'];

        if (!$file['name']) {
            return '请选择上传的Excel文件';
        }

        $allow_type = Config::get('allow_type');

        $upload_path = UPLOAD_PATH;

        $fail_msg = check_upload($file, 0, 1);

        if ($fail_msg) {
            return $fail_msg;
        }

        $ext = substr($file['name'], strrpos($file['name'], '.')+1);

        if (!in_array(strtolower($ext), $allow_type)) {
            return '文件格式错误';
        }

        if (empty($fail_msg)) {
            $file_path = an_upload($file['tmp_name'], $ext);
        }

        $file_path = ROOT_PATH.'/upload'.$file_path;

        require_once MODULE_CORE.'/helper/reader.php';

        if (!file_exists($file_path)) {
            return '文件格式不正确';
        }

        $phpexcel = new Spreadsheet_Excel_Reader();
        $phpexcel->setOutputEncoding('CP936');
        $phpexcel->read($file_path);//正式机
        $results = $phpexcel->sheets[0]['cells'];
        $cols = $phpexcel->sheets[0]['numCols'];
        $rows = $phpexcel->sheets[0]['numRows'];

        //Excel第行 需要去掉
        array_shift($results);

        $data = array();

        foreach ($results as $k => $v) {
            //状态， 默认0
            $status = 0;
            //转码
            for($i = 1; $i <= $cols; $i ++) {
                //卖点不判断为空
                if (!isset($v[$i]) ||  !$v[$i]) {
                    $rows = $k + 1;
                    //return "第{$rows}行{$i}列存在空项或参数不全";

                }
                if (!isset($v[$i]) || !$v[$i]) {
                    $v[$i] = '';
                }

                $v[$i] = iconv('GB2312', 'UTF-8//TRANSLIT//IGNORE', $v[$i]);
                $v[$i] = trim($v[$i]);

                //卖点 和 推荐档位 非必填
                if ($i < 9 && $i != 4 && !$v[$i] ) {
                    $status = 3;
                }
            }

            if (count($v) != 13) {
                //参数不正确
                $status = 5;
                //return '参数条数不正确';
            }

            //查询机型信息
            $device_nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v[1], 'version_nickname' => $v[2]));

            if ( !$device_nickname ) {
                //return "暂未查到 ”{$v['3']} {$v['4']}“ 设备信息";
                $status = 4;
            }

            $new_data = array(
                    'phone_name'    => $v['1'],
                    'phone_version' => $v['2'],
                    'retail_price'  => $v['3'],  //零售价
                    'recommended_position' => $v['4'], //推荐档位
                    'selling_point_1' => '', //买点1
                    'selling_point_2' => '', //买点1
                    'selling_point_3' => '', //买点1
                    'selling_point_4' => '', //买点1
                    'selling_point_5' => '', //买点1
                    'selling_point_6' => '', //买点1
                    'param_1'       => $v['8'], //设备参数1
                    'param_2'       => $v['9'], //设备参数2
                    'param_3'       => $v['10'], //设备参数3
                    'param_4'       => $v['11'], //设备参数4
                    'param_5'       => $v['12'], //设备参数5
                    'param_6'       => $v['13'], //设备参数6
                    'status'        => $status

            );


            //卖点
            $selling_point = explode("\n", $v['5']);
            if (isset($selling_point[1])) {
                $new_data['selling_point_1'] = $selling_point[0];
                $new_data['selling_point_2'] = $selling_point[1];
                $len = strlen($new_data['selling_point_1']);
                $len2 = strlen($new_data['selling_point_2']);
                if($len>15 || $len2>15){
                    $status = 6;
                }
            } else {
                $new_data['selling_point_1'] = $selling_point[0];
                $len = strlen($new_data['selling_point_1']);
                //长度检测
                if($len>15){
                    $status = 6;
                }
            }

            //卖点
            $selling_point = explode("\n", $v['6']);
            if (isset($selling_point[1])) {
                $new_data['selling_point_3'] = $selling_point[0];
                $new_data['selling_point_4'] = $selling_point[1];
                $len = strlen($new_data['selling_point_3']);
                $len2 = strlen($new_data['selling_point_4']);
                if($len>15 || $len2>15){
                    $status = 6;
                }
            } else {
                $new_data['selling_point_3'] = $selling_point[0];
                $len = strlen($new_data['selling_point_3']);
                //长度检测
                if($len>15){
                    $status = 6;
                }
            }

            //卖点
            $selling_point = explode("\n", $v['7']);
            if (isset($selling_point[1])) {
                $new_data['selling_point_5'] = $selling_point[0];
                $new_data['selling_point_6'] = $selling_point[1];
                $len = strlen($new_data['selling_point_5']);
                $len2 = strlen($new_data['selling_point_6']);
                if($len>15 || $len2>15){
                    $status = 6;
                }
            } else {
                $new_data['selling_point_5'] = $selling_point[0];
                $len = strlen($new_data['selling_point_5']);
                if($len>15){
                    $status = 6;
                }
            }

            //查询营业厅
            $data[] = $new_data;
        }
        return $data;
    }


    /**
     * 下载套餐模板说明 .excel文件
     */
    public function load_set_meal_template()
    {

        //查询所有机型信息
        $device_nickname = _model('screen_device_nickname')->getList(array('status' => 1));

//         //查询本归属地下的所有机型信息
//         $device_filter = _widget('screen')->default_search_filter($this->member_info);

//         if (!$device_filter) {
//             $device_filter = array(1=>1);
//         }

//         //查询所有品牌
//         $phone_names    = _model('screen_device')->getFields('phone_name', $device_filter, ' GROUP BY `phone_name` ');

//         //查询所有型号
//         $phone_versions = _model('screen_device')->getFields('phone_version', $device_filter, ' GROUP BY `phone_version` ');

//         //查询所有机型昵称
//         $nickname_filter        = array('phone_name' => $phone_names, 'status' => 1);
//         $device_nickname_list   = _model('screen_device_nickname')->getList($nickname_filter);

//         $device_nickname = array();

//         foreach ($device_nickname_list as $k => $v) {
//             if (in_array($v['phone_name'], $phone_names) && in_array($v['phone_version'], $phone_versions)) {
//                 $device_nickname[] = $v;
//             }
//         }

        if (!$device_nickname) {
            return false;
        }
        $i = 2;

        $objPHPExcel = new PHPExcel();
        $objWriter = new PHPExcel_Writer_Excel5($objPHPExcel);
        $objPHPExcel->setActiveSheetIndex(0);
        $objActSheet = $objPHPExcel->getActiveSheet();
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(30);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(30);

        $excelobj = $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '（必填）品牌')
        ->setCellValue('B1', '（必填）型号')
        ->setCellValue('C1', '（必填）零售价')
        ->setCellValue('D1', '（必填）推荐档位')
        ->setCellValue('E1', "（必填）卖点1（在五个汉字的宽度）")
        ->setCellValue('F1', "（必填）卖点2（在五个汉字的宽度）")
        ->setCellValue('G1', "（必填）卖点3（在五个汉字的宽度）")
        ->setCellValue('H1', "（必填）设备参数1（在五个汉字的宽度）")
        ->setCellValue('I1', "（必填）设备参数2（在五个汉字的宽度）")
        ->setCellValue('J1', "（必填）设备参数3（在五个汉字的宽度）")
        ->setCellValue('K1', "（必填）设备参数4（在五个汉字的宽度）")
        ->setCellValue('L1', "（必填）设备参数5（在五个汉字的宽度）")
        ->setCellValue('M1', "（必填）设备参数6（在五个汉字的宽度）");

        $phone_tmp_list = array();
        $version_tmp_list = array();
        foreach ($device_nickname as $k => $v){
            if (!$v['name_nickname'] || !$v['version_nickname']) {
                continue;
            }
            $phone_tmp_list[] = $v['name_nickname'];
            $version_tmp_list[] = $v['version_nickname'];
        }
        $phone_list = array_unique($phone_tmp_list);
        $version_list = array_unique($version_tmp_list);
        $phone = implode(',',$phone_list);
        $version = implode(',',$version_list);

        //$str_len = strlen($version);
        $phone =  substr("$phone",0,255);
        $version =  substr("$version",0,255);

        //计算字符长度
        //$str_len = strlen($version);
        //p($phone, $version);exit;

        foreach ( $version_list as $k => $v ) {
            $objValidation = $objActSheet->getCell("A".$i)->getDataValidation();
            $objValidation2 = $objActSheet->getCell("B".$i)->getDataValidation();

            $objValidation -> setType(PHPExcel_Cell_DataValidation::TYPE_LIST)
            -> setErrorStyle(PHPExcel_Cell_DataValidation::STYLE_INFORMATION)
            -> setAllowBlank(false)
            -> setShowInputMessage(true)
            //-> setShowErrorMessage()
            -> setShowDropDown(true)
            //-> setErrorTitle('输入的值有误')
            //-> setError('您输入的值不在下拉框列表内.')
            -> setPromptTitle('品牌')
            ->setPrompt('请从下拉框中选择您需要的值！')
            ->setFormula1('"' . $phone . '"');

            //型号 超过范围直接插入

//             if($str_len>=255){
//                 if($version_list)
//                     foreach($version_list as $i =>$d){
//                         $c = "B".$i;
//                         $excelobj->setCellValue($c,$d);
//                 }
//                 $endcell = $c;
// //                 $excelobj->getColumnDimension('B'.$i)->setVisible(false);
//             }
            $objValidation2->setType( PHPExcel_Cell_DataValidation::TYPE_LIST )
            ->setErrorStyle( PHPExcel_Cell_DataValidation::STYLE_INFORMATION )
            ->setAllowBlank(true)
            ->setShowInputMessage(true)
            //->setShowErrorMessage(true)
            ->setShowDropDown(true)
            //->setErrorTitle('输入的值有误')
            //->setError('您输入的值不在下拉框列表内.')
            ->setPromptTitle('型号')
            ->setPrompt('请从下拉框中选择您需要的值！')
            ->setFormula1('"' . $version . '"');
//             if($str_len<255){
//                 //$objValidation2->setFormula1('"' . $version . '"');
//             }else{
//                 $objValidation2->setFormula1("sheet1!B.$i:{$endcell}");
//             }

            $i++;
        }


        //合并单元格
//         $objPHPExcel->getActiveSheet()->mergeCells( 'A28:H28');
//         //设置提示值
//         $excelobj = $objPHPExcel->setActiveSheetIndex(0)
//         ->setCellValueExplicit('A28', '请尽量保持在五个中文汉字的宽度之内');

        //设置sheet标题
        $objPHPExcel->getActiveSheet()->setTitle('导入套餐信息');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="套餐信息模板.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
    /**
     * 下载套餐模板说明 .excel文件
     */
    public function load_device_model_template()
    {

        $objPHPExcel = new PHPExcel();
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(20);

        $excelobj = $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '品牌')
        ->setCellValue('B1', '型号');

        //查询所有机型信息
        $device_nickname = _model('screen_device_nickname')->getList(array('status' => 1));

        if (!$device_nickname) {
            return false;
        }
        $i = 2;
        foreach ( $device_nickname as $k => $v ) {
            if (!$v['name_nickname'] || !$v['version_nickname']) {
                continue;
            }

            $excelobj = $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValueExplicit('A'.$i, $v['name_nickname'], PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('B'.$i, $v['version_nickname'], PHPExcel_Cell_DataType::TYPE_STRING);
            ++$i;
        }

        //设置sheet标题
        $objPHPExcel->getActiveSheet()->setTitle('可支持机型列表');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="可支持机型列表.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }


    /**
     * 信息填写错误列表下载
     */
    public function down_error_list()
    {
        $error_list = htmlspecialchars_decode(Request::post('error_list',''));
        $error_list = json_decode($error_list,true);
        $new_list = array();
        $i = 2;
        $objPHPExcel = new PHPExcel();
        //设置宽度
        $objPHPExcel->getActiveSheet()->getColumnDimension('A')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('B')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('C')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('D')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('E')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('F')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('G')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('H')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('I')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('J')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('K')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('L')->setWidth(40);
        $objPHPExcel->getActiveSheet()->getColumnDimension('M')->setWidth(40);

        $excelobj = $objPHPExcel->setActiveSheetIndex(0)
        ->setCellValue('A1', '（必填）品牌')
        ->setCellValue('B1', '（必填）型号')
        ->setCellValue('C1', '（必填）零售价')
        ->setCellValue('D1', '（必填）推荐档位')
        ->setCellValue('E1', "（必填）卖点1（五个汉字的宽度）")
        ->setCellValue('F1', "（必填）卖点2（五个汉字的宽度）")
        ->setCellValue('G1', "（必填）卖点3（五个汉字的宽度）")
        ->setCellValue('H1', "（必填）设备参数1（五个汉字的宽度）")
        ->setCellValue('I1', "（必填）设备参数2（五个汉字的宽度）")
        ->setCellValue('J1', "（必填）设备参数3（五个汉字的宽度）")
        ->setCellValue('K1', "（必填）设备参数4（五个汉字的宽度）")
        ->setCellValue('L1', "（必填）设备参数5（五个汉字的宽度）")
        ->setCellValue('M1', "（必填）设备参数6（五个汉字的宽度）");

        foreach ($error_list as $k => $v) {

            $device_nickname = _model('screen_device_nickname')->read(array('name_nickname' => $v['phone_name'], 'version_nickname' => $v['phone_version']));

            $excelobj = $objPHPExcel->setActiveSheetIndex(0)
            ->setCellValueExplicit('A'.$i, $v['phone_name'], PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('B'.$i, $v['phone_version'], PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('C'.$i, $v['retail_price'], PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('D'.$i, $v['recommended_position'], PHPExcel_Cell_DataType::TYPE_STRING)
            ->setCellValueExplicit('E'.$i, $v['selling_point_1'])
            ->setCellValueExplicit('F'.$i, $v['selling_point_3'])
            ->setCellValueExplicit('G'.$i, $v['selling_point_5'])
            ->setCellValueExplicit('H'.$i, $v['param_1'])
            ->setCellValueExplicit('I'.$i, $v['param_2'])
            ->setCellValueExplicit('J'.$i, $v['param_3'])
            ->setCellValueExplicit('K'.$i, $v['param_4'])
            ->setCellValueExplicit('L'.$i, $v['param_5'])
            ->setCellValueExplicit('M'.$i, $v['param_6']);

            if ( !$device_nickname ) {
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun($v['phone_name']);
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'A'.$i)->setValue($objRichText);

                $objPayable = $objRichText->createTextRun($v['phone_version']);
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'B'.$i)->setValue($objRichText);
            }

            if(!$v['retail_price']){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun('必填');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'C'.$i)->setValue($objRichText);
            }

            if(!$v['recommended_position']){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun('必填');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'D'.$i)->setValue($objRichText);
            }


            if(!$v['selling_point_1']){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun('必填');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'E'.$i)->setValue($objRichText);
            }

            if(strlen($v['selling_point_1'])>15){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun($v['selling_point_1'].' (超过限宽)');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'E'.$i)->setValue($objRichText);
            }

            if(!$v['selling_point_3']){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun('必填');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'F'.$i)->setValue($objRichText);
            }

            if(strlen($v['selling_point_3'])>15){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun($v['selling_point_3'].' (超过限宽)');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'F'.$i)->setValue($objRichText);
            }

            if(!$v['selling_point_5']){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun('必填');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'G'.$i)->setValue($objRichText);
            }

            if(strlen($v['selling_point_5'])>15){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun($v['selling_point_5'].' (超过限宽)');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'G'.$i)->setValue($objRichText);
            }


            if(!$v['param_1']){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun('必填');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'H'.$i)->setValue($objRichText);
            }

            if(strlen($v['param_1'])>15){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun($v['param_1'].' (超过限宽)');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'H'.$i)->setValue($objRichText);
            }

            if(!$v['param_2']){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun('必填');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'I'.$i)->setValue($objRichText);
            }

            if(strlen($v['param_2'])>15){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun($v['param_2'].' (超过限宽)');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'I'.$i)->setValue($objRichText);
            }


            if(!$v['param_3']){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun('必填');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'J'.$i)->setValue($objRichText);
            }

            if(strlen($v['param_3'])>15){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun($v['param_3'].' (超过限宽)');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'J'.$i)->setValue($objRichText);
            }


            if(!$v['param_4']){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun('必填');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'K'.$i)->setValue($objRichText);
            }

            if(strlen($v['param_4'])>15){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun($v['param_4'].' (超过限宽)');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'K'.$i)->setValue($objRichText);
            }

            if(!$v['param_5']){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun('必填');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'L'.$i)->setValue($objRichText);
            }

            if(strlen($v['param_5'])>15){
                $objRichText = new PHPExcel_RichText();
                $objPayable13 = $objRichText->createTextRun($v['param_5'].' (超过限宽)');
                $objPayable13->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'L'.$i)->setValue($objRichText);
            }


            if(!$v['param_6']){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun('必填');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'M'.$i)->setValue($objRichText);
            }

            if(strlen($v['param_6'])>15){
                $objRichText = new PHPExcel_RichText();
                $objPayable = $objRichText->createTextRun($v['param_6'].' (超过限宽)');
                $objPayable->getFont()->setColor( new PHPExcel_Style_Color( PHPExcel_Style_Color::COLOR_RED ) );
                $objPHPExcel->getActiveSheet()->getCell( 'M'.$i)->setValue($objRichText);
            }


            ++$i;

        }


        //设置sheet标题
        $objPHPExcel->getActiveSheet()->setTitle('套餐信息');
        $objPHPExcel->setActiveSheetIndex(0);
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="套餐信息修改.xls"');
        header('Cache-Control: max-age=0');
        header('Cache-Control: max-age=1');
        header ('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header ('Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT'); // always modified
        header ('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header ('Pragma: public'); // HTTP/1.0

        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
        $objWriter->save('php://output');
        exit;
    }
}
?>