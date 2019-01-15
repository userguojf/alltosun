<?php
/**
 * alltosun.com  
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2018年4月27日: 2016-7-26 下午3:05:10
 * Id
 */

class Action
{
    private $page_size   = 6;
    private $member_id   = '';
    private $member_info = NULL;
    
    public function __construct()
    {
        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);
    
    }

    // added by guojf
    public function bar_off()
    {
        $device_id = tools_helper::post('res_id', 0);

        if ( !$device_id ) return $this->return_error('获取参数失败，请刷新重新');

        $device_info = _model('screen_device')->read(array('id' => $device_id));

        if ( !$device_info ) return $this->return_error('设备信息未找到');

        if ( !$device_info['status'] ) return $this->return_error('设备已经下架');

        _model('screen_device')->update($device_id, array('status' => 0));

        screen_device_helper::drop_off($device_info, 3);

        return $this->return_success();
    }

    public function return_error($errmsg)
    {
        return array('errcode' => 1, 'errmsg' => $errmsg);
    }

    public function return_success()
    {
        return array('errcode' => 0, 'errmsg' => 'ok');
    }

    // added by guojf

    public function edit_price()
    {

        $device_id = Request::Post('device_id', 0);
        $price     = Request::Post('price', 0);
        if (!$device_id) {
            return array('errcode' => 1, 'errmsg' => '由于网络原因，设备选择失效');
        }

        if (!$price) {
            return array('errcode' => 1, 'errmsg' => '请填写手机价格');
        }

        //读出修改价格的设备信息
        $device_info = _model('screen_device')->read(array('id' => $device_id, 'status' => 1));

        if (!$device_info) {
            return array('errcode' => 1, 'errmsg' =>'未知的设备信息（提示：设备可能下架）');
        }

        $content_info = _widget('screen')->get_type4_content_by_device($device_info['business_id'], $device_info['phone_name'], $device_info['phone_version']);

        //有宣传图
        if ( $content_info ) {
            $phone_name    = $device_info['phone_name_nickname'] ? $device_info['phone_name_nickname'] : $device_info['phone_name'];
            $phone_version = $device_info['phone_version_nickname'] ? $device_info['phone_version_nickname'] : $device_info['phone_version'];
            
            //默认白色
            $font_color_type = $content_info['font_color_type'] ? $content_info['font_color_type'] : 2 ;

            //操作结果标识
            $res = '';

            //给原图片压价格操作 是否为指定机型图
            if ($content_info['is_specify']) {
                $url = screen_helper::compose_screen_image($content_info['link'], $price, $font_color_type);
            } else {
                $url = screen_content_helper::compose_phone_model_image($phone_name, $phone_version, $content_info['link'], $font_color_type, $price);
            }

            if ($url) {
                //查看数据
                $show_pic_info = _model('screen_show_pic')->read(array('device_unique_id' => $device_info['device_unique_id']));

                //操作
                if ($show_pic_info) {
                    $id = _model('screen_show_pic')->update(
                            array('device_unique_id' => $device_info['device_unique_id']),
                            array (
                                'business_hall_id' => $device_info['business_id'],
                                'content_id'      => $content_info ['id'],
                                'content_link'    => $content_info['link'],
                                'font_color_type' => $font_color_type,
                                'is_specify'      => $content_info['is_specify'],
                                'link'            => $url,
                                'price'           => $price
                        ));
                } else {
                    $param = array(
                                'device_unique_id' => $device_info['device_unique_id'],
                                'business_hall_id' => $device_info['business_id'],
                                'content_id'       => $content_info['id'],
                                'content_link'     => $content_info['link'],
                                'font_color_type'  => $font_color_type,
                                'is_specify'       => $content_info['is_specify'],
                                'link'             => $url,
                                'price'            => $price
                            );
                    _model('screen_show_pic')->create($param);
                }
            }

        }

        // 添加价格统计
        screen_price_helper::record($device_info, $price, $content_info['id']);

        //修改设备表的展示价格
        _model('screen_device')->update($device_id, array('price' => $price));

        //推送
        push_helper::push_msg('2');

        return array('errcode'=> 0, 'errmsg' =>'edited');
    }

    public function edit_all_price()
    {
    
        $device_id = Request::Post('device_id', 0);
        $price     = Request::Post('price', 0);
    
        if (!$device_id && !$price) {
            return array('errcode' => 1, 'errmsg' => '由于网络原因，设备选择失效');
        }
        
        if (!$price) {
            return array('errcode' => 1, 'errmsg' => '过滤');
        }
    
        if ($price && !$device_id) {
            return array('errcode' => 1, 'errmsg' => '由于网络原因，设备选择失效');
        }
    
        //读出修改价格的设备信息
        $device_info = _model('screen_device')->read(array('id' => $device_id, 'status' => 1));
    
        if (!$device_info) {
            return array('errcode' => 1, 'errmsg' =>'未知的设备信息（提示：设备可能下架）');
        }
    
        $content_info = _widget('screen')->get_type4_content_by_device($device_info['business_id'], $device_info['phone_name'], $device_info['phone_version']);
    
        //有宣传图
        if ( $content_info ) {
            $phone_name    = $device_info['phone_name_nickname'] ? $device_info['phone_name_nickname'] : $device_info['phone_name'];
            $phone_version = $device_info['phone_version_nickname'] ? $device_info['phone_version_nickname'] : $device_info['phone_version'];
    
            //默认白色
            $font_color_type = $content_info['font_color_type'] ? $content_info['font_color_type'] : 2 ;
    
            //操作结果标识
            $res = '';
    
            //给原图片压价格操作
            if ($content_info['is_specify']) {
                $url = screen_helper::compose_screen_image($content_info['link'], $price, $font_color_type);
            } else {
                $url = screen_content_helper::compose_phone_model_image($phone_name, $phone_version, $content_info['link'], $font_color_type, $price);
            }
    
            if ($url) {
                //查看数据
                $show_pic_info = _model('screen_show_pic')->read(array('device_unique_id' => $device_info['device_unique_id']));
    
                //操作
                if ($show_pic_info) {
                    $id = _model('screen_show_pic')->update(
                            array('device_unique_id' => $device_info['device_unique_id']),
                            array (
                                    'business_hall_id' => $device_info['business_id'],
                                    'content_id'      => $content_info ['id'],
                                    'content_link'    => $content_info['link'],
                                    'font_color_type' => $font_color_type,
                                    'is_specify'      => $content_info['is_specify'],
                                    'link'            => $url,
                                    'price'           => $price
                            ));
                } else {
                    $param = array(
                            'device_unique_id' => $device_info['device_unique_id'],
                            'business_hall_id' => $device_info['business_id'],
                            'content_id'       => $content_info['id'],
                            'content_link'     => $content_info['link'],
                            'font_color_type'  => $font_color_type,
                            'is_specify'       => $content_info['is_specify'],
                            'link'             => $url,
                            'price'            => $price
                    );
                    _model('screen_show_pic')->create($param);
                }
            }
    
        }
    
        // 添加价格统计
        screen_price_helper::record($device_info, $price, $content_info['id']);
    
        //修改设备表的展示价格
        _model('screen_device')->update($device_id, array('price' => $price));
    
        //推送
        push_helper::push_msg('2');
    
        return array('errcode'=> 0, 'errmsg' =>'edited');
    }
    
