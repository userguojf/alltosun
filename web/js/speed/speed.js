function speed()
{
	// 实例化对象
	this.networks = new network();
	// 存放每次加载速度的数组，用于挑选一个平均速度
	this.arrays;
	// 全局计数器
	this.Counter;
	// 计时器
    this.Time;

    this.transform;

    this.click_time;
    this.avg_speed;
}
speed.prototype = {
	init: function()
	{
		this.arrays    = [];
		this.Counter   = 0;
		this.Time      = null;
		this.transform = 0;

		$('.js_speedIntro .speed .arrow').css({ 'transform':'rotate(-0deg)' });

		this.click_time = new Date();
		this.avg_speed  = 0;

		// console.log(this.click_time.getSeconds());
	},
	event: function()
	{
		var sped = this;

		/**
	    * 点击测速按钮
	    */
	    $('.js_speedBtn').click(function(){
	    	//页面效果
	    	 $(this).hide();
	         $('.beat_btn').removeClass('none');
	    	// 初始化
	    	sped.init();

	    	if ($('.js_style').length < 1) {
	    		$('body').prepend('<style class="js_style"></style>');
	    	}

	    	// 动画效果
	        $('.js_speedIntro').show().animate({ left:'-71px' }, 500, function(){
	        	// 探测
	        	var res = sped.networks.probe(siteUrl+'/speed/probe', 1000);

	        	if (res) {
	        		// 开始轮询
	        		sped.poll();
	        	} else {
	        		alert('错误，请稍后再试');
	        	}
		    });
	    });

	    /**
	     * 点击关闭
	     */
	     $('.js_speedClose').click(function(){
	         $('.js_speedIntro').animate({ left:'160px' }, 1000, function(){
	             $(this).hide();
	             // 初始化速度
	             $('.num').find('span').html('测速中');
	             // $('.js_speedIntro .speed .arrow').css({ 'transform':'rotate(0deg)' });
	         });
	     });
	},
	/**
	 * 定期请求当前加载的速度
	 */
	poll: function()
	{
/*
    	var array = [256, 128, 384, 512];
    	var k     = 0;
    	var sped  = this;

    	var time = setInterval(function(){
    		if (k >= array.length) {
    			clearInterval(time);
    		} else {
    			var sudu = array[k ++];
    			// 显示当前速度
    	        $('.js_speedIntro .num').find('span').html(sudu);

    	        sped.SmoothMv(sudu);
    		}
    	}, 1300);
*/
		var sped = this;
		var num  = 1;

		sped.Time = setInterval(function(){
			// 10s内没加载完
			if (num > 7) {
				sped.Stop();
			} else {
				var info = sped.networks.get_velocity();

				sped.parse(info);
			}
			num ++;
		}, 1500);

	},
	/**
	 * 停止
	 */
	Stop: function()
	{
		var sped = this;

		// 停止加载
		sped.networks.abort();
		// 计算平均速度
		var sudu = this.Avg(this.arrays);
		// 保留两位小数
		sudu = parseFloat(sudu.toFixed(2));
		// 保存平均速度
		this.avg_speed = sudu;

		// 平滑移动指针
		this.SmoothMv(sudu);

		// 根据速度，显示不同的文字
		this.showText(sudu);

		// 展示信息
        $('.js_speedIntro').animate({ left:'-256px' }, 600, function(){
        	// 清除轮询
    		clearInterval(sped.Time);
        });
    	console.log(this.networks.queue);

    	// 记录本次测速
    	this.record();

		return true;
	},
	/**
	 * 解析参数
	 * @oaram	object	param
	 * @return		
	 */
	parse: function(param)
	{
		// 没参数
		if (typeof(param) == 'undefined') {
			return false;
		}

		// 加载发生异常，产生错误消息
		if (typeof(param.msg) != 'undefined') {
			return false;
		}

		// 加载完成
		if (param.over) {
			// 加载完成
			this.Stop();

			console.log('加载完成');

			return true;
		}

		if (param.size <= 0) {
			var sudu = 0;
		} else {
			// 将b转为kb
			param.size = param.size / 1024;
			// 将毫秒转成秒
			param.time = param.time / 1000;

			// 计算速度 单位：kb/s
			var sudu   = param.size / param.time;

			// 保留两位小数
			if (sudu > 0) {
				sudu = parseFloat(sudu.toFixed(2));
			}
		}

		// 将速度记录，当加载完成时，计算平均速度
		this.arrays[this.Counter ++] = sudu;

		// 平滑移动指针
		this.SmoothMv(sudu);
	},
	/**
	 * 平滑移动指针
	 * @param	Float	sudu	
	 */
	SmoothMv: function(sudu)
	{
		if (typeof(sudu) == 'undefined') {
			sudu = 0;
		}

		// 显示当前速度
        $('.num').find('span').html(sudu);

		// 计算角度
		var angleNum	= this.angle(sudu);
		// 保留两位小数
		angleNum = parseFloat(angleNum.toFixed(2));

		var stopAnimate = 'webkitAnimationEnd mozAnimationEnd MSAnimationEnd oanimationend animationend';

		 var html = '@-webkit-keyframes rotate {'+
		  'to{ -webkit-transform:rotate(-'+angleNum+'deg);}'+
		'}'+
		'@-moz-keyframes rotate {'+
		  'to{ -webkit-transform:rotate(-'+angleNum+'deg);}'+
		'}'+
		'@-o-keyframes rotate {'+
		  'to{ -webkit-transform:rotate(-'+angleNum+'deg);}'+
		'}'+
		'@keyframes rotate {'+
		  'to{ -webkit-transform:rotate(-'+angleNum+'deg);}'+
		'}';

		$('.js_style').html(html);

		$('.js_speedIntro .speed .arrow').addClass('rotate').one(stopAnimate, function(){
			$(this).css({ 'transform':'rotate(-'+angleNum+'deg)' }).removeClass('rotate');
		});
	},
	/**
	 * 根据速度显示对应的文字
	 * @param	Float	sudu	
	 * @returns	void
	 */
	showText: function(sudu)
	{
		// 小于80k，为慢的
		if (sudu < 80) {
			var text = '<p>蜗牛爬的一样</p><p>去底部吐槽它！</p>';
		// 80k - 150k 为	一般
		} else if (sudu < 150) {
			var text = '<p>这速度可以的</p><p>基本可以赶超自行车<p>';
		// 150k - 200k 为一般快
		} else if (sudu < 200) {
			var text = '<p>知道汽车有多快吗？</p><p>就像现在这速度</p>';
		// 200k以上为最快
		} else {
			var text = '<p>word网速厉害了</p><p>像火箭一样快！</p>';
		}

		$('.js_speedIntro .txt').html(text);
	},
	/**
	 * 计算数组中的平均数
	 * @param	array	nums
	 * @returns	Float
	 */
	Avg: function(nums)
	{
		console.log(nums);
		if (typeof(nums) == 'undefined' || !nums) {
    		return 0;
    	}

    	var len = nums.length;
    	var num = 0;

    	/** 计算数组的和 **/
        for (var i = 0; i < len; i++) {
            num += parseFloat(nums[i]);
        }
    	/** end **/

    	return num / len;
	},
	/**
	 * 根据速度计算角度
	 * @param	Float	sudu
	 * @return	Float
	 */
	angle: function(sudu)
	{
		if (typeof(sudu) == 'undefined' || !sudu) {
			return 0;
		}

		// 网速表上的最大值
        var max = 4 * 1024 / 8;
        // 网速表共有180度，计算每度的值
        var one = max / 180;

        // 计算指针度数
        return sudu / one;
	},
	record: function()
	{
		var click_time = parseInt(this.click_time.getTime() / 1000);
		var speeds     = this.arrays.join(',');

		var url  = siteUrl + '/speed/ajax';
		var post = { 'click_time': click_time, 'speeds': speeds, 'avg_speed': this.avg_speed }

		$.post(url, post, function(json){
			console.log(json);
		}, 'json');
		// console.log(this.click_time.getTime());
	}
}