<?php
/**
  * alltosun.com 机型信息操作 phone.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年1月30日 下午12:26:24 $
  * $Id$
  */
class phone_widget
{
    /**
     * 更新所有设备nickname_id
     */
    public function update_all_device_nickname_id()
    {
        $list = _model('rfid_label')->getList(array(1=>1));
        foreach ($list as $k => $v) {
            $this->update_device_nickname_id($v);
        }
    }

    public function update_device_nickname_id($label_info)
    {
        //获取设备nickname_id
        $nickname_id = $this->match_nickname($label_info);

        if ($nickname_id) {
            _model('rfid_label')->update($label_info['id'], array('device_nickname_id' => $nickname_id));
        }

    }

    /**
     * 匹配昵称
     */
    private function match_nickname($label_info)
    {

        //查询机型表 普通匹配
        $nickname_info = _model('screen_device_nickname')->read(array('name_nickname' => $label_info['name'], 'version_nickname' => $label_info['version']));
        if ($nickname_info) {
            return $nickname_info['id'];
        }

        //按IMEI末六位一样、厅一样的机型
        $filter = array(
                'business_id' => $label_info['business_hall_id'],
                'imei LIKE'   => '%'.$label_info['imei'],
                'status'      => 1
        );

        $screen_device_info = _model('screen_device')->read($filter);

        if ($screen_device_info) {
            return $screen_device_info['device_nickname_id'];
        }


        $name           = trim($label_info['name']);
        $version       = trim($label_info['version']);

        //机型昵称中存在品牌 比如 品牌:OPPO 型号:oppo R11
        $version = trim(str_replace($name, '', $version));
        $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $name, 'version_nickname' => $version));

        if ($nickname) {
            return $nickname['id'];
        }

        //指定机型 畅享6S
        if ($version == '畅享6S') {
            $version = trim(str_replace('畅享', '畅享 ', $version));
            $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $name, 'version_nickname' => $version));
            if ($nickname) {
                return $nickname['id'];
            } else {
                return false;
            }
        }

        //指定机型 nova 青春版
        if ($version == 'nova 青春版'){
            $version = 'nova青春版';
            $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $name, 'version_nickname' => $version));
            if ($nickname) {
                return $nickname['id'];
            } else {
                return false;
            }
        }

        //指定机型 三星系列 C501X 等
        if (in_array($version, array('C501X', 'C710X', 'C900X', 'N950XC'))) {
            $nickname = _model('screen_device_nickname')->read(array('phone_name' => 'samsung', 'phone_version' => 'sm-'.strtolower($label_info['version'])));
            if ($nickname) {
                return $nickname['id'];
            } else {
                return false;
            }
        }

        //指定机型 三星系列 s8 等
        if (in_array($version, array('S8', 'S8+', 'C7Pro'))) {
            $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $name, 'version_nickname' => 'Galaxy '.$version));
            if ($nickname) {
                return $nickname['id'];
            } else {
                return false;
            }
        }

        //指定格式 R11plus 格式系列
        if (strpos($version, 'plus') !== false && strpos($version, ' plus') == false) {

            $version = trim(str_replace('plus', ' plus', $version));
            $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $name, 'version_nickname' => $version));
            if ($nickname) {
               return $nickname['id'];
            } else {
                return false;
            }
        }

        //机型错别字系列  mete10
        if (strpos($version, 'mete') !== false) {
            $version = trim(str_replace('mete', 'mate ', $version));
            $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $name, 'version_nickname' => $version));
            if ($nickname) {
               return $nickname['id'];
            } else {
                return false;
            }
        }

        //指定格式 mate10格式系列
        if (strpos($version, 'mate') !== false && strpos($version, 'mate ') === false){
            $version = trim(str_replace('mate', 'mate ', $version));
            $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $name, 'version_nickname' => $version));
            if ($nickname) {
               return $nickname['id'];
            } else {
                return false;
            }
        }

        ///指定格式 Mate10格式系列
        if (strpos($version, 'Mate') !== false && strpos($version, 'Mate ') === false){
            $version = trim(str_replace('Mate', 'mate ', $version));
            $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $name, 'version_nickname' => $version));
            if ($nickname) {
               return $nickname['id'];
            } else {
                return false;
            }
        }

        //指定格式 mate10pro格式系列
        if (strpos($version, 'pro') !== false && strpos($version, ' pro') == false){
            $version = trim(str_replace('pro', ' pro', $version));
            $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $name, 'version_nickname' => $version));
            if ($nickname) {
               return $nickname['id'];
            } else {
                return false;
            }
        }

        //机型错别字系列 畅想6S
        if (strpos($version, '畅想') !== false){
            $version = trim(str_replace('畅想', '畅享', $version));
            $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $name, 'version_nickname' => $version));
            if ($nickname) {
               return $nickname['id'];
            } else {
                return false;
            }
        }

        //指定格式 畅想 系列
        if (strpos($version, '畅享 ') !== false){
            $version = trim(str_replace('畅享 ', '畅享', $version));
            $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $name, 'version_nickname' => $version));
            if ($nickname) {
                return $nickname['id'];
            } else {
                return false;
            }
        }

        //指定格式 红米Note 4X 系列
        if (strpos($version, 'Note') !== false){
            $pos = strpos($version, 'Note');
            if ($version{$pos+1} && strpos($version, 'Note ') === false) {
                $version = trim(str_replace('Note', 'Note ', $version));
            }

            if ($version{$pos-1} && strpos($version, ' Note') === false) {
                $version = trim(str_replace('Note', ' Note', $version));
            }

            $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $name, 'version_nickname' => $version));
            if ($nickname) {
                return $nickname['id'];
            } else {
                return false;
            }
        }


        //指定格式 荣耀8青春版 系列
        if (strpos($version, '青春版') !== false && strpos($version, ' 青春版') === false){
            $version = trim(str_replace('青春版', ' 青春版', $version));
            $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $name, 'version_nickname' => $version));
            if ($nickname) {
                return $nickname['id'];
            } else {
                return false;
            }
        }

        //指定格式  荣耀v9 系列
        if (strpos($version, '荣耀V') !== false){
            $version = trim(str_replace('荣耀V', '荣耀 v', $version));
            $nickname = _model('screen_device_nickname')->read(array('name_nickname' => $name, 'version_nickname' => $version));
            if ($nickname) {
                return $nickname['id'];
            } else {
                return false;
            }
        }

        return false;
    }


//     /**
//      * 更新设备昵称
//      */
//     public function update_device_nickname_id2()
//     {
//         $look = tools_helper::Get('look', 1);

//         //查询所有RFID设备
//         $rfid_device = _model('rfid_label')->getList(array('device_nickname_id' => 0));

//         foreach ($rfid_device as $k => $v) {

//             //型号为 nova 青春版
//             //型号为 三星系列 C501X 等
//             //型号为 三星系列 s8 等
//             //型号为  R11plus 格式
//             //型号为 华为mate10
//             //型号为 华为Mate10
//             //型号为 华为mate10pro
//             //型号为 畅想6S
//             //型号为 畅享6S
//             //型号为 红米Note 4X
//             //型号为 荣耀8青春版
//             //型号为 荣耀v9
//         }

//     }
}

