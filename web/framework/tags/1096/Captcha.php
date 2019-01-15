<?php

/**
 * alltosun.com 验证码类 Captcha.php
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2010-7-23 上午11:31:45 $
 * $Id: Captcha.php 200 2012-04-06 10:40:41Z gaojj $
*/

class Captcha
{
    /**
     * 输出验证码图片
     * @param $captcha_config 验证码配置
     */
    public static function outputImage($captcha_config)
    {
        $img = new Securimage();

        // 默认的字体是相对路径，不能获取，改为绝对路径
        $dir_3rd = AnPHP::$dir_3rd;
        $img->ttf_file = $dir_3rd.'/Securimage 2.0.1 BETA/AHGBold.ttf';

        // 验证码样式设置
        foreach ($captcha_config as $k=>$v) {
            // 为空且不为0时才不赋值，背景图单独处理
            if (empty($v) && $v !== 0 || $k == 'background_image') continue;
            // 颜色处理
            if (stripos($k, 'color') !== false) $v = self::color_handle($v);
            // 设置
            $img->$k = $v;
        }

        // 背景图
        $background_image = '';
        if (!empty($captcha_config['background_image'])) {
            $background_image = $captcha_config['background_image'];
            if (is_array($background_image)) {
                $background_image = array_shift(shuffle($background_image));
            }
        }

        $img->show($background_image);
    }

    /**
     * 处理验证码的颜色
     * @param mixed $color
     * @return Securimage_Color Object
     */
    private static function color_handle($color)
    {
        if (empty($color)) return $color;
        if (is_array($color)) {
            return array_map(array(__CLASS__, 'color_handle'), $color);
        } else {
            return new Securimage_Color($color);
        }
    }

    /**
     * 获取验证码的Html
     * @param int $width
     * @param int $height
     * @return string
     */
    public static function generate($width = null, $height = null)
    {
        $image_src   = "/captcha.png";
        $image_style = "cursor:pointer;";

        if (!empty($width)) {
            $image_src .= "&w=$width";
            $image_style .= " width:{$width}px;";
        }
        if (!empty($height)) {
            $image_src .= "&h=$height";
            $image_style .= " height:{$height}px;";
        }

        $html = <<<CODE
        <img class="captchaImage" src="$image_src" style="$image_style" />
        <span class="captchaRefresh"><a href="javascript:void(0);">看不清楚？换一个</a></span>
        <script type="text/javascript">
        jQuery(function(){
          jQuery(".captchaRefresh").click(function(e){
            e.preventDefault();
            var rand = new Date().getTime();
            jQuery(this).prev("img").attr("src", "$image_src&t="+rand);
            return false;
          });
          jQuery(".captchaImage").click(function(){
            var rand = new Date().getTime();
            jQuery(this).attr("src", "$image_src&t="+rand);
          });
        });
        </script>
CODE;
        return $html;
    }

    /**
     * 校验验证码
     * @param $code
     * @return bool
     */
    public static function check($code)
    {
        $img = new Securimage();
        return $img->check($code);
    }
}
?>