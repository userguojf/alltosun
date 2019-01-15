$(function(){
	
	var slyScoreExchange = null;
	
	//积分兑换商品轮播
	slyScoreExchange = new Sly('#scoreExchange', {
        horizontal: 1,
		itemNav: 'basic',
		smart: 1,
		activateOn: 'click',
		mouseDragging: 1,
		touchDragging: 1,
		releaseSwing: 1,
		startAt: 3,
		scrollBy: 1,
		pagesBar: $('.sc-pages'),
		speed: 300,
		elasticBounds: 1,
		easing: 'easeOutExpo',
		dragHandle: 1,
		dynamicHandle: 1,
		clickBar: 1,
		
		// Cycling
		cycleBy: 'items',
		cycleInterval: 5000,
		pauseOnHover: 1
    });
  	slyScoreExchange.init();
	
});