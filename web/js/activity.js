/**
 *   html ×ÖÌå
 */
//
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
 * activity toggle
 */
//$(function(){
//    $(".act_cose").on("click",function(){
//        var $cose=$(".cose");
//        if($cose.hasClass("hide")){
//            $cose.removeClass("hide");
//        }else{
//            $cose.addClass("hide");
//        }
//    });
//    $(".my_ok").on("click",function(){
//        $(".success_layer").addClass("hide");
//    })
//
//});

// pro_warp µÄ¸ß
document.addEventListener("DOMContentLoaded",function(){
    try{
        var width=document.body.scrollWidth,
            height=document.body.scrollHeight,
            pro_warp=document.getElementsByClassName("pro_warp")[0];
        pro_warp.style.width=width+"px";
        pro_warp.style.height=height+"px";
    }catch(e){

    }
});


document.addEventListener("DOMContentLoaded", function () {
    var act_cose = document.getElementsByClassName("act_cose")[0],
        cose = document.getElementsByClassName("cose")[0];
    act_cose.onclick = function () {
        if (surfing.hasClass(cose, "hide")) {
            surfing.removeClass(cose, "hide")
        } else {
            surfing.addClass(cose, "hide");
        }
    };

   try{
       var my_ok = document.getElementsByClassName("my_ok")[0],
           success_layer = document.getElementsByClassName("success_layer")[0];
       my_ok.onclick = function () {
           surfing.addClass(success_layer, "hide");
       };
   }catch(e){
       console.log();
   }

    try{
        var failure_btn=document.getElementsByClassName("failure_btn")[0],
            failure_layer=document.getElementsByClassName("failure_layer")[0];
        failure_btn.onclick=function(){
            surfing.addClass(failure_layer,"hide");
        }
    }catch(e){
        console.log();
    }

}, false);


var surfing = {};
//hasClass£ºDetermine if there is a style value
surfing.hasClass = function (curEle, cName) {
    var reg = new RegExp("(?:^| +)" + cName + "(?: +|$)", "g");
    return reg.test(curEle.className);
};

//addClass£ºTo add a class style value to an element
surfing.addClass = function (curEle, cName) {
    if (!this.hasClass(curEle, cName)) {
        var cc = curEle.className;
        curEle.className += (cc === "") ? cName : " " + cName;
    }
};

//removeClass£ºRemove style
surfing.removeClass = function (curEle, cName) {
    if (this.hasClass(curEle, cName)) {
        var reg = new RegExp("(?:^| +)" + cName + "(?: +|$)", "g");
        curEle.className = curEle.className.replace(reg, " ");
    }
};




