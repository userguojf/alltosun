/**
 * 测试网速
 * @returns {network}
 */
function network()
{
	// 探测地址
	this.url;
	// 每隔interval毫秒计算一次速度
	this.interval;
	// ajax
	this.ajax;

	// 记录请求开始时间
	this.start		= null;
	// 锁
	this.lock		= false;
	// 上一次加载时间
	this.last_time	= 0;
	// 上一次加载大小
	this.last_size	= 0;
	// 保存加载信息的队列
	this.queue = [];

	// 初始化
    this.init();
}
network.prototype = {
	/**
	 * 初始化
	 */
	init: function()
	{
		// 开始时间
		this.start = null;
		// 是否锁定
        this.lock  = false;
        // 上次加载时间
        this.last_time = (new Date()).getTime();
        // 上次加载大小
        this.last_size = 0;
	},
	/**
	 * 探测
	 * @param	String	url	探测地址
	 * @param	int		每隔interval毫秒收集一次数据
	 */
	probe: function(url, interval)
	{
		var network = this;

		if (typeof(url) == 'undefined' || !url) {
			return false;
		}

		if (typeof(interval) == 'undefined' || !interval) {
			return false;
		}

		this.url		= url;
		this.interval	= interval;

		// 注：同一个对象，不同同时测试两个节点
		if (this.start !== null) {
            return false;
        }

		// ajax
		this.ajax  = new Ajax('GET', url);
        // 记录开始请求时间
        this.start = (new Date()).getTime();

        // 加载完成
        this.ajax.onload(function(e){
        	network.onload(e);
        });

        // 加载失败
        this.ajax.onerror(function(e){
        	network.onerror(e);
        });

        // 取消加载
        this.ajax.onabort(function(e){
        	network.onabort(e);
        });

        // 加载进度
        this.ajax.onprogress(function(e){
        	network.onprogress(e);
        });

        // 发送请求
        this.ajax.send();

        return true;
	},
	/**
	 * 加载进度
	 */
	onprogress: function(e)
	{
		if (e.loaded == e.total) {
			console.log('加载完成：', e);
		}

		var network = this;

		/** 控制程序每此执行间隔 this.interval毫秒 **/
		if (network.lock) {
			return network.lock;
		}
		network.lock = true;

		// 注：每秒执行一次
		setTimeout(function(){
			network.lock = false;
        }, network.interval);
		/** end **/

		// 当前时间，单位：毫秒
        var now_time = (new Date()).getTime();
        // 当前已经加载内容大小，单位：字节
        var now_size = e.loaded;

        // 计算当前时间距离上次时间过了多少毫秒
        var diif_time = now_time - network.last_time;
        // 计算上次加载和本次加载的差值大小
        var diif_size = now_size - network.last_size;
// console.log(now_time, now_size);
        var info = {
        	'size':	diif_size,	// 加载大小
        	'time': diif_time,	// 时间
        	'over': false		// 是否完成
        };
        console.log(info);
        network.queue.push(info);

        // 记住本次加载大小
        network.last_size = now_size;
        // 记住本次加载时间
        network.last_time = now_time;
	},
	/**
	 * 加载完成
	 */
	onload: function(e)
	{
		console.log('load', e);

		var network = this;

		// 当前时间，单位：毫秒
        var now_time  = (new Date()).getTime();

        // 总大小，单位：字节
        var size      = e.loaded;
        // 计算加载完成共费时多少秒
        var time = now_time - network.start;

        var info = {
        	'size':	size,	// 加载大小
        	'time': time,	// 时间
        	'over': true	// 是否完成
        };

        network.queue.push(info);

        // 重新初始化
        network.init();
	},
	/**
	 * 加载错误
	 */
	onerror: function(e)
	{
		// 拼返回格式
    	var info = {
    		'size'  : '0',
    		'time'  : '0',
    		'over'  : true,
    		'msg'	: '加载错误'
    	};
        this.queue.push(info);

		// 重新初始化
		this.init();
	},
	/**
	 * 取消加载
	 */
	onabort: function(e)
	{
		// 拼返回格式
    	var param = {
    		'size'  : '0',
    		'time'  : '0',
    		'over'	: true,
    		'msg'	: '取消加载'
    	};
    	this.queue.push(param);

		// 重新初始化
		this.init();
	},
	/**
	 * 取消加载
	 */
	abort: function()
	{
		this.ajax.abort();
	},
	/**
	 * 获取信息
	 * @return	object
	 */
	get_velocity: function()
	{
		var len = this.queue.length;

		if (len < 1) {
			return {
				'size': 0,
				'time': 0,
				'over': false
			};
		}

		return this.queue.shift();
	}
};