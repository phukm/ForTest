$.jiemMobile = {};
$.jiemMobile.options = {
		  //Add slimscroll to navbar menus
		  //This requires you to load the slimscroll plugin
		  //in every page before app.js
		  navbarMenuSlimscroll: true,
		  navbarMenuSlimscrollWidth: "3px", //The width of the scroll bar
		  navbarMenuHeight: "200px", //The height of the inner menu
		  //Sidebar push menu toggle button selector
		  sidebarToggleSelector: "[data-toggle='offcanvas']",
		  //Activate sidebar push menu
		  sidebarPushMenu: true,
		  //Activate sidebar slimscroll if the fixed layout is set (requires SlimScroll Plugin)
		  sidebarSlimScroll: true,
		  //Enable sidebar expand on hover effect for sidebar mini
		  //This option is forced to true if both the fixed layout and sidebar mini
		  //are used together
		  sidebarExpandOnHover: false,
		  //BoxRefresh Plugin
		  enableBoxRefresh: true,
		  //Bootstrap.js tooltip
		  enableBSToppltip: true,
		  BSTooltipSelector: "[data-toggle='tooltip']",
		  //Enable Fast Click. Fastclick.js creates a more
		  //native touch experience with touch devices. If you
		  //choose to enable the plugin, make sure you load the script
		  //before jiemMobile's app.js
		  enableFastclick: true,
		  //Control Sidebar Options
		  enableControlSidebar: true,
		  controlSidebarOptions: {
		    //Which button should trigger the open/close event
		    toggleBtnSelector: "[data-toggle='control-sidebar']",
		    //The sidebar selector
		    selector: ".control-sidebar",
		    //Enable slide over content
		    slide: true
		  }
		
		
		
		  
		};

		/* ------------------
		 * - Implementation -
		 * ------------------
		 * The next block of code implements jiemMobile's
		 * functions and plugins as specified by the
		 * options above.
		 */
		$(function () {
		  //Extend options if external options exist
		  if (typeof jiemMobileOptions !== "undefined") {
		    $.extend(true,
		            $.jiemMobile.options,
		            jiemMobileOptions);
		  }
		  //Easy access to options
		  var o = $.jiemMobile.options;

		  //Set up the object
		  _init();

		  //Activate the layout maker
		  $.jiemMobile.layout.activate();

		  //Enable sidebar tree view controls
		  
		  //Enable control sidebar
		  

		  //Add slimscroll to navbar dropdown
		  if (o.navbarMenuSlimscroll && typeof $.fn.slimscroll != 'undefined') {
		    $(".navbar .menu").slimscroll({
		      height: o.navbarMenuHeight,
		      alwaysVisible: false,
		      size: o.navbarMenuSlimscrollWidth
		    }).css("width", "100%");
		  }
		  /*
		   * INITIALIZE BUTTON TOGGLE
		   * ------------------------
		   */
		  $('.btn-group[data-toggle="btn-toggle"]').each(function () {
		    var group = $(this);
		    $(this).find(".btn").on('click', function (e) {
		      group.find(".btn.active").removeClass("active");
		      $(this).addClass("active");
		      e.preventDefault();
		    });

		  });
		});

function _init() {
	$.jiemMobile.layout = {
    activate: function () {
      var _this = this;
      _this.fix();
      
      $(window, ".wrapper").resize(function () {
        _this.fix();       
      });
    },
    fix: function () {
      //Get window height and the wrapper height
      var neg = $('.lower-footer').outerHeight() +$('#stltp-footer').outerHeight() + $('#menubar').outerHeight()+ $('.top-navbar').outerHeight();
      var window_height = $(window).height();    
      var sidebar_height = $(".sidebar").height();
      //Set the min-height of the content and sidebar based on the
      //the height of the document.
      if ($("body").hasClass("fixed")) {
        $(".content-wrapper, .right-side").css('min-height', window_height - $('.main-footer').outerHeight());
      } else {
        var postSetWidth;
        if (window_height >= sidebar_height) {
          $(".content-wrapper").css('min-height', window_height - neg);
          postSetWidth = window_height - neg;
        } else {
          $(".content-wrapper").css('min-height', sidebar_height);
          postSetWidth = sidebar_height;
        }

        //Fix for the control sidebar height
        var controlSidebar = $($.jiemMobile.options.controlSidebarOptions.selector);
        if (typeof controlSidebar !== "undefined") {
          if (controlSidebar.height() > postSetWidth)
            $(".content-wrapper, .right-side").css('min-height', controlSidebar.height());
        }

      }
    }
    };
    }
