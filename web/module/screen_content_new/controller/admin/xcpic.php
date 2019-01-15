<?php
/**
 * alltosun.com  xcpic.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-7-21 下午12:28:47 $
 * $Id$
 */
class Action
{
    private $per_page = 10;
    private $member_id  = 0;

    private $res_name = '';
    private $res_id   = 0;
    private $member_info = [];

    private $table = 'screen_content_make_pic_record';

    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);

        if ($this->member_info) {
            $this->res_name = $this->member_info['res_name'];
            $this->res_id   = $this->member_info['res_id'];

            Response::assign('member_info', $this->member_info);
        }
    }

    public function __call($action = '', $params = array())
    {
        // 内容展示必须符合各省的条件
        $search_filter = tools_helper::get('search_filter', array());

        $filter = [];

        if ( isset($search_filter['title']) && $search_filter['title']) {
            $filter['title'] = trim($search_filter['title']);
        }

        if ( isset($search_filter['creator']) && $search_filter['creator']) {
            $filter['res_name'] = trim($search_filter['creator']);
        }

        if ( !$filter ) $filter = [1 => 1];

        $count = _model($this->table)->getTotal($filter);

        if ($count) {
            $pager = new Pager($this->per_page);
            $list = _model($this->table)->getList($filter, ' ORDER BY `id` DESC '.$pager->getLimit());

            Response::assign('list', $list);

            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
        }
        Response::assign('count', $count);
        Response::assign('search_filter', $search_filter);
        Response::display("admin/xc_pic_list.html");
    }

    /**
     * 删除信息
     * @return string
     */
    public function delete()
    {
        $id = tools_helper::get('id', 0);

        if (!$id) return '请选择您要操作的信息';

        $info = _uri($this->table, array('id' => $id));
        if (!$info) return '该信息已经不存在';

        _model($this->table)->delete(array('id' => $id), " LIMIT 1 ");

        return 'ok';
    }

    public function add()
    {
        $id = tools_helper::get('id', 0);

        if ( $id ) {
            $info = _model($this->table) ->read(
                    array(
                        'id'       => $id,
                        'res_name' => $this->res_name,
                        'res_id'   => $this->res_id,
                    )
            );

            Response::assign('info', $info);
        }

        Response::display("admin/xc_pic_add.html");
    }

    public function save()
    {
        $id = tools_helper::post('id', 0);
        $info  = tools_helper::post('info', array());

        if (!isset($info['title']) || !$info['title']) {
            return '标题不能为空';
        }

        if (!$id && (!isset($_FILES['img_link']['tmp_name']) || !$_FILES['img_link']['tmp_name'])) {
            return '请上传图片';
        }

        $link = '';
        if ( $_FILES['img_link']['tmp_name'] ) {
            $link = upload_file($_FILES['img_link'],false, 'focus');
        }

        if ($link) {
            $info['link'] = $link;
        }
        //修改
        if ($id) {
            $old_info = _uri($this->table, array(
                    'id'     => $id,
                    'res_id' => $this->res_id,
                    'res_name' => $this->res_name,
                    'status'   => 1
            ));

            if (!$old_info) {
                return '对不起，该信息不存在';
            }

            _model($this->table)->update($id, $info);
        } else {
            $info['res_id'] = $this->res_id;
            $info['res_name'] = $this->res_name;
            $info['status']   = 1;

            _model($this->table)->create($info);
        }

//         return array('操作成功' , 'success' ,AnUrl('screen_content_new/admin/xcpic'));
        
        header('location:'.AnUrl("screen_content_new/admin/xcpic"));
    }

}
