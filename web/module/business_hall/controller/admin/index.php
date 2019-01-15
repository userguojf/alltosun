<?php
/**
 * alltosun.com  business_hall.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2016-7-26 下午3:05:10 $
 * $Id$
 */
class Action
{
    private $per_page =30 ;
    private $member_id  = 0;
    private $member_res_name = '';
    private $member_res_id   = 0;
    private $member_info     = array();
    private $ranks           = 0;

    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $member_info = member_helper::get_member_info($this->member_id);

        if ($member_info) {
            $this->member_res_name = $member_info['res_name'];
            $this->member_res_id   = $member_info['res_id'];
            $this->ranks           = $member_info['ranks'];
            $this->member_info = $member_info;
            Response::assign('member_res_name', $this->member_res_name);
            Response::assign('member_res_id', $this->member_res_id);
        } else {
            return '您无权访问此页面';
        }

        Response::assign('curr_member_ranks', $this->ranks);
        Response::assign('member_info', $member_info);
    }

    public function __call($action = '' , $param = array())
    {
        $search_filter = Request::get('search_filter' ,array());
        $page          = Request::get('page_no' , 1);
        $if_export     = Request::get('if_export', 0);

        $list = $filter =  array();

        //分权限获取城市初始条件
        $filter = _widget('business_hall')->init_filter($this->member_info, $search_filter);

        //搜索类型 -wangjf
        if (isset($search_filter['search_type'])  && $search_filter['search_type']!= 'undefined') {

            //厅渠道编码搜索
            if (isset($search_filter['search_type_value']['user_number']) && !empty($search_filter['search_type_value']['user_number'])) {
                $filter['user_number'] = $search_filter['search_type_value']['user_number'];
            }
            //厅名称搜索
            if (isset($search_filter['search_type_value']['title']) && !empty($search_filter['search_type_value']['title'])) {
                $filter['title LIKE '] = "%".$search_filter['search_type_value']['title']."%";
            }
            //联系人搜索
            if (isset($search_filter['search_type_value']['contact']) && !empty($search_filter['search_type_value']['contact'])) {
                $filter['contact'] = trim($search_filter['search_type_value']['contact']);
            }
            //活跃度搜索
            if (isset($search_filter['search_type_value']['activity']) && $search_filter['search_type_value']['activity']!=100) {
                $filter['activity']        = $search_filter['search_type_value']['activity'];
            }else {
                $search_filter['search_type_value']['activity'] = 100;
            }
            //绑定状态搜索
            if (isset($search_filter['search_type_value']['is_bounding']) && $search_filter['search_type_value']['is_bounding']!=100) {
                //全部绑定
                if ($search_filter['search_type_value']['is_bounding'] == 99) {
                    $filter['is_bounding >'] = 0;
                } else {
                    $filter['is_bounding']        = $search_filter['search_type_value']['is_bounding'];
                }
            }else {
                $search_filter['search_type_value']['is_bounding'] = 100;
            }
            //营业厅类型搜索
            if ($search_filter['search_type_value']['store_type'] != 'undefined' && $search_filter['search_type_value']['store_type'] > 100) {
                $filter['store_scope'] = $search_filter['search_type_value']['store_type']-100;
            } else if ($search_filter['search_type_value']['store_type'] != 'undefined' && $search_filter['search_type_value']['store_type'] < 100) {
                $filter['store_level'] = $search_filter['search_type_value']['store_type'];
            }
            //营业厅设备连接类型搜索
            if (isset($search_filter['search_type_value']['connect_type']) && $search_filter['search_type_value']['connect_type']!='undefined' && $search_filter['search_type'] == 'connect_type') {
                $filter['connect_type'] = $search_filter['search_type_value']['connect_type'];
            }
        } else {
            //清除上次查询
            $search_filter['search_type_value'] = array();
            $search_filter['search_type']       = '';
        }

        if (!$filter) {
            $filter[1] = 1;
        }

        //限制厅
        //$filter['type'] = array(4, 5);

        $count = _model('business_hall')->getTotal($filter);

        if ($count && $if_export) {
            $list  = _model('business_hall')->getList($filter);
            $this->csv_export($list);
        }
        if ($count) {
            $pager = new Pager($this->per_page);
            $list  = _model('business_hall')->getList($filter , $pager->getLimit($page));

            if($pager->generate($count,$page)){
                Response::assign('pager' , $pager);
            }
        }

        //查询省厅地区信息
        $region_info = business_hall_helper::get_region_by_member($this->member_res_name, $this->member_res_id);

        Response::assign('region_info', $region_info);
        Response::assign('count', $count);
        Response::assign('business_list' , $list);
        Response::assign('search_filter' , $search_filter);
        Response::assign('page' , $page);
        Response::display('admin/business_hall_list.html');
    }

    /**
     * 营业厅数据导出
     * @param $list
     */
    public function csv_export($hall_list=array())
    {
        $list = array();

        foreach ($hall_list as $k => $v) {
            $tmp_list                = array();
            $tmp_list['user_number'] = $v['user_number'];
            $tmp_list['title']       = $v['title'];
            $tmp_list['address']     = $v['address'];

            $province_id             = focus_helper::get_field_info($v['province_id'],'province', 'name');

            if (!$province_id) {
                continue;
            }

            $tmp_list['province_id'] = $province_id;

            $city_id = focus_helper::get_field_info($v['city_id'],'city', 'name');

            if (!$city_id) {
                continue;
            }

            $tmp_list['city_id'] = $city_id;
            $list[]              = $tmp_list;
        }

        $params['data'] = $list;
        $params['head'] = array('营业厅渠道号','营业厅名称','营业厅地址','省' , '市' );
        Csv::getCvsObj($params)->export();
    }

    //添加 编辑
    public function add()
    {
        $id = Request::get('id' , 0);

        if ($id) {
            $business_hall_info = _uri('business_hall' , array('id'=>$id));
            $province = array('province_id' => $business_hall_info['province_id']);
            $city = array('city_id' => $business_hall_info['city_id']);

            Response::assign('where1' , $province);
            Response::assign('where2' , $city);
            Response::assign('id' , $id);
            Response::assign('business_hall_info' ,$business_hall_info);
        }

        Response::display('admin/business_hall_add.html');
    }

    //保存
    public function save()
    {
        $business_id        = Request::post('id' , 0);
        $business_hall_info = Request::post('business_hall_info' , array());

        //判断
        if (!isset($business_hall_info['title']) || empty($business_hall_info['title'])) {
            return '请填写 营业厅名称';
        }
        if (!isset($business_hall_info['user_number']) || empty($business_hall_info['user_number'])) {
            return '请填写 渠道码';
        }
        if (!isset($business_hall_info['province_id']) || empty($business_hall_info['province_id'])) {
            return '请填写所属省';
        }
        if (!isset($business_hall_info['city_id']) || empty($business_hall_info['city_id'])) {
            return '请填写所属市';
        }
        if (!isset($business_hall_info['area_id']) || empty($business_hall_info['area_id'])) {
            return '请填写所属地区';
        }
        if (!isset($business_hall_info['contact']) || empty($business_hall_info['contact'])) {
            return '请填写联系人';
        }
        if (!isset($business_hall_info['contact_way']) || empty($business_hall_info['contact_way'])) {
            return '请填写联系人电话';
        }

        if ($business_id) {
            //更新
            $result       = _model('business_hall')->update($business_id , $business_hall_info);
        }else {
            $res       = _model('business_hall')->read(array('user_number' => $business_hall_info['user_number']));

            if ( $res ) return '渠道码已存在，请查找搜索后编辑';

            //添加
            $result       = _model('business_hall')->create($business_hall_info);

            $member_info = array(
                'res_name'      => 'business_hall',
                'res_id'        => $result,
                'member_user'   => $business_hall_info['user_number'],
                'member_pass'   => md5('Awifi@123')
            );

            member_helper::create_member_info($member_info);
        }

        //生成账号 可登录
        return array('操作成功' , 'success' ,AnUrl('business_hall/admin/business_hall'));

    }
}