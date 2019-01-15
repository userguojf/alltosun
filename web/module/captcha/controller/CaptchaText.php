<?
/**
 * alltosun.com 验证码 CaptchaText.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (shenxn@alltosun.com) $
 * $Date: 2014-12-14 下午1:32:07 $
 * $Id$
 */

//////////////////////////////////////////////////////////////////////////
//               巨惠通验证码类，如出现问题，请联系619043894                     //
//////////////////////////////////////////////////////////////////////////

class CaptchaText{
    
    private $captchaText = '';
    private $font_size   = '';
    private $font_file   = '';

    public function __construct()
    {
        $_SESSION['captchaText']     = $this->generateCaptchaTextMarkovClean(5);
        $_SESSION["start_code_time"] = time();

        list($this->font_size, $this->font_file) = array(
               30,STATIC_DIR."/font/3DCaptcha.ttf"
        );
    }

    public function start()
    {
        $fontsize = $this->font_size;
        $fontfile = $this->font_file;

        $details = imagettfbbox($fontsize, 0, $fontfile, $this->captchaText);
        $image2d_x = $details[4] + 4;
        $image2d_y = $fontsize * 1.3;

        $bevel = 4;

        $image2d = imagecreatetruecolor($image2d_x, $image2d_y);
        $black = imagecolorallocate($image2d, 0, 0, 0);
        $white = imagecolorallocate($image2d, 255, 255, 255);

        imagefill($image2d, 0, 0, $black);
        imagettftext($image2d, $fontsize, 0, 2, $fontsize, $white, $fontfile, $this->captchaText);

        $T = $this->cameraTransform(
                array(rand(-90, 90), -200, rand(150, 250)),
                array(0, 0, 0)
        );
        $T = $this->matrixProduct(
                $T,
                $this->viewingTransform(60, 300, 3000)
        );

        $coord = array($image2d_x * $image2d_y);
        $count = 0;
        for ($y = 0; $y < $image2d_y; $y+=2) {
            for ($x = 0; $x < $image2d_x; $x++) {
                $xc = $x - $image2d_x / 2;
                $zc = $y - $image2d_y / 2;
                $yc = -(imagecolorat($image2d, $x, $y) & 0xff) / 256 * $bevel;
                $xyz = array($xc, $yc, $zc, 1);
                $xyz = $this->vectorProduct($xyz, $T);
        
                $coord[$count] = $xyz;
                $count++;
            }
        }

        $image3d_x = 256;
        $image3d_y = $image3d_x * 9 / 16;
        $image3d = imagecreatetruecolor($image3d_x, $image3d_y);
        $fgcolor = imagecolorallocate($image3d, 255, 255, 255);
        $bgcolor = imagecolorallocate($image3d, 0, 0, 0);
        imageantialias($image3d, true);
        imagefill($image3d, 0, 0, $bgcolor);

        $count = 0;
        $scale = 1.75 - $image2d_x/400;
        for ($y = 0; $y < $image2d_y; $y++) {
            for ($x = 0; $x < $image2d_x; $x++) {
                if ($x > 0) {
                    $x0 = @$coord[$count - 1][0] * $scale + $image3d_x / 2;
                    $y0 = @$coord[$count - 1][1] * $scale + $image3d_y / 2;
                    $x1 = @$coord[$count][0] * $scale + $image3d_x / 2;
                    $y1 = @$coord[$count][1] * $scale + $image3d_y / 2;
                    imageline($image3d, $x0, $y0, $x1, $y1, $fgcolor);
                }
                $count++;
            }
        }

        header("Content-type: image/jpeg");
        header("Cache-Control: no-cache, must-revalidate");
        header("Expires: Mon, 03 Apr 1977 11:05:00 GMT");
        imagejpeg($image3d);
    }

