<?php
/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2016-7-26 下午5:09:02 $
 * $Id$
 */

class Action
{

    public function get_title_field()
    {
        $key_word = Request::Get('term','');

        if (!$key_word) {
            return '数据不存在';
        }

        $name = _model('business_hall')->getFields(
                    'title',
                    array(
                        'title LIKE' => "%{$key_word}%"
                        )
                );

        if ($name) {
            exit(json_encode($name));
        }
    }

    /**
     * 根据营业厅标题获取详情
     */
    public function get_info_by_title()
    {
        $key_word = Request::Get('term','');

        if (!$key_word) {
            return '数据不存在';
        }

        $business_list = _model('business_hall')->getList(
                array(
                        'title LIKE' => "{$key_word}%"
                )
                );

        $list2=array();
        foreach ($business_list as $k=> $v)
        {
            $arr=array(
                    'id'=>$v['id'],
                    'label'=>$v['title']

            );
            $list2[] =$arr;
        }
        if ($list2) {
            exit(json_encode($list2));
        }
    }

    //三级联动
    public function get_city_name()
    {
        $province_id = Request::post('province_id');

        if (!$province_id) {
            return array('msg'=>'ok', 'city_info' => array());
        }

        $city_info = _model('city')->getList(array('province_id'=>$province_id));

        if (!$city_info) {
            return false;
        }

        return array('msg'=>'ok' , 'city_info'=> $city_info);
    }

    public function get_area_name()
    {
        $city_id = Request::post('city_id');

        if (!$city_id) {
            return array('msg'=>'no');
        }

        $area_info = _model('area')->getList(array('city_id'=>$city_id));

        if (!$area_info) {
            return array('msg'=>'no');
        }

        return array('msg'=>'ok' , 'area_info'=> $area_info);
    }

    // 获取营业厅
    public function get_business_title()
    {
        $area_id = Request::post('area_id',0);

        if (!$area_id) {
            return array('msg'=>'no');
        }

        $data = _model('business_hall')->getList(array('area_id' => $area_id));

        if (!$data) {
            return array('msg'=>'no');
        }

        return array('msg' => 'ok','business_info' => $data);
    }



    /**
     * 导出登录信息数据
     * @param unknown $list
     * @param unknown $page
     * @param unknown $search_filter
     */
    public function business_login_export() {
        //缓存
        global $mc_wr;

        $search_filter['province_id'] = tools_helper::post('province_id', 0);
        $search_filter['city_id']     = tools_helper::post('city_id', 0);
        $search_filter['area_id']     = tools_helper::post('area_id', 0);
        $search_filter['user_number'] = tools_helper::post('user_number', '');
        $search_filter['business_id'] = tools_helper::post('business_id', 0);

        $is_delete_mc                = tools_helper::post('is_delete_mc', 0);
        //是否为第一次请求，如果是，则删除缓存
        if ($is_delete_mc){
            $mc_wr->delete('business_login_export_data');
        }

        $export_data = $mc_wr->get('business_login_export_data');
        if (!$export_data || $is_delete_mc){
            $page = 1;
            $export_data['page'] = $page;
            $export_data['data'] = array();
        } else {
            $page = $export_data['page'];
        }

        $list = $this->get_business_login_list($page, $search_filter);

        if (!$list){
            return 'end';
        }

        $export_list = array();
        foreach ($list as $k => $v) {

            $tmp_data['title'] = $v['title'];
            $tmp_data['user_number'] = $v['user_number'];
            $tmp_data['province']    = $v['province'];
            $tmp_data['city']        = $v['city'];
            $tmp_data['area']        = $v['area'];
            $tmp_data['duration']   = $v['duration'].'天';
            $tmp_data['last_login_time']   = $v['last_login_time'];
            $export_data['data'][] = $tmp_data;
        }

        ++$page;
        $export_data['page'] = $page;

        $mc_wr->set('business_login_export_data', $export_data, 60*10);
        return array('info' => 'ok', 'page' => $page);
    }

    /**
     * 获取营业厅登录用户数据
     * @param unknown $page
     */
    public function  get_business_login_list($page, $search_filter) {

        $three_date         = date('Y-m-d H:i:s', time() - 3600*24*3);
        $list               = array();

        //搜索判断
        if ($search_filter['province_id']) {
            $filter['province_id'] = $search_filter['province_id'];
        }
        if ($search_filter['city_id']) {
            $filter['city_id'] = $search_filter['city_id'];
        }
        if ($search_filter['area_id']) {
            $filter['area_id'] = $search_filter['area_id'];
        }

        if ($search_filter['user_number']) {
            $filter['user_number'] = $search_filter['user_number'];
        }

        if ($search_filter['business_id']) {
            $filter['id'] = $search_filter['business_id'];
        }


        $filter['activity'] = 1;
        $filter['type']     = array(4, 5);

        $ids   = 0;
        $limit = 100;
        $pager = ($page-1)*$limit;

        //分页获取
        $ids    = _model('business_hall')->getFields('id', $filter, ' LIMIT '.$pager.','.$limit);

        //取每个厅的最后用户登录时间user_business_hall_num表
        if (is_array($ids)) {
            foreach ($ids as $v) {
                $filter = array(
                        'business_hall_id' => $v,
                        'last_login_time <=' => $three_date
                );
                $info = _model('user_business_hall_num')->read($filter, 'ORDER BY `last_login_time` DESC');

                if ($info) {
                    $business_info = _uri('business_hall', $info['business_hall_id']);
                    if ($business_info) {
                        $info['title']         = $business_info['title'];
                        $info['user_number']   = $business_info['user_number'];
                    }

                    $info['province']          = business_hall_helper::get_info_name('province' , $info['province_id'] ,'name');
                    if (!$info['province']) {
                        continue;
                    }
                    $info['city']              = business_hall_helper::get_info_name('city' , $info['city_id'] ,'name');

                    if (!$info['city']) {
                        continue;
                    }
                    $info['area']              = business_hall_helper::get_info_name('area' , $info['area_id'] ,'name');
                    if (!$info['area']) {
                        continue;
                    }

                    //持续时间
                    $duration = time() - strtotime($info['last_login_time']);
                    $info['duration'] = floor($duration/(3600*24));

                    $list[] = $info;

                }


            }

        }

        return $list;
    }

    /**
     *
     */
    public function update_apply_status()
    {
        //获取参数
        $apply_id    = Request::post('id' , 0);
        $status      = Request::post('status' , '');
        $user_number = Request::post('user_number' , '');
        $user_phone  = Request::post('user_phone' , '');

        //判断
        if (!$apply_id || !$user_number || !$user_phone) {
            return array('info' => '由于网络原因，请刷新重试！');
        }

        //审核下发短信     取消审核不发短信
        if ($status) {
            //下发短信
            $show_user_phone   = substr($user_phone , 0 , 3);

            $params['tel']     = $user_phone;

            $content =  array(
                    'param1'    => "{$show_user_phone}",
                    'param2'    => "{$user_number}",
                            );

            $params['content']     = json_encode($content);

            $params['template_id'] = 91552490;

            $msg_res = _widget('message')->send_message($params);

            if ($msg_res['info'] != 'ok') {
                return array('info' => '由于短信接口问题，短信下发失败，请稍后审核');
            }
        }

            //短信下发成功 更新status字段
            _model('business_hall_binding_apply')->update(array('id' => $apply_id),array('status' => $status));

            return array('info' => 'ok');

    }
}