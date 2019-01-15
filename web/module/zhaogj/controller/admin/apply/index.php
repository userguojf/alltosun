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
 * 2018年5月2日: 2016-7-26 下午3:05:10
 * Id
 */
class Action
{
    private $per_page =5 ;
    private $member_id  = 0;
    private $res_id  = 0;
    private $res_name  = '';
    private $ranks      = 0;
    private $time   = '';
   
    public function __construct()
    {
        $this->time        = date('Y-m-d H:i:s',time());
        $this->member_id   = member_helper::get_member_id();
        $member_info = member_helper::get_member_info($this->member_id);

        if ($member_info) {
            $this->res_name = $member_info['res_name'];
            $this->res_id   = $member_info['res_id'];
            $this->ranks           = $member_info['ranks'];
            Response::assign('res_name', $this->res_name);
            Response::assign('res_id', $this->res_id);
        } else {
            return '您无权访问此页面';
        }

        Response::assign('curr_member_ranks', $this->ranks);
        Response::assign('res_name', $this->res_name);
        Response::assign('member_id', $this->member_id);
        Response::assign('member_info', $member_info);
    }

    public function __call($action = '' , $param = array())
    {

//         _model('apply')->delete(array('1'=>1));
//         _model('convert_apply')->delete(array('1'=>1));
//         _model('apply_plan_res')->delete(array('1'=>1));
//          _model('convert_record')->delete(array('1'=>1));

        $search_filter = Request::get('search_filter' ,array());
        $search_type    = tools_helper::get('search_type', 'apply');
        $order = ' ORDER BY `add_time` DESC ';
        $new_num = 0;
        //status 审批状态 0 未提交  1待审批 2审批中 3审批通过 4审批拒绝
        $filter = $list = array();
        //权限
        if($this->res_name == 'province'){
            $filter['province_id'] = $this->res_id;
            $filter['status != '] = 0;
            $new_num = _model('convert_apply')->getTotal(array('status' => 1));
        }
        if($this->res_name == 'city'){
            $filter['city_id'] = $this->res_id;
        }
        
        if($this->res_name == 'group'){
//             $filter['status >'] = 1;
            $filter['status ='] = array(2,4,5,6);
            $num_list = _model('convert_apply')->getAll("SELECT COUNT(*) as num FROM `convert_apply` WHERE `status` = 2 or `status` = 4");
            $new_num = $num_list['0']['num'];
        }
       
        $count = _model('convert_apply')->getTotal($filter, $order);
        
        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
        
            Response::assign('count', $count);
            $list = _model('convert_apply')->getList($filter, $order.$pager->getLimit());
        }
        
