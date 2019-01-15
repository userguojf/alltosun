// 浮层关闭按钮 begin
$('.popup .pop_close').on('click', function(event) {
  event.preventDefault();
  $(this).parents('.popup').addClass('none');
});
// 浮层关闭按钮 end


// 屏蔽拖动 begin
// $(window).on('touchmove', function(e) {
//   e.preventDefault();
// });
// 屏蔽拖动 end


// CSS动画结束事件 begin
var as = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';
// CSS动画结束事件 end


// 获取url参数 begin
var getParam = function(param) {
  var r = new RegExp("\\?(?:.+&)?" + param + "=(.*?)(?:&.*)?$");
  var m = window.location.toString().match(r);
  return m ? m[1] : "";
};
// 获取url参数 end


// 打包记录
var pkgInfo = [];

// 指定范围内的随机数 begin
var getRnd = function(begin, end) {
  return Math.floor(Math.random() * (end - begin)) + begin;
};
// 指定范围内的随机数 end

$(function() {
	
  // 抽奖页 begin
  if ($('#lottery')[0]) {

    // 显示抽奖页 begin
    $('#lottery').removeClass('none');
    // 显示抽奖页 end

    // 接口url begin
    var port = port = siteUrl + '/wheel/wheel_lottery';
    
    //打印对象
    function alertObj(obj){
		var output = "";
		for(var i in obj){  
			var property=obj[i];  
			output+=i+" = "+property+"\n"; 
		}  
		alert(output);
	}
    
    // 抽奖 begin
    var lock = false;
    $('.dial > .btn').on('click', function(event) {
      event.preventDefault();
      
      if (lock == false) {

        $('.dial > .roll').attr('class', 'roll');
        $.ajax({
            url: port,
            type: 'GET',
            dataType: 'json',
            data: {},
            cache: false
          })


          .done(function(data) {
              console.log(data);
              //alertObj(data);return;
            // 请求成功
            var d = data;

            if (d.code == "10000") {
              var _id = d.id,
                _info = d.info,
                _goodsId = d.goods_id,
                _roll = $('.dial > .roll'),
                _popNoaward = $('#pop-noaward'), //未中奖
                _popAward = $('#pop-award'),  //中奖
                _popNochance = $('#pop-nochance');//机会用完


               // 还有机会
                if (_id == '1') {
                  // 再来一次
                   _popAward.find('.sub-tit > span').html(_info);
                   $('.wheel_btn_s').html('再来一次');

                   $('.wheel_btn_s').one('click',function(){
                	   _popAward.addClass('none');
                	   $('.dial > .btn').click();
                   })

                  _roll.addClass('roll-' + _id).one(as, function() {
                      _popAward.removeClass('none');
//                      $('.wheel_btn_s').attr('href',siteUrl+'/wheel');
                      // 解锁
                      lock = false;
                      // 显示浮层
                  });
                } else if (_id == '2' || _id == '8' || _id == '4' || _id == '5'  || _id == '7') {
                
                    if (_id == 7) {
                        _id = '6';
                    }
                    
                    if (_id == 8) {
                        _id = '3';
                    }

                	_popAward.find('.sub-tit > span').html(_info);
                  // 圆盘动画
                  _roll.addClass('roll-' + _id).one(as, function() {
                    // 显示浮层
                    _popAward.removeClass('none');
                    
                    //_goodsId
                    $('.wheel_btn_s').attr('href',siteUrl+'/user/gift');
                    // 解锁
                    lock = false;
                  });
                }
            } else {
            	$('#pop-nochance').find('.sub-tit').text(d.info);
            	$('#pop-nochance').removeClass('none');
//            	_popNochance.find('.sub-tit').text(d.info);
//            	_popNochance.
            	lock = false;
            }
          })
          .fail(function(data) {
            // 请求失败
            alert('网络错误，请刷新页面重试');
            // 解锁
            lock = false;
          })
          .always(function(data) {
            // console.log("complete");
          });
      }
      // 加锁
      lock = true;
    });
    // 抽奖 end

  }
  // 抽奖页 end
});