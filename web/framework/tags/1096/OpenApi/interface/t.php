<?php

/**
 * alltosun.com 开放平台的接口微博类 t.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-9-18 上午10:30:00 $
 * $Id: t.php 643 2013-02-07 12:16:41Z anr $
*/

/**
 * 开放平台的接口微博类
 * @author nignhx@alltosun.com
 * @package AnOpenApi
 */
interface AnOpenApiTWrapper extends AnOpenApiWrapper
{
    /**
     * 获取最新的公共微博
     * @param int $count 单页返回的记录条数，默认为50。
	 * @param int $page 返回结果的页码，默认为1。
	 * @param int $base_app 是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
	 * @return array
     */
    public function publicTimeLine($page = 1, $count = 50, $base_app = 0);

    /**
     * 获取当前登录用户及其所关注用户的最新微博
     * @param int $page 指定返回结果的页码。根据当前登录用户所关注的用户数及这些被关注用户发表的微博数，翻页功能最多能查看的总记录数会有所不同，通常最多能查看1000条左右。默认值1。可选。
	 * @param int $count 每次返回的记录数。缺省值50，最大值200。可选。
	 * @param int $since_id 若指定此参数，则只返回ID比since_id大的微博消息（即比since_id发表时间晚的微博消息）。可选。
	 * @param int $max_id 若指定此参数，则返回ID小于或等于max_id的微博消息。可选。
	 * @param int $base_app 是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
	 * @param int $feature 过滤类型ID，0：全部、1：原创、2：图片、3：视频、4：音乐，默认为0。
	 * @return array
     */
    public function homeTimeLine($page = 1, $count = 50, $since_id = 0, $max_id = 0, $base_app = 0, $feature = 0);

    /**
     * 获取当前登录用户及其所关注用户的最新微博的ID
     * @todo
     */
    public function friendsTimeLineIds();

    /**
     * 获取用户发布的微博
     * @param int $page 页码
	 * @param int $count 每次返回的最大记录数，最多返回200条，默认50。
	 * @param mixed $uid 指定用户UID或微博昵称
	 * @param int $since_id 若指定此参数，则只返回ID比since_id大的微博消息（即比since_id发表时间晚的微博消息）。可选。
	 * @param int $max_id 若指定此参数，则返回ID小于或等于max_id的提到当前登录用户微博消息。可选。
	 * @param int $base_app 是否基于当前应用来获取数据。1为限制本应用微博，0为不做限制。默认为0。
	 * @param int $feature 过滤类型ID，0：全部、1：原创、2：图片、3：视频、4：音乐，默认为0。
	 * @param int $trim_user 返回值中user信息开关，0：返回完整的user信息、1：user字段仅返回uid，默认为0。
	 * @return array
     */
    public function userTimeLine($uid = NULL , $page = 1 , $count = 50 , $since_id = 0, $max_id = 0, $feature = 0, $trim_user = 0, $base_app = 0);

    /**
     * 获取用户发布的微博的ID
     * @todo
     */
    public function userTimeLineIds();

    /**
     * 返回一条原创微博的最新转发微博
     * @param int $sid 要获取转发微博列表的原创微博ID。
	 * @param int $page 返回结果的页码。
	 * @param int $count 单页返回的最大记录数，最多返回200条，默认50。可选。
	 * @param int $since_id 若指定此参数，则只返回ID比since_id大的记录（比since_id发表时间晚）。可选。
	 * @param int $max_id 若指定此参数，则返回ID小于或等于max_id的记录。可选。
	 * @param int $filter_by_author 作者筛选类型，0：全部、1：我关注的人、2：陌生人，默认为0。
	 * @return array
     */
    public function rtTimeLine($sid, $page = 1, $count = 50, $since_id = 0, $max_id = 0, $filter_by_author = 0);

    /**
     * 获取一条原创微博的最新转发微博的ID
     * @todo
     */
    public function rtTimeLineIds();

    /**
     * 返回用户转发的最新微博
     * @param int $page 返回结果的页码。
	 * @param int $count  每次返回的最大记录数，最多返回200条，默认50。可选。
	 * @param int $since_id 若指定此参数，则只返回ID比since_id大的记录（比since_id发表时间晚）。可选。
	 * @param int $max_id  若指定此参数，则返回ID小于或等于max_id的记录。可选。
	 * @return array
     */
    public function rtByMe($page = 1, $count = 50, $since_id = 0, $max_id = 0, $filter_by_type = 0);

    /**
     * 获取@当前用户的最新微博
     * @param int $page 返回结果的页序号。
	 * @param int $count 每次返回的最大记录数（即页面大小），不大于200，默认为50。
	 * @param int $since_id 若指定此参数，则只返回ID比since_id大的微博消息（即比since_id发表时间晚的微博消息）。可选。
	 * @param int $max_id 若指定此参数，则返回ID小于或等于max_id的提到当前登录用户微博消息。可选。
	 * @param int $filter_by_author 作者筛选类型，0：全部、1：我关注的人、2：陌生人，默认为0。
	 * @param int $filter_by_source 来源筛选类型，0：全部、1：来自微博、2：来自微群，默认为0。
	 * @param int $filter_by_type 原创筛选类型，0：全部微博、1：原创的微博，默认为0。
	 * @return array
     */
    public function mentions($page = 1, $count = 50, $since_id = 0, $max_id = 0, $filter_by_author = 0, $filter_by_source = 0, $filter_by_type = 0);

