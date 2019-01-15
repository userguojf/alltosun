//百度编辑器公用配置
// 初始化编辑器
var img_url  = siteUrl + '/editor/ajax/upload_img';
var file_url = siteUrl + '/editor/ajax/upload_file';

var editor = new UE.ui.Editor({
    imageUrl : img_url,               //图片上传提交地址
    fileUrl  : file_url,
    fileFieldName  : 'fileData',
    imagePath:"",                     //图片修正地址，引用了fixedImagePath,如有特殊需求，可自行配置
    imageFieldName : "imgData",
    initialFrameWidth:640,
    initialFrameHeight:400,
    elementPathEnabled:false,
    autoHeightEnabled: false,
    autoFloatEnabled: false,
    wordCount: false,
    fileAllowFiles: [[
                    ".png", ".jpg", ".jpeg", ".gif", ".bmp",
                    ".flv", ".swf", ".mkv", ".avi", ".rm", ".rmvb", ".mpeg", ".mpg",
                    ".ogg", ".ogv", ".mov", ".wmv", ".mp4", ".webm", ".mp3", ".wav", ".mid",
                    ".rar", ".zip", ".tar", ".gz", ".7z", ".bz2", ".cab", ".iso",
                    ".doc", ".docx", ".xls", ".xlsx", ".ppt", ".pptx", ".pdf", ".txt", ".md", ".xml"
                ]],
//    toolbars:[['source',"justifyleft","justifycenter","justifyright","justifyjustify","bold","italic","underline","fontsize","forecolor","insertimage"],[]]
                toolbars: [
                           [
                               'undo', //撤销
                               'redo', //重做
                               'bold', //加粗
                               'italic', //斜体
                               'underline', //下划线
                               'removeformat', //清除格式
                               'forecolor', //字体颜色
                               'backcolor', //背景色
                               'insertorderedlist', //有序列表
                               'insertunorderedlist', //无序列表
                               'fontfamily', //字体
                               'fontsize', //字号
                               'justifyleft', //居左对齐
                               'justifyright', //居右对齐
                               'justifycenter', //居中对齐
                               'justifyjustify', //两端对齐
                               'source', //源代码
                               'edittip ', //编辑提示
                               'link', //超链接
                               'unlink', //取消链接
                               'simpleupload', //单图上传
                               'insertimage', //多图上传
                               'insertvideo', //视频
                           ]
                        ]
});


