var JLib = {};
JLib.Klass = function(parent) {
    var klass = function() {
        this.init.apply(this, arguments);
    };

    // 改变 klass 的原型
    if (parent) {
        var subclass = function() {};
        subclass.prototype = parent.prototype;
        klass.prototype = new subclass;
    }

    klass.prototype.init = function() {};

    // 定义 prototype 的别名
    klass.fn = klass.prototype;

    // 定义类的别名
    klass.fn.parent = klass;

    // 给类添加属性
    klass.extend = function(obj) {
        var extended = obj.extended || obj.setup;

        delete obj.included;
        delete obj.extended;
        delete obj.setup;

        for (var i in obj)
            this[i] = obj[i];
        if (extended) extended.apply(this);
        delete extended;
    };

    // 给实例添加属性
    klass.include = function(obj) {
        var included = obj.included || obj.setup;

        delete obj.included;
        delete obj.extended;
        delete obj.setup;

        for (var i in obj)
            this.fn[i] = obj[i];
        if (included) included.apply(this);
    };

    // 添加一个 proxy 函数
    klass.proxy = function(func) {
        var self = this;
        return (function() {
            return func.apply(self, arguments);
        });
    };

    // 在实例中也添加这个函数
    klass.fn.proxy = klass.proxy;

    return klass;
};