    private function generateCaptchaTextMarkov($length) {
        $transitionMatrix = array(
            0.0001, 0.0218, 0.0528, 0.1184, 0.1189, 0.1277, 0.1450, 0.1458, 0.1914, 0.1915, 0.2028, 0.2792, 0.3131, 0.5293, 0.5304, 0.5448, 0.5448, 0.6397, 0.7581, 0.9047, 0.9185, 0.9502, 0.9600, 0.9601, 0.9982, 1.0000,
            0.0893, 0.0950, 0.0950, 0.0950, 0.4471, 0.4471, 0.4471, 0.4471, 0.4784, 0.4821, 0.4821, 0.6075, 0.6078, 0.6078, 0.7300, 0.7300, 0.7300, 0.7979, 0.8220, 0.8296, 0.9342, 0.9348, 0.9351, 0.9351, 1.0000, 1.0000,
            0.1313, 0.1317, 0.1433, 0.1433, 0.3264, 0.3264, 0.3264, 0.4887, 0.5454, 0.5454, 0.5946, 0.6255, 0.6255, 0.6255, 0.8022, 0.8022, 0.8035, 0.8720, 0.8753, 0.9545, 0.9928, 0.9928, 0.9928, 0.9928, 1.0000, 1.0000,
            0.0542, 0.0587, 0.0590, 0.0840, 0.3725, 0.3837, 0.3879, 0.3887, 0.5203, 0.5208, 0.5211, 0.5390, 0.5435, 0.5550, 0.8183, 0.8191, 0.8191, 0.8759, 0.9376, 0.9400, 0.9629, 0.9648, 0.9664, 0.9664, 1.0000, 1.0000,
            0.0860, 0.0877, 0.1111, 0.2533, 0.3017, 0.3125, 0.3183, 0.3211, 0.3350, 0.3355, 0.3378, 0.4042, 0.4381, 0.5655, 0.5727, 0.5842, 0.5852, 0.7817, 0.8718, 0.9191, 0.9201, 0.9530, 0.9652, 0.9792, 0.9998, 1.0000,
            0.1033, 0.1037, 0.1050, 0.1057, 0.2916, 0.3321, 0.3324, 0.3324, 0.4337, 0.4337, 0.4337, 0.4912, 0.4912, 0.4912, 0.7237, 0.7274, 0.7274, 0.8545, 0.8569, 0.9150, 0.9986, 0.9986, 0.9990, 0.9990, 1.0000, 1.0000,
            0.1014, 0.1017, 0.1024, 0.1028, 0.2725, 0.2729, 0.2855, 0.4981, 0.5770, 0.5770, 0.5770, 0.6184, 0.6191, 0.6384, 0.7783, 0.7797, 0.7797, 0.9249, 0.9663, 0.9688, 0.9923, 0.9923, 0.9937, 0.9937, 1.0000, 1.0000,
            0.2577, 0.2579, 0.2580, 0.2581, 0.6967, 0.6970, 0.6970, 0.6970, 0.8648, 0.8648, 0.8650, 0.8661, 0.8667, 0.8670, 0.9397, 0.9397, 0.9397, 0.9509, 0.9533, 0.9855, 0.9926, 0.9926, 0.9929, 0.9929, 1.0000, 1.0000,
            0.0324, 0.0478, 0.0870, 0.1267, 0.1585, 0.1908, 0.2182, 0.2183, 0.2193, 0.2193, 0.2309, 0.2859, 0.3426, 0.6110, 0.6501, 0.6579, 0.6583, 0.6923, 0.8211, 0.9764, 0.9781, 0.9948, 0.9949, 0.9965, 0.9965, 1.0000,
            0.1276, 0.1276, 0.1276, 0.1276, 0.4286, 0.4286, 0.4286, 0.4286, 0.4337, 0.4337, 0.4337, 0.4337, 0.4337, 0.4337, 0.6684, 0.6684, 0.6684, 0.6684, 0.6684, 0.6684, 1.0000, 1.0000, 1.0000, 1.0000, 1.0000, 1.0000,
            0.0033, 0.0059, 0.0100, 0.0109, 0.5401, 0.5443, 0.5477, 0.5485, 0.7149, 0.7149, 0.7149, 0.7316, 0.7333, 0.9247, 0.9264, 0.9273, 0.9273, 0.9289, 0.9791, 0.9816, 0.9824, 0.9824, 0.9833, 0.9833, 1.0000, 1.0000,
            0.0850, 0.0865, 0.0874, 0.1753, 0.3439, 0.3725, 0.3744, 0.3746, 0.5083, 0.5083, 0.5192, 0.6784, 0.6840, 0.6848, 0.8088, 0.8128, 0.8128, 0.8147, 0.8326, 0.8511, 0.8743, 0.8817, 0.9054, 0.9054, 1.0000, 1.0000,
            0.1562, 0.1760, 0.1774, 0.1776, 0.5513, 0.5517, 0.5517, 0.5520, 0.6352, 0.6352, 0.6352, 0.6369, 0.6486, 0.6499, 0.7717, 0.8230, 0.8230, 0.8337, 0.8697, 0.8703, 0.9376, 0.9376, 0.9378, 0.9378, 1.0000, 1.0000,
            0.0255, 0.0265, 0.0682, 0.2986, 0.4139, 0.4204, 0.6002, 0.6009, 0.6351, 0.6360, 0.6507, 0.6672, 0.6679, 0.6786, 0.7718, 0.7723, 0.7732, 0.7873, 0.8364, 0.9715, 0.9753, 0.9797, 0.9803, 0.9804, 0.9997, 1.0000,
            0.0050, 0.0089, 0.0183, 0.0379, 0.0410, 0.1451, 0.1494, 0.1514, 0.1654, 0.1656, 0.1866, 0.2171, 0.2821, 0.4272, 0.4761, 0.4926, 0.4927, 0.6434, 0.6722, 0.7195, 0.9126, 0.9332, 0.9913, 0.9925, 0.9999, 1.0000,
            0.1596, 0.1688, 0.1688, 0.1688, 0.3799, 0.3799, 0.3799, 0.4011, 0.4827, 0.4827, 0.4833, 0.6081, 0.6087, 0.6090, 0.7353, 0.7953, 0.7953, 0.8804, 0.9181, 0.9584, 0.9952, 0.9952, 0.9952, 0.9952, 1.0000, 1.0000,
            0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 0.0000, 1.0000, 1.0000, 1.0000, 1.0000, 1.0000, 1.0000,
            0.0902, 0.0938, 0.1003, 0.1555, 0.4505, 0.4606, 0.4705, 0.4740, 0.5928, 0.5928, 0.6018, 0.6201, 0.6402, 0.6605, 0.7619, 0.7666, 0.7671, 0.8125, 0.8645, 0.9029, 0.9226, 0.9298, 0.9319, 0.9319, 0.9996, 1.0000,
            0.0584, 0.0598, 0.0903, 0.0912, 0.2850, 0.2870, 0.2883, 0.3902, 0.5057, 0.5058, 0.5165, 0.5271, 0.5400, 0.5447, 0.6525, 0.6762, 0.6792, 0.6792, 0.7512, 0.9370, 0.9843, 0.9851, 0.9953, 0.9953, 0.9999, 1.0000,
            0.0416, 0.0419, 0.0466, 0.0467, 0.1673, 0.1696, 0.1697, 0.6314, 0.7003, 0.7003, 0.7003, 0.7142, 0.7150, 0.7160, 0.8626, 0.8626, 0.8627, 0.9023, 0.9255, 0.9498, 0.9746, 0.9746, 0.9812, 0.9812, 0.9998, 1.0000,
            0.0141, 0.0308, 0.0668, 0.0877, 0.1241, 0.1282, 0.1874, 0.1874, 0.2191, 0.2192, 0.2210, 0.3626, 0.3794, 0.4618, 0.4632, 0.5097, 0.5097, 0.6957, 0.8373, 0.9949, 0.9949, 0.9961, 0.9963, 0.9982, 0.9984, 1.0000,
            0.0740, 0.0740, 0.0740, 0.0740, 0.8423, 0.8423, 0.8423, 0.8423, 0.9486, 0.9486, 0.9486, 0.9486, 0.9486, 0.9491, 0.9836, 0.9836, 0.9836, 0.9849, 0.9849, 0.9849, 0.9907, 0.9907, 0.9907, 0.9907, 1.0000, 1.0000,
            0.2785, 0.2789, 0.2795, 0.2823, 0.4088, 0.4118, 0.4118, 0.6070, 0.7774, 0.7774, 0.7782, 0.7840, 0.7840, 0.8334, 0.9704, 0.9704, 0.9704, 0.9861, 0.9996, 1.0000, 1.0000, 1.0000, 1.0000, 1.0000, 1.0000, 1.0000,
            0.0741, 0.0741, 0.1963, 0.1963, 0.2519, 0.2741, 0.2741, 0.3333, 0.4000, 0.4000, 0.4000, 0.4000, 0.4000, 0.4000, 0.4037, 0.6741, 0.7667, 0.7667, 0.7667, 0.9667, 0.9963, 0.9963, 0.9963, 0.9963, 1.0000, 1.0000,
            0.0082, 0.0130, 0.0208, 0.0225, 0.1587, 0.1608, 0.1613, 0.1686, 0.2028, 0.2028, 0.2032, 0.2322, 0.2391, 0.2417, 0.8232, 0.8314, 0.8314, 0.8409, 0.9529, 0.9965, 0.9965, 0.9965, 0.9991, 0.9996, 1.0000, 1.0000,
            0.0678, 0.0678, 0.0763, 0.0763, 0.7373, 0.7373, 0.7373, 0.7458, 0.8729, 0.8729, 0.8729, 0.8814, 0.8814, 0.8814, 0.9237, 0.9237, 0.9237, 0.9237, 0.9237, 0.9407, 0.9492, 0.9492, 0.9492, 0.9492, 0.9492, 1.0000
        );

        $captchaText = '';
        $char = rand(0, 25);

        for ($i = 0; $i < $length; $i++) {
            $captchaText .= chr($char + 65 + 32);

            $next = rand(0, 10000) / 10000;
            for ($j = 0; $j < 26; $j++) {
                if ($next < $transitionMatrix[$char * 26 + $j]) {
                    $char = $j;
                    break;
                }
            }
        }

        return $captchaText;
    }

