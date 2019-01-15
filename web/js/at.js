
/**
 * @desc 联想提示
 */
if (typeof mblog == 'undefined') {
    var mblog = [];
    mblog.Func = [];
}
$.extend($.fn,{
  setStyle:function(property,val){
    var el = this[0];
    if(!!$.browser.msie){
      switch(property) {
        case "opacity" :
          el.style.filter = "alpha(opacity=" + (val*100)+")";
          if(!el.currentStyle || !el.currentStyle.hasLayout) {
            el.style.zoom = 1;
          }
          break;
        case "float" :
          property = "styleFloat";
        default:
          el.style[property] = val;
      }
    }else{
      if(property == "float") {
        property = "cssFloat";
      }
      el.style[property] = val;
    }
  },
  getStyle:function(property) {
    var el = this[0];
    if (!$.browser.msie) {
      if (property == "float") {
        property = "cssFloat";
      }
      try {
        var computed = document.defaultView.getComputedStyle(el, "");
      } catch (e) {
      }
      return el.style[property] || computed ? computed[property] : null;
    } else {
      switch (property) {
        case "opacity" :
          var val = 100;
          try {
            val = el.filters["DXImageTransform.Microsoft.Alpha"].opacity;
          } catch (e) {
            try {
              val = el.filters("alpha").opacity;
            } catch (e) {
            }
          }
          return val / 100;
        case "float" :
          property = "styleFloat";
        default :
          var value = el.currentStyle ? el.currentStyle[property] : null;
          return (el.style[property] || value);
      };
    }
  },
  getScrollPos:function() {
    var oDocument = this[0];
    oDocument = oDocument || document;
    var dd = oDocument.documentElement;
    var db = oDocument.body;
    return [Math.max(dd.scrollTop, db.scrollTop),Math.max(dd.scrollLeft, db.scrollLeft),Math.max(dd.scrollWidth, db.scrollWidth),Math.max(dd.scrollHeight, db.scrollHeight)];
  },
  pos:function() {
    var el = this[0];
    if ((el.parentNode == null || el.offsetParent == null || $(el).getStyle("display") == "none") && el != document.body) {
      return false;
    }
    var parentNode = null;
    var pos = [],box,doc = el.ownerDocument;
    box = el.getBoundingClientRect();
    var scrollPos = $(el.ownerDocument).getScrollPos();
    return [box.left + scrollPos[1], box.top + scrollPos[0]];
    parentNode = el.parentNode;
    while (parentNode.tagName && !/^body|html$/i.test(parentNode.tagName)) {
      if ($(parentNode).getStyle("display").search(/^inline|table-row.*$/i)) {
        pos[0] -= parentNode.scrollLeft;
        pos[1] -= parentNode.scrollTop;
      }
      parentNode = parentNode.parentNode;
    }
    return pos;
  }
});

mblog.Func.EncodeUtils = (function () {
  var _hash = {
    "<":"&lt;",">":"&gt;",'"':"&quot;","\\":"&#92;","&":"&amp;","'":"&#039;","\r":"","\n":"<br>"
  },fReg = /<|>|\'|\"|&|\\|\r\n|\n|/gi;
  var it = {
  };
  it.regexp = function (value) {
    return value.replace(/\}|\]|\)|\.|\$|\^|\{|\[|\(|\|\|\*|\+|\?|\\/gi,function (k) {
      k = k.charCodeAt(0).toString (16);
      return "\\u"+(new Array(5-k.length)).join("0") + k;
    });
  };
  it.html = function (value,hash) {
    hash=hash||_hash;
    return value.replace(fReg,function (k) {
      return hash[k];
    });
  };
  return it;
})();

