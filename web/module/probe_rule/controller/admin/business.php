<?php 
/**
 * alltosun.com 营业厅规则 business.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2017-4-6 下午3:07:15 $
*/
class Action
{
    private $per_page  = 20;
    private $member_info = array();

    public function __construct()
    {
        $this->member_info = member_helper::get_member_info();

        Response::assign('member_info', $this->member_info);
    }

    public function index()
    {
        if ( !$this->member_info ) {
            return '请先登录';
        }

        $page                = Request::get('page_no' , 1);
        $business_hall_title = Request::get('business_hall_title' , '');

        if ($this->member_info['res_name']  == 'group' ) {
            if ($business_hall_title) {
                $business_hall_id = _model('business_hall')->getFields('id', array('title' => $business_hall_title));
                if ($business_hall_id) {
                    $filter = array('business_id'=>$business_hall_id, 'status'=>1);
                }
            } else {
                $filter = array('status'=>1);
            }

        } else if ( $this->member_info['res_name'] == 'province' ) {
            $filter = array('province_id'=>$this->member_info['res_id'], 'status'=>1);
        } else if ( $this->member_info['res_name'] == 'city' ) {
            $filter = array('city_id'=>$this->member_info['res_id'], 'status'=>1);
        } else if ($this->member_info['res_name'] == 'area' ) {
            $filter = array('area_id'=>$this->member_info['res_id'], 'status'=>1);
        } else if ( $this->member_info['res_name'] == 'business_hall' ) {
            $filter = array('business_id'=>$this->member_info['res_id'], 'status'=>1);
        }

        $count = _model('probe_device')->getTotal($filter);

        if ($count) {
            $pager  = new Pager($this->per_page);
            $list  = _model('probe_device')->getFields('business_id', $filter, $pager->getLimit($page), ' GROUP BY `business_id` ');

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('business_hall_title', $business_hall_title);
        Response::assign('page', $page);
        Response::assign('b_ids', $list);
        Response::display('admin/business_list.html');
    }

    public function add()
    {
        Response::display('admin/business_add.html');
    }

    public function edit()
    {
        $business_id = Request::Get('business_id', 0);

        if ( !$business_id ) {
            return '请选择您要编辑的营业厅';
        }

        $b_info = _model('business_hall')->read(array('id'=>$business_id));

        if ( !$b_info ) {
            return '营业厅不存在';
        }

        Response::assign('info', $b_info);
        Response::display('admin/business_add.html');
    }

    public function save()
    {
        $id       = Request::Post('id', 0);
        $business = Request::Post('business', 0);
        $info     = Request::Post('info', array());

        if ( !$this->member_info ) {
            return '请先登录';
        }

        // 验证营业厅
        if ( !$business ) {
            return '请选择营业厅';
        }
        $b_info = _model('business_hall')->read(array('id'=>$business));
        if ( !$b_info ) {
            return '营业厅不存在';
        }

        // 权限
        if ( $this->member_info['res_name'] == 'group' ) {
            
        } else if ( $this->member_info['res_name'] == 'province' ) {
            if ( $b_info['province_id'] != $this->member_info['res_id'] ) {
                return '对不起，您没权限操作';
            }
        } else if ( $this->member_info['res_name'] == 'city' ) {
            if ( $b_info['city_id'] != $this->member_info['res_id'] ) {
                return '对不起，您没权限操作';
            }
        } else if ( $this->member_info['res_name'] == 'area' ) {
            if ( $b_info['area_id'] != $this->member_info['res_id'] ) {
                return '对不起，您没权限操作';
            }
        } else if ( $this->member_info['res_name'] == 'business_hall' ) {
            if ( $b_info['id'] != $this->member_info['res_id'] ) {
                return '对不起，您没权限操作';
            }
        } else {
            return '无法识别您的身份';
        }

        // 规则的验证和重新组装格式
        $i    = 0;
        $rule = array();
        foreach ($info as $k => $v) {
            $rule[$i] = array(
                'id'    =>  $k,
                'value' =>  '',
                'alias' =>  '',
            );
            // 验证规则是否存在
            $info = _model('probe_rule')->read(array('id'=>$k));
            if ( !$info ) {
                return '规则'.($i + 1).'不存在';
            }
            $rule[$i]['alias'] = $info['alias'];

            if ( isset($v['value']) ) {
                foreach ($v['value'] as $value) {
                    if ( !$value ) {
                        return '请输入规则'.($i + 1).'的选项值';
                    }
                    if ( !is_numeric($value) || (int)$value != $value ) {
                        return '规则'.($i + 1).'的选项值只能为整数';
                    }
                }
                $rule[$i]['value'] = implode('-', $v['value']);
            }
            $i ++;
        }

        // 删除原来规则
        _model('probe_business_rule')->delete(array('business_id'=>$business));

        // 添加新规则
        foreach ($rule as $k => $v) {
            $create = array(
                'province_id'   =>  $b_info['province_id'],
                'city_id'       =>  $b_info['city_id'],
                'area_id'       =>  $b_info['area_id'],
                'business_id'   =>  $business,
                'rule_id'       =>  $v['id'],
                'value'         =>  $v['value'],
                'alias'         =>  $v['alias']
            );
            _model('probe_business_rule')->create($create);
        }

        Response::redirect(AnUrl('probe_rule/admin/business'));
        Response::flush();
    }
}
?>