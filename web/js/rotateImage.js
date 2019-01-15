/**
 * 图片旋转模块
 * $Id: rotateImage.js 69676 2013-09-26 13:46:43Z liw $
 */
define(function(require, exports, module){

  module.id = 'rotateImage';

  // jQueyr, 公共函数库
  var $ = require('jquery'), common = require('jquery.func');

  // 是否IE8 以下
  var isIEOld    = $.browser.msie && $.browser.msie.version <= 8;
  // 是否支持css3 transform
  var isCss3Suport = (function(temp) {
      var props = ['transformProperty', 'WebkitTransform', 'MozTransform', 'OTransform', 'msTransform'];
      for ( var i in props ) if (temp.style[ props[i] ] !== undefined) return true;
      return false;
    })(document.createElement('div'));

  /**
   * 向右转动
   * @param {jQueryObj, domObj, idStr} img 要旋转的图片：
   *                            可以是jquery选择后的对象，原始dom对象和字符串id属性
   * @param {Number} maxWidth 图片最大宽度，默认图片宽度
   * @param {Number} maxHeight 图片的最大高度，默认图片高度
   * @param {String} fixHClass 高度修正的类名
   */
  exports.rotateRight = function(img, maxWidth, maxHeight, fixHClass)
  {
    img = checkImg(img);
    // 初始化
    if (!img.data('rotate_ori_width')) {
      initImg(img, function(){
        rotate(img, 90, maxWidth, maxHeight, fixHClass);
      })
      return img;
    }

    var deg = img.data('rotate_deg') + 90;
    if (deg >= 360) deg = 0;
    rotate(img, deg, maxWidth, maxHeight, fixHClass);

    return img;
  };


  /**
   * 向左转动
   * @description 参数同上
   */
  exports.rotateLeft = function(img, maxWidth, maxHeight, fixHClass)
  {
    img = checkImg(img);
    // 初始化
    if (!img.data('rotate_ori_width')) {
      initImg(img, function(){
        rotate(img, 270, maxWidth, maxHeight, fixHClass);
      })
      return img;
    }

    var deg = img.data('rotate_deg') - 90;
    if (deg < 0) deg = 270;
    rotate(img, deg, maxWidth, maxHeight, fixHClass);

    return img;
  };

  /**
   * 恢复
   * @description 参数同上
   */
  exports.rotateDefault = function(img)
  {
    img = checkImg(img);
    // 初始化
    if (!img.data('rotate_ori_width')) {
      return img;
    }
    rotate(img, 0);
    return img;
  };

  /**
   * 旋转
   * @param fixHClass 高度修正的类名
   */
  function rotate(img, deg, maxWidth, maxHeight, fixHClass)
  {

    img.data('rotate_deg', deg);
    img.attr('data-rotate_deg', deg);

    if (isCss3Suport) {
      // @fixme
    } else if (isIEOld || 1) {
      var deg2radians = Math.PI * 2 / 360;
      var rad = deg * deg2radians;
      var costheta = Math.cos(rad);
      var sintheta = Math.sin(rad);
      var M11 = costheta;
      var M12 = -sintheta;
      var M21 = sintheta;
      var M22 = costheta;
      img[0].style.filter = "progid:DXImageTransform.Microsoft.Matrix(M11="+M11+",M12="+M12+",M21="+M21+",M22="+M22+",SizingMethod='auto expand')";

    } else {
      // @todo
    }

    // type=0 正常，type=1 横竖颠倒
    var type = deg % 180 == 0 ? 0 : 1,
      sizeH = type == 0 ? 'height' : 'width',
      sizeW = sizeH == 'height' ? 'width' : 'height'
      cssObj = {};
    
    img.attr('data-rotate_type', type);

    // 缩放图片适应大小
    var width  = type == 0 ? img.data('rotate_ori_width') : img.data('rotate_ori_height');
    var height = type == 0 ? img.data('rotate_ori_height') : img.data('rotate_ori_width'),
    maxWidth = maxWidth || img.data('rotate_ori_width');
    maxHeight = maxHeight || img.data('rotate_ori_height');
    
    var newW = width, newH = height;
    
    if (newW > maxWidth) {
      newW  = maxWidth;
      newH  = newW / width * height;
    }
    if (newH > maxHeight) {
      newH  = maxHeight;
      newW  = newH / height * width;
    }
    cssObj[sizeW] = newW + 'px';
    cssObj[sizeH] = newH + 'px';

    //debugger;
    if (fixHClass) {
      img.closest('.'+fixHClass).height(newH);
    }

    if (isCss3Suport) {
      // 计算top，变形原点与原来重合
      var tmpH = cssObj['height'].replace('px', '');
      /*if (tmpH != maxHeight) {
        cssObj['top'] = (maxHeight - tmpH)/2 + 'px';
      } else {
        cssObj['top'] = 0;
      }*/
      // css3居中
      img.parent().css('text-align', 'left');
    } else {
      // left
      if(newW < maxWidth) {
        cssObj['left'] = (maxWidth - newW)/2 + 'px';
      } else {
        cssObj['left'] = 0;
      }
      /*if (newH < maxHeight) {
        cssObj['top'] = (maxHeight - newH)/2 + 'px';
      } else {
        cssObj['top'] = 0;
      }*/
      // Ie居左
      img.parent().css('text-align', 'left');
    }

    img.css(cssObj);
    
    if (isCss3Suport) {
      var cssObj = {};
      if (deg == 0) {
        cssObj.left  = 0;
        cssObj.top = 0;
      } else if (deg == 90) {
        cssObj.left = newW;
        cssObj.top = 0;
      } else if (deg == 180) {
        cssObj.left = newW;
        cssObj.top = newH;
      } else if (deg == 270) {
        cssObj.top = newH;
        cssObj.left = 0;
      }
      $.extend(cssObj, {
        'transform-origin': '0% 0%',
        '-ms-transform-origin': '0% 0%',
        '-webkit-transform-origin': '0% 0%',
        'transform': 'rotate('+deg+'deg)',
        '-ms-transform': 'rotate('+deg+'deg)',
        '-webkit-transform': 'rotate('+deg+'deg)'
      });
      
      // 居中
      if(newW < maxWidth) {
        cssObj.left += (maxWidth - newW)/2;
      }
      /*if (newH < maxHeight) {
        cssObj.top += (maxHeight - newH)/2;
      }*/
      if (cssObj.left) cssObj.left += 'px';
      if (cssObj.top) cssObj.top += 'px';
      
      img.css(cssObj);
    }
    
    return img;
  }

  /**
   * 初始化图片
   */
  function initImg(img, cb)
  {
    common.imgReady(img.attr('src'), function(){
      img.data('rotate_ori_width', this.width)
        .data('rotate_ori_height', this.height)
        .data('rotate_deg', 0)
        .css({
          'position': 'relative',
          'background': 'transparent'
        });
      if (cb) cb(img);
    });
  }

  /**
   * 检查img参数
   * @return {jQueryObj} 返回jquery包裹对象
   */
  function checkImg(img)
  {
    if (!img || (img.length && img.length > 1)) {
      throw new Error('rotateImage.rotateRight error: param img type is incorrect');
    }
    if ('string' == typeof img) {
      img = $('#'+img);
    } else if ('undefined' == typeof img.length) {
      img = $(img);
    }
    if (img.length != 1) {
      throw new Error('rotateImage.rotateRight error: img not found');
    }
    return img;
  }
});