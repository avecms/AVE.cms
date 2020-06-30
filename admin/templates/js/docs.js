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
};