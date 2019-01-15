 var drag=function(obj){
            
                obj.bind("mousedown",start);
 
                function start(event){
                	console.log(event.clientX)
                    if(event.button==0){//判断是否点击鼠标左键
                        /*
                         * clientX和clientY代表鼠标当前的横纵坐标
                         * offset()该方法返回的对象包含两个整型属性：top 和 left，以像素计。此方法只对可见元素有效。
                         * bind()绑定事件，同样unbind解绑定，此效果的实现最后必须要解绑定，否则鼠标松开后拖拽效果依然存在
                         * getX获取当前鼠标横坐标和对象离屏幕左侧距离之差（也就是left）值，
                         * getY和getX同样道理，这两个差值就是鼠标相对于对象的定位，因为拖拽后鼠标和拖拽对象的相对位置是不变的
                         */
                        gapX=event.clientX-obj.offset().left;
                        gapY=event.clientY-obj.offset().top;
                        
                        console.log(event.clientX)
                         console.log(obj.offset().left)
                        //movemove事件必须绑定到$(document)上，鼠标移动是在整个屏幕上的
                        $(document).bind("mousemove",move);
                        //此处的$(document)可以改为obj
                        $(document).bind("mouseup",stop);
                       
                    }
                    return false;//阻止默认事件或冒泡
                }
                function move(event){
                    obj.css({
                        "left":(event.clientX-gapX)+"px",
                        "top":(event.clientY-gapY)+"px"
                    });
                    return false;//阻止默认事件或冒泡
                }
                function stop(){
                    //解绑定，这一步很必要，前面有解释
                    $(document).unbind("mousemove",move);
                    $(document).unbind("mouseup",stop);
                    
                }
            }
            obj=$("#pic");
            drag(obj);//传入的必须是jQuery对象，否则不能调用jQuery的自定义函数
            
//            var x,y,start = false,X,Y;
//            $('#pic').on('mousedown',function(e){
//            	start = true;
//            	x = e.clientX; y=e.clientY;
//            })
//            $('#pic').on('mousemove',function(e){
//            	if(start === true) {
//            		x = e.clientX; y= e.clientY;
//            		var picL = $('.picture-box').offset().left; // 图片距离左侧距离
//            		var picT = $('.picture-box').offset().top; // 图片距离顶部距离
//            		var W =$('.picture-box').innerWidth() //图片宽
//            		var H =$('.picture-box').innerHeight() // 图片高
//            		var w = $('#pic').innerWidth(); // 文本框宽度
//            		var h = $('#pic').innerHeight(); // 文本框高度
//            		// 计算鼠标可移动范围
//            		// picL + w / 2 < X < picL + W - w / 2 
//            		// picT + h / 2 < Y < picT + H - h / 2
//            		if(picL + w / 2 > x) {
//            			X = w / 2;
//            		} else if(picL + W - w / 2 < x) {
//            			X = W - w / 2;
//            		} else {
//            			X = x - picL;
//            		}
//            		if(picT + h / 2 > y) {
//            			Y = h / 2;
//            		} else if(picT + H - h / 2 < y) {
//            			Y = H - h / 2;
//            		} else {
//            			Y = y - picT;
//            		}
//            	// 赋值
//            		$('#pic').css({
//            		    "left":X+"px",
//            		    "top":Y+"px"
//            		})	
//            	}
//            })
//            $('#pic').on('mouseup',function(e){
//            	start = false;
//            })