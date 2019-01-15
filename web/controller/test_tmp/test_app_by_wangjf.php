<?php
/**
  * alltosun.com app 测试 test_app_by_wangjf.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年12月26日 下午5:08:54 $
  * $Id$
  */
set_time_limit(0);

class Action
{
    public $records = array();

    //接口基础数据
    public $site_url;
    public $api_version;
    public $api_list;
    public $user_number;
    public $imei            = '1234554321';
    public $mac             = 'af:ss:50:f1:68';
    public $phone_name      = 'MIv2';
    public $phone_version   = 'MIv2 6';
    public $shoppe_id;
    public $registration_id = 'abcdefg1234567';
    public $device_unique_id;

    //营业厅搜索接口
    public $get_business_list_title     = '北京市';
    public $get_business_list_page      = 1;

    //定位接口
    public $get_business_lat            = '40.075615';
    public $get_business_lng            = '116.422398';

    //获取专柜后缀接口
    public $get_series_number_shoppe_brand = '华为';

    //创建专柜接口
    public $create_shoppe_shoppe_name;

    //一键开启接口
    public $device_version;

    //添加wifi
    public $add_wifi_wifi_user_name     = 'wifi_name';
    public $add_wifi_wifi_pwd           = 'wifi_pwd';

    //改价
    public $price                       = 0;

    //轮播内容统计
    public $content_stat_content_id     = 0;
    public $roll_sum                    = 1;

    //吐槽内容
    public $spitslot_content            = '机器吐槽';

    //安卓错误日志内容
    public $error_log_content           = '机器测试error';

    //热更新记录接口参数
    public $update_version_record_mode  = 2;
    public $update_version_record_code  = 100;
    public $update_version_record_info  = 'infoabc';
    public $update_version_record_handlePatchVersion;

    //执行序号
    public $number                      = 0;

    /**
     * 执行入口文件
     */
    public function app_test()
    {
        exit();//关闭测试
        //请求url
        $this -> site_url                       = SITE_URL;
        //设置请求接口版本
        $this -> api_version                    = 3;
        //设置厅渠道编码
        $this -> user_number                    = 1101081002052;
        //设置机型 （设置此参数，则不会执行添加设备接口, 且厅渠道编码重新设置、设备参数重新设置）
        //$this -> device_unique_id               = 'a444d1dae1f7';
        //初始化
        $this -> init();
        //执行
        $this -> app_exec();
        //显示
        $this -> display();
    }

    /**
     * 初始化
     */
    private function init() {
        $this -> device_version                 = ($this -> api_version -1).'.0.0';
        $this -> price                          = rand(1000, 9999);  //改价
        $this -> spitslot_content               = $this -> spitslot_content.rand(100000, 999999);
        $this -> error_log_content              = $this -> error_log_content.rand(100000, 999999);
        $this -> update_version_record_handlePatchVersion = ($this -> api_version -1).'.0.'.rand(1, 9);

        if (!$this->device_unique_id) {
            return true;
        }

        //有设备唯一标识，则根据设备设置相关参数
        $this -> get_device_info_by_device();
    }


    /**
     * 模拟App运行
     */
    private function app_exec ()
    {
        //定位接口
        $this -> get_business();
        //搜索营业厅接口
        $this -> get_business_list();
        //一键开启接口
        $this -> add_device_info();
        //获取设备唯一标识接口
        $this -> get_device_unique_id();
        //更新设备版本号
        $this -> update_version_number();
        //添加wifi接口
        $this -> add_wifi();
        //获取wifi接口
        $this -> get_device_wifi_info();
        //是否绑定专柜
        $this -> is_bind();
        //获取品牌接口
        $this -> get_shoppe_brand();
        //生成专柜号接口
        $this -> get_series_number();
        //创建专柜接口, 暂时不创建
        //$this -> create_shoppe();
        //获取专柜接口
        $this -> get_shoppe_list();
        //绑定专柜， 暂时不不绑定
        $this -> update_shoppe();
        //在线上报
        $this -> online_add();
        //获取轮播内容
        $this -> get_content();
        //轮播上报
        $this -> add_content_stat();
        //轮播内容点击
        $this -> content_click();
        //动作上报
        $this -> add_device_record();
        //更改价格
        $this -> edit_price();
        //根据注册id获取设备列表
        $this -> get_info_by_registration_id();
        //吐槽
        $this -> add_spitslot();
        //获取最新版本
        $this -> get_version_info();
        //检查版本更新的接口
        $this -> diff_version();
        //安卓错误日志记录接口
        $this -> error_log();

        if ($this -> api_version != 3) {
            return false;
        }

////////////////////////////  api v3新增接口  ////////////////////////////
        //是否存在机型宣传图
        $this -> is_exist_type4_v3();

        //热更新记录接口
        $this -> update_version_record_v3();
    }

