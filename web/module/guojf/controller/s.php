<?php
/**
 * alltosun.com  s.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-5-23 下午5:37:04 $
 * $Id$
 */
class Action {
    // ---------------------------------------
    // 常用排序算法
    // ---------------------------------------
    // 冒泡排序
    public function index() {
        $arr = array (
                3, 5, 8, 4, 9, 6, 2, 7, 1
        );

        $lenth = count($arr);

        for ( $i = 0; $i < $lenth; $i ++ ) {

            for ( $j = $lenth -1; $j > $i; $j -- ) {
                if ( $arr[$j] < $arr[$j -1 ] ) {
                    $tmp = $arr[$j];
                    $arr[$j] = $arr[$j - 1];
                    $arr[$j - 1] = $tmp;
                }
            }
            
        }

        p ( $arr );
    }

    //插入排序
    public function inset()
    {
        $arr = array (
                3, 5, 8, 4, 9, 6, 2, 7, 1
        );

        $length = count($arr);
        if($length <=1){
            return $arr;
        }
        for($i=1;$i<$length;$i++){
            $x = $arr[$i];
            $j = $i-1;
            while($x<$arr[$j] && $j>=0){
                $arr[$j+1] = $arr[$j];
                $j--;
            }
            $arr[$j+1] = $x;
        }
        p($arr);
    }
    
    
    
    
    
    
    
    
    
    

    public function mao() {
        $arr = array (
                3, 5, 8, 4, 9, 6, 2, 7, 1
        );
    
        echo implode ( " ", $arr ) . "<br/>";
    
        $length = count ( $arr );
    
        if ($length <= 1)
            return $arr;
    
        for($i = 0; $i < $length; $i ++) {
    
            for( $j = $length - 1; $j > $i; $j -- ) {
                if ( $arr [$j] < $arr [$j - 1] ) {
                    $tmp = $arr [$j];
                    $arr [$j] = $arr [$j - 1];
                    $arr [$j - 1] = $tmp;
                }
            }
            p($arr);exit();
    
        }
    
        p ( $arr );
    }

}