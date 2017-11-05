var ACHIEVE_GOAL = {
	achieveUrl: '/homepage/homepage/achieve-goal',
	init: function() {
	
		$('#_selectY').change(function(){
			window.location = ACHIEVE_GOAL.achieveUrl 
			+ '/year/' + $(this).val() 
			+ '/kai/' + $('#_selectKai').val() 
			+ '/kyu/' + $('#_selectKyu').val()
			+ '/orgscy/' + $('#_selectSchoolYear').val();
		});
		$('#_selectKai').change(function(){
			window.location = ACHIEVE_GOAL.achieveUrl 
			+ '/year/' +$('#_selectY').val() 
			+ '/kai/' + $(this).val()
			+ '/kyu/' + $('#_selectKyu').val()
			+ '/orgscy/' + $('#_selectSchoolYear').val();
		});		
		$('#_selectKyu').change(function(){
			window.location = ACHIEVE_GOAL.achieveUrl 
			+ '/year/' +$('#_selectY').val() 
			+ '/kai/' + $('#_selectKai').val()
			+ '/kyu/' + $(this).val()
			+ '/orgscy/' + $('#_selectSchoolYear').val();
		});		
		$('#_selectSchoolYear').change(function(){		
			window.location = ACHIEVE_GOAL.achieveUrl 
			+ '/year/' +$('#_selectY').val() 
			+ '/kai/' + $('#_selectKai').val()
			+ '/kyu/' + $('#_selectKyu').val()
			+ '/orgscy/' + $(this).val();
		});
		if($('.header-sort1').length) {
			$('.header-sort1').find('th').click(function(){			
				if($(this).attr('data-sort')){
					var ord = $('#ord').val();					
					 if(ord == 'a') ord = 'd';
					 	else ord = 'a';					 
					window.location = ACHIEVE_GOAL.achieveUrl 
					+ '/year/' +$('#_selectY').val() 
					+ '/kai/' + $('#_selectKai').val()
					+ '/kyu/' + $('#_selectKyu').val()
					+ '/orgscy/' + $('#_selectSchoolYear').val()
					+ '/key/' + $(this).attr('data-sort')
					+ '/ord/' + ord;
				}
			});
		};	
		$("#_selectSchoolYear").focus();
	// active order
		var currentUrl = window.location.href;
		var isColInUrl = currentUrl.lastIndexOf("/col");
		if(isColInUrl > 0)
		{
			var pgurl = currentUrl.substr(currentUrl.lastIndexOf("/col")+1);
			var strUrl = pgurl.split('/'); 
			if(strUrl[2]== 'a')
				{
					$('.' + strUrl[0]).find('span').removeClass('caret');
					$('.' + strUrl[0]).find('span').addClass('caretdown');
				}
			else
				{
				$('.' + strUrl[0]).find('span').removeClass('caretdown');
				$('.' + strUrl[0]).find('span').addClass('caret');
				}
			}
		$('#chart1').highcharts({
			chart : {
				type : 'column',
				marginTop : 0,
				marginLeft : 90,
				marginBottom : 0,
				backgroundColor : '#F2f2f2'
			},
			title : {
				style : {
					display : 'none'
				},
				text : ''
			},
			legend : {
				enabled : false
			},
			credits : {
				enabled : false
			},
			xAxis : {
				categories : [currentYear - 2, currentYear - 1, currentYear],
				labels : {
					enabled : false
				}
			},
			yAxis : {
				min : 0,
				title : {
					style : {
						display : 'none'
					},
					text : ''
				},
				labels : {
					enabled : false
				},
				gridLineColor : 'transparent'
			},
			tooltip : {
				// pointFormat: '<b>{point.y}</b><br>',
				shared : true
			},
			plotOptions : {
				column : {
					stacking : 'normal',
					borderWidth : 0
				}
			},
			series : [ {
				name : '第1回',
				data : dataA1[1]['data'],
				color : '#0077cb'
			}, {
				name : '第2回',
				data : dataA1[2]['data'],
				color : '#66ade0'
			}, {
				name : '第3回',
				data : dataA1[3]['data'],
				color : '#b2d6ef'

			} ]
		});
		
		
		$('#chart2').highcharts({
			chart : {
				marginTop : 10,
				marginLeft : 90,
				marginBottom : 0,
				backgroundColor : '#F2f2f2'
			},
			title : {
				style : {
					display : 'none'
				},
				text : ''
			},
			legend : {
				enabled : false
			},
			credits : {
				enabled : false
			},
			xAxis : {
				categories : [currentYear - 2, currentYear - 1, currentYear],
				labels : {
					enabled : false
				}
			},
			yAxis : {
				min : 0,
				title : {
					style : {
						display : 'none'
					},
					text : ''
				},
				labels : {
					enabled : false
				},
				gridLineColor : 'transparent'
			},			
			tooltip : {
				valueSuffix : '%'
			},

			series : [  {
				name : '第3回',
				data : dataA2[3]['data'],
				marker : {
					symbol : 'circle',
					radius : 3,
					lineColor : '#002aff',
					lineWidth : 1,
					fillColor : '#ffffff'
				},
				color : '#002aff'
			}, {
				name : '第2回',
				data : dataA2[2]['data'],
				marker : {
					symbol : 'circle',
					radius : 3,
					lineColor : '#ff7e00',
					lineWidth : 1,
					fillColor : '#ffffff'
				},
				color : '#ff7e00'
			},{
				name : '第1回',
				data : dataA2[1]['data'],
				marker : {
					symbol : 'circle',
					radius : 3,
					lineColor : '#e71223',
					lineWidth : 1,
					fillColor : '#ffffff'
				},
				color : '#e71223'
			}]
		});		
	},
        showMSGNoData : function(){
                    ERROR_MESSAGE.show('エクスポート対象がありません。');
        }
};

