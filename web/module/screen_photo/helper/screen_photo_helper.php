<?php
/**
 * alltosun.com  screen_photo_helper.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-1-15 下午12:19:54 $
 * $Id$
 */

class screen_photo_helper
{
    
    /**
     * 套餐图合成方法
     * @param unknown $param
     * @return boolean|unknown|Ambigous <string, boolean>
     */
    public static function screen_ps($param)
    {
//         P($param);exit();
        // 判断  下标后需添加
        if ( !is_array($param) ) {
            return false;
        }

        $file_info = pathinfo($param['res_link']);
        $type      = $file_info['extension'];

        // 背景模版
        switch($type){
            case 'jpg':
                $image = imagecreatefromjpeg(_image($param['res_link']));
                break;
            case 'png':
                $image = imagecreatefrompng(_image($param['res_link']));
                break;
            default:
                exit("请上传规定的图片类型");
                break;
        }
        $list = [];
        // 获取模板的长宽
        $image_x   = imagesx($image); //720
        $image_y   = imagesy($image); //1280

        // 设置字体颜色 现在默认白色
        $font_color = screen_config::$screen_color_type[2];
        // 每处的字体大小
        // 机型
        $phone_font_size = 75;
        $phone_version_font_size = 60;
        // 零售价
        $retail_font_size = 35;
        // 合约价价
        $contract_font_size = 45;
        // 推荐合约档次
        $recommended_position_font_size = 35;

        // 屏幕相机电池
        $sell_font_size = 24;

        // 底部解说
        $bottom_font_size = 20;

        $price_unit = '元';
        // 字体文件 目前一个
        $font_file = STATIC_DIR."/font/Adobe-block.otf";

        // ImageTTFBBox函数返回一个包围着 TrueType 文本范围的虚拟方框的像素大小
        // 机型信息
        $phone_name_version = $param['phone_name']. ' ' .$param['phone_version'];
        $phone_area = ImageTTFBBox($phone_font_size, 0, $font_file, $phone_name_version);

        if ( $image_x - $phone_area[2] - $phone_area[0] >  200 ) {
            $phone_x = ( $image_x - $phone_area[2] - $phone_area[0] ) / 2;
            $list[] = array($phone_font_size, 0, $phone_x, 350, $phone_name_version);
        } else {
            $name_area = ImageTTFBBox($phone_font_size, 0, $font_file, $param['phone_name']);
            $version_area = ImageTTFBBox($phone_version_font_size, 0, $font_file, $param['phone_version']);
            $name_x    = ( $image_x - $name_area[2] - $name_area[0] ) / 2;
            $version_x = ( $image_x - $version_area[2] - $version_area[0] ) / 2;
            $list[] = array($phone_font_size, 0, $name_x, 280, $param['phone_name']);
            $list[] = array($phone_version_font_size, 0, $version_x, 400, $param['phone_version']);
        }

        $price_word_str = '';

        /////
        if ( isset($param['retail_price']) && $param['retail_price'] ) {
            $price_word_str .= '零售价';
        }
        if (isset($param['recommended_position']) && $param['recommended_position']) {
            $price_word_str .= '              推荐套餐';
        }

        $price_word_font_size = 30;
        $price_word_area = ImageTTFBBox($price_word_font_size, 0, $font_file, $price_word_str);

        $price_word_x    = ( $image_x - $price_word_area[2] - $price_word_area[0] ) / 2;

        if ( isset($param['recommended_position'] ) && $param['recommended_position'] ) {
            $word_x = 140;
        } else {
            $word_x = $price_word_x;
        }
        
        $word_y = 560;
        $list[]  = array($price_word_font_size, 0, $word_x, $word_y, $price_word_str);
        /////

        $price_str = '';

        if ( isset($param['retail_price']) && $param['retail_price'] ) {
            $price_str .= $param['retail_price'].'元';
        }
        if (isset($param['recommended_position']) && $param['recommended_position']) {
            $price_str .= '      ' . $param['recommended_position'];
        }

        $price_font_size = 40;
        $price_area = ImageTTFBBox($price_font_size, 0, $font_file, $price_str);

        $price_x    = ( $image_x - $price_area[2] - $price_area[0] ) / 2;

        $price_y    = 640;
        $list[]     = array($price_font_size, 0, $price_x, $price_y, $price_str);

        // 买点第一行信息
        if ( $param['selling_point_1'] ) {
            $selling_1_center_x   = 120;
            $selling_point_1      = $param['selling_point_1'];
            $selling_point_1_area = ImageTTFBBox($sell_font_size, 0, $font_file, $selling_point_1);
            $selling_point_1_x    = $selling_1_center_x - ( $selling_point_1_area[2] - $selling_point_1_area[0] ) / 2;
            $selling_point_1_y    = 930;
            $list[]  = array($sell_font_size, 0, $selling_point_1_x, $selling_point_1_y, $selling_point_1);
        }

        if ( $param['selling_point_3'] ) {
            $selling_3_center_x   = 360;
            $selling_point_3      = $param['selling_point_3'];
            $selling_point_3_area = ImageTTFBBox($sell_font_size, 0, $font_file, $selling_point_3);
            $selling_point_3_x    = $selling_3_center_x - ( $selling_point_3_area[2] - $selling_point_3_area[0] ) / 2;
            $selling_point_3_y    = 930;
            $list[]  = array($sell_font_size, 0, $selling_point_3_x, $selling_point_3_y, $selling_point_3);
        }

        if ( $param['selling_point_5'] ) {
            $selling_5_center_x   = 600;
            $selling_point_5      = $param['selling_point_5'];
            $selling_point_5_area = ImageTTFBBox($sell_font_size, 0, $font_file, $selling_point_5);
            $selling_point_5_x    = $selling_5_center_x - ( $selling_point_5_area[2]- $selling_point_5_area[0] ) / 2;
            $selling_point_5_y    = 930;
            $list[]  = array($sell_font_size, 0, $selling_point_5_x, $selling_point_5_y, $selling_point_5);
        }

        if ( $param['selling_point_2'] ) {
            $selling_2_center_x   = 120;
            $selling_point_2      = $param['selling_point_2'];
            $selling_point_2_area = ImageTTFBBox($sell_font_size, 0, $font_file, $selling_point_2);
            $selling_point_2_x    = $selling_2_center_x - ( $selling_point_2_area[2] - $selling_point_2_area[0] ) / 2;
            $selling_point_2_y    = 970;
            $list[]  = array($sell_font_size, 0, $selling_point_2_x, $selling_point_2_y, $selling_point_2);
        }

        if ( $param['selling_point_4'] ) {
            $selling_4_center_x   = 360;
            $selling_point_4      = $param['selling_point_4'];
            $selling_point_4_area = ImageTTFBBox($sell_font_size, 0, $font_file, $selling_point_4);
            $selling_point_4_x    = $selling_4_center_x - ( $selling_point_4_area[2] - $selling_point_4_area[0] ) / 2;
            $selling_point_4_y    = 970;
            $list[]  = array($sell_font_size, 0, $selling_point_4_x, $selling_point_4_y, $selling_point_4);
        }

        if ( $param['selling_point_6'] ) {
            $selling_6_center_x   = 600;
            $selling_point_6      = $param['selling_point_6'];
            $selling_point_6_area = ImageTTFBBox($sell_font_size, 0, $font_file, $selling_point_6);
            $selling_point_6_x    = $selling_6_center_x - ( $selling_point_5_area[2]- $selling_point_6_area[0] ) / 2;
            $selling_point_6_y    = 970;
            $list[]  = array($sell_font_size, 0, $selling_point_6_x, $selling_point_6_y, $selling_point_6);
        }

        // 底部信息1
        $botton_1 = $botton_2 = '';

        if ( $param['param_1'] ) {
            $botton_1 = $param['param_1'];
        } 
        if ( $param['param_2'] ) {
            $botton_1 .= '  |  '.$param['param_2'];
        }
        if ( $param['param_3'] ) {
            $botton_1 .= '  |  '.$param['param_3'];
        }

        if ( $botton_1 ) {
            $botton_1_area = ImageTTFBBox($bottom_font_size, 0, $font_file, $botton_1);
            $botton_1_x    = ( $image_x - $botton_1_area[2] - $botton_1_area[0] ) / 2;
            $botton_1_y    = 1050;
            $list[]  = array($bottom_font_size, 0, $botton_1_x, $botton_1_y, $botton_1);
        }

         if ( $param['param_4'] ) {
            $botton_2 = $param['param_4'];
        } 
        if ( $param['param_5'] ) {
            $botton_2 .= '  |  '.$param['param_5'];
        }
        if ( $param['param_6'] ) {
            $botton_2 .= '  |  '.$param['param_6'];
        }
        // 底部信息2
        if ( $botton_2 ) {
            $botton_2_area = ImageTTFBBox($bottom_font_size, 0, $font_file, $botton_2);
            $botton_2_x    = ( $image_x - $botton_2_area[2] - $botton_2_area[0] ) / 2;
            $botton_2_y    = 1100;
            $list[]  = array($bottom_font_size, 0, $botton_2_x, $botton_2_y, $botton_2);
        }
        
        // 为一幅图像分配颜色
        $image_color  = ImageColorAllocate($image, $font_color[0], $font_color[1], $font_color[2]);

        foreach ( $list as $k => $v ) {
            // 写字
            imagettftext($image, $v[0], $v[1], $v[2], $v[3], $image_color, $font_file, $v[4]);
        }

        ob_start();
        //将带有文字的图片保存到文件
        switch($type){
            case 'jpg':
                //背景模版
                $result = imagejpeg($image, null, 75);
                header('Content-Type: image/jpeg');

//         imagejpeg($image);
//         imagedestroy($image);
//         exit();

                break;
            case 'png':
                $result = imagepng($image);
                header('Content-Type: image/png');

//         imagejpeg($image);
//         imagedestroy($image);
//         exit();

                break;
            default:
                exit("请上传规定的图片类型");
                break;
        }


        imagedestroy($image);
        $ob_image = ob_get_contents();

        ob_clean();

        //二进制图片转链接
        $link =  tools_helper::save_binary_image($param['res_link'], $ob_image);

//         if ($link) {
//             screen_helper::set_screen_show_pic_cache($hash, $link);
//         }

        return $link;
    }
}