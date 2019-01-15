<?php

/**
 * alltosun.com function_container
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2016-6-13 下午3:37:07 $
 * $Id: $
 */
class Action
{
    private $per_page = 30;
    
    /**
     * 用户得奖记录列表
     * @param unknown $action
     * @param unknown $params
     */
    public function __call($action = '', $params = array())
    {
        $order         = ' ORDER BY `id` DESC ';
        $page          = tools_helper::get('page_no', 1);
        $user_phone    = tools_helper::get('user_phone', '');
        $search_filter = Request::get('search_filter' , array());
        $filter     = $list = array(); 
		//手机号搜索
        if (isset($search_filter['user_phone']) && $search_filter['user_phone']) {
/*         	$filter['`phone` LIKE'] = '%'.$search_filter['user_phone'].'%';*/
        	
        	$user_id = _uri('user', array('phone' => $search_filter['user_phone']), 'id');
 
        	     if($user_id) {
        	          $filter['user_id'] = $user_id;
                } else {
        	          $filter['user_id'] = 0;
                }
        }
        //添加时间搜索
        if (isset($search_filter['add_time']) && $search_filter['add_time']) {
        	$filter['`add_time` LIKE'] = '%'.$search_filter['add_time'].'%';
        }
        
        if (!$filter) {
            $filter = array(1 => 1);
        }
        
        $prize_total = _model('user_prize')->getTotal($filter);
        
        //分页
        if ($prize_total) {
            $pager = new Pager($this->per_page);
			
            $list = _model('user_prize')->getList($filter,  $pager->getLimit($page));
            
            if ($pager->generate($prize_total,$page)) {
                Response::assign('pager', $pager);
            }
        }
       
        Response::assign('list', $list);
        Response::assign('total', $prize_total);
        Response::assign('search_filter', $search_filter);
        Response::display('admin/user_prize.html');
    }
    
    /**
     * 删除奖品
     * @return multitype:string
     */
    public function delete()
    {
        $id = Request::getParam('id');
        if (!$id) {
            return array('info' => '要删除的奖品不存在！');
        }
        $ids = explode(',',trim($id,','));
        
        foreach($ids as $k => $v) {
            _model('user_prize')->delete($v);
        }
        
        return array('info' => 'ok');
    }
}