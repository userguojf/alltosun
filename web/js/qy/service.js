$(function() {
  
    $('.chat-list .title').on('click', function () {
      
      if ($('.chat-list #chat_title').hasClass('glyphicon-menu-down')) {
        $('.chat-list #chat_title').removeClass('glyphicon-menu-down');
        $('.chat-list #chat_title').addClass('glyphicon-menu-left');
        $('.chat-list ul').addClass('hidden');
      } else if($('.chat-list #chat_title').hasClass('glyphicon-menu-left')){
        $('.chat-list #chat_title').removeClass('glyphicon-menu-left');
        $('.chat-list #chat_title').addClass('glyphicon-menu-down');
        $('.chat-list ul').removeClass('hidden');
      }
    });
    
    //聊天内容
    $("#js_chatContent").mCustomScrollbar({
        scrollButtons: {
            enable: true
        }
    });
    
    //咨询中会话列表
    $("#js_chatCurr").mCustomScrollbar({
        scrollButtons: {
            enable: true
        }
    });
    
    //更新滚动条
    updateMsgScroll();
    //导出咨询记录
    var isExport = true;
    $('.js_btnExport').click(function() {
        isExport = false
        $('.js_exportBox').toggle();
    })
    $('body').click(function() {
        if (isExport) {
            $('.js_exportBox').hide();
        }
        isExport = true
    })

    var isReply = true;
    $('.js_btnReply').click(function() {
        if ($('.js_replyBox').hasClass('hidden')) {
            isReply = false
            $('.js_replyBox').removeClass('hidden');
            if (!$('.js_replyBox .con').hasClass('mCustomScrollbar')) {
                $('.js_replyBox .con').mCustomScrollbar({
                    scrollButtons: {
                        enable: true
                    }
                });
            }
        } else {
            isReply = true
            $('.js_replyBox').addClass('hidden');
        }
    })
    $('body').click(function() {
        if (isReply) {
            $('.js_replyBox').addClass('hidden');
        }
        isReply = true
    })
    
    //快速回复消息点击事件
    $('.js_replyBox li').on('click', function (e) {
      e.stopPropagation();
      $('#js_text_content').val($(this).text());
      isReply = true;
      $('.js_replyBox').addClass('hidden');
    });
    
    //点击发送消息
    $('.js_reply_msg').on('click', function () {
      serviceConversation.sendServiceMsg()
    });
    //回车发送消息
    $(window).on('keyup',function(e){ 
      if(e.keyCode==13) {
        serviceConversation.sendServiceMsg();
      }
      
    });  
    
    
    //会话点击事件（切换会话）
    $('.chat-list ul li').on('click', function () {
      $('.chat-list li').removeClass('curr');
      $(this).addClass('curr');
      conversationId = $(this).data('id');
      if (!conversationId) {
        alert('非法的会话');
        return false;
      }
      
      
      //更新会话用户信息
      var thisIndex = $(this).index();
      serviceConversation.updateUserInfo(thisIndex);
      //加载会话消息
      serviceConversation.loadMsg();
    });
    
    //会话点击事件（切换会话）动态添加
    $('.chat-list').on('click', '.appendDom', function () {
      $('.chat-list li').removeClass('curr');
      $(this).addClass('curr');
      conversationId = $(this).data('id');
      if (!conversationId) {
        alert('非法的会话');
        return false;
      }
      
      //更新会话用户信息
      var thisIndex = $(this).index();
      serviceConversation.updateUserInfo(thisIndex);

      //加载会话消息
      serviceConversation.loadMsg();
    });
    
    
    //退出、关闭会话
    $('#quit').on('click', function () {
      serviceConversation.quitConversation();
    });
    
    $('.chat-list').on('click', '.delete', function (e) {
      e.stopPropagation();
      var conversation_id = $(this).closest('li').data('id');
      serviceConversation.quitConversation(conversation_id);
    });

});
  
//会话对象
function serviceConversation()
{

}

serviceConversation.sendServiceMsg = function () {
  //获取内容并清空
  var content = $.trim($('#js_text_content').val());
  $('#js_text_content').val('');
  if (!content) {
    alert('请输入回复内容');
    return false;
  }

  if (!conversationId) {
    alert('非法会话');
    return false;
  }
  //回复消息
  serviceConversation.replyMsg(content);
}