public function load_data()
    {
        $page   = tools_helper::post('page', 1);
        $put_type   = tools_helper::post('put_type', 1);
        $filter = htmlspecialchars_decode(tools_helper::post('filter',''));
        $filter = json_decode($filter,true);
        $order         = " ORDER BY `view_order` ASC,`add_time` DESC ";
        $max = 3;
        $page  = ($page-1)*$max;
        $content_list = _model('screen_content')->getList($filter , $order.' LIMIT '.$page.','.$max);
        
        $html ='';
        foreach ($content_list as $v) {
            $html .= '<li>';
            $html .= '<div class="title">';
            $html .= '<h3>'.$v['title'].'</h3>';
            
            if($v['res_name'] == 'group'){
                $html .= ' <p>集团级</p>';
            }elseif($v['res_name'] == 'province'){
                $html .= ' <p>省级</p>';
            }elseif($v['res_name'] == 'city'){
                $html .= '<p>市级</p>';
            }elseif($v['res_name'] == 'area'){
                $html .= '<p>区级</p>';
            }else{
                $html .= '<p>厅店级</p>';
            }

            $link = SITE_URL;
            $link_fun = SITE_URL.'/screen_dm';
            $html .= '</div>';
            $html .= '<div class="con">';
            $html .= '<div class="pic">';
            $link_thumb = screen_dm_helper::get_thumb($v['link']);
            if($v['type'] == 1 || $v['type'] == 4){
                $html .= '<a href="'._image($v['link'], 'middle').'"'.'target="_blank">';
                $html .= '<img width="100%" height = "100%" src="'._image($v['link'], 'middle').'"'.'"></a>';
                
            }else if($v['type'] == 2){
                $html .= '<video style="width:100%;height:100%; ">';
                $html .= '<source src="'.'/upload/video/'.$v['link'].'"'.'type="video/mp4"></source><video>';
            }elseif ($v['type'] == 3){
                $html .= '<a href="'.$v['link'].'">'.'链接(点击跳转)</a>';
            }elseif ($v['type'] == 5){
                $html .= '<a href="'.$v['link'].'"'.'target="_blank">';
                $html .= '<img width="100%" height = "100%" src="'.$v['link'].'"'.'"></a>';
            }
            
            $html .= '</div>';
            $html .= '<div class="desc">';
            $html .= '<p>上线时间<span>'.substr($v['start_time'],0,10).'</span></p>';
            $html .= '<p>下线时间<span>'.substr($v['end_time'],0,10).'</span></p>';
            $html .= '<p class = "toufang" >投放机型';
            //获取机型
            $phone_version = screen_dm_helper::get_content_phone_version($v['id']);
            $phone_version_num = screen_dm_helper::get_content_phone_version_num($v['id']);
            if($phone_version_num == 1){
                if($phone_version == 'all' || $phone_version == ''){
                    $html .='<span>全部机型</span>';
                }else{
                    $html .='<span>'.$phone_version.'</span>';
                }
            }elseif($phone_version_num == 0){
                $html .='<span>暂未投放</span>';
            }else{
                $html .='<span>'.$phone_version_num.'种机型</span>';
            }
            
            $html .='</p>';
            $html .='<p>轮播数';
            $html .='<span>'.screen_stat_helper::get_content_stat_num($v['id'], $this->member_info['res_name'], $this->member_info['res_id']).'</span>';
            $html .='</p>';
            $html .='<p>点击数';
            $html .='<span>'.screen_stat_helper::get_content_res_click_total($v['id']).'</span>';
            $html .='</p></div></div>';
           
            $html .=' <div class="options" res_id='.$v['id'].'>';
            if($put_type != 2){
                if(!$v['status']){
                    $html .=' <a href="javascript:void(0);" class="up_del">上线</a>';
                }else{
                    $html .=' <a href="javascript:void(0);" class="down_del">下线</a>';
                }
            }
            if($put_type == 1){
                $html .=' <a href="'.$link_fun.'/edit?id='.$v['id'].'&search_type=0"'.')}>编辑</a>';
            }
            $html .='</div>';
            
            $html .= '</li>';
        }
        return array('info' => 'ok', 'errno' => 0, 'list' => $html);
        //return array('info' => 'ok', 'content_list' => $content_list);


    }
    
    
    public function update_res_status()
    {
        $content_id = Request::Post('id', 0);
        $status = Request::Post('status', 0);
// p($content_id);exit();
        if (!$content_id) {
            return '信息错误';
        }

        $info = _uri('screen_content',$content_id);

        if (!$info) {
            return '内容不存在';
        }

        //设备宣传图发布，则验证是否发布到设备
        if ($info['type'] == 4 && $status == 1) {
            $info = _model('screen_content_res')->read(array('content_id' => $info['id'], 'phone_name != ' => ''));
            if (!$info) {
                return '发布失败，请在编辑页投放到指定设备';
            }
        }

        if ($status == 2) {
            //删除内容
            _model('screen_content')->delete($content_id);
            //删除发布
            _model('screen_content_res')->delete(array('content_id' => $content_id));
        } else {
            //修改状态
            _model('screen_content')->update($content_id,array('status' => $status));
        }

//////////////////////////////////////////////////////// 推送发布 start ////////////////////////////////////////////////////////////////
        $res_list = _model('screen_content_res')->getList(array('content_id' => $info['id']));

        $registration_ids = array();

        foreach ( $res_list as $k => $v ){
            //获取需要推送的注册id     put_registration_ids
            $registration_id = _widget('screen_content.put')->get_put_registration_ids($v);

            if (!$registration_id) {
                continue;
            }

            $registration_ids = array_merge($registration_ids, $registration_id);

        }

        if ($registration_ids) {
            //推送
            push_helper::push_msg('2', $registration_ids);
        }
//////////////////////////////////////////////////////// 推送发布end /////////////////////////////////////////
        return 'ok';
    }
    
    public function edit_all_phone()
    {
        $id = Request::Post('id', '');
        if (!$content_id) {
            return '参数不合法！';
        }
        $content_info = screen_content_helper::get_content_info($content_id);
        
        if (!$content_info) {
            return '内容不能修改!';
        }
    
    }
    
    
    public function reset_price()
    {
    
        $reset     = Request::Post('reset', 0);
        $device_id = Request::Post('device_id', 0);
        $price     = 0;
        if (!$device_id) {
            return array('errcode' => 1, 'errmsg' => '由于网络原因，设备选择失效');
        }
    
        //读出修改价格的设备信息
        $device_info = _model('screen_device')->read(array('id' => $device_id, 'status' => 1));
    
        if (!$device_info) {
            return array('errcode' => 1, 'errmsg' =>'未知的设备信息（提示：设备可能下架）');
        }
    
        $content_info = _widget('screen')->get_type4_content_by_device($device_info['business_id'], $device_info['phone_name'], $device_info['phone_version']);
        //有宣传图
        if ( $content_info ) {
            $phone_name    = $device_info['phone_name_nickname'] ? $device_info['phone_name_nickname'] : $device_info['phone_name'];
            $phone_version = $device_info['phone_version_nickname'] ? $device_info['phone_version_nickname'] : $device_info['phone_version'];
    
            //默认白色
            $font_color_type = $content_info['font_color_type'] ? $content_info['font_color_type'] : 2 ;
    
            //操作结果标识
            $res = '';
    
            //给原图片压价格操作 是否为指定机型图
            if ($content_info['is_specify']) {
                $url = screen_helper::compose_screen_image($content_info['link'], $price, $font_color_type,$title='');
           
            } else {
                $url = screen_content_helper::compose_phone_model_image($phone_name, $phone_version, $content_info['link'], $font_color_type, $price);
            }
    
            if ($url) {
                //查看数据
                $show_pic_info = _model('screen_show_pic')->read(array('device_unique_id' => $device_info['device_unique_id']));
    
                //操作
                if ($show_pic_info) {
                    $id = _model('screen_show_pic')->update(
                            array('device_unique_id' => $device_info['device_unique_id']),
                            array (
                                    'business_hall_id' => $device_info['business_id'],
                                    'content_id'      => $content_info ['id'],
                                    'content_link'    => $content_info['link'],
                                    'font_color_type' => $font_color_type,
                                    'is_specify'      => $content_info['is_specify'],
                                    'link'            => $url,
                                    'price'           => $price
                            ));
                } else {
                    $param = array(
                            'device_unique_id' => $device_info['device_unique_id'],
                            'business_hall_id' => $device_info['business_id'],
                            'content_id'       => $content_info['id'],
                            'content_link'     => $content_info['link'],
                            'font_color_type'  => $font_color_type,
                            'is_specify'       => $content_info['is_specify'],
                            'link'             => $url,
                            'price'            => $price
                    );
                    _model('screen_show_pic')->create($param);
                }
            }
    
        }
    
        // 添加价格统计
//         screen_price_helper::record($device_info, $price, $content_info['id']);
    
        //修改设备表的展示价格
        _model('screen_device')->update($device_id, array('price' => $price));
    
        //推送
        push_helper::push_msg('2');
    
        return array('errcode'=> 0, 'errmsg' =>'edited');
    }
    
}