    /**
     * 执行接口
     */
    public function exec_api($api)
    {

        ++ $this -> number;

        if (!isset($this->api_list[$api])) {
            echo '接口不存在'.$api.'<br>';
        }

        // 生成接口基础数据
        $post_data = api_helper::make_test_base_data();
        // 加密
        $post_data['sign'] = api_helper::encode_sign($post_data);
        if (!empty($this->api_list[$api])) {
            $post_data = array_merge($post_data, $this->api_list[$api]);
        }

        $new_api = $this->site_url.'/'.$api.'?cache=0';
        //执行接口
        $res = an_curl($new_api, $post_data, 0, 0);

        $info = array();
        $info['api_url']  = $api;
        $info['response'] = $res;
        $info['request']  = json_encode($post_data);

        $this->set_records($info);

        return $res;
    }

    /**
     * 设置接口记录
     */
    private function set_records($info)
    {

        $record = array();

        $res = json_decode($info['response'], true);

        $record['number'] = $this -> number;
        $record['code'] = 1000;
        $record['msg']  = 'success';
        $record['api_url']  = $info['api_url'];
        $record['request']  = $info['request'];
        $record['response']  = '';
        $record['result']   = '';

        if (!$res || !isset($res['status']['code'])) {
            $record['code']     = 0;
            $record['msg']      = '请求失败';
            $this -> records[] = $record;
            return true;
        }

        $record['code']     = $res['status']['code'];
        $record['msg']      = $res['status']['message'];

        //返回值
        $record['response'] = json_encode($res);

        //返回result
        $record['result']  = json_encode($res['result']);

        $this -> records[] = $record;

        return true;
    }

    /**
     * 获取设备信息接口
     */
    public function get_device_info_by_device()
    {
        $api = '/screen/api/1/phone/get_device_info';
        $this->api_list[$api] = array(
                'device_unique_id' => $this -> device_unique_id
        );
        $res = $this->exec_api($api);
        $arr = json_decode($res, true);

        if (!empty($arr['result']['user_number'])) {
            $this -> user_number = $arr['result']['user_number'];
            $this -> imei        = $arr['result']['imei'];
            $this -> mac         = $arr['result']['mac'];
            $this -> phone_name  = $arr['result']['phone_name'];
            $this -> phone_version = $arr['result']['phone_version'];
            $this -> registration_id = $arr['result']['registration_id'];
        }

    }

    /**
     * 热更新记录接口
     */
    public function update_version_record_v3()
    {
        $api = '/screen/api/'.$this -> api_version.'/version/update_version_info';
        $this->api_list[$api] = array(
                'mode'       => $this -> update_version_record_mode,
                'code'       => $this -> update_version_record_code,
                'info'       => $this -> update_version_record_info,
                'handlePatchVersion' => $this -> update_version_record_handlePatchVersion,
                'device_unique_id' => $this -> device_unique_id
        );

        $this->exec_api($api);
    }

    /**
     * 机型是否存在机型宣传图
     */
    public function is_exist_type4_v3()
    {
        $api = '/screen/api/'.$this -> api_version.'/content/is_exist_type4';

        $this->api_list[$api] = array(
                'device_unique_id'       => $this -> device_unique_id,
        );
        $this->exec_api($api);
    }

    /**
     * 更新设备当前安装的版本号
     */
    public function update_version_number()
    {
        $api = '/screen/api/'.$this -> api_version.'/phone/update_version';

        $this->api_list[$api] = array(
                'device_unique_id'       => $this -> device_unique_id,
                'version_no'             => $this -> device_version,
        );

        $this->exec_api($api);
    }

    /**
     * 根据设备信息获取设备标识
     */
    public function get_device_unique_id()
    {
        $api = '/screen/api/'.$this -> api_version.'/phone/get_device_unique_id';

        $this->api_list[$api] = array(
                'phone_imei'       => $this -> imei,
                'phone_mac'  => $this -> mac,
        );

        $this->exec_api($api);
    }

    /**
     * 安卓的错误日志记录接口
     */
    public function error_log()
    {
        $api = '/screen/api/'.$this -> api_version.'/error_log';
        $this->api_list[$api] = array(
                'user_number'       => $this -> user_number,
                'device_unique_id'  => $this -> device_unique_id,
                'date'              => date('Y-m-d H:i:s'),
                'content'           => $this -> error_log_content
        );

        $this->exec_api($api);
    }