//加载会话消息
serviceConversation.loadMsg = function () {
  if (!conversationId) {
    return false;
  }

  $.post(siteUrl+'/qy_message/admin/ajax/get_message_list', { conversation_id: conversationId }, function (result) {
    $('#js_chatContent ul').html('');
    if (result.info != 'ok') {
      alert(result.msg);
      return false;
    }
   
    if (result.last_msg_id) {
      lastMsgId = result.last_msg_id;
      $('#js_chatContent ul').html(serviceConversation.joinMsg(result.list));
      //更新滚动条
      updateMsgScroll();
    }
    
  }, 'json');
}

//回复消息
serviceConversation.replyMsg = function (content){

  if (!conversationId) {
    alert('非法会话');
    return false;
  }
  
  if (!content) {
    alert('请输入回复内容');
    return false;
  }
  
  $.post(siteUrl+'/qy_message/admin/ajax/reply_message', { conversation_id: conversationId, content:content }, function (result) {
    if (result.info != 'ok') {
      alert(result.msg);
      return false;
    }

    var html = serviceConversation.joinReplyMsg(htmlString(content), result.date);
    $('#js_chatContent ul').append(html);
    //更新滚动条
    updateMsgScroll();
    
  }, 'json');
  
}


//锁
var is_return = true;
//轮询新消息 和 新会话
serviceConversation.rollPoling = function () {

  var timer = setInterval(function() {
    var requestData = { conversation_id:conversationId, last_msg_id:lastMsgId, last_conversation_id:lastConversationId, service_id: serviceId };
      //如果上轮返回则执行请求
      if (!is_return) {
          return false;
      }

      $.post(siteUrl+"/qy_message/admin/ajax/roll_poling",requestData,function(data){
        //如果返回数据则更改标示
        if (data) {
            is_return = true;
        }

        if(data.info != "ok") {
          return false;
        }
          
        //消息
        if (data.msg_list.length > 0 && data.last_msg_id) {
          lastMsgId = data.last_msg_id;
          //追加
          $('#js_chatContent ul').append(serviceConversation.joinMsg(data.msg_list));
          //更新滚动条
          updateMsgScroll();
        }
        
        //会话
        if (data.conversation_list.length > 0 && data.last_conversation_id) {
          lastConversationId = data.last_conversation_id;
          //追加
          var html = serviceConversation.joinConversation(data.conversation_list);

          if (conversation_list.length == 0) {
            
            $('.chat-list ul').html(html);
            
            //更新滚动条
            updateConversationScroll();
            
            //合并会话对象
            conversation_list = data.conversation_list;
            
            //更新会话用户信息
            serviceConversation.updateUserInfo(0);
            //加载会话消息
            serviceConversation.loadMsg();
          } else {
            $('.chat-list ul').prepend(html);
            //合并会话对象
              for(var i in conversation_list) {
                data.conversation_list.push(conversation_list[i]);
              }
              
            conversation_list = data.conversation_list;
            
            
          }
        }

      },"json");

    },rollPolingTime);
   
}

//更新会话用户信息
serviceConversation.updateUserInfo = function (index){
  var info = conversation_list[index];
  //更新用户头像
  currAvatar = info['user_avatar']?info['user_avatar']:defaultUserAvatar;
  $('.chat-infos span').eq(0).text('渠道号：'+info.user_number);
  $('.chat-infos span').eq(1).text('营业厅名称：'+info.hall_name_substr);
  $('.chat-infos span').eq(2).text('姓名：'+info.user_name_substr);
  $('.chat-infos span').eq(3).text('联系方式：'+info.user_phone);
  
  $('.chat-infos span').eq(1).attr('title', info.hall_name);
  $('.chat-infos span').eq(2).attr('title', info.user_name);
}


