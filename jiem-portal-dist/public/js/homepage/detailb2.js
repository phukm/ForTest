var DETAIL_B = {
		orgNo:dataJsonB.orgNo,
		isBegin : true,
		baseUrl : window.location.protocol + "//" + window.location.host + "/",
		year0Chart: ["0","1","2"],
		year1Chart: ["3","4","5"],
		year2Chart: ["6","7","8"],
		init: function(){
			DETAIL_B.initChart();
			DETAIL_B.initPage();
			$('#btnSearch').click(function() {
			    $('#detailb2').submit();
			});
			 $('#btnSearch').keypress(function (e) {
					  if (e.which == 13) {
					    $('#detailb2').submit();
					    return false;    
					  }
					});
			$('#_crYear0').focus();
                        var id = "flg_crYear2";
                        DETAIL_B.showMSGNoData(id);
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
		showChart: function(array){
			var chart = $('#chartB3').highcharts();
			jQuery.each(array,function(index,value){
				chart.series[value].show();
			});
		},
		showRow: function(key) {
			$(key).show();
		},
		hideRow: function(key) {
			$(key).hide();
		},
		hideChart: function(array){
			var chart = $('#chartB3').highcharts();
			jQuery.each(array,function(index,value){
				chart.series[value].hide();
			});
		},
		displayChart: function(year0, year1, year2){
			var data = [];
			if(year0) {
				DETAIL_B.addArray(data, DETAIL_B.year0Chart);
				DETAIL_B.showRow('.rowY0');
				DETAIL_B.showRow('#_crNotiY0');
				DETAIL_B.showChart(DETAIL_B.year0Chart);
				} else {
					DETAIL_B.hideRow('.rowY0');
					DETAIL_B.hideRow('#_crNotiY0');
					DETAIL_B.hideChart(DETAIL_B.year0Chart);
				}
			if(year1){ 
				DETAIL_B.addArray(data, DETAIL_B.year1Chart);
				DETAIL_B.showRow('.rowY1');
				DETAIL_B.showRow('#_crNotiY1');
				DETAIL_B.showChart(DETAIL_B.year1Chart);
			} else {
				DETAIL_B.hideRow('.rowY1');
				DETAIL_B.hideRow('#_crNotiY1');
				DETAIL_B.hideChart(DETAIL_B.year1Chart);
			}
			if(year2){ 
				DETAIL_B.addArray(data, DETAIL_B.year2Chart);
				DETAIL_B.showRow('.rowY2');
				DETAIL_B.showRow('#_crNotiY2');
				DETAIL_B.showChart(DETAIL_B.year2Chart);
			} else {
				DETAIL_B.hideRow('.rowY2');
				DETAIL_B.hideRow('#_crNotiY2');
				DETAIL_B.hideChart(DETAIL_B.year2Chart);
			}
		},
		initPage: function(){
			$('.detail-b-type-check').click(function(){
				var year0 = $('#_crYear0').is(':checked');
				var year1 = $('#_crYear1').is(':checked');
				var year2 = $('#_crYear2').is(':checked');
				if(year0 == false && year1 == false && year2 == false)
				{
					 $('#detail-b-max-rate').addClass('display-none');
				}
				else
				{
					 $('#detail-b-max-rate').removeClass('display-none');
				}
				DETAIL_B.displayChart(year0, year1, year2);
                                var id = "flg"+$(this).attr("id");
                                DETAIL_B.showMSGNoData(id);
			});
		},
		initChart: function(){
			$(function () {
				$('#chartB3').highcharts({
					chart: {
						backgroundColor: '#f2f2f2',
						marginTop: 0,
						marginLeft: 205,
						marginBottom: 0,
						marginRight: 0,
						type: 'column',
					},
					title: {
						text: 'Combination chart',
						style: {display: 'none'}
					},
					legend: {
						enabled: false
					},
					credits: {
						enabled: false
					},
					xAxis: {
						categories: ['1級', '準1級', '2級', '準2級', '3級', '4級', '5級'],
						labels: {enabled: false}
					},
					yAxis: {
						min: 0,
						max:  maxRate > 100 ? maxRate + 20 : 100,
						title: {
							style: {display: 'none'},
							text: ''
						},
						labels: {enabled: false},
						gridLineColor: 'transparent'
					},
					plotOptions: {
						column: {
							grouping: true,
							borderWidth: 0
						},
						series: {
							borderWidth: 0,
							dataLabels: {
								enabled: true,
								allowOverlap: true,
								align: 'center',
								formatter: function () {
									if (this.y != 0) {
										return (this.y > 0 ? this.y + '%': '');
									} else {
										return null;
									}
								}
							}
						}
					},
					series: [
						//--- current year - 2
						{
							name: '本校-' + dataGraphJ.Y0[1]['year'],
							data: [{y: parseFloat(dataGraphJ.Y0[1]["orgRate"])},
								{y: parseFloat(dataGraphJ.Y0[2]["orgRate"])},
								{y: parseFloat(dataGraphJ.Y0[3]["orgRate"])},
								{y: parseFloat(dataGraphJ.Y0[4]["orgRate"])},
								{y: parseFloat(dataGraphJ.Y0[5]["orgRate"])},
								{y: parseFloat(dataGraphJ.Y0[6]["orgRate"])},
								{y: parseFloat(dataGraphJ.Y0[7]["orgRate"])}],
							color: '#9cdbc9',
							visible: false,
						},
						{
							name: dataJsonB.cityName + '-' + dataGraphJ.Y0[1]['year'],
							data: [{y: parseFloat(dataGraphJ.Y0[1]["cityRate"])},
								{y: parseFloat(dataGraphJ.Y0[2]["cityRate"])},
								{y: parseFloat(dataGraphJ.Y0[3]["cityRate"])},
								{y: parseFloat(dataGraphJ.Y0[4]["cityRate"])},
								{y: parseFloat(dataGraphJ.Y0[5]["cityRate"])},
								{y: parseFloat(dataGraphJ.Y0[6]["cityRate"])},
								{y: parseFloat(dataGraphJ.Y0[7]["cityRate"])}],
							color: '#4cbf9e',
							visible: false,
						},
						{
							name: '全国-' + dataGraphJ.Y0[1]['year'],
							data: [{y: parseFloat(dataGraphJ.Y0[1]["nationRate"])},
								{y: parseFloat(dataGraphJ.Y0[2]["nationRate"])},
								{y: parseFloat(dataGraphJ.Y0[3]["nationRate"])},
								{y: parseFloat(dataGraphJ.Y0[4]["nationRate"])},
								{y: parseFloat(dataGraphJ.Y0[5]["nationRate"])},
								{y: parseFloat(dataGraphJ.Y0[6]["nationRate"])},
								{y: parseFloat(dataGraphJ.Y0[7]["nationRate"])}],
							color: '#33a585',
							visible: false,
						},
						// current year - 1
						{
							name: '本校-' + dataGraphJ.Y1[1]['year'],
							data: [parseFloat(dataGraphJ.Y1[1]["orgRate"]),
								parseFloat(dataGraphJ.Y1[2]["orgRate"]),
								parseFloat(dataGraphJ.Y1[3]["orgRate"]),
								parseFloat(dataGraphJ.Y1[4]["orgRate"]),
								parseFloat(dataGraphJ.Y1[5]["orgRate"]),
								parseFloat(dataGraphJ.Y1[6]["orgRate"]),
				              	parseFloat(dataGraphJ.Y1[7]["orgRate"])
							],
							color: '#9cdbc9', // previous #0ebccc
//							visible: false,
						},
						{
							name: dataJsonB.cityName + '-' + dataGraphJ.Y1[1]['year'],
							data: [parseFloat(dataGraphJ.Y1[1]["cityRate"]),
								parseFloat(dataGraphJ.Y1[2]["cityRate"]),
								parseFloat(dataGraphJ.Y1[3]["cityRate"]),
								parseFloat(dataGraphJ.Y1[4]["cityRate"]),
								parseFloat(dataGraphJ.Y1[5]["cityRate"]),
								parseFloat(dataGraphJ.Y1[6]["cityRate"]),
								parseFloat(dataGraphJ.Y1[7]["cityRate"]),
							],
							color: '#4cbf9e', // previous #ff7e00
//							visible: false,
						},
						{
							name: '全国-' + dataGraphJ.Y1[1]['year'],
							data: [parseFloat(dataGraphJ.Y1[1]["nationRate"]),
								parseFloat(dataGraphJ.Y1[2]["nationRate"]),
								parseFloat(dataGraphJ.Y1[3]["nationRate"]),
								parseFloat(dataGraphJ.Y1[4]["nationRate"]),
								parseFloat(dataGraphJ.Y1[5]["nationRate"]),
								parseFloat(dataGraphJ.Y1[6]["nationRate"]),
								parseFloat(dataGraphJ.Y1[7]["nationRate"])
							],
							color: '#33a585', // previous #d9b9ff
//							visible: false,
						},
						// current year
						{
							name: '本校-' + dataGraphJ.Y2[1]['year'],
							data: [{y: parseFloat(dataGraphJ.Y2[1]["orgRate"])},
								{y: parseFloat(dataGraphJ.Y2[2]["orgRate"])},
								{y: parseFloat(dataGraphJ.Y2[3]["orgRate"])},
								{y: parseFloat(dataGraphJ.Y2[4]["orgRate"])},
								{y: parseFloat(dataGraphJ.Y2[5]["orgRate"])},
								{y: parseFloat(dataGraphJ.Y2[6]["orgRate"])},
								{y: parseFloat(dataGraphJ.Y2[7]["orgRate"])}],
							color: '#9cdbc9', // previous #2f8d00
                                                        visible: false,
						},
						{
							name: dataJsonB.cityName + '-' + dataGraphJ.Y2[1]['year'],
							data: [{y: parseFloat(dataGraphJ.Y2[1]["cityRate"])},
								{y: parseFloat(dataGraphJ.Y2[2]["cityRate"])},
								{y: parseFloat(dataGraphJ.Y2[3]["cityRate"])},
								{y: parseFloat(dataGraphJ.Y2[4]["cityRate"])},
								{y: parseFloat(dataGraphJ.Y2[5]["cityRate"])},
								{y: parseFloat(dataGraphJ.Y2[6]["cityRate"])},
								{y: parseFloat(dataGraphJ.Y2[7]["cityRate"])}],
							color: '#4cbf9e', // previous #e10215
                                                        visible: false,
						},
						{
							name: '全国-' + dataGraphJ.Y2[1]['year'],
							data: [{y: parseFloat(dataGraphJ.Y2[1]["nationRate"])},
								{y: parseFloat(dataGraphJ.Y2[2]["nationRate"])},
								{y: parseFloat(dataGraphJ.Y2[3]["nationRate"])},
								{y: parseFloat(dataGraphJ.Y2[4]["nationRate"])},
								{y: parseFloat(dataGraphJ.Y2[5]["nationRate"])},
								{y: parseFloat(dataGraphJ.Y2[6]["nationRate"])},
								{y: parseFloat(dataGraphJ.Y2[7]["nationRate"])}],
							color: '#33a585', // previous #febcbc
                                                        visible: false,
						}
					]
				});
				// display current year - 1 and hide previous years.
				DETAIL_B.displayChart(false,false,true);
			});
                            },
			showMSGNoData: function (id) {
				var flg = $("#" + id).val();
				var classj = $("#" + id).data("class");
				var idTable = $("#" + id).data("id");
				var dataYear = $("#" + id).data("year");
				if (flg == 0) {
					var datahtml = '<tr>'
						+ '<th class="rspYcr" style="width: 1%;">' + dataYear + '</th>'
						+ '<td colspan="8" class="text-left-10">検索条件に一致するデータがありません。</td>'
						+ '</tr>'
						+ '<input type="hidden" data-year="' + dataYear + '" data-class="' + classj + '" data-id="' + idTable + '" id="' + id + '" value="0">';
					$("#" + idTable).html(datahtml);
				}
			}
}