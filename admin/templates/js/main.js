/*
 * jQuery windowResizeFix
 *
 */
(function($) {

	if (document.windowResizeFixFired) {
		return;
	}
	document.windowResizeFixFired = true;

	var $window = $(window),
		_wWidth = $window.width(),
		_wHeight = $window.height();

	$window.on('resize',

		function(event) {
			var _nWidth = $window.width(),
				_nHeight = $window.height();

			if (_wWidth == _nWidth && _wHeight == _nHeight) {
				event.preventDefault();
				event.stopImmediatePropagation();
				return;
			}
			_wWidth = _nWidth;
			_wHeight = _nHeight;
		});

})(jQuery);

function browse_uploads(target, width, height, scrollbar) {
	if (typeof width == 'undefined' || width == '') var width = screen.width * 0.8;
	if (typeof height == 'undefined' || height == '') var height = screen.height * 0.8;
	if (typeof scrollbar == 'undefined') var scrollbar = 0;
	var targetVal = document.getElementById(target).value;
	var left = (screen.width - width) / 2;
	var top = (screen.height - height) / 2;
	window.open('index.php?do=browser&type=image&target=' + target + '&tval=' + targetVal, 'imgpop', 'left=' + left + ',top=' + top + ',width=' + width + ',height=' + height + ',scrollbars=' + scrollbar + ',resizable=1');
}

function browse_dirs(target, width, height, scrollbar) {
	if (typeof width == 'undefined' || width == '') var width = screen.width * 0.8;
	if (typeof height == 'undefined' || height == '') var height = screen.height * 0.8;
	if (typeof scrollbar == 'undefined') var scrollbar = 0;
	var left = (screen.width - width) / 2;
	var top = (screen.height - height) / 2;
	window.open('index.php?do=browser&type=directory&target=' + target, 'imgpop', 'left=' + left + ',top=' + top + ',width=' + width + ',height=' + height + ',scrollbars=' + scrollbar + ',resizable=1');
}

function windowOpen(url, width, height, scrollbar, winname) {
	if (typeof width == 'undefined' || width == '') var width = screen.width * 0.8;
	if (typeof height == 'undefined' || height == '') var height = screen.height * 0.8;
	if (typeof scrollbar == 'undefined') var scrollbar = 1;
	if (typeof winname == 'undefined') var winname = 'pop';
	var left = (screen.width - width) / 2;
	var top = (screen.height - height) / 2;
	window.open(url, winname, 'left=' + left + ',top=' + top + ',width=' + width + ',height=' + height + ',scrollbars=' + scrollbar + ',resizable=1').focus();
}

function openLinkWindow(target, doc, document_alias) {
	if (typeof width == 'undefined' || width == '') var width = screen.width * 0.6;
	if (typeof height == 'undefined' || height == '') var height = screen.height * 0.6;
	if (typeof doc == 'undefined') var doc = 'Title';
	if (typeof scrollbar == 'undefined') var scrollbar = 1;
	var left = (screen.width - width) / 2;
	var top = (screen.height - height) / 2;
	window.open('index.php?doc=' + doc + '&target=' + target + '&document_alias=' + document_alias + '&do=docs&action=showsimple&cp={$sess}&pop=1', 'pop', 'left=' + left + ',top=' + top + ',width=' + width + ',height=' + height + ',scrollbars=' + scrollbar + ',resizable=1');
}

function openFileWindow(target, id, document_alias) {
	if (typeof width == 'undefined' || width == '') var width = screen.width * 0.6;
	if (typeof height == 'undefined' || height == '') var height = screen.height * 0.6;
	if (typeof doc == 'undefined') var doc = 'Title';
	if (typeof scrollbar == 'undefined') var scrollbar = 1;
	var left = (screen.width - width) / 2;
	var top = (screen.height - height) / 2;
	window.open('index.php?do=browser&id=' + id + '&type=file&target=navi&cp={$sess}', 'pop', 'left=' + left + ',top=' + top + ',width=' + width + ',height=' + height + ',scrollbars=' + scrollbar + ',resizable=1');
}

