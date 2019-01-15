<?php

/**
 * alltosun.com 企业号配置文件 qydev_config.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (shenxn@alltosun.com) $
 * $Date: 2017-4-6 下午6:04:59 $
 * $Id$
 */

class qydev_config
{
    public static $corp_id = 'wx1a1fb37c4adad916';

    public static $secret = 'FPcvdPv9cztiBQ9aNK4MvsjuWLOFdtqrM-cOVfrO1QPINNlM6JUwmCHBXXhvalAs';

    /**
     * 获取access_token
     * 传参形式 corpid=id&corpsecret=secrect
     */
    public static $gettoken_url ="https://qyapi.weixin.qq.com/cgi-bin/gettoken?";

    /**
     * GET方式请求获得jsapi_ticket
     * access_token=ACCESS_TOKE
     */
    public static $get_jsapi_ticket_url = "https://qyapi.weixin.qq.com/cgi-bin/get_jsapi_ticket?";

    /**
     * 获取部门
     * 穿参形式 access_token=ACCESS_TOKEN&id=ID
     */
    public static $get_department_list_url= "https://qyapi.weixin.qq.com/cgi-bin/department/list?";

    /**
     * 创建菜单
     * 传参形式access_token=ACCESS_TOKEN&agentid=AGENTID
     */
    public static $menu_create_url ="https://qyapi.weixin.qq.com/cgi-bin/menu/create?";


    /**
     * 删除菜单
     * 传参形式access_token=ACCESS_TOKEN&agentid=AGENTID
     */
    public static $menu_delete_url ="https://qyapi.weixin.qq.com/cgi-bin/menu/delete?";


    /**
     * 获取菜单列表
     * 传参形式access_token=ACCESS_TOKEN&agentid=AGENTID
     */
    public static $menu_get_url ="https://qyapi.weixin.qq.com/cgi-bin/menu/get?";



    /**
     * 企业号授权
     * appid=CORPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect
     */
    public  static  $auth_url = "https://open.weixin.qq.com/connect/oauth2/authorize?";


    /**
     * 企业号获取user_id
     * access_token=ACCESS_TOKEN&userid=USERID
     */
    public static $get_user_id_url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?";

    /**
     * 获取部门成员(详情)
     * 参数access_token=ACCESS_TOKEN&department_id=DEPARTMENT_ID&fetch_child=FETCH_CHILD&status=STATUS
     */
    public static $user_detail_url = 'https://qyapi.weixin.qq.com/cgi-bin/user/list?';

    /**
     * 获取部门列表
     * 参数access_token=ACCESS_TOKEN&id=ID
     */
    public static $department_list_url = 'https://qyapi.weixin.qq.com/cgi-bin/department/list?';

    /**
     * 创建部门
     * access_token=ACCESS_TOKEN
     */
    public static $create_department_url = 'https://qyapi.weixin.qq.com/cgi-bin/department/create?';

    /**
     * 发送消息接口
     * 参数access_token=ACCESS_TOKEN
     */
    public static $send_msg_url = 'https://qyapi.weixin.qq.com/cgi-bin/message/send?';

    /**
     * 获取成员信息
     * 参数access_token=ACCESS_TOKEN&userid=USERID
     */
    public static $get_user_info_url = 'https://qyapi.weixin.qq.com/cgi-bin/user/get?';

    /**
     * 获取部门成员
     * access_token=ACCESS_TOKEN&department_id=DEPARTMENT_ID&fetch_child=FETCH_CHILD&status=STATUS
     * @var unknown
     */
    public static $get_department_user_url = 'https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?';
    /**
     * 更新成员信息
     * access_token=ACCESS_TOKEN
     */
    public static $update_user_info_url = 'https://qyapi.weixin.qq.com/cgi-bin/user/update?';

    /**
     * 创建通讯录成员
     * 参数access_token=ACCESS_TOKEN
     */
    public static $create_user_url   = 'https://qyapi.weixin.qq.com/cgi-bin/user/create?';

    /**
     * 删除通讯录成员
     * 参数access_token=ACCESS_TOKEN&userid=USERID
     */
    public static $delete_user_url   = 'https://qyapi.weixin.qq.com/cgi-bin/user/delete?';

    /**
     * 注：企业号中部门      myself自定义
     * 1.数字地图
     * 2.集团公司
     * 3.爱WIFI
     * 4.O2O事业部
     * 5.ibeacon
     * 6.一体化排队
     */
    public static $qy_myself_department = array(
            1 => '数字地图',
            2 => '集团公司',
            3 => '爱WIFI',
            4 => 'O2O事业部',
            5 => 'ibeacon',
            6 => '一体化排队',
            7 => '待分配权限组',
            8 => '旧排队'
    );

    /**
     * 企业号的部门ID对应的本站的后台部门自定义ID
     * @var unknown
     */
    public static $local_department_id = array(
            2    => 1,
            145  => 3,
            147  => 5,
            211  => 6,
            1513 => 7,
            2043 => 8
    );

    /**
     * 修复用到
     */
    public static $repair_department_id = array(
            1 => 2,
            3 => 145,
            5 => 147,
            6 => 211
    );

    /**
     * 账号级别
     * @var unknown
    */
    public static $user_type = array(
            0 => '集团',
            1 => '省级',
            2 => '市级',
            3 => '地区',
            4 => '营业厅',
    );

    /**
     * 添加通讯录成员方式
     */
    public static $user_add_type = array(
            1 => '单独添加',
            2 => '批量添加'
    );

    /**
     * 菜单点击量统计
     */
    public static $stat_menu_name = array(
            1 => 'add',
            2 => 'list',
    );

    /**
     * 应用的凭证密钥
     */
    public static $agent_secret = array(
            // 通讯录
            0    => 'rKA0poU_eDR_-e3a1nMqX1tIsl9gcnLo1zLKX_CdMNk',
            'work' => '2k5xVmwkcIfLPjtEyK6OgW9SH8OH0S6RdmFpCRI6qjg',
            27   => 'foFFDRXgEmjjxVfMrGEd9ftR0Uaca5ghqv_wwMPBXB8',
    );
}
?>