mblog.Func.TextareaUtils = (function () {
  var it = {
  },ds = document.selection;
  //获取或设置当前选择的起始位置的字符索引
  it.selectionStart = function (oElement) {
    if(!ds) {
      return oElement.selectionStart;
    }
    var er = ds.createRange(),value,len,s = 0,
      er1 = document.body.createTextRange();
    er1.moveToElementText(oElement);
    for(s;er1.compareEndPoints("StartToStart",er) < 0;s++) {
      er1.moveStart("character",1);
    }
    return s;
  };
  it.selectionBefore = function (oElement) {
    return oElement.value.slice(0,it.selectionStart(oElement));
  };
  it.selectText = function (oElement,nStart,nEnd) {
    oElement.focus();
    if(!ds) {
      oElement.setSelectionRange(nStart,nEnd);
      return;
    }
    var c = oElement.createTextRange();
    c.collapse(1);
    c.moveStart("character",nStart);
    c.moveEnd("character",nEnd - nStart);
    c.select();
  };
  it.insertText = function (oElement,sInsertText,nStart,nLen) {
    oElement.focus();
    nLen = nLen||0;
    if(!ds) {
      var text = oElement.value,start = nStart - nLen,end = start + sInsertText.length;

      oElement.value = text.slice(0,start) + sInsertText + text.slice(nStart,text.length);
      it.selectText(oElement,end,end);
      return;
    }
    var c = ds.createRange();
    c.moveStart("character",- nLen);
    c.text = sInsertText;
  };
  it.getCursorPos = function (obj) {
    var CaretPos = 0;
    if($IE) {
      obj.focus();
      var range = null;
      range = ds.createRange();
      var stored_range = range.duplicate();
      stored_range.moveToElementText(obj);
      stored_range.setEndPoint("EndToEnd",range);
      obj.selectionStart = stored_range.text.length-range.text.length;
      obj.selectionEnd = obj.selectionStart+range.text.length;
      CaretPos = obj.selectionStart;
    }else {
      if(obj.selectionStart || obj.selectionStart == "0") {
        CaretPos = obj.selectionStart;
      }
    }
    return CaretPos;
  };
  it.getSelectedText = function (obj) {
    var selectedText = "";
    var getSelection = function (e) {
      if(e.selectionStart != undefined && e.selectionEnd!=undefined) {
        return e.value.substring(e.selectionStart,e.selectionEnd);
      }else {
        return "";
      }
    };
    if(window.getSelection) {
      selectedText = getSelection(obj);
    }else {
      selectedText = ds.createRange().text;
    }
    return selectedText;
  };
  it.setCursor = function (obj,pos,coverlen) {
    pos = pos== null ? obj.value.length : pos;
    coverlen = coverlen == null ? 0 : coverlen;
    obj.focus();
    if(obj.createTextRange) {
      var range = obj.createTextRange();
      range.move("character",pos);
      range.moveEnd("character",coverlen);
      range.select();
    }else {
      obj.setSelectionRange(pos,pos + coverlen);
    }
  };
  it.unCoverInsertText = function (obj,str,pars) {
    pars = (pars == null) ? {
    } : pars;
    pars.rcs = pars.rcs == null ? obj.value.length:pars.rcs*1;
    pars.rccl = pars.rccl == null ? 0 : pars.rccl*1;
    var text = obj.value,fstr = text.slice(0,pars.rcs),lstr = text.slice(pars.rcs+pars.rccl,text == "" ? 0 : text.length);
    obj.value = fstr + str + lstr;
    this.setCursor(obj,pars.rcs + (str == null ? 0 : str.length));
  };
  return it;
})();

