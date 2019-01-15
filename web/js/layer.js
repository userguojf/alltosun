;(function($) {
    var selectedOpts = {},
        type, msgWrapper, finalops = {},
        icon = "", overlay,
        _init = function(opts) {
            
            var defaults = {
                width: "auto",
                height: "auto",
                autoClose: true,
                timeout: 2000,
                header: '提示消息',
                icon: false,
                iconclss: "icon-msg",
                showCloseButton: true,
                hideOnContentClick: false,

                leftbtn: "",
                leftbtn: "",

                overlayShow: true,
                overlayOpacity: 0.2,
                overlayColor: '#777',
                hideOnOverlayClick: false
            };
            
            selectedOpts = $.extend(defaults, opts);
            _create();
            _pos();
//          _finish();
        },
        _finish = function() {
            $(window).unbind("resize.lay scroll.lay");
            $(window).bind("resize.lay", _pos);
            $(window).bind("scroll.lay", _pos);
            if (selectedOpts.hideOnContentClick) {
                msgWrapper.bind('click', _close);
            }

            if (selectedOpts.hideOnOverlayClick) {
                overlay.bind('click', _close);
            }
        },
        _create = function() {
            var info, btns, clickFn;
//          msgWrapper = $('<div class="popwindows"></div>').appendTo($("body"));
            msgWrapper = $('<div class="scorepop common-pop controBox"></div>').appendTo($("body"));
            msgWrapperInner = $('<div class="inner-scorepop"></div>').appendTo(msgWrapper);
            msgWrapperInner.append(tip = $('<div class="cpop-top clearfix"><a href="javascript:void(0);" class="btn-close right">×</a><span>提示</span></div>'), msgBody=$('<div class="cpopc"></div>'));
            
            if (selectedOpts.show_save_box == 1) {
                msgBody.append(info = $('<p class="tips-sc1"></div>'), btns=$('<p class="btn-wrap-sc"></p>'));
                saveInfo = $('<form action ="'+siteUrl+'/credit_lottery/save_info" method="post" class="saveWinnerInfo"></form>').insertAfter(info);
                saveInfo.append('<input type="hidden" name="lottery_id"   class="winnerLotteryId" value="'+selectedOpts.lottery_id+'">');
                saveInfo.append('<input type="hidden" name="prize_level"  class="winnerPrizeLevel" value="'+selectedOpts.prize_level+'">');
                saveInfo.append('<p class="tips-sc2">请填写领奖信息</p>');
                saveInfo.append('<p class="scfill"><label><span>收货人：</span><input type="text" name="name" class="winnerName"></label></p>');
                saveInfo.append('<p class="scfill"><label><span>电话：</span><input type="text" name="telephone" class="winnerTelephone"></label></p>');
                saveInfo.append('<p class="scfill"><label><span>收货地址：</span><input type="text" name="address" class="winnerAddress"></label></p>');
                saveInfo.append('<p class="scfill"><label><span>邮编：</span><input type="text" name="post_code" class="winnerPostCode"></label></p>');
            } else if ((selectedOpts.lottery_id == 1 && selectedOpts.prize_level == 4) || (selectedOpts.lottery_id == 4 && selectedOpts.prize_level == 5) || (selectedOpts.lottery_id == 3 && selectedOpts.prize_level == 3)) {
              msgBody.append(info = $('<p class="tips-sc1"></div>'), btns=$('<p class="btn-wrap-sc"></p>'));
              saveInfo = $('<form action ="'+siteUrl+'/credit_lottery/save_info" method="post" class="saveWinnerInfo"></form>').insertAfter(info);
              saveInfo.append('<input type="hidden" name="lottery_id"   class="winnerLotteryId" value="'+selectedOpts.lottery_id+'">');
              saveInfo.append('<input type="hidden" name="prize_level"  class="winnerPrizeLevel" value="'+selectedOpts.prize_level+'">');
              saveInfo.append('<p class="tips-sc2">优惠码：NBASINA1025（请妥善保存）</p>');
            } else {
              msgBody.append(info = $('<p class="tips-w2"></p>'), btns=$('<p class="btn-wrap-sc"></p>'));
            }
            
            //msgWrapper.append(info = $('<div class="popbody"></div>'), btns = $('<div class="btnbox clearfix"></div>'));
//          if(selectedOpts.img){
//              info.append('<div class="popwindowsimg">' + selectedOpts.img + '</div>');
//          }
//          var txt = $('<div class="popwindowstxt"></div>').appendTo(info);
            var txt = info;

//          if(selectedOpts.header){
//              txt.append('<h4>' + selectedOpts.header + "</h4>");
//          }
            if(selectedOpts.content){
//              txt.append("<p>"+selectedOpts.content +"</p>");
                txt.append(selectedOpts.content);
            }
            
            $('.btn-close').click(function(){
              _close();
            });
            /*info.append('<div class="popwindowsimg">' + selectedOpts.img + '</div>', 
                        '<div class="popwindowstxt"><h4>' + selectedOpts.header + "</h4><p>"+selectedOpts.content +"</p></div>");*/

            if (selectedOpts.leftbtn && selectedOpts.rightbtn) {
                btns = _setBtns(selectedOpts.leftbtn, btns, "no closepop");
                btns = _setBtns(selectedOpts.rightbtn, btns, "btn-pop btn-ok");
            } else if (selectedOpts.leftbtn) {
                btns = _setBtns(selectedOpts.leftbtn, btns, "btn-pop btn-ok");
            } else {
                /*if (selectedOpts.icon) {
                    icon = "<span class=" + selectedOpts.iconclss + "></span>"
                }
                info.append('<h3 class="tit poppic">' + selectedOpts.header + '</h3>', icon + "<div class='content'>" + selectedOpts.content + "</span>");
                if (selectedOpts.autoClose) {
                    _autoClose();
                }*/
            }

//          if (selectedOpts.hideOnContentClick) {
//              $('<span class="icon-close"></span>').insertBefore('.info').bind("click", _close);
//          }

            if (selectedOpts.overlayShow) {
                overlay = $('<div id="layer-overlay"></div>');
                overlay.css({
                    'background-color': selectedOpts.overlayColor,
                    'opacity': selectedOpts.overlayOpacity,
                    'cursor': selectedOpts.hideOnOverlayClick ? 'pointer': 'auto'

                }).appendTo('body');
                overlay.width($(window).width());
                overlay.height($(window).height());
            }

        },
        _setBtns = function(btn, btns, claSty){
            claSty = claSty ? claSty.toString() : "";
            var _btn = $('<a href="javascript:;" class="btn"></a>').addClass(claSty);
            switch(typeof(btn)) {
                case "object":
                    if (!btn.text)
                        break;
                    
                    if ($.isFunction(btn.callback)) {
                        claSty = true;
                        _btn.click(btn.callback);
                    }
                    if (btn.url) {
                        _btn.attr("href", btn.url);
                    }
                    btn = btn.text;
                case "string":
                    (claSty !== true) && (_btn.click(_close));
                    btns.append(_btn.html(btn));
                    break;
            }
            return btns;
        },
        _autoClose = function() {
            selectedOpts.timeout = (/^\d+$/.test(selectedOpts.timeout)) ? selectedOpts.timeout: $.fn.layer.defaults.timeout;
            setTimeout(function() {
                _close();
                if (selectedOpts.url) {
                    window.location.assign(selectedOpts.url);
                } else if ($.isFunction(selectedOpts.callback)) {
                    selectedOpts.callback();
                }
            },
            selectedOpts.timeout);
        },
        _pos = function() {
            var leftops = ($(window).width() - msgWrapper.width()) / 2,
            topops = ($(window).height() - msgWrapper.height()) / 2;
            finalops = {
                x: leftops,
                y: topops
            }
            _setpos();
        },
        _setpos = function() {
          
            msgWrapper.each(function() {
                //$(this).css("left", finalops.x + $(document).scrollLeft());
                //$(this).css("top", finalops.y + $(document).scrollTop())
                $(this).css("left", '27%');
                $(this).css("top", '930px')
            });
            if (overlay) {
              overlay.css({/*
                'top': $(document).scrollTop(),
                'left': $(document).scrollLeft(),*/
                'height': $(window).height(),
                'width': $(window).width()
              })
            }
        },
        _close = function() {
            msgWrapper.remove();
            if (overlay) {
              overlay.remove();
            }
            $(window).unbind("resize.lay scroll.lay");
        };

        $.layer = function(opts) {
            if (typeof(opts) === "string") {
                opts = {content: opts};
            }
            
            _init(opts)
        },
        $.layer.close = _close;
})(jQuery);