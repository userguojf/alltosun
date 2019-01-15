

/**
*   html ×ÖÌå
 */
~function () {
    // Gets a high and sets the visual window
    var width = document.body.scrollWidth,
        fontSize = document.getElementsByTagName("html")[0];
    if (width >= 740) {
        width = 740;
    }
    fontSize.style.fontSize = width * 0.05 + "px";
}();
~function () {
    // Gets a high and sets the visual window
    window.onresize = function () {
        var width = document.body.scrollWidth,
            fontSize = document.getElementsByTagName("html")[0];
        if (width >= 740) {
            width = 740;
            fontSize.style.fontSize = width * 0.05 + "px";
            return;
        }
        fontSize.style.fontSize = width * 0.05 + "px";
    }
}();

/**
*  surfing
 */


$(function(){
	var slideLength = $('.slide .swiper-slide').length;
	console.log(slideLength);
	if (slideLength>1){
		var mySwiper = new Swiper('.slide', {
			loop: true,
			pagination: '.swiper-pagination',
			autoplay: 3000,
			autoplayDisableOnInteraction: false
		});
	}
    $(".header_close").on("click", function () {
        $(".wrapLayer").addClass("hide");
    });
    $(".download_close").on("click", function () {
        $(".download_txt").addClass("hide");
    });
	
	var slideLength = $('.discount_list .swiper-slide').length;
	console.log(slideLength);
	if (slideLength>2){
		var mySwiper2 = new Swiper('.discount_list', {
			loop: true,
			autoplay: 3000,
			slidesPerView: 2,
			nextButton: '.discount_list .swiper-button-next',
			prevButton: '.discount_list .swiper-button-prev',
			autoplayDisableOnInteraction: false
		});
	}
	
	$('.js_productMore').click(function(){
	  var h = $('.product_list').find('dl').height();
	  var count = Math.ceil($('.product_list dl').length / 2);
	  $('.product_list').animate({ height:h*count }, function(){
	    $('.js_productMore').parent('.loading').hide();
	  });
	});
	
	$('.js_discountMore').click(function(){
	  var h = $('.discount_list').find('li').height();
	  var count = Math.ceil($('.discount_list li').length / 2);
	  $('.discount_list').animate({ height:h*count }, function(){
	    $('.js_discountMore').parent('.loading').hide();
	  });
	});
	
	$('.js_videoMore').click(function(){
	  var h = $('.js_videoList').find('dl').height();
	  var count = Math.ceil($('.js_videoList dl').length / 2);
	  $('.js_videoList').animate({ height:h*count }, function(){
	    $('.js_videoMore').parent('.loading').hide();
	  });
	});
	
	
	
});




