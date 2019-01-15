<?php

/**
 * alltosun.com gravatar 调用头像
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京共创阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2009-08-23 20:31:12 +0800 $
 * $Id: Gravatar.php 203 2012-04-08 14:45:23Z gaojj $
*/

class Gravatar
{
    private static $default_array = array('identicon', 'wavatar', 'monsterid');
    private static $rating_array = array('g', 'pg', 'r', 'x');

    private $size = 80;
    private $rating = 'g';
    private $default = '';
    private $email = '';
    const URL = 'http://www.gravatar.com/avatar/';

    public function __construct($email = '')
    {
        $this->email = md5(trim((string)$email));
    }

    public static function create($email = '')
    {
        return new self($email);
    }

    public function setRating($rating = 'g')
    {
        if (in_array($rating, self::$rating_array))
        {
            $this->rating = $rating;
        }

        return $this;
    }

    public function setDefault($default = '')
    {
        $default = trim($default);
        if (substr($default, 0, 4) == 'http')
        {
            $this->default = urlencode($default);
        }
        elseif (in_array($default, self::$default_array))
        {
            $this->default = $default;
        }

        return $this;
    }

    public function setSize($size = 80)
    {
        $size = intval($size);
        $size < 1 && $size = 1;
        $size > 512 && $size = 512;
        $this->size = $size;

        return $this;
    }

    public function __toString()
    {
        $result = self::URL . $this->email . '?s=' . $this->size . '&r=' .$this->rating;
        $this->default && $result .= '&d=' . $this->default;

        return $result;
    }
}
?>