$(document).ready(function() {

	$('#debug_btn').on('click', function() {

		$bar = $('#debug_bar');

		if ($bar.css('display') == 'none') {
			$(document.body).css('overflow', 'hidden');
			$bar.show();
		} else {
			$(document.body).css('overflow', '');
			$bar.hide();
		}

	});

	$('.debug_tabs > li').on('click', function() {
		$('.debug_tabs > li').removeClass('selected');
		$('.debug_tab').hide();
		$(this).addClass('selected');
		$('#'+this.id+'-cont').show();
		if($.cookie){
			$.cookie('__debug_bar', this.id, {expires: 7, path: '/'});
		}
	});

	if($.cookie){
		var id = $.cookie('__debug_bar');
		var tab = $('.debug_tabs > li#'+id);
		if(tab.length > 0){
			tab.click();
		}
	}

}); //document.ready