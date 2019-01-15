define(function(require, exports, mdoule){
    var $       = require('jquery');
    var anUrl   = window.AnUrl;
    /**
     * 聚会通加载
     * js加载数据 模块化 单独加载使用 适应不同的页面
     */
    var loadIndexData = {
        _lock      : false,
        _event     : 'click',
        _btn       : '',
        _htmlBox   : '',
        _num       : 1,
        _ajaxUrl   : '',
        _params    : {},
        func       : 'addCouponHtml',
        _more_href : '',
        _perPage   : 10,
        _ser       : 0,
        _jsCoupon  : '',
        _pageNum   : 3,
        _submenu   : '.submenu',
        //入口
        init   : function(event) {
            this._btn       = event.btn;
            this._htmlBox   = event.box;
            this._ajaxUrl   = event.ajaxUrl;
            this._more_href = event.more_href;
            this._perPage   = event.perPage;
            this._jsCoupon  = event.jsIconMenuCoupon;
            
            if (event.pageNum > 0) {
                this._pageNum   = event.pageNum;
            }
            this.bindEvent();
        },
        //绑定事件
        bindEvent:function() {
            var obj = this;

            $(this._btn).live(this._event,function(){
                if (obj._lock == false) {
                    //加锁
                    obj._lock = true;
                    //判断
	                if (obj._num == obj._pageNum) {
	                    obj._btn.attr('href',obj._more_href);
	                    return;
	                } else {
	                    obj.loadpageshow('加载中...');
	                    //加载数据
	                    obj.loadData();
	                }
                }
            })
        },
        //加载数据
        loadData : function() {
            ++ this._num;

            this._params.page       = this._num;
            this._params.per_page   = this._perPage;
            this._params.seller_id  = this._ser;

            var obj = this;
            $.get(this._ajaxUrl,this._params,function(data){
                if (data.info == 'ok') {
                    //添加数据
                    obj.addCouponHtml(data.data);

                    if (data.num < obj._perPage || data.page == data.end_page) {
                    	if (data.type == 'recomm') {
                            obj.loadpageshow('更多');
                            $(obj._btn).attr('href',obj._more_href);
                    	} else {
                            obj.loadpageshow('已全部加载');
                            $(obj._btn).attr('href','javascript:;');
                    	}

                        window.cpsReplaceUrl();
                        window.fReplaceUrl();
                        obj._lock = true;
                        return false;
                    } else if (obj._num == obj._pageNum){
                       obj.loadpageshow('更多');
                       $(obj._btn).attr('href',obj._more_href);
                       window.cpsReplaceUrl();
                       window.fReplaceUrl();
                       return false;
                    } else {
                       obj.loadpageshow('<i class="icon"></i>加载更多');
                       $(obj._btn).attr('href','javascript:;');
                       window.cpsReplaceUrl();
                       window.fReplaceUrl();
                       obj._lock = false;
                    }
                    
                } else {
                	
                	//console.log($(obj._btn));
                    if ($(obj._btn).parent().css('display')=='none') {
                        $(obj._btn).parent().show();
                    }

                    $(obj._btn).html('暂无该商家内容');
                    obj._lock = true;
                }
                window.htmldomheight(obj._htmlBox);
            },'json')
        },
        //添加dom
        addCouponHtml:function(data) {
            $(this._htmlBox).append(data);
        },
        loadpageshow :function(html){
            $(this._btn).html(html);
        },
        startObj : function() {
            var func = new Function;
            func.prototype = loadIndexData;
            var addCoupon = new func;
            return addCoupon;
         },
         clearHtml : function(){
             this._num   = 0;
             this._lock  = false;
             $(this._htmlBox).find('li').remove();
         },
         btnShow:function(obj){
             $(obj._jsCoupon).live('click',function(){
                 obj.submenuShow();
             })
         }
    }

    //侧边栏的显示
    loadIndexData.submenuShow = function() {
        var submenu = this._submenu;
        var bHeight = $(window).height();
        $(submenu).css({ height:bHeight+'px' });

        var sidebarW = parseInt($(submenu).css('width'));
        if ( sidebarW == 0) {
        	loadIndexData.OpenShow(submenu);
        } else {
        	loadIndexData.CloseShow(submenu);
        }
    }
    
    loadIndexData.OpenShow = function(sub) {
        $('#wrap').animate({ marginRight:'120px',marginLeft:'-120px' },150);
        $(sub).css({ position:'fixed', zIndex:'1001'}).show().animate({ width:'120px' },150);
    }

    loadIndexData.CloseShow = function(sub) {
        $('#wrap').animate({ marginRight:'0px',marginLeft:'0px' },150);
        $(sub).css({ position:'absolute' }).animate({ width:'0px' },150, function(){ $(this).hide(); });
    }
    
    //延时加载
    loadIndexData.autoloadHtml = function(box,eve,obj) {
        var that = this;

        $(box).bind(eve, function () {
            var windowHeight = $(window).height();
            var scrollTop = $(window).scrollTop();
            var bodyHeight = $(document).height();
            if (windowHeight + scrollTop < bodyHeight) {
                return;
            }

            var event = that._event;
            $(obj)[event]();
        });
    }

    //团购的GPS定位 h5地理位置获取坐标
    loadIndexData.getLocation = function () {
    	if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position){
            var x = position.coords.latitude;
            var y = position.coords.longitude;

            $.post(AnUrl('e/ajax/get_city_id'),{ x:x,y:y },function(data) {
                if (data.info == 'ok') {

                 if (data.city_id) {
                    city_id = data.city_id;
                 }
                 loadIndexData.getLocation.back(city_id);
                 }
            },'json');

            });
        }

        loadIndexData.getLocation.back(51);
    }

    loadIndexData.getLocation.back = function (city_id) {
        if ($("#selectGroupCity option").is( function(){ return $(this).val()== city_id })) 
        {
             window.city_id = city_id;
        }

        $('#selectGroupCity option').attr('selected',false);
        var option = $('#selectGroupCity option').filter(function(){ return $(this).val()== city_id; });

        setCookie('city_id',city_id,{ 'expires': 3600 })
        $(option).attr('selected',true);
        $('#selectGroupCity').change();
    }
    return loadIndexData;
})