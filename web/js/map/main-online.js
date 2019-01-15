// JavaScript Document

$(function(){
	
		
	// $.ajax({
	// 	url: projectName+'/idea123Action.do?method=getIdea123MapData&reportName='+reportName,
	// 	data: data,
	// 	dataType: 'json',
	// 	success: function(data){

	var data = {
				"jiangsu": {
					"value": "2345",
					"index": "1",
					"stateInitColor": "8"
				},
				"henan": {
					"value": "2345",
					"index": "2",
					"stateInitColor": "8"
				},
				"anhui": {
					"value": "2345",
					"index": "3",
					"stateInitColor": "9"
				},
				"zhejiang": {
					"value": "2345",
					"index": "4",
					"stateInitColor": "9"
				},
				"liaoning": {
					"value": "2345",
					"index": "5",
					"stateInitColor": "7"
				},
				"beijing": {
					"value": "2345",
					"index": "6",
					"stateInitColor": "10"
				},
				"hubei": {
					"value": "2345",
					"index": "7",
					"stateInitColor": "9"
				},
				"jilin": {
					"value": "2345",
					"index": "8",
					"stateInitColor": "7"
				},
				"shanghai": {
					"value": "2345",
					"index": "9",
					"stateInitColor": "10"
				},
				"guangxi": {
					"value": "2345",
					"index": "10",
					"stateInitColor": "7"
				},
				"sichuan": {
					"value": "2345",
					"index": "11",
					"stateInitColor": "5"
				},
				"guizhou": {
					"value": "0.99%",
					"index": "12",
					"stateInitColor": "6"
				},
				"hunan": {
					"value": "2345",
					"index": "13",
					"stateInitColor": "10"
				},
				"shandong": {
					"value": "2345",
					"index": "14",
					"stateInitColor": "8"
				},
				"guangdong": {
					"value": "2345",
					"index": "15",
					"stateInitColor": "10"
				},
				"jiangxi": {
					"value": "2345",
					"index": "16",
					"stateInitColor": "8"
				},
				"fujian": {
					"value": "2345",
					"index": "17",
					"stateInitColor": "9"
				},
				"yunnan": {
					"value": "2345",
					"index": "18",
					"stateInitColor": "7"
				},
				"hainan": {
					"value": "2345",
					"index": "19",
					"stateInitColor": "8"
				},
				"shanxi": {
					"value": "2345",
					"index": "20",
					"stateInitColor": "6"
				},
				"hebei": {
					"value": "2345",
					"index": "21",
					"stateInitColor": "9"
				},
				"neimongol": {
					"value": "2345",
					"index": "22",
					"stateInitColor": "5"
				},
				"tianjin": {
					"value": "2345",
					"index": "23",
					"stateInitColor": "10"
				},
				"gansu": {
					"value": "2345",
					"index": "24",
					"stateInitColor": "8"
				},
				"shaanxi": {
					"value": "2345",
					"index": "25",
					"stateInitColor": "4"
				},
				"macau": {
					"value": "2345",
					"index": "26",
					"stateInitColor": "7"
				},
				"hongkong": {
					"value": "2345",
					"index": "27",
					"stateInitColor": "7"
				},
				"taiwan": {
					"value": "2345",
					"index": "28",
					"stateInitColor": "8"
				},
				"qinghai": {
					"value": "2345",
					"index": "29",
					"stateInitColor": "2"
				},
				"xizang": {
					"value": "2345",
					"index": "30",
					"stateInitColor": "3"
				},
				"ningxia": {
					"value": "2345",
					"index": "31",
					"stateInitColor": "6"
				},
				"xinjiang": {
					"value": "2345",
					"index": "32",
					"stateInitColor": "0"
				},
				"heilongjiang": {
					"value": "2345",
					"index": "33",
					"stateInitColor": "1"
				},
				"chongqing": {
					"value": "2345",
					"index": "34",
					"stateInitColor": "8"
				}
			};
			var i = 1;
			for(k in data){
				$('#MapControl .list1').append('<li name="'+k+'"><div class="mapInfo"><i>'+(i++)+'</i><span>'+chinaMapConfig.names[k]+'&nbsp;:</span><b>'+data[k].value+'</b></div></li>')
			}

			var mapObj_1 = {};
			var stateColorList = ['677dbd', '6a9ad1', '78b9e6', '78c3ed', 'add7e3', 'b6e1d8', 'deecbb', 'fcf4cd', 'fde0c1', 'f7acad', 'f69fa3'];
			
			$('#RegionMap').SVGMap({
				external: mapObj_1,
				mapName: 'china',
				mapWidth: 350,
				mapHeight: 300,
				stateData: data,
				// stateTipWidth: 118,
				// stateTipHeight: 47,
				// stateTipX: 2,
				// stateTipY: 0,
				stateTipHtml: function (mapData, obj) {
					var _value = mapData[obj.id].value;
					var _idx = mapData[obj.id].index;
					var active = '';
					_idx < 4 ? active = 'active' : active = '';
					var tipStr = '<div class="mapInfo"><i class="' + active + '">' + _idx + '</i><span>' + obj.name + '：</span><b>' + _value + '</b></div>';
					return tipStr;
				}
			});
			$('#MapControl li').hover(function () {
				var thisName = $(this).attr('name');
				
				var thisHtml = $(this).html();
				$('#MapControl li').removeClass('select');
				$(this).addClass('select');
				$(document.body).append('<div id="StateTip"></div');
				$('#StateTip').css({
					left: $(mapObj_1[thisName].node).offset().left - 50,
					top: $(mapObj_1[thisName].node).offset().top - 40
				}).html(thisHtml).show();
				mapObj_1[thisName].attr({
					fill: '#E99A4D'
				});
			}, function () {
				var thisName = $(this).attr('name');

				$('#StateTip').remove();
				$('#MapControl li').removeClass('select');
				mapObj_1[$(this).attr('name')].attr({
					fill: "#" + stateColorList[data[$(this).attr('name')].stateInitColor]
				});
			});
			
			$('#MapColor').show();
	// 	}
	// });
   	
	//展开查看更多
	$('#Region .btn-open').click(function(){
	  if ($(this).text()=='展开查看更多'){
	    $('#MapControl .list1').animate({ height:'288px' },500);
		$(this).text('关闭')
	  } else {
	    $('#MapControl .list1').animate({ height:'48px' },500);
		$(this).text('展开查看更多')
	  }
	})

});