// Функция-плагин для включения tipsy сразу для всех классов внутри элемента
(function($) {
	$.fn.addTipsy = function() {
		this.find(' .topDir').tipsy({
			fade: false,
			gravity: 's',
			opacity: 0.9,
			live: true,
			delayOut: 0
		});
		this.find(' .topleftDir').tipsy({
			fade: false,
			gravity: 'se',
			opacity: 0.9,
			live: true,
			delayOut: 0
		});
		this.find(' .toprightDir').tipsy({
			fade: false,
			gravity: 'sw',
			opacity: 0.9,
			live: true,
			delayOut: 0
		});
		this.find(' .botDir').tipsy({
			fade: false,
			gravity: 'n',
			opacity: 0.9,
			live: true,
			delayOut: 0
		});
		this.find(' .bottomDir').tipsy({
			fade: false,
			gravity: 'n',
			opacity: 0.9,
			live: true,
			delayOut: 0
		});
		this.find(' .botleftDir').tipsy({
			fade: false,
			gravity: 'ne',
			opacity: 0.9,
			live: true,
			delayOut: 0
		});
		this.find(' .botrightDir').tipsy({
			fade: false,
			gravity: 'nw',
			opacity: 0.9,
			live: true,
			delayOut: 0
		});
		this.find(' .leftDir').tipsy({
			fade: false,
			gravity: 'e',
			opacity: 0.9,
			live: true,
			delayOut: 0
		});
		this.find(' .rightDir').tipsy({
			fade: false,
			gravity: 'w',
			opacity: 0.9,
			live: true,
			delayOut: 0
		});
	};
})(jQuery);

/**
 * Плагин для подключения сортировки к таблице
 *
 * @param mixed items селектор для сортируемых элементов default: 'tr'
 * @param mixed handle селектор для элемента, который активирует перетаскивание default: '.ico_move'
 * @param string url адрес, куда будет отправлена последовательность элементов
 * @param string key имя массива $_GET[key] default: 'sort'
 * @param string attr имя аттрибута, по которому считываются id элементов default: 'data-id'
 * @param string success текст, которые показывает всплывашка в случае успеха default: 'Порядок сохранён'
 */
(function($) {
	$.fn.tableSortable = function(options) {
		options = $.extend({}, $.fn.tableSortable.defaults, options);
		this.sortable({
			items: options.items,
			axis: 'y',
			cursor: 'move',
			tolerance: 'pointer',
			handle: options.handle,
			helper: 'clone',
			placeholder: 'sortable-placeholder',
			start: function(event, ui) {
				// задаём placeholder
				$(this).find(' .sortable-placeholder').html(ui.item.html()).css({
					'opacity': 0.2
				});
				// назначаем колонкам ширину
				origTd = $(this).find(' .sortable-placeholder td');
				ui.helper.find(' td').each(function(index, element) {
					$(element).width(origTd.eq(index).width());
				});
			},
			stop: function(event, ui) {
				// удаляем ширину колонок
				ui.item.find(' tr:first td').each(function(index, element) {
					$(element).width('');
				});
			},
			update: function(event, ui) {
				// отправляем результаты сортировки
				sorted = $(this).sortable('serialize', {
					key: options.key + '[]',
					attribute: options.attr
				});
				$.ajax({
					url: options.url + '&' + sorted,
					dataType: 'json',
					success: function(data) {
						if (options.success == true) {
							$.jGrowl(data['message'], {
								header: data['header'],
								theme: data['theme']
							});
						}
					}
				});
			}
		});
	};

	$.fn.tableSortable.defaults = {
		items: 'tr',
		handle: '.ico_move',
		url: 'index.php?',
		key: 'sort',
		attr: 'data-id',
		success: false
	};

})(jQuery);

//===== Tabs =====//
$.fn.simpleTabs = function()
{
	$("ul.tabs li").click(function()
	{
		$(this).parent().parent().find("ul.tabs li").removeClass("activeTab");
		$(this).addClass("activeTab");
		$(this).parent().parent().find(".tab_content").hide();

		var activeTab = $(this).find("a").attr("href");

		$(activeTab).show();

		$('.CodeMirror').each(function(i, el)
		{
			el.CodeMirror.refresh();
		});

		AveAdmin.select_form();
		AveAdmin.sticky_panel_refresh();

		return false;
	});
};


$.fn.extend({
	limit: function(limit, element) {
		var interval, f;
		var self = $(this);
		$(this).focus(function() {
			interval = window.setInterval(substring, 100)
		});
		$(this).blur(function() {
			clearInterval(interval);
			substring()
		});
		substringFunction = "function substring(){ var val = $(self).val();var length = val.length;if(length > limit){$(self).val($(self).val().substring(0,limit));}";
		if (typeof element != 'undefined') substringFunction += "if($(element).html() != limit-length){$(element).html((limit-length<=0)?'0':limit-length);}";
		substringFunction += "}";
		eval(substringFunction);
		substring()
	}
});


