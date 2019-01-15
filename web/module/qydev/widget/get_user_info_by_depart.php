<?php
/**
 * alltosun.com  get_user_info.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-4-17 上午11:17:01 $
 * $Id$
 */

class get_user_info_widget
{
    /**
     * 处理通讯录成员详情
     * @$dept 即部门的名称
     * 注：企业号中部门      myself自定义
     * 1.数字地图
     * 2.集团公司
     * 3.爱WIFI
     * 4.O2O事业部
     * 5.ibeacon
     * 6.一体化排队
     */

    private $access_token = NULL;

    public function __construct()
    {
        //获取$this->access_token
        $this->access_token = _widget('qydev.token')->get_access_token();

        if (!$this->access_token) {
            //记录错误日志
            qydev_helper::record_error_log('user_list', 'access_token获取失败', $this->access_token);
            return false;
        }
    }

    public function user_info($dept)
    {
        //数组
        $filter = [];

        if (!$dept || !in_array($dept , qydev_config::$qy_myself_department)) {
            //记录错误日志
            qydev_helper::record_error_log('user_list', '部门名称出现问题', 'look up qydev_config::$qy_myself_department');
            return false;
        }

        //查询对应部门的department_id
        $department_id = _uri('public_contact_department',array('name' => $dept) ,'department_id');

        if (!$department_id) {
            //记录错误日志
            qydev_helper::record_error_log('user_list', 'public_contact_department表中对应的部门名称未找到', '');
            return false;
        }

        //获取部门成员详情的接口
        $user_json = curl_get(qydev_config::$user_detail_url.'access_token='.$this->access_token.'&department_id='.$department_id.'&fetch_child=1&status=0');

        //通讯录成员的详细信息
        $user_info = json_decode($user_json , true);

        //判断接口返回情况
        if (!isset($user_info['errcode']) || $user_info['errcode'] != 0) {
            //记录错误日志
            qydev_helper::record_error_log('user_list', '接口错误', $user_json);
            return false;
        }

        if (!isset($user_info['userlist']) || empty($user_info['userlist'])) {
            //记录错误日志
            qydev_helper::record_error_log('user_list', '获取部门成员(详情)为空', $user_json);
            return false;
        }

        //遍历存表
        foreach ($user_info['userlist'] as $key => $val) {

            /**
             * array_search()函数
             * 返回一个键名，如果值有重复返回第一个键名
             */
            //自定义所属部门id
            $filter['from_id'] = array_search($dept , qydev_config::$qy_myself_department);

            //初始化
            $filter['user_number'] = '';
            $filter['an_id']       = '';
            $filter['analog_id']   = '';
            $filter['type']        = '';

            //直接赋值  必填
            $filter['unique_id']  = $val['userid'];

            /**
             * 由命名规则判断
             */
            //判断账号等级
            if (!strpos($val['userid'] , "_")) {
                //记录错误日志
                qydev_helper::record_error_log('user_list', '不符合命名规则', json_encode($val));
                //不能影响其他的用户录入
                continue;
            }

            $user_arr_class = explode("_" , $val['userid']);

            //集团
            if (3 == count($user_arr_class) && 'JT' == $user_arr_class[0]) {
                $filter['type']        = 0;
                $filter['user_number'] = 'admin';
            }
            //省
            if (3 == count($user_arr_class) && 'JT' !=$user_arr_class[0]  && '0000' == $user_arr_class[1]) {
                $filter['type']        = 1;
            }
            //市
            if (3 == count($user_arr_class) && '0000' != $user_arr_class[1] ) {
                $filter['type']        = 2;
            }

            //营业厅只有营业厅账号一个'_'
            if (2 == count($user_arr_class)) {
                $filter['type']        = 4;

                /**
                 * 只要是营业厅就要他的渠道码
                 */
                $filter['user_number'] = explode("_" , $val['userid'])[0];
            }

            if (isset($val['name']) && $val['name']) {
                $filter['user_name']  = $val['name'];
            }

            if (isset($val['mobile']) && $val['mobile']) {
                $filter['user_phone'] = $val['mobile'];
            }

            //判断扩展属性
            if (isset($val['extattr']) && $val['extattr']) {

                //遍历扩展属性值里的属性
                if (isset($val['extattr']['attrs']) && $val['extattr']['attrs']) {

                    foreach ($val['extattr']['attrs'] as $k => $v) {

                        //an_id企业号后台添加时是后台登录账号 analog_id数字地图的账号
                        if ($v['value'] && $v['name'] == 'an_id') {
                            $filter['an_id']       = $v['value'];
                            $filter['user_number'] = $v['value'];

                        } else if ($v['value'] && $v['name'] == 'analog_id') {
                            $filter['analog_id']   = $v['value'];

                        }
                    }
                }//扩展属性里一层end

            }//扩展属性最外层end

            //处理条件->判断数据库是否已经有对应账号的数据
            $db_user_info = _model('public_contact_user')->read(array('unique_id' => $val['userid']));

            if (!$db_user_info) {
                //一条数据完成 存表
                _model('public_contact_user')->create($filter);
            } else {
                //一条数据更新表
                $filter['from_id'] = $db_user_info['from_id'].','.$filter['from_id'];

                _model('public_contact_user')->update(array('id' => $db_user_info['id']) , $filter);
            }

        }//最大循环遍历end

        return true;
    }


    /**
     * 获取成员信息
     * @param array $params
     * @return string[]
     */
    public function get_user_info($params= array())
    {
        if (!isset($params['user_id']) || !$params['user_id']){
            return array('info' =>'fail', 'msg' => '无效的成员id');
        }

        if (!$this->access_token) {
            return array('info' => 'fail', 'msg' => '获取access_token失败');
        }

        $url = qydev_config::$get_user_info_url.'access_token='.$this->access_token.'&userid='.$params['user_id'];

        $result = curl_get($url);

        $result = json_decode($result, true);

        if ($result['errcode'] != 0) {
            return array('info' => 'fail', 'msg' => $result['errmsg']);
        }

        return array('info' => 'ok', 'user_info' => $result);

    }
}