<?php

class Action
{
    private $user_id = 0;

    public function __construct()
    {
        $this->user_id = user_helper::get_user_id();
    }

    public function __call($action = '', $params = array())
    {
        if (!$this->user_id) {
            return '请登录后操作！';
        }

        $prize_list = $list = array();

        $prize_list = _model('user_prize')->getList(
            array('user_id' => $this->user_id),
            ' ORDER BY `id` DESC '
        ); 

        foreach ($prize_list as $k => $v) {
            $prize_card_info = _uri('prize_card', $v['card_id']);
            $list[$k] = $prize_card_info;
        }
        Response::assign('prize_list',$prize_list);
        Response::assign('list',$list);
        Response::display('gift.html');
    }
}