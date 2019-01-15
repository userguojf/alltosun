<?php
/**
 * alltosun.com  企业微信基本配置 wework_config.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-5-2 下午4:53:28 $
 * $Id$
 */
class wework_config
{
    /**
     * 企业ID
     */
    public static $wework_corpid = 'wx1a1fb37c4adad916';

    /**
     * 获取access_token
     * 请求方式：GET（HTTPS）
     * 请求URL：https://qyapi.weixin.qq.com/cgi-bin/gettoken?corpid=ID&corpsecret=SECRECT
     */
    public static $get_access_token_url = 'https://qyapi.weixin.qq.com/cgi-bin/gettoken?';

    /**
     * 获取菜单
     * 请求方式：POST（HTTPS）
     * 请求地址：https://qyapi.weixin.qq.com/cgi-bin/menu/create?access_token=ACCESS_TOKEN&agentid=AGENTID
     */
    public static $menu_create_url = 'https://qyapi.weixin.qq.com/cgi-bin/menu/create?';

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
     * 授权
     * appid=CORPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect
     */
    public  static  $auth_url = "https://open.weixin.qq.com/connect/oauth2/authorize?";

    /**
     * 获取user_id
     * access_token=ACCESS_TOKEN&userid=USERID
     */
    public static $get_user_id_url = "https://qyapi.weixin.qq.com/cgi-bin/user/getuserinfo?";

    /**
     * 获取部门成员(详情)
     * 参数access_token=ACCESS_TOKEN&department_id=DEPARTMENT_ID&fetch_child=FETCH_CHILD&status=STATUS
     */
    public static $deaprt_user_detail_url = 'https://qyapi.weixin.qq.com/cgi-bin/user/list?';

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
     * 删除部门
     * access_token=ACCESS_TOKEN&id=ID
     */
    public static $delete_department_url = 'https://qyapi.weixin.qq.com/cgi-bin/department/delete?';

    /**
     * 更新部门
     * access_token=ACCESS_TOKEN
     */
    public static $update_department_url = 'https://qyapi.weixin.qq.com/cgi-bin/department/update?';

    
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
     * access_token=ACCESS_TOKEN&department_id=DEPARTMENT_ID&fetch_child=FETCH_CHILD
     * 1/0：是否递归获取子部门下面的成员
     * @var unknown
     */
    public static $get_department_user_url = 'https://qyapi.weixin.qq.com/cgi-bin/user/simplelist?';

    /**
     * 更新成员信息
     * access_token=ACCESS_TOKEN
     */
    public static $update_user_url = 'https://qyapi.weixin.qq.com/cgi-bin/user/update?';

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
     * 大连锁辅销助手
     * 大连锁 应用的凭证密钥
     */
    public static $dls_agent_secret = array(
            'work' => '2k5xVmwkcIfLPjtEyK6OgW9SH8OH0S6RdmFpCRI6qjg',

            0  => 'rKA0poU_eDR_-e3a1nMqX1tIsl9gcnLo1zLKX_CdMNk',
            27 => 'foFFDRXgEmjjxVfMrGEd9ftR0Uaca5ghqv_wwMPBXB8',
            21 => 'yoOvQSDy-daQ_8zJ3_HjVPFLJzMFuTi5yixeuDPwGRU',
    );

    /**
     * 企业微信部门
     * @var unknown
     */
    public static $wework_department = array(
         1 => '集团公司',
         2 => '开发测试部门',
     );

    /**
     * 企业微信部门类型
     * @var unknown
     */
    public static $department_type = array(
            0,
            1,
            2
    );

    public static $i_depart = array(
            '中国电信' => 1,
            '数字地图' => 2,
            '集团公司' => 4,
            '爱WIFI' => 145,
            'O2O事业部' => 6,
            'ibeacon' => 147,
            '一体化排队' => 211,
            '测试用户' => 30274,
            '亮屏管理' => 30348
    );

    public static $depart_i = array(
            1 => '中国电信',
            2 => '数字地图',
            4 => '集团公司',
            145 => '爱WIFI',
            6 => 'O2O事业部',
            147 => 'ibeacon',
            211 => '一体化排队',
            30274 => '测试用户',
            30348 => '亮屏管理'
    );

    public static $province_list = array ( 
            '北京' => 'BJ', '安徽' => 'AH', '福建' => 'FJ', 
            '甘肃' => 'GS', '广东' => 'GD', '广西' => 'GX', 
            '贵州' => 'GZ', '海南' => 'HI', '河北' => 'HB',
            '河南' => 'HA', '黑龙江' => 'HL', '湖北' => 'HE' , 
            '湖南' => 'HN', '吉林' => 'JL', '江苏' => 'JS', 
            '江西' => 'JX', '辽宁' => 'LN', '内蒙古' => 'NM', 
            '宁夏' => 'NX', '青海' => 'QH', '山东' => 'SD', 
            '山西' => 'SX', '陕西' => 'SN', '上海' => 'SH', 
            '四川' => 'SC', '天津' => 'TJ', '西藏' => 'XZ', 
            '新疆' => 'XJ', '云南' => 'YN', '浙江' => 'ZJ', '重庆' => 'CQ', 
            '香港' => 'XG', '澳门' => 'AM', '台湾' => 'TW', 
    );
}