    /**
     * 检查版本更新的接口
     */
    public function diff_version()
    {
        $api = '/screen/api/'.$this -> api_version.'/version';

        $this->api_list[$api] = array(
                'version_no'    => $this -> device_version,
        );

        $this->exec_api($api);
    }

    /**
     * 获取最新发布的版本接口
     */
    public function get_version_info()
    {
        $api = '/screen/api/'.$this -> api_version.'/version/get_version_info';

        $this->api_list[$api] = array(
                'version_no'    => $this -> device_version,
        );

        $this->exec_api($api);
    }

    /**
     * 轮播上报接口
     */
    public function add_content_stat()
    {
        $api = '/screen/api/'.$this -> api_version.'/content/content_stat/add_content_stat';

        if ($this -> api_version == 2) {
            $info = '[{"res_id":"'.$this->content_stat_content_id.'","res_name":"photo", "click_time":"'.date('Y-m-d H:i:s').'"}, {"res_id":"'.$this->content_stat_content_id.'","res_name":"photo", "click_time":"'.date('Y-m-d H:i:s').'"}]';
        } else if ($this -> api_version == 3) {
            $info = '[{"content_id":"'.$this->content_stat_content_id.'","id":1,"roll_sum":'.$this->roll_sum.',"time":'.(time()+rand(1, 100)).'},{"content_id":"'.$this->content_stat_content_id.'","id":2,"roll_sum":'.$this -> roll_sum.',"time":'.(time()+rand(1, 100)).'}, {"content_id":"8","id":54,"roll_sum":6,"time":'.(time()+rand(1, 100)).'}]';
        }

        $this->api_list[$api] = array(
                'device_unique_id' => $this -> device_unique_id,
                'user_number'      => $this -> user_number,
                'info'             => $info,
        );
        $this->exec_api($api);
    }

    /**
     * 吐槽接口
     */
    public function add_spitslot()
    {
        $api = '/screen/api/'.$this -> api_version.'/spitslot/add_spitslot';

        $this->api_list[$api] = array(
                'device_unique_id'       => $this -> device_unique_id,
                'user_number'            => $this -> user_number,
                'content'                => $this -> spitslot_content
        );
        $this->exec_api($api);
    }

    /**
     * 根据极光推注册id获取设备列表
     */
    public function get_info_by_registration_id()
    {
        $api = '/screen/api/'.$this -> api_version.'/registration/get_info';

        $this->api_list[$api] = array(
                'registration_id'       => $this -> registration_id,
        );
        $this->exec_api($api);
    }

    /**
     * 改价接口
     */
    public function edit_price()
    {
        $api = '/screen/api/'.$this -> api_version.'/price/edit_price';

        $this->api_list[$api] = array(
                'device_unique_id'      => $this -> device_unique_id,
                'price'                 => $this -> price,
        );
        $this->exec_api($api);
    }

    /**
     * 动作接口
     */
    public function add_device_record()
    {
        $api = '/screen/api/'.$this -> api_version.'/phone/record/add_device_record';
        //2版本
        if ($this -> api_version == 2) {
            $time = time() - 24 * 3600;
            $time3 = $time + rand(3, 20);
            $info = '[{"device_unique_id":"'.$this -> device_unique_id.'","experience_time":'.$time.',"phone_mac":"'.$this->mac.'","type":1},{"device_unique_id":"'.$this -> device_unique_id.'","experience_time":'.$time3.',"phone_mac":"'.$this->mac.'","type":2}]';
        //3版本
        } else if ($this -> api_version == 3) {
            $time = time();
            $ex1 = rand(1, 10);
            $ex2 = rand(1, 10);
            $ex3 = rand(1, 10);
            $info = '[{"device_unique_id":"'.$this -> device_unique_id.'","experience_time":'.$ex1.',"add_time":"'.$time.'"},{"device_unique_id":"c40bcb1d0666","experience_time":'.$ex2.',"add_time":"'.$time.'"}]';
        }

        $this->api_list[$api] = array(
                'user_number'      => $this -> user_number,
                'info'             => $info,
        );
        $this->exec_api($api);
    }

    /**
     * 内容点击
     */
    public function content_click()
    {
        $api = '/screen/api/'.$this -> api_version.'/phone/click/add_click';
        //定位营业厅接口， 必须参数：lat、 lng
        $this->api_list[$api] = array(
                'device_unique_id' => $this -> device_unique_id,
                'user_number'      => $this -> user_number,
                'res_id'           => $this -> content_stat_content_id
        );
        $this->exec_api($api);
    }