    private function generateCaptchaTextMarkovClean($length) {
        do {
            $cleanText = $this->generateCaptchaTextMarkov($length);
        } while (
                (preg_match('/anal/', $cleanText) > 0)  ||
                (preg_match('/anus/', $cleanText) > 0)  ||
                (preg_match('/arse/', $cleanText) > 0)  ||
                (preg_match('/bitch/', $cleanText) > 0) ||
                (preg_match('/blow/', $cleanText) > 0)  ||
                (preg_match('/boner/', $cleanText) > 0) ||
                (preg_match('/boob/', $cleanText) > 0)  ||
                (preg_match('/butt/', $cleanText) > 0)  ||
                (preg_match('/clit/', $cleanText) > 0)  ||
                (preg_match('/cock/', $cleanText) > 0)  ||
                (preg_match('/crap/', $cleanText) > 0)  ||
                (preg_match('/cum/', $cleanText) > 0)   ||
                (preg_match('/cunt/', $cleanText) > 0)  ||
                (preg_match('/dick/', $cleanText) > 0)  ||
                (preg_match('/dildo/', $cleanText) > 0) ||
                (preg_match('/edema/', $cleanText) > 0) ||
                (preg_match('/erect/', $cleanText) > 0) ||
                (preg_match('/fag/', $cleanText) > 0)   ||
                (preg_match('/fart/', $cleanText) > 0)  ||
                (preg_match('/fuck/', $cleanText) > 0)  ||
                (preg_match('/hell/', $cleanText) > 0)  ||
                (preg_match('/hore/', $cleanText) > 0)  ||
                (preg_match('/jack/', $cleanText) > 0)  ||
                (preg_match('/jerk/', $cleanText) > 0)  ||
                (preg_match('/niger/', $cleanText) > 0) ||
                (preg_match('/penis/', $cleanText) > 0) ||
                (preg_match('/piss/', $cleanText) > 0)  ||
                (preg_match('/porn/', $cleanText) > 0)  ||
                (preg_match('/prick/', $cleanText) > 0) ||
                (preg_match('/piss/', $cleanText) > 0)  ||
                (preg_match('/pubi/', $cleanText) > 0)  ||
                (preg_match('/pussy/', $cleanText) > 0) ||
                (preg_match('/semen/', $cleanText) > 0) ||
                (preg_match('/sex/', $cleanText) > 0)   ||
                (preg_match('/shag/', $cleanText) > 0)  ||
                (preg_match('/shit/', $cleanText) > 0)  ||
                (preg_match('/slut/', $cleanText) > 0)  ||
                (preg_match('/sperm/', $cleanText) > 0) ||
                (preg_match('/suck/', $cleanText) > 0)  ||
                (preg_match('/tit/', $cleanText) > 0)   ||
                (preg_match('/wank/', $cleanText) > 0)  ||
                (preg_match('/whore/', $cleanText) > 0)
        );

        $this->captchaText = $cleanText;
        return $this->captchaText;
    }

