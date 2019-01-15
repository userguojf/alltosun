<?php
/**
  * alltosun.com 亮屏内容helper screen_content_helper.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年10月17日 下午4:29:19 $
  * $Id$
  */
class screen_content_helper
{
    /**
     * 获取内容详情
     * @param unknown $screen_content_id
     * @param string $field
     * @return boolean
     */
    public static function get_content_info($screen_content_id, $field=''){
        if (!$screen_content_id) {
            return false;
        }

        if ($field) {
            return _uri('screen_content', $screen_content_id, $field);
        } else {
            return _uri('screen_content', $screen_content_id);
        }
    }

    /**
     * 获取搜索省市地区的 id
     * @param str $name
     * @param int $id
     * return screen_content_ids
     */
    public static function get_search_ids($name , $id , $table)
    {
        if (!$name || !$id || !$table) {
            return false;
        }
        //拼接赋值
        $name_id   = $name.'_id';
        $table_id  = $table.'_id';
        $table_res = $table.'_res';

        $data_ids = _model($table_res)->getFields($table_id , array("$name_id" => $id , 'ranks >' => 1));

        if ($data_ids) {
            return $data_ids;
        }
        return false;
    }

    /**
     * 传入id获取省市(地区)
     * @param int $id
     * @param str $province or city or area
     * @param str $table
     * return province or city or area
     */
    public static function get_name($content_info , $name, $table='')
    {
        if (!$content_info || !$name) {
            return '';
        }

        if ($content_info['res_name'] == 'group') {
            return '全国';
        } else {

            $info = business_hall_helper::get_info_name($content_info['res_name'], $content_info['res_id']);
            if (!$info) {
                return '';
            }

            if ($name == $content_info['res_name']) {
                if ($content_info['res_name'] == 'business_hall') {
                    return $info['title'];
                }
                return $info['name'];
            }

            if (!empty($info[$name.'_id'])) {
               return _uri($name, $info[$name.'_id'], 'name');
            }
        }

        return '';
    }

    /**
     * 获取搜索营业厅的 id
     * @param str $business_hall
     * return screen_content_ids
     */
    public static function get_search_business($search_name , $table)
    {
        if (!$search_name || !$table) {
            return false;
        }
        //营业厅id
        $id = _uri('business_hall',array('title' => $search_name), 'id');
        if (!$id) {
            return false;
        }
        //拼接赋值
        $table_res = $table.'_res';
        $table_id  = $table.'_id';

        $data_ids = _model($table_res)->getFields($table_id , array('res_id' => $id , 'ranks >' => 1));
        if ($data_ids) {
            return $data_ids;
        }
        return false;
    }

    /**
     * 获取营业厅
     * @param int $screen_content_id
     * @param str $business_hall
     * return business_hall
     */
    public static function get_business_hall_name($id , $name , $table='')
    {
        if (!$id || !$name || !$table) {
            return false;
        }
        //拼接赋值
        $table_res = $table.'_res';
        $table_id  = 'content_id';

        $data_info = _uri($table_res , array("$table_id" => $id));

        if ($data_info['res_name'] == $name) {

            $business_hall_name = _uri($name , array('id' => $data_info['res_id']) , 'title');
        }

        if ($business_hall_name) {
            return $business_hall_name;
        }

        return "全营业厅";
    }


    /**
     * 查询本焦点图是否成功投放
     * @param int $screen_content_id
     * @param str $res_name
     * @param int $res_id
     */
    public static function is_content_put($screen_content_id , $res_name, $res_id = 0,  $device_info=array(), $table='screen_content_res')
    {
        //
        if (!$screen_content_id || !$res_name) {
            return true;
        }

        $filter['content_id']       = $screen_content_id;
        $filter['res_name']         = $res_name;
        $filter['res_id']           = $res_id;

        if (isset($device_info['phone_name']) && isset($device_info['phone_version'])) {
            $filter['phone_name'] = $device_info['phone_name'];
            $filter['phone_version'] = $device_info['phone_version'];
        }

        $info = _uri($table, $filter);

        if (!$info) {
            return false;
        }

        return true;
    }

