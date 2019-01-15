/**
 * 个人中心相关
 */
$(function() {

  /**
   * 订单列表tab吸顶 begin
   */
  if ($('.order-list > #order-list-tab')[0]) {
    $(document).ready(function() {
      $('.order-list > #order-list-tab').stickUp();
    });
  }
  /**
   * 订单列表tab吸顶 end
   */

  /**
   * 优惠券列表tab吸顶 begin
   */
  if ($('#my-coupon-tab')[0]) {
    $(document).ready(function() {
      $('#my-coupon-tab').stickUp();
    });
  }
  /**
   * 优惠券列表tab吸顶 end
   */
   
  /**
   * 优惠券规则说明 begin
   */
  $('.btnIntro').click(function(){
    var that = $(this).parents('li').children('.giftIntro');
	if(that.hasClass('hide')){
	  $(this).text("隐藏使用说明");
	  that.removeClass('hide');
	  $(this).parents('li').siblings('li').children('.giftIntro').addClass('hide');
	} else {
	  $(this).text("查看使用说明");
	  that.addClass('hide');
	}
  });
  /**
   * 优惠券规则说明 end
   */

});

