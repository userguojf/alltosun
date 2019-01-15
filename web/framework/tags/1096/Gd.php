<?php

/**
 * alltosun.com GD处理类 Gd.php
 * ============================================================================
 * 版权所有 (C) 2007-2009 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2009-08-23 20:31:12 +0800 $
 * $Id: Gd.php 1024 2015-08-14 11:14:15Z qianym $
*/

class Gd
{
    public $im     = null;
    public $width  = null;
    public $height = null;
    private $file_type;

    function __construct($data = null)
    {
        if (empty($data)) {
            return true;
        }

        if (file_exists($data) && is_readable($data)) {
            return $this->loadFile($data);
        } elseif (is_resource($data) && 'gd' === get_resource_type($data)) {
            return $this->loadResource($data);
        } else {
            return $this->loadData($data);
        }
    }

    /**
     * 生成一张背景色为$color的图片，作为融图时的底图
     * @param int $width
     * @param int $height
     * @param int $color
     */
    public static function create($width, $height, $color = '#FFFFFF')
    {
        $thumb = imagecreatetruecolor($width, $height);
        $color = ltrim($color, '#');
        if (3 === strlen($color)) {
            $color .= $color;
        }

        $red = $green = $blue = 255;
        sscanf($color, "%2x%2x%2x", $red, $green, $blue);
        $color = imagecolorallocate($thumb, $red, $green, $blue);

        imagefill($thumb, 0, 0, $color);
        imagecolortransparent($thumb, $color);

        return new self($thumb);
    }

    /**
     * 为生成的图片资源初始化一些信息
     * @param resource $im
     */
    public function loadResource($im)
    {
        if (!is_resource($im) || 'gd' !== get_resource_type($im)) {
            return false;
        }

        $this->im     = $im;
        $this->width  = imagesx($im);
        $this->height = imagesy($im);

        return true;
    }

    public function loadData($filedata)
    {
        $im = imagecreatefromstring($filedata);

        return $this->loadResource($im);
    }

    /**
     * 加载外部图片，成生图片资源
     * @param string $filename
     */
    public function loadFile($filename)
    {
        if (!file_exists($filename) || !is_readable($filename)) {
            return false;
        }

        $info = getimagesize($filename);
        $this->file_type = $type = image_type_to_extension($info[2], false);
        if ($type == 'jpeg' && (imagetypes() & IMG_JPG)) {
            $im = imagecreatefromjpeg($filename);
        } elseif ($type == 'png' && (imagetypes() & IMG_PNG)) {
            $im = imagecreatefrompng($filename);
            imagealphablending($im, true);
            // imageSaveAlpha($im, true);
        } elseif ($type == 'gif' && (imagetypes() & IMG_GIF)) {
            $im = imagecreatefromgif($filename);
        } else if ($type =='bmp') {
            $im = $this->imagecreatefrombmp($filename);
        } else {
            return false;
        }

        return $this->loadResource($im);
    }

    /**
     * 缩放图片
     * @param int $new_width
     * @param int $new_height
     */
    public function resize($new_width, $new_height)
    {
        $dest = imagecreatetruecolor($new_width, $new_height);
        // 透明处理
        imagesavealpha($dest, true);
        $trans_color = imagecolorallocatealpha($dest, 0, 0, 0, 127);
        imagefill($dest, 0, 0, $trans_color);

        if (imagecopyresampled($dest, $this->im, 0, 0, 0, 0, $new_width, $new_height, $this->width, $this->height)) {
            return $this->loadResource($dest);
        }

        return false;
    }

    /**
     * 截图(cut模式)：先等比缩放，然后截取
     * @param int $x 横向从什么位置开始截
     * @param int $y 纵向从什么位置开始截
     * @param int $w 指定的要截取的宽度
     * @param int $h 指定的要截取的高度
     */
    public function crop($x, $y, $w, $h)
    {
        $dest = imagecreatetruecolor($w, $h);
        // 透明处理
        imagesavealpha($dest, true);
        $trans_color = imagecolorallocatealpha($dest, 0, 0, 0, 127);
        imagefill($dest, 0, 0, $trans_color);

        if (imagecopyresampled($dest, $this->im, 0, 0, $x, $y, $w, $h, $w, $h)) {
            return $this->loadResource($dest);
        }

        return false;
    }

