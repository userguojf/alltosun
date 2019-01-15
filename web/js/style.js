/** 
 *Description：银泰网巨惠通页面样式js
 *Date       ：2014.06.11
 *Author     ：lij
**/
$(function(){ 
  $('.couponList li:nth-child(3n)').css('marginRight',0);
  $('.coupshow table tr:last-child td').css('borderBottom' , 'none');
  $('.bs-drop-list li:nth-child(6n)').css('marginRight',0);
  
  //商品分类查看更多
  $('.btnBusi').click(function(){
	 $('.busi-menu').toggleClass('hidden'); 
  })
  
  var $category = $('.dropLIst li:gt(17)');
  $category.hide();
  var $toggleBtn = $('.more');
  $toggleBtn.click(function(){
	if($category.is(":visible")){
	  $category.hide();
	  $toggleBtn.text("查看更多");
	}else{
	  $category.show();
	  $toggleBtn.text("收起更多");
	}
  })
  
  
});















