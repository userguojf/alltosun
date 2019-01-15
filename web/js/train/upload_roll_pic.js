/////////////上传轮播图
 /**
 * 点击本地上轮播图
 */
 $('.roll-upload').click(function(){
     var thisObj = $(this);

     $('.js_hideInput_roll').click();
 });

 /**
 * 选择要上轮播图
 */
 $('.js_hideInput_roll').live('change', function(){
     var thisObj = $(this);
     var parent  = thisObj.parent();
     var val     = thisObj.val();
     var action  = parent.attr('action');

     if (!val) {
         return '';
     }

     action = siteUrl + '/train/admin/ajax/up_roll_pic';

     parent.attr('action', action);

     parent.submit();
 });

/**
 * 处理iframe上传图片成功的后续流程
 * @param String url
 * @param String id
 */
function handle_roll_path(url , db_url)
{
 //id = parseInt(id);

 if (!url || !db_url ) {
  alert('图片上传失败，请刷新后重试');
  return false;
 }

 $('.pop-bg').addClass('hidden');
 $('.pop-pics').addClass('hidden');

    //只能传一个的限定
    $(".js_roll").remove();
    //追加个图片地址的输入框
    var html  = '<img src="'+ url +'" class="js_roll" width="414px" height="220px" style="margin-top:10px;">';
        html += '<input type="text" name="roll_info[path]" style="display: none;" value="'+db_url+ '" />';

    $(".upload_roll").append(html);

}

/**
* iframe上传图片的回调函数
* @param Stirng msg 上传结果
* @param String url 图片地址
* @param String id
*/
function up_roll_callback(msg, url , db_url)
{
    if (msg != 'ok') {
        alert(msg);
        return false;
    }

    // 注：上成功后的处理流程有其他函数处理
    handle_roll_path(url , db_url);
}