    /**
     * 投放指定范围
     */
    public static function put_group_by_id($screen_content_id, $res_name ,$res_id, $ranks, $table='screen_content_res')
    {
        //$screen_content_id
        if (!$screen_content_id || !$res_name || !$ranks) {
            return false;
        }

        $filter = array('content_id' => $screen_content_id);

        //删除已经投放的记录
        _model($table)->delete($filter);

        //重新全国投放
        $filter['res_name']    = $res_name;
        $filter['res_id']      = $res_id;
        $filter['ranks']       = $ranks;

        $info = _uri($res_name ,$res_id);

        if ($ranks == 2) {
            $filter['province_id'] = $info['id'];
        } else if ($ranks > 2) {
            $filter['province_id'] = $info['province_id'];;
        }

        if ( $ranks == 3)  {
            $filter['city_id'] = $info['id'];
        } else if ($ranks > 3) {
            $filter['city_id'] = $info['city_id'];;
        }

        if ( $ranks == 4) {
            $filter['area_id'] = $info['id'];
        } else if ($ranks > 4) {
            $filter['area_id'] = $info['area_id'];;
        }

        //wangjf add: 添加发布者
        if ($table='screen_content_res') {
            $member_info = member_helper::get_member_info();
            if ($member_info) {
                $filter['issuer_res_name']  = $member_info['res_name'];
                $filter['issuer_res_id']    = $member_info['res_id'];
            }

        }

        _model($table)->create($filter);

        return true;
    }

