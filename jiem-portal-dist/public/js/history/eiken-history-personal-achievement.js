$(function () {
	if($('.scrollup').length){
		$('.scrollup').click(function () {
	        $("html, body").animate({
	            scrollTop: 0
	        }, 600);
	        return false;
	    });
	}
        $("#tab1").on("click", function () {
            $(".b3").css("display", "");
            $(".b4").css("display", "");
            $(".b5").css("display", "");
            $(".b7").css("display", "none"); //tab2
            $(".b6-1").css("display", "");
            $(".b6-2").css("display", "none"); //tab2
            //function for ticket GNCCNCJDR5-732
            $(".nav-tabs>li>#tab1").css("color","#000");
            $(".nav-tabs>li>#tab2").css("color","#555");

        });
        $("#tab2").on("click", function () {
            $(".b3").css("display", "none");
            $(".b4").css("display", "none");
            $(".b5").css("display", "none");
            $(".b7").css("display", ""); //tab2
            $(".b6-1").css("display", "none");
            $(".b6-2").css("display", ""); //tab2
            //function for ticket GNCCNCJDR5-732
            $(".nav-tabs>li>#tab1").css("color", "#555");
            $(".nav-tabs>li>#tab2").css("color","#000");
        });
	//--- Fix bolder	
	$(".per-achi .b3 table > tbody > tr > td").each(function(){		
		   // check the text of the table cell		
		   if ($(this).text() == "Ο")			  
		       $(this).html("<strong>Ο</strong>");
		});
	//Fix for ie 8
    var ver = getInternetExplorerVersion();
    if (ver > -1) {
        if (ver <= 8.0)            
          {
        	$(".per-achi .tb-striped-c-e6 > tbody >tr> td:nth-of-type(2n + 1)").css("background-color","#e6e6e6");
    		$(".per-achi .tb-striped-r-f2> tbody > tr:nth-of-type(2n+2)").css("background-color","#f2f2f2");
    		$(".per-achi .tb-striped-r-e6> tbody > tr:nth-of-type(2n+1)").css("background-color","#e6e6e6");	
    		/*table 1*/ 
    		$(".per-achi .b1 .tb1-str-td > tbody >tr> td:nth-of-type(1)").css({"background-color":"#e6e6e6", "width": "55px", "font-weight": "bolder"});
    		$(".per-achi .b1 .tb1-str-td > tbody >tr> td:nth-of-type(2)").css("border-right","0px");
    		
    		/*table 2*/	
    		$(".per-achi .b1 .tb2-str-td > tbody >tr> td:nth-of-type(1)").css("width","85px"); 
    		$(".per-achi .b1 .tb2-str-td > tbody >tr> td:nth-of-type(2)").css("width","55px");
    		$(".per-achi .b1 .tb2-str-td > tbody >tr> td:nth-of-type(3)").css("width","100px"); 
    		$(".per-achi .b1 .tb2-str-td > tbody >tr> td:nth-of-type(4)").css("width","100px"); 
    		$(".per-achi .b1 .tb2-str-td > tbody >tr> td:nth-of-type(5)").css("width","85px");
    		$(".per-achi .b1 .tb2-str-td > tbody >tr> td:nth-of-type(6)").css("width","90px");
    		$(".per-achi .b1 .tb2-str-td> tbody >tr> td:nth-of-type(2n + 1)").css("font-weight","bolder");

    		/*BOX-2*/	
    		$(".per-achi .b2 table> tbody > tr:nth-of-type(1)").css("font-weight","bolder");
    		$(".per-achi .b2 table> tbody > tr> td:nth-of-type(2)").css("background-color","#e6e6e6");
    		$(".per-achi .b2 table> tbody > tr:nth-of-type(3)").css({"background":"#fff","border": "0px"});
    		$(".per-achi .b2 .tab2> tbody > tr:nth-of-type(2)> td:nth-of-type(2)").css("background-color","#f79646");
    		
    		/*BOX-3*/
    		$(".per-achi .b3 table> tbody > tr:nth-of-type(4n+1)>td").css({"background-color": "#e6e6e6", "font-weight":"bolder","font-size": "14px"});
    		$(".per-achi .b3 table> tbody > tr> td:nth-of-type(1)").css({"background-color": "#e6e6e6","font-weight": "bolder", "width": "95px!important", "font-size": "14px !important", "padding": "0px 15px 0px 15px"});
    		$(".per-achi .b3 table > tbody > tr:nth-of-type(4n+4) > td").css({"font-size":"13px","font-weight": "600"});
    		
    		/*BOX-4*/
    		
    		$(".per-achi .b4 table> thead> tr> td:nth-of-type(1)").css("border-right","1px solid #d9d9d9");
    		$(".per-achi .b4 table> thead> tr> td:nth-of-type(2)").css("border-right","1px solid #d9d9d9");	
    		$(".per-achi .b4 table> tbody > tr> td:nth-of-type(1)").css({"background-color": "#e6e6e6","font-weight": "bolder","width": "110px!important","font-size": "14px !important","padding": "0px 15px 0px 15px","text-align": "left"});
    		$(".per-achi .b4 table> tbody > tr> td:nth-of-type(2)").css("width", "166px");
    		$(".per-achi .b4 table> tbody > tr> td:nth-of-type(3)").css("width", "72px");

    		/*BOX-5*/	
    		$(".per-achi .b5 table> thead> tr> td:nth-of-type(n)").css("border-right","1px solid #d9d9d9");
    		$(".per-achi .b5 table> tbody > tr:nth-of-type(1)> td:nth-of-type(1)").css({"background-color": "#e6e6e6","font-weight": "bolder", "width": "110px!important","font-size": "14px !important", "padding": "0px 15px 0px 15px", "text-align": "left"});
    		$(".per-achi .b5 table> tbody > tr:nth-of-type(2)> td:nth-of-type(1)").css({"background-color": "#e6e6e6","font-weight": "bolder", "width": "110px!important","font-size": "14px !important", "padding": "0px 15px 0px 15px", "text-align": "left"});
    		$(".per-achi .b5 table> tbody > tr:nth-of-type(4)> td:nth-of-type(1)").css({"background-color": "#e6e6e6","font-weight": "bolder", "width": "110px!important","font-size": "14px !important", "padding": "0px 15px 0px 15px", "text-align": "left"});
    		$(".per-achi .b5 table> tbody > tr:nth-of-type(8)> td:nth-of-type(1)").css({"background-color": "#e6e6e6","font-weight": "bolder", "width": "110px!important","font-size": "14px !important", "padding": "0px 15px 0px 15px", "text-align": "left"});
    		$(".per-achi .b5 table> tbody > tr> td:nth-of-type(2)").css({"width": "152px","text-align": "left"});
    		$(".per-achi .b5 table> tbody > tr> td:nth-of-type(3)").css("width","120px");
    		$(".per-achi .b5 table> tbody > tr> td:nth-of-type(4)").css({"width": "177px","text-align": "justify"});
    		$(".per-achi .b5 table> tbody > tr> td:nth-of-type(5)").css("text-align","justify");
    		$(".per-achi .b5 table> tbody > tr:nth-of-type(4)> td:nth-of-type(5)").css("background-color","fff");
    		$(".per-achi .b5 table> tbody > tr:nth-of-type(3)>  td:nth-of-type(1),	.per-achi .b5 table> tbody > tr:nth-of-type(5)>  td:nth-of-type(1),	.per-achi .b5 table> tbody > tr:nth-of-type(6)>  td:nth-of-type(1),	.per-achi .b5 table> tbody > tr:nth-of-type(7)>  td:nth-of-type(1)").css("text-align","left");
    		$(".per-achi .b5 table> tbody > tr:nth-of-type(3)>  td:nth-of-type(2),	.per-achi .b5 table> tbody > tr:nth-of-type(5)>  td:nth-of-type(2),	.per-achi .b5 table> tbody > tr:nth-of-type(6)>  td:nth-of-type(2),	.per-achi .b5 table> tbody > tr:nth-of-type(7)>  td:nth-of-type(2)").css("text-align","center");
    		$(".per-achi .b5 table> tbody > tr:nth-of-type(3)>  td:nth-of-type(3),	.per-achi .b5 table> tbody > tr:nth-of-type(5)>  td:nth-of-type(3),	.per-achi .b5 table> tbody > tr:nth-of-type(6)>  td:nth-of-type(3),	.per-achi .b5 table> tbody > tr:nth-of-type(7)>  td:nth-of-type(3)").css("text-align","justify");

    		/*BOX-6*/
    		$(".per-achi .b6 table > tbody >tr:nth-of-type(1)").css("background-color","#f2f2f2");
    		$(".per-achi .b6 table > tbody >tr:nth-of-type(1)>td:nth-of-type(3)").css("background-color","#fff");
    		$(".per-achi .b6 table > tbody >tr:nth-of-type(1)> td:nth-of-type(1)").css({"background-color": "#e4a1a1", "width": "95px","font-weight": "bolder", "padding-left": "14px", "text-align": "left"});
    		$(".per-achi .b6 .tb1 > tbody >tr:nth-of-type(1)> td:nth-of-type(2)").css("width","112px");
    		$(".per-achi .b6 .tb2 tr:nth-of-type(2)> td:nth-of-type(1),.per-achi .b6 .tb2 td:nth-of-type(2)").css("padding-left","70px");

    		/*TAB-BOX-7*/
    		
    		$(".per-achi .b7 table> thead> tr> td:nth-of-type(1)").css("border-right","1px solid #d9d9d9");
    		$(".per-achi .b7 table> thead> tr> td:nth-of-type(2)").css("border-right","1px solid #d9d9d9");	
    		$(".per-achi .b7 table> tbody > tr> td:nth-of-type(1)").css({"background-color": "#e6e6e6", "font-weight": "bolder", "width": "120px!important","text-align": "left"});
    		$(".per-achi .b7 table> tbody > tr> td:nth-of-type(2)").css({"width": "148px","text-align": "center"});
    		$(".per-achi .b7 table> tbody > tr:nth-of-type(4)> td:nth-of-type(3)").css({"background-color": "#fff", "border": "0px"});
          }
    }	   
});

function getInternetExplorerVersion() {

    var rv = -1; // Return value assumes failure.
    if (navigator.appName == 'Microsoft Internet Explorer') {
        var ua = navigator.userAgent;
        var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
        if (re.exec(ua) != null)
            rv = parseFloat(RegExp.$1);
    }
    return rv;
}

function goBack() {
    window.history.back();
}