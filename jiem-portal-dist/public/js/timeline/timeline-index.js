//---- OPEN LINK AJAX
function openUrl(url_request, ddlExamName, ddlKai, ddlYear) {
	$.ajax({
		type : 'POST',
		url : url_request,
		data : {
			'ddlExamName' : ddlExamName,
			'ddlKai' : ddlKai,
			'ddlYear' : ddlYear
		}
	}).done(function() {
		window.location.href = url_request;
	});
}
// --- RESET FONT FOR HOME PAGE
$(document).ready(function() {
	// Reset Font Size
	var st = $('.fix-font-1').text();
	var st1 = $('.fix-font-2').text();
	st = $.trim(st.replace('人', ''));
	st1 = $.trim(st1.replace('人', ''));
	var st2 = $('.dv').text();
	st2 = $.trim(st2.replace('人', ''));

	if (st.length > 2 || st1.length > 2) {
		$('.box-l').css('font-size', 32);
	}
	if (st.length > 3 || st1.length > 3) {
		$('.box-l').css('font-size', 23);
	}
	if (st.length > 4 || st1.length > 4) {
		$('.box-l').css('font-size', 17);
	}
	if (st.length > 6 || st1.length > 6) {
		$('.box-l').css('font-size', 14);
	}
	if (st2.length > 3) {
		$('.dv').css('font-size', 18);
	}
	if (st2.length > 4) {
		$('.dv').css('font-size', 15);
	}
	if (st2.length > 5) {
		$('.dv').css('font-size', 10);
	}
});
// ----- TIMELINE FOR HOMEPAGE
var data = new Array();
$.each(datas, function(index, value) {
	if (value.start) {
		value.start = new Date(value.start);
	}
	if (value.end) {
		value.end = new Date(value.end);
	}
	data.push(value);
});
google.load("visualization", "1");
// Set callback to run when API is loaded
google.setOnLoadCallback(drawVisualization);
var timeline;

// Called when the Visualization API is loaded.
function drawVisualization() {
	// Set data here (json data)
	// specify options
	var options = {
		width : "2392px",
		height : "100px",
		editable : false, // enable dragging and editing events
		enableKeys : false,
		axisOnTop : true,
		showNavigation : true,
		showButtonNew : false,
		animate : false,
		animateZoom : true,
		layout : "Dot",
		style : "dot",
		showCurrentTime : false,
		locale : "ja",
		min : new Date(currYear,0,0),
		max : new Date(currYear + 1, 12, 10)
	};

	timeline = new links.Timeline(document.getElementById('mytimeline1'), options);
	timeline.draw(data);

}
// create a simple animation
var animateTimeout = undefined;
var animateFinal = undefined;
function animateTo(date) {
	// get the new final date
	animateFinal = date.valueOf();
	timeline.setCustomTime(date);

	// cancel any running animation
	animateCancel();
	// animate towards the final date
	var animate = function() {
		var range = timeline.getVisibleChartRange();
		var current = (range.start.getTime() + range.end.getTime()) / 2;
		var width = (range.end.getTime() - range.start.getTime());
		var minDiff = Math.max(width / 1000, 1);
		var diff = (animateFinal - current);
		if (Math.abs(diff) > minDiff) {
			// move towards the final date
			var start = new Date(range.start.getTime() + diff / 4);
			var end = new Date(range.end.getTime() + diff / 4);
			timeline.setVisibleChartRange(start, end);
			// start next timer
			animateTimeout = setTimeout(animate, 50);
		}
	};
	animate();
}
function animateCancel() {
	if (animateTimeout) {
		clearTimeout(animateTimeout);
		animateTimeout = undefined;
	}
}