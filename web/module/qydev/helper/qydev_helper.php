<?php
/**
 * alltosun.com  qydev_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-4-9 上午11:12:14 $
 * $Id$
 */

class qydev_helper
{
    public static function check_qydev_auth($return_url)
    {
        $qydev_user_id = self::get_qydev_user_id();

        if (is_weixin()) {
            if (!$qydev_user_id){
                $url = AnUrl("qydev/auth?return_url=" . urlencode($return_url));
                self::redirect($url);
            }
        }
    }

    /**
     * 重定向
     * @param unknown $url
     */
    public static function redirect($url)
    {
//        p($url);exit();
//         $url = $url . '&power=1';
        Response::redirect($url);
        Response::flush();
        exit();
    }

    /**
     * 获取企业号用户ID
     * @return unknown|boolean
     */
    public static function get_qydev_seession_user_id()
    {
        static $qydev_seession_user_id;

        if ($qydev_seession_user_id) {
            return $qydev_seession_user_id;
        }

        if (isset($_SESSION['qydev_user_id']) && $_SESSION['qydev_user_id']) {
            $qydev_seession_user_id = $_SESSION['qydev_user_id'];

            return $qydev_seession_user_id;
        }

        return false;
    }

    /**
     * 获取站内登录账号member_id
     * @return int|boolean
     */
    public static function get_qydev_user_id()
    {
        static $qydev_user_id;

        if ($qydev_user_id) {
            return $qydev_user_id;
        }

        if (isset($_SESSION['member_id']) && $_SESSION['member_id']) {
            $qydev_user_id = $_SESSION['member_id'];

            return $qydev_user_id;
        }

        return false;
    }

    /**
     * 添加新的规则的判断 added by guojf
     * @param array $info
     * 代码块
     */
    public static function qydev_user_login($info)
    {

        // 菜单统计需要知道是谁点击
        $_SESSION['qydev_user_id'] = $info['UserId'];

        // 判断是否符合命名规则
        if ( !strpos($info['UserId'], '_' ) ) return  false;

        $user_level = explode("_", $info['UserId']);

        $user_number = qydev_user_helper::loacal_operation($info['UserId']);

//         if ( !$user_number ) return false;

        // 集团
        if (3 == count($user_level) && 'JT' == $user_level[0]) {
            //存session
            $_SESSION['member_id'] = 1;

            return '';
        }

        if ( !$user_number ) return false;


        $member_info  = _model('member')->read(array('member_user' => $user_number));

        if ( !$member_info ) return false;

        member_helper::remember_me_set($member_info);

        return true;
    }

    /*
     * 监测数据为空    直接跳到登录页 added by guojf
     */
    public static function check_qydev_redirect_login($qydev_info , $member_info)
    {
        if (!$qydev_info || !$member_info) {
            Response::redirect(AnUrl('liangliang/e_login'));
            return '';
        }
    }

    /**
     * 获取企业号的唯一账号 added by guojf
     * @return Ambigous <NULL, number, unknown>
     */
//     public static function get_qydev_user_id()
//     {

//         static $static_qydev_user_id = null;

//         if ($static_qydev_user_id === null) {
//             $static_qydev_user_id = isset($_SESSION['qydev_user_id']) ? $_SESSION['qydev_user_id'] : 0;
//         }

//         return $static_qydev_user_id;
//     }

    /**
     * 企业号的菜单统计 added by guojf
     * @param string $res_name
     * @param string $member_user
     */
    public static function stat_qydev_menu($res_name , $member_user)
    {
        if (!$res_name || !$member_user) {
            return false;
        }

        if (!in_array($res_name , qydev_config::$stat_menu_name)) {
            return false;
        }

        //统计数组
        $params = array();

        $params['res_name']    = $res_name;
        $params['unique_id']   = self::get_qydev_user_id();
        $params['user_number'] = $member_user;

        _widget('e.menu_stat')->qydev_stat($params);
    }


