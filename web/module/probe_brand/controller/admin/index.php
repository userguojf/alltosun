<?php

/**
 * 设备品牌分布
 *
 * @author  wangl
 */

// load trait stat
probe_helper::load('stat', 'trait');

class Action
{
    use stat;

    private $member_info = array();
    private $per_page = 20;

    /**
     * 构造函数
     *
     * @return  Obj
     */
    public function __construct()
    {
        $this->member_info = member_helper::get_member_info();

        Response::assign('member_info', $this->member_info);
    }

    /**
     * 品牌分布页
     *
     * @author  wangl
     */
    public function index()
    {
        $type  = Request::Get('type', 'in');
        $b_id  = Request::Get('b_id', 0);
        $date  = Request::Get('date', '');
        $page_no  = Request::Get('page_no', 1);

        // 身份验证
        if ( !$this->member_info ) {
            return '请先登录';
        }

        // 拿我可以查看的营业厅ID
        $b_ids = probe_dev_helper::get_business_ids($this->member_info['res_name'], $this->member_info['res_id']);

        if ( !$b_ids ) {
            return '暂无数据';
        }

        // 验证当查看的营业厅
        if ( $b_id ) {
            if ( !in_array($b_id, $b_ids) ) {
                return '您无权查看';
            }
        } else {
            $b_id = $b_ids[0];
        }

        // 查看时间
        if ( $date ) {
            $date = str_replace('-', '', $date);
        } else {
            $date = (int)date('Ymd');
        }

        // 营业厅信息
        $b_info = _model('business_hall')->read($b_id);

        if ( !$b_info ) {
            return '营业厅不存在';
        }

        $filter['date']      = $date;
        $filter['b_id']      = $b_id;
        $filter['is_indoor'] = $type;

        $db   = get_db($b_id);

        if (!$db){
            return '暂无品牌分布';
        }

        $count = $db -> getTotal($filter);

        if ($count) {
            $pager = new Pager($this->per_page);
            $list  = $db -> getList($filter , $pager->getLimit($page_no));

            if ($pager->generate($count,$page_no)) {
                Response::assign('pager' , $pager);
            }
        }

        $data = $this -> brand_stat($b_id, $date);

        Response::assign('type', $type);
        Response::assign('b_info', $b_info);
        Response::assign('b_ids', $b_ids);
        Response::assign('date', $date);
        Response::assign('brands', $data['brand']);
        Response::assign('list', $list);
        Response::display('admin/index.html');
    }

    /**
     * 进店记录
     *
     * @author  wangl
     */
    public function record()
    {
        $mac   = Request::Get('mac', '');
        $b_id  = Request::Get('b_id', 0);

        if ( !$mac ) {
            return '请选择mac地址';
        }

        if ( !$b_id ) {
            return '请选择营业厅';
        }

        // 查询营业厅信息
        $b_info = business_hall_helper::get_business_hall_info($b_id);

        if ( !$b_info ) {
            return '营业厅不存在';
        }

        $list = $this -> get_mac_list($b_id, $mac);

        Response::assign('mac', $mac);
        Response::assign('b_info', $b_info);
        Response::assign('list', $list);
        Response::display('admin/record.html');
    }
}
