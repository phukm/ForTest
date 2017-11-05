var PUPIL_ACHIEVEMENT = {
	init : function(){
		$('form:first *:input[type!=hidden][disabled!=disabled]:first').focus();

	    $('#submitsearch').on('click', function(){
	    	$("#list").submit();
	    });

	    $('#clearsearch').on('click', function(){
	        $('#orgSchoolYear').val('');
	        $("#classj").val('');
			$("#name").val('');

			$("#list").submit();
	    });
	}
}

$(document).ready(function() {
	PUPIL_ACHIEVEMENT.init();
});
