var imageLoading = '<div class="img-loading" style=\"font:14px Meiryo\"><img src="/img/loader.svg" /><span>Loading...</span></div>';
PageSize = 20;
PageIndex = 1;
function getQueryStrings(name, strUrl) {
    name = name.replace(/[\[]/, "\\\[").replace(/[\]]/, "\\\]");
    var regexS = "[\\?&#]" + name + "=([^&#]*)";
    var regex = new RegExp(regexS);
    var results = regex.exec(strUrl);
    if (results == null)
        return "";
    else
        return results[1];
}
function reLoadListContent() {
    var urlReload;
    if (getUrlReload() != null)
        urlReload = urlLists + getSpliter(urlLists) + getDefaultQueryValue() + "&" + getUrlReload();
    else
        urlReload = urlLists + getSpliter(urlLists) + getDefaultQueryValue();
    LoadListContent(urlReload);
}
function getUrlReload() {
    url = window.location.href;
    if (url.indexOf('#') != -1) {
        url = url.split('#');
        return url[1];
    }
}
function LoadListContent(urlContent) {
    $("#list-container").html('');
    loadAjaxContent(urlContent, '#list-container')
}
function loadAjaxContent(urlContent, container) {
    $.ajax({
        url: encodeURI(urlContent),
        cache: false,
        type: "POST",
        success: function (data) {
            $(container).html(data);

        }
    });
}
function getDefaultQueryValue() {
    return "PageSize=" + PageSize + "&Page=" + PageIndex;
}
function getSpliter(urlcheck) {
    var strReturn = "?";
    if (urlcheck.indexOf(strReturn) > -1)
        strReturn = "&";
    return strReturn;
}
 $(function () {
    $('#select_all').click(function(event) {  
        if(this.checked) { 
            $('.checkbox1').each(function() { 
                this.checked = true;               
            });
        }else{
            $('.checkbox1').each(function() { 
                this.checked = false;                       
            });         
        }
    });
    
});		

$('.checkbox1').click(function(event) {  //on click 
	  var check_checked = true;
  if(this.checked) { // check select status
      $('.checkbox1').each(function() { //loop through each checkbox
    	  if($(this).is(':checked')){
           	 
              
          }else{
        	  check_checked= false;
          }              
      });
      if(check_checked==true){
    	  $('.table #select_all').prop('checked', true);
      }
  }else{
	  $('.table #select_all').prop('checked', false);  
  }
});
function goBack() {
    window.history.back(-1);
}