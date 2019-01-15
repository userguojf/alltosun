<?php
/**
* alltosun.com index.php
================================================
* 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
* 网站地址: http://www.alltosun.com
* ----------------------------------------------------------------------------
* 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
* ============================================================================
* @author: 祝利柯 (zhulk@alltosun.com)
* @date:2015-4-28
* $$Id$$
*/

class Action
{
    private $per_page = 30;

    public function __call($action, $params = array())
    {
        $member_id = member_helper::get_member_id();

        $filter = array('member_id' => $member_id);

        $business_hall_user_info = _uri('business_hall_user', $filter);

        Response::assign('business_hall_user_info', $business_hall_user_info);
        Response::display('admin/bu_user.html');
    }

    /**
     *
     */
    public function save()
    {
       $member_id = member_helper::get_member_id();

       $data    = AnForm::parse('admin/bu_user.html');

       if (!$data) {
           return '请正确填写内容！';
       }

       // 营业厅名称
       if (empty($data['bu_user']['user_name'])) {
           return '请填写联系人姓名！';
       }


       // 省份
       if (empty($data['bu_user']['phone'])) {
            return '请填写联系人手机号！';
       }

       $filter = array('member_id' => $member_id);

       $business_hall_user_info = _uri('business_hall_user', $filter);

       if ($business_hall_user_info) {
           _model('business_hall_user')->update($business_hall_user_info['id'], $data['bu_user']);
       } else {
           $data['bu_user']['member_id'] = $member_id;
           _model('business_hall_user')->create($data['bu_user']);
       }

       return array('操作成功', 'success', AnUrl("admin"));
    }

    /**
     * 删除
     */
    public function delete()
    {
        $id = tools_helper::get('id', 0);
        if (!$id) {
            return '请选择你要删除的内容!';
        }
        $info = _model('4g_business_hall')->read(array('id'=>$id));
        if (!$info) {
            return '该内容不存在！';
        }
        _model('4g_business_hall')->delete($id);
        return 'ok';
    }
}
?>