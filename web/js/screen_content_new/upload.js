/**
 * html5选择照片后的预览(需要特定的class和ID 如：js_upFileBox， js_upFile)
 * @param evt
 * @returns {Boolean}
 */
function handleFileSelect(obj, callback)
{
    //alert(FileReader)
    if (typeof FileReader == "undefined") {
        return false;
    }
    var thisClosest = obj.closest('.js_perUpWrap');
    var thisOuter = thisClosest.find('.js_perUpOuter');
    if (typeof thisClosest.length == "undefined" || typeof thisOuter.length == "undefined") {
        return;
    }

    var files = obj[0].files;
    var f = files[0];
    if (!isAllowFile(f.name)) {
        alert("请上传常规格式的图片,如：jpg, png等");
        return false;
    }

    // 如果浏览器支持html5 FileReader
    if (typeof FileReader != 'undefined') {
        var reader = new FileReader();
        reader.onload = (function(theFile){
            return function (e) {
                var tmpSrc = e.target.result;
                if (tmpSrc.lastIndexOf('data:base64') != -1) {
                    tmpSrc = tmpSrc.replace('data:base64', 'data:image/jpeg;base64');
                } else if (tmpSrc.lastIndexOf('data:,') != -1) {
                    tmpSrc = tmpSrc.replace('data:,', 'data:image/jpeg;base64,');
                }

                doFileSelected(tmpSrc, thisOuter, callback);
            };
        })(f)
        reader.readAsDataURL(f);
        //alert('可以的')
    } else {
        //alert('不可以');
        var tmpSrc = siteUrl+"/images/admin2/pic_select_defalut.png";
        doFileSelected(tmpSrc, thisOuter, callback);
    }
}

// 选择图片后的操作
function doFileSelected(tmpSrc, thisOuter, callback)
{
    var img = '<img src="'+tmpSrc+'"  class="do_img"/>';

    thisOuter.find('.js_perUpAdd').hide();
    thisOuter.find('.js_perUpChange').show();
    thisOuter.find('img').remove();
    thisOuter.prepend(img);

    var showId = thisOuter.attr('data-show-id');
    if (showId) {
        if ($("#"+showId).length >= 1) {
            $("#"+showId).find('img').attr('src', tmpSrc);

        } else if ($("."+showId).length >= 1) {
            $("."+showId).each(function(i){
                $(this).html(img);
            });
        }
    }
    if (typeof(callback) != 'undefined') {
        callback(tmpSrc);
    }
}