<?php
/**
 * alltosun.com 标签管理 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com

 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宋志宇 (songzy@alltosun.com) $
 * $Date: 2017年10月17日 上午11:41:22 $
 * $Id$
 */
class Action
{
    private $per_page = 20;
    private $member_id  = 0;
    private $member_res_name = '';
    private $member_res_id   = 0;
    private $member_info     = array();
    private $ranks           = 0;

    public function __construct()
    {

        $this->member_id   = member_helper::get_member_id();
        $this->member_info = member_helper::get_member_info($this->member_id);

        if ($this->member_info) {
            $this->member_res_name = $this->member_info['res_name'];
            $this->member_res_id   = $this->member_info['res_id'];
            $this->ranks           = $this->member_info['ranks'];
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

    public function __call($action = '', $params = array())
    {
        //接收数据
        $search_filter = Request::Get('search_filter', array());
        $page            = Request::Get('page_no', 1);
        $hall_title       = Request::Get('hall_title', '');
        $is_export      = Request::Get('is_export', 0);

        //分页，排序
        $order         = ' ORDER BY `id` DESC ';

        $push_list  = $filter = array();

        //标题搜索
        if(!empty($search_filter['tag_type']) && !empty(push_config::$tag_type[$search_filter['tag_type']])) {
            $filter['res_name'] = $search_filter['tag_type'];
        }

        //tag 搜索条件
        if(!empty($search_filter['tag_nickname']) && !empty($filter['res_name'])) {
            $search_filter['tag_nickname'] = trim($search_filter['tag_nickname']);
            if (in_array($filter['res_name'], array('province', 'city', 'area' ))) {
                $filter['res_id'] = _model($filter['res_name'])->getFields('id', array('name LIKE '=> '%'.$search_filter['tag_nickname'].'%'));
            } else if ($filter['res_name'] == 'business_hall') {
                $filter['res_id'] = _model($filter['res_name'])->getFields('id', array('title LIKE '=> '%'.$search_filter['tag_nickname'].'%'));
            } else if ($filter['res_name'] == 'phone_name_version') {
                $filter['tag'] = $search_filter['tag_nickname'];
            } else {
                return '未知的搜索类型';
            }
        }
        if (!$filter) {
            $filter = array( 1 => 1);
        }

        $count= _model('screen_device_tag')->getTotal($filter);

        if ($count) {
            $pager     = new Pager($this->per_page);

            //查询推送标签列表
            $push_list = _model('screen_device_tag')->getList($filter ,$order.$pager->getLimit($page));
            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        //导出
        if ($is_export == 1) {
            $this->export_excel(_model('screen_device_tag')->getList($filter));
        }

        Response::assign('search_filter', $search_filter);
        Response::assign('count', $count);
        Response::assign('push_list', $push_list);
        Response::display('admin/tag/push_tag_list.html');
    }

    public function device_view()
    {

        //接收参数
        $tag_id = Request::get('id');
        $order         = ' ORDER BY `id` DESC ';
        $hall_title        = Request::Get('hall_title', '');
        $search_filter  = Request::Get('search_filter', array());
        $page             = tools_helper::get('page_no', 1);
        $default_filter = _widget('screen')->default_search_filter($this->member_info);
   
        //设备唯一标识
        $search_device_unique_id = tools_helper::get('device_unique_id', '');

        $filter = $default_filter;


        //营业厅权限跳过标题搜索
        if ($this->member_res_name != 'business_hall' && $hall_title) {
            $business_hall_list = _model('business_hall')->getList(array('title' => $hall_title));
            $business_hall_ids = array();
            foreach ($business_hall_list as $k => $v) {
                //非集团管理员并且搜索的营业厅不在本身权限之内则跳过
                if ($this->member_res_name != 'group' && $v["{$this->member_res_name}_id"] != $this->member_res_id) {
                    continue;
                }
                $business_hall_ids[] = $v['id'];
            }

            if (!$business_hall_ids) {
                $business_hall_ids = 0;
            }
            $filter['business_id'] = $business_hall_ids;
        }

     
        //搜索判断
        //省
        if (!empty($search_filter['province_id']) ) {
            $filter['province_id'] = $search_filter['province_id'];
            $province                = array('province_id' => $search_filter['province_id']);
            Response::assign('where1' , $province);
        }
        //市
        if (!empty($search_filter['city_id'])) {
            $filter['city_id'] = $search_filter['city_id'];
            $city                = array('city_id' => $search_filter['city_id']);
            Response::assign('where2' , $city);
        }
        //区
        if (!empty($search_filter['area_id'])) {
            $filter['area_id'] = $search_filter['area_id'];
        }

        if (!empty($search_filter['imei'])) {
            //根据市imei查询screen_click_record 表res_id
            $imei   = _uri('screen_device')->getFields('device_unique_id',array('imei' => $search_filter['imei']));

            if($imei){
                $filter['device_unique_id']= $imei[0];
            }else{
                $filter['device_unique_id']=0;
            }

        }

        

        if ($search_device_unique_id) {
            $filter['device_unique_id'] = $search_device_unique_id;
        }

        if (!$filter) {
            $filter = array(1=>1);
        }

        
        //根据id获取tag_res列表
        $tag_res_list = _model('screen_device_tag_res')->getList(array('tag_id'=>$tag_id));


        //registration设备注册集合
        $registration_list =array();
        foreach ($tag_res_list as $k => $v)
        {
            $registration_list[]=$v['registration_id'];
        }

       //去掉空数据
       $registration_list=array_filter($registration_list);

       //未绑定设备时
       if(empty($registration_list)){
           $registration_list=1;
       }
       
       $filter['registration_id'] =$registration_list;
      
          //设备列表
        $pager  = new Pager($this->per_page);
        $device_list = _model('screen_device')->getList($filter,$order.$pager->getLimit($page));
       
        Response::assign('tag_id', $tag_id);
        Response::assign('pager', $pager);
        Response::assign('device_list', $device_list);
        Response::assign('search_filter', $search_filter);
        Response::assign('device_unique_id', $search_device_unique_id);
        Response::display('admin/tag/device_list.html');
    }
    //删除
    public function delete()
    {
        //get 方式获取id、标签
        $id = Request::get('id');
        $tag = Request::get('tag');

        if (!$id) {
            return array('info'=>'请选择删除的数据');
        }


       //根据screen_device_tag 标签表id 查询 screen_device_tag_res 表 registration_id
       $filter=array(
           "tag_id"=>$id
       );

       $registration_ids   = _model('screen_device_tag_res')->getFields('registration_id',$filter);


        //解绑极光标签、删除screen_device_tag_res 表里相关数据
       $res=push_helper::unbind_tag($registration_ids,$tag);

       if($res){
            _model('screen_device_tag')->delete($id);
            return array('info' => 'ok');
       }
       return array('info' => 'error');
    }


    //导出
    function export_excel($list)
    {
        if (!$list) {
            return '暂无数据';
        }

       foreach ($list as $k=>$v) {
            $info[$k]['res_name'] = $v['res_name'];
            $info[$k]['tag']      = $v['tag'];
            $info[$k]['res_id']   = $v['res_id'];
            $info[$k]['add_time'] = substr($v['add_time'], 0, 10);
        }

        $params['filename'] = '标签列表';
        $params['data']     = $info;
        $params['head']     = array('标签类型', '标签名', '标签值', '添加时间');

        Csv::getCvsObj($params)->export();
    }


    public function add()
    {

        Response::display("admin/tag/add_tag.html");
    }


    public function save()
    {
        //获取表单数据
        $tag       = Request::Post('tag', array());
        $res_title_select =Request::Post('res_title_select',' ');
        $res_title =Request::Post("res_title",' ');
        $flag=Request::Post("flag_type",'');
        //判断空值
        if($flag){

            if(empty($tag['res_id_select'])){
                return "请输入标签值";
            }
        }

        if(!$flag){
            if(empty($tag['res_id'])){
                return "请输入标签值";
            }
            if(empty($tag['res_name'])){
                return "请输入类型名称";
            }
            if($res_title == ''){
                return "请输入标签标题";
            }
        }

        //去掉空数据
        $temp_list=array_filter($tag);
        //检测是什么方式添加 有select 为原有标签添加
       $flage=array_key_exists('res_name_select', $temp_list);

       //添加时间
       $add_time=date('Y-m-d H:i:s',time());
        if($flage){
            $tag_list  = array(
                    'res_name'  => $temp_list['res_name_select'],
                    'res_id'        =>  $temp_list['res_id_select'],
                    'res_title'     =>$res_title_select,
                    'add_time'   =>$add_time
            );
        }else{

            $tag_list  = array(
                    'res_name'  => $temp_list['res_name'],
                    'res_id'        =>  $temp_list['res_id'],
                    'res_title'     =>$res_title,
                    'add_time'   =>$add_time
            );
        }

        if(empty($tag_list['res_title']))
        {
            if($tag_list['res_name']=='business_hall'){
                $tag_list['res_title']='营业厅';
            }
            if($tag_list['res_name']=='city'){
                $tag_list['res_title']='市';
            }
            if($tag_list['res_name']=='area'){
                $tag_list['res_title']='区';
            }
        }
        //生成标签
        $tag_name = substr(md5($tag_list['res_name'].'_'.$tag_list['res_id']),8,16);

        $tag_list['tag']=$tag_name;

        //确保唯一
        $info = _model('screen_device_tag')->read(array('res_name'=>$tag_list['res_name'], 'res_id'=>$tag_list['res_id']));
        if ( $info) {
            return '该标签已存在';
        }
        //在screen_device_tag 增加数据
        $id = _model('screen_device_tag')->create($tag_list);

        if($id){
            $tag_res_list=array(
                    'tag_id'      =>$id,
                    'add_time' =>$add_time
            );
            //在screen_device_tag_res 增加数据
            $tag_res_id = _model('screen_device_tag_res')->create($tag_res_list);
        }

        if ( !$id ) {
            return '添加失败';
        }
         Response::redirect(AnUrl('push/admin'));
    }

    public function add_device()
    {
        $tag_id    = Request::Get('tag_id', 0);
        $tag_title =_uri('screen_device_tag',$tag_id,'res_title');

        Response::assign('tag_id', $tag_id);
        Response::assign('tag_title', $tag_title);
        Response::display("admin/tag/add_device.html");
    }


    //绑定设备
    public function binding_device()
    {
        $tag_id = Request::Post('tag_id', 0);
        $imei    =Request::Post('imei', '');
        $time=date('Y-m-d H:i:s',time());
        $registration_id='';

        $tags = array();
        $tags[]=$tag_id;


        //根据imei获取极光推注册id
        $device_info=_uri('screen_device')->getFields('registration_id', array('imei' => $imei));

        if($device_info){
            $registration_id=$device_info[0];
        }

        $bool=push_helper::binding_tag($registration_id, $tags,  false);

        if($bool){
            //修改设备表
            _model('screen_device')->update(array('registration_id' => $registration_id),array(
                    'update_time' => $time
            ));

            Response::redirect(AnUrl("push/admin/device_view?id={$tag_id}"));

        }
       return array('info'=>'绑定失败');
    }
}