    private function addVector($a, $b) {
        return array($a[0] + $b[0], $a[1] + $b[1], $a[2] + $b[2]);
    }

    private function scalarProduct($vector, $scalar) {
        return array($vector[0] * $scalar, $vector[1] * $scalar, $vector[2] * $scalar);
    }

    private function dotProduct($a, $b) {
        return ($a[0] * $b[0] + $a[1] * $b[1] + $a[2] * $b[2]);
    }

    private function norm($vector) {
        return sqrt($this->dotProduct($vector, $vector));
    }

    private function normalize($vector) {
        return $this->scalarProduct($vector, 1 / $this->norm($vector));
    }

    private function crossProduct($a, $b) {
        return array(
                ($a[1] * $b[2] - $a[2] * $b[1]),
                ($a[2] * $b[0] - $a[0] * $b[2]),
                ($a[0] * $b[1] - $a[1] * $b[0])
        );
    }

    private function vectorProductIndexed($v, $m, $i) {
        return array(
                $v[$i + 0] * $m[0] + $v[$i + 1] * $m[4] + $v[$i + 2] * $m[8] + $v[$i + 3] * $m[12],
                $v[$i + 0] * $m[1] + $v[$i + 1] * $m[5] + $v[$i + 2] * $m[9] + $v[$i + 3] * $m[13],
                $v[$i + 0] * $m[2] + $v[$i + 1] * $m[6] + $v[$i + 2] * $m[10]+ $v[$i + 3] * $m[14],
                $v[$i + 0] * $m[3] + $v[$i + 1] * $m[7] + $v[$i + 2] * $m[11]+ $v[$i + 3] * $m[15]
        );
    }