mblog.Func.PopUpCombo = (function () {
  var it = {},filter = mblog.Func.EncodeUtils.regexp,toIndex,value,content,current,key,reg,tip,panel,head,lis = [],onSelect,onClose,len,selected = 0;
  it.validate = false;
  it.index = function (num) {
    toIndex = !num ? 0 : selected + num;
    toIndex = toIndex < 0 ? len : (toIndex > len) ? 0 : toIndex;
    lis[selected].removeClass('cur');
    lis[toIndex].addClass('cur');
    selected = toIndex;
    value = content[selected];
  };
  it._click = function () {
    onSelect && onSelect(value);
  };
  it.hidden = function () {
    it.initTip();
    tip.css({'display':'none'});
    it.validate && !(it.validate = false) && onClose && onClose();
  };
  it.initTip = function () {
    if(!tip) {
      tip = $('<div/>');
      panel = $('<ul/>');
      tip.append(panel);
      with(tip.get(0).style) {
        zIndex = 99999999;
        position = "absolute";
        display = "none";
      }
      tip.addClass('Atwho');
      $('body').append(tip);
    }
  };
  it.position = function (x,y,offsetX,offsetY) {
    it.initTip();
    it.validate = true;
    tip.show();
    with(tip.get(0).style) {
      left = (x + offsetX) + "px";
      top = (y + offsetY) + "px";
    }
  };
  it.selection = function (event) {
    var keyCode = event.keyCode,toIndex,value;
    if(!it.validate) {
      return;
    }
    if(keyCode == 40 || keyCode == 38) {
      it.index(keyCode == 40 ? 1 : -1);
      it.stopEvent();
    }else {
      if(keyCode == 13 || keyCode == 9) {
        it._click();
        it.stopEvent();
      }else {
        if(keyCode == 27) {
          it.hidden();
          it.stopEvent();
        }
      }
    }
  };
  it.addItem=function (itemValue) {
    var li = $('<li/>'),index;
    li.html(itemValue.replace(reg,"<b>$1</b>"));
    lis.push(li);
    len = index = lis.length - 1;
    content.push(itemValue);
    panel.append(li);
    li.mouseover(function(event){
      lis[selected].removeClass('cur');
      lis[index].addClass('cur');
      value = itemValue;
      selected = index;
      it.stopEvent();
    });
    li.mousedown(function(){
      it._click();
      it.hidden();
      it.stopEvent();
    });
  };
  it.bind = function (oElement,aContent,sKey,fOnSelect,fOnClose,sHead) {
    var i = 0,l = aContent.length;
    reg = new RegExp("(" + filter(sKey) + ")","gi");
    selected = 0;
    content = [];
    onSelect = fOnSelect;
    len = 0;
    lis = [];
    onClose = fOnClose;
    it.initTip();
    panel.html('');
    if(sHead) {
      head = $('<div/>');
      panel.append(head);
      head.html(sHead);
    }
    for(i;i < l;i++) {
      it.addItem(aContent[i]);
    }
    if(!lis.length) {
      it.addItem(sKey);
    }
    it.index(0);
    if(current == oElement) {
      return;
    }
    current = $(oElement);
    current && current.unbind('keydown');
    $(document.body).unbind('mouseup');
    current.bind('keydown',function(event){
      it.selection(event);
    });
    $(document.body).bind('mouseup',function(){
      it.hidden();
    });
  };
  it.stopEvent = function(){
    var ev = window.event ? window.event : (function(){
      var o = arguments.callee.caller;
      var e;
      var n = 0;
      while(o != null && n < 40) {
        e = o.arguments[0];
        if(e && (e.constructor == Event || e.constructor == MouseEvent)) {
          return e;
        }
        n++;
        o = o.caller;
      }
      return e;
    })();
    if(document.attachEvent){
      ev.cancelBubble = true;
      ev.returnValue = false;
    }else{
      ev.preventDefault();
      ev.stopPropagation();
    }
  };
  return it;
})();

mblog.Func.EncodeUtils = (function() {
  var g = {
    "<": "&lt;",
    ">": "&gt;",
    '"': "&quot;",
    "\\": "&#92;",
    "&": "&amp;",
    "'": "&#039;",
    "\r": "",
    "\n": "<br>"
  },
  a = /<|>|\'|\"|&|\\|\r\n|\n| /gi;
  var b = {};
  b.regexp = function(h) {
    return h.replace(/\}|\]|\)|\.|\$|\^|\{|\[|\(|\|\|\*|\+|\?|\\/gi,
    function(j) {
      j = j.charCodeAt(0).toString(16);
      return "\\u" + (new Array(5 - j.length)).join("0") + j;
    });
  };
  b.html = function(h, j) {
    j = j || g;
    return h.replace(a,
    function(m) {
      return j[m];
    });
  };
  return b;
})();

