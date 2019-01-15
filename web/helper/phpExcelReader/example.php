<?php
// 测试文档

require_once 'Excel/reader.php';

// Excel文件($filename, $encoding);
$data = new Spreadsheet_Excel_Reader();


// 设置输入编码 UTF-8/GB2312/CP936等等
$data->setOutputEncoding('UTF-8'); 

/***
* 如果服务器不支持 iconv 添加下面的代码使用 mb_convert_encoding 编码
* $data->setUTFEncoder('mb');
*
**/

/***
* 默认情况下行和列的技术从1开始
* 如果要修改起始数值，添加：
* $data->setRowColOffset(0);
*
**/


/***
*  设置工作模式
* $data->setDefaultFormat('%.2f');
* setDefaultFormat - 最大兼容模式
*
* $data->setColumnFormat(4, '%.3f');
* setColumnFormat - 列的格式设置（仅适用于数字字段）
*
**/

$data->read('jxlrwtest.xls');

/*


 $data->sheets[0]['numRows'] - 行数
 $data->sheets[0]['numCols'] - 列数
 $data->sheets[0]['cells'][$i][$j] - 行$i 列$j里的数据

 $data->sheets[0]['cellsInfo'][$i][$j] - 文件的拓展信息
    
    $data->sheets[0]['cellsInfo'][$i][$j]['type'] = "date" | "number" | "unknown"
    	当type为unknown时使用raw值，因为元素中包含'0.00'的格式。
    $data->sheets[0]['cellsInfo'][$i][$j]['raw'] = 未被格式化的值
    $data->sheets[0]['cellsInfo'][$i][$j]['colspan'] 
    $data->sheets[0]['cellsInfo'][$i][$j]['rowspan'] 
*/

error_reporting(E_ALL ^ E_NOTICE);

for ($i = 1; $i <= $data->sheets[0]['numRows']; $i++) {
	for ($j = 1; $j <= $data->sheets[0]['numCols']; $j++) {
		echo "\"".$data->sheets[0]['cells'][$i][$j]."\",";
	}
	echo "\n";

}


//print_r($data);
//print_r($data->formatRecords);
?>