// Запускаем после загрузки документа
$(function() {
	$(document)
		.ajaxStart(function() {
			NProgress.start();
		})
		.ajaxStop(function() {
			NProgress.done();
		});
	// настройка аякса
	$.ajaxSetup({
		cache: false,
		error: function(jqXHR, exception) {
			if (jqXHR.status === 0) {
				$.alerts._overlay('hide');
				$.jGrowl(ajaxErrorStatus, {
					theme: 'error'
				});
			} else if (jqXHR.status == 404) {
				$.alerts._overlay('hide');
				$.jGrowl(ajaxErrorStatus404, {
					theme: 'error'
				});
			} else if (jqXHR.status == 401) {
				$.alerts._overlay('hide');
				$.jGrowl(ajaxErrorStatus401, {
					theme: 'error'
				});
			} else if (jqXHR.status == 500) {
				$.alerts._overlay('hide');
				$.jGrowl(ajaxErrorStatus500, {
					theme: 'error'
				});
			} else if (exception === 'parsererror') {
				$.alerts._overlay('hide');
				$.jGrowl(ajaxErrorStatusJSON, {
					theme: 'error'
				});
			} else if (exception === 'timeout') {
				$.alerts._overlay('hide');
				$.jGrowl(ajaxErrorStatusTimeOut, {
					theme: 'error'
				});
			} else if (exception === 'abort') {
				$.alerts._overlay('hide');
				$.jGrowl(ajaxErrorStatusAbort, {
					theme: 'error'
				});
			} else {
				$.alerts._overlay('hide');
				$.jGrowl(ajaxErrorStatusMess + jqXHR.responseText, {
					theme: 'error'
				});
			}
		}
	});

	if (typeof width == 'undefined' || width == '') var width = screen.width * 0.7;
	if (typeof height == 'undefined' || height == '') var height = screen.height * 0.7;

	//===== Dynamic Tables =====//
	oTable = $('#dinamTable').dataTable({
		"bJQueryUI": true,
		"sPaginationType": "full_numbers",
		"aaSorting": [
			[0, "desc"]
		],
		"sDom": '<""f>t<"F"lp>'
	});

	//===== Information boxes =====//
	$(".hideit").click(function() {
		$(this).fadeOut(400);
	});

	$("div[class^='widget']").simpleTabs(); //Run function on any div with class name of "Simple Tabs"

});