mblog.Func.bindAtToTextarea = (function(){
    var doc = document,format = mblog.Func.EncodeUtils.html,select = mblog.Func.PopUpCombo,selectionStart;
  var cssg = ["overflowY","height","width","paddingTop","paddingLeft","paddingRight","paddingBottom","marginTop","marginLeft","marginRight","marginBottom"];
  var font = "Tahoma,宋体",cssc = {
    fontFamily:font/*borderStyle:"solid",borderWidth:"0px",wordWrap:"break-word",fontSize:"16px",lineHeight:"25px",overflowX:"hidden"*/
  };
  var selectHead = '<div style="height:20px;color:#999999;padding-left:8px;padding-top:2px;line-height:18px;font-size:12px;Tahoma,宋体;">' + '想用@提到谁？' + "</div>";
  var isCss1 = false,ua = navigator.userAgent,r = /MSIE([0-9]{1,}[\.0-9]{0,})/.exec(ua);
  if(r && (r = parseFloat(RegExp.$1)) && r < 8) {
    isCss1 = true;
  }
  var hash = {
    "<":"&lt;",">":"&gt;",'"':"&quot;","\\":"&#92;","&":"&amp;","'":"&#039;","\r":"","\n":"<br>"," ":!isCss1?"<span style='white-space:pre-wrap;font-family:"+font+";'> </span>":"<pre style='overflow:hidden;display:inline;font-family:"+font+";word-wrap:break-word;'> </pre>"
  },fReg=/<|>|\'|\"|&|\\|\r\n|\n|/gi;
  var cssCell = {

  };
  var AjaxHasAbort = function (url,success,error) {
    var req,res,error;
    req = window.ActiveXObject ? new ActiveXObject("Microsoft.XMLHTTP") : new XMLHttpRequest();
    if(!req) {
      return;
    }
    req.onreadystatechange=function () {
      try{
        if(req.readyState == 4) {
          res = eval("("+req.responseText+")");
          success(res);
        }
      }catch(e) {
        return false;
      }
    };
    try{
      req.open("GET",url,true);
      req.send(null);
    }catch(e) {
      return false;
    }
    return req;
  };
  var doRequest = (function () {
    var req;
    return function (url,success,error) {
      if(req) {
        req.abort();
        req;
      }
      req = AjaxHasAbort(url,success,error);
    };
  })();
  var at = (function () {
    var it = {},current,panel,cache,lastCache,flag,content,nbody,reg,tu = mblog.Func.TextareaUtils,clock,reqed = {},validate = false,currentKey,keyChange = 0,items;
    reg = /@[^@\s]{1,20}$/g;
    it.onClose = function () {
      cache = null;
      lastCache = null;
      currentKey = null;
      setTimeout(function () {
        try{
          current.focus();
        }catch(e) {
        }
      },0);
    };
    it.onSelect = function (value) {
      var st = current.scrollTop;
      current.focus();
      tu.insertText(current,value.substring(0,value.indexOf("(") > 0 ? value.indexOf("("):value.length) + " ",selectionStart,currentKey.length);
      current.scrollTop = st;
    };
    it.setContent = function (value,last) {
      panel.style.height = current.clientHeight + "px";
      if(cache != value) {
        cache = value;
        //content.innerHTML = format(value,hash);
        content.innerHTML = format(value,hash);
      }if(lastCache!=last) {
        lastCache=last;
        nbody.innerHTML = format(last,hash);
      }if($.browser.safari) {
        panel.style.overflowY = $(current).getStyle("overflowY") == "hidden"?"hidden":"scroll";
      }else {
        panel.style.overflowY=(current.scrollHeight>current.clientHeight)?"auto":"hidden";
      }
    };
    it.parseJson = function(json){
      var data = [];
      for(var i = 0,len = json.length;i < len;i++){
        data.push(json[i].nickname);
      }
      return data;
    };
    it.initTip=function (json,key) {
      var data,len,i = 0,list = [],name,tmp = "background-color:#ebebeb;",point;
      if(data = it.parseJson(json) || []) {
        point= $(flag).pos();
        select.position(point[0],point[1],0,-(current.scrollTop-20));
        select.bind(current,data,currentKey,it.onSelect,it.onClose,selectHead);
        reqed[currentKey] = json;
        return;
      }
      select.hidden();
    };
    it.check=function () {
      var snap,snap = value = current.value.replace(/\r/g,""),key,len,html,param,last;
      selectionStart = tu.selectionStart(current);
      value = value.slice(0,selectionStart);
      if((key = value.match(reg)) && (key=key[0]) && /^@[a-zA-Z0-9\u4e00-\u9fa5_]+$/.test(key)) {
        key = key.slice(1);
        if(currentKey == key) {
          return
        }
        currentKey = key;
        last = snap.slice(selectionStart-currentKey.length,snap.length);
        value=value.slice(0,-currentKey.length-1);
        it.setContent(value,last);
        if(reqed[key]) {
          it.initTip(reqed[key],key);
          return
        }
        var tmpUrl = siteUrl;
        if (siteUrl.lastIndexOf('/index.php') != -1) {
            tmpUrl = siteUrl.substring(0, siteUrl.lastIndexOf('/index.php'));
        } else if (siteUrl.lastIndexOf('/?anu=') != -1) {
            tmpUrl = siteUrl.substring(0, siteUrl.lastIndexOf('/?anu='));
        }
        siteUrl = tmpUrl;
        doRequest(siteUrl+'/index.php?anu=at/suggestion&k=' + encodeURIComponent(key), function(json){
          it.initTip(json,key);
        },select.hidden);
        return;
      }
      select.hidden();
    };
    it.sleep = function (event) {
      var keyCode = event.keyCode;
      if(keyCode=="27") {
        return
      }
      clearTimeout(clock);
      clock = setTimeout(it.check,100);
    };
    it.bindEvent = function (oElement,b) {
      if(!b){
        $(oElement).unbind('keypress',function(event){
          it.sleep(event);
        }).unbind('keyup',function(event){
          it.sleep(event);
        }).unbind('mouseup',function(event){
          it.sleep(event);
        });
      }else{
        $(oElement).bind('keypress',function(event){
          it.sleep(event);
        }).bind('keyup',function(event){
          it.sleep(event);
        }).bind('mouseup',function(event){
          it.sleep(event);
        });
      }
    };
    it.rePosition=(function () {
      var clock,stop = function () {
        clearInterval(clock);
      };
      var flush = function () {
        try{
          if(!current) {
            return
          }
          point = $(current).pos();
          with(panel.style) {
            left=point[0]+"px";
            top=point[1]+"px";
          }
        }catch(e) {
          stop();
        }
      };
      return function () {
        stop();
        clock=setInterval(flush,100);
      };
    })();
    it.mirror = function (oStyleFix) {
      var i = 0,p,len = cssg.length,point,fix = 0,size = "14px",w;
      if($.browser.mozilla) {
        fix = -2;
      }
      if($.browser.safari) {
        fix = -6;
      }
      for(i;i<len;i++) {
        panel.style[cssg[i]] = $(current).getStyle(cssg[i]);
      }
      for(p in cssc) {
        panel.style[p] = current.style[p] = cssc[p];
      }
      for(p in oStyleFix) {
        panel.style[p] = current.style[p]=oStyleFix[p];
      }
      if(oStyleFix && oStyleFix.fontSize) {
        size = oStyleFix.fontSize;
      }
      hash[" "] = !isCss1 ? "<span style='white-space:pre-wrap;font-family:"+font+";'> </span>":"<pre style='overflow:hidden;display:inline;font-family:"+font+";word-wrap:break-word;'> </pre>";
      panel.style.width = ((parseInt(current.style.width)||current.offsetWidth)+fix)+"px";
      it.bindEvent(current,true);
      it.rePosition();
      return false;
    };
    it.cssCell = function(){
      var cssText = [],f = '.Atwho';
      cssText.push(f + '{width:160px;border:1px solid #ccc;background:#fff;padding:2px 2px;position:absolute;}');
      cssText.push(f + ' li {color:#666;height:21px;line-height:21px;overflow:hidden;padding:0 0 0 8px;cursor:pointer;}');
      cssText.push(f + ' li.cur {background:#eeeff6;}');
      it.createCss(cssText.join(''));
    };
    it.createCss = function(cssText){
      if($.browser.msie){
        var css = document.createStyleSheet();
        css.cssText = cssText;
      }else{
        var c = document.createElement('style');
        c.type = 'text/css';
        c.appendChild(document.createTextNode(cssText));
        document.getElementsByTagName('head')[0].appendChild(c);
      }
    };
    it.to = function (oElement,oStyleFix) {
      if(current == oElement) {
        return;
      }
      it.cssCell();
      if(!it.panel) {
        doc.body.appendChild(it.panel = panel = document.createElement('div'));
        panel.appendChild(it.content = content = document.createElement('span'));
        panel.appendChild(it.flag = flag = document.createElement('span'));
        panel.appendChild(it.nbody = nbody = document.createElement('span'));
        with(panel.style) {
          zIndex = -1000;
          position = "absolute";
        }
        flag.innerHTML = "@";
        flag.id = "at_flag_span";
        $(panel).setStyle("opacity",0);
      }
      current && it.bindEvent(current,false);
      (current = oElement) && it.mirror(oStyleFix);
    };
    return it;
  })();
  return function (oElement,oStyleFix) {
    oElement.style.fontFamily = font;
    $(oElement).bind('focus',function(){
      at.to(this,0);
    });
  };
})();