var DETAIL_B = {
		orgNo:dataJsonB.orgNo,
		isBegin : true,
		baseUrl : window.location.protocol + "//" + window.location.host + "/",
		ajaxDetailClassB : 'homepage/homepage/ajaxDetailClassB',
		ajaxTableByClassB : 'homepage/homepage/ajaxTableByClassB',
		exportListExamLink : COMMON.baseUrl + 'homepage/homepage/getExportListAttendPupil',
		nationChart: ["5","8","11"],
		cityChart: ["4","7","10"],
		year0Chart: ["0","9","10","11"],
		year1Chart: ["1","6","7","8"],
		year2Chart: ["2","3","4","5"],
		type1Chart: ["0","1","2"],
		type2Chart: ["3","4","5","6","7","8","9","10","11"],
		type3Chart: ["0","1","2","3","4","5","6","7","8","9","10","11"],
		listTitle: {0:".lb-total-0",
					1:".lb-total-1",
					2:".lb-total-2",
					3:".lb-rate-org-2",
					4:".lb-rate-city-2",
					5:".lb-rate-nation-2",
					6:".lb-rate-org-1",
					7:".lb-rate-city-1",
					8:".lb-rate-nation-1",
					9:".lb-rate-org-0",
					10:".lb-rate-city-0",
					11:".lb-rate-nation-0"
					},
		init: function(){
			DETAIL_B.initChart();
			DETAIL_B.initPage();
			DETAIL_B.loadPage();
		},
		addArray: function(array1,array2){
			jQuery.each(array2, function(index, value) {
				array1.push(value);
			   });
			return array1;
		},
		removeArray: function(array1,array2){
			var arr = [];
			arr = jQuery.grep(array2, function(el) {
		        if (jQuery.inArray(el, array1) == -1) return el;
			});
			return arr;
		},
		sameArray: function(array1,array2){
			var sameArr=[];
			jQuery.grep(array2, function(el) {
		        if (jQuery.inArray(el, array1) !== -1) sameArr.push(el);
			});
			return sameArr;
		},
		showChart: function(array){
			var chart = $('#chartB3').highcharts();
			jQuery.each(array,function(index,value){
				chart.series[value].show();
			});
		},
		showTitle: function(array) {
			jQuery.each(array,function(index,value){
				$(DETAIL_B.listTitle[value]).show();
			});
		},
		showRow: function(key) {
			if(key =='.nation' || key =='.city'){
				//$('.rspYcr').attr('rowspan',$('.rspYcr').attr('rowspan')+1);
			}
			$(key).show();
		},
		hideRow: function(key) {
			if(key =='.nation' || key =='.city'){
				//$('.rspYcr').attr('rowspan',$('.rspYcr').attr('rowspan')-1);
			}
			$(key).hide();
		},
		hideTitle: function(array) {
			jQuery.each(array,function(index,value){
				$(DETAIL_B.listTitle[value]).hide();
			});
		},
		hideChart: function(array){
			var chart = $('#chartB3').highcharts();
			jQuery.each(array,function(index,value){
				chart.series[value].hide();
			});
		},
		loadPage: function(){
			$('#_selectY option[value='+dataJsonB.year+']').attr('selected', true);
			//$('#_selectT option[value='+time+']').attr('selected', true);
			DETAIL_B.hideRow('.city');
			DETAIL_B.hideRow('.nation');
		},
		display2Chart: function(type, year0, year1, year2, nation, city){
			var data = [];
			if(year0) {
				DETAIL_B.addArray(data, DETAIL_B.year0Chart);
				DETAIL_B.showRow('.rowY0');
				} else {
					DETAIL_B.hideRow('.rowY0');
				}
			if(year1){ 
				DETAIL_B.addArray(data, DETAIL_B.year1Chart);
				DETAIL_B.showRow('.rowY1');
			} else {
				DETAIL_B.hideRow('.rowY1');
			}
			if(year2){ 
				DETAIL_B.addArray(data, DETAIL_B.year2Chart);
				DETAIL_B.showRow('.rowY2');
			} else {
				DETAIL_B.hideRow('.rowY2');
			}
			if(!nation){ 
				data = DETAIL_B.removeArray(DETAIL_B.nationChart, data);
				DETAIL_B.hideRow('.nation');
			} else {
				DETAIL_B.showRow('.nation');
			}
			if(!city){ 
				data = DETAIL_B.removeArray(DETAIL_B.cityChart, data);
				DETAIL_B.hideRow('.city');
			} else {
				DETAIL_B.showRow('.city');
			}
			switch (type) {
			case "chk1":
				listShow = DETAIL_B.sameArray(DETAIL_B.type1Chart, data);
				break;
			case "chk2":
				listShow = DETAIL_B.sameArray(DETAIL_B.type2Chart, data);
				break;
			case "chk3":
				listShow = DETAIL_B.sameArray(DETAIL_B.type3Chart, data);
				break;
			default:
				break;
			}
			var listHide = DETAIL_B.removeArray(listShow, DETAIL_B.type3Chart);
			DETAIL_B.showChart(listShow);
			DETAIL_B.showTitle(listShow);
			DETAIL_B.hideChart(listHide);
			DETAIL_B.hideTitle(listHide);
		},
		displayChart: function(type){
			if(!type){
				type = $('.chk-type .active').attr('id');
			}
			var year0 = $('#_crYear0').is(':checked');
			var year1 = $('#_crYear1').is(':checked');
			var year2 = $('#_crYear2').is(':checked');
			var nation = $('#_nation').is(':checked');
			var city = $('#_city').is(':checked');
			DETAIL_B.display2Chart(type, year0, year1, year2, nation, city);
		},
		initPage: function(){
			$('.detail-b-type-check').click(function(){
				var type =0;
				if($(this).hasClass('detail-b-type-table')) type = $(this).attr("id");
				DETAIL_B.displayChart(type);
			});
			$('#chk1').click(function(){
				var chart = $('#chartB3').highcharts();
				//DETAIL_B.typeClick(1, 1);
				$('#_city').attr("checked", false);
				$('#_nation').attr("checked", false);
				$('#_city').prop('disabled', true);
				$('#_nation').prop('disabled', true);
			});
			$('#chk2').click(function(){
				$('#_city').prop('disabled', false);
				$('#_nation').prop('disabled', false);
				//$('#_city').prop("checked", true);
				//$('#_nation').prop("checked", true);
				//DETAIL_B.typeClick(2, 1);
			});
			
			$('#chk3').click(function(){
				var chart = $('#chartB3').highcharts();
				//DETAIL_B.typeClick(3, 1);
				
				//$('#_city').prop("checked", true);
				//$('#_nation').prop("checked", true);
				$('#_city').prop('disabled', false);
				$('#_nation').prop('disabled', false);
			});
			/*$('#_columnY0').click(function() {
					//DETAIL_B.clickYear0();
				});
			$('#_columnY1').click(function() {
				//DETAIL_B.clickYear1();
			});
			$('#_columnY2').click(function() {
				//DETAIL_B.clickYear2();
			});*/
			$('.select-detail-b').change(function() {
				//$('.select-detail-c option[value=3]').attr('selected', true);
				var value = $(this).val();
				var id = $(this).attr("id");
				//$('#'+id+' option').attr('selected', false);
				//$('#'+id+' option[value='+value+']').attr('selected', true);
				 var year = $('#_selectY').val();
				 var time = $('#_selectT').val();
				DETAIL_B.ajaxDetailClass(year,time);
			});
			$(document).on( "click", ".detail-table-b", function() {
				var year = $('#_selectY').val();
				var time = $('#_selectT').val();
				var res = $(this).data('detail').split("-");
				var sum = $(this).data('count');
				var page =1;
				DETAIL_B.ajaxTableByClass(res[0],res[1],year,time,sum,page);//[0]orgClassification,[1]orgShoolYear
			});
			$(document).on( "click", ".pagination li a", function() {
				var page = $(this).attr('href').substring(6);
				$(this).removeAttr("");
				if(page == "pre" || page == "next"){
					return false;
				}
				var year = $('#_selectY').val();
				var time = $('#_selectT').val();
				var res = $('#listClassYear li.active').data('detail').split("-");
				var sum = $('#listClassYear li.active').data('count');
				DETAIL_B.ajaxTableByClass(res[0],res[1],year,time,sum,page);
				return false;
			});
                                              
			
		},
		/*
		 * Draw highcharts
		 * DucNA17
		 */
		initChart: function(){
			$(function () {
			    $('#chartB3').highcharts({
			    	 chart: {
				            backgroundColor: '#f2f2f2',
				            marginTop: 0,
			                marginLeft: 205,
			                marginBottom: 0,
			                marginRight: 0
				        },
			        title: {
			            text: 'Combination chart',
			            style:{display:'none'}
			        },
			        legend:{
				          enabled:false
				        },
				    credits: {
				            enabled: false },
			        xAxis: {
			            categories: ['5級', '4級', '3級', '準2級', '2級','準1級','1級'],
			            labels: {enabled: false}
			        },
			        yAxis:{
			        	  title: {
			                	style:{display : 'none'},
			                    text: ''
			                },
			        	labels: {enabled: false},
			        	gridLineColor: 'transparent'
				        },
			        plotOptions: {
			        	 column: {
			        		 grouping: true,
			                 borderWidth: 0
			             }
			        },
			        series: [{
			            type: 'column',
			            name: '本校-'+dataGraphJ.Y0[1]['year'],
			            data: [ parseInt(dataGraphJ.Y0[7]["totalPassed"]),
					            parseInt(dataGraphJ.Y0[6]["totalPassed"]),
					            parseInt(dataGraphJ.Y0[5]["totalPassed"]),
			                    parseInt(dataGraphJ.Y0[4]["totalPassed"]),
			                    parseInt(dataGraphJ.Y0[3]["totalPassed"]),
			                    parseInt(dataGraphJ.Y0[2]["totalPassed"]),
			                    parseInt(dataGraphJ.Y0[1]["totalPassed"])
					            ],
			            color: '#9dcff2',
			            pointWidth: 16,
		        		pointPadding: 0.3,
			            pointPlacement:0.03
			        },

			        {
			            type: 'column',
			            name: '本校-'+dataGraphJ.Y1[1]['year'],
			            data: [ parseInt(dataGraphJ.Y1[7]["totalPassed"]),
					            parseInt(dataGraphJ.Y1[6]["totalPassed"]),
					            parseInt(dataGraphJ.Y1[5]["totalPassed"]),
			                    parseInt(dataGraphJ.Y1[4]["totalPassed"]),
			                    parseInt(dataGraphJ.Y1[3]["totalPassed"]),
			                    parseInt(dataGraphJ.Y1[2]["totalPassed"]),
			                    parseInt(dataGraphJ.Y1[1]["totalPassed"])
					            ],
			            color: '#0077cb',
				        pointWidth: 16,
		        		pointPadding: 0.3,
			            pointPlacement:0
			        },
			         {
			            type: 'column',
			            name: '本校-'+dataGraphJ.Y2[1]['year'],
			            data: [ parseInt(dataGraphJ.Y2[7]["totalPassed"]),
					            parseInt(dataGraphJ.Y2[6]["totalPassed"]),
					            parseInt(dataGraphJ.Y2[5]["totalPassed"]),
			                    parseInt(dataGraphJ.Y2[4]["totalPassed"]),
			                    parseInt(dataGraphJ.Y2[3]["totalPassed"]),
			                    parseInt(dataGraphJ.Y2[2]["totalPassed"]),
			                    parseInt(dataGraphJ.Y2[1]["totalPassed"])
					            ],
			            color: '#005897',
			            pointWidth: 16,
		        		pointPadding: 0.3,
			            pointPlacement:-0.02
			        },
			     // 2013
			     	{
				        type: 'line',
			            name: '本校-'+dataGraphJ.Y2[1]['year'],
			            data: [{x:-0.17, y: parseFloat(dataGraphJ.Y2[7]["orgRate"])},
					            {x:0.84, y: parseFloat(dataGraphJ.Y2[6]["orgRate"])},
					            {x:1.84, y: parseFloat(dataGraphJ.Y2[5]["orgRate"])},
					            {x:2.84, y: parseFloat(dataGraphJ.Y2[4]["orgRate"])},
					            {x:3.84, y: parseFloat(dataGraphJ.Y2[3]["orgRate"])},
					            {x:4.84, y: parseFloat(dataGraphJ.Y2[2]["orgRate"])},
					            {x:5.84, y: parseFloat(dataGraphJ.Y2[1]["orgRate"])}],
			            color:'#2f8d00',
			            marker: {
			            	symbol: 'circle',
			                lineWidth: 2,
			                radius: 3,
			                lineColor:'#2f8d00', //Highcharts.getOptions().colors[1],
			                fillColor: 'white'
			            }
					},
				    {
			            name: dataJsonB.cityName+'-'+dataGraphJ.Y2[1]['year'],
			            data: [{x:-0.17, y: parseFloat(dataGraphJ.Y2[7]["cityRate"])},
					            {x:0.84, y: parseFloat(dataGraphJ.Y2[6]["cityRate"])},
					            {x:1.84, y: parseFloat(dataGraphJ.Y2[5]["cityRate"])},
					            {x:2.84, y: parseFloat(dataGraphJ.Y2[4]["cityRate"])},
					            {x:3.84, y: parseFloat(dataGraphJ.Y2[3]["cityRate"])},
					            {x:4.84, y: parseFloat(dataGraphJ.Y2[2]["cityRate"])},
					            {x:5.84, y: parseFloat(dataGraphJ.Y2[1]["cityRate"])}],
			            color:'#e10215',
			            marker: {
			            	symbol: 'circle',
			                lineWidth: 2,
			                radius: 3,
			                lineColor:'#e10215', //Highcharts.getOptions().colors[1],
			                fillColor: 'white'
			            }
			        },
					{
				        type: 'line',
			            name: '全国-'+dataGraphJ.Y2[1]['year'],
			            data: [{x:-0.17, y: parseFloat(dataGraphJ.Y2[7]["nationRate"])},
					            {x:0.84, y: parseFloat(dataGraphJ.Y2[6]["nationRate"])},
					            {x:1.84, y: parseFloat(dataGraphJ.Y2[5]["nationRate"])},
					            {x:2.84, y: parseFloat(dataGraphJ.Y2[4]["nationRate"])},
					            {x:3.84, y: parseFloat(dataGraphJ.Y2[3]["nationRate"])},
					            {x:4.84, y: parseFloat(dataGraphJ.Y2[2]["nationRate"])},
					            {x:5.84, y: parseFloat(dataGraphJ.Y2[1]["nationRate"])}],
			            color:'#febcbc',
			            marker: {
			            	symbol: 'circle',
			                lineWidth: 2,
			                radius: 3,
			                lineColor:'#febcbc', //Highcharts.getOptions().colors[1],
			                fillColor: 'white'
			            }
					},
					//2014
					{
				        type: 'line',
			            name: '本校-'+dataGraphJ.Y1[1]['year'],
			            data: [ parseFloat(dataGraphJ.Y1[7]["orgRate"]),
			                    parseFloat(dataGraphJ.Y1[6]["orgRate"]),
			                    parseFloat(dataGraphJ.Y1[5]["orgRate"]),
			                    parseFloat(dataGraphJ.Y1[4]["orgRate"]),
			                    parseFloat(dataGraphJ.Y1[3]["orgRate"]),
			                    parseFloat(dataGraphJ.Y1[2]["orgRate"]),
			                    parseFloat(dataGraphJ.Y1[1]["orgRate"])
					            ],
			            color:'#0ebccc',
			            marker: {
			            	symbol: 'circle',
			                lineWidth: 2,
			                radius: 3,
			                lineColor:'#0ebccc', //Highcharts.getOptions().colors[1],
			                fillColor: 'white'
			            }
					},
			        {
			            name: dataJsonB.cityName+'-'+dataGraphJ.Y1[1]['year'],
			            data: [ parseFloat(dataGraphJ.Y1[7]["cityRate"]),
			                    parseFloat(dataGraphJ.Y1[6]["cityRate"]),
			                    parseFloat(dataGraphJ.Y1[5]["cityRate"]),
			                    parseFloat(dataGraphJ.Y1[4]["cityRate"]),
			                    parseFloat(dataGraphJ.Y1[3]["cityRate"]),
			                    parseFloat(dataGraphJ.Y1[2]["cityRate"]),
			                    parseFloat(dataGraphJ.Y1[1]["cityRate"])
					            ],
			            color:'#ff7e00',
			            marker: {
			            	symbol: 'circle',
			                lineWidth: 2,
			                radius: 3,
			                lineColor:'#ff7e00', //Highcharts.getOptions().colors[1],
			                fillColor: 'white'
			            }
			        },
					{
				        type: 'line',
			            name: '全国-'+dataGraphJ.Y1[1]['year'],
			            data: [ parseFloat(dataGraphJ.Y1[7]["nationRate"]),
			                    parseFloat(dataGraphJ.Y1[6]["nationRate"]),
			                    parseFloat(dataGraphJ.Y1[5]["nationRate"]),
			                    parseFloat(dataGraphJ.Y1[4]["nationRate"]),
			                    parseFloat(dataGraphJ.Y1[3]["nationRate"]),
			                    parseFloat(dataGraphJ.Y1[2]["nationRate"]),
			                    parseFloat(dataGraphJ.Y1[1]["nationRate"])
					            ],
			            color:'#d9b9ff',
			            marker: {
			            	symbol: 'circle',
			                lineWidth: 2,
			                radius: 3,
			                lineColor:'#d9b9ff', //Highcharts.getOptions().colors[1],
			                fillColor: 'white'
			            }
					},
			        //--- 2015
			        {
				        type: 'line',
			            name: '本校-'+dataGraphJ.Y0[1]['year'],
			            data: [ {x:0.17, y: parseFloat(dataGraphJ.Y0[7]["orgRate"])},
					            {x:1.18, y: parseFloat(dataGraphJ.Y0[6]["orgRate"])},
					            {x:2.18, y: parseFloat(dataGraphJ.Y0[5]["orgRate"])},
					            {x:3.18, y: parseFloat(dataGraphJ.Y0[4]["orgRate"])},
					            {x:4.18, y: parseFloat(dataGraphJ.Y0[3]["orgRate"])},
					            {x:5.18, y: parseFloat(dataGraphJ.Y0[2]["orgRate"])},
					            {x:6.18, y: parseFloat(dataGraphJ.Y0[1]["orgRate"])}],
			            color:'#f10cfe',
			            marker: {
			            	symbol: 'circle',
			                lineWidth: 2,
			                radius: 3,
			                lineColor:'#f10cfe', //Highcharts.getOptions().colors[1],
			                fillColor: 'white'
			            }
					},
					{
			            name: dataJsonB.cityName+'-'+dataGraphJ.Y0[1]['year'],
			            data: [ {x:0.17, y: parseFloat(dataGraphJ.Y0[7]["cityRate"])},
					            {x:1.18, y: parseFloat(dataGraphJ.Y0[6]["cityRate"])},
					            {x:2.18, y: parseFloat(dataGraphJ.Y0[5]["cityRate"])},
					            {x:3.18, y: parseFloat(dataGraphJ.Y0[4]["cityRate"])},
					            {x:4.18, y: parseFloat(dataGraphJ.Y0[3]["cityRate"])},
					            {x:5.18, y: parseFloat(dataGraphJ.Y0[2]["cityRate"])},
					            {x:6.18, y: parseFloat(dataGraphJ.Y0[1]["cityRate"])}],
			            color:'#002aff',
			            marker: {
			            	symbol: 'circle',
			                lineWidth: 2,
			                radius: 3,
			                lineColor:'#002aff', //Highcharts.getOptions().colors[1],
			                fillColor: 'white'
			            }
			        },
			        {
				        type: 'line',
			            name: '全国-'+dataGraphJ.Y0[1]['year'],
			            data: [ {x:0.17, y: parseFloat(dataGraphJ.Y0[7]["nationRate"])},
					            {x:1.18, y: parseFloat(dataGraphJ.Y0[6]["nationRate"])},
					            {x:2.18, y: parseFloat(dataGraphJ.Y0[5]["nationRate"])},
					            {x:3.18, y: parseFloat(dataGraphJ.Y0[4]["nationRate"])},
					            {x:4.18, y: parseFloat(dataGraphJ.Y0[3]["nationRate"])},
					            {x:5.18, y: parseFloat(dataGraphJ.Y0[2]["nationRate"])},
					            {x:6.18, y: parseFloat(dataGraphJ.Y0[1]["nationRate"])}],
			            color:'#b8f4ff',
			            marker: {
			            	symbol: 'circle',
			                lineWidth: 2,
			                radius: 3,
			                lineColor:'#b8f4ff', //Highcharts.getOptions().colors[1],
			                fillColor: 'white'
			            }
					},
					]
			    });
			    $('#chartB3').highcharts().series[4].hide();
			    $('#chartB3').highcharts().series[7].hide();
			    $('#chartB3').highcharts().series[10].hide();
			    $('#chartB3').highcharts().series[5].hide();
			    $('#chartB3').highcharts().series[8].hide();
			    $('#chartB3').highcharts().series[11].hide();
			});
		},
		/*clickYear0: function(){
			var chart = $('#chart1').highcharts();
			if($("#_columnY0").is(':checked'))
			{
				//---- Show bar chart in hightchart
				chart.series[0].show();
				  	for($i=0; $i< chart.series.length; $i++)
				    	chart.series[$i].update({ groupPadding: 0.2 });
				    //--- show tr in table
				$('#_rowY0').show();

			}
			else
			{
				//----- Hidden bar chart in hightchart
				chart.series[0].hide();
				for($i=0; $i< chart.series.length; $i++)
				    	chart.series[$i].update({ groupPadding: 0.3 });
				//---  hidden tr in table
				$('#_rowY0').hide();
			}
		},
		clickYear1: function(){
			var chart = $('#chart1').highcharts();
			if($("#_columnY1").is(':checked'))
			{
				//---- Show bar chart in hightchart
				chart.series[1].show();

				  	for($i=0; $i< chart.series.length; $i++)
				    	chart.series[$i].update({ groupPadding: 0.2 });
				    //--- show tr in table
				$('#_rowY1').show();

			}
			else
			{
				//----- Hidden bar chart in hightchart
				chart.series[1].hide();

				for($i=0; $i< chart.series.length; $i++)
				    	chart.series[$i].update({ groupPadding: 0.3 });
				//---  hidden tr in table
				$('#_rowY1').hide();

			}
		},
		clickYear2: function(){
			var chart = $('#chart1').highcharts();
			if($("#_columnY2").is(':checked'))
			{
				//---- Show bar chart in hightchart
				chart.series[2].show();

				  	for($i=0; $i< chart.series.length; $i++)
				    	chart.series[$i].update({ groupPadding: 0.2 });
				    //--- show tr in table
				$('#_rowY2').show();

			}
			else
			{
				//----- Hidden bar chart in hightchart
				chart.series[2].hide();

				for($i=0; $i< chart.series.length; $i++)
				    	chart.series[$i].update({ groupPadding: 0.3 });
				//---  hidden tr in table
				$('#_rowY2').hide();

			}
		},
		*/
		ajaxDetailClass: function(year,time){
			  var myData = 'orgNo=' + DETAIL_B.orgNo + '&year='+year+'&time='+time;
		 	  $.ajax({
		          type:"POST",
		          url:DETAIL_B.baseUrl+DETAIL_B.ajaxDetailClassB,
		          data:myData,
		          dataType : 'html',
		          success:function(data){
		              $('#listClassYear').empty();
		              $('#listClassYear').append(data);
		           },
		            error:function(){
		            }
		         });
		},
		ajaxTableByClass: function(orgClassification,orgShoolYear,year,time,sum,page){
			var myData = 'orgNo=' + DETAIL_B.orgNo +'&year='+year+'&time='+time+'&orgClassification='+orgClassification+'&orgShoolYear='+orgShoolYear+'&sum='+sum +'&page=' +page;
			$.ajax({
				type:"POST",
				url:DETAIL_B.baseUrl+DETAIL_B.ajaxTableByClassB,
				data:myData,
				dataType : 'html',
				success:function(data){
					//$('#detail-exam-table-b').empty();
					//$('#detail-exam-table-b').append(data);
					$('#detailTable').empty();
					$('#detailTable').append(data);
				
				},
				error:function(){
				}
			});
		},
		exportListExam: function(){
			var data = {};
			data.orgNo = DETAIL_B.orgNo;
			data.year = $('#_selectY').val();
			data.kai = $('#_selectT').val();
			data.typeDetail = 'B';
			window.location.href = DETAIL_B.exportListExamLink+"?"+$.param(data);
		}

}


$("document").ready(function(){
	DETAIL_B.init();
});

