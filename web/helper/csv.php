<?php
/**
 * alltosun.com csv导出类 csv.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Shenxn 申小宁 (shenxn@alltosun.com) $
 * $Date: Jul 18, 2014 12:49:05 PM $
 * $Id: csv.php 125095 2014-07-18 07:19:33Z shenxn $
 */

/**
 * 调用方式 Csv::getCvsObj($params)->export()
 * 调用方式 Csv::getCvsObj()->export($params)
 */
class Csv {

    private $fileName;
    private $fp;
    private $head;
    private $data;
    public  static $cvsObj = '';

    public function __construct($params) {
        if ($params) $this->addParams($params);
    }

    public static function getCvsObj($params = array())
    {
        if (empty(self::$cvsObj)) {
            self::$cvsObj = new self($params);
        }

        return self::$cvsObj;
    }

    public function addParams($params)
    {
        if (isset($params['head']) && $params['head']) {
            $this->head = $params['head'];
        }

        if (isset($params['filename']) && $params['filename']) {
            $prefixs = $params['filename'];
        } else {
            $prefixs = date('Y/m/d');
        }

        $this->fileName = $prefixs.'.csv';

        if (isset($params['data']) && $params['data']) {
            $this->data = $params['data'];
        }
    }

    public function checkData()
    {
        //检查列名变量是否为空
        if(empty($this->head)){
            throw new Exception("excel表格没有设定列名");
        }
        //检查列名变量是否为数组
        if(!is_array($this->head)){
            throw new Exception("列名变量必须是个数组");
        }

        //检查变量是否为空
        if(empty($this->data)){
            throw new Exception("下载的数据为空");
        }

        //检查列名变量是否为数组
        if(!is_array($this->data)){
            throw new Exception("下载的数据必须是个数组");
        }
    }

    public function export($params = array())
    {
        if ($params) $this->addParams($params);

        //设置输出excel文件头
        header('Content-Type: application/vnd.ms-excel');
        header('Content-Disposition: attachment;filename="'.$this->fileName.'"');
        header('Cache-Control: max-age=0');
        $this->fp = fopen('php://output','a');

        //检测数据
        $this->checkData();

        //写标题行
        $this->writeHead();

        //写内容
        $this->writeData();
        exit;
    }

    //写标题行
    private  function writeHead()
    {
        $head = $this->head;
        //编码转化
        $head = $this->utf8ToGbk($head);
        $this->head = $head;
        fputcsv($this->fp,$this->head);
    }

    //写内容行
    private function writeData()
    {
        $data = $this->data;
        //设置一个计数器防止由于数据过大造成数据阻塞
        $count = 0;
        //每隔$limit清空一次buffer
        $limit = 5000;
        //着行写入内容信息
        foreach($data as $key=>$value){
            //先进行编码转化
            $value = $this->utf8ToGbk($value);
            fputcsv($this->fp,$value);
            $count ++;
            //如果数据达到5000则输出一次缓存
            if($count == $limit){
                ob_flush();
                flush();
                $count=0;
            }
        }
    }

    // CSV的Excel支持GBK编码，一定要转换，否则乱码
    private function utf8ToGbk($data)
    {
        foreach ($data as $k => $v){
            // CSV的Excel支持GBK编码，一定要转换，否则乱码
            $data[$k] = @iconv('utf-8', 'gbk', $v);
        }
        return $data;
    }
}


