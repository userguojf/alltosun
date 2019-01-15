/**
 * 图片轮播模块
 * 帖子列表图片
 */
define(function (require, exports, module) {
    var $ = require('jquery');
    var f = require('jquery.func');
    var rotateImage = require('rotateImage');
    
    // 图片点击放大模块
    var viewImage = require('thread/viewImage');
    viewImage.init();
    
    $(function(){

        /**
         * 帖子列表点击小图切换到图片展示模块
         */
        $('.seeBigImg').live('click', function(){
            var id = $(this).attr('id');
            var selectIndex = $(this).data('index');
            var mapBox = $('#mapBox_'+id);
            var params = {};
            
            params.thread_id = id;
            $.post(siteUrl + '/?anu=thread/ajax/get_pic_list', params, function(json){
                mapBox.hide();
                bigImgDom(json, selectIndex);
            }, 'json')
            
        })
        
        /**
         * 图片展示模块DOM元素
         * @param 组合DOM元素信息对象
         */
        var bigImgDom = function(obj, selectIndex) {
            var    html = '';
            var    display = 'none';
            var    superSmallImg = '';
            var    superBigImg = '';
            var    boxId = '#bigViewBox_'+ obj.id;
            var    bigViewBox = $(boxId);
            var    index = $(this).data('index');
            
            html = html + '<p class="pv-txt">';
            html = html + '<a href="javascript:void(0);" class="retract smallViewBox" id="'+ obj.id +'"  ><i></i>收起</a><span class="line">|</span>';
            html = html + '<a href="javascript:void(0);" class="showbig" id="' + obj.id + '"><i></i>查看大图</a><span class="line">|</span>';
            html = html + '<a href="javascript:void(0);" class="turnleft"><i></i>向左转</a><span class="line">|</span>';
            html = html + '<a href="javascript:void(0);" class="turnright"><i></i>向右转</a>';
            html = html + '</p>';
            html = html + '<div class="pv-wrap">';
            html = html + '<div class="showimg">';
              html = html + '<div class="pv-bigimg pvcur"  id="imgBig_'+ obj.id +'">';
                html = html + '<ul class="biglist clearfix">';
                
                for(var i = 0; i < obj.big_pic_list.length; i++) {
                    html = html + '<li><img src="' + obj.big_pic_list[i] + '" alt=""></li>';
                    // 超级大图
                    superBigImg = superBigImg + '<li class="active"><img src="' + obj.pic_list[i] + '" alt=""></li>';
                }
                
                html = html + '</ul>';
              html = html + '</div>';
              
              // 单张图片不显示下列滚动
              if (obj.big_pic_list.length > 1) {
                  display = 'block';
              }
              
              html = html + '<a href="javascript:void(0);" style="display:' + display+'" class="arrowbig bignext bignext_' + obj.id + '"></a>';
              html = html + '<a href="javascript:void(0);"  style="display:' + display+'"  class="arrowbig bigprev bigprev_' + obj.id + '"></a>';
              
            html = html + '</div>';
            html = html + '<div class="showimg"  style="display:'+ display+'">';
             
             html = html + '<div class="pv-smallimg" id="imgSmall_' + obj.id + '">';
             html = html + '<ul class="smalllist clearfix">';
             
              for(var i = 0; i < obj.small_pic_list.length; i++) {
                  html = html + '<li><img src="' + obj.small_pic_list[i] + '" alt=""></li>';
                  // 超级小图
                  superSmallImg = superSmallImg + '<li class=""><img src="' + obj.small_pic_list[i] + '" alt=""></li>';
              }
              
             html = html + '</ul>';
           html = html + '</div>';
           html = html + '<a href="javascript:void(0);" class="arrowsmall smallnext smallnext_'+ obj.id +'"></a>';
           html = html + '<a href="javascript:void(0);" class="arrowsmall smallprev smallprev_'+ obj.id +'"></a>';

            htm = html + '</div>';
          html = html + '</div>';
          bigViewBox.html(html).show();
          // 绑定小图
          bindSlyImg('#imgBig_' + obj.id, '#imgSmall_'+obj.id, '.bignext_'+obj.id, '.bigprev_'+obj.id,  '.smallnext_' + obj.id, '.smallprev_' + obj.id, index);

          
          // 准备大图DOM结构
          $('#imgSuperBig ul').html(superBigImg);
          $('#imgSuperSmall ul').html(superSmallImg);
          
          // 关闭大图
          $('.bigImgPopBoxClose').click(function(){
               $('.bigImgPopBox').hide();
          })
          
          // 绑定查看大图
          $(boxId + ' .showbig').live('click', function(){
              var id = $(this).attr('id');
              var index = $('#imgBig_' + id + 'li').filter('.active').index();
              $('.bigImgPopBox').show();
              bindSlyImg('#imgSuperBig', '#imgSuperSmall', 'bigprev', 'null', '.spuersmallnext', '.smallprev', index);
          })
          
          
          $(boxId + ' .turnleft').click(function(){
              var img = $(this).closest('.previewbox').find('.biglist li.active img');
              if (!img.length) img = $(this).closest('.previewbox').find('.biglist li:eq(0) img');
              // 需要旋转的图片，外框最大宽度，外框最大高度
              rotateImage.rotateLeft(img, 440, null, 'pv-bigimg');
          });
          
          $(boxId + ' .turnright').click(function(){
              var img = $(this).closest('.previewbox').find('.biglist li.active img');
              if (!img.length) img = $(this).closest('.previewbox').find('.biglist li:eq(0) img');
              // 需要旋转的图片，外框最大宽度，外框最大高度
              rotateImage.rotateRight(img, 440, null, 'pv-bigimg');
          });
          
          // liw add 点击大图隐藏
          $(boxId + ' .biglist li img').click(function(){
            $(this).closest('.previewbox').find('.smallViewBox').trigger('click');
          });
          
          // liw add 限制最大宽度，高度不限制
          $.each(bigViewBox.find('.biglist li img'), function(){
            var tmpImg = $(this);
            f.imgReady(tmpImg.attr('src'), function(){
              if (this.width > 440) {
                tmpImg.width(440);
              }
            });
          });
        }
        
        // 图片展示模块，收起功能  jQuery 1.9 live 被 delegate 替换
        $('body').delegate('.smallViewBox', 'click', function(){
            var id = $(this).attr('id');
            var bigViewBox = $('#bigViewBox_'+ id);
            var mapBox = $('#mapBox_'+id);
            bigViewBox.hide();
            mapBox.show();
        })
        
        // 绑定一个展示模块
        function bindSlyImg(bigImg, smallImg, bigNext, bigPrev, smallNext, smallPrev, selectIndex) {
            var slyPostsPhotoBig = null;
            var slyPostsPhotoSmall = null;
            slyPostsPhotoBig = new Sly(bigImg, {
                horizontal: 1,
                itemNav: 'centered',
                smart: 1,
                activateOn: 'click',
                mouseDragging: 1,
                touchDragging: 1,
                releaseSwing: 1,
                startAt: 0,
                scrollBy: 1,
                speed: 300,
                elasticBounds: 1,
                //easing: 'easeOutExpo',
                dragHandle: 1,
                dynamicHandle: 1,
                clickBar: 1,
                
                // Buttons
                prev: $(bigPrev),
                next: $(bigNext)
            });
            
            slyPostsPhotoSmall = new Sly(smallImg, {
                horizontal: 1,
                itemNav: 'basic',
                smart: 1,
                activateOn: 'click',
                mouseDragging: 1,
                touchDragging: 1,
                releaseSwing: 1,
                startAt: 0,
                scrollBy: 1,
                speed: 300,
                elasticBounds: 1,
                //easing: 'easeOutExpo',
                dragHandle: 1,
                dynamicHandle: 1,
                clickBar: 1,
                
                // Buttons
                prevPage: $(smallPrev),
                nextPage: $(smallNext)
            });
            
            // 点击小图切换大图
            $(smallImg).find('li').bind('click', function(){
              slyPostsPhotoBig.activate($(this).index());
            });
            // 大图滚动完毕回调
            slyPostsPhotoBig.on('active', function(eventName, index){
                slyPostsPhotoSmall.activate(index);
                // 其他的图隐藏 liw add
                var nowLi = $(this.slidee).find('li:eq('+index+')');
                var rotateType = nowLi.find('img').attr('data-rotate_type');
                f.imgReady(nowLi.find('img').attr('src'), function(){
                  var me = this;
                  setTimeout(function(){
                    if (rotateType == 1) {
                      var nowHeight = nowLi.find('img').width();
                    } else {
                      var nowHeight = nowLi.find('img').height();
                    }
                    nowLi.closest('.pv-bigimg').height(nowHeight);
                  }, 1);
                });
            });
            
            // 选中的当前图片
            if (selectIndex) {
                slyPostsPhotoBig.activate(selectIndex);
                slyPostsPhotoSmall.activate(selectIndex);
            } else {
              slyPostsPhotoBig.activate(0);
            }
            
            slyPostsPhotoBig.init();
            slyPostsPhotoSmall.init();
        }

    });
})