    /**
     * 获取轮播内容
     */
    public function get_content()
    {
        $api = '/screen/api/'.$this -> api_version.'/content/get_content';
        //定位营业厅接口， 必须参数：lat、 lng
        $this->api_list[$api] = array(
                'device_unique_id' => $this -> device_unique_id,
                'user_number'        => $this -> user_number
        );
        $res = $this->exec_api($api);

        $arr = json_decode($res, true);
        //设置统计接口内容id
        if (isset($arr['result']['0']['id']) && !$this -> content_stat_content_id) {
            $this -> content_stat_content_id = $arr['result']['0']['id'];
        }
    }

    /**
     * 在线上报接口
     */
    public function online_add()
    {
        $api = '/screen/api/'.$this -> api_version.'/online/add';
        //定位营业厅接口， 必须参数：lat、 lng
        $this->api_list[$api] = array(
                'device_unique_id' => $this -> device_unique_id,
                'user_number'        => $this -> user_number
        );
        $this->exec_api($api);
    }

    /**
     * 绑定专柜
     */
    public function update_shoppe()
    {
        if (!$this -> shoppe_id) {
            $this -> shoppe_id = 1;
        }

        $api = '/screen/api/'.$this -> api_version.'/shoppe/phone_shoppe/update_shoppe';
        //定位营业厅接口， 必须参数：lat、 lng
        $this->api_list[$api] = array(
                'device_unique_id' => $this -> device_unique_id,
                'shoppe_id'        => $this -> shoppe_id
        );
        $this->exec_api($api);
    }

    /**
     * 是否绑定专柜
     */
    public function is_bind()
    {
        $api = '/screen/api/'.$this -> api_version.'/shoppe/phone_shoppe/is_bind';

        $this->api_list[$api] = array(
                'device_unique_id' => $this -> device_unique_id
        );
        $this->exec_api($api);
    }

    /**
     * 获取品牌
     */
    public function get_shoppe_brand()
    {
        $api = '/screen/api/'.$this -> api_version.'/shoppe/get_shoppe_brand';

        $this->api_list[$api] = array(
        );
        $res = $this->exec_api($api);
        $arr = json_decode($res, true);

        if (isset($arr['result'][0]) && !$this -> get_series_number_shoppe_brand) {
            $this -> get_series_number_shoppe_brand = $arr['result'][0];
        }

    }

    /**
     * 创建专柜接口
     */
    public function create_shoppe()
    {
        $api = '/screen/api/'.$this -> api_version.'/shoppe/create_shoppe';
        //定位营业厅接口， 必须参数：lat、 lng
        $this->api_list[$api] = array(
                'user_number'   => $this->user_number,
                'shoppe_brand'  => $this->get_series_number_shoppe_brand,
                'shoppe_name'   => $this->create_shoppe_shoppe_name
        );

        $this->exec_api($api);

    }

    /**
     * 专柜生成后缀接口
     */
    public function get_series_number()
    {
        $api = '/screen/api/'.$this -> api_version.'/shoppe/get_series_number';
        //定位营业厅接口， 必须参数：lat、 lng
        $this->api_list[$api] = array(
                'user_number'   => $this->user_number,
                'shoppe_brand'  => $this->get_series_number_shoppe_brand
        );
        $res = $this->exec_api($api);
        $arr = json_decode($res, true);
        if (isset($arr['result']['shoppe_name']) && !$this -> create_shoppe_shoppe_name) {
            $this -> create_shoppe_shoppe_name = $arr['result']['shoppe_name'];
        }

    }

    /**
     * 定位接口
     */
    public function get_business()
    {
        $api = '/screen/api/'.$this -> api_version.'/business/get_business';
        //定位营业厅接口， 必须参数：lat、 lng
        $this->api_list[$api] = array(
                'lat' => $this->get_business_lat,
                'lng' => $this->get_business_lng
        );

        $res = $this->exec_api($api);
        //p($res);exit;
        $arr = json_decode($res, true);
        if (isset($arr['result']['0']['user_number']) && !$this->user_number) {
            $this -> user_number = $arr['result']['0']['user_number'];
        }

    }

    /**
     * 搜索营业厅接口
     */
    public function get_business_list()
    {

        $api = '/screen/api/'.$this -> api_version.'/business/get_business_list';
        //定位营业厅接口， 必须参数：lat、 lng
        $this->api_list[$api] = array(
                'title' => $this->get_business_list_title,
                'page'  => $this->get_business_list_page
        );

        $res = $this->exec_api($api);
        $arr = json_decode($res, true);
        if (isset($arr['result']['data']['0']['user_number']) && !$this->user_number) {
            $this -> user_number = $arr['result']['data']['0']['user_number'];
        }
    }

