/////////////上传封面
 /**
 * 点击本地上传封面
 */
 $('.document-upload').click(function(){
     var thisObj = $(this);

     $('.js_hideInput_document').click();
 });

 /**
 * 选择要上传封面
 */
 $('.js_hideInput_document').live('change', function(){
     var thisObj = $(this);
     var parent  = thisObj.parent();
     var val     = thisObj.val();
     var action  = parent.attr('action');

     if (!val) {
         return '';
     }

     action = siteUrl + '/train/admin/ajax/up_document';

     parent.attr('action', action);

     parent.submit();
 });

/**
 * 处理iframe上传图片成功的后续流程
 * @param String url
 * @param String id
 */
function handle_document_path(url , name , type ,size)
{

    if (!url ) {
        alert('文档上传失败，请刷新后重试');
        return false;
    }

    //只能传一个的限定
    $(".js_document").remove();
    //追加个图片地址的输入框

//    var html  = '<video class="js_document" style="width:200px; margin-top:100px;" controls >';
//        html += '<source src="'+url+'" type="video/mp4">';
//        html += '</video>';
//        html += '<input type="text" name="document_info[path]" style="display: none;" value="'+url+ '" />';
    var html  = '<p class="file-name js_document">'+name+'.'+type+'</p>';
        html += '<input type="text" name="document_info[path]"          style="display: none;" value="'+url+ '" />';
        html += '<input type="text" name="document_info[document_name]" style="display: none;" value="'+name+ '" />';
        html += '<input type="text" name="document_info[type]"          style="display: none;" value="'+type+ '" />';
        html += '<input type="text" name="document_info[size]"          style="display: none;" value="'+size+ '" />';

    $(".document-upload-tips").append(html);

}

/**
* iframe上传图片的回调函数
* @param Stirng msg 上传结果
* @param String url 图片地址
* @param String id
*/
function up_document_callback(msg, url ,name ,type ,size)
{
    if (msg != 'ok') {
        alert(msg);
        return false;
    }

    // 注：上成功后的处理流程有其他函数处理
    handle_document_path(url , name ,type ,size);
}