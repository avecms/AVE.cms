/*
 * Name: 			Ave.cms Install Scripts
 * Written by: 		Ave.cms - (http://www.ave-cms.ru)
 * Version: 		1.0a
 */

(function($) {
	$.fn.addTipsy = function() {
		this.find(' .topDir').tipsy({fade: false, gravity: 's', opacity: 0.9});
		this.find(' .topleftDir').tipsy({fade: false, gravity: 'se', opacity: 0.9});
		this.find(' .toprightDir').tipsy({fade: false, gravity: 'sw', opacity: 0.9});
		this.find(' .botDir').tipsy({fade: false, gravity: 'n', opacity: 0.9});
		this.find(' .bottomDir').tipsy({fade: false, gravity: 'n', opacity: 0.9});
		this.find(' .botleftDir').tipsy({fade: false, gravity: 'ne', opacity: 0.9});
		this.find(' .botrightDir').tipsy({fade: false, gravity: 'nw', opacity: 0.9});
		this.find(' .leftDir').tipsy({fade: false, gravity: 'e', opacity: 0.9});
		this.find(' .rightDir').tipsy({fade: false, gravity: 'w', opacity: 0.9});
	};
})(jQuery);

var AveInstall = {

	initialized: false,

	initialize: function() {

		if (this.initialized) return;
		this.initialized = true;

		this.build();
		this.events();

	},

	build: function() {

		this.add_tipsy();
		this.placeholder();
		this.transform();
		this.ui_totop();
		this.link_hover();
		this.cancel();

	},

	events: function() {

		$.ajaxSetup({
			cache: false,
			error: function(jqXHR, exception) {
				if (jqXHR.status === 0) {
					$.alerts._overlay('hide');
					$.jGrowl(ajaxErrorStatus,{theme: 'error'});
				} else if (jqXHR.status == 404) {
					$.alerts._overlay('hide');
					$.jGrowl(ajaxErrorStatus404,{theme: 'error'});
				} else if (jqXHR.status == 401) {
					$.alerts._overlay('hide');
					$.jGrowl(ajaxErrorStatus401,{theme: 'error'});
				} else if (jqXHR.status == 500) {
					$.alerts._overlay('hide');
					$.jGrowl(ajaxErrorStatus500,{theme: 'error'});
				} else if (exception === 'parsererror') {
					$.alerts._overlay('hide');
					$.jGrowl(ajaxErrorStatusJSON,{theme: 'error'});
				} else if (exception === 'timeout') {
					$.alerts._overlay('hide');
					$.jGrowl(ajaxErrorStatusTimeOut,{theme: 'error'});
				} else if (exception === 'abort') {
					$.alerts._overlay('hide');
					$.jGrowl(ajaxErrorStatusAbort,{theme: 'error'});
				} else {
					$.alerts._overlay('hide');
					$.jGrowl(ajaxErrorStatusMess + jqXHR.responseText,{theme: 'error'});
				}
			}
		});

	},


	ui_totop: function() {

		$().UItoTop({ easingType: 'easeOutQuart' });
	},

	link_hover: function() {
		$('.actions a').hover(function(){
			$(this).animate({opacity: 1.0},100);
				},function(){
			$(this).animate({opacity: 0.5},100);
		});
	},

	placeholder: function() {
		$('input[placeholder], textarea[placeholder]').placeholder();
	},

	transform: function() {
		$(".mainForm").jqTransform({imgPath:"../images"});
	},

	cancel: function() {

		$("#ask-cancel").click( function(e) {
			e.preventDefault();
			var thisHref = 'exit.html';
			var title = cancelTitle;
			var confirm = cancelConfirm;
			jConfirm(
					confirm,
					title,
					function(b){
						if (b){
							$.alerts._overlay('hide');
							$.alerts._overlay('show');
							window.location = thisHref;
						}
					}
				);
		});
	},

	add_tipsy: function() {
		$('body').addTipsy();
	}

};


$(window).load(function () {

	AveInstall.initialize();

});