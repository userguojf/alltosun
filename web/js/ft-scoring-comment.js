// [28,470,57] published at 2013-01-15 14:46:53
/**
 * SAB_BASE.js 
 * ----------------------------------------------------------------------------------------------------------
 */

/*window.onerror = (function() {
	var killerror = function(){
		return true;
	}
	if(location.href.indexOf('showerror')==-1){
		return killerror;
	}else{
		return null;
	}
})();*/
var $globalInfo = $globalInfo||{};
if(typeof $globalInfo.SABLoaded == 'undefined'){
	$globalInfo.SABLoaded = false;
	var SAB = (function(){
		var it = {};
		//getElementById
		it.E = function(id){
			if (typeof id === "string") {
				return document.getElementById(id);
			}
			return id;
		};
		//createElement
		it.C = function(tag){
			tag = tag.toUpperCase();
			if (tag == 'TEXT') {
				return document.createTextNode('');
			}
			if (tag == 'BUFFER') {
				return document.createDocumentFragment();
			}
			return document.createElement(tag);
		};
		//register
		it.register = function(namespace, method) {
	        var i   = 0,
				un  = it,
				ns  = namespace.split('.'),
				len = ns.length,
				upp = len - 1,
				key;
			while(i<len){
				key = ns[i];
				if(i==upp){
					if(un[key] !== undefined){
						throw ns + ':: has registered';
					}
					un[key] = method(it);
				}
				if(un[key]===undefined){
					un[key] = {}
				}
				un = un[key];
				i++
			}
	    };
		//register short
		it.regShort = function(key, method){
			if (it[key] !== undefined) {
				throw key + ':: has registered';
			}
	        it[key] = method;
		};
		var Detect = function(){
	        var ua = navigator.userAgent.toLowerCase();
	        this.isIE = /msie/.test(ua);
	        this.isOPERA = /opera/.test(ua);
	        this.isMOZ = /gecko/.test(ua);
	        this.isIE5 = /msie 5 /.test(ua);
	        this.isIE55 = /msie 5.5/.test(ua);
	        this.isIE6 = /msie 6/.test(ua);
	        this.isIE7 = /msie 7/.test(ua);
	        this.isSAFARI = /safari/.test(ua);
	        this.iswinXP = /windows nt 5.1/.test(ua);
	        this.iswinVista = /windows nt 6.0/.test(ua);
	        this.isFF = /firefox/.test(ua);
	        this.isIOS = /\((iPhone|iPad|iPod)/i.test(ua);
	    };
	    $globalInfo.ua = new Detect();
		return it;
	})();
}else{
	SAB._register = SAB.register;
	SAB.register = function(m,n){}
}

SAB.register('dom.ready', function(){
	var  fns     = []
		,isReady = 0
		,inited  = 0
		,isReady = 0;

	var checkReady = function(){
		if(document.readyState === 'complete'){
			return 1;
		}
		return isReady;
	};

	var onReady = function(type){
		if(isReady){return}
		isReady = 1;

		if(fns){
			while (fns.length) {
				fns.shift()()
			}
		}
		fns = null
	};

	var bindReady = function(){
		if(inited){return}
		inited = 1;	

		//开始初始化domReady函数，判定页面的加载情况
		if (document.readyState === "complete") {
			onReady();
		} else if (document.addEventListener) {
			document.addEventListener("DOMContentLoaded", function() {
				document.removeEventListener("DOMContentLoaded", arguments.callee, false);
				onReady();
			}, false);
			//不加这个有时chrome firefox不起作用
			window.addEventListener( "load", function(){
				window.removeEventListener("load", arguments.callee, false);
				onReady();
			}, false );
		} else {
			document.attachEvent("onreadystatechange", function() {
				if (document.readyState == "complete") {
					document.detachEvent("onreadystatechange", arguments.callee);
					onReady();
				}
			});
			(function() {
				if (isReady) {
					return;
				}
				var node = new Image
				try {
					node.doScroll();
					node = null //防止IE内存泄漏
				} catch (e) {
					setTimeout(arguments.callee, 64);
					return;
				}
				onReady();
			})();
		}
	};

	return function(fn){
		bindReady();
		if(!checkReady()){
			fns.push(fn);
			return;
		}
		//onReady();
		fn.call();
	}
});
SAB.register('dom.hasClass', function($){
	return function(ele,cls){
	    return ele.className.match(new RegExp('(\\s|^)' + cls + '(\\s|$)'));
	}
});
SAB.register('dom.addClass', function($){
	return function(ele, cls){
	    if (!$.dom.hasClass(ele, cls)) {
	    		ele.className += " " + cls;
	    	}
	}
});
SAB.register('dom.removeClass', function($){
	return function(ele, cls){
	    if ($.dom.hasClass(ele, cls)) {
			var reg = new RegExp('(\\s|^)' + cls + '(\\s|$)');
			ele.className = ele.className.replace(reg, '');
		}
	}
});
SAB.register('dom.getScrollPos', function($){
	return function(doc){
	    doc = doc || document;
	    var dd = doc.documentElement;
	    var db = doc.body;
	    return [
	    		Math.max(dd.scrollTop, db.scrollTop), 
	    		Math.max(dd.scrollLeft, db.scrollLeft),
	    		Math.max(dd.scrollWidth, db.scrollWidth), 
	    		Math.max(dd.scrollHeight, db.scrollHeight)
	    		];
	}
});
SAB.register('dom.getStyle', function($){
	    var getStyle = function (el, property) {
	    	switch (property) {
	    		// 透明度
	    		case "opacity":
	    			var val = 100;
	    			try {
	    					val = el.filters['DXImageTransform.Microsoft.Alpha'].opacity;
	    			}
	    			catch(e) {
	    				try {
	    					val = el.filters('alpha').opacity;
	    				}catch(e){}
	    			}
	    			return val/100;
	    		 // 浮动
	    		 case "float":
	    			 property = "styleFloat";
	    		 default:
	    			 var value = el.currentStyle ? el.currentStyle[property] : null;
	    			 return ( el.style[property] || value );
	    	}
	    };
	    if(!$globalInfo.ua.isIE) {
			getStyle = function (el, property) {
				// 浮动
				if(property == "float") {
					property = "cssFloat";
				}
				// 获取集合
				try{
					var computed = document.defaultView.getComputedStyle(el, "");
				}
				catch(e) {
					traceError(e);
				}
				return el.style[property] || computed ? computed[property] : null;
			};
		}
	return getStyle;
});
SAB.register('dom.getXY', function($){
	var getStyle = $.dom.getStyle;
	var getScrollPos = $.dom.getScrollPos;
	var getXY = function (el) {
		if ((el.parentNode == null || el.offsetParent == null || getStyle(el, "display") == "none") && el != document.body) {
			return false;
		}
		var parentNode = null;
		var pos = [];
		var box;
		var doc = el.ownerDocument;
		// IE
		box = el.getBoundingClientRect();
		var scrollPos = getScrollPos(el.ownerDocument);
		return [box.left + scrollPos[1], box.top + scrollPos[0]];
		// IE end
		parentNode = el.parentNode;
		while (parentNode.tagName && !/^body|html$/i.test(parentNode.tagName)) {
			if (getStyle(parentNode, "display").search(/^inline|table-row.*$/i)) { 
				pos[0] -= parentNode.scrollLeft;
				pos[1] -= parentNode.scrollTop;
			}
			parentNode = parentNode.parentNode; 
		}
		return pos;
	};
	if(!$globalInfo.ua.isIE) {
		getXY = function (el) {
			if ((el.parentNode == null || el.offsetParent == null || getStyle(el, "display") == "none") && el != document.body) {
				return false;
			}
			var parentNode = null;
			var pos = [];
			var box;
			var doc = el.ownerDocument;

			var isSAFARI = $globalInfo.ua.isSAFARI;

			// FF
			pos = [el.offsetLeft, el.offsetTop];
			parentNode = el.offsetParent;
			var hasAbs = getStyle(el, "position") == "absolute";

			if (parentNode != el) {
				while (parentNode) {
						pos[0] += parentNode.offsetLeft;
						pos[1] += parentNode.offsetTop;
						if (isSAFARI && !hasAbs && getStyle(parentNode,"position") == "absolute" ) {
								hasAbs = true;
						}
						parentNode = parentNode.offsetParent;
				}
			}

			if (isSAFARI && hasAbs) {
				pos[0] -= el.ownerDocument.body.offsetLeft;
				pos[1] -= el.ownerDocument.body.offsetTop;
			}
			parentNode = el.parentNode;
			// FF End
			while (parentNode.tagName && !/^body|html$/i.test(parentNode.tagName)) {
				if (getStyle(parentNode, "display").search(/^inline|table-row.*$/i)) { 
					pos[0] -= parentNode.scrollLeft;
					pos[1] -= parentNode.scrollTop;
				}
				parentNode = parentNode.parentNode; 
			}
			return pos;
		};
	}
	return getXY;
});
SAB.register('dom.isNode', function($){
	return function(oNode){
	    return !!((oNode != undefined) && oNode.nodeName && oNode.nodeType);
	}
});
SAB.register('str.trim', function($){
	return function(str){
		//return str.replace(/(^\s*)|(\s*$)/g, ""); 
		//包括全角空格
		return str.replace(/(^[\s\u3000]*)|([\s\u3000]*$)/g, "");
	};
});
SAB.register('str.encodeDoubleByte', function($){
	return function (str) {
		if(typeof str != "string") {
			return str;
		}
		return encodeURIComponent(str);
	};
});
SAB.register('str.encodeHTML', function($){
	return function(str){
		var s = '';
		var div = document.createElement("div");
		div.appendChild(document.createTextNode(str));
		//(div.textContent != null) ? (div.textContent = str) : (div.innerText = str);
		s = div.innerHTML;
		div = null;
		s = s.replace( /(&lt;br\/&gt;){1,}/ig, "<br/>" );
		s = s.replace(/\s/g, "&nbsp;")
		return s;
	};
});
SAB.register('str.byteLength', function($){
	return function(str){
		if(typeof str == "undefined"){
			return 0;
		}
		var aMatch = str.match(/[^\x00-\x80]/g);
		return (str.length + (!aMatch ? 0 : aMatch.length));
	};
});
SAB.register('arr.indexOf', function($){
	return function(oElement, aArray){
		if (aArray.indexOf) {
			return aArray.indexOf(oElement);
		}
		var i = 0, len = aArray.length;
		while(i<len){
			if (aArray[i] === oElement) {
				return i;
			}
			i++
		}
		return -1;
	};
});
SAB.register('arr.inArray', function($){
	return function(oElement, aSource){
		return $.arr.indexOf(oElement, aSource) > -1;
	};
});

SAB.register('arr.foreach', function($){
	return function(aArray, insp){
		if (!$.arr.isArray(aArray)) {
			throw 'the foreach function needs an array as first parameter';
		}
		var i = 0, len = aArray.length, ret = [];
		while(i<len){
			var snap = insp(aArray[i], i);
			if(snap === false){break}
			if(snap !== null) {ret[i] = snap}
			i++
		}
		return ret;
	};
});
SAB.register('arr.isArray', function($){
	return function(o){
	  return Object.prototype.toString.call(o) === '[object Array]';
	};
});
SAB.register('json.jsonToQuery',function($){
	var _fdata   = function(data,isEncode){
		data = data == null? '': data;
		data = $.trim(data.toString());
		if(isEncode){
			return encodeURIComponent(data);
		}else{
			return data;
		}
	};
	return function(JSON,isEncode){
		var _Qstring = [];
		if(typeof JSON == "object"){
			for(var k in JSON){
				if(JSON[k] instanceof Array){
					for(var i = 0, len = JSON[k].length; i < len; i++){
						_Qstring.push(k + "=" + _fdata(JSON[k][i],isEncode));
					}
				}else{
					if(typeof JSON[k] != 'function'){
						_Qstring.push(k + "=" +_fdata(JSON[k],isEncode));
					}
				}
			}
		}
		if(_Qstring.length){
			return _Qstring.join("&");
		}else{
			return "";
		}
	};
});
SAB.register('json.queryToJson',function($){
	return function(QS, isDecode){
		var _Qlist = $.str.trim(QS).split("&");
		var _json  = {};
		var _fData = function(data){
			if(isDecode){
				return decodeURIComponent(data);
			}else{
				return data;
			}
		};
		for(var i = 0, len = _Qlist.length; i < len; i++){
			if(_Qlist[i]){
				_hsh = _Qlist[i].split("=");
				_key = _hsh[0];
				_value = _hsh[1];

				// 如果只有key没有value, 那么将全部丢入一个$nullName数组中
				if(_hsh.length < 2){
					_value = _key;
					_key = '$nullName';
				}
				// 如果缓存堆栈中没有这个数据
				if(!_json[_key]) {
					_json[_key] = _fData(_value);
				}
				// 如果堆栈中已经存在这个数据，则转换成数组存储
				else {
					if($.arr.isArray(_json[_key]) != true) {
						_json[_key] = [_json[_key]];
					}
					_json[_key].push(_fData(_value));
				}
			}
		}
		return _json;
	};
});
SAB.register('evt.addEvent',function($){
	return function(elm, evType,func, useCapture) {
		var _el = $.dom.byId(elm);
		if(_el == null){
			throw new Error("addEvent 找不到对象：" + elm);
			return;
		}
		if (typeof useCapture == 'undefined') {
			useCapture = false;
		}
		if (typeof evType == 'undefined') {
			evType = 'click';
		}
		if (_el.addEventListener) {
			_el.addEventListener(evType, func, useCapture);
			return true;
		}
		else if (_el.attachEvent) {
			var r = _el.attachEvent('on' + evType, func);
			return true;
		}
		else {
			_el['on' + evType] = func;
		}
	};
});
SAB.register('evt.removeEvent',function($){
	return function (oElement,sName, fHandler) {
		var _el = $.dom.byId(oElement);
		if(_el == null){
			throw ("removeEvent 找不到对象：" + oElement);
			return;
		}
		if (typeof fHandler != "function") {
			return;
		}
		if (typeof sName == 'undefined') {
			sName = 'click';
		}
		if (_el.addEventListener) {
			_el.removeEventListener(sName, fHandler, false);
		}
		else if (_el.attachEvent) {
			_el.detachEvent("on" + sName, fHandler);
		}
		fHandler[sName] = null;
	};
});
SAB.register('evt.fixEvent',function($){
	return fixEvent = function (e) {
		if(typeof e == 'undefined')e = window.event;
		if (!e.target) {
			e.target = e.srcElement;
			e.pageX = e.x;
			e.pageY = e.y;
		}
		if(typeof e.layerX == 'undefined')e.layerX = e.offsetX;
		if(typeof e.layerY == 'undefined')e.layerY = e.offsetY;
		return e;
	};
});
SAB.register('evt.preventDefault',function($){
	return function (e) {
		var e = e||window.event;
		if ($globalInfo.ua.isIE) {
		    e.returnValue = false;
		} else {
		    e.preventDefault();
		}
	};
});
//byid 
SAB.register('dom.byId',function($){
	return function(id){
        if (typeof id === 'string') {
            return document.getElementById(id);
        }
        else {
            return id;
        }
    };
});
//byclass
SAB.register('dom.byClass',function($){
	return function(clz,el,tg){
		el = el || document;
		el = typeof el=='string'?$.dom.byId(el):el;
		tg = tg || '*';
		var rs = [];
		clz = " " + clz +" ";
		var cldr = el.getElementsByTagName(tg), len = cldr.length;
		for (var i = 0; i < len; ++ i){
			var o = cldr[i];
			if (o.nodeType == 1){
				var ecl = " " + o.className + " ";
				if (ecl.indexOf(clz) != -1){
					rs[rs.length] = o;
				}
			}
		}
		return rs;
	};
});
//byattr 
SAB.register('dom.byAttr',function($){
	return function(node, attname, attvalue){
		var nodes = [];
		for(var i = 0, l = node.childNodes.length; i < l; i ++){
			if(node.childNodes[i].nodeType == 1){
				if(node.childNodes[i].getAttribute(attname) == attvalue){
					nodes.push(node.childNodes[i]);
				}
				if(node.childNodes[i].childNodes.length > 0){
					nodes = nodes.concat(arguments.callee.call(null, node.childNodes[i], attname, attvalue));
				}
			}
		}
		return nodes;
	};
});
SAB.register('dom.contains', function($){
	return function(root, el) {
        if (root.compareDocumentPosition)
             return root === el || !!(root.compareDocumentPosition(el) & 16);
         if (root.contains && el.nodeType === 1){
             return root.contains(el) && root !== el;
         }
         while ((el = el.parentNode)){
             if (el === root){
             	return true;
             }
         }
         return false;
    };
});
// 自定义事件
SAB.register("evt.custEvent", function($) {

	var _custAttr = "__custEventKey__",
		_custKey = 1,
		_custCache = {},
		/**
		 * 从缓存中查找相关对象 
		 * 当已经定义时 
		 * 	有type时返回缓存中的列表 没有时返回缓存中的对象
		 * 没有定义时返回false
		 * @param {Object|number} obj 对象引用或获取的key
		 * @param {String} type 自定义事件名称
		 */
		_findObj = function(obj, type) {
			var _key = (typeof obj == "number") ? obj : obj[_custAttr];
			return (_key && _custCache[_key]) && {
				obj: (typeof type == "string" ? _custCache[_key][type] : _custCache[_key]),
				key: _key
			};
		};

	return {
		/**
		 * 对象自定义事件的定义 未定义的事件不得绑定
		 * @method define
		 * @static
		 * @param {Object|number} obj 对象引用或获取的下标(key); 必选 
		 * @param {String|Array} type 自定义事件名称; 必选
		 * @return {number} key 下标
		 */
		define: function(obj, type) {
			if(obj && type) {
				var _key = (typeof obj == "number") ? obj : obj[_custAttr] || (obj[_custAttr] = _custKey++),
					_cache = _custCache[_key] || (_custCache[_key] = {});
				type = [].concat(type);
				for(var i = 0; i < type.length; i++) {
					_cache[type[i]] || (_cache[type[i]] = []);
				}
				return _key;
			}
		},

		/**
		 * 对象自定义事件的取消定义 
		 * 当对象的所有事件定义都被取消时 删除对对象的引用
		 * @method define
		 * @static
		 * @param {Object|number} obj 对象引用或获取的(key); 必选
		 * @param {String} type 自定义事件名称; 可选 不填可取消所有事件的定义
		 */
		undefine: function(obj, type) {
			if (obj) {
				var _key = (typeof obj == "number") ? obj : obj[_custAttr];
				if (_key && _custCache[_key]) {
					if (typeof type == "string") {
						if (type in _custCache[_key]) delete _custCache[_key][type];
					} else {
						delete _custCache[_key];
					}
				}
			}
		},

		/**
		 * 事件添加或绑定
		 * @method add
		 * @static
		 * @param {Object|number} obj 对象引用或获取的(key); 必选
		 * @param {String} type 自定义事件名称; 必选
		 * @param {Function} fn 事件处理方法; 必选
		 * @param {Any} data 扩展数据任意类型; 可选
		 * @return {number} key 下标
		 */
		add: function(obj, type, fn, data) {
			if(obj && typeof type == "string" && fn) {
				var _cache = _findObj(obj, type);
				if(!_cache || !_cache.obj) {
					throw "custEvent (" + type + ") is undefined !";
				}
				_cache.obj.push({fn: fn, data: data});
				return _cache.key;
			}
		},

		/**
		 * 事件删除或解绑
		 * @method remove
		 * @static
		 * @param {Object|number} obj 对象引用或获取的(key); 必选
		 * @param {String} type 自定义事件名称; 可选; 为空时删除对象下的所有事件绑定
		 * @param {Function} fn 事件处理方法; 可选; 为空且type不为空时 删除对象下type事件相关的所有处理方法
		 * @return {number} key 下标
		 */
		remove: function(obj, type, fn) {
			if (obj) {
				var _cache = _findObj(obj, type), _obj;
				if (_cache && (_obj = _cache.obj)) {
					if ($.arr.isArray(_obj)) {
						if (fn) {
							for (var i = 0; i < _obj.length && _obj[i].fn !== fn; i++);
							_obj.splice(i, 1);
						} else {
							_obj.splice(0);
						}
					} else {
						for (var i in _obj) {
							_obj[i] = [];
						}
					}
					return _cache.key;
				}
			}
		},

		/**
		 * 事件触发
		 * @method fire
		 * @static
		 * @param {Object|number} obj 对象引用或获取的(key); 必选
		 * @param {String} type 自定义事件名称; 必选
		 * @param {Any|Array} args 参数数组或单个的其他数据; 可选
		 * @return {number} key 下标
		 */
		fire: function(obj, type, args) {
			if(obj && typeof type == "string") {
				var _cache = _findObj(obj, type), _obj;
				if (_cache && (_obj = _cache.obj)) {
					if(!$.arr.isArray(args)) {
						args = args != undefined ? [args] : [];
					}
					for(var i = 0; i < _obj.length; i++) {
						var fn = _obj[i].fn;
						if(fn && fn.apply) {
							fn.apply($, [{type: type, data: _obj[i].data}].concat(args));
						}
					}
					return _cache.key;
				}
			}
		},
		/**
		 * 销毁
		 * @method destroy
		 * @static
		 */
		destroy: function() {
			_custCache = {};
			_custKey = 1;
		}
	};
});
// 事件委派
SAB.register('evt.delegatedEvent',function($){

	var checkContains = function(list,el){
		for(var i = 0, len = list.length; i < len; i += 1){
			if($.dom.contains(list[i],el)){
				return true;
			}
		}
		return false;
	};

	return function(actEl,expEls){
		if(!$.dom.isNode(actEl)){
			throw 'SAB.evt.delegatedEvent need an Element as first Parameter';
		}
		if(!expEls){
			expEls = [];
		}
		if($.arr.isArray(expEls)){
			expEls = [expEls];
		}
		var evtList = {};
		var bindEvent = function(e){
			var evt = $.evt.fixEvent(e);
			var el = evt.target;
			var type = e.type;
			if(checkContains(expEls,el)){
				return false;
			}else if(!$.dom.contains(actEl, el)){
				return false;
			}else{
				var actionType = null;
				var checkBuble = function(){
					if(evtList[type] && evtList[type][actionType]){
						return evtList[type][actionType]({
							'evt' : evt,
							'el' : el,
							'e' :e,
							'data' : $.json.queryToJson(el.getAttribute('action-data') || '')
						});
					}else{
						return true;
					}
				};
				while(el && el !== actEl){
					if(!el.getAttribute){
						break;
					}
					actionType = el.getAttribute('action-type');
					if(checkBuble() === false){
						break;
					}
					el = el.parentNode;
				}

			}
		};
		var that = {};
		/**
		 * 添加代理事件
		 * @method add
		 * @param {String} funcName
		 * @param {String} evtType
		 * @param {Function} process
		 * @return {void}
		 * @example
		 * 		document.body.innerHTML = '<div id="outer"><a href="###" action_type="alert" action_data="test=123">test</a><div id="inner"></div></div>'
		 * 		var a = STK.core.evt.delegatedEvent($.E('outer'),$.E('inner'));
		 * 		a.add('alert','click',function(spec){window.alert(spec.data.test)});
		 *
		 */
		that.add = function(funcName, evtType, process){
			if(!evtList[evtType]){
				evtList[evtType] = {};
				$.evt.addEvent(actEl,evtType, bindEvent );
			}
			var ns = evtList[evtType];
			ns[funcName] = process;
		};
		/**
		 * 移出代理事件
		 * @method remove
		 * @param {String} funcName
		 * @param {String} evtType
		 * @return {void}
		 * @example
		 * 		document.body.innerHTML = '<div id="outer"><a href="###" action_type="alert" action_data="test=123">test</a><div id="inner"></div></div>'
		 * 		var a = STK.core.evt.delegatedEvent($.E('outer'),$.E('inner'));
		 * 		a.add('alert','click',function(spec){window.alert(spec.data.test)});
		 * 		a.remove('alert','click');
		 */
		that.remove = function(funcName, evtType){
			if(evtList[evtType]){
				delete evtList[evtType][funcName];
				if($.objIsEmpty(evtList[evtType])){
					delete evtList[evtType];
					$.evt.removeEvent(actEl, bindEvent, evtType);
				}
			}
		};

		that.pushExcept = function(el){
			expEls.push(el);
		};

		that.removeExcept = function(el){
			if(!el){
				expEls = [];
			}else{
				for(var i = 0, len = expEls.length; i < len; i += 1){
					if(expEls[i] === el){
						expEls.splice(i,1);
					}
				}
			}

		};

		that.clearExcept = function(el){
			expEls = [];
		};

		that.destroy = function(){
			for(k in evtList){
				for(l in evtList[k]){
					delete evtList[k][l];
				}
				delete evtList[k];
				$.evt.removeEvent(actEl, bindEvent, k);
			}
		};
		return that;
	};

});
//SAB.register('fun.bind2',function($){
SAB.register('fun.bind2',function($){
	/**
	 * 保留原型扩展
	 * stan | chaoliang@staff.sina.com.cn
	 * @param {Object} object
	 */
	Function.prototype.bind2 = function(object) { 
		var __method = this; 
		return function() { 
		   return __method.apply(object, arguments); 
		};
	};
	return function(fFunc, object) { 
		var __method = fFunc; 
		return function() { 
			return __method.apply(object, arguments); 
		};
	};

});
SAB.register('io.jsonp',function($){
	/**
	 * jsonp
	 * @param  {String}   url      url
	 * @param  {String}   params   params
	 * @param  {Function||String} callback 回调函数，当fix为true时，要求为函数名，即字符串
	 * @param  {Boolean}   fix      是否要回调固定函数，默认为为false，在dpc=1时为true
	 */
	return function(url, params, callback,fix) {
		var byId = $.dom.byId;
		var idStr = url+'&'+params;
		var fun = '';
		if (byId(url)) {
			document.body.removeChild(byId(url));
		}
		fix = fix||false;
		if(!fix){
			//添加时间戳
			url = url + ((url.indexOf('?') == -1) ? '?' : '&') + '_t=' + Math.random();
			//添加回调
			if (typeof callback == 'function') {
				fun = 'fun_' + new Date().getUTCMilliseconds() + ('' + Math.random()).substring(3);
				eval(fun + '=function(res){callback(res)}');
			}
		}else{
			if(typeof callback == 'string'){
				fun = callback;
			}
		}
		url = url + '&callback=' + fun;
		//添加参数,放在最后，dpc=1一般放在最后
		url = url+'&'+params;
		var head_dom = document.getElementsByTagName('head')[0];
		var old_script = byId(idStr);
		if(old_script){
			head_dom.removeChild(old_script);
		}
		var script_dom = $.C('script');
		script_dom.src  =   url;
		script_dom.id   =   idStr;
		script_dom.type =  'text/javascript';
		script_dom.language = 'javascript';
		head_dom.appendChild(script_dom);

	};
});
SAB.register('io.ajax',function($){
	//TODO
		/**
		 * 创建 XMLHttpRequest 对象
		 */
	return {
		createRequest:function() {
			var request = null;
			try {
				request = new XMLHttpRequest();
			} catch (trymicrosoft) {
				try {
					request = new ActiveXObject("Msxml2.XMLHTTP");
				} catch (othermicrosoft) {
					try {
						request = ActiveXObject("Microsoft.XMLHTTP");
					} catch (failed) {}
				}
			}
			if(request == null){
				throw ("<b>create request failed</b>", {'html':true});
			}
			else {
				return request;
			}
		},
		/**
		 * 请求参数接收
		 * 
		 * @param url 必选参数。请求数据的URL，是一个 URL 字符串，不支持数组
		 * @param option 可选参数 {
		 *  onComplete  : Function (Array responsedData),
		 *  onException : Function (),
		 *  returnType : "txt"/"xml"/"json", 返回数据类型
		 *  GET : {}, 通过 GET 提交的数据
		 *  POST : {} 通过 POST 提交的数据
		 * }
		 */
		request : function (url, option) {
			option = option || {};
			option.onComplete = option.onComplete || function () {};
			option.onException = option.onException || function () {};
			option.onTimeout = option.onTimeout || function () {};
			option.timeout = option.timeout? option.timeout: -1;
			option.returnType = option.returnType || "txt";
			option.method = option.method || "get";
			option.data = option.data || {};
			if(typeof option.GET != "undefined" && typeof option.GET.url_random != "undefined" && option.GET.url_random == 0){
				this.rand = false;
				option.GET.url_random = null;
			}
			this.loadData(url, option);
		},
		/**
		 * 载入指定数据
		 * @param {Object} url
		 * @param {Object} option
		 */
		loadData: function (url, option) {
			var request = this.createRequest(), tmpArr = [];
			var _url = new $.util.url(url);

			var timer;
			// 如果有需要 POST 的数据，加以整理
			if(option.POST){
				for (var postkey in option.POST) {
					var postvalue = option.POST[postkey];
					if(postvalue != null){
						tmpArr.push(postkey + '=' + $.str.encodeDoubleByte(postvalue));
					}
				}
			}
			var sParameter = tmpArr.join("&") || "";
			// GET 方式提交的数据都放入地址中
			if (option.GET) {
				for(var key in option.GET){
					if (key != "url_random") {
						_url.setParam(key, $.str.encodeDoubleByte(option.GET[key]));
					}
				}					
			}
			if (this.rand != false) {
				// 接口增加随机数
				_url.setParam("rnd", Math.random());
			}

			if (option.timeout > -1) {
				timer = setTimeout(option.onTimeout, option.timeout);
			}

			// 处理回调
			request.onreadystatechange = function() {
				if(request.readyState == 4){
					var response, type = option.returnType;
					try{
						// 根据类型返回不同的响应
						switch (type){
							case "txt":
								response = request.responseText;
								break;
							case "xml":
								if (Core.Base.detect.$IE) {
									response = request.responseXML;
								}
								else {
									var Dparser = new DOMParser();
									response = Dparser.parseFromString(request.responseText, "text/xml");
								}
								break;
							case "json":
									response = eval("(" + request.responseText + ")");
								break;
						}
						option.onComplete(response);
						clearTimeout(timer);
					}
					catch(e){
						option.onException(e.message, _url);
						return false;
					}
				}
			};
			try{
				// 发送请求
				if(option.POST){
					request.open("POST", _url, true);
					request.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
					request.send(sParameter);
				}
				else {
					request.open("GET", _url, true);
					request.send(null);
				}
			}
			catch(e){
				option.onException(e.message, _url);
				return false;
			}
		}
	};

});
SAB.register('io.ijax',function($){
	return {
			/**
			 * 保存缓冲的任务列表
			 */
			arrTaskLists : [],
			/**
			 * 创建 iframe 节点用于载入数据，因为支持双线程，同时建立两个，减少 DOM 操作次数
			 */
			createLoadingIframe: function () {
				if(this.loadFrames != null){
					return false;
				}
				/**
				 * 生成随机 ID 来保证提交到当前页面的数据交互 iframe
				 * L.Ming | liming1@staff.sina.com.cn 2009-01-11
				 */
				var rndId1 = "loadingIframe_thread" + Math.ceil(Math.random() * 10000);
				var rndId2 = "loadingIframe_thread" + Math.ceil((Math.random() + 1) * 10000);
				this.loadFrames = [rndId1, rndId2];

				var iframeSrc = '';
				if($globalInfo.ua.isIE6){
					// ie6 父页面或在iframe页面中设置document.domain后，无论是和当前域名相同还是根域名，一律视为跨域
					iframeSrc = "javascript:void((function(){document.open();document.domain='sina.com.cn';document.close()})())";
				}
			    var html = '<iframe id="' + rndId1 +'" name="' + rndId1 +'" class="invisible"\
			              scrolling="no" src=""\
			              allowTransparency="true" style="display:none;" frameborder="0"\
			              ><\/iframe>\
						  <iframe id="' + rndId2 +'" name="' + rndId2 +'" class="invisible"\
			              scrolling="no" src="'+iframeSrc+'"\
			              allowTransparency="true" style="display:none;" frameborder="0"\
			              ><\/iframe>';
			    //Sina.dom.addHTML(document.body, html); 临时替换
				var oIjaxIframeCnt = $.C("div");
				oIjaxIframeCnt.id = "ijax_iframes";

				oIjaxIframeCnt.innerHTML = html;
				//$Debug("创建 Ijax 需要的 iframe");
				document.body.appendChild(oIjaxIframeCnt);
				// 记录两个 iframe 加载器，默认是空闲状态

				var loadTimer = setInterval($.fun.bind2(function(){
					if($.E(this.loadFrames[0]) != null && $.E(this.loadFrames[1]) != null){
						clearInterval(loadTimer);
						loadTimer = null;
						this.loadingIframe = {
							"thread1" : {
								"container" : $.E(this.loadFrames[0]),
								"isBusy" : false
							},
							"thread2" : {
								"container" : $.E(this.loadFrames[1]),
								"isBusy" : false
							}
						};		
						this.loadByList();
					}
				}, this), 10);
			},
			/**
			 * 判断是否可以开始加载数据，必须是两个 iframe 节点可用的情况下
			 */
			isIjaxReady: function () {
				if(typeof this.loadingIframe == "undefined"){
					return false;
				}
				for(var oLoadCnt in this.loadingIframe){
					if(this.loadingIframe[oLoadCnt].isBusy == false){
						this.loadingIframe[oLoadCnt].isBusy = true;
						return this.loadingIframe[oLoadCnt];
					}
				}
				return false;			
			},
			/**
			 * 处理请求参数接收
			 * 
			 * @param url 必选参数。请求数据的URL，是一个 URL 字符串，不支持数组
			 * @param option 可选参数 {
			 *  onComplete  : Function (Array responsedData),
			 *  onException : Function ();
			 *  GET : {}, 通过 GET 提交的数据
			 *  POST : {} 通过 POST 提交的数据
			 * }
			 */
			request: function (url, option) {
				var oTask = {};
				oTask.url = url;
				oTask.option = option || {};
				this.arrTaskLists.push(oTask);
				if(this.loadFrames == null){
					this.createLoadingIframe();
				}
				else{
					this.loadByList();
				}		
			},
			/**
			 * 缓冲列表管理
			 */
			loadByList: function () {
				// 如果等待列表为空，则终止加载
				if (this.arrTaskLists.length == 0) {
					// 重新建立 iframe
					return false;
				}
				// 取得两个加载器的状态，看是否有空闲的
				var loadStatus = this.isIjaxReady();
				if(loadStatus == false){
					return false;
				}
				var newData = this.arrTaskLists[0];
				this.loadData(newData.url, newData.option, loadStatus);
				// 删除列表第一条
				this.arrTaskLists.shift();			
			},
			/**
			 * 加载单条数据
			 */
			loadData: function (url, option, loader) {
				var _url = new $.util.url(url);
				if (option.GET) {
					for(var key in option.GET){
						_url.setParam(key, Core.String.encodeDoubleByte(option.GET[key]));
					}					
				}		
				// 接口设置 Domain
				//_url.setParam("domain", "1");
				// 接口增加随机数
				//modified by stan | chaoliang@staff.sina.com.cn
				//减少不必要的强制更新数据
				//_url.setParam("rnd", Math.random());
				_url = _url.toString();
				// 当前用于加载数据的 iframe 对象
				var ifm = loader.container;
				ifm.listener = $.fun.bind2(function () {
					if(option.onComplete||option.onException){
						try{
							var iframeObject = ifm.contentWindow.document, sResult;
							// 临时函数
							var tArea = iframeObject.getElementsByTagName( 'textarea')[0];
							if (typeof tArea != "undefined") {
								sResult = tArea.value;
							}
							else {
								sResult = iframeObject.body.innerHTML;
							}
							if(option.onComplete){
								option.onComplete(sResult);
							}
							else{
								option.onException();
							}
						}
						catch(e){
							if(option.onException){
								option.onException(e.message, _url.toString());
							}
						}
					}
					loader.isBusy = false;
					$.evt.removeEvent(ifm,"load",ifm.listener);
					this.loadByList();
				},this);

				$.evt.addEvent(ifm,"load", ifm.listener);

				// 如果需要 post 数据
				if(option.POST){
					var oIjaxForm = $.C("form");
					oIjaxForm.id = "IjaxForm";
					oIjaxForm.action = _url;
					oIjaxForm.method = "post";
					oIjaxForm.target = ifm.id; 
					for(var oItem in option.POST) {
						var oInput = $.C("input");
						oInput.type = "hidden";
						oInput.name = oItem;
						//oInput.value = $.str.encodeDoubleByte(option.POST[oItem]);
						//encodeDoubleByte就是encodeURIComponent，会把gbk字符转成utf-8造成乱码
						oInput.value = option.POST[oItem];
						oIjaxForm.appendChild(oInput);
					};
					document.body.appendChild(oIjaxForm);
					try{
						oIjaxForm.submit();
					}catch(e){

					}
				}
				else{
					try{
						window.frames(ifm.id).location.href = _url;
					}catch(e){
						ifm.src = _url;
					};			
				}
			}
	};
});
SAB.register('io.jsload',function($){
	JsLoad = {};
	(function () {
		function createScripts (oOpts, oCFG) {

			processUrl(oOpts, oCFG);

			var urls = oOpts.urls;
			var i, len = urls.length;
			for(i = 0; i < len; i ++ ) {
				var js = $.C("script");
				js.src = urls[i].url;
				//js.charset = urls[i].charset;
				/*js[$globalInfo.ua.isIE ? "onreadystatechange" : "onload"] = function(){
					if ($globalInfo.ua.isMOZ || this.readyState.toLowerCase() == 'complete' || this.readyState.toLowerCase() == 'loaded') {*/
				js[document.all ? "onreadystatechange" : "onload"] = function() {
					if (/gecko/.test(navigator.userAgent.toLowerCase()) || this.readyState.toLowerCase() == "complete" || this.readyState.toLowerCase() == "loaded") {
						oCFG.script_loaded_num ++;
					}
				};
				document.getElementsByTagName("head")[0].appendChild(js);
			}
		}

		function processUrl(oOpts, oCFG) {
			var urls = oOpts.urls;
			var get_hash = oOpts.GET;

			var i, len = urls.length;
			var key, url_cls, jsvar, rnd;
			for (i = 0; i < len; i++) {
				rnd =  parseInt(Math.random() * 100000000);
				url_cls = new $.util.url(urls[i].url);
				for(key in get_hash) {
					if(oOpts.noencode == true) {
						url_cls.setParam(key, get_hash[key]);
					}
					else {
						url_cls.setParam(key, $.str.encodeDoubleByte(get_hash[key]));
					}
				}

				jsvar = url_cls.getParam("jsvar") || "requestId_" + rnd;

				if (oOpts.noreturn != true) {
					url_cls.setParam("jsvar", jsvar);
				}

				oCFG.script_var_arr.push(jsvar);
				urls[i].url = url_cls.toString();
				urls[i].charset = urls[i].charset || oOpts.charset; 
			}
		}

		function ancestor (aUrls, oOpts) {
			var _opts = {
				urls: [],
				charset: "utf-8",
				noreturn: false,
				noencode: true,
				timeout: -1,
				POST: {},
				GET: {},
				onComplete: null,
				onException: null
			};

			var _cfg = {
				script_loaded_num: 0,
				is_timeout: false,
				is_loadcomplete: false,
				script_var_arr: []
			};

			_opts.urls = typeof aUrls == "string"? [{url: aUrls}]: aUrls;

			$.util.parseParam(_opts, oOpts);
			createScripts(_opts, _cfg);

			// 定时检查完成情况
			(function () {

				if(_opts.noreturn == true && _opts.onComplete == null)return;
				var i, data = [];
				// 全部完成
				if (_cfg.script_loaded_num == _opts.urls.length) {
					_cfg.is_loadcomplete = true;
					if (_opts.onComplete != null) {
						for(i = 0; i < _cfg.script_var_arr.length; i ++ ) {
							data.push(window[_cfg.script_var_arr[i]]);
						}
						if(_cfg.script_var_arr.length < 2) {
							_opts.onComplete(data[0]);
						}
						else {
							_opts.onComplete(data);
						}
					}
					return;
				}
				// 达到超时
				if(_cfg.is_timeout == true) {
					return;
				}
				setTimeout(arguments.callee, 50);
			})();

			// 超时处理
			if(_opts.timeout > 0) {
				setTimeout(function () {
					if (_cfg.is_loadcomplete != true) {
						if (_opts.onException != null) {
							_opts.onException();
						}
						_cfg.is_timeout = true;
					}
				}, _opts.timeout);
			}
		}

		JsLoad.request = function (aUrls, oOpts) {
			new ancestor(aUrls, oOpts);
		};

	})();
	return JsLoad;
});
/**
 * Cross-domain POST using window.postMessage()
 */
SAB.register("io.html5Ijax", function($) {
    var _add = $.evt.addEvent,
        _remove = $.evt.removeEvent,

        NOOP = function() {},
        RE_URL = /^http\s?\:\/\/[a-z\d\-\.]+/i,
        ID_PREFIX = 'ijax-html5-iframe-',

        /**
         * Message sender class
         */
        MsgSender = function(cfg) {
            cfg = cfg || {};
            this.init(cfg);
        };
        MsgSender.prototype = {
        	ready: false,

        	init: function(cfg) {
        	    if (this.ready) {
        	        return;
        	    }
        	    var self = this,
        	        iframeId, iframeHtml, iframe, loaded, receiver,
        	        proxyUrl = cfg.proxyUrl,
        	        datas = {};
        	    self.onsuccess = cfg.onsuccess || NOOP;
        	    self.onfailure = cfg.onfailure || NOOP;
        	    if (!proxyUrl) {
        	        return;
        	    }

        	    receiver = function(e) {
        	        if (!self.ready || e.origin !== self.target) {
        	        	self.destroy();
        	            return;
        	        }
        	        var ret = e.data;
        	        if (!ret || ret === 'failure') {
        	        	self.destroy();
        	            self.onfailure && self.onfailure();
        	        } else {
        	            self.onsuccess && self.onsuccess(e.data); 
        	            self.destroy()
        	        }
        	    };
        	    _add(window, 'message', receiver);

        	    // insert an iframe
        	    iframeId = ID_PREFIX+Date.parse(new Date());
        	    iframeHtml = '<iframe id="' + iframeId + '" name="' + iframeId + 
        	        '" src="' + proxyUrl + '" frameborder="0" ' +
        	        'style="width:0;height:0;display:none;"></iframe>';
        	    var oIjaxIframeCnt = $.C("div");
        	    oIjaxIframeCnt.id = ID_PREFIX+"iframes";
        	    oIjaxIframeCnt.innerHTML = iframeHtml;
        	    // document.body.appendChild(oIjaxIframeCnt);
        	    iframe = oIjaxIframeCnt.childNodes[0];
        	    loaded = function() {
        	        self.ready = true;
        	        var src = iframe.src,
        	            matched = src.match(RE_URL);
        	        self.target = (matched && matched[0]) || '*';
        	    };
        	    _add(iframe, 'load', loaded);
        	    document.body.insertBefore(iframe, document.body.firstChild);

        	    self._iframe = iframe;
        	    self._iframeLoaded = loaded;
        	    self._receiver = receiver;
        	},

        	send: function(cfg) {
        	    cfg = cfg || {};
        	    var self = this,
        	        url = cfg.url,
        	        data = cfg.data,
        	        onsuccess = cfg.onsuccess,
        	        onfailure = cfg.onfailure;

        	    if (!url || typeof url !== 'string') {
        	        return;
        	    }
        	    if (onsuccess) {
        	        self.onsuccess = onsuccess;
        	    }
        	    if (onfailure) {
        	        self.onfailure = onfailure;
        	    }

        	    if (!self.ready) {
        	        setTimeout(function() {
        	            self.send(cfg);
        	        }, 50);
        	        return;
        	    }

        	    if (data) {
        	        data += '&_url=' + window.encodeURIComponent(url);
        	    } else {
        	        data = '_url=' + window.encodeURIComponent(url);
        	    }
        	    self._iframe.contentWindow.postMessage(data, self.target);
        	},

        	destroy: function() {
        	    var iframe = this._iframe;
        	    _remove(iframe, 'load', this._iframeLoaded);
        	    iframe.parentNode.removeChild(iframe);
        	    _remove(window, 'message', this._receiver);
        	    this._iframe = null;
        	    this._iframeLoaded = null;
        	    this._receiver = null;
        	}
        };

    return MsgSender;
});
SAB.register('clz.extend',function($){
	return  function(target,source,deep) {
		for (var property in source) {
			target[property] = source[property];
		}
		return target;
	// 	target = target || {};
	// 	var sType = typeof source, i = 1, options;
	// 	if(sType === 'undefined' || sType === 'boolean') {
	// 		deep = sType === 'boolean' ? source : false;
	// 		source = target;
	// 		target = this;
	// 	}
	// 	if( typeof source !== 'object' && Object.prototype.toString.call(source) !== '[object Function]') {
	// 		source = {};
	// 	}
	// 	while(i <= 2) {
	// 		options = i === 1 ? target : source;
	// 		if(options !== null) {
	// 			for(var name in options ) {
	// 				var src = target[name], copy = options[name];
	// 				if(target === copy){
	// 					continue;
	// 				}
	// 				if(deep && copy && typeof copy === 'object' && !copy.nodeType){
	// 					target[name] = this.extend(src || (copy.length !== null ? [] : {}), copy, deep);
	// 				}else if(copy !== undefined){
	// 					target[name] = copy;
	// 				}
	// 			}
	// 		}
	// 		i++;
	// 	}
	// 	return target;
	}
});
SAB.register('clz.objExtend',function($){
	return function(subClass,superClass){
	    var F = function(){};
	    F.prototype = superClass.prototype;
	    subClass.prototype = new F();
	    subClass.prototype.constructor = subClass;
	    subClass.superclass = superClass.prototype; //加多了个属性指向父类本身以便调用父类函数
	    if(superClass.prototype.constructor == Object.prototype.constructor){
	        superClass.prototype.constructor = superClass;
	    }
	}
});
SAB.register('util.cookie',function($){
	/**
	 * 读取cookie,注意cookie名字中不得带奇怪的字符，在正则表达式的所有元字符中，目前 .[]$ 是安全的。
	 * @param {Object} cookie的名字
	 * @return {String} cookie的值
	 * @example
	 * var value = co.getCookie(name);
	 */
	var co={};
	co.getCookie = function (name) {
		name = name.replace(/([\.\[\]\$])/g,'\\\$1');
		var rep = new RegExp(name + '=([^;]*)?;','i'); 
		var co = document.cookie + ';';
		var res = co.match(rep);
		if (res) {
			return res[1] || "";
		}
		else {
			return "";
		}
	};

	/**
	 * 设置cookie
	 * @param {String} name cookie名
	 * @param {String} value cookie值
	 * @param {Number} expire Cookie有效期，单位：小时
	 * @param {String} path 路径
	 * @param {String} domain 域
	 * @param {Boolean} secure 安全cookie
	 * @example
	 * co.setCookie('name','sina',null,"")
	 */
	co.setCookie = function (name, value, expire, path, domain, secure) {
			var cstr = [];
			cstr.push(name + '=' + escape(value));
			if(expire){
				var dd = new Date();
				var expires = dd.getTime() + expire * 3600000;
				dd.setTime(expires);
				cstr.push('expires=' + dd.toGMTString());
			}
			if (path) {
				cstr.push('path=' + path);
			}
			if (domain) {
				cstr.push('domain=' + domain);
			}
			if (secure) {
				cstr.push(secure);
			}
			document.cookie = cstr.join(';');
	};

	/**
	 * 删除cookie
	 * @param {String} name cookie名
	 */
	co.deleteCookie = function(name) {
			document.cookie = name + '=;' + 'expires=Fri, 31 Dec 1999 23:59:59 GMT;'; 
	};
	return co;
});
SAB.register('util.parseParam',function($){
	return function (oSource, oParams) {
		var key;
		try {
			if (typeof oParams != "undefined") {
				for (key in oSource) {
					if (oParams[key] != null) {
						oSource[key] = oParams[key];
					}
				}
			}
		}
		finally {
			key = null;
			return oSource;
		}
	};
});
SAB.register('util.byteLength',function($){
	 return function(str){
		if(typeof str == "undefined"){
			return 0;
		}
		var aMatch = str.match(/[^\x00-\x80]/g);
		return (str.length + (!aMatch ? 0 : aMatch.length));
	};
});
SAB.register('util.url',function($){
	Url = function (url){
	    url = url || "";
	    this.url = url;
		this.query = {};
		this.parse();
	};

	Url.prototype = {
		/**
		 * 解析URL，注意解析锚点必须在解析GET参数之前，以免锚点影响GET参数的解析
		 * @param{String} url? 如果传入参数，则将会覆盖初始化时的传入的url 串
		 */
		parse : function (url){
			if (url) {
				this.url = url;
			}
		    this.parseAnchor();
		    this.parseParam();
		},
		/**
		 * 解析锚点 #anchor
		 */
		parseAnchor : function (){
		    var anchor = this.url.match(/\#(.*)/);
		    anchor = anchor ? anchor[1] : null;
		    this._anchor = anchor;
		    if (anchor != null){
		      this.anchor = this.getNameValuePair(anchor);
		      this.url = this.url.replace(/\#.*/,"");
		    }
		},

		/**
		 * 解析GET参数 ?name=value;
		 */
		parseParam : function (){
		    var query = this.url.match(/\?([^\?]*)/);
		    query = query ? query[1] : null;
		    if (query != null){
		      this.url = this.url.replace(/\?([^\?]*)/,"");
		      this.query = this.getNameValuePair(query);
		    }
		 },
		/**
		 * 目前对json格式的value 不支持
		 * @param {String} str 为值对形式,其中value支持 '1,2,3'逗号分割
		 * @return 返回str的分析结果对象
		 */
		getNameValuePair : function (str){
		    var o = {};
		    str.replace(/([^&=]*)(?:\=([^&]*))?/gim, function (w, n, v) {
		     	if(n == ""){return;}
		      	//v = v || "";//alert(v)
		     	//o[n] = ((/[a-z\d]+(,[a-z\d]+)*/.test(v)) || (/^[\u00ff-\ufffe,]+$/.test(v)) || v=="") ? v : (v.j2o() ? v.j2o() : v);
		    	o[n] = v || "";
			});
		    return o;
		 },
		 /**
		  * 从 URL 中获取指定参数的值
		  * @param {Object} sPara
		  */
		 getParam : function (sPara) {
		 	return this.query[sPara] || "";
		 },
		/**
		 * 清除URL实例的GET请求参数
		 */
		clearParam : function (){
		    this.query = {};
		},

		/**
		 * 设置GET请求的参数，当个设置
		 * @param {String} name 参数名
		 * @param {String} value 参数值
		 */
		setParam : function (name, value) {
		    if (name == null || name == "" || typeof(name) != "string") {
				throw new Error("no param name set");
			}
		    this.query = this.query || {};
		    this.query[name]=value;
		},

		/**
		 * 设置多个参数，注意这个设置是覆盖式的，将清空设置之前的所有参数。设置之后，URL.query将指向o，而不是duplicate了o对象
		 * @param {Object} o 参数对象，其属性都将成为URL实例的GET参数
		 */
		setParams : function (o){
		    this.query = o;
		},

		/**
		 * 序列化一个对象为值对的形式
		 * @param {Object} o 待序列化的对象，注意，只支持一级深度，多维的对象请绕过，重新实现
		 * @return {String} 序列化之后的标准的值对形式的String
		 */
		serialize : function (o){
			var ar = [];
			for (var i in o){
			    if (o[i] == null || o[i] == "") {
					ar.push(i + "=");
				}else{
					ar.push(i + "=" + o[i]);
				}
			}
			return ar.join("&");
		},
		/**
		 * 将URL对象转化成为标准的URL地址
		 * @return {String} URL地址
		 */
		toString : function (){
		    var queryStr = this.serialize(this.query);
		    return this.url + (queryStr.length > 0 ? "?" + queryStr : "") 
		                    + (this.anchor ? "#" + this.serialize(this.anchor) : "");
		},

		/**
		 * 得到anchor的串
		 * @param {Boolean} forceSharp 强制带#符号
		 * @return {String} 锚anchor的串
		 */
		getHashStr : function (forceSharp){
		    return this.anchor ? "#" + this.serialize(this.anchor) : (forceSharp ? "#" : "");
		}
	};
	return Url;
});
/**
 * 模板
 * @param  {Object} $ SAB
 */
SAB.register('util.template',function($){
	return function(template, data,isDecode){
	    return template.replace(/#\{(.+?)\}/ig, function(){
	        var key = arguments[1].replace(/\s/ig, '');
	        var ret = arguments[0];
	        var list = key.split('||');
	        for (var i = 0, len = list.length; i < len; i += 1) {
	            if (/^default:.*$/.test(list[i])) {
	                ret = isDecode?decodeURIComponent(list[i].replace(/^default:/, '')):list[i].replace(/^default:/, '');
	                break;
	            }
	            else 
	                if (data[list[i]] !== undefined) {
	                    ret =isDecode?decodeURIComponent(data[list[i]]):data[list[i]];
	                    break;
	                }
	        }
	        return ret;
	    });
	};
});
/**
 *	log,控制台
 * @param  {Object} $ SAB
 */
SAB.register('app.log',function($){
	var trace = true;
	return function() {
		if (!trace) return;
		if (typeof console == 'undefined') return;
		var slice = Array.prototype.slice;
		var args = slice.call(arguments, 0);
		args.unshift("* SAB.app.log >>>>>>");
		try{
			console.log.apply(console, args);
		}catch(e){
			console.log(args);
		}

	};
});
/**
 * 截字，包括全角
 * @param  {Object} $ SAB
 */
SAB.register('app.strLeft',function($){
	return function (s, n) {
		var ELLIPSIS = '...';
		var s2 = s.slice(0, n),
			i = s2.replace(/[^\x00-\xff]/g, "**").length,
			j = s.length,
			k = s2.length;
		//if (i <= n) return s2;
		if(i<n){
			return s2;
		}else if(i==n){
			//原样返回
			if(n==j||k==j){
				return s2;
			}else{
				return s.slice(0,n-2)+ELLIPSIS;
			}
		}
		//汉字
		i -= s2.length;
		switch (i) {
			case 0: return s2;
			case n: 
				var s4;
				if(n==j){
					s4 = s.slice(0, (n>>1)-1);
					return s4+ELLIPSIS;
				}else{
					s4 = s.slice(0, n>>1);
					return s4;
				}
			default:
				var k = n - i,
					s3 = s.slice(k, n),
					j = s3.replace(/[\x00-\xff]/g, "").length;
				return j ? s.slice(0, k) + arguments.callee(s3, j) : s.slice(0, k);
		}
	};  

});
SAB.register('app.strLeft2',function($){
	var byteLen = $.util.byteLength
	return function(str,len){
		var s = str.replace(/\*/g, " ").replace(/[^\x00-\xff]/g, "**");
		str = str.slice(0, s.slice(0, len).replace(/\*\*/g, " ").replace(/\*/g, "").length);
		if(byteLen(str) > len) str = str.slice(0,str.length -1);
		return str;
	};

});
SAB.register('app.splitNum',function($){
	//千分位
	return function(num){
		num = num+""; 
		var re=/(-?\d+)(\d{3})/ 
		while(re.test(num))
		{ 
		num=num.replace(re,"$1,$2") 
		}
		return num;
	}
});
/**
 * 输入框占位
 * @param  {Object} $ SAB
 */
SAB.register('app.placeholder',function($){
	$globalInfo.supportPlaceholder = 'placeholder' in document.createElement('input');
	return function(inputs){

			function p(input){
				//如果支持placeholder,返回
				if($globalInfo.supportPlaceholder){
			        return;
				}
				//已经初始化，hasPlaceholder为1
				var hasPlaceholder = input.getAttribute('hasPlaceholder')||0;
				if(hasPlaceholder=='1'){
					return;
				}
				var toggleTip = function(){
					defaultValue = input.defaultValue;
					$.dom.addClass(input,'gray');
					input.value = text;
					input.onfocus = function(){

					    if(input.value === defaultValue || input.value === text){
					        this.value = '';
					        $.dom.removeClass(input,'gray');
					    }
					}
					input.onblur = function(){
					    if(input.value === ''){
					        this.value = text;
					        $.dom.addClass(input,'gray');
					    }
					}
				};
				var simulateTip = function(){
					var pwdPlaceholder = $.C('input');
					pwdPlaceholder.type='text';
					pwdPlaceholder.className = 'pwd_placeholder gray '+input.className;
					pwdPlaceholder.value=text;
					pwdPlaceholder.autocomplete = 'off';
					input.style.display='none';
		            input.parentNode.appendChild(pwdPlaceholder);
		            pwdPlaceholder.onfocus = function(){
		                pwdPlaceholder.style.display = 'none';
		                input.style.display = '';
		                input.focus();
		            }
		            input.onblur = function(){
		                if(input.value === ''){
		                    pwdPlaceholder.style.display='';  
		                    input.style.display='none';
		                }
		            }
				}

				//如果没有placeholder或者没有placeholder值，返回
				var text = input.getAttribute('placeholder');
				if(!text){
					//ie10 下的ie7 无法用input.getAttribute('placeholder')取到placeholder值，奇怪！
					if(input.attributes&&input.attributes.placeholder){
						text=input.attributes.placeholder.value;
					}
				}
				var tagName = input.tagName;
				if(tagName=='INPUT'){
					var inputType = input.type;
					if(inputType == 'password'&&text){
						simulateTip();
					}else if(inputType=='text'&&text){
						toggleTip();
					}
				}else if(tagName=='TEXTAREA'){
					toggleTip();
				}
				input.setAttribute('hasPlaceholder','1');

			}
			for (var i = inputs.length - 1; i >= 0; i--) {
				var input = inputs[i]
				p(input);
			};

		};   

});
/**
 * 锚点跳转
 * @param  {Object} $ SAB
 */
SAB.register('app.anchorGo',function($){
	/**
	 * @param  {HTML Element} trigger 带锚点的链接
	 * @param  {Number} time    动画时间
	 * @param  {Number} offset  偏移量
	 * @param  {Number} dir     方向，上下1，左右2
	 */
	return function(trigger,time,offset,dir,e){
		time = time||800;
		dir = dir||1;
		var destId = trigger.href.split('#')[1];
		dest = $.dom.byId(destId);
		offset = offset||0;
		switch(dir||1){
		    case 1: 
		    	var gap = parseInt(dest?jQuery(dest).offset().top:0)+offset;
		    	if(!$globalInfo.ua.isIE6){
			        jQuery("body,html").animate({scrollTop:gap},time);
		    	}else{
		    		document.documentElement.scrollTop=gap; 
					document.body.scrollTop=gap; 
		    	}
		        break;
		    case 2:
		        var gap = parseInt(dest?jQuery(dest).offset().left:0)+offset;
            	if(!$globalInfo.ua.isIE6){
        	        jQuery("body,html").animate({scrollLeft:gap},time);
            	}else{
            		document.documentElement.scrollLeft=gap; 
        			document.body.scrollLeft=gap; 
            	}
		        break;
		    default:
		    	return;
		}
		$.evt.preventDefault(e);
		return false;
	}
});
SAB.register('util.timer',function($){
	return new function(){
		this.list = {};
		this.refNum = 0;
		this.clock = null;	
		this.allpause = false;
		this.delay = 25;

		this.add = function(fun){
			if(typeof fun != 'function'){
				throw('The timer needs add a function as a parameters');
			}
			var key = '' 
				+ (new Date()).getTime()
				+ (Math.random())*Math.pow(10,17);

			this.list[key] = {'fun' : fun,'pause' : false};
			if(this.refNum <= 0){
				this.start();
			}
			this.refNum ++;
			return key;
		};

		this.remove = function(key){
			if(this.list[key]){
				delete this.list[key];
				this.refNum --;
			}
			if(this.refNum <= 0){
				this.stop();
			}
		};

		this.pause = function(key){
			if(this.list[key]){
				this.list[key]['pause'] = true;
			}
		};

		this.play = function(key){
			if(this.list[key]){
				this.list[key]['pause'] = false;
			}
		};

		this.stop = function(){
			clearInterval(this.clock);
			this.clock = null;
		};

		this.start = function(){
			var _this = this;
			this.clock = setInterval(
				function(){
					_this.loop.apply(_this)
				},
				this.delay
			);
		};

		this.loop = function(){
			for(var k in this.list){
				if(!this.list[k]['pause']){
					this.list[k]['fun']();
				}
			}
		};
	};
});

SAB.register("app.shine", function($) {
	var timer = $.util.timer;
	var b = function(a) {
			return a.slice(0, a.length - 1).concat(a.concat([]).reverse())
		};
	return function(c, d) {
		var e = $.util.parseParam({
			start: "#fff",
			color: "#fbb",
			times: 2,
			step: 5,
			length: 4
		}, d),
			f = e.start.split(""),
			g = e.color.split(""),
			h = [];
		for(var i = 0; i < e.step; i += 1) {
			var j = f[0];
			for(var k = 1; k < e.length; k += 1) {
				var l = parseInt(f[k], 16),
					m = parseInt(g[k], 16);
				j += Math.floor(parseInt(l + (m - l) * i / e.step, 10)).toString(16)
			}
			h.push(j)
		}
		for(var i = 0; i < e.times; i += 1) h = b(h);
		var n = !1,
			o = timer.add(function() {
				if(!h.length) timer.remove(o);
				else {
					if(n) {
						n = !1;
						return
					}
					n = !0;
					c.style.backgroundColor = h.pop()
				}
			})
	}
});/**
 * 评论数链接动画跳转
 */
SAB.register('job.cmntConfig',function($){
	var extend = $.clz.extend;
	//默认设置，页面设置，url设置
	var url = this.url = new $.util.url(location.href);
	var query = url.query||{};

	var commonFaces = {
			'哈哈':'haha',
			'偷笑':'tx',
			'泪':'lei',
			'嘻嘻':'xixi',
			'爱你':'aini',
			'挖鼻屎':'wbs',
			'心':'xin'
		};
	var allFaces = {'国旗': 'dc/flag_thumb', '走你': 'ed/zouni_thumb', '笑哈哈': '32/lxhwahaha_thumb', '江南style': '67/gangnamstyle_thumb', '吐血': '8c/lxhtuxue_thumb', '好激动': 'ae/lxhjidong_thumb', 'lt切克闹': '73/ltqiekenao_thumb', 'moc转发': 'cb/moczhuanfa_thumb', 'ala蹦': 'b7/alabeng_thumb', 'gst耐你': '1b/gstnaini_thumb', 'xb压力': 'e0/xbyali_thumb', 'din推撞': 'dd/dintuizhuang_thumb', '草泥马': '7a/shenshou_thumb', '神马': '60/horse2_thumb', '浮云': 'bc/fuyun_thumb', '给力': 'c9/geili_thumb', '围观': 'f2/wg_thumb', '威武': '70/vw_thumb', '熊猫': '6e/panda_thumb', '兔子': '81/rabbit_thumb', '奥特曼': 'bc/otm_thumb', '?': '15/j_thumb', '互粉': '89/hufen_thumb', '礼物': 'c4/liwu_thumb', '呵呵': 'ac/smilea_thumb', '嘻嘻': '0b/tootha_thumb', '哈哈': '6a/laugh', '可爱': '14/tza_thumb', '可怜': 'af/kl_thumb', '挖鼻屎': 'a0/kbsa_thumb', '吃惊': 'f4/cj_thumb', '害羞': '6e/shamea_thumb', '挤眼': 'c3/zy_thumb', '闭嘴': '29/bz_thumb', '鄙视': '71/bs2_thumb', '爱你': '6d/lovea_thumb', '泪': '9d/sada_thumb', '偷笑': '19/heia_thumb', '亲亲': '8f/qq_thumb', '生病': 'b6/sb_thumb', '太开心': '58/mb_thumb', '懒得理你': '17/ldln_thumb', '右哼哼': '98/yhh_thumb', '左哼哼': '6d/zhh_thumb', '嘘': 'a6/x_thumb', '衰': 'af/cry', '委屈': '73/wq_thumb', '吐': '9e/t_thumb', '打哈欠': 'f3/k_thumb', '抱抱': '27/bba_thumb', '怒': '7c/angrya_thumb', '疑问': '5c/yw_thumb', '馋嘴': 'a5/cza_thumb', '拜拜': '70/88_thumb', '思考': 'e9/sk_thumb', '汗': '24/sweata_thumb', '困': '7f/sleepya_thumb', '睡觉': '6b/sleepa_thumb', '钱': '90/money_thumb', '失望': '0c/sw_thumb', '酷': '40/cool_thumb', '花心': '8c/hsa_thumb', '哼': '49/hatea_thumb', '鼓掌': '36/gza_thumb', '晕': 'd9/dizzya_thumb', '悲伤': '1a/bs_thumb', '抓狂': '62/crazya_thumb', '黑线': '91/h_thumb', '阴险': '6d/yx_thumb', '怒骂': '89/nm_thumb', '心': '40/hearta_thumb', '伤心': 'ea/unheart', '猪头': '58/pig', 'ok': 'd6/ok_thumb', '耶': 'd9/ye_thumb', 'good': 'd8/good_thumb', '不要': 'c7/no_thumb', '赞': 'd0/z2_thumb', '来': '40/come_thumb', '弱': 'd8/sad_thumb', '蜡烛': '91/lazu_thumb', '蛋糕': '6a/cake', '钟': 'd3/clock_thumb', '话筒': '1b/m_thumb'};
	var DEFAULTS = {
		//评论微博转发视频地址
	    video_url:'',
	    //评论微博转发图片地址，可置空会自动取图
	    pic_url:'',
	    //频道
	    channel:'ty',
	    //新闻id
	    newsid:'6-12-6341970',
	    //组，默认为0,为1是为专题
	    group:0,
	    //编码
	    encoding:'gbk',
	    //是否显示评论列表,为1隐藏，为0显示
	    hideCMNTList:0,
		//微博转发参数
	    source: '新浪娱乐',
	    sourceUrl: 'http://ent.sina.com.cn/',
	    uid: '1642591402',
	    //是否是专题评论，本来为皮肤，历史问题，适配到group 具体咨询王磊
	    style:0,
	    channelId: 28,
	    //是否论坛页
	    isBBS:0,
	    //分布评论数
	    pageNum:10,
	    //热帖第一页评论数
	    hotPageNum:5,
	    //常用表情
	    commonFaces:commonFaces,
	    commonFacesBase:'http://i3.sinaimg.cn/dy/deco/2012/1217/face/',
	    //全部表情
	    allFaces:allFaces,
	    allFacesBase:'http://img.t.sinajs.cn/t35/style/images/common/face/ext/normal/'
	  };
	ARTICLE_DATA = typeof ARTICLE_DATA !='undefined'?ARTICLE_DATA:{}; 
 	ARTICLE_DATA = extend(DEFAULTS, ARTICLE_DATA, true);
 	ARTICLE_DATA = extend(ARTICLE_DATA, query, true);
});SAB.register('app.setWeiboUserInfo',function($){
	//全局的变量 $globalInfo
	return function(d){
			var data = d.result.data;
			//var $globalInfo = $globalInfo?$globalInfo:{};
		    if(data){
		    	$globalInfo.isWeiboLogin  = true;
		    	$globalInfo.weiboData = data;
		    	$globalInfo.weiboName = data.name;
		    	$globalInfo.weiboNick = data.screen_name;
		    	$globalInfo.weiboUser = data;
		    	$globalInfo.profile_image_url = data.profile_image_url;
		    	//触发微博登录事件
		    	var cusEvt = $.evt.custEvent;
		    	cusEvt.fire($, 'ce_weiboLogin');
		    }else{
		    	$globalInfo.isWeiboLogin  = false;

		    }
			//window.$globalInfo = $globalInfo;
		};   

});

/**
 * 设置全局用户信息
 * @param  {Object} $ SAB
 */
SAB.register('app.setSinaUserInfo',function($){
	//全局的变量 $globalInfo
	return function(){
			//var $globalInfo = $globalInfo?$globalInfo:{};
		    var cookie = sinaSSOController.get51UCCookie();
		    if(cookie){
				$globalInfo.isLogin  = true;
				$globalInfo.uid      = cookie.uid;
				$globalInfo.name     = cookie.name;
				$globalInfo.sinaNick = cookie.nick;
				$globalInfo.sinaUser = cookie;

				var cusEvt = $.evt.custEvent;
				var appLogin = $.app.login;

		    }else{
				$globalInfo.isLogin  = false;
				$globalInfo.isWeiboLogin = false;
		    }
			window.$globalInfo = $globalInfo;
		};   

});/**
 * 退出
 * @param  {Object} $ SAB
 */
SAB.register('app.logout',function($){
	return function(callback){
	    sinaSSOController.logout();
	    var _outime = setInterval(function(){
	    	if(!sinaSSOController.get51UCCookie()){
	    		clearInterval(_outime);
	    		callback.apply(this,arguments);
	    	}
	    },10);
	};
});/**
 * 登录
 * @param  {Object} $ SAB
 */
SAB.register('app.login',function($){
	return function(user,psd){
	    sinaSSOController.login(user,psd);
	};
});/**
 * 自动登录，循环调用
 * @param  {Object} $ SAB
 */
SAB.register('app.autoLogin',function($){
	return function(){
		var cusEvt = $.evt.custEvent;
	    var cookie = sinaSSOController.get51UCCookie();
	    //如果已经登录，不做处理，返回
	    if(cookie&&$globalInfo.isLogin){
	    	return;
	    }
	    //如果没登录，尝试自动登录
    	var cusEvt = $.evt.custEvent;
    	//触发退出事件，防止在其它页面退出，本页还是登录状态
    	cusEvt.fire($, 'ce_logout');

	    $.app.setSinaUserInfo();
		sinaSSOController.autoLogin(function(cookie){
			if(!cookie) return;
			$.app.setSinaUserInfo();
			$.app.isWeibo();
		});
	};
});/**
 * 通过接口判断登录后的用户是否微博用户
 * @param  {Object} $ SAB
 * @return {Boolean}   是否微博用户
 */
SAB.register('app.isWeibo',function($){
		return function(){
			var cusEvt = $.evt.custEvent;
			//ce_前缀为自定义事件
			var userUrl,userData,onUserSuccess;
			userUrl = 'http://api.sina.com.cn/weibo/2/users/show.json';
			userData = 'uid=' + $globalInfo.uid + '&source=2835469272';
			onUserSuccess = function(data){
				$.app.setWeiboUserInfo(data);
				//触发自定义事件ce_login
				cusEvt.fire($, 'ce_login');
			}
			$.io.jsonp(userUrl, userData, onUserSuccess);
		};
});/**
 * 登录模块，初始化各种登录自定义事件
 * @param  {Object} $ SAG
 */
SAB.register('module.login',function($){
	var cusEvt = $.evt.custEvent;
	var byClass = $.dom.byClass;
	var addClass = $.dom.addClass;
	//ce_前缀为自定义事件
	//登录新浪事件
	cusEvt.define($,'ce_login');
	//登录微博事件
	cusEvt.define($,'ce_weiboLogin');
	//登录出错事件
	cusEvt.define($,'ce_loginError');
	//退出事件
	cusEvt.define($,'ce_logout');

	var SINA_USER_LINK = 'http://login.sina.com.cn/member/my.php';
	var WEIBO_USER_LINK = 'http://weibo.com';
	var ICOHREF = ['http://login.sina.com.cn/member/my.php','http://weibo.com'];
	var ICOHTML = ['<img src="http://img.t.sinajs.cn/t4/style/images/common/transparent.gif" title="新浪 " alt="sina" class="username_icon"></a>','<img src="http://img.t.sinajs.cn/t4/style/images/common/transparent.gif" title="微博" alt="weibo" class="weibo_icon"></a>'];

	var login = function () {
		this.init();
	};
	login.prototype = {	
		init:function(){
			this.wrap = document.body;
			var cusEvt = $.evt.custEvent;
			this.bindEvent();
			if(typeof sinaSSOController =='undefined'){
				//throw "没载入sinaLogin.js";
				return;
			}
			//与顶部登录模块冲突
			var controller = sinaSSOController;
			controller.service = 'vblog';
			controller.pageCharset = 'GB2312';
			controller.setDomain = true;
			//之前是sinaSSOController.customLoginCallBack
			// controller.loginCallBack = function(loginStatus) {
			controller.customLoginCallBack = function(loginStatus) {
				//4049 验证码
				//if (loginStatus.retcode!=0) {
				if (!loginStatus.result) {
					cusEvt.fire($, 'ce_loginError');
					return false;
				}
				$.app.setSinaUserInfo();
				$.app.isWeibo();
			};
			controller.customLogoutCallBack = function(logoutStatus) {
				cusEvt.fire($, 'ce_logout');
			};
		},
		bindEvent:function(){
			var self = this;

			//绑定退出触发事件
			cusEvt.add($,'ce_logout',function(e){
			  	self.showUnlogined();
			},{});
			//绑定登录触发事件
			cusEvt.add($,'ce_login',function(e){
			    self.showLogined();
			},{});
			//绑定微博登录触发事件
			cusEvt.add($,'ce_weiboLogin',function(e){
			    self.showWeiboLogined();
			},{});
		},
		showLogined:function(wrap){
			var name = $globalInfo.sinaNick || $globalInfo.name;
			//宽度只能支持8个汉字srtLeft(name,16)
			var shortName = name;

			var isWeibo = 0;
			if($globalInfo.isWeiboLogin){
				name = $globalInfo.weiboNick||$globalInfo.weiboName;
				shortName = $.app.strLeft(name,16);
				name = '<a href="'+WEIBO_USER_LINK+'" target="_blank">'+shortName+'</a>';
				isWeibo = 1;
			}else{
				shortName = $.app.strLeft(name,16);
				name = '<a href="'+SINA_USER_LINK+'" target="_blank">'+shortName+'</a>';
			}
			var wrap = wrap||this.wrap;
			var login_ico = byClass('J_Login_Ico',wrap);
			var unlogin_dom = byClass('J_Unlogin',wrap);
			var logined_dom = byClass('J_Logined',wrap);
			var name_dom = byClass('J_Name',wrap);
			for (var i=0,len=login_ico.length; i<len;i++) {
				var item = login_ico[i];
				item.href= ICOHREF[isWeibo];
				item.innerHTML = ICOHTML[isWeibo];
			};
			for (var i=0,len=unlogin_dom.length; i<len;i++) {
				unlogin_dom[i].style.display = 'none';
			};
			for (var i=0,len=logined_dom.length; i<len;i++) {
				logined_dom[i].style.display = '';
			};
			for (var i=0,len=name_dom.length; i<len;i++) {
				name_dom[i].innerHTML = name;
			};
			//登录成功后清空所有用户名和密码，并加上提示
			var user_dom = byClass('J_Login_User',wrap);
			var psw_dom = byClass('J_Login_Psw',wrap);

			for (var i=0, len=user_dom.length; i<len; i++) {
				var item = user_dom[i];
				if($globalInfo.supportPlaceholder){
					item.value = '';
				}else{
					item.value = item.getAttribute('placeholder');
					addClass(item,'gray');
				}

			};
			for (var i=0, len=psw_dom.length; i<len; i++) {
				// 有可能是模拟placeholder pwd_placeholder
				var item = psw_dom[i];
				if(item.className.indexOf('pwd_placeholder')==-1){
					item.value = '';
				}
			};
		},
		showUnlogined:function(wrap){
			var wrap = wrap||this.wrap;
			var unlogin_dom = byClass('J_Unlogin',wrap);
			var logined_dom = byClass('J_Logined',wrap);
			var weiboLogined_dom = byClass('J_WeiboLogined',wrap);
			for (var i=0, len=unlogin_dom.length; i<len; i++) {
				unlogin_dom[i].style.display = '';
			};
			for (var i=0, len=logined_dom.length; i<len; i++) {
				logined_dom[i].style.display = 'none';
			};
			for (var i=0, len=weiboLogined_dom.length; i<len; i++) {
				weiboLogined_dom[i].style.display = 'none';	
			};
		},
		showWeiboUnLogined:function(wrap){
			var wrap = wrap||this.wrap;
			var weiboLogined_dom = byClass('J_WeiboLogined',wrap);
			for (var i=0,len=weiboLogined_dom.length;i<len;i++) {		
				weiboLogined_dom[i].style.display = 'none';
			};
		},
		showWeiboLogined:function(wrap){
			var wrap = wrap||this.wrap;
			var weiboLogined_dom = byClass('J_WeiboLogined',wrap);
			for (var i=0,len=weiboLogined_dom.length;i<len;i++) {			
				weiboLogined_dom[i].style.display = '';
			};
		}

	};
	var login1 = new login();
	return login1;
});
/**
 * 顶部登录
 * @param  {Object} $ SAB
 */
SAB.register('job.topLogin',function($){
		var byClass = $.dom.byClass;
		var addClz     = $.dom.addClass;
		var removeClz  = $.dom.removeClass;
		var addEvt     = $.evt.addEvent;
		var cusEvt     = $.evt.custEvent;
		var dldEvt     = $.evt.delegatedEvent;
		var s_contains = $.dom.contains;
		var topLogin = function () {
			this.init();
		};
		topLogin.prototype = {	
			init:function(){
				var self = this;
				self.intervalCount = null;
				self.weiboData = [];
				self.getDom();
			},
			getDom:function(){
				var byId = $.dom.byId;
				var dom = {
					unlogin : byId('J_Unlogin_A'),
					loginLink : byId('J_Login_Btn_A'),
					logined: byId('J_Logined_A'),
					loginedName : byId('J_Logined_Name_A'),
					loginedList :byId('J_Logined_List_A')
				};
				if(!dom.unlogin){
					return;
				}
				this.dom = dom;
				this.changeLoginLink().bindEvent();
			},
			changeLoginLink:function(){
				this.dom.loginLink.href = 'http://login.sina.com.cn/signup/signin.php?r='+location.href;
				return this;
			},
			bindEvent:function(){
				var self   = this;
				var dom    = self.dom;
				var domLogined = dom.logined;
				var loginedList = {
					dom:dom.loginedList,
					isShow:false,
					show:function(){
						if(this.dom.innerHTML!==''){
						  this.dom.style.display = 'block';
						  this.isShow = true;
						}
					},
					hide:function(){
						this.dom.style.display = 'none';
						this.isShow = false;
					}
				};
				var stopDefault = function(e) {  
				    if (e && e.preventDefault){
				        e.preventDefault(); 
				    }else{
				    	window.event.returnValue = false;
				    } 
				    return false;
				}
				//用户下拉列表事件
				if(!$globalInfo.ua.isIOS){
					addEvt(dom.loginedName,'mouseover',function(){
					  loginedList.show.apply(loginedList);
					});
					addEvt(domLogined,'mouseout',function(e){
						var e = e||window.event;
						//ie 内部点击链接跳出现页面时，触发mouseout移出时没有reltg
						try{
							var reltg = e.relatedTarget||e.toElement;
							var reltgTagName = reltg.tagName;
							if(s_contains(reltg,domLogined)||!(s_contains(domLogined,reltg))){
								loginedList.hide.apply(loginedList);
							}
						}catch(e){
							loginedList.hide.apply(loginedList);
						}

					});
				}else{
					addEvt(dom.loginedName,'click',function(e){
						e = e||window.event;
						if(!loginedList.isShow){
							loginedList.show.apply(loginedList);
						}
						stopDefault(e);
					});
				}
				addEvt(document.documentElement,'click',function(e){
					if(!loginedList.isShow){
						return;
					}
					e = e||window.event;
					var t = e.target||e.srcElement;
					if(s_contains(t,domLogined)||!(s_contains(domLogined,t))){
						loginedList.hide.apply(loginedList);
					}

				});
				//自定义的退出，登录事件
				var dldEvt_logined = dldEvt(domLogined);
				//点击退出
				dldEvt_logined.add('logout','click',function(e){
					var onlogout = function(){
						location.reload();
					}
				    $.app.logout(onlogout);
				});
				//绑定退出触发事件
				cusEvt.add($,'ce_logout',function(e){
				  	self.showUnlogined();
				},{});
				//绑定登录触发事件,有可能dom还没初始化,登录已经完成并触发了
				cusEvt.add($,'ce_login',function(e){
				    self.showLogined();

				},{});
				if($globalInfo.isLogin){
					self.showLogined();
				}
				//点击时，马上更新一次列表数目
				dldEvt_logined.add('reset','click',function(e){
					setTimeout(function(){
					    self.updateCounts();
					},1000);
				});

				return self;
			},
			get_user_lnk:function(name){
				var sName = $.app.strLeft(name,8);
				//sName = sName==name?name:sName+'...';
				var WBURL = 'http://weibo.com/';
				var SINAURL = 'http://login.sina.com.cn/member/my.php';
				var lnk = $globalInfo.isWeiboLogin?WBURL:SINAURL;
				return '<a href="'+lnk+'" target="_blank" title="'+name+'">'+sName+'</a>';
			},
			showLogined:function(){
				var self = this;
				var dom = self.dom;
				var loginedName = dom.loginedName;
				dom.unlogin.style.display = 'none';
				var name = $globalInfo.weiboNick||$globalInfo.sinaNick||$globalInfo.weiboName||$globalInfo.name;
				var icoClz = '';

				self.nameLnk = self.get_user_lnk(name);
				if($globalInfo.isWeiboLogin){
					addClz(loginedName,'wt_user_weibo');
					icoClz = 'drop_weibo';
				}else{
					removeClz(loginedName,'wt_user_weibo');
				}
				loginedName.innerHTML = '<span class="wt_user_cont">'+self.nameLnk+'</span>';
				loginedName.title = name;
				var list_html = '<h2 class="'+icoClz+'" ><span class="h2cont">'+self.nameLnk+'</span></h2>'
				  +'<ul>'
				    +self.listRender()
				    +'<li class="drop_logout"><a href="javascript:;" onclick="return false;" action-type="logout" action-data="">退出</a></li>'
				  +'</ul>';
				dom.loginedList.innerHTML = list_html;
				dom.logined.style.display = 'block';
				self.updateCounts();
			},
			showUnlogined:function(){
				var dom = this.dom;
				//统一登录的退出回调不可用，手动显示登录链接 TODO 梁栋
				var btn = $.dom.byClass('outlogin_LoginBtn',dom.unlogin,'a')[0];
				if(btn){btn.style.display = '';}
				dom.unlogin.style.display = 'block';
				dom.loginedList.innerHTML = '';
				dom.logined.style.display = 'none';
				$.evt.removeEvent(dom.logined);
			},
			listRender:function(){
				var self = this;
				var WEIBO = 'http://weibo.com/';
				function render(){
					var countArr = [
						{
							url:WEIBO+'messages?topnav=1&amp;wvr=4',
							txt:'私信'
						},
						{
							url:WEIBO+'comment/inbox?topnav=1&amp;wvr=4&amp;f=1',
							txt:'评论'
						},
						{
							url:WEIBO+'at/weibo?topnav=1&amp;wvr=4',
							txt:'@我'
						}
					];
					var html=[];
					for (var i = 0; i < countArr.length; i++) {
						var item = countArr[i];
						html.push('<li><a action-type="reset" href="'+item.url+'" title="'+item.txt+'" target="_blank">'+item.txt+'</a></li>');
					};
					return html.join('');
				}
				if($globalInfo.isWeiboLogin){
					return render();
				}else{
					return '';
				}

			},
			updateCounts:function(){
				var self = this;
				var COUNTBASE = 'http://api.sina.com.cn/weibo/2/remind/unread_count.json';
				var list = self.dom.loginedList;
				var name1 = self.dom.loginedName;

				function formatNum(n,m){
					n = parseInt(n);
					return n>m?m+'+':n;
				}
				function updateCounts(d){
					if(!d){return;}
					//dm新私信数，cmt新评论数，mention_cmt新提及我的评论数，metion_status新提及我的微博数
					var counts = [d.dm,d.cmt,d.mention_cmt+d.mention_status];
					var nameLnk = self.nameLnk;
					//如果两次数据都一样，就不渲染了
					if(self.weiboData.toString()==counts.toString()){
						return;
					}else{
						self.weiboData=counts;
					}
					var total = 0;
					//更新列表
					for (var i = 0; i < 3; i++) {
						var item = links[i];
						var count = counts[i];

						if(count!==0){
							item.innerHTML = item.title + '(<span class="fred">'+formatNum(count,999)+'</span>)';
						}else{
							item.innerHTML = item.title;
						}
						total +=count;
					};
					//更新名字数字
					if(total!==0){
						name1.innerHTML = '<span class="wt_user_cont">'+nameLnk + '(<span class="fred">'+formatNum(total,999)+'</span>)</span>';
						name2.innerHTML = '<span class="h2cont">'+nameLnk + '(<span class="fred">'+formatNum(total,999)+'</span>)</span>';
					}else{
						name1.innerHTML = '<span class="wt_user_cont">'+nameLnk + '</span>';
						name2.innerHTML = '<span class="h2cont">'+nameLnk + '</span>';
					}

				}
				if($globalInfo.isWeiboLogin){
					var name2 = list.getElementsByTagName('h2')[0];
					var links = list.getElementsByTagName('ul')[0].getElementsByTagName('a');
					var param = 'uid=' + $globalInfo.uid + '&source=' + ARTICLE_DATA.appkey;
					var onUserSuccess = function(data){
						updateCounts(data.result.data);
					};
					$.io.jsonp(COUNTBASE,param,onUserSuccess);
				}
				if(self.intervalCount){
					clearTimeout(self.intervalCount);
				}
				self.intervalCount = setTimeout(function(){
					self.updateCounts();
				},30*1000);
			}

		};
	$.dom.ready(function(){
		var topLogin1 = new topLogin();
	});

});/**
 * 顶部统一浮层登录，依赖http://i.sso.sina.com.cn/js/outlogin_layer.js
 * @param  {Object} $ SAB
 */
SAB.register('job.topLoginForm',function($){
	var cusEvt = $.evt.custEvent;
	var addEvt = $.evt.addEvent;
	//ce_前缀为自定义事件

	var loginLayer = window.SINA_OUTLOGIN_LAYER;

	if(loginLayer){
		var lSTK = loginLayer.STK;
		lSTK.Ready(function(){
			var nickNode = lSTK.E('J_Logined_Name_A');
			var btnLogin = lSTK.E('J_Login_Btn_A');
			//var btnLogout = lSTK.E('logout_button');
			// 头部登录重置登录框位置信息
			addEvt(btnLogin,'mousedown',function(){
				loginLayer.set('styles', {
					'marginTop' : '30px',
					'marginLeft' : '7px'
				});
			});
			loginLayer.set('sso', {
				entry : 'account'
			})
			.set('styles', {
				'marginTop' : '30px',
				'marginLeft' : '7px',
				'zIndex':'10001'
			})
			.set('plugin', {
				position : 'top,right',					//设置相对位置
				relatedNode : lSTK.E('J_Login_Btn_A')	//设置定位元素
			})
			.register('login_success', function(){
				//处理登录成功需要执行的操作
				$.app.setSinaUserInfo();
				$.app.isWeibo();

			})
			.register('logout_success', function(){
				//处理登录成功需要执行的操作,不起作用，TODO梁栋
				cusEvt.fire($, 'ce_logout');
			})
			.setLoginButton(btnLogin)
			.init();

			// lSTK.addEvent(btnLogout, 'click', function(){
			// 	$.preventDefault();
			// 	loginLayer.logout();
			// });
			// 

		});
	}});
/*影视打分登录信息注册*/
SAB.register('job.setFtScoringInfo',function($){
	var cusEvt = $.evt.custEvent;

	// 注册登录信息
	var setFtScoringInfo = function(){
		var byId = $.dom.byId;
		var byClass = $.dom.byClass;
		var addClass = $.dom.addClass;
	    var removeClass = $.dom.removeClass;
		var Login = $globalInfo.isLogin;
		var weiboLogin = $globalInfo.isWeiboLogin;

		var BASESINAURL = 'http://blog.sina.com.cn/u/';
		var BASEWEIBOURL = 'http://weibo.com/';
		var INFOURL = 'http://dafen.ent.sina.com.cn/get_user_comment?format=json';

		// 未登录时头像地址
		var unLoginImg = 'http://i3.sinaimg.cn/dy/deco/2012/1018/sina_comment_defaultface.png';
		var unLoginTip = '未登录';
	
		var dom = {
			Jface : byId('J_Ft_User_Face'),
			loginBtn : byClass('J_Comment_Submit',byId('J_Form_Wrap'))[0],
			scoreTop : byId('J_Fx_Top')
		};

		// 登录时获取评论信息
		if(!!Login){
			var param = 'movie_id=' + ARTICLE_DATA.movieID;
			var onUserSuccess = function(data){
				var status = data.result.status;
				if(status.code==0){
					var data = data.result.data[0];
					var all = data.all;
					var story = data.story || '0.0';
					var act = data.act || '0.0';
					var director = data.director || '0.0';
					var picture = data.picture || '0.0';
					var music = data.music || '0.0';
					var content = data.cmnt.content;
					var contentWrap = byId('J_Comment_After').getElementsByTagName('SPAN')[0];
					var mBar = $globalInfo.abBar.mBar;
					var fxBar = $globalInfo.abBar.fxBar;
					if(!!all){
						$globalInfo.hasCommented = true;
						mBar.setSpeScore(all);
						mBar.eventControl={'move':false,'down':false};
						if($globalInfo.hasCommented){
							var prev = $.dom.byId('J_Comment_Prev');
							var after = $.dom.byId('J_Comment_After');
							var countTip = $.dom.byId('J_Count_Tip');
							prev.style.display = 'none';
							after.style.display = 'block';
							countTip.innerHTML = 0;
						}
						// 只要存在一项分项打分，则所有的分项打分必然存在
						if(!!parseInt(story)){
							$globalInfo.abBar.stopFXPF = true;
							$globalInfo.abBar.story = story;
							$globalInfo.abBar.act = act;
							$globalInfo.abBar.director = director;
							$globalInfo.abBar.picture = picture;
							$globalInfo.abBar.music = music;
						}
						if($globalInfo.abBar.fxBar.length != 0){
							var fxBar = $globalInfo.abBar.fxBar;
							fxBar[0].setSpeScore(story,true);
							fxBar[1].setSpeScore(act,true);
							fxBar[2].setSpeScore(director,true);
							fxBar[3].setSpeScore(picture,true);
							fxBar[4].setSpeScore(music,true);
						}
						contentWrap.innerHTML = content;
					}
					
					// console.log(all+'-'+story+'-'+act+'-'+director+'-'+picture+'-'+music+'-'+content);
				}
			};
			$.io.jsonp(INFOURL,param,onUserSuccess);
		}


		function setUserInfo(flag){
			var face,nickName,url;
			if(!!flag){
				face = $globalInfo.weiboData.profile_image_url;
				nickName = $globalInfo.weiboData.screen_name;
				!!$globalInfo.weiboData.domain ? url = BASEWEIBOURL + $globalInfo.weiboData.domain : url = BASEWEIBOURL + $globalInfo.uid;		
			} else {
				face = 'http://i3.sinaimg.cn/dy/deco/2012/1018/sina_comment_defaultface.png';
				nickName = $globalInfo.sinaNick;
				url = BASESINAURL + $globalInfo.uid;
			}
			dom.Jface.innerHTML = '<a href="' + url + '" title="' + nickName + '" target="_blank"><img src="' + face + '" alt="' + nickName + '" title="' + nickName + '" /></a><a href="' + url + '" title="' + nickName + '" target="_blank" class="user_name">' + nickName + '</a>';
		}

		function resetUserInfo() {
			dom.Jface.innerHTML = '<span><img src="' + unLoginImg + '" alt="' + unLoginTip + '" title="' + unLoginTip + '" /></span><span class="user_name">' + unLoginTip + '</span>';
		}

		if(!!Login){
			removeClass(dom.loginBtn,'post_inline_login');
			if(weiboLogin){
				setUserInfo(true);
			} else {
				setUserInfo(false);
			}
		} else {
			addClass(dom.loginBtn,' post_inline_login');
			resetUserInfo();
		}
	};

	cusEvt.add($,'ce_login',setFtScoringInfo);
});
/*影视打分未登录时注销用户信息*/
SAB.register('job.resetFtScoringInfo',function($){
	var cusEvt = $.evt.custEvent;
	// 未登录时重置用户信息
	var resetFtScoringInfo = function(){
		var byId = $.dom.byId;
		var byClass = $.dom.byClass;
		var addClass = $.dom.addClass;
	    var removeClass = $.dom.removeClass;
		var Login = $globalInfo.isLogin;
		var weiboLogin = $globalInfo.isWeiboLogin;

		var BASESINAURL = 'http://blog.sina.com.cn/u/';
		var BASEWEIBOURL = 'http://weibo.com/';

		// 未登录时头像地址
		var unLoginImg = 'http://i3.sinaimg.cn/dy/deco/2012/1018/sina_comment_defaultface.png';
		var unLoginTip = '未登录';
		
		var dom = {
			Jface : byId('J_Ft_User_Face'),
			loginBtn : byClass('J_Comment_Submit',byId('J_Form_Wrap'))[0]
		};

		if($globalInfo.hasCommented){
			var prev = $.dom.byId('J_Comment_Prev');
			var after = $.dom.byId('J_Comment_After');
			var countTip = $.dom.byId('J_Count_Tip');
			prev.style.display = 'block';
			after.style.display = 'none';
			countTip.innerHTML = 0;
		}
		/*$globalInfo.abBar.hasCommented = false;
		$globalInfo.abBar.stopFXPF = false;
		if($globalInfo.abBar.mBar){
			$globalInfo.abBar.mBar.resetSpeBar();
		}
		if($globalInfo.abBar.fxBar.length != 0){
			var fxBar = $globalInfo.abBar.fxBar;
			fxBar[0].resetSpeBar();
			fxBar[1].resetSpeBar();
			fxBar[2].resetSpeBar();
			fxBar[3].resetSpeBar();
			fxBar[4].resetSpeBar();
		}
		$globalInfo.fxActive = new $.app.fxActive();*/
		function resetUserInfo() {
			dom.Jface.innerHTML = '<span><img src="' + unLoginImg + '" alt="' + unLoginTip + '" title="' + unLoginTip + '" /></span><span class="user_name">' + unLoginTip + '</span>';
		}
	
		addClass(dom.loginBtn,' post_inline_login');
		resetUserInfo();		
	};
	// cusEvt.add($,'ce_logout',resetFtScoringInfo);
});
/*打分条，随鼠标 移动*/
SAB.register('app.MMBar',function($){
	var byId = $.dom.byId;
	var byClass = $.dom.byClass;
	var addClass = $.dom.addClass;
	var removeClass = $.dom.removeClass;
	var hasClass = $.dom.hasClass;
	var getXY = $.dom.getXY;
	var addEvt = $.evt.addEvent;
	var removeEvt = $.evt.removeEvent;
	var doc = document;
	var win = window;
	var body = doc.getElementsByTagName('BODY')[0];

	var MMBar = function(botBar,map,unabledClass,wrap){
		this.wrap = byId(wrap) || body;
		this.botBar = byClass(botBar,this.wrap)[0];
		this.topBar = this.botBar.getElementsByTagName('*')[0];
		this.scoreTxt = byClass(botBar + '_Score',this.wrap)[0];
		this.scoreDes = byClass(botBar + '_Des',this.wrap)[0];
		this.ulWrap = this.wrap;
		this.rect = {};
		this.mousePos = {};
		this.actived = true; 
		this.sure = false; //打分条是否点击过
		this.mout = false; //鼠标是否已经离开打分条
		this.eventControl = {
			'move': true,
			'down': true
		};
		this.map = map || {
			'0' : '未打分',
			'1' : '烂到极点',
			'2' : '浪费时间',
			'3' : '拙劣可笑',
			'4' : '一般难看',
			'5' : '平庸之作',
			'6' : '尚可一看',
			'7' : '不乏亮点',
			'8' : '非常优秀',
			'9' : '接近完美',
			'10' : '经典之作'
		};
		this.unabledClass = unabledClass || 'score_bar_unabled';
		this.score = 0;
		this.init.apply(this,arguments);
	}

	MMBar.prototype = {
		init: function(){
			var self = this;
			var rect = self.getTarRect(self.botBar);
			var length = self.length = self.botBar.offsetWidth;
			addEvt(self.botBar,'mousemove',function(e){
				self.mout = false;//鼠标进入目标对象
				if($globalInfo.hasCommented && $globalInfo.abBar.stopFXPF){return}
				if(!self.actived){self.resetBar()}
				if(self.eventControl.move){
					var mousePos = self.getMousePos(e);
					var moveX = mousePos.x - rect.x1;
					var rate = moveX / length;
					self.doBarMove(rate,length);
				} else {
					return
				}
			});
			// 移出恢复初始状态
			if(self.ulWrap && !$globalInfo.ua.isIOS){
				addEvt(self.ulWrap,'mousemove',function(e){
					if(self.eventControl['down'] == false){return}
					e = e || window.event;
					tar = e.target || e.srcElement;
					if(tar != self.botBar && tar != self.topBar){
						if(!self.sure && !self.mout){
							if(typeof self.resetSpeBar === 'function') {
								self.resetSpeBar();
								self.mout = true;
							}
						} else {
							return
						}
					}
				});
			}
			addEvt(self.botBar,'mousedown',function(e){
				if($globalInfo.hasCommented && $globalInfo.abBar.stopFXPF){return}
				if(!self.actived){self.resetBar()}
				self.eventControl.move = false;
				if(self.eventControl.down){
					var mousePos = self.getMousePos(e);
					var moveX = mousePos.x - rect.x1;
					var rate = moveX / length;
					self.sure = true;
					self.doBarMove(rate,length);
				} else {
					return
				}
			});
		},
		containsElement: function(obj1, obj2) {
			while (obj2.nodeName != 'HTML') {
				if (obj2 == obj1) return true;
				obj2 = obj2.parentNode
			}
			return false
		},
		doBarMove: function(rate,length){
			var self = this;
			var barWidth = 0;
			var tmpScore = self.score;
			if(rate > 0 && rate <= 0.1) {
				barWidth = parseInt(0.1*length);
				self.score = 1;
				self.setTips('1');
			} else if(rate > 0.1 && rate <= 0.2) {
				barWidth = parseInt(0.2*length);
				self.score = 2;
				self.setTips('2');
			} else if(rate > 0.2 && rate <= 0.3) {
				barWidth = parseInt(0.3*length);
				self.score = 3;
				self.setTips('3');
			} else if(rate > 0.3 && rate <= 0.4) {
				barWidth = parseInt(0.4*length);
				self.score = 4;
				self.setTips('4');
			} else if(rate > 0.4 && rate <= 0.5) {
				barWidth = parseInt(0.5*length);
				self.score = 5;
				self.setTips('5');	
			} else if(rate > 0.5 && rate <= 0.6) {
				barWidth = parseInt(0.6*length);
				self.score = 6;
				self.setTips('6');
			} else if(rate > 0.6 && rate <= 0.7) {
				barWidth = parseInt(0.7*length);
				self.score = 7;
				self.setTips('7');
			} else if(rate > 0.7 && rate <= 0.8) {
				barWidth = parseInt(0.8*length);
				self.score = 8;
				self.setTips('8');
			} else if(rate > 0.8 && rate <= 0.9) {
				barWidth = parseInt(0.9*length);
				self.score = 9;
				self.setTips('9');
			} else if(rate > 0.9 && rate <= 1.0) {
				barWidth = parseInt(1.0*length);
				self.score = 10;
				self.setTips('10');
			} else {
				barWidth = 1;
				self.score = 1;
			}
			// console.log(self.barWidth);
			if(self.score != tmpScore){
				self.onScoreChange();
			}
			self.topBar.style.width = barWidth + 'px';
		},
		setSpeScore: function(score,stopF){
			this.topBar.style.width = (this.length*parseFloat(score)/10) + 'px';
			this.score = score;
			this.setTips(score+'',true);
			if(stopF){
				this.eventControl={'move':false,'down':false}
			};
		},
		resetSpeBar: function(){
			
			this.topBar.style.width = '0px';
			this.score = 0;
			this.eventControl = {
				'move': true,
				'down': true
			};
			this.setTips('0');
			
		},
		onScoreChange: function(){

		},

		stopComment: function(){
			this.eventControl = {'move': false, 'down':false}
		},

		setTips : function(num,spe){
			try {
				if(parseInt(num) != '0'){
					addClass(this.scoreTxt,' cyellow');
					addClass(this.scoreDes,' cyellow');
				} else {
					removeClass(this.scoreTxt,'cyellow');
					removeClass(this.scoreDes,'cyellow')
				}
				if(spe) {
					if(parseInt(num)==10){num=10}
					this.scoreTxt.innerHTML = num;
					this.scoreDes.innerHTML = this.map[Math.round(num)+''];
				} else {
					if(parseInt(num)==10){num='10'}
					this.scoreTxt.innerHTML = this.formatScore(num);
					this.scoreDes.innerHTML = this.map[num];
				}
			} catch (err){

			}
		},

		setUnabled : function(){
			unabledClass = this.unabledClass;
			if(!hasClass(this.botBar,unabledClass)){
				this.actived = false;
				addClass(this.botBar,' ' + unabledClass);
				this.topBar.style.width = '0px';
				this.scoreTxt.style.color = '#fd3716';
				this.scoreTxt.parentNode.style.color = '#fd3716';
				this.scoreDes.innerHTML = '您还没提交打分';
				this.scoreDes.style.color = '#fd3716';
			}
		},

		resetBar : function(){
			if(hasClass(this.botBar,unabledClass)){
				this.actived = true;
				removeClass(this.botBar,unabledClass);
				this.scoreTxt.parentNode.style.color = '#999';
			}
		},

		getTarRect: function(el){
			var x1 = getXY(el)[0];
			var y1 = getXY(el)[1];
			var x2 = x1 + el.offsetWidth;
			var y2 = y1 + el.offsetHeight;

			this.rect = {
				'x1' : x1,
				'x2' : x2,
				'y1' : y1,
				'y2' : y2
			} 

			return this.rect;
		},
		formatScore: function(num){
			if(parseInt(num) == 10){
				return 10;
			} else {
				return num + '.0';
			}
		},

		getMousePos: function(event){
			var event = event || window.event;
			var sLeft = document.documentElement.scrollLeft || document.body.scrollLeft;
            var sTop = document.documentElement.scrollTop || document.body.scrollTop;
			var mouseCoords = function(event){
				if (event.pageX || event.pageY) {
					return {
						x: event.pageX,
						y: event.pageY
					};
				}
				return {
					x: event.clientX + sLeft - document.body.clientLeft,
					y: event.clientY + sTop - document.body.clientTop
				};
			}
			var mousePos = mouseCoords(event);
			return mousePos;
		}

	};

	return MMBar;
});
/*分项打分激活*/
SAB.register('app.fxActive',function($){
	var byId = $.dom.byId;
	var addClass = $.dom.addClass;
	var byClass = $.dom.byClass;
	var removeClass = $.dom.removeClass;
	var addEvt = $.evt.addEvent;
	var removeEvent = $.evt.removeEvent;
	var extend = $.clz.objExtend;
	var MMBar = $.app.MMBar;
	var getXY = $.dom.getXY;
	var doc = document;
	var body = doc.getElementsByTagName('BODY')[0];

	var fxActive = function(){
		this.fxBtn = byId('J_Fx_Score_Btn');
		this.fxDet = byId('J_Fx_Score_Detail');
		this.totalScore = byId('J_Total_Score');
		this.scoreTip = byId('J_Score_Tip');
		this.submit = byId('J_Fx_Submit');
		this.cancel = byId('J_Fx_Cancel');
		this.fxScoreWrap = byId('J_Fx_Score_Wrap');
		this.joinMem = byId('J_Join_Mem');
		this.fxCont =  byClass('fx_score_cont',this.fxDet)[0];
		this.fxBg = byClass('fx_score_bg',this.fxDet)[0];
		this.fxClose = byClass('J_Fx_Close',this.fxDet)[0];
		this.fxDeal = byClass('fx_score_deal',this.fxDet)[0];
		this.fxTop = byId('J_Fx_Top');
		this.barArr = [];
		this.map = {
			'1': 'story',
			'2': 'act',
			'3': 'director',
			'4': 'picture',
			'5': 'music'
		};
		this.status = 'close';
		this.firstOpen = true;
        this.fx_usercount = byId('J_Fx_UserCount');
		this.mBar = {};
		this.nMMBar = {};
		this.init.apply(this,arguments);
	}

	fxActive.prototype = {
		init : function(){
			var self = this;
			if(!$globalInfo.abBar) {
				$globalInfo.abBar = {};
			}
			if(!$globalInfo.score){
				$globalInfo.score = {};
				$globalInfo.score.story = 0;
				$globalInfo.score.act = 0;
				$globalInfo.score.director = 0;
				$globalInfo.score.picture = 0;
				$globalInfo.score.music = 0;
			}
			if(!$globalInfo.abBar.fxBar){
				$globalInfo.abBar.fxBar = [];
			}
			/*是否已经评分*/
			$globalInfo.hasCommented = ARTICLE_DATA.hasCommented;
			/*分项打分条控制标志*/
			$globalInfo.abBar.stopFXPF = ARTICLE_DATA.hasFxScore;
			$globalInfo.abBar.mBar = self.mBar = new MMBar('J_Main_Bar');
			if($globalInfo.hasCommented){self.mBar.eventControl={'move':false,'down':false}}	
			// $globalInfo.abBar.cBar = [];
			self.nMMBar = function(){
				MMBar.apply(this,arguments);
			}
			extend(self.nMMBar,MMBar);
			self.nMMBar.prototype.ulWrap = byClass('J_Fx_Ul_Wrap',self.fxDet)[0];
			self.nMMBar.prototype.onScoreChange = function(){
				self.scroeHasChanged();
			}
			self.nMMBar.prototype.resetBar = function(){
				self.nowResetBar();
			}
			self.nMMBar.prototype.resetSpeBar = function(){
				this.botBar.parentNode.parentNode.className = 'score_undo clearfix';
				this.topBar.style.width = '0px';
				this.score = 0;
				this.eventControl = {
					'move': true,
					'down': true
				};
				this.setTips('0');
				this.onScoreChange();
			}
			
			self.bindEvents();
		},
		bindEvents: function(){
			var self = this;
			/*open*/
			addEvt(self.fxBtn,'mousedown',function(){
				if($globalInfo.hasCommented){self.fxStyleChanged();}
				if($globalInfo.hasCommented && $globalInfo.abBar.stopFXPF){self.fxHidSubmit();}
				if(self.status === 'close'){
					self.open();
					if(self.firstOpen){
						self.firstOpen = false;	
						for(var i = 1; i < 6; i++){
							(function(i){
								var tmpScore = $globalInfo.abBar[self.map[i]] || '0.0';
								parseInt(tmpScore) == 10 && (tmpScore = 10);
								$globalInfo.abBar.fxBar[i-1] = self.barArr[i] = new self.nMMBar('J_Other_Bar0'+i);
								self.barArr[i].setSpeScore(tmpScore);
								if($globalInfo.abBar.stopFXPF){self.barArr[i].eventControl={'move':false,'down':false}}
							})(i);
						}
					}
				} else if(status = 'open') {
					self.close();
				}
			});

			/*reset*/
			addEvt(self.cancel,'mousedown',function(){
				if($globalInfo.abBar.stopFXPF){self.close();return}
				for(var i = 1; i < 6; i++){
					self.barArr[i].resetSpeBar();
				}
				self.scoreTip.style.visibility = 'hidden';
				self.totalScore.innerHTML = '0';
				self.close();
				return false;
			});

			/*IOS 客户端关闭按钮*/ 
			if($globalInfo.ua.isIOS){
				addEvt(self.fxClose,'mousedown',function(){
					if($globalInfo.hasCommented){self.close();return}
					/*for(var i = 1; i < 6; i++){
						self.barArr[i].resetSpeBar();
					}*/
					self.scoreTip.style.visibility = 'hidden';
					self.totalScore.innerHTML = '0';
					self.close();
					return false;
				});
			}

			/*close*/
			/*addEvt(body,'mousedown',function(e){
				e = e || window.event;
				target = e.target || e.srcElement;
				if(!self.containsElement(self.fxBtn.parentNode,target)){
					self.close();
				}
			});*/

			addEvt(self.submit,'mousedown',function(){
				if($globalInfo.abBar.stopFXPF){alert('您已经提交过分项打分了');self.close();return}
				var canSubmit = true;
				for(var i = 1; i < 6; i++){	
					if(self.barArr[i].score == 0){
						self.scoreTip.style.visibility = 'visible';
						self.barArr[i].botBar.parentNode.parentNode.className = 'score_unabled clearfix';
						self.barArr[i].actived = false;
						canSubmit = false;
					}	
				}
				if(canSubmit){
					// hasCommented表示是否已经评论，stopFXPF表示是否已经提交分项打分
					var fxScore = self.fxTop;
					var story = self.barArr[1].score;
					var act = self.barArr[2].score;
					var director = self.barArr[3].score;
					var picture = self.barArr[4].score;
					var music = self.barArr[5].score;
					$globalInfo.score.story = story;
					$globalInfo.score.act = act;
					$globalInfo.score.director = director;
					$globalInfo.score.picture = picture;
					$globalInfo.score.music = music;
					if($globalInfo.hasCommented){
						if(!$globalInfo.abBar.stopFXPF){
							// 提交分项打分
							$.app.fxScore(ARTICLE_DATA.movieID,story,act,director,picture,music);
							self.changeTopFxScore();
							alert('分项评分提交成功');
						} else {
							alert('您已经提交过分项打分了');
						}
						self.close();
						$globalInfo.abBar.stopFXPF = true;
						return;
					}
					self.mBar.setSpeScore(self.toDecimal1(parseFloat(self.totalScore.innerHTML)));
					self.mBar.eventControl = {'move':false,'down':false};
					self.close();
				} else {
					return
				}

			});
		},
		scroeHasChanged: function(){
			var self = this;
			var totalNum = 0;
			for(var i = 1; i < 6; i++){	
				// self.fxTop.getElementsByTagName('EM')[i-1].innerHTML = self.formatScore(self.barArr[i].score);
				totalNum += parseInt(self.barArr[i].score);	
			}
			// console.log(totalNum);
			self.totalScore.innerHTML = self.toDecimal1(totalNum/5);
		},
		changeTopFxScore: function(){
			var self = this;
			var len = self.barArr.length;
			// 修改顶部分项打分结果
			for(var i = 1; i < len; i++){
				var nowScore = parseFloat(self.fxTop.getElementsByTagName('EM')[i-1].innerHTML);
				var newScore = self.toDecimal1((nowScore*parseInt(self.fx_usercount.innerHTML) + parseInt(self.barArr[i].score))/(parseInt(self.fx_usercount.innerHTML) + 1));
				if(newScore == 10.0){
					newScore = 10
				}
				self.fxTop.getElementsByTagName('EM')[i-1].innerHTML = newScore;
			}
		},
		fxHidSubmit: function(){
			var wrap = this.fxDet;
			var cont = this.fxCont;
			var bg = this.fxBg;
			this.scoreTip.style.display = 'none';
			this.fxDeal.style.display = 'none';
			cont.style.height = '260px';
			bg.style.height = '260px';
			wrap.style.height = '265px';
		},
		formatScore: function(num){
			if(parseInt(num) == 10){
				return 10;
			} else {
				return num + '.0';
			}
		},

		fxStyleChanged: function(){
			var self = this;
			if(!!self.totalScore.parentNode){
				self.totalScore.parentNode.innerHTML = '分项打分';
			}
			addClass(self.submit,' fx_score_submit');
		},

		nowResetBar: function(){
			var self = this;
			var scoreInfo = true;
			for(var i = 1; i < 6; i++){
				if(self.barArr[i].score == 0){
					self.barArr[i].botBar.parentNode.parentNode.className = 'score_undo clearfix';
					self.barArr[i].topBar.style.width = '0px';
					scoreInfo = false;
				}
			}
			if(scoreInfo) {
				self.scoreTip.style.visibility = 'hidden';
			}
		},


		containsElement: function(obj1, obj2) {
			while (obj2.nodeName != 'HTML') {
				if (obj2 == obj1) return true;
				obj2 = obj2.parentNode
			}
			return false
		},

		open: function(){
			//ios平台
			if($globalInfo.ua.isIOS){
				this.iPadPosAjt();
			} else {
				addClass(this.fxBtn,' fx_score_btn_actived');		
			}
			this.fxDet.style.display = 'block';
			this.status = 'open';
		},
		iPadPosAjt: function(){
			var fxBtn = this.fxBtn;
			var detDiv = this.fxDet
			var fxWrap = this.fxScoreWrap;
			var fxBg = this.fxBg;
			var fxClose = this.fxClose;
			var maskDiv = byId('__maskDiv__');
			var fxBtnxy = getXY(fxBtn);
			if(!maskDiv){
				var maskDiv = doc.createElement('DIV');
				maskDiv.className = 'maskDiv';
				maskDiv.id = '__maskDiv__';
				body.appendChild(maskDiv);
			}
			detDiv.parentNode.removeChild(detDiv);
			body.appendChild(detDiv);
			fxWrap.style.position = 'static';
			fxBg.style.display = 'none';
			fxClose.style.display = 'block';
			fxBtn.style.left = fxBtnxy[0] + 'px';
			fxBtn.style.top = fxBtnxy[1] + 'px';
			maskDiv.style.height = doc.body.scrollHeight + 'px';
			detDiv.style.cssText = 'z-index:20000;'
			this.setPos(detDiv);
		},
		setPos: function(obj){
			var bodyWid = document.documentElement.clientWidth || document.body.clientWidth;
			var bodyHei = document.documentElement.clientHeight || document.body.clientHeight;
			var bodyScrollTop = document.documentElement.scrollTop || document.body.scrollTop;
			// alert(bodyWid + '_' + bodyHei + ':' + obj.offsetWidth + '-' + obj.offsetHeight); 
			obj.style.left = parseInt((bodyWid - obj.offsetWidth)/2) + 'px';
			obj.style.top = (bodyScrollTop + parseInt((bodyHei - obj.offsetHeight)/2)) + 'px';
		},

		close: function(){
			var fxBtn = this.fxBtn;
			var maskDiv = byId('__maskDiv__');
			if(!!maskDiv){
				maskDiv.parentNode.removeChild(maskDiv);	
			}
			this.fxScoreWrap.style.position = 'relative';
			fxBtn.style.left = 'auto';
			fxBtn.style.right = '73px';
			fxBtn.style.top = '0px';
			removeClass(this.fxBtn,'fx_score_btn_actived');
			this.fxDet.style.display = 'none';
			this.status = 'close';
		},

		// 保留到小数点后一位
		toDecimal1: function(x) {  
	        var f = parseFloat(x);  
	        if (isNaN(f)) {  
	            return false;  
	        }  
	        var f = Math.round(x*10)/10;  
	        var s = f.toString();  
	        var rs = s.indexOf('.');  
	        if (rs < 0) {  
	            rs = s.length;  
	            s += '.';  
	        }  
	        while (s.length <= rs + 1) {  
	            s += '0';  
	        }  
	        return s;  
	    } 

	}
	$globalInfo.fxActive = new fxActive();
	return fxActive;

});
/*分项打分接口*/
SAB.register('app.fxScore',function($){
	var url = 'http://dafen.ent.sina.com.cn/score_subitems';
	return function(movie_id,story,act,director,picture,music){
		var param = {
			movie_id : movie_id,
			story : story,
			act : act,
			director : director,
			picture : picture,
			music : music,
			format : 'xml'
		};
		$.io.ijax.request(url,{
			//param:param,
			POST:param
		});
	}

});
/*一键关注*/
SAB.register('app.batchFollow',function($){
	var byId = $.dom.byId;
	var byClass = $.dom.byClass;
	var addClass = $.dom.addClass;
	var removeClass = $.dom.removeClass;
	var hasClass = $.dom.hasClass;
	var cusEvt = $.evt.custEvent;
	var addEvt = $.evt.addEvent;
	var _apis = Weibo.apis, Login = Weibo.Login, Widgets = Weibo.Widgets;

	var followURL = 'http://api.sina.com.cn/weibo/2/friendships/create_batch.json?source=' + ARTICLE_DATA.appKey;
	var batchFollow = function(wrap,trigger,gzbtn){
		this.wrap = byId(wrap);
		this.triggers = byClass(trigger,wrap);
		this.gzbtn = byId(gzbtn);
		this.init.apply(this,arguments);
	}

	batchFollow.prototype = {
		init: function(){
			var self = this;
			var triggers = self.triggers;
			var gzbtn = self.gzbtn;
			var len = triggers.length;
			for(var i = 0; i < len; i++){
				(function(i){
					addEvt(triggers[i],'mousedown',function(e){
						if(hasClass(triggers[i],'selected')){
							removeClass(triggers[i],'selected');
						} else {
							addClass(triggers[i],' selected');
						}
					})
				})(i);
			}
			addEvt(gzbtn,'mousedown',function(){
				var uids = [];
				for(var i=0; i < len; i++){
					(function(i){
						if(hasClass(triggers[i],'selected')){
							var uid = triggers[i].getAttribute('uid');
							uids.push(uid);
						}
					})(i);
				}
				if(uids.length == 0){
					alert('请选择关注对象')
				} else {
					self.followIt(uids);
				}
			});
		},

		followIt: function(uids){
			var self = this;
			var params = {
	            data: {uids: uids.join(',')},
	            onsuccess: function() {
	                // 关注成功的代码, 比如:
	                alert("关注成功");
	            },
	            onfailure: function(st) {
	                if (st && st.code == 20506) {
	                    // 关注成功的代码, 比如:
	                    alert("已关注！");
	                } else {
	                    // alert('关注失败');
	                }
	            }
	        };
	        // alert(params.data.uids);
	        // 判断登录
	        if(!Login.check()){
				// self.commentTip('error','请先登录再提交评论');
				//绑定登录后评论
				self.loginWithFollow = true;
				cusEvt.add($,'ce_login',function(e){
					if(e.data.action==='loginWithFollow'&&self.loginWithFollow){
				   	  	_apis.batchFollow(params);
				   	  	self.loginWithFollow = false;
					}
				},{action:'loginWithFollow'});
				//登录
				self.login();
			} else {
	        	_apis.batchFollow(params);
	        }
		},
		login: function(){
			if($globalInfo.isLogin){return;}
			var loginLayer = window.SINA_OUTLOGIN_LAYER;
			var bodyHeight = document.documentElement.clientHeight || document.body.clientHeight;
			var bodyScrollTop = document.documentElement.scrollTop || document.body.scrollTop;
			loginLayer.set('styles',{
				'marginTop': (bodyScrollTop + parseInt((bodyHeight - 208)/2)) + 'px'
			});
			loginLayer.show();
		}
	}

	return batchFollow;
});
SAB.register('app.comment',function($){
	var url = 'http://dafen.ent.sina.com.cn/submit_comment?format=json';
	return function(con,mid,toWeibo,videoUrl,rank,anonymous,spoiler,movieID,story,act,director,picture,music,callback){
		var param = {
			channel:$globalInfo.news.channel,
			newsid:$globalInfo.news.newsid,
			parent:mid,
			content:con,
			format:'js',
			ie:ARTICLE_DATA.encoding,
			oe:ARTICLE_DATA.encoding,
			ispost:toWeibo,
			movie_id:movieID,
			rank:rank,
			story:story,
			act:act,
			director:director,
			picture:picture,
			music:music,
			anonymous: anonymous,
			callback:callback,
			config: '&anonymous=' + anonymous + '&spoiler=' + spoiler,
			share_url:location.href.split('#')[0],
			video_url:ARTICLE_DATA.video_url||''
		};
		$.io.ijax.request(url,{
			//param:param,
			POST:param
		});
	}

});

SAB.register('job.cmntCustEvent',function($){
	var cusEvt = $.evt.custEvent;
	cusEvt.define($,'ce_cmntHtmlInit');
	cusEvt.define($,'ce_cmntLoadStart');
	cusEvt.define($,'ce_cmntLoadEnd');
	cusEvt.define($,'ce_cmntRenderStart');
	cusEvt.define($,'ce_cmntRenderEnd');
	cusEvt.define($,'ce_cmntFirstRenderEnd');
	cusEvt.define($,'ce_cmntFormReset');
	cusEvt.define($,'ce_cmntFormFix');
	cusEvt.define($,'ce_cmntSubmitEnd');

});SAB.register('job.cmntListToggle',function($){
	var toggle = function(){
			var Cmntlist = $.job.cmntList;
			var data = Cmntlist.data;
			var dom = Cmntlist.dom;
			//整个评论包括评论框
			var comment_wrap_dom = $.dom.byId('J_Comment_List_Frame');
			//只是评论列表
			var list_wrap_dom = $.dom.byId('J_Comment_List_Wrap');

			//是否要关闭整个评论
			if(data.news&&data.news.status=='N_CLOSE'){
				return;
			}else{
				comment_wrap_dom.style.display='';
			}
			//如果评论数为0不显示
			if(!data.cmntlist||data.cmntlist.length==0){
				return;
			}
			//某些频道不显示评论列表，比如国内，ARTICLE_DATA.hideCMNTList，默认为0不显示，为1显示
			var hideList = ARTICLE_DATA.hideCMNTList&&ARTICLE_DATA.hideCMNTList==1;
			if(hideList){
				return;
			}else{
				list_wrap_dom.style.display = '';
			}

	};
	var cusEvt = $.evt.custEvent;
	cusEvt.add($, 'ce_cmntRenderStart', toggle);
});SAB.register("app.formatTime", function($) {
    var monthSrt = '月',
        dayStr = '日',
        todayStr = '今天',
        secondStr = '秒前',
        minStr = '分钟前';
    return function(nDate, oDate) {
        var nYear = nDate.getFullYear(),
            oYear = oDate.getFullYear(),
            nMonth = nDate.getMonth() + 1,
            oMonth = oDate.getMonth() + 1,
            nDay = nDate.getDate(),
            oDay = oDate.getDate(),
            nHour = nDate.getHours(),
            oHour = oDate.getHours();
        oHour < 10 && (oHour = "0" + oHour);
        var oMin = oDate.getMinutes();
        oMin < 10 && (oMin = "0" + oMin);
        var dDate = nDate - oDate;
        dDate = dDate > 0 ? dDate : 0;
        dDate = dDate / 1e3;
        if(nYear != oYear) return oYear + "-" + oMonth + "-" + oDay + " " + oHour + ":" + oMin;
        if(nMonth != oMonth || nDay != oDay) return oMonth + monthSrt + oDay + dayStr + oHour + ":" + oMin;
        if(nHour != oHour && dDate > 3600) return todayStr + oHour + ":" + oMin;
        if(dDate < 51) {
            dDate = dDate < 1 ? 1 : dDate;
            return Math.floor((dDate - 1) / 10) + 1 + "0" + secondStr
        }
        return Math.floor(dDate / 60 + 1) + minStr
    }
});

SAB.register("app.getTimeStr", function($) {
    var formatTime =$.app.formatTime;
    return function(time,clz){
        clz = clz||'';
        if(time){
            //发布时间 tDate1,如果time为空
            var tDate1 = new Date;
            time = time.replace(/-/g,'/');
            var timeStr = Date.parse(time);
            tDate1.setTime(parseInt(timeStr, 10));
            //此时时间 tDate
            var tDate = new Date;
            tDate.setTime(tDate.getTime());

            var formatStr = formatTime(tDate,tDate1);
        }else{
            timeStr = Date.parse(new Date);
            var formatStr='1秒前'
        }

        return '<span class="'+clz+' J_Comment_Time" date="'+timeStr+'">'+ formatStr+'</span>';
    };

});
SAB.register("app.updateTime", function($) {
    var formatFeedTime = $.app.formatTime,
        cusEvt = $.evt.custEvent;
        cDate =0,
        d = function(wrap) {
            var dateDom = $.dom.byClass('J_Comment_Time', wrap),
                tDate = new Date;
            tDate.setTime(tDate.getTime() - cDate);
            var g;
            for(var h = 0; h < dateDom.length; h++) {
                var item = dateDom[h],
                    dateStr = item.getAttribute("date");
                if(!/^\s*\d+\s*$/.test(dateStr)) continue;
                var tDate1 = new Date;
                tDate1.setTime(parseInt(dateStr, 10));
                item.innerHTML = formatFeedTime(tDate, tDate1);
                g == undefined && (g = tDate.getTime() - tDate1.getTime() < 6e4)
            }
            return g
        };
    return function(wrap) {
            // var TIME = 1e4;
            var TIME = 1e3;
            var upDateTimer, setUpDateTimer = function(t) {
                    clearTimeout(upDateTimer);
                    upDateTimer = setTimeout(function() {
                        d(wrap) ? setUpDateTimer(TIME) : setUpDateTimer(6e4)
                    }, t)
                },
                g = function() {
                    setUpDateTimer(TIME);
                };
            setUpDateTimer(TIME);
            cusEvt.add($, 'ce_cmntFirstRenderEnd', g);
            var UT = {
                destroy: function() {
                    clearTimeout(upDateTimer);
                    cusEvt.remove($, 'ce_cmntFirstRenderEnd', g);
                    UT = wrap = upDateTimer = setUpDateTimer = g = null
                }
            };
            return UT
    }
});

SAB.register("job.cmntShare", function($) {

        //获取窗口地址
         var getUrlData = function(o,newWin){
            // 分享【新浪微博用户 @微博号】 对【标题】的#精彩新闻评论# 【评论地址】
            ///从评论列表中找出当前评论数据
            var s = screen;
            var d = document;
            var e = encodeURIComponent;
            var news = $globalInfo.news;
            var newsid = news.newsid;
            var channel = news.channel;
            var title = news.title;
            var img = ARTICLE_DATA.pic_url&&ARTICLE_DATA.pic_url.length>0?ARTICLE_DATA.pic_url[0]:'';

            // var show_name = '';
            var show_nick = '';
            var oData = o.data;
            var mid = oData.mid;
            var site = oData.site;
            var setWinUrl = function(){
                // if( usertype == "wb" ){
                //     show_name = '新浪微博用户';
                //         // var name_1 = config.match(/wb_screen_name=([^&]*)/i);
                //         var name_1 = wb_screen_name;
                //         if (name_1){
                //                 show_nick = '@'+name_1;
                //         }
                // }else if(usertype == "wap"){
                //     show_name="新浪"+area+"手机用户";
                //     if(nick!='手机用户')
                //          show_nick = nick;
                // }else {
                //     show_name = "新浪"+area+"网友";
                //     if(nick)
                //         show_nick = nick;
                // }
                if(wb_screen_name){
                    show_nick = '@'+wb_screen_name;
                }else{
                    if(usertype == "wap"){
                        show_nick="新浪手机用户";
                        if(nick!='手机用户')
                             show_nick += nick;
                    }else {
                        show_nick = "新浪网友";
                        if(nick)
                            show_nick += nick;
                    }
                }
                // var cmntUrl ='http://comment5.news.sina.com.cn/comment/skin/default.html?channel='+channel+'&newsid='+newsid;
                var link = location.href;
                title = title.replace(/<.*?>/ig, "");
                // title = "分享"+show_name+" "+show_nick+ " 对《"+title+"》的#精彩新闻评论#";
                // soureurl已经带链接
                title = '#精彩评论#【'+title+'】'+show_nick+'：'+cmnt;
                var API = {
                    'sina':{
                        base:'http://v.t.sina.com.cn/share/share.php?',
                        param:['url=',e(link),'&title=',e(title),'&source=',e('新浪娱乐'),'&sourceUrl=',e('http://news.sina.com.cn/hotnews/'),'&content=','utf-8','&pic=',e(img),'&appkey=','445563689'].join('')
                    },
                    'tencent':{
                        base:'http://share.v.t.qq.com/index.php?',
                        param:['c=','share','&a=','index','&url=',e(link),'&title=',e(title),'&content=','gb2312','&pic=',e(img),'&appkey=','dcba10cb2d574a48a16f24c9b6af610c','&assname=','${RALATEUID}'].join('')
                    }

                };

                if(newWin) {
                    newWin.location.href = [API[site].base,API[site].param].join('')
                }
            };
            //获取评论截图
            var getImgSrc = function(o){
                    var cbName = 'iJax'+Date.parse(new Date());
                    var url = 'http://comment5.news.sina.com.cn/image';
                    window[cbName]=function(m){
                        if(typeof m == 'string'){
                            m = eval('('+m+')');
                        }
                        var cmntImg = m.result.image||'';
                        //新闻图片+评论截图
                        img = img?img+'||'+cmntImg:cmntImg;
                        if(/Firefox/.test(navigator.userAgent)) {
                            setTimeout(function(){
                                setWinUrl();
                            }, 30);
                        } else {
                            setWinUrl();
                        }
                    };
                    if(!$globalInfo.ua.isFF){
                        var param = {
                            channel:news.channel,
                            newsid:news.newsid,
                            mid:mid,
                            format:'js',
                            callback:cbName
                        };
                        $.io.ijax.request(url,{
                            POST:param
                        });
                    }else{
                        var param = 'channel='+news.channel+'&newsid='+news.newsid+'&mid='+mid;
                        var Sender = new $.io.html5Ijax({
                                proxyUrl : 'http://comment5.news.sina.com.cn/comment/postmsg.html'
                            });
                        Sender.send({
                            url: url,
                            data: param,
                            onsuccess: window[cbName],
                            onfailure: function(){}
                        });
                    }
            };
            if(!oData.type){
                var cList= $.job.cmntList.cList;
                var curCmntlist = {};
                for (var i = cList.length - 1; i >= 0; i--) {
                    var item = cList[i];
                    if(item.mid==mid){
                        curCmntlist = item;
                    }
                };
                var usertype = curCmntlist.usertype;
                var nick = curCmntlist.nick;
                // var area = curCmntlist.area;
                var wb_screen_name = oData.wb_screen_name;
                var cmnt = curCmntlist.content;
            }else{
                var usertype = oData.usertype;
                var nick = oData.nick;
                // var area = oData.area;
                var wb_screen_name = oData.wb_screen_name;
                var cmnt = oData.con;
            }
            //如果不带回复评论或者评论不超过80字直接只带新闻配图，否则还带评论截图,楼里回复（oData.type == 'reply'）
            var maxCmntLen = 80;
            if(oData.hasReply||$.str.byteLength(cmnt)>maxCmntLen*2){
                cmnt = $.app.strLeft(cmnt,maxCmntLen*2);
                // 获取截图后，在回调里设置窗口地址
                getImgSrc();
            }else{
                //直接设置窗口地址
                setWinUrl();
            }

        };

    //绑定点击分享事件
    var bindShare = function(){
	    	var byId = $.dom.byId;
	    	var wrap = byId('J_Comment_List_Frame');
    		if(!wrap){
    			return;
    		}
    		//事件委派
    		var dldEvt = $.evt.delegatedEvent;
    		var dldEvt_share= dldEvt(wrap);
    		//点击分享
    		dldEvt_share.add('share','click',function(o){
    			var ele = o.el;
                //点击时马上打开窗口，防止被拦截
                var newWin = window.open('','mb',['toolbar=0,status=0,resizable=1,width=440,height=430,left=',(screen.width-440)/2,',top=',(screen.height-430)/2].join(''));
                //获取url数据（用来填充窗口地址）
                getUrlData(o,newWin);
    		});
    	}
    //评论渲染完成后绑定事件
    var cusEvt = $.evt.custEvent;
    cusEvt.add($, 'ce_cmntFirstRenderEnd', bindShare);
});
SAB.register("job.cmntShareHover", function($) {
    var bindShareHover = function(){
        var byId = $.dom.byId;
        var byClass = $.dom.byClass;
        var addClass = $.dom.addClass;
        var removeClass = $.dom.removeClass;
        var wrap = byId('J_Comment_List_Frame');
        if(!wrap){
            return;
        }
        //事件委派
        var dldEvt = $.evt.delegatedEvent;
        var dldEvt_hover= dldEvt(wrap);

        var activeBtn=null;
        var timer;
        var HIDETIME = 2000;
        var ACTIVECLZ = 'cmnt-share-tirgger-active';
        //显示，设置当前激活按钮，设置样式
        var show = function(ele,btn){
            if(typeof jQuery != 'undefined'){
                jQuery(btn).show().animate({right: "-68px"}, "fast");
            }else{
                btn.style.cssText =';right:-68px;display:block;';
            }
            activeBtn = ele;
            addClass(ele,ACTIVECLZ);
        };
        //隐藏，设置当前激活按钮为null，设置样式
        var hide = function(ele,btn){
            if(typeof jQuery != 'undefined'){
                jQuery(btn).animate({opacity:'hide',right: "-14px"}, "fast");
            }else{
                btn.style.cssText =';right:-14px;display:none;';
            }
            if(ele){
                removeClass(ele,ACTIVECLZ);
                activeBtn = null;
            }
        };
        //获取“新浪，腾讯”按钮wrap
        var getBtn = function(o){
            return byClass('J_Comment_Share_Btns',o.parentNode.parentNode)[0]
        };

        //点击分享
        dldEvt_hover.add('shareHover','mouseover',function(o){
            clearTimeout(timer);
            var ele = o.el;
            var btn = getBtn(ele);
            //显示前，先隐藏之前打开的按钮
            if(activeBtn&&activeBtn!=ele){
                hide(activeBtn,getBtn(activeBtn));
            }
            show(ele,btn);

        });
        dldEvt_hover.add('shareHover','mouseout',function(o){
            var ele = o.el;
            var btn = getBtn(ele);
            var evt = o.evt;
            //
            // if(btn==evt.toElement){
                btn.onmouseout=function(){
                    timer = setTimeout(function(){
                        hide(ele,btn);
                    },HIDETIME);
                };
                btn.onmouseover=function(){
                    clearTimeout(timer);
                };
                // return;
            // }
            //延时隐藏
            timer = setTimeout(function(){
                hide(ele,btn);
            },HIDETIME);
        });

    };

    var cusEvt = $.evt.custEvent;
    cusEvt.add($, 'ce_cmntFirstRenderEnd', bindShareHover);

});
/**
 * 是否显示列表标题
 */
SAB.register('job.cmntTitleToggle',function($){
	var toggle = function(){
		var byId = $.dom.byId;
		var data = $.job.cmntList.data;
		if(!data){
			return;
		}
		//显示彩票提示
		if(data.news.column=='彩票'){
			byId('J_Comment_CP_Tip').style.display = 'block';
		}
		if(data.hot_list&&data.hot_list.length!=0){
			byId('J_Comment_Wrap_Hot').style.display = '';
		}
		if(data.cmntlist){
			byId('J_Comment_Wrap_Latest').style.display = '';
		}
	};
	var cusEvt = $.evt.custEvent;
	cusEvt.add($, 'ce_cmntFirstRenderEnd', toggle);
});SAB.register("job.cmntReload", function($) {
    var bindShareHover = function(){
        var CmntList = $.job.cmntList;
        var wrap = CmntList.dom.wrap;
        if(!wrap){
            return;
        }
        //事件委派
        var dldEvt = $.evt.delegatedEvent;
        var dldEvt_reload= dldEvt(wrap);

        dldEvt_reload.add('reload','click',function(o){
            if(CmntList.loading){
                return;
            }
            var type = o.data.type;

           if(type){
                var list = $.dom.byId('J_Comment_List_'+type);
                var listPar = list.parentNode;
                var temp = [];
                temp.push('<div class="comment_item comment_loading">');
                  temp.push('<span>');
                    temp.push('<img src="http://i3.sinaimg.cn/ent/deco/2012/0912/images/indicator_24.gif" height="24" width="24" alt="" style="vertical-align:middle;">评论加载中，请稍候...</span>');
                temp.push('</div>');
                var fragment = $.C('div');
                fragment.innerHTML = temp.join('');
                listPar.insertBefore(fragment,list);

                var removeLoding = function(){
                    listPar.removeChild(fragment);
                    cusEvt.remove($, 'ce_cmntRenderEnd', removeLoding);
                };
                cusEvt.add($, 'ce_cmntRenderEnd', removeLoding);
           }
           CmntList.setType(type.toLowerCase());
           CmntList.options.param.page = 1;
           CmntList.getData();
        });

    };
    var cusEvt = $.evt.custEvent;

    cusEvt.add($, 'ce_cmntFirstRenderEnd', bindShareHover);

});
/*背景闪烁 by cq*/
SAB.register('app.bgFlicker',function($){
	var bgFlicker = function(obj,callback,blikNum,range,durSpace){
		this.blikNum = blikNum || 2;//闪烁次数，为奇数
		this.range = range || [200,255];//色彩变化范围
		this.blikcounts = 0;//闪烁计数器
		this.durSpace = durSpace || 7;//闪烁速度，越小越快
		this.callback = callback || function(){};
		this.obj = obj || document.getElementsByTagName('BODY')[0];
		this._inTime = {};
		this._outTime = {}
		this.init.apply(this,arguments);
	}

	bgFlicker.prototype = {
		init: function(){
			this.fadeIn(this.range[1]);
		},

		fadeIn: function(where){
			var self = this;
			clearTimeout(self._outTime);
			if (where >= self.range[0]) {
				self.obj.style.backgroundColor="rgb(255," + where + "," + where + ")";   
				where -= 1;
				self._inTime = setTimeout(function(){
					self.fadeIn(where)
				}, self.durSpace);
			} else {
				self.blikcounts++;
				self._outTime = setTimeout(function(){
					self.fadeOut(self.range[0]);
				}, self.durSpace);
			}
		},

		fadeOut: function(where){
			var self = this;
			clearTimeout(self._inTime);
			if (where <= self.range[1]) {
				self.obj.style.backgroundColor="rgb(255," + where + "," + where + ")";   
				where += 1;
				self._outTime = setTimeout(function(){
					self.fadeOut(where)
				}, self.durSpace);
			} else {
				self.blikcounts++;
				if(self.blikcounts > self.blikNum){
					self.callback.apply(self,arguments);
					return false;
				}
				self._inTime = setTimeout(function(){
					self.fadeIn(self.range[1]);
				}, self.durSpace);
			}
		},
	}
	return bgFlicker;
});
SAB.register('job.cmntForm',function($){
	var byId     = $.dom.byId;
	var byClass  = $.dom.byClass;
	var addClass = $.dom.addClass;
	var removeClass =$.dom.removeClass;
	var addEvt   = $.evt.addEvent;
	var cusEvt   = $.evt.custEvent;
	var encodeHTML = $.str.encodeHTML;
	var trim = $.str.trim;

	//格式化时间
	var formatTime = $.app.formatTime;
	var updateTime = $.app.updateTime;
	var getTimeStr = $.app.getTimeStr;

	// cusEvt.add($,'ce_cmntSubmitEnd');
	var cmntForm = function (id) {
		this.map = {
			'0' : '未打分',
			'1' : '烂到极点',
			'2' : '浪费时间',
			'3' : '拙劣可笑',
			'4' : '一般难看',
			'5' : '平庸之作',
			'6' : '尚可一看',
			'7' : '不乏亮点',
			'8' : '非常优秀',
			'9' : '接近完美',
			'10' : '经典之作'
		};
		this.init(id);
	};
	cmntForm.prototype = {	
		init:function(id){
			var self = this;
			self.commenting = false;
			self.getMid(id);
			self.getDom(id);
		},
		getMid:function(id){
			var mid = id.replace('J_Comment_Form_','').split('_')[0];
			this.mid = mid;
		},
		getDom:function(id){
			var self = this;
			var dom =$.dom;
			var wrap = byId(id);
			if(!wrap){
				return;
			}
			/**
			 * 用到的相关dom节点
			 * wrap,大容器，包括评论区和用户信息区
			 * form,评论表单
			 * content,评论内容
			 * submit,评论提交按钮
			 * commentTip,评论提示
			 * user,用户输入框
			 * psw,密码输入框
			 * login,登录按钮
			 * logout,退出链接
			 * loginTip,登录提示
			 */
			this.dom = {
				wrap:wrap,
				form:byClass('J_Comment_Form',wrap)[0],
				content:byClass('J_Comment_Content',wrap)[0],
				submit:byClass('J_Comment_Submit',wrap)[0],
				commentTip:byClass('J_Comment_Tip',wrap)[0],
				// user:byClass('J_Login_User',wrap)[0],
				// psw:byClass('J_Login_Psw',wrap)[0],
				// login:byClass('J_Login_Submit',wrap)[0],
				// logout:byClass('J_Login_Logout',wrap)[0],
				// loginTip:byClass('J_Login_Tip',wrap)[0],
				rank: byClass('J_Main_Bar_Score',wrap)[0],
				toWeiboWrap:byClass('J_Comment_ToWeibo_Wrap',wrap)[0],
				toWeibo:byClass('J_Comment_ToWeibo',wrap)[0],
				anonymousWrap:byClass('J_Comment_Anonymous_Wrap',wrap)[0],
				anonymous:byClass('J_Comment_Anonymous',wrap)[0],
				spoilerWrap:byClass('J_Comment_Spoiler_Wrap',wrap)[0],
				spoiler:byClass('J_Comment_Spoiler',wrap)[0],
				totalBar:byId('J_Total_SBar'),
				totalSco:byId('J_Total_Sco'),
				totalMen:byId('J_Join_Mem'),
				fx_usercount:byId('J_Fx_UserCount'),
				fxTop:byId('J_Fx_Top'),
				countTip:byId('J_Count_Tip')
			};
			self.bindEvent();
		},
		bindEvent:function(){
			var self     = this;
			var dom      = self.dom;
			var content = trim(dom.content.value);
			var countCheck = function(){
				var content = dom.content.value;
				var num = Math.ceil($.util.byteLength(content)/2);
				if(num > 140){
					dom.countTip.parentNode.style.color = '#f00';
					dom.countTip.innerHTML = '已经超出' + (num - 140);
				} else {
					dom.countTip.parentNode.style.color = '#999';
					dom.countTip.innerHTML = num;
				}
			}
			// 检查评论框字数
			countCheck();
			var submitComment = function(){
				self.commenting = true;
				/*产品不要登录按钮，评论前登录*/
				// 未评分不允许提交
				if($globalInfo.abBar.mBar.score == 0){
					$globalInfo.abBar.mBar.setUnabled();
					return;
				}
				//未填写内容
				var content = trim(dom.content.value);
				var emptyTip = dom.content.getAttribute('placeholder');
				if(content==''||content==emptyTip){
					self.commentTip('error',emptyTip);
					dom.content.focus();
					return;
				}
				//超过140字不允许提交
				if(Math.ceil($.util.byteLength(content)/2)>140){
					new $.app.bgFlicker(dom.content);
					dom.content.focus();
					return;
				}
				
				//$globalInfo.isLogin为通过自定义登录成功事件设置的，比较慢，有用户退出还可为true的情况
				//if(!$globalInfo.isLogin){
				if(!sinaSSOController.get51UCCookie()){
					// self.commentTip('error','请先登录再提交评论');
					//绑定登录后评论
					self.loginWithComment = true;
					cusEvt.add($,'ce_login',function(e){
						if(e.data.action==='loginWithComment'&&self.loginWithComment && $globalInfo.isWeiboLogin){
					   	  	self.comment();
					   	  	self.commenting = false;
					   	  	self.loginWithComment = false;
						} else if(e.data.action==='loginWithComment'&&self.loginWithComment) {
							window.open('http://www.weibo.com','');
							self.comment();
							self.commenting = false;
					   	  	self.loginWithComment = false;
						}
					},{action:'loginWithComment'});
					//登录
					//suda统计点击
					try{
						_S_uaTrack("entcomment", "login");
					}catch(e){

					}
					self.login();
				}else{
					//已经登录马上评论
					self.comment();
				}
				/*/产品不要登录按钮，评论前登录*/
			};
			var addPropertyChangeEvent = function (obj,fn) {
			  if(window.ActiveXObject){
			      obj.onpropertychange = fn;
			  }else{
			      obj.addEventListener("input",fn,false);
			  }
			}
			
			// 绑定转发微博吧，匿名，剧透事件
			var bindSelectEvt = function(w,tar,ch_Attr,ch_class){
				addEvt(w,'click',function(o){
					if($.dom.hasClass(tar,ch_class)){
						$.dom.removeClass(tar,ch_class);
						tar.setAttribute(ch_Attr,0);
					}else{
						$.dom.addClass(tar,ch_class);
						tar.setAttribute(ch_Attr,1);
					}
					//suda统计点击
					try{
						_S_uaTrack("entcomment", ch_Attr);
					}catch(e){

					}

				});
			}
			// 是否转发到微博
			bindSelectEvt(dom.toWeiboWrap,dom.toWeibo,'toweibo','to_mb_selected');
			// 是否匿名
			bindSelectEvt(dom.anonymousWrap,dom.anonymous,'anonymous','to_mb_selected');
			// 是否剧透
			bindSelectEvt(dom.spoilerWrap,dom.spoiler,'spoiler','to_mb_selected');
			
			//评论
			addEvt(dom.submit,'click',function(){
				submitComment();
			});
			addEvt(dom.content,'keydown',function(e){
				e = e || window.event;
				if (e.keyCode == 13 && e.ctrlKey) {
				    submitComment();
				}
			});
			addEvt(dom.content,'keyup',function(e){
				countCheck();
				setTimeout(function(){
					self.toggleSubmitBtn();
				},200);
			});
			addEvt(dom.wrap,'click',function(e){
				setTimeout(function(){
					self.toggleSubmitBtn();
				},200);
			});
			addEvt(dom.content,'focus',function(e){
				setTimeout(function(){
					self.toggleSubmitBtn();
				},200);
			});
			addPropertyChangeEvent(dom.content,function(){
				self.toggleSubmitBtn();
			});
			//placeholder
			var placeholders = [dom.content];
			$.app.placeholder(placeholders);

			return self;

		},
		toggleSubmitBtn:function(){
			var self = this;
			var dom = self.dom;
			var content = dom.content;
			var submit = dom.submit;
			var cont = trim(content.value);
			var emptyTip = content.getAttribute('placeholder');
			var disableClz = 'post_inline_comment_disbled';
			if(cont==''||cont==emptyTip){
				addClass(submit,disableClz);
			}else{
				removeClass(submit,disableClz);
			}
		},
		login:function(){
			if($globalInfo.isLogin){return;}
			var self = this;
			var dom = self.dom;
			var loginLayer = window.SINA_OUTLOGIN_LAYER;
			var bodyHeight = document.documentElement.clientHeight || document.body.clientHeight;
			var bodyScrollTop = document.documentElement.scrollTop || document.body.scrollTop;
			loginLayer.set('styles',{
				'marginTop': (bodyScrollTop + parseInt((bodyHeight - 208)/2)) + 'px'
			});
			loginLayer.show();

		},
		loginError:function(e){
			var dom = e.data;
			dom.loginTip.innerHTML = '<span class="notice">用户名/密码错误</span>';
			dom.loginTip.style.display = '';
			setTimeout(function(){
				if(jQuery){
					jQuery(dom.loginTip).fadeOut('slow');
				}else{
					dom.loginTip.style.display = 'none';
				}
			},3000);
		},
		comment:function(){
			if($globalInfo.hasCommented){
				alert('对不起,您已经评论过了');
				return
			}
			var self = this;
			var dom = self.dom;
			//评论内容
			var content = trim(dom.content.value);
			content = content.replace(/\r\n/g,'<br/>');
			content = content.replace(/\n/g,'<br/>');  
			content = content.replace(/\r/g,"<br />");  
			content = content.replace(/\t/g,'!@');
			/*var regExp = new RegExp('','g');
			content = content.replace(regExp , '&nbsp');*/
			//是否转发到微博，转发到微博附加原文链接，如果有视频链接附加视频链接
			var toweibo = dom.toWeibo.getAttribute('toweibo');
			var anonymous = dom.anonymous.getAttribute('anonymous');
			var spoiler = dom.spoiler.getAttribute('spoiler');
			var rank = dom.rank.innerHTML;
			var story = $globalInfo.score.story;
			var act = $globalInfo.score.act;
			var director = $globalInfo.score.director;
			var picture = $globalInfo.score.picture;
			var music = $globalInfo.score.music;
			var movieID = ARTICLE_DATA.movieID;
			var totalScore = story+act+director+picture+music;
			if(totalScore != 0){
				$globalInfo.abBar.stopFXPF = true;
				changeAllFxBar();
			}
			function changeTopBar(){
				var tip = '';
				var _topBar = dom.totalBar;
				var _botBar = _topBar.getElementsByTagName('*')[0];

                var score=0;
                if(!!parseFloat(dom.totalSco.innerHTML))
                score = dom.totalSco.innerHTML;

				var totalRank = parseFloat(score)*parseInt(dom.totalMen.innerHTML) + parseFloat(rank);
				var tmpScore = toDecimal1(totalRank/(parseInt(dom.totalMen.innerHTML) + 1));
				dom.totalMen.innerHTML = parseInt(dom.totalMen.innerHTML) + 1;
				dom.totalSco.innerHTML = tmpScore;
				_botBar.style.width = (_topBar.offsetWidth * tmpScore/10) + 'px';
			}
			// 保留到小数点后一位
			function toDecimal1(x) {  
		        var f = parseFloat(x);  
		        if (isNaN(f)) {  
		            return false;  
		        }  
		        var f = Math.round(x*10)/10;  
		        var s = f.toString();  
		        var rs = s.indexOf('.');  
		        if (rs < 0) {  
		            rs = s.length;  
		            s += '.';  
		        }  
		        while (s.length <= rs + 1) {  
		            s += '0';  
		        }  
		        return s;  
		    } 
			function changeAllFxBar(){
				var joinmens = parseInt(dom.fx_usercount.innerHTML);
				var tmpStory = parseInt(dom.fxTop.getElementsByTagName('EM')[0].innerHTML);
				var tmpAct = parseInt(dom.fxTop.getElementsByTagName('EM')[1].innerHTML);
				var tmpDirector = parseInt(dom.fxTop.getElementsByTagName('EM')[2].innerHTML);
				var tmpPicture = parseInt(dom.fxTop.getElementsByTagName('EM')[3].innerHTML);
				var tmpMusic = parseInt(dom.fxTop.getElementsByTagName('EM')[4].innerHTML);
				dom.fxTop.getElementsByTagName('EM')[0].innerHTML = toDecimal1((tmpStory*joinmens + parseInt(story))/(joinmens+1));
				dom.fxTop.getElementsByTagName('EM')[1].innerHTML = toDecimal1((tmpAct*joinmens + parseInt(act))/(joinmens+1));
				dom.fxTop.getElementsByTagName('EM')[2].innerHTML = toDecimal1((tmpDirector*joinmens + parseInt(director))/(joinmens+1));
				dom.fxTop.getElementsByTagName('EM')[3].innerHTML = toDecimal1((tmpPicture*joinmens + parseInt(picture))/(joinmens+1));
				dom.fxTop.getElementsByTagName('EM')[4].innerHTML = toDecimal1((tmpMusic*joinmens + parseInt(music))/(joinmens+1));
			}
			//评论接口
			$.app.comment(content,self.mid,toweibo,'',rank,anonymous,spoiler,movieID,story,act,director,picture,music,'onsuccess');
			// 修改总评分
			changeTopBar();
			window['onsuccess'] = function(data){
				if(data){
					// 评论标识
					$globalInfo.hasCommented = true;
					//评论后动作，模拟已经评论，盖楼
					self.commented(content,toweibo);
				} else {
					alert('您已经评论过了');
				}
			}
			//suda统计点击
			try{
				_S_uaTrack("entcomment", "comment");
			}catch(e){

			}
		},
		commentTip:function(type,txt,callback){
			//succ,eror,tip
			var tip = this.dom.commentTip;
			tip.innerHTML = '<p class="post_tip_'+type+'">'+txt+'</p>';
			tip.style.display = '';
			setTimeout(function(){
				if(jQuery){
					jQuery(tip).fadeOut('slow',callback);
				}else{
					tip.style.display = 'none';
					callback.apply(this,arguments);
				}
			},1000);

		},

		commented:function(con,toweibo){
			var self = this;
			var mid = self.mid;
			var dom = self.dom;
			var anonymous = dom.anonymous.getAttribute('anonymous');
			var spoiler = dom.spoiler.getAttribute('spoiler');
			var rank = dom.rank.innerHTML;
			var spoilertxt;
			spoiler == 1 ? spoilertxt = '（该微评有剧透）' : spoilertxt = '';
			//清空评论框
			dom.content.value='';
			self.toggleSubmitBtn();
			// 提交后总评分不可修改
			$globalInfo.abBar.mBar.eventControl={'move':false,'down':false}
			var WBUURL = 'http://weibo.com/u/';
			//用户类型css类
			var typeClz = $globalInfo.isWeiboLogin? 't_weibo':'';
			//乔敏建议不要审核提示
			//var POSTING_TIP = '<p class="comment_posting">评论成功，审核中...</p><p>';
			var POSTING_TIP = '';
			//var POSTING_TIP = '';

			/**
			 * 获取微博用户的链接或者新浪用户昵称
			 * @return {[type]} [description]
			 */
			function get_user_lnk(){
				var userLnk;
				if(anonymous == 1){userLnk = '匿名用户'}
				else{
					userLnk = $globalInfo.isWeiboLogin?
						'<a href="'+WBUURL+$globalInfo.uid+'" target="_blank">'+$globalInfo.weiboName+'</a>':
						$globalInfo.sinaNick;
				}
				return userLnk;
			}
			function get_user_face(){
				var face = 'http://i3.sinaimg.cn/dy/deco/2012/1018/sina_comment_defaultface.png';
				var link = 'http://login.sina.com.cn/member/my.php';
				if(anonymous == 1){return '<img src="'+face+'"/>'}
				var name = $globalInfo.sinaNick;
				if($globalInfo.isWeiboLogin){
					face = $globalInfo.profile_image_url;
					link = 'http://weibo.com/';
					name = $globalInfo.weiboName;
				}
				return '<a href="'+link+'" title="'+name+'" target="_blank"><img src="'+face+'"/></a>';
			}
			function getCParent(ele,clz){
				var par = ele.parentNode;
				if($.dom.hasClass(par,clz)){
					return par;
				}else{
					return getCParent(par,clz);
				}
			}

			// 评论结束后隐藏文本框显示评论后样式
			function hasCommentedArea(txt){
				self.commentTip('succ','提交成功',function(){
					var prev = $.dom.byId('J_Comment_Prev');
					var after = $.dom.byId('J_Comment_After');
					prev.style.display = 'none';
					after.style.display = 'block';
					$.dom.byClass('J_Commnet_Atxt',after)[0].innerHTML = txt;
				});		
			}

			//type有三种，回复楼内，回复楼外，直接评论
			var type = self.dom.wrap.getAttribute('cmnt-type');
			if(type=='outerReply'){
				//整条评论容器

				var comment_item = byId('J_Comment_Item-'+self.mid);
				//当前评论信息容器
				var info_wrap = byClass('J_Comment_Info',comment_item)[0];
				//评论列表容器
				var replylist_wrap = byClass('J_Comment_Reply',comment_item)[0];
				//当前评论内容容器
				var content_wrap = byClass('J_Comment_Txt',comment_item)[0];
				//头像容器
				var face_wrap = byClass('J_Comment_Face',comment_item)[0];
				//原有被回复评论html
				var html = replylist_wrap.innerHTML;
				//从评论列表中找出当前评论数据
				var cList= $.job.cmntList.cList;
				var curCmntlist = {};
				for (var i = cList.length - 1; i >= 0; i--) {
					var item = cList[i];
					if(item.mid==mid){
						curCmntlist = item;
					}
				};
				//计算出新数层数（被回复数）
				var reply_length = byClass('orig_index',replylist_wrap).length+1;
				var area = (curCmntlist.usertype == 'wb'||curCmntlist.area=='')?'&nbsp;':'['+curCmntlist.area+']';
				//当前评论转为被回复评论
				var tempHtml = '<span class="orig_index">'+reply_length+'</span>'
	             	   +'<div class="orig_user">'+$.job.cmntList.get_user_lnk(curCmntlist)+'<span class="orig_area">'+area+'</span></div>'
					   +'<div class="orig_content">'+$.job.cmntList.filterEmotionIco(encodeHTML(curCmntlist.content))+'</div>'
					   +'<div class="orig_reply"  style="visibility: ;"><div class="reply">'+getTimeStr(curCmntlist.time,'datetime')+'<span class="replay-right"><a action-type="vote" action-data="mid='+curCmntlist.mid+'" href="javascript:;" voted="false" class="comment_ding_link" title="支持"><span>支持<em>('+curCmntlist.agree+')</em></span></a> <a action-type="reply" action-data="mid='+curCmntlist.mid+'&type=innerReply" href="javascript:;" poped="false" class="comment_reply_link" title="回复">回复</a> '
					   +'<a href="javascript:;" action-type="shareHover" class="cmnt-share-tirgger" title="分享"><em>分享</em></a></span>'
		           +'<span class="cmnt-share-btns J_Comment_Share_Btns"><a class="cmnt-share-btn-sina" href="javascript:;" action-type="share" action-data="mid='+curCmntlist.mid+'&site=sina">新浪</a><a class="cmnt-share-btn-qq" href="javascript:;"  action-type="share" action-data="mid='+curCmntlist.mid+'&site=tencent">腾讯</a></span>'
					   +'</div></div>';
				html = '<div class="comment_orig_content"><div class="orig_cont clearfix">' +html+tempHtml +'</div></div>';

				replylist_wrap.innerHTML = html;
				//当前回复内容
				content_wrap.innerHTML = POSTING_TIP+$.job.cmntList.filterEmotionIco(encodeHTML(con))+'</p><div class="reply">'+getTimeStr('','datetime')+'</div>';
				//当前回复的信息，用户名，用户类型，时间，地区没有
				info_wrap.innerHTML = '<div class="t_info"> <span class="t_username '+typeClz+'">'+get_user_lnk()+'</span><span class="t_area">&nbsp;</span></div>'
         	 +'';
         	 	face_wrap.innerHTML = get_user_face();
         	 	//回复完毕后，隐藏发布框
         	 	self.dom.wrap.style.display = 'none';

			}else if(type=='comment'){
				var weiboTip = toweibo==1?'并转发至微博':'';

				//新评论内容
				var tempHtml = '<div class="comment_item"><div class="comment_item_cont clearfix"><div class="J_Comment_Face t_face">'
	         	+get_user_face()+'</div><div class="t_content"><div class="J_Comment_Info"><div class="t_info"><span class="t_username '+typeClz+'">'+get_user_lnk()+'</span><span class="t_area">&nbsp;</span><span class="t_pf">评分:<em class="cyellow score">' + rank + '</em>分<em class="cyellow">' + spoilertxt + '</em></span></div></div><div class="comment_content J_Comment_Txt clearfix"><div class="t_txt"><p>'+POSTING_TIP+$.job.cmntList.filterEmotionIco(encodeHTML(con))+'</p></div><div class="comment_orig_content J_Comment_Reply clearfix">'+getTimeStr('','datetime')+'</div></div></div></div></div>';
				//插到列表最前面
				var fragment = $.C('div');
				fragment.innerHTML = tempHtml;

				var cmntList_dom = byId('J_Comment_List_Latest');
				hasCommentedArea(POSTING_TIP+$.job.cmntList.filterEmotionIco(encodeHTML(con)));
				cmntList_dom.insertBefore(fragment,cmntList_dom.firstChild);
				//不隐藏评论框的评论，应该出现提示
				// self.commentTip('succ','评论成功'+weiboTip);
				//评论框恢复默认样式并打开列表
				setTimeout(function(){
					if(self.dom.wrap.getAttribute('isFixed')=='1'){
						removeClass(self.dom.wrap,'post_box_showall');
					}
				},2500);
				//某些频道不显示评论列表,即使评论了也不显示，ARTICLE_DATA.hideCMNTList，默认为0不显示，为1显示
				if(!(ARTICLE_DATA.hideCMNTList&&ARTICLE_DATA.hideCMNTList==1)){
					byId('J_Comment_List_Wrap').style.display='';
					//评论后跳转到最新评论的顶端
					self.toLatestList();
				}

			}else{
				var tempHtml = '<div class="orig_user"><span class="t_username '+typeClz+'">'+get_user_lnk()+'</span><div class="orig_content">'+POSTING_TIP+$.job.cmntList.filterEmotionIco(encodeHTML(con))+'</p><div class="reply">'+getTimeStr('','orig_time')+'</div></div>';
				var fragment = $.C('div');
				fragment.className = 'orig_reply_wrap';
				fragment.innerHTML = tempHtml;
				// TODO 插入dom
				var replay_wrap = getCParent(dom.submit,'orig_cont');
				replay_wrap.appendChild(fragment);
				//回复完毕后，隐藏发布框
				self.dom.wrap.style.display = 'none';

			}

		},
		toLatestList:function(){
			//等待提示消失后再跳转
			setTimeout(function(){
				var gap = parseInt(jQuery('#J_Comment_Wrap_Latest').offset().top)-300;
		        if(!$globalInfo.ua.isIE6){
			        jQuery("body,html").animate({scrollTop:gap},500);
				}else{
					document.documentElement.scrollTop=gap; 
					document.body.scrollTop=gap; 
				}
			},1500);

		}

	};

	var cusEvt = $.evt.custEvent;
	cusEvt.add($, 'ce_cmntFirstRenderEnd', function(){
		new cmntForm('J_Comment_Form_B');
	});
	return cmntForm;
});/**
 * 支持
 * @param  {Object} $ SAB
 */
SAB.register('job.vote',function($){
	function bindVote(){
		var byId = $.dom.byId;
		var cmntlist = $.job.cmntList;
		var wrap = byId('J_Comment_List_Frame');
		if(!wrap){
			return;
		}

		//事件委派
		var dldEvt = $.evt.delegatedEvent;
		var dldEvt_comment= dldEvt(wrap);

		//点击支持
		dldEvt_comment.add('vote','click',function(o){
			var ele = o.el;
			var hasVoted = ele.getAttribute('voted');
			if(hasVoted=='true'){return;}
			var url = 'http://comment5.news.sina.com.cn/cmnt/vote';
			var param = {
				channel:$globalInfo.news.channel,
				newsid:$globalInfo.news.newsid,
				parent:o.data.mid,
				format:'js',
				vote:1,
				callback:function(d){},
				domain:'sina.com.cn'
			};
			$.io.ijax.request(url,{
				//param:param,
				POST:param,
				onComplete:function(msg){
					//TODO 回调不成功
				}
			});
			var vote_tip = $.C('div');
			vote_tip.className = 'vote_tip';
			ele.parentNode.appendChild(vote_tip);
			if(jQuery){
				jQuery(vote_tip).animate({opacity: "show", top: "-28"}, "fast");
			}else{
				vote_tip.style.cssText =' ;display:block;top:-28px;';
			}
			setTimeout(function(){
				if(jQuery){
					jQuery(vote_tip).animate({opacity: "hide", top: "0"}, "fast");
				}else{
					vote_tip.style.cssText =' ;';
				}
			},800);
			$.dom.addClass(ele,'comment_ding_link_active');

			var vote = ele.title;
			var voted = '已'+vote;
			var html = ele.innerHTML;
			var num = parseInt(html.replace(/[^\d]/g,''),10);
			var newnum = num+1;
			ele.innerHTML = html.replace(num,newnum).replace(vote,voted);
			ele.title = voted;
			ele.setAttribute('voted','true');
		});
	}
	var cusEvt = $.evt.custEvent;
	cusEvt.add($, 'ce_cmntFirstRenderEnd', bindVote);

});SAB.register('job.cmntListHover',function($){
	var listHover = function(){
			var jQ = jQuery;
			if(typeof jQ!= 'undefined'){
					var cmntlist = jQ('#J_Comment_List_Frame');
					var cmntSelectedClz = 'comment_selected';
					//回复列表s楼里
					cmntlist.delegate("div.orig_cont", "mouseover",function(e) {
						var item = jQ(this);
						var commentItem = item.parents('div.comment_item');
						commentItem.addClass(cmntSelectedClz);
						var reply = item.find('.replay-right');
						if(reply.length>0){
							reply[0].style.visibility='visible';
						}
				        // e.stopPropagation();
				    });
				    cmntlist.delegate("div.orig_cont","mouseout",function(e) {
	    		        var reply = jQ(this).find('.replay-right');
	    		        if(reply.length>0){
	    			        // reply[0].style.visibility='hidden';
	    		        }
				        // e.stopPropagation();
				    });
				    // 楼外
			    	cmntlist.delegate("div.comment_item", "mouseover",function(e) {
			    		var $item = jQ(this);
			    		$item.addClass(cmntSelectedClz);
			    		// var reply = $item.find('div.J_Comment_Txt span.replay-right');
			    		// if(reply.length>0){
				    	// 	reply[0].style.visibility='visible';
			    		// }
			            // e.stopPropagation();
			        });
			        cmntlist.delegate("div.comment_item","mouseout",function(e) {
			        	var $item = jQ(this);
			    		$item.removeClass(cmntSelectedClz);
			    		// var reply = $item.find('div.J_Comment_Txt span.replay-right');
			    		// if(reply.length>0){
				     //        reply[0].style.visibility='hidden';
			    		// }
			            // e.stopPropagation();
			        });
			}
	};
	var cusEvt = $.evt.custEvent;
	cusEvt.add($, 'ce_cmntFirstRenderEnd', listHover);

});/**
 * 列表下方加载更多
 * @param  {Object} $ SAB
 */
SAB.register('job.getmore',function($){
	var hasMore = function(){
		var byId    = $.dom.byId;
		var byClass = $.dom.byClass;
		var wrap = byId('J_Comment_List_Frame');
		var Cmntlist = $.job.cmntList;
		var opt = Cmntlist.options;
		var param = opt.param; 
		var listType = opt.listType;
		//如果上次加载没加载完成，忽略此次请求
		if(!wrap||Cmntlist.loading){
			return;
		}
		var showMore = function(t){
			//页面内还有隐藏的page时，直接显示，没有是则请求
			var pagesDom = byClass('J_Comment_Page_'+t,wrap,'div');
			var moreDom = $.dom.byId('J_Comment_More_'+t);
			if(pagesDom&&pagesDom.length>0){
				if(jQuery||typeof jQuery !=='undefined'){
					jQuery(pagesDom[0]).fadeIn();
				}else{
					pagesDom[0].style.display = '';
				}
				$.dom.removeClass(pagesDom[0],'J_Comment_Page_'+t);
				//显示更多供点击，如果这是热评最后一个就不要显示更多了，评论不会再getData请求数据，最新评论要通过getData请求数据，如果小于总评论数才隐藏
				if(pagesDom.length==1&&t=='Hot'){
					moreDom.style.display = 'none';
				}else{
					moreDom.style.display = '';
				}

			}else{
				if(t!='Hot'){
					//当还有数据时加载，否则显示end>没有评论了
					if(param.page*param.page_size<Cmntlist.totalNum){
						//加载下一页，只更新latest数据（其实接口获取的数据还是全部，只是在渲染的时候不渲染热点评论列表）
						param.page++;
						Cmntlist.setType('latest');
						Cmntlist.getData();
						moreDom.style.display = '';
					}else{
						//隐藏更多
						moreDom.style.display = 'none';
						Cmntlist.dom.end.style.display = '';

					}
				}else{
					//热点评论只有两个分页,隐藏更多
					moreDom.style.display = 'none';
				}

			}
		};
		if(listType!='all'){
			var listType=listType=='latest'?'Latest':'Hot';
			showMore(listType);
		}else{
			var listTypes = ['Latest','Hot'];
			for (var i = listTypes.length - 1; i >= 0; i--) {
				showMore(listTypes[i]);
			};
		}

		//suda统计点击,其实包括默认打开的第一页,click1不统计
		// var sudaClickIndex = self.totalPages-pages.length+1;
		// if(sudaClickIndex!=1){
		// 	try{
		// 		_S_uaTrack("entcomment", "click"+sudaClickIndex);
		// 	}catch(e){

		// 	}
		// }
	};
	var getmore = function(){
		var PVURL = 'http://comment5.news.sina.com.cn/comment/log.html';
		var iFrameID = 'J_CMNTMORE_PV_IFRAME';
		var body_dom = document.getElementsByTagName('body')[0];

		var byId    = $.dom.byId;
		var byClass = $.dom.byClass;
		var wrap = byId('J_Comment_List_Frame');
		if(!wrap){
			return;
		}
		var Cmntlist = $.job.cmntList;
		var addEvt = $.evt.addEvent;

		//事件委派
		var dldEvt         = $.evt.delegatedEvent;
		var dldEvt_comment= dldEvt(wrap);

		//点击退出
		dldEvt_comment.add('getmore','click',function(o){
			var type = o.data.type;
			Cmntlist.setType(type);
			hasMore();
			//新建iframe统计流量
			var old_iframe = byId(iFrameID);
			if(old_iframe){
				body_dom.removeChild(old_iframe);
			}
			var fragment = $.C('div');
			fragment.style.display = 'none';
			fragment.id=iFrameID;
			fragment.innerHTML = '<iframe class="invisible" scrolling="no" src="'+PVURL+'?_t='+ new Date().getUTCMilliseconds() + ('' + Math.random()).substring(3)+'" allowTransparency="true" style="display:none;" frameborder="0"></iframe>';
			body_dom.appendChild(fragment);
		});
	};

	var cusEvt = $.evt.custEvent;
	cusEvt.add($, 'ce_cmntRenderEnd', hasMore);
	cusEvt.add($, 'ce_cmntFirstRenderEnd', getmore);

});SAB.register('app.textareaUtils', function($){
				var T = {}, ds=document.selection;
				/**
				 * 获取指定Textarea的光标位置
				 * @param {HTMLElement} oElement 必选参数，Textarea对像
				 */
				T.selectionStart = function( oElement ){
					if(!ds){return oElement.selectionStart}
					var er = ds.createRange(), value, len, s=0;
					var er1 = document.body.createTextRange();
						er1.moveToElementText(oElement);
						for(s; er1.compareEndPoints("StartToStart", er)<0; s++){
							er1.moveStart('character', 1);
						}
					return s
				}
				T.selectionBefore = function( oElement ){
					return oElement.value.slice(0,T.selectionStart(oElement))
				}
				/**
				 * 选择指定有开始和结束位置的文本
				 * @param {HTMLElement} oElement 必选参数，Textarea对像
				 * @param {Number}      iStart   必选参数, 起始位置
				 * @param {Number}      iEnd     必选参数，结束位置
				 */
				T.selectText = function( oElement, nStart, nEnd) {
					oElement.focus();
					if (!ds){oElement.setSelectionRange(nStart, nEnd);return}
					var c = oElement.createTextRange();
						c.collapse(1);
						c.moveStart("character", nStart);
						c.moveEnd("character", nEnd - nStart);
						c.select()
				}
				/**
				 * 在起始位置插入或替换文本
				 * @param {HTMLElement} oElement    必选参数，Textarea对像
				 * @param {String}      sInsertText 必选参数，插入的文本
				 * @param {Number}      iStart      必选参数，插入位置
				 * @param {Number}      iLength     非必选参数，替换长度 
				 */
				T.insertText = function( oElement, sInsertText, nStart, nLen){
					oElement.focus();nLen = nLen||0;
					if(!ds){
						var text = oElement.value, start = nStart - nLen, end = start + sInsertText.length;
						oElement.value = text.slice(0,start) + sInsertText + text.slice(nStart, text.length);
						T.selectText(oElement, end, end);return
					}
					var c = ds.createRange();
						c.moveStart("character", -nLen);
						c.text = sInsertText
				}

				/**
				 * @param {object} 文本对象
				 */
				T.getCursorPos = function(obj){
					var CaretPos = 0; 
				    if ($globalInfo.ua.isIE) {   
				        obj.focus();
				        var range = null;
						range = ds.createRange();
						var stored_range = range.duplicate();
						stored_range.moveToElementText( obj );
						stored_range.setEndPoint('EndToEnd', range );
						obj.selectionStart = stored_range.text.length - range.text.length;
						obj.selectionEnd = obj.selectionStart + range.text.length;
						CaretPos = obj.selectionStart;
				    }else if (obj.selectionStart || obj.selectionStart =='0'){
						CaretPos = obj.selectionStart; 
					}
				    return CaretPos; 
				}
				/**
				 * @param {object} 文本对象
				 */
				T.getSelectedText = function(obj){
					var selectedText = '';
					var getSelection = function (e){
			            if (e.selectionStart != undefined && e.selectionEnd != undefined) 
			                return e.value.substring(e.selectionStart, e.selectionEnd);
			            else 
			                return '';
			        };
			        if (window.getSelection){
						selectedText = getSelection(obj);
					}else {
						selectedText = ds.createRange().text;
					}
					return selectedText;
				}
				/**
				 * @param {object} 文本对象
				 * @param {int} pars.rcs Range cur start
				 * @param {int} pars.rccl  Range cur cover length
				 * 用法
				 * setCursor(obj) cursor在文本最后
				 * setCursor(obj,5)第五个文字的后面
				 * setCursor(obj,5,2)选中第五个之后2个文本
				 */
				T.setCursor = function(obj,pos,coverlen){
					pos = pos == null ? obj.value.length : pos;
					coverlen = coverlen == null ? 0 : coverlen;
					obj.focus();
					if(obj.createTextRange) { //hack ie
				        var range = obj.createTextRange(); 
				        range.move('character', pos);
						range.moveEnd("character", coverlen);
				        range.select(); 
					} else {
				        obj.setSelectionRange(pos, pos+coverlen); 
				    }
				}
				/**
				 * @param {object} 文本对象
				 * @param {Json} 插入文本
				 * @param {Json} pars 扩展json参数
				 * @param {int} pars.rcs Range cur start
				 * @param {int} pars.rccl  Range cur cover length
				 * 
				 */
				T.unCoverInsertText = function(obj,str,pars){
					pars = (pars == null)? {} : pars ;
					pars.rcs = pars.rcs == null ? obj.value.length : pars.rcs*1;
					pars.rccl = pars.rccl == null ? 0 : pars.rccl*1;
					var text = obj.value,
						fstr = text.slice(0,pars.rcs),
						lstr = text.slice(pars.rcs + pars.rccl,text== ''?0:text.length);
					obj.value = fstr + str + lstr;
					this.setCursor(obj,pars.rcs+(str==null?0:str.length));
				}
				return T;
		});

SAB.register('job.editor', function($){
	var bindEditor = function(){
		var textareaUtils = $.app.textareaUtils;
		var wrap = $.dom.byId('J_Comment_List_Frame');
		if(!wrap){
			return;
		}
		//事件委派
		var dldEvt = $.evt.delegatedEvent;
		var dldEvt_editor= dldEvt(wrap);
		var editorRange = function(editor){
			var selValue = textareaUtils.getSelectedText(editor);
			var slen = (selValue == '' || selValue == null) ? 0 : selValue.length;
			var start = textareaUtils.getCursorPos(editor);
			var curStr = start + '&' + slen;
			editor.setAttribute('range',curStr);
		};

		dldEvt_editor.add('editor-change','keyup',function(o){
			editorRange(o.el);
		});
		dldEvt_editor.add('editor-change','mouseup',function(o){
			editorRange(o.el);
		});
	};
	var cusEvt = $.evt.custEvent;
	cusEvt.add($, 'ce_cmntFirstRenderEnd', bindEditor);
});
/**
 * 弹出快捷回复
 * @param  {Object} $ SAB
 * @return {Object}   快捷回复对象实例
 *
 * 
 */
 /*
SAB.register('job.quickReply',function($){
	function bindReply(){
		var byId = $.dom.byId;
		var cmntlist = $.job.cmntList;
		var addEvt = $.evt.addEvent;

		var wrap =byId('J_Comment_List_Frame');
		if(!wrap){
			return;
		}

		//事件委派
		var cusEvt = $.evt.custEvent;

		var dldEvt = $.evt.delegatedEvent;
		var dldEvt_comment= dldEvt(wrap);

		//登录模块
		var Login = $.module.login;

		//点击支持
		dldEvt_comment.add('reply','click',function(o){
			var ele = o.el;
			var mid = o.data.mid;
			var parMid = o.data.parMid||'comment';
			//用自身mid和被回复的mid组合，保证这个form是唯一的
			var form_id = 'J_Comment_Form_'+mid+'_'+parMid;
			var form = byId(form_id);
			var html='<div class="box_border_top_cont"><div class="box_border_top"> <em>◆</em>'
				    +'<span>◆</span>'
				  +'</div></div>'
				  +'<div class="post_box post_box_show">'
				    +'<form class="J_Comment_Form" name="post_form_inline">'
				      +'<div class="post_box_cont clearfix">'
				        +'<textarea  name="content" placeholder="请输入评论内容" class="J_Comment_Content"></textarea>'
				      +'</div>'
				        +'<div class="J_Comment_Tip post_tip" style="display:none;"><p class="post_tip_error">请输入评论内容</p></div>'        
				    +'</form>'
				    +'<div class="cmnt_user_cont clearfix">'
				      +'<div class="cmnt-emotion-btns J_Logined"><a class="cmnt-emotion-trigger" href="javascript:;" action-type="face-toggle" action-data="id='+form_id+'"></a></div>'
				      +'<div class="cmnt_name J_Unlogin" style="display:none;">'
				        +'<input class="form_input_long J_Login_User" autocomplete="off" placeholder="微博账号/博客/邮箱" name="user"/></div>'
				      +'<div class="cmnt_password J_Unlogin" style="display:none;">'
				        +'<input type="password" class="form_input_long J_Login_Psw"  autocomplete="off" placeholder="请输入密码" name="pass"/></div>'
				      +'<div class="cmnt_user_link J_Unlogin" style="display:none;">'
				        +'<a class="cmnt_user_login J_Login_Submit" href="javascript:;">登录</a>'
				        +'<a href="https://login.sina.com.cn/signup/signup.php" suda-uatrack="key=entcomment&value=comment">注册</a>'
				        +' | '
				        +'<a href="https://login.sina.com.cn/getpass.html">忘记密码？</a>'
				      +'</div>'
				      +'<div class="cmnt_user_login_info J_Login_Tip" style="display:none">'
				        +'<span class="notice">用户名/密码错误</span>'
				      +'</div>'
				      +'<a class="J_Comment_Submit post_inline_comment post_inline_comment_disbled" href="javascript:;"></a>' 
				      +'<div class="cmnt_user_other J_WeiboLogined" style="display:none;">'
				        +'<a class="ccto_link" href="#url">@TA</a>'
				        +'<label class="share_wt J_Comment_ToWeibo_Wrap">'
				          +'<span class="to_mb J_Comment_ToWeibo" toweibo="0"></span>'
				          +'分享到微博'
				        +'</label>'
				      +'</div>'
				      +'<div class="cmnt_user J_Logined" style="display:none;">'
				        +'<a target="_blank" href="#url" class="J_Login_Ico">'
				          +'<img src="http://img.t.sinajs.cn/t4/style/images/common/transparent.gif" title="微博" alt="weibo" class="username_icon"></a>'
				        +'<span class="J_Name">用户名</span>'
				        +'&nbsp;|&nbsp;'
				        +'<a class="J_Login_Logout" title="退出登录" onclick="return false;"  href="javascript:;">退出</a>'
				      +'</div>'
				    +'</div>'
				  +'</div>';
			//通过类找到最近的祖先元素
			function getCParent(ele,clz){
				var par = ele.parentNode;
				if($.dom.hasClass(par,clz)){
					return par;
				}else{
					return getCParent(par,clz);
				}
			}
			//楼里回复
			if(o.data.type=='innerReply'){
				var item_dom = getCParent(ele,'orig_cont');
			}
			//楼外回复
			else{
				var item_dom = byId('J_Comment_Item-'+mid);
			}
			//不管有没有form都要重建，因为楼外回复的按钮，被回复一次后，会变成楼里的回复按钮 cmnt-type改变
			if(1){
				var fragment = $.C('div');
				fragment.style.display = 'none';
				fragment.id = form_id;
				fragment.className = 'cmnt_inline_post_box';
				fragment.setAttribute('cmnt-type',o.data.type);
				//innerHTML进来的字符串不能带onclick="****",ie会报错
				fragment.innerHTML = html;
				item_dom.appendChild(fragment);
				if($globalInfo.isWeiboLogin){
					Login.showWeiboLogined(item_dom);
					// cusEvt.fire($, 'ce_weiboLogin');
				}
				if($globalInfo.isLogin){
					Login.showLogined(item_dom);
					// cusEvt.fire($, 'ce_login');
				}else{
					Login.showUnlogined(item_dom);
					// cusEvt.fire($, 'ce_logout');
				}
				form = byId(form_id);
				//新建一个form对象
				var cmntForm = new $.job.cmntForm(form_id);
			}
			//隐藏当前已打开的且不是现在要打开的回复form
			if($globalInfo.curReplyForm!=form_id){
				var curReplyForm = byId($globalInfo.curReplyForm);
				if(curReplyForm){
					curReplyForm.style.display = 'none';
				}
			}
			$globalInfo.curReplyForm = form_id;
			//弹出toggle
			var content = $.dom.byClass('J_Comment_Content',form)[0];
			if(form.style.display!=='none'){
				if(jQuery&&!$globalInfo.ua.isIE6){
					jQuery(form).slideUp('fast','linear',function(){
						form.setAttribute('poped','false');
					});
				}else{
					form.style.display = 'none';
					form.setAttribute('poped','false');
				}
			}else{
				if(jQuery&&!$globalInfo.ua.isIE6){
					jQuery(form).slideDown('fast','linear',function(){
						form.setAttribute('poped','true');
						content.focus();
					});
				}else{
					form.style.display = '';
					form.setAttribute('poped','true');
					content.focus();
				}

			}
		});
	}
	var cusEvt = $.evt.custEvent;
	cusEvt.add($, 'ce_cmntFirstRenderEnd', bindReply);
});
*/

/**
 * 加载器
 * @param  {Object} $ SAB
 */
SAB.register('app.dataLoader',function($){
	var DataLoader = function(options) {
		var self = this;
		self.inited = false;
		self.isReflash = true;
		self.options = self._setOptions(options);

	};
	DataLoader.prototype = {
		init : function() {
			this._init();
		},
		_init : function() {
			var self = this;
			/*是否刷新*/
			self.inited = true;
			/*是否正在加载中*/
			self.data = '';
			self.loading = false;
			self.getData();
		},
		/*设置默认设置*/
		_setOptions : function(options) {
			/*默认设置*/
			var defaults = this.defaults = {
				/*数据地址*/
				url : '',
				param : '',
				interval : 0,
				beforeLoad : function() {
				},
				loaded : function(data) {
				},
				error : function(error) {
				}
			};

			return $.clz.extend(defaults, options, true);

		},
		/*获取数据*/
		getData : function() {
			var self = this;
			var opt = self.options;
			var url = opt.url;
			var param = opt.param;
			function intetval(){
				if (opt.interval > 0) {
					self.setTimeout = null;
					self.setTimeout = setTimeout(function() {
								self.getData();
							}, opt.interval);
				}
			}
			function request(){
				$.io.jsload.request(url,{
					GET:param,
					onComplete:function(msg){
						if(typeof msg == 'undefined'){
							return;
						}
						if(msg.result.status.code==0){
							self.data = msg.result;
							opt.loaded(self);
							intetval();
						}else{
							var error = msg.result.status.msg;
							opt.error(error);
							intetval();
						}
						self.loading = false;
					}
				});

			}
			/*判断是否刷新*/
 			if(self.isReflash){
 				self.loading = true;
 				opt.beforeLoad(self);
				request();
 			}else{
 				intetval();
 			}

		},
        /*是否刷新*/
        reflash:function(b){
        	this.isReflash = b;
        }
	};
	return DataLoader;

});/**
 * 评论列表
 * @param  {Object} $ SAB
 * @return {OBject}   评论列表对象
 */
SAB.register('job.cmntList',function($){
	var CMNTURL = 'http://comment5.news.sina.com.cn/page/info';
	// var CMNTURL = 'http://comment5.news.sina.com.cn/cmnt/stream';
	//默认显示页面数
	var PAGENUM = ARTICLE_DATA.pageNum;
	var HOTPAGNUM = ARTICLE_DATA.hotPageNum;
	//微博用户uid链接
	var WBUURL = 'http://weibo.com/u/';
	var encodeHTML = $.str.encodeHTML;

	//格式化时间
	var formatTime = $.app.formatTime;
	var updateTime = $.app.updateTime;
	var getTimeStr = $.app.getTimeStr;

	var byId = $.dom.byId;
	var byClass = $.dom.byClass;

	//自定义事件
	var cusEvt = $.evt.custEvent;

	var TIMEOUT = 10*1000;
	var loadTimeout = function(){
		//10秒后还在加载中，或者没数据则为超时
		if((list.loading||!list.data)&&ARTICLE_DATA.newsid!=''){
			try{
				_S_uaTrack("page_comment", "timeout");
			}catch(e){

			}
		}
	};

	//TODO param部分数据从url获取
	var param = {
		format:'js',
		channel:ARTICLE_DATA.channel,
		newsid:ARTICLE_DATA.newsid,
		//style=1本来为皮肤，应该为group=1
		group:ARTICLE_DATA.group||ARTICLE_DATA.style,
		compress:1,
		ie:ARTICLE_DATA.encoding,
		oe:ARTICLE_DATA.encoding,
		page:1,
		page_size:100
	};

	var list = new $.app.dataLoader({
		url:CMNTURL,
		param:param,
		listType:'all',
		beforeLoad:function(self){
			self.beforeLoad();
		},
		loaded:function(self){
			list.render();
			list.loaded();
		}
	});
	list.firstRender = true;
	/**
	 * dom节点
	 */
	list.getDom = function(){
		var dom= {
			wrap:byId('J_Comment_List_Wrap'),
			latest:byId('J_Comment_List_Latest'),
			hot:byId('J_Comment_List_Hot'),
			loading:byId('J_Comment_Loading'),
			more_hot:byId('J_Comment_More_Hot'),
			more_latest:byId('J_Comment_More_Latest'),
			end:byId('J_Comment_End')
		};
		return dom;
	};
	/**
	 * 加载前
	 */
	list.beforeLoad = function(){
		var dom = this.dom = this.getDom();
		dom.loading.style.display = '';
		dom.end.style.display = 'none';
	};
	/**
	 * 加载后
	 */
	list.loaded = function(){
		var self = this;
		var dom = this.dom;
		//总假分页
		self.totalPages = 0;
		//列表类型，全部？热门？
		var listType = this.options.listType;
		dom.loading.style.display = 'none';
		//渲染结束
		cusEvt.fire($,'ce_cmntRenderEnd');

		//触发自定义事件
		if(self.firstRender){
			//定时更新时间
			updateTime(self.dom.wrap);
			cusEvt.fire($,'ce_cmntFirstRenderEnd');
			self.firstRender = false;
		}
	};
	list.filterEmotionIco = function(text){
		/*var template:'<li title="#{title}"><a href="#" onclick="return false;"><img alt="#{title}" src="#{img_url}" /></a></li>',*/
		var baseUrl=ARTICLE_DATA.allFacesBase;
		var emoticons = ARTICLE_DATA.allFaces; 
		var regExp= /\[(.*?)\]/g;
        text = text || "";
        text = text.replace(regExp,function($0,$1){
                var imgUrl = emoticons[$1];
                if(imgUrl){
                        imgUrl = baseUrl+imgUrl+'.gif';
                }
                return !imgUrl?$1:'<img class="comment_content_emotion" title="'+$1+'" alt="'+$1+'" src="'+imgUrl+'" />';
        });
        return text;

	};
	list.get_wb_screen_name = function(d){
		var temp = d.config.match(/wb_screen_name=([^&]*)/i);
		return temp?temp[1]:'';
	};

	list.isAnonymous = function(d){
		var temp = d.config.match(/anonymous=([^&]*)/i);
		return temp ? temp[1]: 0;
	};

	list.isSpoiler = function(d){
		var temp = d.config.match(/spoiler=([^&]*)/i);
		return temp ? temp[1]: 0;
	};

	// 影视打分第一期隐藏回复列表
	list.replyListRender = function(d){
		var WBUURL = 'http://weibo.com/u/';
		var self = this;
		var mid = d.mid;
		//该条评论的回复列表
		var tReplyList = replydict[mid];
		var tReplyHtml = '';
		if(tReplyList&&tReplyList.length>0){
			for (var i = 0,len = tReplyList.length; i < len; i++) {
				var item = tReplyList[i];
				var wb_name = self.get_wb_screen_name(item);

				var tUserLnk = wb_name==''?
					'<a href="'+WBUURL+item.uid+'" target="_blank">'+self.get_wb_screen_name(item)+'</a>':
					d.nick;
				var area = (item.usertype == 'wb'||item.area=='')?'&nbsp;':'['+item.area+']';
				//是否有回复
				var hasReply = '';
				if(i!==0){
					hasReply = 1;
				}
				var share_action_data = ' href="javascript:;" action-type="share" action-data=" mid='+item.mid+'&usertype='+item.usertype+'&wb_screen_name='+wb_name+'&area='+item.area+'&nick='+item.nick+'&con='+item.content+'&type=reply&hasReply='+hasReply+'&site=';
				var tempHtml = '<span class="orig_index">'+(i+1)+'</span>'
	             	   +'<div class="orig_user">'+self.get_user_lnk(item)+'<span class="orig_area">'+area+'</span></div>'
					   +'<div class="orig_content">'+self.filterEmotionIco(encodeHTML(item.content))+'</div>'
					   +'<div class="orig_reply" style="visibility: ;"><div class="reply">'+getTimeStr(item.time,'orig_time')+'<span class="replay-right"><a action-type="vote" action-data="mid='+item.mid+'" href="javascript:;" voted="false" class="comment_ding_link" title="支持"><span>支持<em>('+item.agree+')</em></span></a> <a action-type="reply" action-data="mid='+item.mid+'&parMid='+mid+'&type=innerReply" href="javascript:;" poped="false" class="comment_reply_link" title="回复">回复</a> '
					   +'<a href="javascript:;" action-type="shareHover" class="cmnt-share-tirgger" title="分享"><em>分享</em></a></span>'
		           	   +'<span class="cmnt-share-btns J_Comment_Share_Btns"><a title="分享到新浪微博" class="cmnt-share-btn-sina" '+share_action_data+'sina">新浪</a><a title="分享到腾讯微博" class="cmnt-share-btn-qq" '+share_action_data+'tencent">腾讯</a></span>'
					   +'</div></div>';
				if(i<10){
					tReplyHtml = '<div class="orig_cont clearfix">' +tReplyHtml+tempHtml +'</div>';
				}else{
					tReplyHtml = tReplyHtml+'<div class="orig_cont clearfix">' + tempHtml +'</div>';
				}

			};
		}
		if(tReplyHtml!==''){
			tReplyHtml = '<div class="comment_orig_content">'+tReplyHtml+'</div>'
		}
		return tReplyHtml;

	};
	list.get_user_lnk = function(d){
		var self = this;
		var wb_name = self.get_wb_screen_name(d);
		//如果wb_screen_name为空的话，说明不是用微博名来评论的
		if(self.isAnonymous(d) == 1){
			return '匿名用户';
		} else if(wb_name==''){
			return d.nick;
		}else{
			return '<a href="'+WBUURL+d.uid+'" target="_blank">'+wb_name+'</a>';
		}
	};
	list.get_user_face = function(d){
		var self = this;
		var face =$.json.queryToJson(d.config,true).wb_profile_img||'http://i3.sinaimg.cn/dy/deco/2012/1018/sina_comment_defaultface.png';
		var wb_name = self.get_wb_screen_name(d);
		//如果wb_screen_name为空的话，说明不是用微博名来评论的
		if(self.isAnonymous(d) == 1){
			return '<img src="http://i3.sinaimg.cn/dy/deco/2012/1018/sina_comment_defaultface.png"/>';
		} else if(wb_name==''){
			return '<img src="'+face+'"/>';
		}else{
			return '<a href="'+WBUURL+d.uid+'" title="'+wb_name+'" target="_blank"><img src="'+face+'"/></a>';
		}
	};
	list.get_user_ico = function(d){
		var self = this;
		//用户类型css类
		var typeClzObj = {
			'wap':'t_mobile',
			'wb':'t_weibo'
		};
		var typeClz = typeClzObj[d.usertype];
		typeClz = typeClz||'';
		if(typeClz==''){
			//如果有微博链接就是微博用户
			var wb_name = self.get_wb_screen_name(d);
			//如果wb_screen_name为空的话，说明不是用微博名来评论的
			if(wb_name==''){
				typeClz = '';
			}else{
				typeClz=typeClzObj.wb;
			}
		}
		return typeClz

	};
	list.cmntListRender = function(cmntlist,type){
		var self = this;
		var html = [];
		var isSummary = self.isSummary;
		var getNewsLink = function(id){
			return '';
		};
		if(isSummary){
			var newsdict = self.data.newsdict;
			var getNewsLink = function(id){
				var item = newsdict[id];
				if(!item){
					return '';
				}
				var html = '<span class="t_newslink"><a target="_blank" href="'+item.url+'" title="'+item.title+'">'+item.title+'</a></span>';
				return html
			};
		}
		// 保留到小数点后一位
		var toDecimal1 = function(x) {  
	        var f = parseFloat(x);  
	        if (isNaN(f)) {  
	            return false;  
	        }  
	        var f = Math.round(x*10)/10;  
	        var s = f.toString();  
	        var rs = s.indexOf('.');  
	        if (rs < 0) {  
	            rs = s.length;  
	            s += '.';  
	        }  
	        while (s.length <= rs + 1) {  
	            s += '0';  
	        }  
	        return s;  
	    }
		var render = function(d,type){
			var html = '';
			var mid = d.mid;
			var rank = toDecimal1(d.rank) || '0.0';
			var area = (d.usertype == 'wb'||d.area=='')?'&nbsp;':'['+d.area+']';
			// 隐藏回复列表 bycq
			// tReplyHtml = self.replyListRender(d);
			var isSpoiler = self.isSpoiler(d);
			var isSpoilertxt = isSpoiler == 1 ? '（该微评有剧透）' : '';
			tReplyHtml = '';
			//是否有回复
			var hasReply = '';
			if(tReplyHtml){
				hasReply = 1;
			}
			// 是否是最热评论
			var tmpHotStr = '';
			if(!!type && type == 'hot'){
				tmpHotStr = '<span class="hot_icon"><img src="images/hot_icon.png" alt="最热评论" title="最热评论"/></span>';
			}
			var wb_name = self.get_wb_screen_name(d);
			var share_action_data = 'href="javascript:;" action-type="share" action-data="mid='+mid+'&wb_screen_name='+wb_name+'&hasReply='+hasReply+'&site=';

			tReplyHtml = '<div class="J_Comment_Reply">'+tReplyHtml+'</div>';
			html = '<div class="comment_item" id="J_Comment_Item-'+mid+'">'
	        +'<div class="comment_item_cont clearfix">'
	         +'<div class="J_Comment_Face t_face">'
	         	+self.get_user_face(d)
	         +'</div>'
	         +'<div class="t_content">'
		         +'<div class="J_Comment_Info">'
		        	 +'<div class="t_info"> '+getNewsLink(d.newsid)+' <span class="t_username '+self.get_user_ico(d)+'">'+self.get_user_lnk(d)+'</span><span class="t_area">'+area+'</span><span class="t_pf">评分:<em class="cyellow score">' + rank + '</em>分<em class="cyellow">' + isSpoilertxt + '</em></span>' + tmpHotStr + '</div>'
		         +'</div>'
		         + tReplyHtml
		         +'<div class="comment_content J_Comment_Txt clearfix">'
		           +'<div class="t_txt">'+self.filterEmotionIco(encodeHTML(d.content))+'</div>'
		           +'<div class="reply" style="visibility: visible;">'+getTimeStr(d.time,'datetime')+'<div class="vote_tip">+1</div> <span class="replay-right"><a action-type="vote" action-data="mid='+mid+'" href="javascript:;" voted="false" class="comment_ding_link" title="支持"><span>支持<em>('+d.agree+')</em></span></a> <a action-type="reply" action-data="mid='+mid+'&type=outerReply" href="javascript:;" poped="false" class="comment_reply_link" title="回复" style="display:none">回复</a> '
		           +'<a href="javascript:;" action-type="shareHover" class="cmnt-share-tirgger" title="分享"><em>分享</em></a></span>'
		           +'<span class="cmnt-share-btns J_Comment_Share_Btns"><a title="分享到新浪微博" class="cmnt-share-btn-sina" '+share_action_data+'sina">新浪</a><a title="分享到腾讯微博" class="cmnt-share-btn-qq"  '+share_action_data+'tencent">腾讯</a></span>'
		           +'</div>'
		         +'</div>'
		       +'</div>' 
	        +'</div>'
	      +'</div>';

	      return html;
		};

		if(typeof cmntlist == "object"){
			var index = 0;
			var divNum = 0;
			var totalPages = 0;
			var hotlistNum = 0;
			var postfix = '';
			//最新评论只显示第一页，其它通过更多来加载
			if(type=='latest'){
				for(var i in cmntlist){
					if((index+1)%PAGENUM==1){
						postfix = index==0?'_first':'';
						html.push('<div class="comment_item_page'+postfix+' J_Comment_Page_Latest" style="display:none">');
						divNum++;
						totalPages++;

					}
					html.push(render(cmntlist[i]));
					if((index+1)%PAGENUM==0){
						html.push('</div>');
						divNum++;
					}
					index++;
				}
				if(divNum%2!=0){
					html.push('</div>');
				}
			}
			//最热评论全部显示
			else{
				html.push('<div class="comment_item_page_first J_Comment_Page_Hot" style="display:none;">');
				divNum++;
				totalPages++;
				for(var i in cmntlist){
					hotlistNum++;
					if(index==HOTPAGNUM){
						html.push('<div class="comment_item_page J_Comment_Page_Hot" style="display:none;">');
						divNum++;
						totalPages++;
					}
					html.push(render(cmntlist[i],type));
					if((index+1)/HOTPAGNUM==1){
						html.push('</div>');
						divNum++;
					}

					index++;
					if(hotlistNum == ARTICLE_DATA.hotLimitNum){
						break;
					}
				}
				if(divNum%2!=0){
					html.push('</div>');
				}
			}
			if(self.totalPages==0&&totalPages>1){
				//suda统计点击,显示第一个“更多评论”的次数，说白了就是每个正文加载时多于10条的次数
				try{
					_S_uaTrack("entcomment", "onemorepageview");
				}catch(e){

				}
			}
			self.totalPages = totalPages;
		}
		for (var i = 0; i < html.length; i++) {
			html[i]
		};
		return html.join('');
	};
	/**
	 * 渲染评论列表
	 * @return {object} self
	 */
	list.render = function(){
		var self = this;
		var data = self.data;
		var dom = self.dom;
		var opt = self.options;
		var param = self.options.param;
		var listType = opt.listType;
		//本条新闻信息
		self.setNewsData(data.news);
		//数据总条数
		self.totalNum = self.data.count.show;
		//是否是汇总评论，有的评论是相关的新闻评论汇总一起的，在BBS里，每条评论旁要显示对应的新闻链接
		self.isSummary = (ARTICLE_DATA.isBBS&&ARTICLE_DATA.style);
		//渲染开始,要用到data.news
		cusEvt.fire($,'ce_cmntRenderStart');
		//评论列表
		var cmntlist = data.cmntlist;
		var hotlist = data.hot_list;
		if(cmntlist.length==0){return;}
		//把每次加载进来的评论列表合并起来
		if(self.cList){
			self.cList = self.cList.concat(cmntlist);
		}else{
			self.cList = cmntlist;
		}
		self.cList = self.cList.concat(hotlist);

		//回复列表
		replydict = data.replydict;

		//如果列表类型listType为hot只更新hot列表，latest类似，all时全部更新
		if(listType=='all'){
			var hot_html = self.cmntListRender(hotlist,'hot');
			var latest_html = self.cmntListRender(cmntlist,'latest');
			dom.hot.innerHTML=hot_html;
			dom.latest.innerHTML=latest_html;
		}else if(listType=='latest'){
			var latest_html = self.cmntListRender(cmntlist,'latest');
			//第一次加载,也就是第一页时，再渲染热门评论和innerHTML时最新评论,否则appenchild加到结尾
			if(param.page==1){
				dom.latest.innerHTML=latest_html;
			}else{
				var fragment = $.C('div');
				fragment.innerHTML = latest_html;
				dom.latest.appendChild(fragment);
			}
		}else{
			var hot_html = self.cmntListRender(hotlist,'hot');
			dom.hot.innerHTML=hot_html;
		}
		//加载完成
		self.loading = false;
		return self;
	};

	/**
	 * 清空列表，一般在更换评论type时，如加载热门评论“hot”前使用
	 * @return {[type]} [description]
	 */
	list.empty = function(){
		this.dom.wrap.innerHTML ='';
		return this;
	};
	list.setNewsData = function(d){
		if(d){
			$globalInfo.news=d;
		}
		return this;
	};
	/**
	 * 设置页面，为下次加载数据做准备
	 * @param {Number} i 要设置的页数
	 */
	list.setPage = function(i){
		if(i<1){return this;}
		this.options.param.page = i;
		return this;
	};
	/**
	 * 设置评论类型，为下次加载数据做准备,
	 * @param {String} t all||hot 全部评论或热门评论
	 */
	list.setType = function(t){
		//all全部，hot最热
		if(t!='all'&&t!='hot'&&t!='latest'){
			return;
		}
		this.options.listType = t;
		return this;
	};
	var cmntlistInit = function(){
		setTimeout(function(){
			loadTimeout();
		},TIMEOUT);
		list.init();
		try{
			_S_uaTrack("entcomment", "pageview");
		}catch(e){

		}
	};
	cusEvt.add($,'ce_cmntHtmlInit',cmntlistInit);
	return list;
});/**
 * 评论列表选项卡
 * @param  {Object} $ SAB
 * @return {Object}   选项卡HTML element
 */
SAB.register('job.cmntStructure',function($){
	var render  = function(){
		var temp = [];
		var iDiv = function(s){
			temp.push(s);
		};
		//论坛里的评论数不带链接
		var isBBS = ARTICLE_DATA.isBBS;		 
		  iDiv('<div class="b_cont2" id="J_Comment_List_Wrap" style="display:none;">');
		    iDiv('<div id="J_Comment_Tip" class="comment_item comment_tip" style="display:none;">');
		      iDiv('<span>还没有评论</span>');
		    iDiv('</div>');
		    iDiv('<div id="J_Comment_Wrap_Hot" style="display:none;">');		 
			    iDiv('<div class="b_txt" id="J_Comment_List_Hot">');
			    iDiv('</div>');
			    iDiv('<div id="J_Comment_More_Hot" class="comment_more" action-type="getmore" action-data="type=hot" style="display:none; ">');
			      iDiv('<a href="javascript:;" onclick="return false;">显示更多热评&gt;&gt;</a>');
			    iDiv('</div>');
			iDiv('</div>');
			iDiv('<div id="J_Comment_Wrap_Latest">');
			    iDiv('<div class="b_txt" id="J_Comment_List_Latest">');
			    iDiv('</div>');
			    iDiv('<div id="J_Comment_More_Latest" class="comment_more comment_nobg" action-type="getmore" action-data="type=latest" style="display:none; ">');
			      iDiv('<a href="javascript:;" onclick="return false;">显示更多评论&gt;&gt;</a>');
			    iDiv('</div>');
			iDiv('</div>');
		    iDiv('<div id="J_Comment_Loading" class="comment_item comment_loading" style="display:none; ">');
		      iDiv('<span>');
		        iDiv('<img src="http://i3.sinaimg.cn/ent/deco/2012/0912/images/indicator_24.gif" height="24" width="24" alt="" style="vertical-align:middle;">评论加载中，请稍候...</span>');
		    iDiv('</div>');

		    iDiv('<div id="J_Comment_End" class="comment_more comment_nobg" style="display: none; ">');
		      //评论结束提示
		      iDiv(isBBS?'<span>已到最后一页</span>':'<a href="javascript:;" target="_blank">查看更多精彩评论&gt;&gt;</a>');

		    iDiv('</div>');
		  iDiv('</div>');
		var wrap = $.dom.byId('J_Comment_List_Frame');
		if(wrap){
			wrap.innerHTML = temp.join('');
			return wrap;
		}else{
			return null;
		}
	};
	var cusEvt = $.evt.custEvent;
	//评论框和列表wrap结构渲染完成
	$.dom.ready(function(){
		if(render()){
			cusEvt.fire($,'ce_cmntHtmlInit');
			if($globalInfo.isWeiboLogin){
				cusEvt.fire($, 'ce_weiboLogin');
			}
			if($globalInfo.isLogin){
				cusEvt.fire($, 'ce_login');
			}else{
				cusEvt.fire($, 'ce_logout');
			}
		}
	});

});
/**
 * 返回顶部
 * @param  {Object} $ SAB
 */
SAB.register('job.toTop',function($){
	var jQ = jQuery;
	var toTop = jQ('<div class="side-btns-wrap" style="display:none;"><div suda-uatrack="key=up_to_top&value=clicktimes" id="top_btn" class="top_btn"> <a title="返回顶部" href="javascript:void(0)" class="toplink">TOP</a> </div></div>');
	jQ(function() { 
		//iphone,ipod,ipad不需要
		if($globalInfo.ua.isIOS){
			return;
		}
		toTop.appendTo(jQ('body'));
		toTop.on('click',function() {
			if(!$globalInfo.ua.isIE6){
				jQ('html,body').animate({ scrollTop: 0 }, 120);
			}else{
				document.documentElement.scrollTop=0; 
			}
		});
		var  toTopFun = function() {
			var st = jQ(document).scrollTop(),
			winh = jQ(window).height();
			(st > 0)? toTop.fadeIn():toTop.fadeOut();
			//IE6下的定位
			if (!window.XMLHttpRequest) {
				toTop.css("top", st + winh - 166);
			}
		};
		jQ(window).bind('scroll', toTopFun);
		toTopFun(); 
	});
	return toTop;
});
if($globalInfo.SABLoaded){
	SAB.register = SAB._register;
}
$globalInfo.SABLoaded = true;