    /**
     * added by guojf
     * @param string $str
     * @return boolean|Ambigous <string, multitype:string >
     */
    public static function get_arr_value($str)
    {
         if (!$str) {
             return false;
         }

         $msg_title = '';

         $info = explode(',' , $str);

         foreach ($info as $k => $v) {
             $msg_title .= qydev_config::$qy_myself_department[$v].',';
         }

         return trim($msg_title , ',');
    }

    /**
     * added by guojf
     * 企业号通讯录成员通过Excel上传excel处理判断的方法
     * @param array $file
     * @return string|multitype:string NULL unknown |unknown
     */
    public static function handle_excel_upload($file)
    {
        if (!$file['name']) {
            return array('error' => '请选择上传的Excel文件');
        }

        if (isset($file['name']) && $file['name']) {
            $allow_type = Config::get('allow_type');

            $upload_path = UPLOAD_PATH;
            $fail_msg    = check_upload($file, 0, 1);

            if ($fail_msg) {
                return AnUrl('qydev/admin');
            }

            $ext = substr($file['name'], strrpos($file['name'], '.')+1);

            if (!in_array(strtolower($ext), $allow_type)) {
                return array('error' => '文件格式不正确');
            }

            if (empty($fail_msg)) {
                $file_path = an_upload($file['tmp_name'], $ext);
            }
        }

        $file_path = ROOT_PATH.'/upload'.$file_path;

        require_once MODULE_CORE.'/helper/reader.php';

        if (!file_exists($file_path)) {
            return array('error' => '文件不存在');
        }

        return $file_path;
    }

    /**
     *
     * 企业号通讯录成员通过Excel上传excel处理数据的方法
     * @param unknown $file_path
     */
    public static function handle_excel_data($file_path)
    {
        $phpexcel = new Spreadsheet_Excel_Reader();
        $phpexcel->setOutputEncoding('CP936');
        $phpexcel->read($file_path);//正式机
        $results = $phpexcel->sheets[0]['cells'];
        $cols = $phpexcel->sheets[0]['numCols'];
        $rows = $phpexcel->sheets[0]['numRows'];

        //Excel前两行 需要去掉
        array_shift($results);
        array_shift($results);

        $data = array();

        foreach ($results as $k => $v) {
            //转码
            for($i = 1; $i <= $cols; $i ++) {
                if (!isset($v[$i]) || !$v[$i]) {
                    $v[$i] = '';
                    continue;
                }

                $v[$i] = iconv('GB2312', 'UTF-8//TRANSLIT//IGNORE', $v[$i]);
                $v[$i] = trim($v[$i]);
            }

            $data['name']         = $v[1];
            $data['user_id']      = $v[2];
            $data['sex']          = $v[3];
            $data['weixinid']     = $v[4];
            $data['mobile']       = $v[5];
            $data['email']        = $v[6];

            $data['department']   = $v[7];

            $data['position']     = $v[8];
            $data['english_name'] = $v[9];
            $data['desk_phone']   = $v[10];
            $data['vr_id']        = $v[11];
            $data['analog_id']    = $v[12];
            $data['an_id']        = $v[13];

            p($data);
        }
    }

    /*
     * 记录错误日志
     */
    public static function record_error_log( $res_name,$error_ch , $error_eg)
    {
        if (!$res_name) {
            return false;
        }

        //企业号获取用户的错误记录日志
        $param = array('res_name' => $res_name, 'error_ch' => $error_ch , 'error_eg' => $error_eg);

        _model('qydev_error_log')->create($param);
    }

    /**
     * added by guojf
     * 企业号的申请负责项目
     * @param string $str
     */
    public static function apply_depart($str)
    {
        if (!$str) return false;

        $arr = explode(',' , $str);

        $new_str = '';
        foreach ($arr as $v) {
            $new_str  .= e_config::$qydev_apply_depart[$v].' / ';
        }

        return  trim($new_str , ' / ');

    }

