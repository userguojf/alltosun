var isFollowing = false;

$(function(){
  // 关注
  $(".jsAddFollow").live("click", function(){
    var obj = $(this);

    isFollowing = true;
    
    var uid = obj.closest(".follow-con").attr("uid");
    if (!uid) {
      alert('要关注的用户不存在');
      isFollowing = false;
      return false;
    }
    $.post(siteUrl + "/?anu=follow/ajax/add_follow", { 'uid':uid }, function(json){
      if (json.info == 'ok') {
        var inHtml = updateFollowCon(json.relation);
        obj.closest(".follow-con").html(inHtml);
      } else {
        if (json.msg == '请登录') {
          show_login_box();
        } else {
          alert(json.msg);
        }
      }
      isFollowing = false;
    }, 'json');
  });
  
  // 取消关注
  $(".jsCancelFollow").live("click", function(){
    var obj = $(this);
    
    isFollowing = true;
    
    var uid = obj.closest(".follow-con").attr("uid");
    if (!uid) {
      alert('要取消关注的用户不存在');
      isFollowing = false;
      return false;
    }
    $.post(siteUrl + "/?anu=follow/ajax/cancel_follow", { 'uid':uid }, function(json){
      if (json.info == 'ok') {
        var inHtml = updateFollowCon(json.relation);
        obj.closest(".follow-con").html(inHtml);
      } else {
        if (json.msg == '请登录') {
          show_login_box();
        } else {
          alert(json.msg);
        }
      }
      isFollowing = false;
    }, 'json');
  });
  
})

/**
 * 更新关注好友的内容
 */
function updateFollowCon(relation) {
  var inHtml = "<a href='javascript:void(0);' class='btn-common-att jsAddFollow'><span><em class='addicon'>+</em>关注</span></a>";
  switch (relation) {
    // 已关注
    case 1 :
      inHtml = "<span class='btn-common-att2'><span><i class='wicon1 addone'></i>已关注 | <a href='javascript:;' class='cancel-w jsCancelFollow'>取消</a></span></span>";
      break;
      
    case 2 :
      inHtml = "<a href='javascript:void(0);' class='btn-common-att jsAddFollow'><span><i class='wicon1 addone'></i>| <em class='addicon'>+</em>关注</span></a>"
      break;
      
    // 互相关注
    case 3 :
      inHtml = "<span class='btn-common-att2'><span><i class='wicon1 addtwo'></i>互相关注 | <a href='javascript:;' class='cancel-w jsCancelFollow'>取消</a></span></span>";
      break;
      
    // 默认(未关注)
    default :
      inHtml = "<a href='javascript:void(0);' class='btn-common-att jsAddFollow'><span><em class='addicon'>+</em>关注</span></a>";
  }
  
  return inHtml;
}