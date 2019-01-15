function faceEdit(obj, txtAreaObj, containerObj)
{
  //add songrd  截取连接地址
  var tmpUrl = siteUrl;
  if (siteUrl.lastIndexOf('/index.php') != -1) {
    tmpUrl = siteUrl.substring(0, siteUrl.lastIndexOf('/index.php'));
  } else if (siteUrl.lastIndexOf('/?url=') != -1) {
    tmpUrl = siteUrl.substring(0, siteUrl.lastIndexOf('/?url='));
  }
  obj.jqfaceedit({
        txtAreaObj:txtAreaObj,
        containerObj:containerObj,
        top:27,
        left:-25,
        imageurl:tmpUrl+'/js/emotions/emotions/'
      });
}
 
function emotionsToHtml(obj)
{
  obj.emotionsToHtml({
    imageurl:siteUrl+'/js/emotions/emotions/'
  });
}