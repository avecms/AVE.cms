$(function() {
	// отдельный файловый менеджер
	$('#finder').elfinder({
		url : ave_path+'lib/redactor/elfinder/php/connector_template.php',
		lang : 'ru',
	   height : 500,
	   title : 'Файловый менеджер'
	}).elfinder('instance');

	$('#elFinder a').hover(
		function () {
			$('#elFinder a').animate({
				'background-position' : '0 -45px'
			}, 300);
		},
		function () {
			$('#elFinder a').delay(400).animate({
				'background-position' : '0 0'
			}, 300);
		}
	);
});
