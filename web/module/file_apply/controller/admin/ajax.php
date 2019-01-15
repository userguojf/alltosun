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
 * 2018年5月3日: 2016-7-26 下午3:05:10
 * Id
 */
class Action
{
    /**
     * 根据渠道编码获取营业厅信息
     */
    public function getInfoByNumber()
    {
        $user_number = Request::post('user_number', '');
        $province_id = Request::post('province_id', 0);
        $business_info = _uri('business_hall', array('user_number' => $user_number, 'province_id' => $province_id));

        return $business_info;
    }
 

    /**
     * 自动补全渠道编码
     */
    public function get_user_number_list()
    {
        $key_word = Request::Get('term', '');
        $province_id = Request::Get('province_id', '');
        $city_id = Request::Get('c_id', '');

        $filter = array(
            'user_number LIKE' => "{$key_word}%",
            'province_id ' => $province_id,
        );
        if ($city_id) {
            $filter['city_id'] = $city_id;
        }
        if (!$key_word) {
            return '数据不存在';
        }
        $user_number_list = _model('business_hall')->getFields('user_number', $filter);
        if ($user_number_list) {
            exit(json_encode($user_number_list));
        }
    }

    /**
     * 添加申请门店
     * @return string
     */
    public function add_apply()
    {
        $id = Request::Post('id', 0);
        //单条申请id
        $aid = Request::Post('apply_id', 0);
        $member_id = Request::Post('member_id', 0);
        $res_id = Request::Post('res_id', 0);
        $res_name = Request::Post('res_name', '');
        $time = date('Y-m-d H:i:s', time());
        $info = Request::Post('info', array());
        $info['create_time'] = $time;
        $info['member_id'] = $member_id;
        $info['res_id'] = $res_id;
        $info['res_name'] = $res_name;
        $apply_id = 0;

        // apply status 申请状态 10 市未提交 13市提交 20省未提交  14 省提交 11 省通过 12 省拒绝  21 集团通过 22集团拒绝
        //convert_apply status 0 未提交  1省待审批 2省审批中 3省审批结束 4集团待审批 5集团审批中 6集团审批结束
        //分区类型0 门头 1入门 2业务办理 3终端体验 4交付区
        $status = 0;
        if($res_name == 'city'){
            $status = 0;
        }else if($res_name == 'province'){
            $status = 10;
        }
        $apply_status = 0;
        //根据权限确定初始状态
        //p($res_name);
        if ($res_name == 'province') {
            $apply_status = 20;
        } else if ($res_name == 'city') {
            $apply_status = 10;
        }
        
        $convert_info = array(
            'member_id' => $member_id,
            'province_id' => $info['province_id'],
            'city_id' => $info['city_id'],
            'area_id' => $info['area_id'],
            'status' => $status,
            'add_time' => $time,
        );


        //无id第一条数据
        if (!$id) {
            //第一次
            $convert_info['business_number'] = 1;
            //添加主表信息
            $convert_id = _model('convert_apply')->create($convert_info);
        } else {
            $convert_id = $id;
        }

        //添加前先查出又几条数据
        $num = _model('apply')->getTotal(array('cid' => $convert_id));
        if (!$convert_id) {
            return "网络错误";
        }

        $info['status'] = $apply_status;
        //关联主表id
        $info['cid'] = $convert_id;
        //布局方案图片
        $plan_type = $info['plan_type'];
        unset($info['plan_type']);
        if (!empty($_FILES['link']['tmp_name'][0])) {
            $plan_link_files = file_apply_helper::file_info($_FILES['link']);

            $link = upload_file($plan_link_files, false, 'focus');
            //生成缩略图
            _widget('screen_content')->make_thumb($link);
            //主平面图
            $info['link'] = $link;
        }

        //存在是修改  操作单挑申请主数据
        if ($aid) {
            _model('apply')->update($aid, $info);
            $apply_id = $aid;
        } else {
            //返回申请id
            $apply_id = _model('apply')->create($info);
        }

        //存储区域图片  0 门头 1入门 2业务办理 3终端体验 4交付区
        $plan_res = array(
            'apply_id' => $apply_id,
        );
        //门头
        if (!empty($_FILES['plan_mentou']['tmp_name'][0])) {
            $plan_mentou_files = file_apply_helper::file_info($_FILES['plan_mentou']);
            $plan_link = upload_file($plan_mentou_files, false, 'focus');
            //生成缩略图
            _widget('screen_content')->make_thumb($plan_link);
            $plan_res['plan_type'] = 0;
            $plan_res['link'] = $plan_link;
            if ($aid) {
                $res = _model('apply_plan_res')->read(array('apply_id' => $aid, 'plan_type' => 0));
                if ($res) {
                    _model('apply_plan_res')->update(array('apply_id' => $aid, 'plan_type' => 0), $plan_res);
                } else {
                    _model('apply_plan_res')->create($plan_res);
                }
            } else {
                _model('apply_plan_res')->create($plan_res);
            }
        }

        //1入门
        if (!empty($_FILES['plan_rumen']['tmp_name'][0])) {
            $plan_mentou_files = file_apply_helper::file_info($_FILES['plan_rumen']);
            $plan_link = upload_file($plan_mentou_files, false, 'focus');
            //生成缩略图
            _widget('screen_content')->make_thumb($plan_link);
            $plan_res['plan_type'] = 1;
            $plan_res['link'] = $plan_link;
            if ($aid) {
                $res = _model('apply_plan_res')->read(array('apply_id' => $aid, 'plan_type' => 1));
                if ($res) {
                    _model('apply_plan_res')->update(array('apply_id' => $aid, 'plan_type' => 1), $plan_res);
                } else {
                    _model('apply_plan_res')->create($plan_res);
                }
            } else {
                _model('apply_plan_res')->create($plan_res);
            }
        }

        //2业务办理
        if (!empty($_FILES['plan_yewu']['tmp_name'][0])) {
            $plan_mentou_files = file_apply_helper::file_info($_FILES['plan_yewu']);
            $plan_link = upload_file($plan_mentou_files, false, 'focus');
            //生成缩略图
            _widget('screen_content')->make_thumb($plan_link);
            $plan_res['plan_type'] = 2;
            $plan_res['link'] = $plan_link;
            if ($aid) {
                $res = _model('apply_plan_res')->read(array('apply_id' => $aid, 'plan_type' => 2));
                if ($res) {
                    _model('apply_plan_res')->update(array('apply_id' => $aid, 'plan_type' => 2), $plan_res);
                } else {
                    _model('apply_plan_res')->create($plan_res);
                }
            } else {
                _model('apply_plan_res')->create($plan_res);
            }
        }

        //3终端体验
        if (!empty($_FILES['plan_zhongduan']['tmp_name'][0])) {
            $plan_mentou_files = file_apply_helper::file_info($_FILES['plan_zhongduan']);
            $plan_link = upload_file($plan_mentou_files, false, 'focus');
            //生成缩略图
            _widget('screen_content')->make_thumb($plan_link);
            $plan_res['plan_type'] = 3;
            $plan_res['link'] = $plan_link;
            if ($aid) {
                $res = _model('apply_plan_res')->read(array('apply_id' => $aid, 'plan_type' => 3));
                if ($res) {
                    _model('apply_plan_res')->update(array('apply_id' => $aid, 'plan_type' => 3), $plan_res);
                } else {
                    _model('apply_plan_res')->create($plan_res);
                }
            } else {
                _model('apply_plan_res')->create($plan_res);
            }
        }

        //4交付区
        if (!empty($_FILES['plan_jiaofu'    ]['tmp_name'][0])) {
            $plan_mentou_files = file_apply_helper::file_info($_FILES['plan_jiaofu']);
            $plan_link = upload_file($plan_mentou_files, false, 'focus');
            //生成缩略图
            _widget('screen_content')->make_thumb($plan_link);
            $plan_res['plan_type'] = 4;
            $plan_res['link'] = $plan_link;
            if ($aid) {
                $res = _model('apply_plan_res')->read(array('apply_id' => $aid, 'plan_type' => 4));
                if ($res) {
                    _model('apply_plan_res')->update(array('apply_id' => $aid, 'plan_type' => 4), $plan_res);
                } else {
                    _model('apply_plan_res')->create($plan_res);
                }
            } else {
                _model('apply_plan_res')->create($plan_res);
            }
        }

        $apply_info = _model('apply')->read($apply_id);

        //存在是单挑数据修改 主表数量不加 否则数量加一
        if ($aid) {
            _model('convert_apply')->update($convert_id, array('status' => $status));
        } else {
            $business_number = $num + 1;
            _model('convert_apply')->update($convert_id, array('business_number' => $business_number, 'status' => $status));
        }

        //所有cid 下数据更改状态
        _model('apply')->update(array('cid' => $convert_id), array('status' => $apply_status));

        $apply_info = _model('apply')->read($apply_id);

        //用于页面区分
        $apply_info['aid'] = $aid;
        if ($apply_info) {
            return $apply_info;
        } else {
            return array('info' => 'error');
        }
    }

