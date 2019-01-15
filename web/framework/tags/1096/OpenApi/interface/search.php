<?php

/**
 * alltosun.com 开放平台的接口搜索类 search.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-18 上午10:29:48 $
 * $Id: search.php 643 2013-02-07 12:16:41Z anr $
*/

/**
 * 开放平台的接口搜索类
 * @author nignhx@alltosun.com
 * @package AnOpenApi
 */
interface AnOpenApiSearchWrapper extends AnOpenApiWrapper
{
    /**
     * @提示联系
     * @param string $q 关键字
     * @param int $count 返回记录的条数
     * @param int $type 联想类型 允许的资源为users， statuses， companies
     * @param int $range 联想范围
     */
    public function suggestion($q, $type = 'users', $count = 10);

    /**
     * 搜索学校时的联想搜索建议
     * @param string $q 搜索的关键字，必须做URLencoding。必填
     * @param int $count 返回的记录条数，默认为10。
     * @param int type 学校类型，0：全部、1：大学、2：高中、3：中专技校、4：初中、5：小学，默认为0。选填
     * @return array
     */
    public function suggestionSchool($q, $count = 10, $type = 1);

    /**
     * ＠用户时的联想建议
     * @param string $q 搜索的关键字，必须做URLencoding。必填
     * @param int $count 返回的记录条数，默认为10。
     * @param int $type 联想类型，0：关注、1：粉丝。必填
     * @param int $range 联想范围，0：只联想关注人、1：只联想关注人的备注、2：全部，默认为2。选填
     * @return array
     */
    public function suggestionAtUser($q, $count = 10, $type = 0, $range = 2);

    /**
     * 搜索与指定的一个或多个条件相匹配的微博
     * @param array $query 搜索选项。格式：array('key0'=>'value0', 'key1'=>'value1', ....)。支持的key:
     *  - q				string	搜索的关键字，必须进行URLencode。
     *  - filter_ori	int		过滤器，是否为原创，0：全部、1：原创、2：转发，默认为0。
     *  - filter_pic	int		过滤器。是否包含图片，0：全部、1：包含、2：不包含，默认为0。
     *  - fuid			int		搜索的微博作者的用户UID。
     *  - province		int		搜索的省份范围，省份ID。
     *  - city			int		搜索的城市范围，城市ID。
     *  - starttime		int		开始时间，Unix时间戳。
     *  - endtime		int		结束时间，Unix时间戳。
     *  - count			int		单页返回的记录条数，默认为10。
     *  - page			int		返回结果的页码，默认为1。
     *  - needcount		boolean	返回结果中是否包含返回记录数，true：返回、false：不返回，默认为false。
     *  - base_app		int		是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
     * needcount参数不同，会导致相应的返回值结构不同
     * 以上参数全部选填
     * @return array
     */
    public function searchStatusHigh($query);

    /**
     * 通过关键词搜索用户
     * @param array $query 搜索选项。格式：array('key0'=>'value0', 'key1'=>'value1', ....)。支持的key:
     *  - q			string	搜索的关键字，必须进行URLencode。
     *  - snick		int		搜索范围是否包含昵称，0：不包含、1：包含。
     *  - sdomain	int		搜索范围是否包含个性域名，0：不包含、1：包含。
     *  - sintro	int		搜索范围是否包含简介，0：不包含、1：包含。
     *  - stag		int		搜索范围是否包含标签，0：不包含、1：包含。
     *  - province	int		搜索的省份范围，省份ID。
     *  - city		int		搜索的城市范围，城市ID。
     *  - gender	string	搜索的性别范围，m：男、f：女。
     *  - comorsch	string	搜索的公司学校名称。
     *  - sort		int		排序方式，1：按更新时间、2：按粉丝数，默认为1。
     *  - count		int		单页返回的记录条数，默认为10。
     *  - page		int		返回结果的页码，默认为1。
     *  - base_app	int		是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
     * 以上所有参数全部选填
     * @return array
     */
    public function searchUserKeywords($query);
}
?>