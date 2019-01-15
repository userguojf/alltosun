function Sticky(a, b) {
    return Sticky.currentIsSupport ? void console.log("您的浏览器支持 sticky,  可以直接设置 css") : (this.options = $.extend({parentElement: $(document.body),top: 0}, b || {}), this.element = a, this.isBody = this.options.parentElement[0] == document.body, this.elementOffsetTop = this.isBody ? a.offset().top : 0, this.height = a.height(), this.parentOffset = this.isBody ? {top: 0,left: 0,height: $(window).height(),width: $(window).width()} : this.options.parentElement.offset(), this.isFixed = 0, this.ongoing = 1, void this.init())
}
Sticky.prototype = {constructor: Sticky,init: function() {
        this.regEvent()
    },regEvent: function() {
        var a = this;
        $(window).scroll(function() {
            1 == a.ongoing && a.scroll(document.body.scrollTop)
        })
    },scroll: function(a) {
        var b = this.parentOffset;
        0 == this.isFixed && a > b.top + this.elementOffsetTop && a < b.top + b.height + this.elementOffsetTop && (this.isFixed = 1, this.element.css({position: "fixed",top: this.options.top + "px"})), 1 == this.isFixed && 0 == this.isBody && b.top + b.height + this.elementOffsetTop - a < this.height && this.element.css({top: -(this.height - (b.top + b.height - a)) + "px"}), 1 == this.isFixed && (a <= b.top + this.elementOffsetTop || 0 == this.isBody && a > b.top + b.height + this.elementOffsetTop) && (this.isFixed = 0, this.element.css({position: "static"}))
    },update: function() {
        this.elementOffsetTop = this.isBody ? this.element.offset().top : 0, this.height = this.element.height(), this.parentOffset = this.isBody ? {top: 0,left: 0,height: $(window).height(),width: $(window).width()} : this.options.parentElement.offset()
    },stop: function() {
        this.ongoing = 0
    },start: function() {
        this.ongoing = 1
    }};
Sticky.currentIsSupport = function() {


    var a = document.createElement("DIV");
    return a.innerHTML = '<div class="J_Sticky_Support_test" style="position:-webkit-sticky;position:sticky;"></div>', -1 != String($(a).find(".J_Sticky_Support_test").css("position")).indexOf("sticky")
}();

