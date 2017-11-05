var ACTUAL_GOAL_LEVEL = {
    init: function() {
        $('#btnClear').click(function () {
            $("#year").val('');
            $("#orgSchoolYearId").val('');
            $("#typeDeem").prop('checked', true);
            $('#searchActualGoalLevel').submit();
        });
        $('#btnSearch').click(function() {
            $('#searchActualGoalLevel').submit();
        });
        
        $('.toggle').click(function () {
            var hiddenid = $(this).attr('data-id');
            if ($(this).attr('data-expand') == 'true')
            {
                $('.' + hiddenid).hide('fast');
                $(this).parent().parent().removeClass('resultacg-color');
                $(this).find('.search-chevron-down').removeClass('search-chevron-down').addClass('search-chevron-right');
                $(this).attr('data-expand', 'false')
            }
            else
            {
                $('.' + hiddenid).show();
                $(this).parent().parent().addClass('resultacg-color');
                $(this).attr('data-expand', 'true')
                $(this).find('.search-chevron-right').removeClass('search-chevron-right').addClass('search-chevron-down');

            }
        });
    },
    putDataToExamHistoryList : function(schoolYearId, classId){
        var schoolYearName = '';
        var className = '';
        if(schoolYearId > 0){
            schoolYearName = $('#orgschoolyear_' + schoolYearId).val();
        }
        $('#searchExamHistoryList input[name=orgSchoolYear]').val(schoolYearName);
        if(classId > 0){
            className = $('#class_' + classId).val();
        }
        $('#searchExamHistoryList input[name=classj]').val(className);
        
        $('#searchExamHistoryList').submit();
    }
};