    /**
     * @param string  $phone_name    第一行字（手机品牌）
     * @param string  $phone_version 第二行字（手机型号）
     * @param string  $image_url     图片地址
     * @param int     $color_type    字体颜色 （1黑色，2白色）
     * @return string $link          压图的地址
     */
    public static function compose_phone_model_image($phone_name, $phone_version, $image_url, $color_type, $price, $title = 'RMB')
    {
        if (!$phone_name || !$phone_version || !$image_url || !$color_type) {
            return false;
        }

        $hash = self::generate_show_pic_hash($image_url, $phone_name, $phone_version, $color_type, $price);
        $link = self::get_screen_show_pic_cache($hash);

        if ($link) {
            return $link;
        }

        if (!array_key_exists($color_type, screen_config::$screen_color_type)) {
            return false;
        }

        $file_info = pathinfo($image_url);
        $type      = $file_info['extension'];

        switch($type){
            case 'jpg':
                //背景模版
                $image = imagecreatefromjpeg(_image($image_url));
                break;
            case 'png':
                $image = imagecreatefrompng(_image($image_url));
                break;
            default:
                exit("请上传规定的图片类型");
                break;
        }

        $image_x   = imagesx($image); //720
        //$image_y   = imagesy($image); //1280
        $font_size = 60;
        $font_file = STATIC_DIR."/font/Adobe-block.otf";

        $phone_name_area    = ImageTTFBBox($font_size, 0, $font_file, $phone_name);
        $phone_version_area = ImageTTFBBox($font_size, 0, $font_file, $phone_version);

        $name_x    = ( $image_x - $phone_name_area[2] ) / 2;
        $version_x = ( $image_x - $phone_version_area[2] ) / 2;

        $image_conf  = array(
                'pink_color'    => screen_config::$screen_color_type[$color_type],
                'font_file'     => $font_file,
                'phone_name'    => array($font_size, 0, $name_x, 300),
                'phone_version' => array($font_size, 0, $version_x, 400)
        );

        $pink  = ImageColorAllocate($image,$image_conf['pink_color'][0],$image_conf['pink_color'][1],$image_conf['pink_color'][2]);

        //手机品牌
        /**
         * 参数说明：
         * 返回的图象资源
         * 字体的尺寸根据 GD 的版本，为像素尺寸（GD1）或点（磅）尺寸（GD2）
         * 角度制表示的角度，0 度为从左向右读的文本。更高数值表示逆时针旋转。例如 90 度表示从下向上读的文本
         * 由 x，y 所表示的坐标定义了第一个字符的基本点（大概是字符的左下角）。这和 imagestring() 不同，其 x，y 定义了第一个字符的左上角。例如 "top left" 为 0, 0。
         * Y 坐标。它设定了字体基线的位置，不是字符的最底端。
         * 颜色索引。使用负的颜色索引值具有关闭防锯齿的效果。见 imagecolorallocate()。
         * 是想要使用的 TrueType 字体的路径。
         * UTF-8 编码的文本字符串。
         */
        imagettftext($image,$image_conf['phone_name'][0],$image_conf['phone_name'][1],$image_conf['phone_name'][2],$image_conf['phone_name'][3],$pink,$image_conf['font_file'], $phone_name);//写文字
        imagettftext($image,$image_conf['phone_version'][0],$image_conf['phone_version'][1],$image_conf['phone_version'][2],$image_conf['phone_version'][3],$pink,$image_conf['font_file'], $phone_version);//写文字

        //合成价格
        if ($price) {
            $price_image_conf  = array(
                    'pink_color'  => screen_config::$screen_color_type[$color_type],
                    'font_file'   => STATIC_DIR."/font/Avenir-Medium.otf",
                    'title'       => array(25, 0, 235, 650),
                    'price'       => array(50, 0, 330, 650)
            );

            imagettftext($image,$price_image_conf['price'][0],$price_image_conf['price'][1],$price_image_conf['price'][2],$price_image_conf['price'][3],$pink,$price_image_conf['font_file'],$price);//写文字
            imagettftext($image,$price_image_conf['title'][0],$price_image_conf['title'][1],$price_image_conf['title'][2],$price_image_conf['title'][3],$pink,$price_image_conf['font_file'],$title);//写文字
        }

        ob_start();

        //将带有文字的图片保存到文件
        switch($type){
            case 'jpg':
                //背景模版
                $result = imagejpeg($image,null,100);
//                     header('Content-Type: image/jpeg');
//                     imagejpeg($image);
//                     imagedestroy($image);
//                     exit();
                break;
            case 'png':
                $result = imagepng($image);
//                     header('Content-Type: image/png');
//                     imagejpeg($image);
//                     imagedestroy($image);
//                     exit();
                break;
            default:
                exit("请上传规定的图片类型");
                break;
        }


        imagedestroy($image);
        $ob_image = ob_get_contents();

        ob_clean();

        //二进制图片转链接
        $link =  tools_helper::save_binary_image($image_url, $ob_image);

        if ($link) {
            screen_helper::set_screen_show_pic_cache($hash, $link);
        }

        return $link;
    }
    /**
     *
     * @param unknown $image_url
     * @param unknown $phone_name
     * @param unknown $phone_version
     * @param unknown $color_type
     * @return string
     */
    public static function generate_show_pic_hash($image_url, $phone_name, $phone_version, $color_type, $price)
    {
        return md5($image_url.$phone_name.$phone_version.$color_type.$price);
    }

    /**
     * 设置营业厅机器图片缓存
     * @return boolean|string
     */
    public static function set_screen_show_pic_cache($hash , $link)
    {
        if (!$hash || !$link) {
            return false;
        }

        $pic_cache_info = _model('screen_show_pic_cache')->read(array('hash' => $hash));

        if ($pic_cache_info) {
            _model('screen_show_pic_cache')->update($pic_cache_info['id'], array('link' => $link));
        } else {
            _model('screen_show_pic_cache')->create(
            array('hash' => $hash, 'link' => $link)
            );
        }

        return true;
    }

    /**
     * 获取营业厅机型图片缓存
     * @return boolean|string
     */
    public static function get_screen_show_pic_cache($hash)
    {
        if (!$hash) {
            return false;
        }

        return  _uri('screen_show_pic_cache', array('hash' => $hash), 'link');
    }

