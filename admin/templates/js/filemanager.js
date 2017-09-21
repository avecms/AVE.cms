$().ready(function() {

	// отдельный файловый менеджер
	$('#finder').elfinder({
		url : ave_path+'lib/redactor/elfinder/php/connector.php',
		lang : 'ru',
	   height : 500,
	   title : 'Файловый менеджер'
	}).elfinder('instance');


	// диалог выбора изображений
	$('.dialog_images').click(function() {
		var id = $(this).attr("rel");
		$('<div/>').dialogelfinder({
			url : ave_path+'lib/redactor/elfinder/php/connector.php',
			lang : 'ru',
			width : 1100,
			height: 600,
			modal : true,
			title : 'Файловый менеджер',
			getFileCallback : function(files, fm) {
				$("#image__"+id).val('/'+files['url'].slice(1));
				$("#images_feld_"+id).html("<img src="+files['url']+">");
			},
			commandsOptions : {
				getfile : {
					oncomplete : 'destroy',
					folders : false
				}
			}
		})
	});


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