var AveAdmin = {

	initialized: false,

	initialize: function() {

		if (this.initialized) return;
		this.initialized = true;

		this.build();
		this.events();

	},

	build: function() {
		this.toggleMenu();
		this.clear_cache();
		this.clear_cache_sess();
		this.clear_cache_thumb();
		this.clear_revisions();
		this.clear_counter();
		this.cache_show();
		this.main_form();
		this.select_form();
		this.sticky_panel();
		this.customInput();
		this.fancy_box();
		this.fancy_frame();
		this.tooltip();
		this.place_holder();
		this.collapsible_elements();
	},

	events: function() {
		this.a_actions();
		this.drop_down();
		this.collapsible_select();
		this.confirm_logout();
		this.confirm_delete();
		this.ui_totop();
		this.modalDialog();
		this.trim();
	},

	ajax: function() {
		this.select_form();
		this.main_form();
		this.place_holder();
		//this.modalDialog();
	},

	//UItoTop
	ui_totop: function() {

		$().UItoTop({
			easingType: 'easeOutQuart'
		});
	},

	toggleMenu: function() {
		if ($("[id^='toggle']").length) {
			$.each(["LeftMenu"], function(key, value) {
				//Считываем cookie
				var toggle = $.cookie(value);
				//Проверяем cookie
				if (toggle == 'hidden') {
					$(".leftNav").addClass("hidden");
					$(".dd_page").css("display", "");
				} else {
					$("#leftNav_show span").addClass("close");
					$(".dd_page").css("display", "none");
				}

				$("[id='toggle-" + this + "']").click(function() {
					if ($(".leftNav").hasClass('hidden')) {
						$(".dd_page").css("display", "none");
						$(".leftNav").removeClass('hidden').addClass('visible');
						$("#leftNav_show span").addClass("close");
						$.cookie(value, 'visible');
					} else {
						$(".dd_page").css("display", "");
						$(".leftNav").removeClass('visible').addClass('hidden');
						$("#leftNav_show span").removeClass("close");
						$.cookie(value, 'hidden');
					}
				});
			});
		}
	},

	//Окно очистки кэша
	clear_cache: function() {

		$(".clearCache").click(function(event) {
			event.preventDefault();
			var title = clearCacheTitle;
			var confirm = clearCacheConfirm;
			jConfirm(
				confirm,
				title,
				function(b) {
					if (b) {
						$.alerts._overlay('hide');
						$.alerts._overlay('show');
						$.ajax({
							url: ave_path + 'admin/index.php?do=settings&sub=clearcache&ajax=run',
							type: 'POST',
							dataType: "json",
							data: ({
								templateCache: 1,
								templateCompiledTemplate: 1,
								moduleCache: 1,
								sqlCache: 1
							}),
							success: function(data) {
								$.alerts._overlay('hide');
								$.jGrowl(data[0], {
									theme: data[1]
								});
								$('#cachesize').html('0 Kb');
								$('.cachesize').html('0 Kb');
							}
						});

					}
				}
			);
		});

	},

	//Collapsible elements management
	collapsible_elements: function() {

		var width = $("div.content").width();

		$('.opened').collapsible({
			defaultOpen: 'opened',
			cssOpen: 'inactive',
			cssClose: 'normal',
			speed: 5,
			loadOpen: function(elem, opts) {
				elem.next().show();
			},
			loadClose: function(elem, opts) {
				elem.next().hide();
			}
		});

		$('.closed').collapsible({
			defaultOpen: '',
			cssOpen: 'inactive',
			cssClose: 'normal',
			speed: 5,
			loadOpen: function(elem, opts) {
				elem.next().show();
			},
			loadClose: function(elem, opts) {
				elem.next().hide();
			}
		});

	setTimeout(function() {
		AveAdmin.sticky_panel_refresh();
	}, 1);

	},

	//Окно очистки кэша + Сессий
	clear_cache_sess: function() {

		$(".clearCacheSess").click(function(event) {
			event.preventDefault();
			var title = clearCacheSessTitle;
			var confirm = clearCacheSessConfirm;
			jConfirm(
				confirm,
				title,
				function(b) {
					if (b) {
						$.alerts._overlay('hide');
						$.alerts._overlay('show');
						$.ajax({
							url: ave_path + 'admin/index.php?do=settings&sub=clearcache&ajax=run',
							type: 'POST',
							dataType: "json",
							data: ({
								templateCache: 1,
								templateCompiledTemplate: 1,
								moduleCache: 1,
								sqlCache: 1,
								sessionUsers: 1
							}),
							success: function(data) {
								$.alerts._overlay('hide');
								$.jGrowl(data[0], {
									theme: data[1]
								});
								$('#cachesize').html('0 Kb');
								$('.cachesize').html('0 Kb');
							}
						});
					}
				}
			);
		});

	},

	//Окно очистки ревизий документов
	clear_revisions: function() {

		$(".clearRev").click(function(event) {
			event.preventDefault();
			var title = clearRevTitle;
			var confirm = clearRevConfirm;
			jConfirm(
				confirm,
				title,
				function(b) {
					if (b) {
						$.ajax({
							url: ave_path + 'admin/index.php?do=settings&sub=clearrevision&ajax=run',
							type: 'POST',
							dataType: "json",
							success: function(data) {
								$.alerts._overlay('hide');
								$.jGrowl(data['message'], {
									header: data['header'],
									theme: data['theme']
								});
							}
						});
					}
				}
			);
		});
	},

	//Окно очистки ревизий документов
	clear_counter: function() {

		$(".clearCount").click(function(event) {
			event.preventDefault();
			var title = clearCountTitle;
			var confirm = clearCountConfirm;
			jConfirm(
				confirm,
				title,
				function(b) {
					if (b) {
						$.ajax({
							url: ave_path + 'admin/index.php?do=settings&sub=clearcounter&ajax=run',
							type: 'POST',
							dataType: "json",
							success: function(data) {
								$.alerts._overlay('hide');
								$.jGrowl(data['message'], {
									header: data['header'],
									theme: data['theme']
								});
							}
						});
					}
				}
			);
		});
	},

	//Окно очистки миниатюр изображений
	clear_cache_thumb: function() {

		$(".clearThumb").click(function(event) {
			event.preventDefault();
			var title = clearThumbTitle;
			var confirm = clearThumbConfirm;
			jConfirm(
				confirm,
				title,
				function(b) {
					if (b) {
						$.ajax({
							url: ave_path + 'admin/index.php?do=settings&sub=clearthumb&ajax=run',
							type: 'POST',
							dataType: "json",
							success: function(data) {
								$.alerts._overlay('hide');
								$.jGrowl(data[0], {
									theme: data[1]
								});
							}
						});
					}
				}
			);
		});
	},

	//Показать размер кэша
	cache_show: function() {

		$("#cacheShow").click(function(event, x) {
			event.preventDefault();
			var title = cacheShowTitle;
			var confirm = cacheShowConfirm;
			jConfirm(
				confirm,
				title,
				function(b) {
					if (b) {
						$.alerts._overlay('hide');
						$.alerts._overlay('show');
						$.ajax({
							url: ave_path + 'admin/index.php?do=settings&sub=showcache&ajax=run',
							type: 'POST',
							dataType: "json",
							data: ({
								showCache: 1
							}),
							success: function(data) {
								$.alerts._overlay('hide');
								$('#cachesize').html(data[0]);
							}
						});
					}
				}
			);
		});

	},

	//Окно удаления едемента
	confirm_delete: function() {

		$(document).on('click' , '.ConfirmDelete', function(event) {
			event.preventDefault();
			var href = $(this).attr('href');
			var title = $(this).attr('dir');
			var confirm = $(this).attr('name');
			jConfirm(
				confirm,
				title,
				function(b) {
					if (b) {
						$.alerts._overlay('show');
						window.location = href;
					}
				}
			);
		});

	},

	//Выход
	confirm_logout: function() {

		$(".ConfirmLogOut").click(function(event) {
			event.preventDefault();
			var href = $(this).attr('href');
			var title = logoutTitle;
			var confirm = logoutConfirm;
			jConfirm(
				confirm,
				title,
				function(b) {
					if (b) window.location = href;
				}
			);
		});

	},

	//Прилипающая панель с кнопками
	sticky_panel_refresh: function() {

		if ($("#saveBtn").length > 0) {

			$("#saveBtn").trigger('refresh');

			var offset = $('#saveBtn').offset(); //Положение кнопок на странице
			var width = $("div.content").width(); //ширина

			if ($(document).scrollTop() < offset.top - $(window).height()) {
				$('.saveBtn').addClass('fixedBtn').css({
					"width": width - 20
				});
			} else {
				$('.saveBtn').removeClass('fixedBtn').removeAttr('style');
			}
		}

	},

	//Прилипающая панель с кнопками
	sticky_panel: function() {

		if ($("#saveBtn").length > 0) {

			var offset = $('#saveBtn').offset(); //Положение кнопок на странице
			var width = $("div.content").width(); //ширина

			if ($(document).scrollTop() < offset.top - $(window).height()) {
				$('.saveBtn').addClass('fixedBtn').css({
					"width": width - 20
				});
			}

			$(window).scroll(function() {

				var offset = $('#saveBtn').offset(); //Положение кнопок на странице
				var scroll_top = $(document).scrollTop(); //высота прокрученной области
				var window_height = $(window).height(); //высота окна браузера
				var width = $("div.content").width(); //ширина

				if (scroll_top < offset.top - window_height) {
					$('.saveBtn').addClass('fixedBtn').css({
						"width": width - 20
					});
				} else {
					$('.saveBtn').removeClass('fixedBtn').removeAttr('style');
				}
			});

			$(window).on(
				'resize',
				function() {
					$(window).resize(function() {
						var width = $("div.content").width(); //ширина
						$('.saveBtn').css({
							"width": width - 20
						});
					});
				}
			);

		}

	},

	//Custom single file input
	customInput: function() {
		$("input[type=file].input_file").nicefileinput({
			label: 'Выбрать...'
		});
	},

	// jQuery UI Dialog
	modalDialog: function() {
		$('a.openDialog').on('click', function(event) {
			event.preventDefault();
			var idDialog = ($(this).attr('data-dialog')) ? $(this).attr('data-dialog') : '';
			var ajaxDialog = $('<div id="ajax-dialog' + '-' + idDialog + '" style="display:none;" class="ajax-dialog"></div>').appendTo('body');
			var dialogTitle = ($(this).attr('data-title')) ? $(this).attr('data-title') : 'Modal';
			var dialogModal = ($(this).attr('data-modal')) ? $(this).attr('data-modal') : false;
			var dialogHref = ($(this).attr('href')) ? $(this).attr('href') : 'index.php';
			var dialogWidth = ($(this).attr('data-width')) ? $(this).attr('data-width') : undefined;
			var dialogHeight = ($(this).attr('data-height')) ? $(this).attr('data-height') : undefined;
			var dialogTemplate = ($(this).attr('data-template')) ? $(this).attr('data-template') : '&onlycontent=1';

			if (typeof dialogWidth == 'undefined' || dialogWidth == '') var dialogWidth = $(window).width() * 0.9;
			if (typeof dialogHeight == 'undefined' || dialogHeight == '') var dialogHeight = $(window).height() * 0.8;

			ajaxDialog.dialog({
				autoOpen: false,
				modal: dialogModal,
				dialogClass: 'fixed-dialog',
				close: function(event, ui) {
					$(this).dialog('destroy').remove();
				}
			});

			$('#' + ajaxDialog.attr('id')).load(dialogHref + dialogTemplate, function() {
				ajaxDialog.dialog("option", "title", dialogTitle);
				if (typeof(dialogWidth) !== "undefined") {
					ajaxDialog.dialog("option", "width", dialogWidth);
					ajaxDialog.dialog("option", "height", dialogHeight);
				}
				ajaxDialog.dialog("open");
			});
			return false;
		});
	},

	//функция-аналог trim в php
	trim: function() {

		if (!String.prototype.trim) {
			String.prototype.trim = function() {
				return this.replace(/^\s+|\s+$/g, '');
			};
		}
	},

	//Tooltip
	tooltip: function() {
		$('body').addTipsy();
	},

	// Fancybox
	fancy_box: function() {
		if (typeof width == 'undefined' || width == '') var width = screen.width * 0.8;
		if (typeof height == 'undefined' || height == '') var height = screen.height * 0.7;
		$("a.fancy").fancybox({
			padding: 0,
			margin: '30px',
			autoScale: true,
			speedIn: 100,
			speedOut: 100,
			overlayOpacity: 0.5,
			overlayColor: "#000",
			centerOnScroll: true,
			width: width,
			height: height
		});
	},

	// Fancybox
	fancy_frame: function() {
		if (typeof width == 'undefined' || width == '') var width = screen.width * 0.8;
		if (typeof height == 'undefined' || height == '') var height = screen.height * 0.7;
		$("a.iframe").fancybox({
			padding: 0,
			margin: 0,
			width: width,
			height: height,
			autoScale: true,
			speedIn: 100,
			speedOut: 100,
			overlayOpacity: 0.5,
			overlayColor: "#000",
			centerOnScroll: true
		});
	},

	select_form: function() {
		setTimeout(function() {
			$(".mainForm select").styler({
				selectVisibleOptions: 5,
				selectSearch: false
			});

			$(".mainForm select").trigger('refresh');
		}, 100);
	},

	collapsible_select: function () {
		$('.head.closed').on('click', function(){
			AveAdmin.select_form();
		});
	},

	// Преобразование форм
	main_form: function() {
		$(".mainForm").jqTransform({
			imgPath: "../images"
		});
	},

	// Placeholder for all browsers
	place_holder: function() {
		$('input[placeholder], textarea[placeholder]').placeholder();
	},

	// A transactions
	a_actions: function() {
		$('.actions a').hover(function() {
			$(this).animate({
				opacity: 1.0
			}, 100);
		}, function() {
			$(this).animate({
				opacity: 0.5
			}, 100);
		});
	},

	// DropDown menu
	drop_down: function() {
		$(".dropdown").on("mouseenter mouseleave", function(event) {
			var ul = $(this).children("ul");
			ul.stop(true, true);
			if (event.type === "mouseenter") {
				ul.slideToggle(10);
			} else {
				ul.hide(10);
			}
		});
	}

};

$(document).keydown(function(event) {

	var numberOfOptions = $("#rubric_id > option").length;
	var selectedIndex = $("#rubric_id option:selected").val();

	switch (event.keyCode) {
		case 38: // UP Key
			if (selectedIndex > 0) {
				$("#rubric_id").val(parseInt($("#rubric_id option:selected").val()) - 1);
			}
			break;
		case 40: // DOWN Key
			if (selectedIndex < numberOfOptions - 1) {
				$("#rubric_id").val(parseInt($("#rubric_id option:selected").val()) + 1);
			}
			break;
	}

});

$(document).ready(function() {
	AveAdmin.initialize();
});