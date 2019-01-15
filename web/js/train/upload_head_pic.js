
/////////////上传头像
 /**
 * 点击本地上传头像
 */
 $('.js_up_head_pic').click(function(){
     var thisObj = $(this);

     $('.js_hideInput_head_pic').click();
 });

 /**
 * 选择要上传封面
 */
 $('.js_hideInput_head_pic').live('change', function(){
     var thisObj = $(this);
     var parent  = thisObj.parent();
     var val     = thisObj.val();
     var action  = parent.attr('action');

     if (!val) {
         return '';
     }

     action = siteUrl + '/train/admin/ajax/up_head_pic';

     parent.attr('action', action);

     parent.submit();
 });

/**
 * 处理iframe上传图片成功的后续流程
 * @param String url
 * @param String id
 */
function handle_head_pic_path(url , db_url)
{
 //id = parseInt(id);

 if (!url || !db_url) {
  alert('图片上传失败，请刷新后重试');
  return false;
 }

    //$('input[name="cover_type"]').val(id);

    $('.pop-bg').addClass('hidden');
    $('.pop-pics').addClass('hidden');

    //只能传一个的限定
    $(".js_head_pic").remove();
    //追加个图片地址的输入框
    var html  = '<img src="'+ url +'" class="js_head_pic" width="36px" height="36px" style="margin-top:10px;">';
        html += '<input type="text" name="video_info[head_pic]" style="display: none;" value="'+db_url+ '" />';

    $(".upload_head_pic").append(html);

}

/**
* iframe上传图片的回调函数
* @param Stirng msg 上传结果
* @param String url 图片地址
* @param String id
*/
function up_head_pic_callback(msg, url , db_url)
{
    if (msg != 'ok') {
        alert(msg);
        return false;
    }

    // 注：上成功后的处理流程有其他函数处理
    handle_head_pic_path(url , db_url);
}