<?php
/**
 * alltosun.com log模块配置文件 log_config.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 瑞东 宋瑞东 (songrd@alltosun.com) $
 * $Date: 2014-8-12 上午10:59:04 $
 * $Id: log_config.php 69429 2014-09-19 08:49:37Z songrd $
 */

class log_config
{

    /** 操作分类 */
    public static $log_action = array(
            '新增'=>'新增',
            '编辑'=>'编辑',
            '删除'=>'删除',
            '审核'=>'审核'
        );

    /** res_name对应的名字 */
    public static $log_res_name = array(

            'focus'           => '焦点图',
            'log'             => '日志',

            'member'          => '管理员表',
            'group_user'      => '管理员关联表',
            'group'           => '管理员用户组',
            'user'            => '用户表',
            'fav'             => '收藏',
            'bank_card'       => '银行卡',
            'backlist'        => '黑名单',
            'comment'         => '评论',
            'card'            => '银行卡',
            'coupon'          => '优惠',
            'charge_card'     => '充值卡',
            'credit_card'     => '用户充值卡',
            'credit_record'   => '积分导入记录',
            'credit_statements'=> '对账单表',
            'express'         => '快递',
            'article'         => '新闻',
            'attribute_value' => '商品属性值',
            'attribute_key'   => '商品属性',
            'attribute_group' => '商品属性组',
            'category'        => '分类',
            'order'           => '订单',
            'action'          => '权限',
         'user_exchange_shop' => '电商表',
       'user_exchange_credit' => '兑换电商积分表',
       'user_exchange_record' => '兑换电商积分记录表',
           'lottery'          => '抽奖表',
           'lottery_record'   => '抽奖记录表',
           'special'          => '商品专题表',
           'sys_message'      => '系统消息',
           'bank'             => '银行卡',
           'push_record'      => '推送消息记录表',
           'group_user'       => '角色组成员设置表',
            'mokuai'          => '精选模块管理',

    );


    /** 查询分类 */
    public static $search_type = array(
        '2'=>'全部',
        '0'=>'成功',
        '1'=>'失败',
    );

}
?>