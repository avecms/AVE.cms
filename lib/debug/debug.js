var Debug = {

	initialized: false,

	init: function () {

		if (this.initialized)
			return;

		this.initialized = true;

		this.build();
		this.events();
	},

	build: function () {
		this.debugCookie();
	},

	events: function () {
		this.debugButton();
		this.debugTabs();
	},

	debugButton: function () {
		$('#debug_btn').on('click', function(event) {

			event.preventDefault();

			$bar = $('#debug_bar');

			if ($bar.css('display') == 'none') {
				$(document.body).css('overflow', 'hidden');
				$bar.show();
			} else {
				$(document.body).css('overflow', 'inherit');
				$bar.hide();
			}

		});
	},

	debugTabs: function () {
		$('.debug_tabs > li').on('click', function(event) {

			event.preventDefault();

			$('.debug_tabs > li').removeClass('selected');

			$('.debug_tab').hide();

			$(this).addClass('selected');

			$('#' + this.id + '-cont').show();

			if ($.cookie) {
				$.cookie('__debug_bar', this.id, {expires: 7, path: '/'});
			}
		});
	},

	debugCookie: function () {
		if ($.cookie) {
			var id = $.cookie('__debug_bar');

			var tab = $('.debug_tabs > li#' + id);

			if (tab.length > 0) {
				tab.click();
			}
		}
	}
};

$(document).ready(function() {
	Debug.init();
});