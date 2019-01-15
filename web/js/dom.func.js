define(function(require, exports, module){
    var $ = require('jquery');
    /**
     * 元素数值自增
     * 用于自增数+1
     * @param object selector 选择器对象
     * @param int step 自定步长默认为1
     */
    exports.autoAdd = function(selector, step) {
        if (step == undefined) step = 1;
        var num = parseInt(selector.html());
        num = num + step;
        selector.html(num);
    }
    
    /**
     * 设置喜欢帖子之后的DOM
     */
    exports.setThreadLikeDom = function(){
        $('#likeBox').html('<a href="javascript:void(0);" class="btn-like btn-liked"><i></i>已赞</a>');
    }
    
    /**
     * 帖子回复暂无数据
     */
    exports.showEmptyCommentList = function(){
        var html = '';
            html = html + '<li class="pclevel-one">';
              html = html + '<div class="clearfix">';
              html = html + '暂无回复!';
              html = html + '</div>';
            html = html + '</li>';
        
        $('.loading').hide();
        $('.pcomment-list').html(html);
    }
    
    /**
     * 加入版块显示的DOM结构
     */
    exports.followForum = function(forum_id) {
        var html = '<a href="javascript:void(0);" class="btn-join" id="setFollow" data-forum_id="'+forum_id+'">加入</a>';
        $('#setFollowForum').html(html);
    }
    
    /**
     * 取消版块显示的DOM结构
     */
    exports.canceFollowForum = function(forum_id) {
        var html = '<span class="btn-cancel">已加入 | <a href="javascript:void(0)" id="setFollow" data-forum_id="'+forum_id+'">取消</a></span>';
        $('#setFollowForum').html(html);
    }
    
    /**
     * 显示分页框
     */
    exports.showPagerBox = function(obj) {
        var html = '';
        
        if (obj.page_arr.length == 0 || obj.last_page == 1) return false;

        if (obj.current_page > 1) {
            html = '<a class="pages-length" data-page_no="'+ 1 +'" href="'+obj.url+'&page_no=1">首页</a>';
            html = html + '<a class="pages-length" data-page_no="'+ obj.prev_page +'" href="'+obj.url + '&page_no=' + obj.prev_page+'">前一页</a>';
        }
        
        var pageArr = obj.page_arr;
        
        for(var i = 0; i < obj.page_arr.length; i++) {
            var url = obj.url + pageArr[i];
            if (obj.current_page == pageArr[i]) {
                html = html + '<a href="'+ url +'&page_no='+ pageArr[i] +'" data-page_no="'+ pageArr[i] +'" class="curr">' + pageArr[i] + '</a>';
            } else {
                html = html + '<a href="'+ url +'&page_no='+pageArr[i]+'" data-page_no="'+ pageArr[i] +'">' + pageArr[i] + '</a>';
            }
        }
        
        if (obj.current_page < obj.last_page) {
            html = html + '<a class="pages-length" data-page_no="'+ obj.next_page +'" href="'+ obj.url + '&page_no=' + obj.next_page +'">后一页</a>';
            html = html + '<a class="pages-length" data-page_no="'+ obj.last_page +'" href="' + obj.url + '&page_no=' + obj.last_page + '">末页</a>';
        }
        
        $('.pages-num').html(html);
    }
    
    /**
     * 帖子回复框
     * 
     */
    exports.showCommentBox = function(obj, uid, userName, replyCommentId)
    {
       var html = '';

       html = html + '<span class="icon-arup"></span>';
       html = html + '<textarea class="reply-area " id="ajaxContent_' + replyCommentId + '">回复@'+userName+'：</textarea>';
       html = html + '<div class="under-tt clearfix">';
          html = html + '<div id="replyFace_' + replyCommentId + '" class="facebox left"><i class="icon-face"></i></div>';
          html = html + '<p class="choose left"><label><input id="ajaxIsShareWeibo" type="checkbox">同时转发到我的微博</label></p>';
          html = html + '<a href="javascript:void(0);" class="btn-common btn-reply2 right ajaxComment" replyCommentId="'+replyCommentId+'" uid="'+ uid +'">回复</a>';
        html = html + '</div>';
        
      obj.html(html);
      obj.show();
      // 循环绑定表情
      weiboFace( document.getElementById( 'replyFace_'+replyCommentId ), document.getElementById( 'ajaxContent_'+replyCommentId ) );
      // 循环绑定@好友
      mblog.Func.bindAtToTextarea(document.getElementById("ajaxContent_" + replyCommentId));

    }
    
    /**
     * 帖子回复列表dom
     * @param object obj
     * @param flag string // save 写入刷新  page 分页刷新 其他为默认手工刷新
     */
    exports.showCommentList = function(obj, flag) {
        var html = '', tag = 0;
        for (var i = 0; i < obj.length; i++) {
            if (obj[i].status == 1) {
                html = html + '<li class="pclevel-one selectCommentItem" id="comment_box_'+ obj[i].id + '">';
                
                if (obj[i].is_del == 1) {
                    html = html + '<a href="javascript:void(0);" style="display:none" class="icon-common-del deleteOneComment icon-delcont" data-id="'+ obj[i].id +'">×</a>';
                }
                
                html = html + '<div class="clearfix">';
                html = html + '<div class="pr-face left">';
                html = html + '<a href="'+ obj[i].user.href +'"><img src="'+ obj[i].user.avatar +'" alt=""></a>';
                html = html + '</div>';
                html = html + '<div class="pr-mc">';
                html = html + '<p class="prc">';
                html = html + '<a href="'+ obj[i].user.href +'" class="pname idCard">';
                html = html + '<i class="icon-per icon-male"></i>'+ obj[i].user.user_name +'</a>';
                html = html + obj[i].affix;
                html = html + '：';
                html = html + "<span class='comment' >";
                html = html + obj[i].content;
                html = html + "</span>";
                html = html + '<span class="date"> ('+ obj[i].add_time +')</span></p>';
                          
                if (obj[i].pic) {
                    html = html + '<p class="pmap" ><img  width="120" src="' + obj[i].pic + '"></p>';
                 }
                          
                if (obj[i].video) {
                    html = html + '<div class="mapbox clearfix"  style="display: block;" id="mapBox_'+obj[i].video.id+'">' + 
                                  '<div class="wrapvideo">' + 
                                  '<a href="javascript:void(0)"  class="showBigVideo" data-video_id="'+ obj[i].video.id+'" id="'+obj[i].video.id +'">' + 
                                  '<img  width="120"  src="' + obj[i].video.pic + '">' + 
                                  '<a href="javascript:void(0);" class="icon-play showBigVideo" data-video_id="'+ obj[i].video.id+'" id="'+ obj[i].video.id+'"></a>'+
                                  '</a>'+
                                  '</div>'+
                                  '</div>';
                    html = html + '<div class="previewbox" style="display:none" id="bigVideoBox_' + obj[i].video.id + '"></div>';
                    //html = html + '<div  id="pBox_' + obj[i].video.id + '" class="pmap commentVideo videoThumb video-thumb" data-comment_id=' + obj[i].video.id + ' ><img  src="' + obj[i].video.pic + '"><a href="javascript:void(0);" class="icon-play"></a></div>';
                    //html = html + '<p ><div class="previewbox" style="display:none" id="commentVideoBig_' + obj[i].video.id + '"></div></p>';
                 }
                 html = html + '</div>';
                 html = html + '</div>';
                 
                 // 保存楼层数,用于锚点跳转
                 tag = obj[i].tag_num;
                 if (obj[i].tag_num != undefined && obj[i].tag_num > 3) {
                     html = html + '<span id="anchor_'+ tag +'" class="level-name name-other">'+ tag +'</span>';
                 } else if(obj[i].tag_num != undefined && obj[i].tag_num <= 3) {
                     html = html + '<span class="level-name name-first">'+ obj[i].tag +'</span>';
                 }
                 
                 html = html + '<div class="rbox clearfix" >';
                 html = html + '<p class="clearfix"><a href="javascript:void(0);" class="w-reply right replyComment" data-replyCommentId="'+obj[i].id+'" data-name="'+ obj[i].user.user_name +'" id="'+obj[i].user_id+'" >回复</a></p>';
                 html = html + '<div class="replybox" style="display:none;">';
                 html = html + '</div>';
                 html = html + '</div>';
                 html = html + '</li>';
            } else {
                
                html += '<li class="pclevel-one delone">';
                html += '<div class="clearfix">';
                html += '<div class="pr-face left">';
                html += '<a href="javascript:void(0);" class=""><img src="../images/default_pic.jpg" alt=""></a>';
                html += '</div>';
                html += '<div class="pr-mc">';
                html += '<p class="prc"><span class="arrowdel1"></span><i class="arrowdel2"></i>该回复已被删除……</p>';
                html += '</div>';
                html += '</div>';
                
                // 保存楼层数,用于锚点跳转
                tag = obj[i].tag_num;
                if (obj[i].tag_num != undefined && obj[i].tag_num > 3) {
                    html = html + '<span id="anchor_'+ tag +'" class="level-name name-other">'+ tag +'</span>';
                } else if(obj[i].tag_num != undefined && obj[i].tag_num <= 3) {
                    html = html + '<span class="level-name name-first">'+ obj[i].tag +'</span>';
                }
                
                html += '</li>';
            }
       }

        $('.loading').hide();
        $('.pcomment-list').html(html);
        
        // 滚动到可视区域
        var anchor = $('#anchor_' + tag);
        if (anchor[0]) {
             // 发评论刷新,滚动到最后一条记录
            if (flag === 'save') {
                $(window).scrollTop(anchor.offset().top);
            } else if (flag === 'page') {
                // 普通浏览滚动到评论开始位置
                $(window).scrollTop($('#anchorReply').offset().top);
            }
            
        }
        
        // 解析表情
        $('.comment').each(function(k, v){
            weiboFace.parse($(v).html(), function(content){
                $(v).html(content);
            })
        })

    }
})