    /**
     * 执行文件软删除
     * @return string
     */
    public function update_file_status()
    {

        $id = Request::Post('id', 0);

        if (!$id) return '信息错误';

        $info = _uri('files', $id);

        if (!$info) return '信息不存在';

        // 执行软删除
        _model('files')->update($id, array('is_del' => 1));

        // 获取还没有删除的文件个数
        $count = _model('files')->getTotal(array('is_del' => 0));

        return ['info' => 'ok','count' => $count];
    }

    /**
     * 增加文件浏览次数
     */
    public function update_file_count()
    {
        $id = Request::Post('id', 0); // 文件ID
        $mId = Request::Post('mId', 0); // 用户ID
        $info = _uri('file_record', array('member_id' => $mId, 'file_id' => $id));
        $member_info = _uri('member', $mId);
        $con = [
            'member_id' => $member_info['id'],
            'file_id' => $id,
            'res_name' => $member_info['res_name'],
            'ranks' => $member_info['ranks'],
            'res_id' => $member_info['res_id'],
            'status' => 1,
            'view_count' => 1,
        ];
        // 集团管理员不计入操作记录
        if ($member_info['res_name'] != 'group') {
            if (!$info) {
                _model('file_record')->create($con);
            } else {
                // 条件
                $where = [
                    'member_id' => $mId,
                    'file_id' => $id
                ];
                // 更新的字段
                $up = [
                    'update_time' => date('Y-m-d H:i:s', time()),
                    'status' => 1, // 将状态更改为查看过
                ];
                _model('file_record')->update($where, $up);
                _model('file_record')->create(array('id' => $info['id']), 'ON DUPLICATE KEY UPDATE view_count=view_count+1');

            }
            // 文件在线浏览次数+1
            // _model('files')->create(array('id'=>$id), 'ON DUPLICATE KEY UPDATE view_count=view_count+1');
        }
    }

