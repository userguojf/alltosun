<?php
/**
  * alltosun.com 各平台坐标对比 coord_diff.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年5月7日 下午12:47:59 $
  * $Id$
  */

class Action
{
    private $per_page = 20;
//     private $member_id  = 0;
//     private $member_res_name = '';
//     private $member_res_id   = 0;
//     private $member_info;
//     private $ranks           = 0;
//     private $time;

//     public function __construct()
//     {
//         $this->member_id   = member_helper::get_member_id();
//         $this->time        = date('Y-m-d H:i:s');
//         $this->member_info = member_helper::get_member_info($this->member_id);

//         if ($this->member_info) {
//             $this->member_res_name = $this->member_info['res_name'];
//             $this->member_res_id   = $this->member_info['res_id'];
//             $this->ranks           = $this->member_info['ranks'];
//         }
//             Response::assign('member_info', $this->member_info);
//     }
    /**
     * 坐标对比
     * @param string $action
     * @param array $params
     */
    public function __call($action = '', $params = array())
    {
        // 内容展示必须符合各省的条件
        $page               = tools_helper::get('page_no', 1);

        $filter['lat_jc >'] = 10;
        $filter['lat_szdt >']=10;

        $coords = get_data_list('screen_business_hall_coord_diff', $filter, ' ORDER BY `id` DESC ', $page, $this->per_page);

        Response::assign('coords', $coords);
        Response::display("admin/coord_diff.html");
    }

}
?>