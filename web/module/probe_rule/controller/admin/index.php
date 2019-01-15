<?php
/**
 * alltosun.com 探针规则 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2017-4-6 下午12:01:34 $
*/
class Action
{
    private $per_page = 10;

    public function index()
    {
         $list = _model('probe_rule')->getList(array('status'=>1));

        Response::assign('list', $list);
        Response::display('admin/index.html');
    }

    public function add()
    {
        Response::display('admin/add.html');
    }

    public function edit()
    {
        $id = Request::Get('id', 0);

        if ( !$id ) {
            return '请选择要编辑的规则';
        }

        $info = _model('probe_rule')->read(array('id'=>$id));

        if ( !$info ) {
            return '规则不存在';
        }

        Response::assign('info', $info);
        Response::display('admin/add.html');
    }

    public function save()
    {
        $id      = Request::Post('id', 0);
        $content = Request::Post('content', '');
        $alias   = Request::Post('alias', '');

        if ( !$content ) {
            return '请输入规则内容';
        }
        if ( !$alias ) {
            return '请输入规则别名';
        }

        $info = _model('probe_rule')->read(array('alias'=>$alias));

        if ( $info && $info['id'] != $id ) {
            return '已存在相同规则别名';
        }

        $create = array(
            'content'   =>  $content,
            'alias'     =>  $alias
        );

        if ( $id ) {
            _model('probe_rule')->update(array('id'=>$id), $create);
        } else {
            $id = _model('probe_rule')->create($create);

            if ( !$id ) {
                return '添加失败';
            }
        }
        Response::redirect(AnUrl('probe_rule/admin'));
        Response::flush();
    }
}
