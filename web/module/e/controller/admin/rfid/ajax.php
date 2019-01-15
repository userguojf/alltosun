<?php
/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-5-28 下午5:10:59 $
 * $Id$
 */

//config
include MODULE_PATH.'/rfid/server/config.php';
//放缓存
include MODULE_PATH.'/rfid/server/lib/RedisCache.php';
//清缓存
include MODULE_PATH.'/rfid/server/src/secret_helper.php';

class Action
{
    private $member_id   = NULL;
    private $member_info = NULL;
    private $business_hall_info = NULL;

    public function __construct()
    {
        $this->member_id    = member_helper::get_member_id();
        $this->member_info  = member_helper::get_member_info($this->member_id);

        $this->business_hall_info = _model('business_hall')->read(array('user_number' => $this->member_info['member_user']));

    }

    public function phone_save()
    {
        $info = array();

        $res_id           = tools_helper::post('resId', 0);
        $info['shoppe_id'] = tools_helper::post('shoppe_id', 0);
        $info['label_id'] = tools_helper::post('label_id', '');
        $info['name']     = tools_helper::post('name', '');
        $info['version']  = tools_helper::post('version', '');
        $info['color']    = tools_helper::post('color', '');
        $info['imei']     = tools_helper::post('imei', '');

        if (!$info['label_id']) {
            return array('msg' => 'no', 'data' => '非法操作，请填写标签ID');
        }

        /**
         * 添加标签标签ID唯一判断 因为修改是不能改标签
         */
        if (!$res_id) {
            $is_exist_info = _model('rfid_label')->read(array('label_id' => $info['label_id']));

            if ($is_exist_info) {
                return array('msg' => 'no', 'data' => '标签ID已经存在');
            }
        }

        if (!$info['shoppe_id']) {
            return array('msg' => 'no', 'data' => '请完善柜台信息');
        }

        if (!$info['name']) {
            return array('msg' => 'no', 'data' => '选择手机品牌');
        }

        if (!$info['version']) {
            return array('msg' => 'no', 'data' => '请选择手机型号');
        }

        if (!$info['color']) {
            return array('msg' => 'no', 'data' => '请选择手机颜色');
        }

        if (!$info['imei']) {
            return array('msg' => 'no', 'data' => '请填写IMEI末六位');
        }

        if (!is_int(intval($info['imei'])) || strlen($info['imei']) != 6) {
            return array('msg' => 'no', 'data' => '请填写IMEI末六位数字');
        }

        if (!$this->business_hall_info) {
            return array('msg' => 'no', 'data' => '请用营业厅账号登录添加');
        }

        //集团和营业厅的区别
        if ($this->member_info['member_user'] != 'admin') {
            $info['province_id'] = $this->business_hall_info['province_id'];
            $info['city_id']     = $this->business_hall_info['city_id'];
            $info['area_id']     = $this->business_hall_info['area_id'];
            $info['business_hall_id'] = $this->business_hall_info['id'];

        } else {

            $info['province_id']      = 0;
            $info['city_id']          = 0;
            $info['area_id']          = 0;
            $info['business_hall_id'] = 0;
        }
// p($info['imei']);exit();
        //自定义手机信息
        $phone_info = _model('rfid_phone')->read(array('name' => $info['name'],'version' => $info['version'],'color'=>$info['color'] ));

        if (!$phone_info) {
            $info['phone_id'] =  _model('rfid_phone')->create(array('name' => $info['name'],'version' => $info['version'],'color'=>$info['color'] ));
        } else {
            $info['phone_id'] = $phone_info['id'];
        }

        if ($res_id) {
            //缓存
            secret_helper::update_secret($info['label_id']);

            _model('rfid_label')->update($res_id , $info);
        } else {
            $param = array(
                    'type'          => 'create',
                    'user_number'   => $this->business_hall_info['user_number'],
                    'label_id'      => $info['label_id'],
                    'phone_name'    => $info['name'],
                    'phone_version' => $info['version'],
                    'shoppe_id'     => $info['shoppe_id'],
            );
            //传给数字地图并记录日志
            rfid_helper::create_api_log($param);

            _model('rfid_label')->create($info);
        }

            return array('msg' => 'ok');
    }


    public function update_res_status()
    {
        $id = Request::Post('id', 0);

        if (!$id) {
            return array('info' => '信息错误');
        }

        $info = _uri('rfid_label',$id);

        if (!$info) {
            return array('info' => '标签设备不存在');
        }

        //数字地图需要数据
        $param = array(
                'type'        => 'delete',
                'user_number' => $this->business_hall_info['user_number'],
                'label_id'    => $info['label_id']
        );

        //传给数字地图上删除并记录日志
        rfid_helper::create_api_log($param);

        //缓存
        secret_helper::update_secret($info['label_id']);

        _model('rfid_label')->delete($id);

        return array('info' => 'ok');
    }
}