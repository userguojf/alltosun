/**
 *XML httpRequest 上传文件实现进度条
 *add guojf
 */
    //允许的文件类型
var arrType = ['jpg', 'png', 'jpeg', 'gif', 'vnd.openxmlformats-officedocument.wordprocessingml.document', 'mp3', 'mp4', 'xls', 'doc', 'ppt', 'csv', 'pdf', 'msword'];// 'vnd.ms-excel'];

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
    var content = document.getElementById("content").value;
    var file_number = document.getElementById("file_number").value;
    var print_time = document.getElementById("print_time").value;

    if (file_number == '') {
        $('#upload_tip').html('请输入文件号');
        return false;
    }

    if (print_time == '') {
        $('#upload_tip').html('请选择文件印发时间');
        return false;
    }
    //判断文件是否有
    if (typeof(fileObj) == 'undefined') {
        $('#upload_tip').html('请选择文件');
        return false;
    }

    if (inpRank != 1) {
        alert('没有权限');
        return false;
    }

    $('#upload_tip').html('')

    //获取文件的类型
    var file_type = fileObj.type.split('/').pop();
    if (!in_array(file_type, arrType)) {
        $('#upload_tip').html('文件选择的类型不支持');
        return false;
    }
    $('#click_cancel').show();

    //进度条的显示
    document.getElementById("progressBar").style.display = "inline";

    var url = folder_url;          // 接收上传文件的后台地址

    var form = new FormData();     // FormData 对象
    form.append("info", fileObj);     // 文件对象
    form.append("rank", inpRank);     // 文件权限对象
    form.append("content", content);     // 自定义文件名
    form.append("file_number", file_number);     // 自定义文件名
    form.append("print_time", print_time);     // 自定义文件名

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
    //            console.log(form);
    xhr.send(form); //开始上传，发送form数据

}

//上传进度实现方法，上传过程中会频繁调用该方法
function progressFunction(evt) {

    var progressBar = document.getElementById("progressBar"); //进度条
    var percentageDiv = document.getElementById("percentage");  //后面的说明
    // event.total是需要传输的总字节，event.loaded是已经传输的字节。如果event.lengthComputable不为真，则event.total等于0
    if (evt.lengthComputable) {
        progressBar.max = evt.total;
        progressBar.value = evt.loaded;

        percentageDiv.innerHTML = Math.round(evt.loaded / evt.total * 100) + "%"; //四舍五入百分比
    }

    var time = document.getElementById("time");
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
    time.innerHTML = '，速度：' + speed + units + '，剩余时间：' + resttime + 's';
    if (bspeed == 0)
        time.innerHTML = '上传已取消';
}

//上传成功响应
function uploadComplete(evt) {
    //服务断接收完文件返回的结果
    //            setTimeout(
    $('#start_upload').hide();
    $('#click_cancel').hide();
    $('#click_close').show();
    $('.btn_close').hide();
    setTimeout(function () {
        window.location.reload();
    }, 3000)
    //                "window.location.reload();"
    //            ,1500);
}

//上传失败
function uploadFailed(evt) {
    alert("上传失败，请刷新网页");
}

//取消上传
function cancleUploadFile() {
    //取消的方法
    xhr.abort();

}

//        function format_bool(){
//            alert("请重新选择文件，允许的文件类型有jpg,png, jpeg,gif, mp3,mp4, xls ,doc , ppt , csv,pdf");
//        }