    private function vectorProduct($v, $m) {
        return $this->vectorProductIndexed($v, $m, 0);
    }

    private function matrixProduct($a, $b) {
        $o1 = $this->vectorProductIndexed($a, $b, 0);
        $o2 = $this->vectorProductIndexed($a, $b, 4);
        $o3 = $this->vectorProductIndexed($a, $b, 8);
        $o4 = $this->vectorProductIndexed($a, $b, 12);

        return array(
                $o1[0], $o1[1], $o1[2], $o1[3],
                $o2[0], $o2[1], $o2[2], $o2[3],
                $o3[0], $o3[1], $o3[2], $o3[3],
                $o4[0], $o4[1], $o4[2], $o4[3]
        );
    }

    private function cameraTransform($C, $A) {
        $w = $this->normalize($this->addVector($C, $this->scalarProduct($A, -1)));
        $y = array(0, 1, 0);
        $u = $this->normalize($this->crossProduct($y, $w));
        $v = $this->crossProduct($w, $u);
        $t = $this->scalarProduct($C, -1);

        return array(
                $u[0], $v[0], $w[0], 0,
                $u[1], $v[1], $w[1], 0,
                $u[2], $v[2], $w[2], 0,
                $this->dotProduct($u, $t), $this->dotProduct($v, $t), $this->dotProduct($w, $t), 1
        );
    }

    private function viewingTransform($fov, $n, $f) {
        $fov *= (M_PI / 180);
        $cot = 1 / tan($fov / 2);

        return array(
                $cot, 0, 0, 0,
                0, $cot, 0, 0,
                0, 0, ($f + $n) / ($f - $n), -1,
                0, 0, 2 * $f * $n / ($f - $n), 0
        );
    }
}
?>
