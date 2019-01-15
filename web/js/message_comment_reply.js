$(function(){
  /**
   * PC版弹出评论框
   */
  $(".replyComment").click(function(){
    var obj = $(this);
    // 恢复框
    var replyBox = obj.parent().next(".replybox")
    if (replyBox.length > 0) {
      replyBox.remove();
      return false;
    }
    
    var threadId  = obj.attr("thread-id");
    var commentId = obj.attr("comment-id");
    if (!threadId || !commentId) {
      alert('该帖子已不存在~');
      return false;
    }
    
    var dataName = obj.attr("data-name");
    
    var html = "<div class='replybox'>";
    html += "<span class='icon-arup'></span>";
    html += "<textarea class='reply-area reply-content' id='ajaxContent_" + commentId + "'>回复@"+dataName+" :</textarea>";
    html += "<div class='under-tt clearfix'>";
    html += "<div class='facebox left' id='replyFace_" + commentId + "'><i class='icon-face'></i></div>";
    html += "<p class='choose left'><label><input type='checkbox' class='is-share'>同时转发到我的微博</label></p>";
    html += "<a href='javascript:void(0);' class='btn-common right send-reply' thread-id='"+threadId+"' comment-id='"+commentId+"'>回复</a>";
    html += "</div></div>";
    
    obj.closest("dd").append(html);
    // 光标定位
    CommentsetFocusPos($("#ajaxContent_" + commentId), $("#ajaxContent_" + commentId).html().length); 
    // 循环绑定表情 
    weiboFace( document.getElementById( 'replyFace_'+commentId ), document.getElementById( 'ajaxContent_'+commentId ) );
    // 循环绑定@好友
    mblog.Func.bindAtToTextarea(document.getElementById("ajaxContent_" + commentId));
  });
  
  /**
   * 手机版弹出评论框
   */
  $(".mobileReplyComment").click(function(){
    var obj = $(this);
    var msgObj = obj.closest(".messageCon");
    // 恢复框
    var replyBox = msgObj.find(".replybox")
    if (replyBox.length > 0) {
      replyBox.remove();
      return false;
    }
    
    var threadId  = obj.attr("thread-id");
    var commentId = obj.attr("comment-id");
    if (!threadId || !commentId) {
      alert('该帖子已不存在~');
      return false;
    }
    
    var dataName = obj.attr("data-name");
    
    var html = "<div class='comment-box replybox'>";
    html += "<textarea class='com-con reply-content' id='ajaxContent_" + commentId + "' placeholder='回复@"+dataName+"'>回复@"+dataName+" :</textarea>";
    html += "<div class='comm-hf clearfix'>";
    //html += "<div class='left'>";
    //html += "<a href='javascript:;'><img src='"+siteUrl+"/images/m/bq.png' width='15' height='15'>表情</a>";
    //html += "</div>";
    html += "<div class='right'><button class='red-radius send-reply' thread-id='"+threadId+"' comment-id='"+commentId+"'>回复</button></div>";
    html += "</div></div>";
    
    msgObj.append(html);
    // 光标定位
    CommentsetFocusPos($("#ajaxContent_" + commentId), $("#ajaxContent_" + commentId).html().length); 
    // 循环绑定@好友
    mblog.Func.bindAtToTextarea(document.getElementById("ajaxContent_" + commentId));
  });
  
  /**
   * 发送评论
   */
  $(".send-reply").live("click", function(){
    // 判断登录
    /*if (!window.userId) {
      show_login_box();
      return false;
    }*/
    
    var obj = $(this);
    // 帖子id和评论id
    var threadId  = obj.attr("thread-id");
    var commentId = obj.attr("comment-id");
    if (!threadId || !commentId) {
      alert('该帖子已不存在~');
      return false;
    }
    // 回复内容
    var content = obj.closest(".replybox").find(".reply-content").val();
    if (!content) {
      alert('请填写要回复的内容');
      return false;
    }
    // 是否发送微博 
    if (obj.closest(".replybox").find(".is-share").attr("checked") == 'checked') {
      var isShare =  1;
    } else {
      var isShare =  0;
    }
    obj.attr("disabled", "disabled");
    $.post(AnUrl('thread/ajax/comment?route=pc'), { 'content':content, 'thread_id':threadId, 'reply_comment_id':commentId, 'is_share_weibo':isShare }, function(json){
      if (json.info == 'ok') {
        alert('回复成功');
        obj.closest(".replybox").remove();
      } else {
        alert(json.info);
      }
      obj.removeAttr("disabled", "disabled");
    }, 'json');
  });
  
})

/**
 * 光标定位
 * @params int obj jQuery对象
 * @params int v   位置
 */
function CommentsetFocusPos(obj, v){
  var range,len,v = v === undefined ? 0 : parseInt(v);
  obj.each(function(k, o){
      if($.browser.msie){
          range=o.createTextRange();
          v === 0 ? range.collapse(false):range.move("character",v);
          range.select();
      }else{
          len=o.value.length;
          v === 0 ? o.setSelectionRange(len,len): o.setSelectionRange(v,v);
      }
      o.focus();
  });
  return obj;
}