        Response::assign('search_type', $search_type);
        Response::assign('list', $list);
        Response::assign('new_num', $new_num);
        Response::display('admin/apply/list_apply.html');
    }

 
    /**
     * 添加页面
     */
    public function add()
    {

        $c_id = 0;
        if($this->res_name == 'province'){
            Response::assign('p_id', $this->res_id);
            Response::assign('c_id', $c_id);
        }
        if($this->res_name == 'city'){
            $business_info  = _model('business_hall')->read(array('city_id'=>$this->res_id));
            $p_id = $business_info['province_id'];
            Response::assign('c_id', $this->res_id);
            Response::assign('p_id', $p_id);
        }
        Response::display('admin/apply/apply_add.html');
    }
    
    /**
     * 进入详情页
     */
    public function detail_apply()
    {
    
        $cid     = Request::Get('cid', 0);
        $apply_id     = Request::Get('apply_id', 0);
        $order = ' ORDER BY `create_time` DESC ';
        $filter_tmp = array('cid'=>$cid);
        $filter= array('cid'=>$cid);
        $num_list = [];
        if($apply_id){
            $filter = array(
                    'cid'=>$cid,
                    'id'=>$apply_id
                    
            );
        }
        $convert_apply_info = _model('convert_apply')->read($cid);
        $apply_info = _model('apply')->read($filter,$order);
        $apply_list = _model('apply')->getList($filter_tmp,$order);
        //关系数据
        $plan_info = _model('apply_plan_res')->getList(array('apply_id'=>$apply_info['id']));
        
        
        $plan_res_list = $plan_type = [];
        //根据存在的类型 整合关系表图片数据
        foreach ($plan_info as $k=> $v){
            $plan_type[$k] = $v['plan_type'];
            if($v['plan_type'] == 0){
                $plan_res_list['mentou'] = $v;
            }else if($v['plan_type'] == 1){
                $plan_res_list['rumen'] = $v;
            }else if($v['plan_type'] == 2){
                $plan_res_list['yewu'] = $v;
            }else if($v['plan_type'] == 3){
                $plan_res_list['zhongduan'] = $v;
            }else if($v['plan_type'] == 4){
                $plan_res_list['jiaofu'] = $v;
            }
        }
        
       
        Response::assign('plan_res_list', $plan_res_list);
        Response::assign('plan_type', $plan_type);
        Response::assign('apply_info', $apply_info);
        Response::assign('apply_list', $apply_list);
        Response::assign('convert_info', $convert_apply_info);
        Response::display('admin/apply/detail_apply.html');
    }
    
    

    public function save()
    {
        $id     = Request::Post('id', 0);
        //单挑申请id
        $aid     = Request::Post('apply_id', 0);
        $flag_id     = Request::Post('flag', 0);//1 提交 0草稿
        $info= Request::Post('info', array());
        $info['create_time'] = $this->time;
        $info['member_id'] = $this->member_id;
        $info['res_id'] = $this->res_id;
        $info['res_name'] = $this->res_name;
        // apply status 申请状态 10 市未提交 13市提交 20省未提交  14 省提交 
        //convert_apply status 审批状态 0 市未提交  10 省未提交 1省待审批 2省成功 3省失败 4集团待审批 5成功 6失败
        //分区类型0 门头 1入门 2业务办理 3终端体验 4交付区
        //组状态
        $status = 0;
       
        if($this->res_name == 'city'){
            $status = 0;
            if($flag_id){
                $status = 1;
            }
        }else if($this->res_name == 'province'){
            $status = 10;
            if($flag_id){
                $status = 4;
            }
        }
        //根据权限确定初始状态
       if($this->res_name =='province'){
           if($flag_id){
               $apply_status = 14;
           }else{
               $apply_status = 20;
           }
       }else if($this->res_name == 'city'){
           if($flag_id){
               $apply_status = 13;
           }else{
               $apply_status = 10;
           }
       }
      
        $convert_info = array(
               'member_id' => $info['member_id'],
               'province_id' => $info['province_id'],
               'city_id' => $info['city_id'],
               'area_id' => $info['area_id'],
               'status' => $status,
               'add_time' => $info['create_time'],
       );
        //无id第一条数据
        if(!$id){
            //第一次
            $convert_info['business_number'] = 1;
            //添加主表信息
            $convert_id = _model('convert_apply')->create($convert_info);
        }else{
            $convert_id = $id;
        }

        //添加前先查出又几条数据
        $num = _model('apply')->getTotal(array('cid'=>$convert_id));
        
        if(!$convert_id){
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
             
            $link = upload_file($plan_link_files,false, 'focus');
            //生成缩略图
            _widget('screen_content')->make_thumb($link);
            //主平面图
            $info['link'] = $link;
        }
        
        //存在是修改  操作单挑申请主数据
        if($aid){
             _model('apply')->update($aid,$info);
             $apply_id = $aid;
        }else{
            //返回申请id
            $apply_id = _model('apply')->create($info);
        }
 
        //存储区域图片  0 门头 1入门 2业务办理 3终端体验 4交付区
        $plan_res = array(
                'apply_id' => $apply_id,
        );
        //门头
        if(!empty($_FILES['plan_mentou']['tmp_name'][0])){
            $plan_mentou_files = file_apply_helper::file_info($_FILES['plan_mentou']);
            $plan_link = upload_file($plan_mentou_files,false, 'focus');
            //生成缩略图
            _widget('screen_content')->make_thumb($link);
            $plan_res['plan_type'] = 0;
            $plan_res['link'] = $plan_link;
            if($aid){
                _model('apply_plan_res')->update(array('apply_id' => $aid),$plan_res);
            }else{
                _model('apply_plan_res')->create($plan_res);
            }
        }
        
        //1入门
        if(!empty($_FILES['plan_rumen']['tmp_name'][0])){
            $plan_mentou_files = file_apply_helper::file_info($_FILES['plan_rumen']);
            $plan_link = upload_file($plan_mentou_files,false, 'focus');
            //生成缩略图
            _widget('screen_content')->make_thumb($link);
            $plan_res['plan_type'] = 1;
            $plan_res['link'] = $plan_link;
            if($aid){
                _model('apply_plan_res')->update(array('apply_id' => $aid),$plan_res);
            }else{
                _model('apply_plan_res')->create($plan_res);
            }
        }
        
        //2业务办理
        if(!empty($_FILES['plan_yewu']['tmp_name'][0])){
            $plan_mentou_files = file_apply_helper::file_info($_FILES['plan_yewu']);
            $plan_link = upload_file($plan_mentou_files,false, 'focus');
            //生成缩略图
            _widget('screen_content')->make_thumb($link);
            $plan_res['plan_type'] = 2;
            $plan_res['link'] = $plan_link;
             if($aid){
                    _model('apply_plan_res')->update(array('apply_id' => $aid),$plan_res);
                }else{
                    _model('apply_plan_res')->create($plan_res);
                }
        }
        
        //3终端体验
        if(!empty($_FILES['plan_zhongduan']['tmp_name'][0])){
            $plan_mentou_files = file_apply_helper::file_info($_FILES['plan_zhongduan']);
            $plan_link = upload_file($plan_mentou_files,false, 'focus');
            //生成缩略图
            _widget('screen_content')->make_thumb($link);
            $plan_res['plan_type'] = 3;
            $plan_res['link'] = $plan_link;
            if($aid){
                _model('apply_plan_res')->update(array('apply_id' => $aid),$plan_res);
            }else{
                _model('apply_plan_res')->create($plan_res);
            }
        }
        
        //4交付区
        if(!empty($_FILES['plan_jiaofu']['tmp_name'][0])){
            $plan_mentou_files = file_apply_helper::file_info($_FILES['plan_jiaofu']);
            $plan_link = upload_file($plan_mentou_files,false, 'focus');
            //生成缩略图
            _widget('screen_content')->make_thumb($link);
            $plan_res['plan_type'] = 4;
            $plan_res['link'] = $plan_link;
            if($aid){
                _model('apply_plan_res')->update(array('apply_id' => $aid),$plan_res);
            }else{
                _model('apply_plan_res')->create($plan_res);
            }
        }
        
        //存在是单挑数据修改 主表数量不加 否则数量加一
        if($aid){
            _model('convert_apply')->update($convert_id,array('status'=>$status));
        }else{
            $business_number = $num +1;
            _model('convert_apply')->update($convert_id, array('business_number'=>$business_number,'status'=>$status));
        }
        
        //所有cid 下数据更改状态
        _model('apply')->update(array('cid'=>$convert_id), array('status'=>$apply_status));
        
        
        
       Response::redirect(AnUrl('file_apply/admin/apply'));
    }
    
    
    public function load_apply()
    {
        $id     = Request::Get('id', 0);
        $cid    = Request::Get('cid', 0);
        $city_id   = 0;
        $index_id     = Request::Get('index_id', 0);  //标识位是否是主页面进来
        $order = ' ORDER BY `create_time` DESC ';
        $filter = array('cid'=>$cid);
        $num_list = [];
        if($this->res_name == 'province'){
            Response::assign('p_id', $this->res_id);
            Response::assign('c_id', $city_id);
        }
        if($this->res_name == 'city'){
            $business_info  = _model('business_hall')->read(array('city_id'=>$this->res_id));
            $p_id = $business_info['province_id'];
            Response::assign('c_id', $this->res_id);
            Response::assign('p_id', $p_id);
        }
        
        //申请信息条数
        $apply_list = _model('apply')->getList($filter,$order);
        $num = count($apply_list);
        
        //如果主页面进来 默认第一个数据id为 申请id
        if($index_id){
            $id = $apply_list[0]['id'];
        }
        $info = _model('apply')->read($id);
        
        $plan_info = _model('apply_plan_res')->getList(array('apply_id'=>$info['id']));
        
        foreach ($apply_list as $k => $v){
            $num_list[$k]['id'] = $v['id'];
            $num_list[$k]['cid'] = $v['cid'];
        }
        
        //存在的类型
        foreach ($plan_info as $k=> $v){
            $plan_type[$k] = $v['plan_type'];
        }
        
        //整合关系表图片数据
        $plan_res_list = [];
        foreach ($plan_info as $k => $v){
            if($v['plan_type'] == 0){
                $plan_res_list['mentou'] = $v;
            }else if($v['plan_type'] == 1){
                $plan_res_list['rumen'] = $v;
            }else if($v['plan_type'] == 2){
                $plan_res_list['yewu'] = $v;
            }else if($v['plan_type'] == 3){
                $plan_res_list['zhongduan'] = $v;
            }else if($v['plan_type'] == 4){
                $plan_res_list['jiaofu'] = $v;
            }
        }

        Response::assign('num', $num);
        Response::assign('index_id', $index_id);
        Response::assign('info', $info);
        Response::assign('apply_id', $info['id']);
        Response::assign('num_list', $num_list);
        Response::assign('plan_info', $plan_info);
        Response::assign('plan_type', $plan_type);
        Response::assign('plan_res_list', $plan_res_list);
        Response::display('admin/apply/apply_added.html');
        
    }

    
    
    /**
     * 删除只有一条申请的信息
     */
    public function delete_apply()
    {
    
        $id     = Request::Post('id', 0);
        $cid     = Request::Post('cid', 0);
        $count_list = _model('apply')->getList(array('cid'=>$cid));
        $num = count($count_list);
        //删除关系表
        _model('apply_plan_res')->delete(array('apply_id'=>$id));
        //删除申请信息
        _model('apply')->delete(array('id'=>$id));
        
        _model('convert_apply')->delete(array('id'=>$cid));
        return array('info' => 'clear','msg' =>"成功");
    }
    
    
    /**
     * 删除有多条数据的信息
     */
    public function apply_delete()
    {
        $id     = Request::Get('id', 0);
        $cid     = Request::Get('cid', 0);
        $c_id     = 0;
        $order = ' ORDER BY `create_time` DESC ';
        $filter = array('cid' => $cid);
        $count_list = _model('apply')->getList(array('cid'=>$cid));
        $num = count($count_list);
      
        //删除关系表
        _model('apply_plan_res')->delete(array('apply_id'=>$id));
        //删除申请信息
        _model('apply')->delete(array('id'=>$id));
                 
        _model('convert_apply')->update($cid, array('business_number'=>$num-1));
        
//         $convert_info = _model('convert_apply')->read($cid);
        $apply_list = _model('apply')->getList($filter,$order);
        $info = $apply_list[0];
        $num_list = array();
        foreach ($apply_list as $k => $v){
            $num_list[$k]['id'] = $v['id'];
            $num_list[$k]['cid'] = $v['cid'];
        }
        
        if($this->res_name == 'province'){
            Response::assign('p_id', $this->res_id);
            Response::assign('c_id', $c_id);
        }
        if($this->res_name == 'city'){
            $business_info  = _model('business_hall')->read(array('city_id'=>$this->res_id));
            $p_id = $business_info['province_id'];
            Response::assign('c_id', $this->res_id);
            Response::assign('p_id', $p_id);
        }
        
        $plan_info = _model('apply_plan_res')->getList(array('apply_id'=>$info['id']));
        
        foreach ($apply_list as $k => $v){
            $num_list[$k]['id'] = $v['id'];
            $num_list[$k]['cid'] = $v['cid'];
        }
        
        //存在的类型
        foreach ($plan_info as $k=> $v){
            $plan_type[$k] = $v['plan_type'];
        }
        
        //整合关系表图片数据
        $plan_res_list = [];
        foreach ($plan_info as $k => $v){
            if($v['plan_type'] == 0){
                $plan_res_list['mentou'] = $v;
            }else if($v['plan_type'] == 1){
                $plan_res_list['rumen'] = $v;
            }else if($v['plan_type'] == 2){
                $plan_res_list['yewu'] = $v;
            }else if($v['plan_type'] == 3){
                $plan_res_list['zhongduan'] = $v;
            }else if($v['plan_type'] == 4){
                $plan_res_list['jiaofu'] = $v;
            }
        }
        
        Response::assign('info', $info);
        Response::assign('apply_id', $info['id']);
        Response::assign('plan_info', $plan_info);
        Response::assign('plan_type', $plan_type);
        Response::assign('plan_res_list', $plan_res_list);
        Response::assign('num', $num-1);
        Response::assign('num_list', $num_list);
        Response::display('admin/apply/apply_added.html');
    }
    
    
    
}