    /**
     * 获取专柜列表接口
     */
    public function get_shoppe_list()
    {
        $api = '/screen/api/'.$this -> api_version.'/shoppe/get_shoppe_list';
         //获取专柜接口，必须参数：user_number
        $this->api_list[$api] = array(
                'user_number' => $this->user_number
        );

        $res = $this->exec_api($api);
        $arr = json_decode($res, true);
        if (isset($arr['result']['0']['shoppe_name'])) {
            if (!$this -> create_shoppe_shoppe_name) {
                $this -> create_shoppe_shoppe_name = $arr['result']['0']['shoppe_name'];
            }
            if (!$this -> shoppe_id) {
                $this -> shoppe_id                 = $arr['result']['0']['shoppe_id'];
            }
        }
    }

    /**
     * 添加设备接口
     */
    public function add_device_info()
    {
        $api = '/screen/api/'.$this -> api_version.'/phone/add_device_info';

        //存在设备唯一id
        if ($this -> device_unique_id) {
            return false;
        }

        //添加设备接口
        $this->api_list[$api] = array(
                'user_number' => $this -> user_number,
                'phone_imei'  => $this -> imei,
                'phone_name'  => $this -> phone_name,
                'phone_version' => $this -> phone_version,
                'phone_mac'   => $this -> mac,
                'shoppe_id'   => $this -> shoppe_id,
                'registration_id' => $this -> registration_id,

        );

        $res = $this->exec_api($api);

        $arr = json_decode($res, true);

        if (isset($arr['result']['device_unique_id'])) {
            $this -> device_unique_id = $arr['result']['device_unique_id'];
        }
    }

    /**
     * 添加wifi接口
     */
    public function add_wifi()
    {
        $api = '/screen/api/'.$this -> api_version.'/wifi/add_wifi';
        //添加设备接口
        $this->api_list[$api] = array(
                'user_number' => $this -> user_number,
                'device_unique_id' => $this -> device_unique_id,
                'wifi_user_name'   => $this -> add_wifi_wifi_user_name,
                'wifi_pwd'          => $this -> add_wifi_wifi_pwd
        );

        $this->exec_api($api);

    }

    /**
     * 获取wifi接口
     */
    public function get_device_wifi_info()
    {
        $api = '/screen/api/'.$this -> api_version.'/wifi/get_device_wifi_info';
        //获取wifi接口
        $this->api_list[$api] = array(
                'user_number' => $this -> user_number,
        );

        $this->exec_api($api);
    }


    private function display()
    {

        $css = "
        <style>
            table {
                table-layout: fixed;
                width: 98% border:0px;
                margin: 0px;
                font-family : 微软雅黑,宋体;
                font-size : 0.5em;
                border: 1px solid #ccc;
            }

            tr td {
                text-overflow: ellipsis; /* for IE */
                -moz-text-overflow: ellipsis; /* for Firefox,mozilla */
                overflow: hidden;
                white-space: nowrap;

                text-align: left
            }
        </style>";

        $html = $css.'<table border="1" cellspacing="0" cellpadding="5" width="150%">'.$this -> join_table_hander();
        foreach ($this -> records as $k => $v) {
            $html.= $this -> join_table_content($v);
        }
        $html .= '</table>';
        echo $html;
        exit();
    }

    /**
     * 拼接表头
     */
    private function join_table_hander()
    {
        return  "<tr>
                    <th width='3%'>序号</th>
                    <th width='3%'>接口版本</th>
                    <th width='20%'>接口地址</th>
                    <th width='20%'>请求值</th>
                    <th width='30%'>返回值</th>
                    <th width='30%'>返回值result</th>
                    <th width='5%'>返回码</th>
                    <th width='20%'>消息</th>
                <tr> ";
    }

    /**
     * 拼接表内容
     */
    private function join_table_content($info)
    {
        if ($info['code'] == 0) {
            $html = '<tr style="background-color:red">';
        } else if ($info['code'] != 1000) {
            $html = '<tr style="background-color:yellow">';
        } else {
            $html = '<tr>';
        }

        $html .= "
        <td title='{$info['number']}'>{$info['number']}</td>
        <td title='{$this->api_version}'>{$this->api_version}</td>
        <td title='{$info['api_url']}'>{$info['api_url']}</td>
        <td title='{$info['request']}'>{$info['request']}</td>
        <td title='{$info['response']}'>{$info['response']}</td>
        <td title='{$info['result']}}'>{$info['result']}</td>
        <td title='{$info['code']}'>{$info['code']}</td>
        <td title='{$info['msg']}'>{$info['msg']}</td>
        <tr>";
        return $html;
    }



}