//拼接消息
serviceConversation.joinMsg = function (list)
{
  var html = '';
  
  $.each(list, function (i, o) {
    var avatar = ''
    //是否是当前会话的内容
    if (o.conversation_id != conversationId) {
      return true;
    }
    
    if (o.is_reply != 0) {
      html +='<li class="mine">';
      avatar = defaultServiceAvatar;
    } else {
      avatar = currAvatar
      html +='<li>';
    }
    var text_style="";
    if (o.pic_url) {
      o.content = "<a href='"+o.pic_url+"' target='_blank'><img width=200 src='"+o.pic_url+"' /></a>";
      text_style="style='text-align:center'";
    }
    html += '<div class="time">'+o.add_time+'</div>\
              <div class="clearfix">\
                  <div class="ava"><img src="'+avatar+'"></div>\
                  <div class="con">\
                      <em class="arrow"></em>\
                      <div class="text" '+text_style+'>\
                          '+o.content+'\
                      </div>\
                  </div>\
              </div>\
          </li>';
  });
  return html;
}

function htmlString(str){
  str = str.replace(/'/g, "&#039;").replace(/"/g, "&quot;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/&/g, "&amp;");
  return str;
}





//拼接回复消息
serviceConversation.joinReplyMsg = function (content, date)
{
  content = emotion_parse(content);
  var html  ='<li class="mine">\
                  <div class="time">'+date+'</div>\
                  <div class="clearfix">\
                      <div class="ava"><img src="'+defaultServiceAvatar+'"></div>\
                      <div class="con">\
                          <em class="arrow"></em>\
                          <div text_style class="text">\
                              '+content+'\
                          </div>\
                      </div>\
                  </div>\
              </li>';
  return html;
}

//拼接会话信息
serviceConversation.joinConversation = function (list){
  var html = '';
  $.each(list, function (i, o) {
    if (conversation_list.length == 0 && i == 0) {
      conversationId = o.id;
      html+= '<li class="appendDom curr" data-id="'+o.id+'" title="'+o.completion_user_name+'">'+o.completion_user_name_substr+'<em class="delete"></em></li>';
    } else {
      html+= '<li class="appendDom" data-id="'+o.id+'" title="'+o.completion_user_name+'">'+o.completion_user_name_substr+'<em class="delete"></em></li>';
    }

  });
  
  return html;
}

//退出、关闭会话
serviceConversation.quitConversation = function (conversation_id)
{

  var close_id = conversation_id ? conversation_id : conversationId;
  if (conversation_id) {
    
  }
  $.post(siteUrl+'/qy_conversation/admin/ajax/close_conversation', { conversation_id:conversationId }, function (res) {
    if (res.info == 'fail') {
      alert(res.msg);
      return false;
    }
    
    window.location.reload();
    
  }, 'json');
}

//会话导出
$('.js_export').on('click', function () {
  window.location.href=siteUrl+'/qydev/admin/service/export_msg?conversation_id='+conversationId;
});

//更新滚动条到底部
function updateMsgScroll() {
  //更新滚动条
  $("#js_chatContent").mCustomScrollbar("update");
  //滚动到底部
  $("#js_chatContent").mCustomScrollbar("scrollTo","bottom");
  
}

//更新会话列表滚动条
function updateConversationScroll()
{
  //咨询中
  $("#js_chatCurr").mCustomScrollbar("update");
}

//表情相关

//表情按钮
$('.js_faceBtn').on('click', function () {
 
  if($('.face-box').hasClass('hidden')) {
    $('.face-box').removeClass('hidden');
  } else {
    $('.face-box').addClass('hidden');
  }
});

//选择表情
$('.emotion_img').on('click', function () {
  var content = $('#js_text_content').val();
  console.log($(this).find('img').data('phrase'));
  content += $(this).find('img').data('phrase');
  $('#js_text_content').val(content);
  $('.face-box').addClass('hidden');
});

function emotion_parse(content){
  //匹配[]格式
  var reg = /(\[([\u4e00-\u9fa5]|[a-zA-Z])*\]).*?/g;
  var matches = content.match(reg);

  if ( matches != null ) {
    for( var i = 0; i < matches.length; i++) {
        for (var item = 0; item < emotion_list.length; item++) {
          if (emotion_list[item].indexOf(matches[i]) > 0) {
            content = content.replace( matches[i], emotion_list[item] );
            break; 
          }
        }
        
    }
  }
 
  return content;
}