    /**
     * 集团管理发布文件 短信通知省市管理员
     */
    public function file_release_noti()
    {
        $file_name = Request::Post('file_name', '');
        $print_time = Request::Post('print_time', '');
        $file_number = Request::Post('file_number', '');
        // 查询条件
        $where = [
            'province',
            'city',
            1
        ];
        $param2 = '集团于' . $print_time . '印发了文件号为“' . $file_number . '”的' . $file_name . '文件';
        $param3 = "登录门店数字化运营平台下载或查看 http://udb.pzclub.cn。";
        $content = [
            'param1' => '集团文件',
            'param2' => $param2,
            'param3' => $param3,
        ];
        $memberphones = _model('member')->getCol("SELECT bu.phone FROM `business_hall_user` as bu, member as mb WHERE bu.member_id = mb.id AND mb.res_name in (?,?) AND mb.status = ?", $where);

        // 电信方手机
        $phones = [
            // '18801235362',
            // '13301163580',
            '18813044687'
        ];
        $params['content'] = json_encode($content);
        $params['template_id'] = 91551836;
        foreach ($phones as $v) {
            $params['tel'] = $v;
            $msg_res = _widget('message')->send_message($params);
        }

        return $msg_res;
    }

    // 查询文件号是否重复
    public function file_number_exists()
    {
        $file_number = trim(Request::Post('file_number', ''));
        $where = [
            'is_del' => 0,
            'file_number' => $file_number
        ];
        $res = _uri('files', $where);
        if ($res) {
            echo 0;
        } else {
            echo 1;
        }

    }