    /**
     * 获取@当前用户的最新微博的ID
     * @todo
     */
    public function mentionsIds();

    /**
     * 获取双向关注用户的最新微博
     */
    public function bilateralTimeLine();

    /**
     * 根据ID获取单条微博信息
     * @param int $id 要获取已发表的微博ID, 如ID不存在返回空
	 * @return array
     */
    public function show($id);

    /**
     * 通过id获取mid
     * @param int|string $id  需要查询的微博（评论、私信）ID，批量模式下，用半角逗号分隔，最多不超过20个。
	 * @param int $type  获取类型，1：微博、2：评论、3：私信，默认为1。
	 * @param int $is_batch 是否使用批量模式，0：否、1：是，默认为0。
	 * @return array
     */
    public function queryMid($id, $type = 1, $is_batch = 0);

    /**
     * 通过mid获取id
     * @param int|string $mid  需要查询的微博（评论、私信）MID，批量模式下，用半角逗号分隔，最多不超过20个。
	 * @param int $type  获取类型，1：微博、2：评论、3：私信，默认为1。
	 * @param int $is_batch 是否使用批量模式，0：否、1：是，默认为0。
	 * @param int $inbox  仅对私信有效，当MID类型为私信时用此参数，0：发件箱、1：收件箱，默认为0 。
	 * @param int $isBase62 MID是否是base62编码，0：否、1：是，默认为0。
	 * @return array
     */
    public function queryId($mid, $type = 1, $is_batch = 0, $inbox = 0, $isBase62 = 0);

    /**
     * 按天返回热门转发榜
     * @param int $count 返回的记录条数，最大不超过50，默认为20。
	 * @param int $base_app 是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
	 * @return array
     */
    public function hotRepostDaily($count = 20, $base_app = 0, $filter_by_type = 0);

    /**
     * 按周返回热门转发榜
     * @param int $count 返回的记录条数，最大不超过50，默认为20。
	 * @param int $base_app 是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
	 * @return array
     */
    public function hotRepostWeekly($count = 20,  $base_app = 0);

    /**
     * 按天返回当前用户关注人的热门微博评论榜
     * @param int $count 返回的记录条数，最大不超过50，默认为20。
	 * @param int $base_app 是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
	 * @return array
     */
    public function hotCommentsDaily($count = 20, $base_app = 0);

    /**
     * 按周返回热门评论榜
     * @param int $count 返回的记录条数，最大不超过50，默认为20。
	 * @param int $base_app 是否只获取当前应用的数据。0为否（所有数据），1为是（仅当前应用），默认为0。
	 * @return array
     */
    public function hotCommentsWeekly($count = 20, $base_app = 0);

    /**
     * 批量获取指定微博的转发数评论数
     * @todo
     */
    public function count();

    /**
     * 转发微博
     * @param int $rt_id 要转发的id
     * @param string $status 添加的转发文本，内容不超过140个汉字，不填则默认为“转发微博”。
     * @param int $is_comment 是否在转发的同时发表评论，0：否、1：评论给当前微博、2：评论给原微博、3：都评论，默认为0 。
     */
    public function rt($rt_id, $status = '', $is_comment = 0);

    /**
     * 删除某条微博
     * @param int $t_id
     */
    public function delete($t_id);

    /**
     * 发微博
     * @param string $status 要更新的微博信息。信息内容不超过140个汉字, 为空返回400错误。
	 * @param float $lat 纬度，发表当前微博所在的地理位置，有效范围 -90.0到+90.0, +表示北纬。可选。
	 * @param float $long 经度。有效范围-180.0到+180.0, +表示东经。可选。
	 * @param mixed $annotations 可选参数。元数据，主要是为了方便第三方应用记录一些适合于自己使用的信息。每条微博可以包含一个或者多个元数据。请以json字串的形式提交，字串长度不超过512个字符，或者数组方式，要求json_encode后字串长度不超过512个字符。具体内容可以自定。例如：'[{"type2":123}, {"a":"b", "c":"d"}]'或array(array("type2"=>123), array("a"=>"b", "c"=>"d"))。
	 * @return array
     */
    public function update($status, $pic_path = '', $lat = '', $long = '');

    /**
     * 发布一条微博同时指定上传的图片或图片url
     * @param string $status  要发布的微博文本内容，内容不超过140个汉字。
	 * @param string $url    图片的URL地址，必须以http开头。
	 * @return array
     */
    public function uploadUrlText($status, $url, $client_ip = '');

    /**
     * 获取表情
     * @param string $type 表情类别。"face":普通表情，"ani"：魔法表情，"cartoon"：动漫表情。默认为"face"。可选。
	 * @param string $language 语言类别，"cnname"简体，"twname"繁体。默认为"cnname"。可选
	 * @return array
     */
    public function getEmotions($type = 'face', $language = "cnname");
}
?>