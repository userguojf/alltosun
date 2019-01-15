<?php
/**
 * alltosun.com  dm.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-1-30 下午3:36:51 $
 * $Id$
 */

class Action
{
    private $depart = '[30349]';
    private $token  = '';

    public function __construct()
    {
        $this->token = _widget('wework.token')->get_access_token('work');
    }

    public function index()
    {
        $page = tools_helper::Get('page', 1);

        $limit = 5;
        $page_num = ( $page - 1) * $limit;

        $list = _model('wework_zj_record')->getList(
                array('id >' => $id, 'status' => 0), " LIMIT $page_num, $limit ");

        if ( !$list ) return '结束';

        foreach ($list as $k => $v) {
            // 下次数据的地方
            $id = $v['id'];

            $user_info = _model('wework_user')->read(
                         array('user_id LIKE' => '%' . $v['user_id'] . '%'), 
                         " ORDER BY `user_id` DESC "
                    );

            // 唯一账户
            if ( !$user_info ) {
                $v['user_id'] = $v['user_id'] . '_01';
            } else {
                $v['user_id'] = wework_rule_helper::name($user_info['user_id']);
            }


            $this->create($v);
        }

        echo "<script>window.location.href = '". AnUrl('guojf/wework') ."?id={$id}'</script>";
    }

    public function create($filter)
    {

        $data = '{
                "userid": "'.$filter["user_id"].'",
                "name": " '.$filter["name"].' ",
                "mobile": "'.$filter["phone"].' ",
                "department":'.$this->depart.',
                "extattr": {
                        "attrs":[
                    {"name":"an_id","value":" '.$filter["user_number"].' "},
                    {"name":"analog_i","value":" '.$filter["user_number"].' "}
                        ]
                }
            }';

        $url = wework_config::$create_user_url . 'access_token=' . $this->token;

        $json = curl_post($url, $data);

        $info = json_decode($json, true);

        if ( isset($info['errcode']) && !$info['errcode'] ) {
            $this->update($filter['id'], 3, '', $filter);
        } else {
            $errmsg = _widget('wework.errmsg')->get_errmsg($info['errcode']);
            $this->update($filter['id'], 2, $errmsg, $filter);
        }

        return true;
    }

    public function update($id, $status, $errmsg, $filter)
    {
        _model('wework_zj_record')->update(
            array('id' => $id),
            array('status' => $status, 'errmsg' => $errmsg)
        );

        if ( $errmsg ) return true;

        $this->create_user($filter);

        return true;
    }

    public function create_user($filter)
    {
        $param = array (
                'user_id'    => $filter['user_id'],
                'name'       => $filter['name'],
                'department' => $this->depart,
                'mobile'     => $filter['phone'],
        );

        _model('wework_user')->create($param);

        return true;
    }
}