    /**
     * 取消删除全部
     */
    public function delete_apply()
    {

        $id = Request::Post('id', 0);
        if (!$id) {
            return array('info' => 'err', 'msg' => "参数错误");
        }
        $convert_apply_info = _model('convert_apply')->read($id);

        $apply_list = _model('apply')->getList(array('cid' => $id));
        $ids = [];
        //取出全部符合的id
        foreach ($apply_list as $k => $v) {
            $ids[$k] = $v['id'];
        }

        //删除关系表
        _model('apply_plan_res')->delete(array('apply_id' => $ids));
        //删除申请信息
        _model('apply')->delete(array('cid' => $id));

        _model('convert_apply')->delete(array('id' => $id));
        return array('info' => 'ok', 'msg' => "成功");
    }


    /**
     * 审核通过
     */
    public function approval_sure()
    {
        //组审核状态  0 市未提交  10 省未提交 1省待审批 2省成功 3省失败 4集团待审批 5成功 6失败
        //单条审核状态  0 未审核 1 省审核通过 2省审核不通过 3集团通过 4集团不通过
        $cid = Request::Post('cid', 0);
        $member_id = Request::Post('member_id', 0);
        $apply_id = Request::Post('apply_id', 0);
        $res_name = Request::Post('res_name', '');
        $time = date('Y-m-d H:i:s', time());
        $member_user = file_apply_helper::get_user_by_memberid($member_id);
        $info = array(
            'apply_id' => $apply_id,
        );


        $record_info = _model('convert_record')->read($info);

        //检查是否全部审核完
        $apply_ids = _model('apply')->getFields('id', array('cid' => $cid));
        $num = count($apply_ids);
        $record_num = 0;
        //没有数据第一次审核 新添加数据
        if (empty($record_info)) {
            if ($res_name == 'province') {
                $info['province_checker'] = $member_user;
                $info['province_time'] = $time;
                $info['check_status'] = 1;
                _model('convert_record')->create($info);
                $record_ids = _model('convert_record')->getFields('id', array('apply_id' => $apply_ids, 'check_status' => 1));
                $rescord_num = count($record_ids);
                if ($num == $rescord_num) {
                    _model('convert_apply')->update($cid, array('status' => 2));
                }
            }

            if ($res_name == 'group') {
                $info['group_checker'] = $member_user;
                $info['group_time'] = $time;
                $info['check_status'] = 3;
                _model('convert_record')->create($info);
                $record_ids = _model('convert_record')->getFields('id', array('apply_id' => $apply_ids, 'check_status' => 3));
                $rescord_num = count($record_ids);
                if ($num == $rescord_num) {
                    _model('convert_apply')->update($cid, array('status' => 5));
                }
            }
        }
        if ($record_info) {
            if ($res_name == 'group') {
                $record_id = $record_info['id'];
                $info['group_checker'] = $member_user;
                $info['group_time'] = date('Y-m-d H:i:s', time());
                $info['check_status'] = 3;
                _model('convert_record')->update($record_id, $info);
                $record_ids = _model('convert_record')->getFields('id', array('apply_id' => $apply_ids, 'check_status' => 3));
                $rescord_num = count($record_ids);
                if ($num == $rescord_num) {
                    _model('convert_apply')->update($cid, array('status' => 5));
                }
            }
        }

        
        
        //审核表记录个数
        if($res_name == 'province'){
            $record_num = _model('convert_record')->getTotal(array('apply_id' => $apply_ids, 'check_status ' => array(1,2)));
        }else if($res_name == 'group'){
            $record_list = _model('convert_record')->getList(array('apply_id' => $apply_ids, 'check_status >' => 0));
            foreach ($record_list as $k => $v){
                if($v['check_status'] > 2){
                    $record_num_sure = _model('convert_record')->getTotal(array('apply_id' => $apply_ids, 'check_status ' => array(1,3,4)));
                    $new_num = file_apply_helper::get_number($cid);
                    if($record_num_sure == $new_num){
                        $record_num = $num;
                    }
                    break;
                }else{
                    $record_num = 0;
                }
            }
        }
        //全部审核结束
        if($num == $record_num){
            return array('info' => 'success', 'msg' => "成功");
        }else{
            return array('info' => 'ok', 'msg' => "成功");
        }
    }