    public function merge(Gd $thumb, $left, $top)
    {
        imagealphablending($this->im, true);
        imageCopy($this->im, $thumb->im, $left, $top, 0, 0, $thumb->width, $thumb->height);

        return true;
    }

    /**
     * 融图(merge)：把图片资源与指定大图的图片融在一起，使图片最终大小，即为指定大小
     * @param resource $thumb 图片资源
     * @param int $pct
     */
    public function merge_auto(Gd $thumb, $pct = 100)
    {
        $left = round(($this->width - $thumb->width) / 2);
        $top = round(($this->height - $thumb->height) / 2);
        // imagealphablending($thumb->im, true);
        imagecopymerge($this->im, $thumb->im, $left, $top, 0, 0, $thumb->width, $thumb->height, $pct);
    }

    /**
     * 输出图片
     * @param int $type
     * @param int $quality
     */
    public function output($type = "jpg", $quality = 80)
    {
        if ($type == 'jpg' && (imagetypes() & IMG_JPG)) {
            header("Content-Type: image/jpeg");
            imagejpeg($this->im, '', $quality);
            return true;
        } elseif ($type == 'png' && (imagetypes() & IMG_PNG)) {
            header("Content-Type: image/png");
            imagepng($this->im);
            return true;
        } elseif ($type == 'gif' && (imagetypes() & IMG_GIF)) {
            header("Content-Type: image/gif");
            imagegif($this->im);
            return true;
        } else {
            return false;
        }
    }

    /**
     * 保存图片
     * @param string $filename
     * @param string $type
     * @param int $quality
     */
    public function saveAs($filename, $type = "jpg", $quality = 90)
    {
        if ($this->file_type && in_array($this->file_type, array('jpg', 'png', 'gif'))) {
            $type = $this->file_type;
        }
        $dir = dirname($filename);
        if (!file_exists($dir)) {
            @mkdir($dir, 0777, true);
        } if ($type == 'jpg' && (imagetypes() & IMG_JPG)) {
            return imagejpeg($this->im, $filename, $quality);
        } elseif ($type == 'png' && (imagetypes() & IMG_PNG)) {
            // imagepng第三个参数与其它的不同，它是0-9，其中0不压缩，9压的程度最多,(实际测试即使填了9，也压不了多少)
            // imagejpeg第三个参数值是从 0（最差质量，文件更小）到 100（最佳质量，文件最大）。默认为 IJG 默认的质量值（大约 75）
            return imagepng($this->im, $filename, 9);
        } elseif ($type == 'gif' && (imagetypes() & IMG_GIF)) {
            return imagegif($this->im, $filename);
        } else {
            return false;
        }
    }

    /**
     * 确定宽高，调用resize等比缩放图片
     * @param int $new_width
     * @param int $new_height
     */
    public function scale($new_width = null, $new_height = null)
    {
        if (!is_null($new_width) && is_null($new_height)) {
            $new_height = round($new_width * $this->height / $this->width);
        } elseif (is_null($new_width) && !is_null($new_height)) {
            $new_width = $this->width / $this->height * $new_height;
        } elseif(!is_null($new_width) && !is_null($new_height)) {
            $width = round($new_height * $this->width / $this->height);
            if ($width > $new_width) {
                $new_height = round($new_width * $this->height / $this->width);
            } else {
                $new_width = $width;
            }
        } else {
            return false;
        }

        return $this->resize($new_width, $new_height);
    }