    /**
     * @param array $user_info
     */
    public static function apply_user_create($user_number , $user_name , $phone , $depart)
    {
        $params = [];

        if (!$user_number || !$user_name || !$phone || !$depart) return false;

        $params['user_id']        = $user_number.'_ON';
        $params['name']           = $user_name;
        $params['department_ids'] = self::get_depart_ids($depart);
//         $params['position']       = '';
        $params['phone']          = $phone;
//         $params['sex']            = ';
//         $params['email']          = '';
//         $params['weixinid']       = '';
        //扩展字段
//         $params['egligsh_name'] = '';
//         $params['desk_phone']   = '';
//         $params['vr_id']        = '';
//         $params['analog_id']    = '';
        $params['an_id']        = $user_number;

        //企业号后台添加
        $result = _widget('qydev.user')->create_user($params);

        if (!$result) {
            return false;
        }

        //本站添加
        self::awifi_qydev_users_info($user_number, $user_name, $phone, $depart);

        return true;
    }

    /**
     * 获取类似[1 ,2, 3 ,4]
     * @param array  $depart_ids
     * @return boolean|string
     */
    public static function get_depart_ids($depart_ids)
    {
        if (!$depart_ids) return false;

        $str = '[';

        foreach ($depart_ids as $v) {
            $str .= $v.',';
        }

        return trim($str , ',') . ']';
    }

    /**
     * 创建本站的企业号成员的部分重要信息
     * @param str   $user_number
     * @param str   $user_name
     * @param str   $phone
     * @param array $depart  array(1,2,3)
     * @return boolean
     */
    public static function awifi_qydev_users_info($user_number , $user_name , $phone , $depart)
    {
        $filter = [];

        $filter['type']        = 4;
        $filter['user_number'] = $user_number;
        $filter['user_name']   = $user_name;
        $filter['user_phone']  = $phone;

        $filter['unique_id']   = $user_number.'_ON';
        $filter['an_id']       = $user_number;

        $depart_str = implode(',', $depart);

        //因为企业号的ID是唯一（不可改的）
        $user_info = _model('public_contact_user')->read(array('unique_id' => $filter['unique_id']));

        if ($user_info) {
            if ($depart_str == $user_info['from_id']) {
                return array('errcode' => 1, 'errmsg' => '您选择负责的项目已经都开通的权限' );
            }

            _model('public_contact_user')->update($user_info['id'] , $filter);
        } else {
             $filter['from_id'] = $depart_str;

             _model('public_contact_user')->create($filter);
        }

        return array('errcode' => 0, 'errmsg' => 'ok' );
    }

    /**
     * 获取一条数据或者该条数据的某个字段
     * @param unknown $table
     * @param string $field
     * @param unknown $filter
     * @return boolean|Ambigous <multitype:, string, unknown, Obj>
     */
    public static function get_field_info($table , $field = '', $filter)
    {
        if (!$table || !$filter) return false;

        if (!$field) {
            $info = _model($table)->read($filter);
        } else {
            $info = _uri($table , $filter , $field);
        }

        return $info;
    }