    /**
     * 审核短信
     */
    public function get_sms_template()
    {
        $cid = Request::Post('cid', 0);
        $res_name = Request::Post('res_name', '');
        //申请信息
        $convert_info = _model('convert_apply')->read($cid);
        //全部该id下的申请
        $apply_ids = _model('apply')->getFields('id', array('cid' => $cid));
        //申请的审核列表
        $record_list = _model('convert_record')->getList(array('apply_id' => $apply_ids));
        
        //定义省集团审核通过空数组 赋值判断审核详细信息选择模板
        $province_sure_list = $group_sure_list = $province_error_list = $group_error_list = array();
        foreach ($record_list as $k => $v){
            if($v['check_status'] == 1){
                $province_sure_list[] = $v;
            }else if($v['check_status'] == 2){
                $province_error_list[] = $v;
            }else if($v['check_status'] == 3){
                $group_sure_list[] = $v;
            }else if($v['check_status'] == 4){
                $group_error_list[] = $v;
            }
        }
        $apply_time = substr($convert_info['add_time'],6, 4);
        $all_num = $convert_info['business_number'];
        $param2 = '您于'.$apply_time.'提交的'.$all_num.'家门店形象改造申请';
        $param3 = '';
      
        //您于3月15日提交的2家门店形象申请改造，已审批结束，审批结果为全部驳回，登录平台查看审批详情
        
        if($province_sure_list && !$group_sure_list && !$province_error_list && !$group_error_list && count($province_sure_list) == $all_num){
            //省全部通过集团未审核 模板二
            $param3 = '省级领导已全部通过，现已提交至集团领导，待领导审批。可登陆平台查看审批进度。';
        
        }else if($province_sure_list && $province_error_list && !$group_error_list && !$group_sure_list){
            //省审核结束有不通过 移交集团 模板二
            $error_number = count($province_error_list);
            $sure_number = count($province_sure_list);
            $param3 = '经省级领导审批'.$error_number.'家驳回，另'.$sure_number.'家已提交至集团领导，待领导审批。登录平台查看审批详情。';
        }else if($province_error_list && !$group_error_list && !$group_sure_list && !$province_sure_list && count($province_error_list) == $all_num){
            //省全部驳回 模板一
             $param3 = '已审批结束，审批结果为全部驳回。登录平台查看审批详情。';
        }else if($group_sure_list && count($group_sure_list) == $all_num){
            //走到最后 集团全部通过 模板一
            $param3 = '已审批结束，审批结果为全部通过。登录平台查看审批详情。';
        }else if($group_sure_list && $group_error_list  && !$province_error_list ){
            //省全部通过 集团有未通过 模板一
            $error_number = count($group_error_list);
            $sure_number = count($group_sure_list);
            $param3 = '已审批结束，审批结果为'.$group_error_list.'家驳回'.$group_sure_list.'家通过。登录平台查看审批详情。';
        }else if($group_error_list && count($group_error_list) == $all_num){
            $param3 = '已审批结束，审批结果为审批结果为全部驳回。登录平台查看审批详情。';
        }
        
        $content = [
                'param1' => '门店形象改造',
                'param2' => $param2,
                'param3' => $param3,
        ];
        
        //$params['tel']         = '18801235362';
        $params['tel']         = '18813044687';
        $params['content']     = json_encode($content);
        $params['template_id'] = 91551836;
        
        $msg_res = _widget('message')->send_message($params);
        
        if($msg_res){
            return array('msg' => 'ok', 'info' => json_encode($content));
        }else{
            return '网络错误';
        }
        
    }

