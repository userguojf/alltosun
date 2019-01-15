<?php
/**
 * alltosun.com  user.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-3-14 上午11:52:36 $
 * $Id$
 */
class Action
{
    private $wework   = '企业微信通讯录';
    private $per_page = 10;

    public function __construct()
    {
        Response::assign('wework' , $this->wework);
    }

    public function __call($action = '' , $param = '')
    {
        $page = Request::get('page_no' , 1) ;

        $search_filter = Request::get('search_filter' , array());

        $list = $filter = array();

        if (isset($search_filter['account']) && $search_filter['account']) {
           $filter['account'] = trim($search_filter['account']);
        }

        if (isset($search_filter['mobile']) && $search_filter['mobile']) {
            $filter['mobile'] = trim($search_filter['mobile']);
        }

        if (isset($search_filter['name']) && $search_filter['name']) {
            $filter['name'] = trim($search_filter['name']);
        }

        if (isset($search_filter['user_id']) && $search_filter['user_id']) {
            $filter['user_id'] = trim($search_filter['user_id']);
        }

        if (!$filter )  $filter = array( 1 => 1 );


        $count = _model('wework_user')->getTotal($filter);

        if ($count) {
            $pager  = new Pager($this->per_page);
            $list   = _model('wework_user')->getList($filter, $pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('list' , $list);
        Response::assign('count' , $count);
        
        Response::assign('page' , $page);
        Response::assign('search_filter' , $search_filter );

        Response::display('admin/user_list.html');
    }

    //添加数据
    public function add()
    {
        $id = Request::get('id' , 0);

        $depart_list = _model('wework_department')->getList(array(1 => 1));

        if ( $id ) {
            $user_info = _uri('wework_user' , array('id'=>$id));

            Response::assign('user_info' , $user_info);
        }
        Response::assign('depart_list' , $depart_list);
        Response::display('admin/add_user.html');

    }

    //保存
    public function save()
    {
        $user_info = Request::post('user_info' , array());

        // 创建成员json
        $json = '{';
        //判断
        if (!isset($user_info['user_id']) || !$user_info['user_id'] ) {
            return '企业账号不能为空';
        } else {
            $json .= ' "userid": "' . $user_info['user_id'] . '",';
        }

        if (!isset($user_info['name']) || !$user_info['name'] ) {
            return '联系人不能为空';
        } else {
            $json .= ' "name": "' . $user_info['name'] . '",';
        }

        if (!isset($user_info['mobile']) || !$user_info['mobile'] ) {
            return '电话号不能不为空';
        } else {
            $json .= ' "mobile": "' . $user_info['mobile'] . '",';
        }
        if ( isset($user_info['position']) && $user_info['position'] ) {
            $json .= ' "position": "' . $user_info['position'] . '",';
        }
        if ( isset($user_info['gender']) && $user_info['gender'] ) {
            $json .= ' "gender": "' . $user_info['gender'] . '",';
        }
        if ( isset($user_info['email']) && $user_info['email'] ) {
            $json .= ' "email": "' . $user_info['email'] . '",';
        }
        if ( isset($user_info['isleader']) && $user_info['isleader'] ) {
            $json .= ' "isleader": "' . $user_info['isleader'] . '",';
        }

        if (!isset($user_info['account']) || !$user_info['account'] ) {
            return '平台账号不能为空';
        } else {
            $business_info = _model('business_hall')->read(array('user_number' => $user_info['account']));

            if ( !$business_info ) return '抱歉，平台账号未获取到';

            $json .= ' "extattr": {"attrs":[{"name":"account","value":" ' . $user_info['account'] . ' "}';
        }

        if (!isset($user_info['t_name']) || !$user_info['t_name'] ) {
            return '营业厅名称不能为空';
        } else {
            $json .= ' ,{"name":"t_name","value":"'. $user_info['t_name'] .'"}]}, ';
        }

        if ( !isset($user_info['business']) || !$user_info['business'] ) {
            return '请选择所属部门';
        } else {
            $depart_id = wework_department_helper::get_department_id($user_info['business'],
                    $business_info['province_id']);

            if ( !$depart_id ) return '创建部门请求微信接口失败';

            $user_info['department'] = implode(',', $depart_id);

            $json .= ' "department": [' . $user_info['department'] . '],';
        }

        $user_info['business']   = implode(',', $user_info['business']);

        $json .= '}';

        if ( $user_info['id'] ) {
            $result = wework_user_helper::update('work', $json);

            if ( !isset($result['errcode']) || $result['errcode'] ) {
                return $result['errmsg']['errmsg'];
            }

            _model('wework_user')->update($user_info['id'] , $user_info);

        } else {
            //账号唯一的判断
            $is_have_info = _model('wework_user')->read(array('user_id' => $user_info['user_id']));

            if ($is_have_info) {
                return '账号已经存在!';
            }

            $result = wework_user_helper::create('work', $json);

            if ( !isset($result['errcode']) || $result['errcode'] ) {
                return $result['errmsg']['errmsg'];
            }

            _model('wework_user')->create($user_info);
        }

        return array('操作成功', 'success', AnUrl("wework/admin/user"));
    }
}