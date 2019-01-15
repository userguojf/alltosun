var themeCalendarImage = siteUrl+"/images/b_calendar.png";
if ($.datepicker) {
  $.datepicker.regional['']['closeText'] = '确定';
  $.datepicker.regional['']['prevText'] = '上个月';
  $.datepicker.regional['']['nextText'] = '下个月';
  $.datepicker.regional['']['currentText'] = '今天';
  $.datepicker.regional['']['monthNames'] = ['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月',];
  $.datepicker.regional['']['monthNamesShort'] = ['一月','二月','三月','四月','五月','六月','七月','八月','九月','十月','十一月','十二月',];
  $.datepicker.regional['']['dayNames'] = ['星期日','星期一','星期二','星期三','星期四','星期五','星期六',];
  $.datepicker.regional['']['dayNamesShort'] = ['周日','周一','周二','周三','周四','周五','周六',];
  $.datepicker.regional['']['dayNamesMin'] = ['日','一','二','三','四','五','六',];
  $.datepicker.regional['']['weekHeader'] = '周';
  $.datepicker.regional['']['hourText'] = '时';
  $.datepicker.regional['']['minuteText'] = '分';
  $.datepicker.regional['']['secondText'] = '秒';
  $.extend($.datepicker._defaults, $.datepicker.regional['']);
}

function datepicker(a) {
  var b = false;
  if (a.is(".datetimefield")) b = true;
  a.datepicker({
    showOn: "both",
    buttonImage: themeCalendarImage,
    buttonImageOnly: true,
    duration: "",
    time24h: true,
    stepMinutes: 1,
    stepHours: 1,
    showTime: b,
    dateFormat: "yy-mm-dd",
    altTimeField: "",
    beforeShow: function() {
      a.data("comes_from", "datepicker")
    },
    constrainInput: false
  });
  
  a.addClass("ui-datepicker-trigger");
}