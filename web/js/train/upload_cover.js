
/////////////上传封面
 /**
 * 点击本地上传封面
 */
 $('.js_up_cover').click(function(){
     var thisObj = $(this);

     $('.js_hideInput_cover').click();
 });

 /**
 * 选择要上传封面
 */
 $('.js_hideInput_cover').live('change', function(){
     var thisObj = $(this);
     var parent  = thisObj.parent();
     var val     = thisObj.val();
     var action  = parent.attr('action');

     if (!val) {
         return '';
     }

     action = siteUrl + '/train/admin/ajax/up_cover';

     parent.attr('action', action);

     parent.submit();
 });

/**
 * 处理iframe上传图片成功的后续流程
 * @param String url
 * @param String id
 */
function handle_cover_path(url , db_url)
{
 //id = parseInt(id);

 if (!url || !db_url) {
  alert('图片上传失败，请刷新后重试');
  return false;
 }

 $('.pop-bg').addClass('hidden');
 $('.pop-pics').addClass('hidden');

    //只能传一个的限定
    $(".js_cover").remove();
    //追加个图片地址的输入框
    var html  = '<img src="'+ url +'" class="js_cover" width="188px" height="121px" style="margin-top:10px;">';
        html += '<input type="text" name="video_info[cover]" style="display: none;" value="'+db_url+ '" />';

    $(".upload_cover").append(html);

}

/**
* iframe上传图片的回调函数
* @param Stirng msg 上传结果
* @param String url 图片地址
* @param String id
*/
function up_cover_callback(msg, url , db_url)
{
    if (msg != 'ok') {
        alert(msg);
        return false;
    }

    // 注：上成功后的处理流程有其他函数处理
    handle_cover_path(url , db_url);
}