var AveDocs = {

	initialized: false,

	init: function () {

		if (this.initialized)
			return;

		this.initialized = true;
	},


	//
	list: function () {
		this.addDocument();
		this.documentCopy();
		this.selectAllDocuments();
		this.documentAction();
		this.documentPublish();
		this.documentRecycle();
	},


	//
	edit: function () {
		this.revisionDelete();
		this.revisionRecover();
		this.revisionsDelete();
		this.translitURL();
		this.checkURLInput();
		this.editDateTime();
		this.linkSelect();
		this.metaKeywords();
		this.documentLanguage();
		this.saveEditBtn();
		this.editMousetrap();
		this.windowOnLoadCKEditor();
	},


	//
	search: function () {
		this.searchDateTime();
		this.searchCollapsible();
	},


	//
	addDocument: function () {
		$(".AddDocument").on('click', function (event) {
			event.preventDefault();

			let rubricId = $('#addDocRub #rubricId').fieldValue();

			if (rubricId == '') {
				jAlert(add_doc_text, add_doc_title);
			} else {
				$.alerts._overlay('show');
				$("#addDocRub").submit();
			}
		});
	},


	//
	selectAllDocuments: function () {
		$('#selectAll').on('change', function (event) {
			event.preventDefault();

			if ($('#selectAll').is(':checked')) {
				$('#docs .checkbox').attr('checked', 'checked').addClass('jqTransformChecked');
				$("#docs a.jqTransformCheckbox").addClass("jqTransformChecked");
			} else {
				$('#docs .checkbox').removeClass('jqTransformChecked').removeAttr('checked');
				$("#docs a.jqTransformCheckbox").removeClass("jqTransformChecked");
			}
		});
	},


	//
	documentPublish: function () {
		$(".documentPublish").on('click', function (event) {
			event.preventDefault();

			let link = $(this);
			let doc_id = link.data('id');

			$.ajax({
				type: 'POST',
				url: 'index.php?do=docs&action=publish&cp=' + sess,
				data: {
					'doc_id': doc_id
				},
				dataType: 'JSON',
				beforeSend: function () {
					$.alerts._overlay('show');
				},
				success: function (data) {
					$.alerts._overlay('hide');

					if (data.success) {

						(data.status != 1)
							? link.closest('tr').addClass('yellow')
							: link.closest('tr').removeClass('yellow');

						(data.status != 1)
							? link.addClass('public')
							: link.removeClass('public');

						link.attr('title', data.text);

						AveAdmin.tooltip();
					}

					$.jGrowl(data['message'], {
						header: data['header'],
						theme: data['theme']
					});
				}
			});
		});
	},


	//
	documentRecycle: function () {
		$(".documentRecycle").on('click', function (event) {
			event.preventDefault();

			let link = $(this);
			let link_tr = link.closest('tr');
			let link_publish = link_tr.find('.documentPublish');
			let doc_id = link.data('id');

			$.ajax({
				type: 'POST',
				url: 'index.php?do=docs&action=recycle&cp=' + sess,
				data: {
					'doc_id': doc_id
				},
				dataType: 'JSON',
				beforeSend: function () {
					$.alerts._overlay('show');
				},
				success: function (data) {
					$.alerts._overlay('hide');

					if (data.success) {

						(data.status == 1)
							? link_tr.removeClass('yellow').addClass('red')
							: link_tr.removeClass('red');

						(data.status == 1)
							? link.addClass('recylce')
							: link.removeClass('recylce');

						(data.status == 1)
							? link_publish.addClass('hidden')
							: link_publish.removeClass('hidden');

						if (data.status == 0 && link_publish.hasClass('public')) {
							link_tr.addClass('yellow')
						}

						link.attr('title', data.text);

						AveAdmin.tooltip();
					}

					$.jGrowl(data['message'], {
						header: data['header'],
						theme: data['theme']
					});
				}
			});
		});
	},


	//
	documentCopy: function () {
		$(".documentCopy").on('click', function (event) {
			event.preventDefault();

			let href = $(this).attr('href');

			jPrompt(copy_doc_text, '', copy_doc_title, function (data) {
				if (data) {
					$.alerts._overlay('show');
					window.location = href + '&document_title=' + data;
				} else {
					$.jGrowl(copy_doc_no, {theme: 'error'});
				}
			});
		});
	},


	//
	documentAction: function () {
		$(".docaction").hover(
			function () { $(this).children(".actions").show("fade", 10); },
			function() { $(this).children(".actions").hide("fade", 10); }
	 	);
	},


	//
	searchDateTime: function () {
		$('#document_published').datepicker({
			changeMonth: true,
			changeYear: true,

			onClose: function (dateText, inst) {
				var endDateTextBox = $('#document_expire');
				if (endDateTextBox.val() != '') {
					var testStartDate = new Date(dateText);
					var testEndDate = new Date(endDateTextBox.val());
					if (testStartDate > testEndDate)
						endDateTextBox.val(dateText);
				}
				else {
					endDateTextBox.val(dateText);
				}
			},
			onSelect: function (selectedDateTime) {
				var start = $(this).datetimepicker('getDate');
				$('#document_expire').datetimepicker('option', 'minDate', new Date(start.getTime()));
			}
		});

		$('#document_expire').datepicker({
			changeMonth: true,
			changeYear: true,

			onClose: function (dateText, inst) {
				var startDateTextBox = $('#document_published');
				if (startDateTextBox.val() != '') {
					var testStartDate = new Date(startDateTextBox.val());
					var testEndDate = new Date(dateText);
					if (testStartDate > testEndDate)
						startDateTextBox.val(dateText);
				}
				else {
					startDateTextBox.val(dateText);
				}
			},
			onSelect: function (selectedDateTime) {
				var end = $(this).datetimepicker('getDate');
				$('#document_published').datetimepicker('option', 'maxDate', new Date(end.getTime()));
			}
		});
	},


	//
	searchCollapsible: function () {
		$('.collapsible').collapsible({
			defaultOpen: 'opened',
			cssOpen: 'inactive',
			cssClose: 'normal',
			cookieName: 'collaps_doc',
			cookieOptions: {
				expires: 7,
				domain: ''
			},
			speed: 5,
			loadOpen: function (elem, opts) {
				elem.next().show();
			},
			loadClose: function (elem, opts) {
				elem.next().hide();
			}
		});

		$('.collapsible').on('click', function () {
			setTimeout(function () {
				AveAdmin.sticky_panel_refresh();
				AveAdmin.select_form();
			}, 10);
		});	
	},


	//
	revisionRecover: function () {
		$(".recoverRevision").on('click', function (event) {
			event.preventDefault();

			let href = $(this).attr('href'),
				title = $(this).data('title'),
				confirm = $(this).data('confirm');

			jConfirm(
				confirm,
				title,
				function (success) {
					if (success) {
						$.alerts._overlay('show');
						window.location = href;
					}
				}
			);
		});
	},


	//
	revisionDelete: function () {
		$(".deleteRevision").on('click', function (event) {
			event.preventDefault();

			let revission = $(this).data('rev'),
				href = $(this).attr('href'),
				title = $(this).data('title'),
				confirm = $(this).data('confirm');

			jConfirm(
				confirm,
				title,
				function (success) {
					if (success) {
						$.alerts._overlay('hide');
						$.alerts._overlay('show');
						$.ajax({
							url: ave_path + 'admin/' + href + '&ajax=run',
							type: 'POST',
							success: function (data) {
								$.alerts._overlay('hide');

								$.jGrowl(revission, { theme: 'accept' });

								$("#" + revission).remove();
							}
						});
					}
				}
			);
		});
	},


	//
	revisionsDelete: function () {
		$(".deleteRevisions").on('click', function (event) {
			event.preventDefault();

			let href = $(this).attr('href'),
				title = $(this).data('title'),
				confirm = $(this).data('confirm');

			jConfirm(
				confirm,
				title,
				function (success) {
					if (success) {
						$.alerts._overlay('hide');
						$.alerts._overlay('show');
						$.ajax({
							url: ave_path + 'admin/' + href,
							type: 'POST',
							dataType: 'JSON',
							success: function (data) {
								$.alerts._overlay('hide');

								$.jGrowl(data.message, { theme: 'accept' });

								$('#tableRevisions').find('tbody').html('');
							}
						});
					}
				}
			);
		});
	},


	//
	checkURL: function () {

		let alias = $("#document_alias").val(),
			doc_id = $('#formDoc').data('id');

		$.ajax({
			beforeSend: function () {
			},
			url: 'index.php',
			data: ({
				'action': 'checkurl',
				'do': 'docs',
				'check': false,
				'cp': sess,
				'id': doc_id,
				'alias': alias
			}),
			timeout: 3000,
			dataType: 'JSON',
			success:
				function (data) {
					$.jGrowl(data[0], {theme: data[1]});
				}
		});
	},


	//
	translitURL: function () {
		$("#translit").on('click', function () {

			let alias = $("#document_alias").val(),
				title = $("#document_title").val(),
				prefix = $('#formDoc').data('prefix');

			$.ajax({
				beforeSend: function () {
					$("#checkResult").html('');
				},
				url: 'index.php',
				data: ({
					'action': 'translit',
					'do': 'docs',
					'cp': sess,
					'alias': alias,
					'title': title,
					'prefix': prefix
				}),
				timeout: 3000,
				success: function (data) {
					$("#document_alias").val(data);
					AveDocs.checkURL();
				}
			});
		});
	},


	//
	checkURLInput: function () {
		$("#document_alias").on('change', function () {
			if ($(this).val() != '')
				AveDocs.checkURL();
		});
	},


	//
	editDateTime: function () {
		$('#document_published').datetimepicker({
			changeMonth: true,
			changeYear: true,
			stepHour: 1,
			stepMinute: 1,

			onClose: function (dateText, inst) {
				var endDateTextBox = $('#document_expire');
				if (endDateTextBox.val() != '') {
					var testStartDate = new Date(dateText);
					var testEndDate = new Date(endDateTextBox.val());
					if (testStartDate > testEndDate)
						endDateTextBox.val(dateText);
				}
				else {
					endDateTextBox.val(dateText);
				}
			}
		});

		$('#document_expire').datetimepicker({
			changeMonth: true,
			changeYear: true,

			stepHour: 1,
			stepMinute: 1,

			onClose: function (dateText, inst) {
				var startDateTextBox = $('#document_published');
				if (startDateTextBox.val() != '') {
					var testStartDate = new Date(startDateTextBox.val());
					var testEndDate = new Date(dateText);
					if (testStartDate > testEndDate)
						startDateTextBox.val(dateText);
				}
				else {
					startDateTextBox.val(dateText);
				}
			},
			onSelect: function (selectedDateTime) {
				var end = $(this).datetimepicker('getDate');
				$('#document_published').datetimepicker('option', 'maxDate', new Date(end.getTime()));
			}
		});
	},


	//
	linkSelect: function () {
		$(".linkSelect").on('change', function() {
			let link = $(this).val(),
				parent = $(this).find('option:selected').attr("data-id"),
				prefix = $('#formDoc').data('prefix');

			if (prefix == '') {
				$("#document_alias").val(link);
			} else {
				$("#document_alias").val(link + '/' + prefix);
			}

			$("#document_parent").val(parent);

			return false;
		});
	},


	//
	metaKeywords: function () {
		$("#document_meta_keywords").autocomplete("index.php?do=docs&action=keywords&ajax=run&cp=" + sess, {
			max: 20,
			width: 300,
			highlight: false,
			multiple: true,
			multipleSeparator: ", ",
			autoFill: true,
			scroll: true,
			scrollHeight: 180
		});
	},


	//
	documentLanguage: function () {
		$('#document_lang').on('change', function () {
			
			let lang = $('#document_lang option:selected').val(),
				alias = $('#document_alias').val().split('/'),
				languages = [];

			$('#document_lang option').each(function () {
				languages.push($(this).attr('value'));
			});

			if ($.inArray(alias[0], languages) > -1) {
				alias.splice(0, 1);
			}

			if ((lang == defaultLang) || (lang == noneLanguage)) {
				$('#document_alias').val(alias.join('/'));
			} else {
				if (alias[0] != "") {
					$('#document_alias').val(lang + '/' + alias.join('/'));
				} else {
					$('#document_alias').val(lang);
				}
			}
		});

		$('#lang_block').hide();

		$('#show_lang').on('click', function (event) {
			event.preventDefault();

			$('#lang_block').show();
			$('#show_lang').hide();
		});
	},


	//
	documentSaveFunction: function () {
		let form = $('#formDoc');

		form.ajaxSubmit({
			url: form.attr('action'),
			dataType: 'JSON',
			beforeSubmit: function () {
				$.alerts._overlay('show');
			},
			success: function (data) {
				$.alerts._overlay('hide');

				$.jGrowl(data['message'], {
					header: data['header'],
					theme: data['theme']
				});
			}
		});
	},


	//
	saveDocument: function () {
		let form = $('#formDoc');

		if (window.CKEDITOR)
			for (var instanceName in CKEDITOR.instances)
				CKEDITOR.instances[instanceName].updateElement();

		if (form.data('id') > 0) {
			AveDocs.documentSaveFunction();
		} else {
			form.submit();
		}
	},


	//
	documentSee: function () {
		let form = $('#formDoc'),
			doc_id = form.data('id');

		if (doc_id > 0) {
			window.open('/index.php?id=' + doc_id, '_blank');
		} else {
			jAlert(alert_none_id, alert_none_id_t);
		}
	},


	//
	saveEditBtn: function () {
		$('.SaveEdit').on('click', function (event) {
			event.preventDefault();
			if (window.CKEDITOR)
				for (var instanceName in CKEDITOR.instances)
					CKEDITOR.instances[instanceName].updateElement();

			AveDocs.saveDocument();
			return false;
		});
	},


	//
	editMousetrap: function () {
		Mousetrap.bind(['ctrl+s', 'command+s'], function (event) {
			event.preventDefault();
			if (window.CKEDITOR)
				for (var instanceName in CKEDITOR.instances)
					CKEDITOR.instances[instanceName].updateElement();

			AveDocs.saveDocument();
			return false;
		});

		Mousetrap.bind(['ctrl+o', 'command+o'], function (event) {
			event.preventDefault();
			AveDocs.documentSee();
			return false;
		});
	},


	//
	windowOnLoadCKEditor: function () {
		window.onload = function () {
			if (window.CKEDITOR) {
				CKEDITOR.on('instanceReady', function (event) {
					event.editor.setKeystroke(CKEDITOR.CTRL + 83 /*S*/, 'savedoc');
				});
			}
		}
	}
};