    /**
     * 审核不通过
     */
    public function approval_no()
    {
        //组审核状态  0 市未提交  10 省未提交 1省待审批 2省成功 3省失败 4集团待审批 5成功 6失败
        //单条审核状态  0 未审核 1 省审核通过 2省审核不通过 3集团通过 4集团不通过
        $cid = Request::Post('cid', 0);
        $member_id = Request::Post('member_id', 0);
        $apply_id = Request::Post('apply_id', 0);
        $res_name = Request::Post('res_name', '');
        $cause = Request::Post('cause', '');
        $time = date('Y-m-d H:i:s', time());
        $member_user = file_apply_helper::get_user_by_memberid($member_id);
        $info = array(
            'apply_id' => $apply_id
        );

        $record_info = _model('convert_record')->read($info);

        //检查是否全部审核完
        $apply_ids = _model('apply')->getFields('id', array('cid' => $cid));
        $num = count($apply_ids);
        
        //没有数据第一次审核 新添加数据
        if (empty($record_info)) {
            if ($res_name == 'province') {
                $info['province_checker'] = $member_user;
                $info['province_time'] = $time;
                $info['check_status'] = 2;
                $info['cause'] = $cause;
                _model('convert_record')->create($info);
                _model('convert_apply')->update($cid, array('status' => 3));
            }

            if ($res_name == 'group') {
                $info['group_checker'] = $member_user;
                $info['group_time'] = $time;
                $info['check_status'] = 4;
                $info['cause'] = $cause;
                _model('convert_record')->create($info);
                _model('convert_apply')->update($cid, array('status' => 6));
            }
        }
        if ($record_info) {
            if ($res_name == 'group') {
                $record_id = $record_info['id'];
                $info['group_checker'] = $member_user;
                $info['group_time'] = date('Y-m-d H:i:s', time());
                $info['check_status'] = 4;
                $info['cause'] = $cause;
                _model('convert_record')->update(array('apply_id' => $apply_id), $info);
                _model('convert_apply')->update($cid, array('status' => 6));
            }
        }
        
        //审核表记录个数
        if($res_name == 'province'){
            $record_num = _model('convert_record')->getTotal(array('apply_id' => $apply_ids, 'check_status ' => array(1,2)));
        }else if($res_name == 'group'){
            $record_list = _model('convert_record')->getList(array('apply_id' => $apply_ids, 'check_status >' => 0));
            foreach ($record_list as $k => $v){
                if($v['check_status'] > 2){
                    $record_num_sure = _model('convert_record')->getTotal(array('apply_id' => $apply_ids, 'check_status ' => array(1,3,4)));
                    $new_num = file_apply_helper::get_number($cid);
                    if($record_num_sure == $new_num){
                        $record_num = $num;
                    }
                    break;
                }else{
                    $record_num = 0;
                }
            }
        }
        //全部审核结束
        if($num == $record_num){
            return array('info' => 'success', 'msg' => "成功");
        }else{
            return array('info' => 'ok', 'msg' => "成功");
        }
    }


    /**
     * 获取市名称
     */
    public function get_city_name()
    {
        $id = Request::Post('cid', 0);
        if (!$id) {
            return false;
        }

        $info = _model('city')->read($id);

        if (!$info) {
            return false;
        }

        return array('msg' => 'ok', 'city_info' => $info);
    }

    /**
     * 获取省名称
     */
    public function get_area_name()
    {
        $id = Request::Post('area_id', 0);
        if (!$id) {
            return false;
        }

        $info = _model('area')->read($id);

        if (!$info) {
            return false;
        }

        return array('msg' => 'ok', 'area_info' => $info);
    }


}