<?php
/**
 * alltosun.com  nmg_msg.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-5-14 下午5:37:13 $
 * $Id$
 */

class Action
{
    // 生成短连接的地址
    private $url = 'https://open.work.weixin.qq.com/wwopen/mpnews?mixuin=scSLDgAABwBHsNcOAAAUAA&mfid=WW0313-2La3WAAABwCoZ5TVqm61jg9kpxU26&idx=0&sn=b2addb352d5eb1cd6492876495fbc2d7';

    public function __call($action = '', $param =array())
    {
        $id = tools_helper::get('id', '0');

        $limit = 10;

        $list = _model('screen_tui_yyt_record')->getList(
                    array( 
                            'id >'   => $id,
                            'date'   => 20180714,
                            'status' => 0
                         ),
                " LIMIT {$limit} "
        );

        if ( !$list ) {
            p('暂无信息');
            exit();
        }


        foreach ( $list as $k => $v ) {
            // 跳转页面需要
            $id = $v['id'];

            $cache = screen_msg_helper::short_url_cache($this->url, $v['user_number'], 4, 1);

            if ( !$cache ) continue;
///////////////////////////////测试使用
//             18310925147
//             13301163580
//             15701651914
//             13311111090
//         p($v);
//         $result = $this->send_msg(18310925147, AnUrl("s/{$cache}"));
//         p($result);
//         exit();
///////////////////////////////测试使用

            if ( ONDEV ) {
                echo '开发机不能跑这个程序';
                exit();
            }

            $result = $this->send_msg($v['phone'], AnUrl("s/{$cache}"));

            if ( !$result ) continue;

            _model('screen_tui_yyt_record')->update(
                    array('id' => $v['id']),
                    array('status' => 1)
            );

        }

        echo "<script>window.location.href = '". AnUrl("screen_msg/push?id=$id")."'</script>";
    }

    public function test()
    {
        $s_url = tools_helper::get('s', '');

//         , 
        $arr = array(15701651914, 18310925147, 13301163580);

        foreach ($arr as $v) {
            $res = $this->send_msg($v, AnUrl("s/{$s_url}"));
            p($res);
        }

    }

    public function send_msg($phone, $param1)
    {
//         return false;
        if ( !$phone || !$param1 ) return false;

        $template_id = 91554166;

        //发短信验证码
        $content =  array(
                'param1' => $param1,
        );

        $params['tel']         = $phone;
        $params['content']     = json_encode($content);
        $params['template_id'] = $template_id;

        $msg_result = _widget('message')->send_message($params);

        if ( 'ok' == $msg_result['info'] ) {
            return true;
        }

        return false;
    }
}