    /**
     * 设备轮播展示的机型宣传图方法
     * @param unknown $device_unique_id
     * @param unknown $content_info
     */
    public static function app_screen_roll_get_image( $device_unique_id, $content_info )
    {
        $device_info = _model('screen_device')->read(array('device_unique_id' => $device_unique_id));

        if ( !$device_info ) return false;

        $phone_name      = $device_info['phone_name_nickname'] ? $device_info['phone_name_nickname'] : $device_info['phone_name'];
        $phone_version   = $device_info['phone_version_nickname'] ? $device_info['phone_version_nickname'] : $device_info['phone_version'];
        $font_color_type = $content_info['font_color_type'] ? $content_info['font_color_type'] : 2 ;

        //查看各个营业厅单独操作的数据
        $show_pic_info = _model('screen_show_pic')->read(array('device_unique_id' => $device_info['device_unique_id']));

        //宣传过并且也是同一个内容
//         if ( $show_pic_info && $show_pic_info['content_id'] == $content_info['id'] ) {
//             return $show_pic_info['link'];
//         }

        // 是不是单独更改过
        if ( $show_pic_info ) {
            // 是同一条数据
            if ( $show_pic_info['content_id'] == $content_info['id'] ) {
                // 管理平台修改一个字段都要改（防止误操作）
                if ( $show_pic_info['content_link'] == $content_info['link'] && $show_pic_info['font_color_type'] == $font_color_type && $show_pic_info['is_specify'] == $content_info['is_specify'] ) {
                    return $show_pic_info['link'];
                } else {
                    return self::image_change($phone_name, $phone_version, $font_color_type, $show_pic_info, $content_info, $device_info);
                }

            // 是不是同一条数据
            } else {
                return self::image_change($phone_name, $phone_version, $font_color_type, $show_pic_info, $content_info, $device_info);
            }

        } else if ( $content_info['new_link'] ) {
            return $content_info['new_link'] ;

        } else {
             if ( $content_info['is_specify'] ) {
                 return $content_info['link'] ;
             } else {
                 return self::compose_phone_model_image($phone_name, $phone_version, $content_info['link'], $font_color_type, $device_info['price'], 'RMB');
             }
        }
    }

    /**
     * 代码块 show_pic有过图 图片改变的情况方法
     * @param unknown $phone_name
     * @param unknown $phone_version
     * @param unknown $font_color_type
     * @param unknown $show_pic_info
     * @param unknown $content_info
     * @param unknown $device_info
     * @return unknown|Ambigous <string, boolean, multitype:, unknown, Obj, mixed, ModelRes, Model>
     */
    public static function image_change( $phone_name, $phone_version, $font_color_type, $show_pic_info, $content_info, $device_info )
    {
        $image_new_link = '';

        // 是专属机型图
        if ( $content_info['is_specify'] ) {
            // 是否有原有价格
            if ( $device_info['price'] ) {
                $title = 'RMB'; // 兼容机型验证
            } else {
                $title = '';
            }
            $image_new_link = screen_helper::compose_screen_image($content_info['link'] , $device_info['price'] , $font_color_type , $title);
        // 不是是专属机型图
        } else {
            // 标题和价格
            $image_new_link = self::compose_phone_model_image($phone_name, $phone_version, $content_info['link'], $font_color_type, $device_info['price'], 'RMB');
        }

        if ( !$image_new_link ) return $show_pic_info['link'];

        // 因为show_pic有过图
        _model('screen_show_pic')->update(
            $show_pic_info['id'],
                array(
                    'content_link'    => $content_info['link'],
                    'font_color_type' => $font_color_type,
                    'is_specify'      => $content_info['is_specify'],
                    'link'            => $image_new_link,
                    'content_id'      => $content_info['id']
            )
        );

        return $image_new_link;
    }

    /**
     * 验证图片是否为动态图片(动画)
     * @param unknown $filename
     */
    public static function is_animated_gif($filename) {

        if (pathinfo($filename, PATHINFO_EXTENSION) != 'gif') {
            return false;
        }

        $fp=fopen($filename,'rb');
        $filecontent=fread($fp,filesize($filename));
        fclose($fp);

        if (strpos($filecontent,chr(0x21).chr(0xff).chr(0x0b).'NETSCAPE2.0')===false) {
            return false;
        }

        return true;
    }

    /**
     * 获取套餐图
     * @param unknown $device_info 设备详情
     * @param unknown $link 底图链接
     */
    public static function get_set_meal($device_info, $content_info)
    {
        $filter = array(
                'status'        => 1,
                'phone_name'    => $device_info['phone_name_nickname'],
                'phone_version' => $device_info['phone_version_nickname'],
                'res_link'      => $content_info['link'],
                'content_id'    => $content_info['id']
        );

        return _model('screen_content_set_meal')->read($filter, ' ORDER BY `id` DESC LIMIT 1 ');
    }