JLib.Utils = new JLib.Klass;
JLib.Utils.extend({
    parseURL: function(url) {
        var a = document.createElement('a');
        a.href = url;
        return {
            source: url,
            protocol: a.protocol.replace(':', ''),
            host: a.hostname,
            port: a.port,
            query: a.search,
            params: (function() {
                var ret = {},
                    seg = a.search.replace(/^\?/, '').split('&'),
                    len = seg.length,
                    i = 0,
                    s;
                for (; i < len; i++) {
                    if (!seg[i]) {
                        continue;
                    }
                    s = seg[i].split('=');
                    ret[s[0]] = s[1];
                }
                return ret;
            })(),
            file: (a.pathname.match(/\/([^\/?#]+)$/i) || [, ''])[1],
            hash: a.hash.replace('#', ''),
            path: a.pathname.replace(/^([^\/])/, '/$1'),
            relative: (a.href.match(/tps?:\/\/[^\/]+(.+)/) || [, ''])[1],
            segments: a.pathname.replace(/^\//, '').split('/')
        };
    },
    alertMsg : function(str,type,focusObj){
        $.layer({
            img: '<s class="'+ type +'"></s>',
            prize_level:0,
            content:str,
            leftbtn: {
                text: "确认",
                callback:function(){
                    $.layer.close();
                    if(!!focusObj){
                        focusObj.focus().addClass('errorinput');
                    }
                }
            }
        });
    },
    get : function(url, callback, params) {
      // 抽奖操作,请求中奖信息
      if (params == undefined) {
        params = [];
      }
      $.post(url, params,  function(json){
        callback(json);
      }, 'json');
    }
});

var Game = new JLib.Klass;

Game.include({
    api4load: siteUrl+"/credit_lottery/show_prize",

//  api4play: "/match_prize/togame",
    api4play: siteUrl+"/credit_lottery/lottery",
    api4checkCredit : siteUrl+'/credit_lottery/check',
    clickTime:0,

    el: "#fortune",
    selectCredit : function() {
        return $('.selectCredit');
    },
    init: function(params) {

        var urlObj = JLib.Utils.parseURL(location.href);
        //var a = JSON.stringify(urlObj)
        //alert(a);
        //alert(urlObj.segments[1]);
        

        this.params = {
            //gameid: 1,
            //matchid: $.isNumeric(urlObj.params.id) ? urlObj.params.id : "",
            //serid:$.isNumeric(urlObj.params.serid) ? urlObj.params.serid : ""
            //lotteryid:$.isNumeric(urlObj.params.lottery_id) ? urlObj.params.lottery_id : "0"
            //lotteryid: if (urlObjsegments[1])
            //lotteryaction:urlObjsegments[1] == undefined ? '' : urlObjsegments[1]
        };
        if (urlObj.segments[1] == undefined || urlObj.segments[1] == '') {
          this.params.lotteryid = '1';
        } else if (urlObj.segments[1] == 'week') {
          this.params.lotteryid = '3';
        } else if (urlObj.segments[1] == 'all') {
          this.params.lotteryid = '4';
        }

        this.box = [];
        this.result = {};

        /*if (this.params.matchid) {*/
            this.g = {
                curIndex: 0, // 开始位置
                endIndex: 0, // 结束位置/中奖位置
                showList: [1],
                jumpStep: 1,
                cycle: 10,
                boxTotal: 0,
                maxBoxTotal: 12
            };
            this.data();
            
        /*} else {
            JLib.Utils.alertMsg("您没有猜中的赛事记录！", "err");
            return false;
        }*/
    },

    data: function() {
      /*JLib.Utils.save(this.api4load, this.proxy(this.render), {
        params: this.params
    });*/
      var that = this;
      JLib.Utils.get(this.api4load+"?lottery_id="+this.params.lotteryid, function(data){
        that.render(data);
      });
  },

    render: function(data) {
        data.result = data.result || [];
        if (!data.errcode && data.result.gameconfig.length === this.g.maxBoxTotal) {
            var that = this;
            this.game = $(this.el);
            this.getFortuneBox(data.result.gameconfig);
            this.g.boxTotal = this.box.length;
            this.game.find(".playbtn a:not(.over)").click(this.proxy(this.play));
            this.selectCredit().click(function(){
                that.play2($(this).val());
                
            });
        } else {
            JLib.Utils.alertMsg("幸运转盘游戏加载失败！", "err");
        }
    },
    
    play2: function(credit) {
        var that = this;
        JLib.Utils.get(that.api4play+"?lottery_id="+that.params.lotteryid + '&bet_credit='+credit, function(data) {
            //alert(JSON.stringify(data));
            // console.log(data);
            if (!data.errcode && (!!data.result)) {
              //JLib.Utils.setUserInfo({"goldchg":"-"+that.params.point});
              that.result = data.result;
              //JLib.Utils.setUserInfo({"goldchg":that.result.gamebalance});
              that.getJumpStep(data.result.side);
              that.run();
            } else {
              alert(data.msg,"err");
              $(this).removeClass("over");
            }
          });
    },
    play: function() {
      // 连续点击判断
      var myDate = new Date();
      var time = myDate.getTime();
      if (this.clickTime == 0) {
        this.clickTime = time;
      } else {
        var diffTime = time - this.clickTime;
        if (diffTime < 2000) {
          this.clickTime = time;
          return false;
        } else {
          this.clickTime = time;
        }
      }
      
      // 判断积分是否
      var that = this;
      JLib.Utils.get(this.api4checkCredit+"?lottery_id="+this.params.lotteryid, function(data) {
        if (data.info != 'ok') {
          if (data.action == 'no login') {
            show_login_box();
            return false;
          } else {
            JLib.Utils.alertMsg(data.info, "err");
            return false;
          }
        } else {
          $this = that.game.find(".playbtn a");
          if ($this.hasClass("over")) return false;
          
          if (that.params.lotteryid == 1) {
              $('#selectCreditBox').show();
              $('.mayer-pop').show();
          } else {
              JLib.Utils.get(that.api4play+"?lottery_id="+that.params.lotteryid, function(data) {
                //alert(JSON.stringify(data));
                // console.log(data);
                if (!data.errcode && (!!data.result)) {
                  //JLib.Utils.setUserInfo({"goldchg":"-"+that.params.point});
                  that.result = data.result;
                  //JLib.Utils.setUserInfo({"goldchg":that.result.gamebalance});
                  that.getJumpStep(data.result.side);
                  that.run();
                } else {
                  alert(data.msg,"err");
                  $this.removeClass("over");
                }
              });
          }
        }
      });
  },
  
    getJumpStep: function(pos) {
        var result = null;
        //pos = pos || "" +"";
        result = this.game.find("li[data\\-flag='" + pos + "']");
        if (!result.length) {
            result = this.game.find("li[data\\-flag='thanks']");
        }

        if (result.length > 1) {
            pos = Math.floor(Math.random() * result.length);
            result = result.eq(pos);
        }

        for (var l = this.box.length, step = 0; step < l; ++step) {
            if (this.box[step].data("index") === result.data("index")) {
                break;
            }
        }

        this.g.endIndex = step;
        // 计算运行步长
        step = this.g.endIndex - this.g.curIndex;
        step = step > 0 ? step : (this.g.boxTotal - this.g.curIndex + this.g.endIndex);
        this.g.jumpStep = this.g.boxTotal * this.g.cycle + step;
    },

    getFortuneBox: function(data, box) {
        box = this.game.find("ul li[data\\-index]");
        box.sort(function(a, b) {
            return ($(a).data("index") - $(b).data("index"));
        });

        this.box = box;
        box = null;

        for (var i = 0, l = data.length, e = null; i < l; ++i) {
            e = $(this.box[i]),
            e.html('<div class="mask"></div><span class="icon-lottery ' + data[i]['class']  + '"></span><span class="btxt">' + data[i].name + '</span>');
            e.attr("data-flag", data[i].flag);
            this.box[i] = e;
        }
    },

    run: function() {
        var that = this;
        if (this.box.length !== this.g.maxBoxTotal)
            return false;

        var time = 500,
            timer = null,
            jumpindex = 0,
            jumpmax = this.g.jumpStep,
            that = this;
        var doTimer = function() {
            time = that.changeshowlist(jumpindex);
            that.showbox(that.g.showList);
            jumpindex++;
            if (jumpindex >= jumpmax) {
                clearTimeout(timer);
                that.g.curIndex = that.g.endIndex;
                setTimeout(function() {
                    that.showresult();
                }, 200);
            } else {
                timer = setTimeout(doTimer, time);
            }
        };

        doTimer();
    },

    showresult: function() {
        var that = this;
        if (this.result.side==0) {
          // 条件不足，提供错误提示
          JLib.Utils.alertMsg(this.result.msg, "err");
          return false;
        }
        $.layer({
            img: '<s class="info"></s>',
            prize_level:this.result.side,
            lottery_id:this.result.lottery_id,
            show_save_box:this.result.show_save_box,
            //content: this.result.msg + "当前游戏金币：" + this.result.gamebalance,
            content:this.result.msg,
            leftbtn: {
                text: this.result.button_cancel,
                //url:window.pathURL+"/index/user?type=user&name=news"
                callback: function() {
                  //that.params.point = that.result.gamebalance;
                  //that.params.matchid = "";
                  $.layer.close();
                  return false;
              }
            },
            rightbtn: {
                text: this.result.button_confirm,
                callback: function() {
                    //that.params.point = that.result.gamebalance;
                    //that.params.matchid = "";
                  if (that.result.show_save_box == 1) {
                    var winnerName = $('.winnerName').val();
                    var winnerTelephone = $('.winnerTelephone').val();
                    var winnerAddress   = $('.winnerAddress').val();
                    var winnerPostCode  = $('.winnerPostCode').val();
                    var winnerLotteryId  = $('.winnerLotteryId').val();
                    var winnerPrizeLevel = $('.winnerPrizeLevel').val();
                    
                    var winnerParams = {'name':winnerName,'telephone':winnerTelephone,'address':winnerAddress,'post_code':winnerPostCode, 'lottery_id':winnerLotteryId, 'prize_level':winnerPrizeLevel};
                    var actionUrl = $('.saveWinnerInfo').attr('action');
                    
                    JLib.Utils.get(actionUrl, function(data) {
                        if (data.succ == 1) {
                          alert(data.msg);
                          $.layer.close();
                          return false;
                        } else {
                          alert(data.msg);
                        }
                    }, winnerParams);
                  } else {
                    $.layer.close();
                    return false;
                  }
                }
            }
        });
        this.game.find(".playbtn a").removeClass("over");
    },

    showbox: function(index) {
        this.game.find("li.now").removeClass("now");
        for (var i = 0, l = index.length, e = null; i < l; ++i) {
            e = this.box[index[i] - 1];
            e.addClass("now");
        }
    },

    //每次改变需要显示的box，返回速度
    changeshowlist: function(jumpindex) {
        var i,
            len = this.g.showList.length,
            jumpmax = this.g.jumpStep,
            total = this.g.boxTotal;

        switch (jumpindex) {
            case 0:
                var v = this.g.showList[0] + 1;
                this.g.showList.length = 0;
                v = v > total ? v - total : v;
                this.g.showList[0] = v;
                return 400;
            case 1:
                if (len == 1) {
                    var v = this.g.showList[0] + 1;
                    v = v > total ? v - total : v;
                    this.g.showList.push(v);
                }
                return 350;
            case 2:
                if (len == 2) {
                    var v = this.g.showList[1] + 1;
                    v = v > total ? v - total : v;
                    this.g.showList.push(v);
                }
                return 300;
            case 3:
                if (len == 3) {
                    var v = this.g.showList[2] + 1;
                    v = v > total ? v - total : v;
                    this.g.showList.push(v);
                }
                return 200;
            case jumpmax - 1:
                var v = this.g.showList[0] + 1;
                this.g.showList.length = 0;
                v = v > total ? v - total : v;
                this.g.showList[0] = v;
                return 800;
            case jumpmax - 2:
                var v = this.g.showList[0] + 1;
                this.g.showList.length = 0;
                v = v > total ? v - total : v;
                this.g.showList[0] = v;
                return 700;
            case jumpmax - 3:
                var v = this.g.showList[0] + 1;
                this.g.showList.length = 0;
                v = v > total ? v - total : v;
                this.g.showList[0] = v;
                return 600;
            case jumpmax - 4:
                var v = this.g.showList[0] + 1;
                this.g.showList.length = 0;
                v = v > total ? v - total : v;
                this.g.showList[0] = v;
                return 400;
            case jumpmax - 5:
                var v = this.g.showList[0] + 1;
                this.g.showList.length = 0;
                v = v > total ? v - total : v;
                this.g.showList[0] = v;
                return 300;
            case jumpmax - 6:
                var v = this.g.showList[1] + 1;
                this.g.showList.length = 0;
                v = v > total ? v - total : v;
                this.g.showList[0] = v;
                return 200;
            case jumpmax - 7:
                var v1 = this.g.showList[1] + 1;
                var v2 = this.g.showList[2] + 1;
                this.g.showList.length = 0;
                v1 = v1 > total ? v1 - total : v1;
                v2 = v2 > total ? v2 - total : v2;
                this.g.showList[0] = v1;
                this.g.showList[1] = v2;
                return 100;
            case jumpmax - 8:
                var v1 = this.g.showList[1] + 1;
                var v2 = this.g.showList[2] + 1;
                var v3 = this.g.showList[3] + 1;
                this.g.showList.length = 0;
                v1 = v1 > total ? v1 - total : v1;
                v2 = v2 > total ? v2 - total : v2;
                v3 = v3 > total ? v3 - total : v3;
                this.g.showList[0] = v1;
                this.g.showList[1] = v2;
                this.g.showList[2] = v3;
                return 50;
            default:
                for (i = 0; i < len; i++) {
                    this.g.showList[i]++;
                    if (this.g.showList[i] > total) {
                        this.g.showList[i] -= total;
                    }
                }
                return 30;
        }
    }
});


$(function() {
    new Game();
});