    /**
     * 确定宽高，调用resize等等比缩放图片，并调用crop截图
     * @param int $new_width
     * @param int $new_height
     */
    public function scale_fill($new_width = null, $new_height = null)
    {
        if (!is_null($new_width) && is_null($new_height)) {
            $new_height = $new_width;
        } elseif (is_null($new_width) && !is_null($new_height)) {
            $new_width = $new_height;
        }

        $sw = $new_width;
        $sh = $new_height;

        $width = round($new_height * $this->width / $this->height);
        if ($width < $new_width) {
            $new_height = round($new_width * $this->height / $this->width);
        } else {
            $new_width = $width;
        }

        $this->resize($new_width, $new_height);
        $x = ($this->width - $sw) / 2;
        $y = ($this->height - $sh) / 2;

        $this->crop($x, $y, $sw, $sh);

        return true;
    }

    /**
     * 類似GD庫打開圖片, 打開bmp格式圖片
     * $file : 圖片路徑
     */
    function imagecreatefrombmp($file) {
        global $CurrentBit, $echoMode;
        $f = fopen ( $file, "r" );
        $Header = fread ( $f, 2 );
        if ($Header == "BM") {
            $Size = $this->freaddword ( $f );
            $Reserved1 = $this->freadword ( $f );
            $Reserved2 = $this->freadword ( $f );
            $FirstByteOfImage = $this->freaddword ( $f );
            $SizeBITMAPINFOHEADER = $this->freaddword ( $f );
            $Width = $this->freaddword ( $f );
            $Height = $this->freaddword ( $f );
            $biPlanes = $this->freadword ( $f );
            $biBitCount = $this->freadword ( $f );
            $RLECompression = $this->freaddword ( $f );
            $WidthxHeight = $this->freaddword ( $f );
            $biXPelsPerMeter = $this->freaddword ( $f );
            $biYPelsPerMeter = $this->freaddword ( $f );
            $NumberOfPalettesUsed = $this->freaddword ( $f );
            $NumberOfImportantColors = $this->freaddword ( $f );
            if ($biBitCount < 24) {
                $img = imagecreate ( $Width, $Height );
                $Colors = pow ( 2, $biBitCount );
                for($p = 0; $p < $Colors; $p ++) {
                    $B = $this->freadbyte ( $f );
                    $G = $this->freadbyte ( $f );
                    $R = $this->freadbyte ( $f );
                    $Reserved = $this->freadbyte ( $f );
                    $Palette [] = imagecolorallocate ( $img, $R, $G, $B );
                }
                if ($RLECompression == 0) {
                    $Zbytek = (4 - ceil ( ($Width / (8 / $biBitCount)) ) % 4) % 4;
                    for($y = $Height - 1; $y >= 0; $y --) {
                        $CurrentBit = 0;
                        for($x = 0; $x < $Width; $x ++) {
                            $C = $this->freadbits ( $f, $biBitCount );
                            imagesetpixel ( $img, $x, $y, $Palette [$C] );
                        }
                        if ($CurrentBit != 0) {
                            $this->freadbyte ( $f );
                        }
                        for($g = 0; $g < $Zbytek; $g ++) {
                            $this->freadbyte ( $f );
                        }
                    }
                }
            }
            if ($RLECompression == 1)             // $BI_RLE8
            {
                $y = $Height;
                $pocetb = 0;
                while ( true ) {
                    $y --;
                    $prefix = $this->freadbyte ( $f );
                    $suffix = $this->freadbyte ( $f );
                    $pocetb += 2;
                    $echoit = false;
                    if ($echoit) {
                        echo "Prefix: $prefix Suffix: $suffix<BR>";
                    }
                    if (($prefix == 0) && ($suffix == 1)) {
                        break;
                    }
                    if (feof ( $f )) {
                        break;
                    }
                    while ( ! (($prefix == 0) && ($suffix == 0)) ) {
                        if ($prefix == 0) {
                            $pocet = $suffix;
                            $Data .= fread ( $f, $pocet );
                            $pocetb += $pocet;
                            if ($pocetb % 2 == 1) {
                                $this->freadbyte ( $f );
                                $pocetb ++;
                            }
                        }
                        if ($prefix > 0) {
                            $pocet = $prefix;
                            for($r = 0; $r < $pocet; $r ++) {
                                $Data .= chr ( $suffix );
                            }
                        }
                        $prefix = $this->freadbyte ( $f );
                        $suffix = $this->freadbyte ( $f );
                        $pocetb += 2;
                        if ($echoit) {
                            echo "Prefix: $prefix Suffix: $suffix<BR>";
                        }
                    }
                    for($x = 0; $x < strlen ( $Data ); $x ++) {
                        imagesetpixel ( $img, $x, $y, $Palette [ord ( $Data [$x] )] );
                    }
                    $Data = "";
                }
            }
            if ($RLECompression == 2) {
                $y = $Height;
                $pocetb = 0;
                while ( true ) {
                    $y --;
                    $prefix = $this->freadbyte ( $f );
                    $suffix = $this->freadbyte ( $f );
                    $pocetb += 2;
                    $echoit = false;
                    if ($echoit) {
                        echo "Prefix: $prefix Suffix: $suffix<BR>";
                    }
                    if (($prefix == 0) && ($suffix == 1)) {
                        break;
                    }
                    if (feof ( $f )) {
                        break;
                    }
                    while ( ! (($prefix == 0) && ($suffix == 0)) ) {
                        if ($prefix == 0) {
                            $pocet = $suffix;
                            $CurrentBit = 0;
                            for($h = 0; $h < $pocet; $h ++) {
                                $Data .= chr ( $this->freadbits ( $f, 4 ) );
                            }
                            if ($CurrentBit != 0) {
                                $this->freadbits ( $f, 4 );
                            }
                            $pocetb += ceil ( ($pocet / 2) );
                            if ($pocetb % 2 == 1) {
                                $this->freadbyte ( $f );
                                $pocetb ++;
                            }
                        }
                        if ($prefix > 0) {
                            $pocet = $prefix;
                            $i = 0;
                            for($r = 0; $r < $pocet; $r ++) {
                                if ($i % 2 == 0) {
                                    $Data .= chr ( $suffix % 16 );
                                } else {
                                    $Data .= chr ( floor ( $suffix / 16 ) );
                                }
                                $i ++;
                            }
                        }
                        $prefix = $this->freadbyte ( $f );
                        $suffix = $this->freadbyte ( $f );
                        $pocetb += 2;
                        if ($echoit) {
                            echo "Prefix: $prefix Suffix: $suffix<BR>";
                        }
                    }
                    for($x = 0; $x < strlen ( $Data ); $x ++) {
                        imagesetpixel ( $img, $x, $y, $Palette [ord ( $Data [$x] )] );
                    }
                    $Data = "";
                }
            }
            if ($biBitCount == 24) {
                $img = imagecreatetruecolor ( $Width, $Height );
                $Zbytek = $Width % 4;
                for($y = $Height - 1; $y >= 0; $y --) {
                    for($x = 0; $x < $Width; $x ++) {
                        $B = $this->freadbyte ( $f );
                        $G = $this->freadbyte ( $f );
                        $R = $this->freadbyte ( $f );
                        $color = imagecolorexact ( $img, $R, $G, $B );
                        if ($color == - 1) {
                            $color = imagecolorallocate ( $img, $R, $G, $B );
                        }
                        imagesetpixel ( $img, $x, $y, $color );
                    }
                    for($z = 0; $z < $Zbytek; $z ++) {
                        $this->freadbyte ( $f );
                    }
                }
            }
            return $img;
        }
        fclose ( $f );
    }

    function freadbyte($f)
    {
        return ord(fread($f, 1));
    }

    function freadword($f)
    {
        $b1 = $this->freadbyte($f);
        $b2 = $this->freadbyte($f);
        return $b2*256 + $b1;
    }

    function freaddword($f)
    {
        $b1 = $this->freadword($f);
        $b2 = $this->freadword($f);
        return $b2*65536 + $b1;
    }
}
?>