    /**
     * 获取内容套餐列表
     */
    public static function get_content_set_meal($content_info)
    {
        $filter = array(
                'content_id' => $content_info['id'],
                'res_link'  => $content_info['link'],
                'status <'    => 2
        );

        return _model('screen_content_set_meal')->getList($filter);
    }

    /**
     * 根据内容获取内容的html元素
     */
    public static function get_content_html_by_type($content_info, $width=100)
    {
        if (in_array($content_info['type'], array(1, 4))) {
//             if ($content_info['new_link']) {
//                 return '<a href="'._image($content_info['new_link']).'" target="_blank"><img width="'.$width.'" src="'._image($content_info.link).'"></a>';
//             } else {
                return '<a href="'._image($content_info['link'], 'middle').'" target="_blank"><img width="'.$width.'" src="'._image($content_info['link'], 'middle').'"></a>';
//            }
        } else if ($content_info['type'] == 5) {
            return '<a href="'.$content_info['link'].'" target="_blank"><img width="'.$width.'" src="'.$content_info['link'].'"></a>';
        } else if ($content_info['type'] == 2) {

            return '<video style="width:'.$width.'px;">
                <source src="/upload/video/'.$content_info['link'].'" type="video/mp4"></source>
            <video>';
        } else if ($content_info['type'] == 3) {
            return '<a href="'.$v['link'].'">点击跳转</a>';
        }
    }

    /**
     * 获取内容发布地区
     */
    public static function get_content_put_region($content_id)
    {
        $content_res_list = _model('screen_content_res')->getList(array('content_id' => $content_id));

        $new_list = array();
        foreach ($content_res_list as $k => $v) {

            $tmp = array(
                    'group'    => '',
                    'province' => '',
                    'city'     => '',
                    'area'     => '',
                    'business_hall' => '',
                    'phone_name' => '',
                    'phone_version' => ''
            );

            if ($v['province_id']) {
                $tmp['province'] = business_hall_helper::get_info_name('province', $v['province_id'], 'name');
            }

            if ($v['city_id']) {
                $tmp['city'] = business_hall_helper::get_info_name('city', $v['city_id'], 'name');
            }

            if ($v['area_id']) {
                $tmp['area'] = business_hall_helper::get_info_name('area', $v['area_id'], 'name');
            }

            if ($v['res_name'] == 'business_hall') {
                $tmp['business_hall'] = business_hall_helper::get_info_name('business_hall', $v['res_id'], 'title');
            }

            if ($v['phone_name'] == 'all') {
                $tmp['phone_name'] = '全部品牌';
            } else if ($v['phone_name']) {
                $phone_name = screen_device_helper::get_device_nickname_info(array('phone_name' => $v['phone_name']), 'name_nickname');
                if ($phone_name) {
                    $tmp['phone_name'] = $phone_name;
                } else {
                    $tmp['phone_name'] = $v['phone_name'];
                }
            }

            if ($v['phone_version'] == 'all') {
                $tmp['phone_version'] = '全部型号';
            } else {
                $phone_version = screen_device_helper::get_device_nickname_info(array('phone_version' => $v['phone_version']), 'version_nickname');
                if ($phone_version) {
                    $tmp['phone_version'] = $phone_version;
                } else {
                    $tmp['phone_version'] = $v['phone_version'];
                }
            }

            //全国
            if (!$v['province_id']) {
                $tmp['group'] = 'group';
            }
            $new_list[] = $tmp;
        }

        return $new_list;
    }

    /**
     * 获取内容关联列表
     * @param unknown $filter
     */
    public static function get_content_res_list($filter)
    {
        if (!$filter) {
            $filter = array(1=>1);
        }

        return _model('screen_content_res')->getList($filter);
    }

    /**
     * 删除内容关联
     * @param unknown $filter
     */
    public static function delete_content_res($filter)
    {
        if (!$filter) {
            return true;
        }

        _model('screen_content_res')->delete($filter);

        return true;
    }
}