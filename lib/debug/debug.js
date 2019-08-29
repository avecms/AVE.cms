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
		//
	},

	events: function () {
		this.debugButton();
		this.debugTabs();
	},

	debugButton: function () {
		$('#debug_btn').on('click', function(event) {

			event.preventDefault();

			let $bar = $('#debug_bar');

			if ($bar.css('display') === 'none') {
				$(document.body).css('overflow', 'hidden');
				$bar.show();
			} else {
				$(document.body).css('overflow', 'inherit');
				$bar.hide();
			}

			Debug.debugStorage();
		});
	},

	debugTabs: function () {
		$('.debug_tabs > li').on('click', function(event) {

			event.preventDefault();

			$('.debug_tabs > li').removeClass('selected');

			$('.debug_tab').hide();

			$(this).addClass('selected');

			$('#' + this.id + '-cont').show();

			localStorage.setItem('__debug_bar', this.id);
		});
	},

	debugStorage: function () {

		let localValue = localStorage.getItem('__debug_bar');

		if (localValue !== '') {

			let tab = $('.debug_tabs > li#' + localValue);

			if (tab.length > 0) {
				tab.click();
			}
		}
	}
};

$(document).ready(function() {
	Debug.init();
});