    /**
     * 查看是否该部门 返回这些部门的企业号的部门IDs
     * @param  array $depart_ids （产品级的部门IDs）
     * @param  array $yyt_info
     * @return array $ids:
     */
    public static function look_up_department($depart_ids , $yyt_info)
    {
        if (!$depart_ids || !$yyt_info) return false;

        $ids = [];

        foreach ($depart_ids as $v) {
            $local_pro_depart = _model('public_contact_department')->read( array('parent_id' => $v , 'name' => $yyt_info['pro_name']));

            $yyt_params = $pro_params = [];

            //创建负责项目下的省部门
            if (!$local_pro_depart) {

                $pro_params['parent_id'] = $v;
                $pro_params['name']      = $yyt_info['pro_name'];

                //企业号创建   省级部门
                $pro_depart_info= _widget('qydev.department')->create_department($pro_params);

                if (isset($pro_depart_info['errcode']) && $pro_depart_info['errcode']) {
                   return false;
                }

                //本地同时创建 省级部门
                self::create_local_department($pro_depart_info['id'], $yyt_info['pro_name'], $v);

                //创建营业厅部门
                $yyt_params['parent_id'] = $pro_depart_info['id'];
                $yyt_params['name']      = $yyt_info['title'];

                $yyt_depart_info = _widget('qydev.department')->create_department($pro_params);

                if (isset($yyt_depart_info['errcode']) && $yyt_depart_info['errcode']) {
                   return false;
                }

                //本地同时创建 营业厅级部门
                self::create_local_department($yyt_depart_info['id'], $yyt_info['name'], $pro_depart_info['id']);

                array_push($ids, $yyt_depart_info['id']);
            } else {
                //查看有没有营业厅部门
                $local_yyt_depart = _model('public_contact_department')->read(array('parent_id' => $local_pro_depart['department_id'] , 'name' => $yyt_info['title']));

                if (!$local_yyt_depart) {
                    //创建营业厅部门
                    $yyt_params['parent_id'] = $local_pro_depart['department_id'];
                    $yyt_params['name']      = $yyt_info['title'];

                    //企业号创建 营业厅级部门
                    $yyt_depart_info = _widget('qydev.department')->create_department($yyt_params);

                    if (isset($yyt_depart_info['errcode']) && $yyt_depart_info['errcode']) {
                       return false;
                    }

                    //本地同时创建 营业厅部门
                    self::create_local_department($yyt_depart_info['id'], $yyt_info['title'], $local_pro_depart['department_id']);

                    array_push($ids, $yyt_depart_info['id']);
                } else {
                    array_push($ids, $local_yyt_depart['department_id']);
                }
            }
        }

        //企业号最小（营业厅级的部门）部门的ID数组
        return $ids;
    }

    
    /**
     * 同时创建本地数据库的部门信息  因为 order 和 status 字段是不返回的没有存储
     * @param unknown $depart_id
     * @param unknown $name
     * @param unknown $parent_id
     * @return boolean
     */
    public static function create_local_department($depart_id , $name , $parent_id )
    {
        if (!$depart_id || !$name || !$parent_id) return false;

        _model('public_contact_department')->create(
                                            array(
                                                  'department_id' => $depart_id,
                                                  'name'          => $name,
                                                  'parent_id'     => $parent_id
                                                ));
    }

    /**
     * 营业厅级企业号ID转化为渠道码
     * @param unknown $user_id
     * @return boolean|multitype:|unknown
     */
    public static function translate_user_number($user_id)
    {
        if (!$user_id) return false;

        if (strpos($user_id, '_')) {
            return explode('_', $user_id)[0];
        }

        return $user_id;
    }
    
    /**
     * 发消息
     * @param string $FromUserName
     * @return unknown
     */
    public static function send_msg($wxcpt, $FromUserName)
    {
        $sRespData = "<xml>
                           <ToUserName><![CDATA[".$FromUserName."]]></ToUserName>
                           <FromUserName><![CDATA[0]]></FromUserName>
                           <CreateTime>".time()."</CreateTime>
                           <MsgType><![CDATA[text]]></MsgType>
                           <Content><![CDATA[".$this->contentStr."]]></Content>
                    </xml>";
    
        $sEncryptMsg = "";
    
        //xml格式的密文$sReqTimeStamp, $sReqNonce, $sEncryptMsg
        $errCode = $wxcpt->EncryptMsg($sRespData,  $this -> sVerifyTimeStamp, $this -> sVerifyNonce , $sEncryptMsg);
    
        return $errCode;
    }
    
    /**
     * 获取赞
     * @param unknown $res_id
     * @param unknown $res_name
     * @param number $type
     * @return string
     */
    public static function get_comment_zan($res_id,$res_name,$type=2)
    {
    
        $count_zan = _model('like')->getTotal(array('res_name'=>$res_name,'type_id'=>$res_id,'status'=>1,'type'=>2));
        if(!$count_zan){
            $count_zan = '';
        }
        return $count_zan;
    }
}