/**
 * sian表情js文件
 */
;(function(config){

///////////////////
// 闭包内的全局变量
///////////////////

  var emotionUrl = '';

  // 表情数据缓存
  var data = null;
  // 模板html缓存
  var template = '';
  // 所有初始化后的face对象
  var faceList = window.weiboFaceList = [];
  // 当前获取操的face表情对象
  var currFace = null;
  // 当前输入框
  var currTextarea = null;
  // 表情框的宽度
  var faceBoxW = 420;
  // 表情框箭头的默认left属性值
  var faceBoxPointDefaultLeft = 5;
  // 分类框的宽度 width - paddingleft - 2px
  var categoryTabW =  320 - 20 -1;
  // 当前的分类，默认
  var currCategoryId = 0;
  // 每个显示分页的list
  var pageCategoryList = [];
  // 当前显示的分类分页
  var currCategoryPage = 0;
  // 是否是第一次展示
  var isFirstShow = true;
  // 是否隐藏箭头当做弹框使用（ifram 不能撑开）
  var isHidePointer = false;
  // 解析后的结果缓存，会先从这里找
  var parsedCache = {};

  // IE
  var ua = navigator.userAgent.toLowerCase();
  var IE = /msie/.test(ua);


  /**
   * 全局的初始化函数
   */
  var faceInit = window.weiboFaceInit = function(config){
    emotionUrl = config.emotionUrl || '';
    data = config.data || '';
    if ( !emotionUrl && !data ) {
      error( 'emotionUrl or data is not configed.' );
    }
    init( render ); 
  };

  // 全局工具函数
  var faceFn = window.weiboFace = function(obj, textObj){
    
    if ( typeof obj == 'string' ) {
      obj = document.getElementById( trim( obj ) );
    }
    if ( typeof textObj == 'string' ) {
      textObj = document.getElementById( trim( textObj ) );
    }
    
    // 多个
    if ( (obj.length  && obj.length > 1) || (textObj.length && textObj.length > 1 ) ) {
      error( 'only one obj can used in weiboFace.' );
    }
    // jQuery 对象
    if ( window.jQuery && obj instanceof jQuery ) {
        obj = obj[0];
    }

    obj     = obj[0] ? obj[0] : obj;
    textObj = textObj[0] ? textObj[0] : textObj;
    
    // 查看是否绑定过
    for ( var i = 0; i < faceList.length; i++ ) {
      if ( faceList[i].face === obj ) {
        return true;
      }
    }
    
    var faceObj = new Face (obj, textObj);
    faceList.push(faceObj);
  };
  
  /**
   * 解析表情
   * @param {String} content
   */
  faceFn.parse = function( content ){
    content = content + '';
    if (!content) {
      return '';
    }
    //正则查找“[]”格式
    var reg = /(\[([\u4e00-\u9fa5]|[a-zA-Z])*\]).*?/g;
    var matches = content.match(reg);

    if ( matches != null ) {
      for( var i = 0; i < matches.length; i++) {
          var img = this.getImgByPhrase( matches[i] );
          if ( img ) {
            content = content.replace( matches[i], img );
          }
      }
    }
    return content;
  };
  
  /**
   * 根据表情文本获取图片
   */
  faceFn.getImgByPhrase = function( phrase ) {
    if ( !phrase ) {
      return '';
    }
    // cache
    if ( typeof parsedCache[phrase] !== 'undefined' ) {
      return parsedCache[phrase];
    }
    // data
    if ( data === null ) {
      error( 'waiting for data ready' );
    }
    for (var i = 0; i < data.emotions.length; i++) {
      for ( var j = 0; j < data.emotions[i].length; j++ ) {
        var face = data.emotions[i][j];
        if ( face.phrase == phrase ) {
          parsedCache[phrase] = '<img  src="'+face.url+'" />';
          return parsedCache[phrase];
        }
      }
    }
    parsedCache[phrase] = '';
    return parsedCache[phrase];
  };
  
  /**
   * 每一个face对象的构造函数
   */
  function Face (obj, textObj){
    this.face = obj[0] ? obj[0] : obj;
    this.textarea = textObj[0] ? textObj[0] : textObj;
    this.bindEvent();
  }

  /**
   * 初始化事件绑定
   * @return {[type]} [description]
   */
  Face.prototype.bindEvent = function(){
    var that = this;
    ready(function(){
      // 记录光标位置
      if ( IE ) {
        // 初始化
        var initRange = function() {
          that.textarea.caretPos = document.selection.createRange().duplicate();
        };
        initRange();
        // 不停的侦听
        addEvent( that.textarea, 'click keyup select', initRange);
      }
      // 表情点击事件
      addEvent( that.face, 'click', function(e){
        currFace = that.face;
        currTextarea = that.textarea;
        // 控制隐藏或者显示
        switchFaceState(e);
      });
    });
  };

//////////////////
// 以下为工具方法
//////////////////

  /**
   * 控制表情显示或者隐藏
   * @param {Object} event 触发的原生的event对象
   */
  function switchFaceState( event ) {
    // 应该关闭
    if ( isVisible() ) {
      hide();
    } else {
      show( event );
      if ( isFirstShow ) {
        generate();
      }
    }
  }

  /**
   * 初始化一些数据
   */
  function generate()
  {
    moveToCategoryList(0);
    generateCategoryPage();
    moveCategoryPage(0);
    isFirstShow = false;
  }

  /**
   * 插入表情
   */
  function insertFaceFn( event )
  {
    if ( !currTextarea ) {
      return;
    }

    var e = event || window.event;
    // 找出target
    var target = e.target || e.srcElement;
    var value = target.getAttribute( 'data-src' );
    var title = target.getAttribute( 'title' );
    title = title.replace('[', '').replace(']', '');
    console.log(title);
    hide();
    
    // div add image
    _insertimg(currTextarea, value, title);return;

    //var text = currTextarea.value;
    //console.log('qiqi', text, currTextarea, $(currTextarea).html());
    
    // textarea
    if (!text) {
      currTextarea.value = value;
      setCurPos(currTextarea, value.length);
      currTextarea.focus();
      if ( document.selection && document.selection.createRange ) {
        currTextarea.caretPos = document.selection.createRange().duplicate();
      }
    } else {
      insertAtCurPos( currTextarea, value );
      currTextarea.focus();
    }
  }

  /**
   * 获取表情的left、top属性
   * @param  {[type]} clickXY 点击的位置xy坐标
   */
  function getFaceBoxPosition( event )
  {
    event = event || window.event;
    var faceBox = document.getElementById( 'weiboface' );
    var xy = getXY( event );
    var viewport = getViewportSize();
    var faceboxLeft   = 0;
    var faceboxTop = 0;
    var faceBoxPointLeft = xy.x;

    var target  = event.target || event.srcElement;
    var rect = target.getBoundingClientRect();
    var bottom = rect.bottom;


    if ( ( viewport.w - xy.x ) < faceBoxW ) {
      faceboxLeft = viewport.w - faceBoxW - 8;
    } else {
      // random 5px
      faceboxLeft = xy.x - faceBoxPointDefaultLeft - 5;
    }
    // +5px
    faceboxTop = bottom + 3;

    // 修正sina iframe中不能撑开页面的问题
    var faceboxH = height( faceBox );
    if ( faceboxTop + faceboxH > viewport.h ) {
      faceboxTop = viewport.h - faceboxH - 5 ;
      isHidePointer = true;
    } else {
      isHidePointer = false;
    }

    return {
      top: faceboxTop + getScrollTop(),
      left: faceboxLeft,
      pointerLeft: xy.x - faceboxLeft - 3
    };
  }
  
  /**
   * 获取滚动高度
   */
  function getScrollTop()
  {
      if ( typeof window.scrollTop !== 'undefined' ) {
        return window.scrollTop;
      }
      var scrollTop=0;
      if(document.documentElement&&document.documentElement.scrollTop)
      {
          scrollTop=document.documentElement.scrollTop;
      }
      else if(document.body)
      {
          scrollTop=document.body.scrollTop;
      }
      return scrollTop;
  }
  
  /**
   * isVisible
   * 判断表情是否可见
   */
  function isVisible()
  {
    var faceBox = document.getElementById( 'weiboface' );
    return !( faceBox.style.display == 'none' );
  }

  /**
   * 隐藏
   */
  function hide()
  {
    var faceBox = document.getElementById( 'weiboface' );
    faceBox.style.display = 'none';
  }

  /**
   * 显示
   */
  function show( event )
  {
    // 触发的位置
    var faceBox = document.getElementById( 'weiboface' );
    var pointer = document.getElementById( 'weiboface-face-arrow' );
    var css = getFaceBoxPosition( event );
    faceBox.style.left = css.left + 'px';
    faceBox.style.top  = css.top  + 'px';
    pointer.style.left = css.pointerLeft + 'px';
    faceBox.style.display = 'block';
    if ( isHidePointer ) {
      pointer.style.display = 'none';
    } else {
      pointer.style.display = 'block';
    }
  }

  /**
   * 分类点击
   */
  function categoryListBtnClick( e )
  {
    e = e || window.event;
    var targetElement = e.target || e.srcElement;
    if ( !targetElement ) {
      error( 'categoryListBtnClick targetElement is null.' );
    }
    var categoryId = targetElement.getAttribute( 'data-category-id' );
    moveToCategoryList( categoryId );
  }

  /**
   * moveCategoryPage 移动分类page
   */
  function moveCategoryPage( toCategoryPage )
  {
    if ( toCategoryPage >=0 && toCategoryPage < pageCategoryList.length ) {
      var categoryBox = document.getElementById( 'weiboface-facebox-category-list' );
      var toCategoryPageIds = pageCategoryList[toCategoryPage];

      for ( var i=0; i < categoryBox.children.length; i++ ) {
        var tmpCategory   = categoryBox.children[i];
        var tmpCategoryId = tmpCategory.getAttribute( 'data-category-id' );
        if ( inArray( tmpCategoryId, toCategoryPageIds ) ) {
          tmpCategory.style.display = 'block';
        } else {
          tmpCategory.style.display = 'none';
        }
      }
      currCategoryPage = toCategoryPage;
    }
    updatePrevNextBtn();
  }

  /**
   * 点击分类后退
   * @return {[type]} [description]
   */
  function categoryPrev()
  {
    var toCategoryPage = currCategoryPage -1;
    moveCategoryPage( toCategoryPage );
  }

  /**
   * 点击分类前进
   */
  function categoryNext()
  {
    var toCategoryPage = currCategoryPage + 1;
    moveCategoryPage( toCategoryPage );
  }

  /**
   * 获取表情数据并初始化
   * @param  {Function} func 获取表情数据后的回调函数
   */
  function init(func)
  {
    if (data !== null) {
      func(data);
      return;
    }

    var request = getXHR();
    request.open('GET', emotionUrl);
    request.setRequestHeader('Content-Type', 'text/json;charset=UTF-8');
    request.onreadystatechange = function() {
      if ( request.readyState == 4 && request.status == 200 ) {
        data = parseJSON( request.responseText );
        if ( !data.category_list ) {
          error( 'get emotion failed' );
        }
        func();
      }
    };

    request.send();
  }

  /**
   * 渲染出表情的html
   */
  function render()
  {
    var categoryHtml = renderCategory(data.category_list);
    var emotionsHtml = renderEmotions(data.emotions);
    //var pagerHtml    = renderPager(data.emotions[0]);
    template = '\
    <div id="weiboface" style="display:none;">\
      <div class="weiboface-face-bg">\
        <div id="weiboface-face-arrow"></div>\
        <div id="weiboface-face-close"></div>\
        <div class="weiboface-face-title">\
          <a href="javascript:void(0);" class="weiboface-face-active"><span>常用表情</span></a>\
        </div>\
        <div class="weiboface-facebox">\
            <div style="display:none;" class="weiboface-facebox-title weiboface-clearfix">\
                <span class="weiboface-facebox-btn weiboface-right">\
                    <a href="javascript:void(0);" class="weiboface-facebox-btn-page" id="weiboface-category-prev" ><span><em id="weiboface-prev-em" class="weiboface-btn-prev"></em></span></a>\
                    <a href="javascript:void(0);" class="weiboface-facebox-btn-page" id="weiboface-category-next"><span><em id="weiboface-next-em" class="weiboface-btn-next"></em></span></a>\
                </span>\
                <ul class="weiboface-facebox-tab weiboface-left" id="weiboface-facebox-category-list">\
                    '+categoryHtml+'\
                </ul>\
            </div>\
            <div style="margin-top:5px;" class="weiboface-facebox-detail" id="weiboface-facebox-list">\
              '+emotionsHtml+'\
              <div class="weiboface-facebox-page" style="height:1px;">\
              </div>\
            </div>\
        </div>\
      </div>\
    </div>';

    // 文档就绪
    ready( documentReadyFn );
  }

  /**
   * 渲染分类
   * @param {Object} category 分类表情
   */
  function renderCategory(categoryList)
  {
    var html = '';
    for (var i = 0; i < categoryList.length; i++) {
      html += '<li data-category-id="' + i + '" style="display:none;">' + categoryList[i] + '</li>';
    }
    return html;
  }

  /**
   * 渲染所有表情列表
   */
  function renderEmotions(emotions)
  {
    var html = '';
    for (var i = 0; i < emotions.length; i++) {
      html += '<ul data-category-id="' + i + '" class="weiboface-faces-list weiboface-clearfix" '+( i > 0 ? 'style="display:none;"' : '' )+'>';
      for (var j = 0; j < emotions[i].length; j++) {
        html += '<li data-phrase="'+emotions[i][j].phrase+'" data-value="'+emotions[i][j].value+'" ><img title="'+emotions[i][j].phrase+'" data-phrase="'+emotions[i][j].phrase+'" data-value="'+emotions[i][j].value+'" data-src="'+emotions[i][j].url+'" width="22" height="22"></li>';
      }
      html += '</ul>';
    }
    return html;
  }

  /**
   * 渲染分页
   * @param  {int} categoryId [分类id]
   * @param  {int} count      [分类表情总数]
   */
  /*function renderPager(categoryId, count, currPage)
  {
    var html = '';
    var perPage = 60;
    var pages = Math.ceil( count / perPage );
    for (var i = 1; i < pages; i++) {
      html += '<a data-category-id="'+(categoryId)+'" href="javascript:void(0);" class="'+(i == currPage ? 'weiboface-current' : '')+'">' + i + '</a>';
    }
    return html;
  }*/

  /**
   * 
   */
  function documentReadyFn()
  {
    setTimeout( function(){
      var faceBox = document.getElementById( 'weiboface' );
      if ( !faceBox ) {

        document.body.appendChild( html( template ) );
        // 初始化关闭
        var closeBtn = document.getElementById( 'weiboface-face-close' );
        // 上一个分类组
        var prevBtn  = document.getElementById( 'weiboface-category-prev' );
        // 下一个分类组
        var nextBtn  = document.getElementById( 'weiboface-category-next' );
        // 分类容器
        var categoryListBtn = document.getElementById( 'weiboface-facebox-category-list' );
        // 表情容器
        var faceListBox     = document.getElementById( 'weiboface-facebox-list' );

        // 关闭
        addEvent( closeBtn, 'click', hide );
        // 后退分类
        addEvent( prevBtn, 'click', categoryPrev );
        // 前进分类
        addEvent( nextBtn, 'click', categoryNext );
        // 点击某个分类
        addEvent( categoryListBtn, 'click', categoryListBtnClick );
        // 点击某个表情
        addEvent( faceListBox, 'click',  insertFaceFn );

      }
      removeEvent(document, 'readystatechange', documentReadyFn);
    } );
  }

  /**
   * 滚动到某一个id
   */
  function moveToCategoryList( categoryId )
  {
    var categoryListBox = document.getElementById( 'weiboface-facebox-list' );
    var length = categoryListBox.children.length;
    for ( var i = 0; i < length; i++ ) {
      var tmpCategory = categoryListBox.children[i];
      if ( tmpCategory.getAttribute( 'data-category-id' ) == categoryId ) {
        // show
        var tmpChildren = tmpCategory.children;
        // 没有初始化过
        if ( !tmpChildren[0].children[0].getAttribute( 'src' ) ) {
          for (var j = 0; j < tmpChildren.length; j++ ) {
            var tmpSrc = tmpChildren[j].children[0].getAttribute( 'data-src' );
            setAttribute( tmpChildren[j].children[0] , 'src', tmpSrc);
          }
        }
        tmpCategory.style.display = 'block'; 
      }
      // 最后一个分页元素
      else if ( i < length -1 ) {
        tmpCategory.style.display = 'none'; 
      }
    }
    // currCategoryId
    moveToCategoryId( categoryId );

    // 是否超出
    var faceBox = document.getElementById( 'weiboface' );
    var viewport = getViewportSize();
    var faceboxTop = parseInt( faceBox.style.top );
    var faceboxH = height( faceBox );
    
    if ( faceboxTop + faceboxH > viewport.h ) {
      faceboxTop = viewport.h - faceboxH - 5;
      faceBox.style.top = faceboxTop + 'px';
      var pointer = document.getElementById( 'weiboface-face-arrow' );
      pointer.style.display = 'none';
    }
  }

  /**
   * 移动到某一个分类
   */
  function moveToCategoryId( categoryId )
  {
    var categoryBox = document.getElementById( 'weiboface-facebox-category-list' );
    var currCategory = null;
    for ( var i = 0; i < categoryBox.children.length; i++ ) {
      if ( categoryBox.children[i].nodeType != 1 ) { 
        continue;
      }
      var tmpCategoryId = categoryBox.children[i].getAttribute( 'data-category-id' );
      if (tmpCategoryId == categoryId) {
        currCategory = categoryBox.children[i];
        setAttribute( categoryBox.children[i], 'class', 'weiboface-current');
      } else {
        setAttribute( categoryBox.children[i], 'class', '');
      }
    }
  }

  /**
   * 移动箭头的切换的颜色
   */
  function updatePrevNextBtn( )
  {
    var prevEm = document.getElementById( 'weiboface-prev-em' );
    var nextEm = document.getElementById( 'weiboface-next-em' );
    // weiboface-btn-prev-none
    // weiboface-btn-next-none
    // weiboface-btn-prev
    // weiboface-btn-next
    if ( currCategoryPage == 0 ) {
      // prev disabled
      setAttribute( prevEm, 'class', 'weiboface-btn-prev-none' );
      setAttribute( nextEm, 'class', 'weiboface-btn-next' );
    }
    // 末尾 
    else if ( currCategoryPage == pageCategoryList.length - 1 ) {
      setAttribute( prevEm, 'class', 'weiboface-btn-prev' );
      setAttribute( nextEm, 'class', 'weiboface-btn-next-none' );
    }
    else {
      setAttribute( prevEm, 'class', 'weiboface-btn-prev' );
      setAttribute( nextEm, 'class', 'weiboface-btn-next' );
    }
  }

  /**
   * 根据字符串创建dom元素
   * @param  {String} html
   * @return {DomObj}
   */
  function html(html)
  {
    var tmpDiv = document.createElement( 'div' );
    var frag   = document.createDocumentFragment();
    tmpDiv.innerHTML = html;
    while( tmpDiv.firstChild ) {
      frag.appendChild( tmpDiv.firstChild );
    }
    return frag;
  }

  /**
   * 设置属性
   */
  function setAttribute( element, attr, value )
  {
    if ( !element ) return false;
    // 如是是设置class
    if ( attr == 'class' ) {
        element.className = value;
      return;
    }
    element.setAttribute(attr, value);
  }

  /**
   * 根据宽度对分类分页
   */
  function generateCategoryPage()
  {
    var categoryBox = document.getElementById( 'weiboface-facebox-category-list' );
    
    var tmpWidth = 0;
    var tmpPage  = [];
    for ( var i=0; i < categoryBox.children.length; i++ ) {
      var tmpCategory = categoryBox.children[i];
      
      tmpWidth += width( tmpCategory ) ;
      if ( tmpWidth < categoryTabW ) {
        tmpPage.push( tmpCategory.getAttribute( 'data-category-id' ) );
      } else {
        pageCategoryList.push( tmpPage );
        tmpWidth = 0;
        tmpPage  = [];
      }
    }
  }

  /**
   * 获取元素的宽度
   * @param  {[type]} element [description]
   * @return {[type]}         [description]
   */
  function width( element )
  {
    if ( !element ) { return 0; }
    var display = element.style.display;
    element.style.display = 'block';
    var rect = element.getBoundingClientRect();
    element.style.display = display;
    return rect.width || ( rect.right - rect.left );
  }

  /**
   * 获取元素高度
   * @param  {[type]} element [description]
   * @return {[type]}         [description]
   */
  function height( element )
  {
    if ( !element ) { return 0; }
    var display = element.style.display;
    element.style.display = 'block';
    var rect = element.getBoundingClientRect();
    element.style.display = display;
    return rect.height || ( rect.bottom - rect.top );
  }

  /**
   * 获取前面一个元素
   * @return {[type]} [description]
   */
  function prevElement( element )
  {
    if ( !element ) {
      return null;
    }
    while(element.previousSibling) {
      if ( element.previousSibling.nodeType == 1) {
        return element.previousSibling;
      }
      element = element.previousSibling;
    }
    return null;
  }

  /**
   * 获取前面一个元素
   * @return {[type]} [description]
   */
  function nextElement( element )
  {
    if ( !element ) {
      return null;
    }
    while(element.nextSibling) {
      if ( element.nextSibling.nodeType == 1) {
        return element.nextSibling;
      }
      element = element.nextSibling;
    }
    return null;
  }

  /**
   * 获取事件点击的视窗坐标
   * @return {Object} { x: int, y: int }
   */
  function getXY(event) 
  {
    event = event || window.event;
    var position = {
      x:0,
      y:0
    };
    // 非触摸
    if(!event.touches || !event.touches.length) {
        position.x = event.clientX;
        position.y = event.clientY;
    }
    // touch
    else {
        position.x = event.touches[0].clientX;
        position.y = event.touches[0].clientY;
    }
    return position;
  }

  /**
   * 获取视窗宽高
   */
  function getViewportSize()
  {
    if ( window.innerWidth ) {
      return { w: window.innerWidth, h: window.innerHeight };
    }
    if ( document.compatMode == 'CSS1Copmat' ) {
      return {
        w: document.documentElement.clientWidth,
        h: document.documentElement.clientHeight
      };
    }
    return {
      w: document.body.clientWidth,
      h: document.body.clientHeight
    };
  }

  /**
   * 获取xmlhttprequest对象
   */
  function getXHR()
  {
    if (!window.XMLHttpRequest) {
      try {
        return new ActiveXObject("Msxml2.XMLHTTP.6.0");
      } catch (e1) {
        try {
          return new ActiveXObject("Msxml2.XMLHTTP.3.0");
        } catch(e2) {
          alert("您的浏览器版本过低，请升级浏览器获得更好的浏览体验");
          error('init xhr error.');
        }
      }
    }
    return new window.XMLHttpRequest();
  }

  /**
   * attach event
   * @param   node    element
   * @param   string  types
   * @param   object  callback
   */
  function addEvent(element, types, callback) {
    if ( !element ) {
      error( 'addEvent element is null.' );
    }
    types = types.split(" ");
    for(var t= 0, len=types.length; t<len; t++) {
      if(element.addEventListener){
        element.addEventListener(types[t], callback, false);
      }
      else if(document.attachEvent){
        element.attachEvent("on"+ types[t], callback);
      }
    }
  }

  /**
   * detach event
   * @param   node    element
   * @param   string  types
   * @param   object  callback
   */
  function removeEvent(element, types, callback) {
    if ( !element ) {
      error( 'addEvent element is null.' );
    }
    types = types.split(" ");
    for(var t= 0,len=types.length; t<len; t++) {
      if(element.removeEventListener){
        element.removeEventListener(types[t], callback, false);
      }
      else if(document.detachEvent){
        element.detachEvent("on"+ types[t], callback);
      }
    }
  }

  /**
   * trim
   */
  function trim(str)
  {
    if ( String.prototype.trim ){
      return String.prototype.trim.call( str );
    }
    var rtrim = /^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g;
    return ( str === null ? "" : ( str + "" ).replace( rtrim, "" ) );
  }

  /**
   * 解析JSON
   */
  function parseJSON( str ) 
  {
    if ( window.JSON && window.JSON.parse ) {
      return window.JSON.parse( str );
    }
    if ( str === null ) {
      return str;
    }
    if ( typeof str === "string" ) {
      // Make sure leading/trailing whitespace is removed (IE can't handle it)
      str = trim( str );
      if ( str ) {
        // JSON RegExp
        var rvalidchars = /^[\],:{}\s]*$/,
        rvalidbraces = /(?:^|:|,)(?:\s*\[)+/g,
        rvalidescape = /\\(?:["\\\/bfnrt]|u[\da-fA-F]{4})/g,
        rvalidtokens = /"[^"\\\r\n]*"|true|false|null|-?(?:\d+\.|)\d+(?:[eE][+-]?\d+|)/g;
        // Make sure the incoming data is actual JSON
        // Logic borrowed from http://json.org/json2.js
        if ( rvalidchars.test( str.replace( rvalidescape, "@" )
          .replace( rvalidtokens, "]" )
          .replace( rvalidbraces, "")) ) {

          return ( new Function( "return " + str ) )();
        }
      }
    }

    error('parseJSON Error.');
  }

  /**
   * document ready
   */
  function ready(callback)
  {
    if ( document.readyState === 'complete' ) {
      callback();
      return;
    }
    addEvent( document, 'readystatechange', function() {
      if ( document.readyState === 'complete' ) {
        callback();
      }
    });
    // addEvent( window, 'load', callback );
  }

  /**
   * inArray
   */
  function inArray( value, array, strict )
  {
    for ( var i = 0; i < array.length; i++ ) {
      var exp = ( strict == true ) ?  ( array[i] === value ) : ( array[i] == value );
      if ( exp === true ) {
        return true;
      }
    }
    return false;
  }
  
  // 在光标处插入html 比如图片
  function _insertimg(textObj, str, title) {
    $(textObj).focus();
    str = '<img title="'+title+'" class="js_faceImg" src="'+str+'">';
    var selection = window.getSelection ? window.getSelection()
        : document.selection;
 
    var range = selection.createRange ? selection.createRange() : selection
        .getRangeAt(0);
    if (!window.getSelection) {
  
      textObj.focus();
      var selection = window.getSelection ? window.getSelection()
          : document.selection;
      var range = selection.createRange ? selection.createRange() : selection
          .getRangeAt(0);
      range.pasteHTML(str);
      range.collapse(false);
      range.select();
    } else {
      $(textObj).focus();
      
      range.collapse(false);
      var hasR = range.createContextualFragment(str);
      var hasR_lastChild = hasR.lastChild;
      while (hasR_lastChild && hasR_lastChild.nodeName.toLowerCase() == "br"
          && hasR_lastChild.previousSibling
          && hasR_lastChild.previousSibling.nodeName.toLowerCase() == "br") {
        var e = hasR_lastChild;
        hasR_lastChild = hasR_lastChild.previousSibling;
        hasR.removeChild(e);
      }
      
      range.insertNode(hasR);
      if (hasR_lastChild) {
        range.setEndAfter(hasR_lastChild);
        range.setStartAfter(hasR_lastChild);
      }
      selection.removeAllRanges();
      
      selection.addRange(range);
    }
    
    if ($(".js_replyContent").length > 0) {
      $(".js_replyContent").trigger('keyup');
    }
  }

  /**
   *  在光标位置处插入文本
   */
  function insertAtCurPos( textObj, textFeildValue )
  {

    if ( document.all && textObj.createTextRange && textObj.caretPos ) {
      var caretPos = textObj.caretPos;
      textObj.focus();
      caretPos.text = caretPos.text.charAt(caretPos.text.length - 1 ) == '' ? textFeildValue + '': textFeildValue;
      textObj.blur();
    }
    else if (textObj.setSelectionRange) {
      var rangeStart = textObj.selectionStart;
      var rangeEnd = textObj.selectionEnd;
      var tempStr1 = textObj.value.substring(0, rangeStart);
      var tempStr2 = textObj.value.substring(rangeEnd);
      textObj.value = tempStr1 + textFeildValue + tempStr2;
      textObj.focus();
      var len = textFeildValue.length;
      textObj.setSelectionRange(rangeStart + len, rangeStart + len);
      //textObj.blur();
    }
    else {
      textObj.value += textFeildValue;
    }
  }

  /**
   * 定位光标
   * @param {[type]} textarea [description]
   * @param {[type]} pos      [description]
   */
  function setCurPos ( textarea , pos ) {
    textarea.focus();
    if (textarea.setSelectionRange) {
      textarea.setSelectionRange(pos, pos);
    } else if (textarea.createTextRange) {
      var range = textarea.createTextRange();
      range.collapse(true);
      range.moveEnd('character', pos);
      range.moveStart('character', pos);
      range.select();
    }
  }

  /**
   * error func
   */
  function error(str)
  {
    throw new Error(str);
  }
})();