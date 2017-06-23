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
				$("#preview__"+id).attr("src", "../index.php?mode=t&thumb="+files['url']);
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
	$('#elRTE a').delay(800).animate({'background-position' : '0 0'}, 300);

	// нормальный редактор
	var opt = {
		cssClass : 'el-rte',
		toolbar  : 'maxi',
		lang     : 'ru',
		allowTextNodes : 'true',
		height   : 500,
		cssfiles : [ave_path+'lib/redactor/elrte/css/elrte-inner.css'],
		fmOpen : function(callback) {
			$('<div />').dialogelfinder({
			   url : ave_path+'lib/redactor/elfinder/php/connector.php',
			   lang : 'ru',
			   width : 1100,
			   height : 500,
			   title : 'Файловый менеджер',
			   commandsOptions : {
			   getfile : {
				   onlyURL	: true,
				   multiple : false,
				   folders	: false,
				   oncomplete : 'destroy'
			   }
				},
			   getFileCallback: callback
		   })
		}
	};

	$('.maxi-editor').elrte(opt);

	// нормальный редактор
	var opt = {
		cssClass : 'el-rte',
		toolbars :  {
			medium : ['format', 'save', 'copypaste', 'undoredo', 'style', 'alignment', 'lists', 'links', 'images', 'fullscreen']
		},
		toolbar  : 'medium',
		lang     : 'ru',
		allowTextNodes : 'true',
		height   : 500,
		cssfiles : [ave_path+'lib/redactor/elrte/css/elrte-inner.css'],
		fmOpen : function(callback) {
			$('<div />').dialogelfinder({
			   url : ave_path+'lib/redactor/elfinder/php/connector.php',
			   lang : 'ru',
			   width : 1100,
			   height : 500,
			   title : 'Файловый менеджер',
			   commandsOptions : {
			   getfile : {
				   onlyURL	: true,
				   multiple : false,
				   folders	: false,
				   oncomplete : 'destroy'
			   }
				},
			   getFileCallback: callback
		   })
		}
	};

	$('.medium-editor').elrte(opt);

	// упрощенный редактор
	var opts = {
		cssClass : 'el-rte',
		toolbars :  {
			small : ['format', 'copypaste', 'undoredo', 'alignment', 'lists', 'links']
		},
		toolbar  : 'small',
		lang	   : 'ru',
		allowTextNodes : 'true',
		height   : 250,
		cssfiles : [ave_path+'lib/redactor/elrte/css/elrte-inner.css']
	};
	$('.small-editor').elrte(opts);

});
