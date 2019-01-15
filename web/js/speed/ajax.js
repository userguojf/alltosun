/**
 * 原生ajax封装
 * @author 王磊
 * @param method	请求方法，post，get...
 * @param url		请求地址
 * @returns {Ajax}
 */
function Ajax(method, url)
{
	this.httpRequest     = new XMLHttpRequest();
	this.method   = method;
	this.url      = url;

	this.open();
}
Ajax.prototype = {
	/**
	 * 打开请求
	 */
	open: function()
	{
		this.httpRequest.open(this.method, this.url);
	},
	/**
	 * 发送请求
	 * @param	data	发送参数，如果不需要则不用传该参数
	 */
	send: function(data)
	{
		if (typeof(data) == 'undefined') {
			this.httpRequest.send();
		} else {
			this.httpRequest.send(data);
		}

		return true;
	},
	/**
	 * 监听progress事件
	 * @param	回调函数
	 */
	onprogress: function(progress){
		
		if (typeof(progress) == 'undefined') {
			return false;
		}
		// this.httpRequest.onprogress = progress;

			this.httpRequest.addEventListener("progress", progress, false);

		return true;
	},
	/**
	 * 监听加载完成事件
	 * @param	回调函数
	 */
	onload: function(load)
	{
		if (typeof(load) == 'undefined') {
			return false;
		}

		// this.httpRequest.onload = load;

			this.httpRequest.addEventListener("load", load, false);

		return false;
	},
	/**
	 * 监听错误事件
	 * @param	回调函数
	 */
	onerror: function(error)
	{
		if (typeof(error) == 'undefined') {
			return false;
		}

//		this.httpRequest.onerror = error;

			this.httpRequest.addEventListener("error", error, false);

		return true;
	},
	/**
	 * 监听取消事件
	 * @param	回调函数
	 */
	onabort: function(abort)
	{
		if (typeof(abort) == 'undefined') {
			return false;
		}

		//this.httpRequest.onabort = abort;

			this.httpRequest.addEventListener("abort", abort, false);

		return true;
	},
	abort: function()
	{
		this.httpRequest.abort();
	}
}