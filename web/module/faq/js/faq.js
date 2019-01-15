/**
 * guojf copied sunxs  16/11/15.
 */
//百度编辑器公用配置
// 初始化编辑器
var image_url = siteUrl + '/faq/ajax/upload_files';
//console.log(image_url);
var editor = new UE.ui.Editor({
    imageUrl:image_url,             //图片上传提交地址
    imagePath:"",                     //图片修正地址，引用了fixedImagePath,如有特殊需求，可自行配置
    imageFieldName:"Filedata",
    initialFrameWidth:400,
    initialFrameHeight:285,
    elementPathEnabled:false,
    autoHeightEnabled: false,
    autoFloatEnabled: false,
    wordCount: false,
    toolbars:[['source',"justifyleft","justifycenter","justifyright","justifyjustify","bold","italic","underline","fontsize","forecolor","insertimage"],[]]
});


