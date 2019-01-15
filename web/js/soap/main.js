var touchstart = "ontouchend" in document ? "touchstart" : "mousedown";
var touchmove = "ontouchend" in document ? "touchmove" : "mousemove";
var touchend = "ontouchend" in document ? "touchend" : "mouseup";
$(function(){
	stage.init();
});


var stage = {
	pages:$(".stage"),
	y:0,
	vy:0,
	lock:false,
	prev:0,
	cur:0,
	eh:$("#main").height(),
	init:function(){
		var _this = this;
		_this.pages.each(function(i){
			$(this).stop().css({top:-(_this.cur-i)*_this.eh});
		});
		
		
		_this.addEvent();
		
		$("#main").animate({opacity:1});
		$(document).bind("touchmove",function(event){
			event.preventDefault();
		});
	},
	addEvent:function(){
		var _this = this;
		$(window).resize(function(){
			_this.resize();
		});
		$(document).bind(touchmove,function(e){
			e.preventDefault();
		});
		$(".scroll").click(function(){
			_this.onNext();	
		});
		_this.pages.bind(touchstart,function(e){
			if(!_this.lock){
				var touch = touchstart == "touchstart" ? event.touches[0] : ( e || window.event ) ;
				this.tempY = touch.pageY;
				this.lockd = true;
			}
			this.curY = 0;
		});
		_this.pages.bind(touchmove,function(e){
			var touch = touchstart == "touchstart" ? event.touches[0] : ( e || window.event ) ;
			this.curY = 0;
			if(!_this.lock && this.lockd){
				this.curY = (touch.pageY - this.tempY);
			}
		});
		_this.pages.bind(touchend,function(e){
			
			if(!_this.lock && this.curY < -100 && this.lockd){
				_this.onNext();
			}else if(!_this.lock && this.curY > 100 && this.lockd){
				_this.onPrev();
			}
			this.lockd = false;
		});
		
	},
	resize:function(){
	
	},
	onNext:function(){
		if(this.lock){
			return false;	
		}
		this.cur = (this.cur < this.pages.size()-1 ? this.cur+=1 : this.pages.size()-1);
		this.scroll();
	},
	onPrev:function(){
		if(this.lock){
			return false;	
		}
		this.cur = (this.cur <= 0 ? 0 : this.cur-=1);
		this.scroll();
	},
	scroll:function(){
		var _this = this;
		if(_this.prev==_this.cur){
			return false;
		}
		
		_this.pages.each(function(i){
			$(this).stop().animate({top:-(_this.cur-i)*_this.eh},600,function(){
				if(i==_this.cur){
					_this.lock = false;
					_this.prev = _this.cur;
					_this.animate(_this.cur);
				}
			});
		});
		/*
		
		_this.lock = true;
		_this.pages.eq(_this.prev).animate({opacity:0},300,function(){
			$(this).css("z-index",1).hide();	
		});
		setTimeout(function(){
			_this.pages.eq(_this.cur).css({opacity:0,display:'block',zIndex:5,"transition":"all 0s ease-in","transform":"scale(1.2)"});
			setTimeout(function(){
				_this.pages.eq(_this.cur).css({"transition":"all .4s ease-out","transform":"scale(1)",opacity:1});
				setTimeout(function(){
					_this.pages.eq(_this.cur).css({"transition":"all 0s ease-out"});
					setTimeout(function(){
						_this.lock = false;
						_this.prev = _this.cur;
					},50);
				},400);
			},10);
			_this.animate(_this.cur);
		},300);*/
	},
	start:function(){
		
	},
	animate:function(index){
		var _this = this;
		if(index==0){
			//第一屏
		}
		if(index==1){
			//第二屏
		}
		if(index==2){
			//第三屏
		}
		if(index==3){
			//第四屏
		}
		
		if(index==4){
			//第五屏
			
		}
	}
};
