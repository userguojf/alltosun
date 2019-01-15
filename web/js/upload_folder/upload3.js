/**
 *XML httpRequest 上传文件实现进度条
 */
    //允许的文件类型
var arrType = ['x-zip-compressed', 'jpg', 'png', 'jpeg', 'gif', 'vnd.openxmlformats-officedocument.wordprocessingml.document', 'mp3', 'mp4', 'xls', 'doc', 'ppt', 'csv', 'pdf', 'msword', 'vnd.openxmlformats-officedocument.spreadsheetml.sheet','vnd.openxmlformats-officedocument.presentationml.presentation',];// 'vnd.ms-excel'];

var xhr;        //对象
var ot;         //上传时间对象
var oloaded;    //置上传开始时，以上传的文件大小为0

//获取后台地址的路径
var ll_url = document.getElementById("ll_url").src.split('/js')[0];
//接收上传文件的后台地址
var folder_url = ll_url + "/file_apply/admin/file/js_save";

//判断一个字符串是否为数组中的一个元素
function in_array(str, array) {
    for (var i = 0; i < array.length; i++) {
        if (array[i] == str) {
            return true;
        }
    }
    return false;
}

//上传文件方法
function UpladFile() {
    var fileObj = document.getElementById("file").files[0]; // js 获取文件对象
    var inpRank = document.getElementById("rank").value;
    var file_number = document.getElementById("file_number").value;
    var print_time = document.getElementById("print_time").value;
    if (file_number == '') {
        alert_info_replace('请输入文件号');
        return false;
    }

    if (file_number.length > 40) {
        alert_info_replace('请输入小于四十个字符');
        return false;
    }

    if (print_time == '') {
        alert_info_replace('请选择文件印发时间');
        return false;
    }
    //判断文件是否有
    if (typeof(fileObj) == 'undefined') {
        alert_info_replace('请选择文件');
        return false;
    }

    //判断文件是否为空
    if (fileObj['size'] <= 0) {
        alert_info_replace('选择的文件大小为空，请重新选择');
        return false;
    }
    if (inpRank != 1) {
        alert_info_replace('没有权限');
        return false;
    }

    //获取文件的类型
    var file_type = fileObj.type.split('/').pop();
    if (!in_array(file_type, arrType)) {
        alert_info_replace('文件选择的类型不支持');
        return false;
    }

    //进度条的显示
    $('#message').addClass('hidden'); // 将发布文件弹框隐藏
    $('#uploading').removeClass('hidden'); // 显示发布中弹框
    var url = folder_url;          // 接收上传文件的后台地址

    var form = new FormData();     // FormData 对象
    form.append("info", fileObj);     // 文件对象
    form.append("rank", inpRank);     // 文件权限对象
    form.append("file_number", file_number);     // 文件号
    form.append("print_time", print_time);     // 文件印发时间

    xhr = new XMLHttpRequest();    // XMLHttpRequest 对象
    xhr.open("post", url, true);  //post方式，url为服务器请求地址，true 该参数规定请求是否异步处理。
    xhr.onload = uploadComplete;  //请求完成 调用方法
    xhr.onerror = uploadFailed;    //请求失败 调用方法
    xhr.upload.onprogress = progressFunction;//【上传进度调用方法实现】

    //上传开始执行方法
    xhr.upload.onloadstart = function () {
        ot = new Date().getTime();      //设置上传开始时间
        oloaded = 0;//设置上传开始时，以上传的文件大小为0
    };

    xhr.send(form); //开始上传，发送form数据

}

//上传进度实现方法，上传过程中会频繁调用该方法
function progressFunction(evt) {

    var progressBar = $('#progressBar');
    // event.total是需要传输的总字节，event.loaded是已经传输的字节。如果event.lengthComputable不为真，则event.total等于0
    if (evt.lengthComputable) {
        progressBar.css("width", Math.round(evt.loaded / evt.total * 100) + "%");
        $("#progressBar-text").html(Math.round(evt.loaded / evt.total * 100) + "%");
    }

    var nt = new Date().getTime(); //获取当前时间
    var pertime = (nt - ot) / 1000;         //计算出上次调用该方法时到现在的时间差，单位为s

    ot = new Date().getTime();          //重新赋值时间，用于下次计算

    var perload = evt.loaded - oloaded; //计算该分段上传的文件大小，单位b
    oloaded = evt.loaded;//重新赋值已上传文件大小，用以下次计算

    //上传速度计算
    var speed = perload / pertime;//单位b/s
    var bspeed = speed;
    var units = 'b/s';//单位名称
    if (speed / 1024 > 1) {
        speed = speed / 1024;
        units = 'k/s';
    }
    if (speed / 1024 > 1) {
        speed = speed / 1024;
        units = 'M/s';
    }
    speed = speed.toFixed(1);
    //剩余时间
    var resttime = ((evt.total - evt.loaded) / bspeed).toFixed(1);
    if (bspeed == 0)
        alert_info_replace('发布已取消');

}

//

//上传成功响应
function uploadComplete(evt) {
    //服务断接收完文件返回的结果
    $('#uploading').addClass('hidden');
    $('#upload-success').removeClass('hidden');
    // 文件名称
    var file_name = document.getElementById("file").files[0].name;
    // 印发日期
    var print_time = document.getElementById("print_time").value;
    //文件号
    var file_number = document.getElementById("file_number").value;
    // 修改时间为 y年m月d日格式
    var new_time =  new Date(print_time).Format("yyyy年MM月dd日");
    // 连接文本
    var text = '集团文件通知：集团于'+new_time+'印发了文件号为“'+file_number+'”的'+file_name+'文件，请登录门店数字化运营平台下载或查看 http://udb.pzclub.cn。';
    $('#upload-success-text').html(text);
}

//上传失败
function uploadFailed(evt) {
    alert_info_replace('上传失败，请刷新网页');

}

//取消上传
function cancleUploadFile() {
    //取消的方法
    xhr.abort();

}

// 日期转换
Date.prototype.Format = function (fmt) {
    var o = {
        "M+": this.getMonth() + 1, //月份
        "d+": this.getDate(), //日
        "h+": this.getHours(), //小时
        "m+": this.getMinutes(), //分
        "s+": this.getSeconds(), //秒
        "q+": Math.floor((this.getMonth() + 3) / 3), //季度
        "S": this.getMilliseconds() //毫秒
    };
    if (/(y+)/.test(fmt)) fmt = fmt.replace(RegExp.$1, (this.getFullYear() + "").substr(4 - RegExp.$1.length));
    for (var k in o)
        if (new RegExp("(" + k + ")").test(fmt)) fmt = fmt.replace(RegExp.$1, (RegExp.$1.length == 1) ? (o[k]) : (("00" + o[k]).substr(("" + o[k]).length)));
    return fmt;
}

// 弹窗信息更换
function alert_info_replace(msg)
{
    $('#alert_noti').html('');
    $('#alert_noti').html(msg);
    $('#alert_noti').css('display','block');
    alert_hidden();
}
// 提示弹窗消失
function alert_hidden()
{
    setTimeout(function () {
        $('#alert_noti').fadeOut();
    },500);
}