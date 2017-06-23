var Mega = {

	init: false,

	init: function() {
		if (this.initialized) return;
		this.initialized = true;

		this.mega();
	},

	mega: function() {
		this.mega_sortable();
		this.mega_del_item();
		this.mega_del_all_item();
		this.mega_add_single();
		this.mega_add_folder();
		this.megae_upload_files();
		this.mega_click_upload();
	},

	mega_update: function() {
		this.mega_maxid();
		this.mega_del_item();
		AveAdmin.fancy_box();
		AveAdmin.tooltip();
	},

	mega_maxid: function(id, doc) {
		var maxid = 1;
		$('#mega_' + doc + '_' + id).children('.mega_sortable').children('.mega_item').each(function() {
			maxid = Math.max(maxid, parseInt($(this).attr("data-id")) + 1);
		});
		return maxid;
	},

	mega_del_item: function() {
		$('.mega_item .delete').on('click', function(event) {
			event.preventDefault();
			var id = $(this).attr('data-id');
			jConfirm(
				mega_del_conf,
				mega_del_head,
				function(b) {
					if (b) {
						$('#mega_image_' + id).remove();
					}
				}
			);
		});
	},

	mega_del_all_item: function() {
		$('.mega_del_all').on('click', function(event) {
			event.preventDefault();
			var c_id = $(this).parent().parent().parent('.mega').attr("data-id");
			var d_id = $(this).parent().parent().parent('.mega').attr("data-doc");
			jConfirm(
				mega_del_all_c,
				mega_del_all_h,
				function(b) {
					if (b) {
						$('#mega_' + d_id + '_' + c_id).children('.mega_sortable').children('.mega_item').each(function() {
							$(this).remove();
						});
					}
				}
			);
		});
	},

	megae_upload_files: function() {
		$('.mega_upload').on('change', function(event) {

			var mega_input = $(this);

			event.preventDefault();

			if (mega_input.val() == '') {
				return false;
			}

			var files_input = this.files.length;
			var max_files = mega_input.attr("data-max-files");

			if (files_input > max_files) {
				$.jGrowl(mega_max_f_t, {
					header: mega_max_f_h,
					theme: 'error'
				});

				mega_input.replaceWith(mega_input.val('').clone(true));

				return false;
			}

			var c_id = $(this).parent('.mega').attr("data-id");
			var d_id = $(this).parent('.mega').attr("data-doc");
			var r_id = $(this).parent('.mega').attr("data-rubric");

			$('#formDoc').ajaxSubmit({
				url: 'index.php?do=fields',
				data: {
					"field_id": c_id,
					"rubric_id": r_id,
					"doc_id": d_id,
					"field": 'image_mega',
					"type": 'upload'
				},
				beforeSend: function() {
					$.alerts._overlay('show');
				},
				dataType: "JSON",
				success: function(data) {
					if (data['respons'] == 'succes') {
						for (var p = 0, max = data.files.length; p < max; p++) {

							iid = Mega.mega_maxid(c_id, d_id);
							var field_value = data['dir'] + data.files[p];
							var img_path = '../index.php?thumb=' + field_value + '&mode=f&width=128&height=128';

							$('#mega_' + d_id + '_' + c_id + ' > .mega_sortable:last').prepend(
								'<div class="mega_item ui-state-default" id="mega_image_' + c_id + '_' + d_id + '_' + iid + '" data-id="' + iid + '" data-doc="' + d_id + '">' +
									'<div class="header grey_bg"></div>' +
									'<a class="topDir icon_sprite ico_photo view fancy preview__' + c_id + '_' + d_id + '_' + iid + '" href="' + field_value + '" title="' + mega_look + '"></a>' +
									'<a class="topDir icon_sprite ico_delete delete" href="javascript:void(0);" title="' + mega_del + '" data-id="' + c_id + '_' + d_id + '_' + iid + '"></a>' +
									'<div class="mega_block">' +
										'<div class="mega_left_block">' +
											'<input type="hidden" value="' + field_value + '" name="feld[' + c_id + '][' + iid + '][url]" id="image__' + c_id + '_' + d_id + '_' + iid + '">' +
											'<img id="preview__' + c_id + '_' + d_id + '_' + iid + '" src="' + img_path + '" onclick="browse_uploads(\'image__' + c_id + '_' + d_id + '_' + iid + '\');" class="image" alt="" width="128" height="128" />' +
											'<span class="topDir icon_sprite ico_info info" title="' + field_value + '"></span>' +
										'</div>' +
										'<div class="mega_left_block">' +
											'<textarea class="mousetrap" name="feld[' + c_id + '][' + iid + '][title]" placeholder="' + mega_title + '"></textarea>' +
											'<textarea class="mousetrap" name="feld[' + c_id + '][' + iid + '][description]" placeholder="' + mega_description + '"></textarea>' +
										'</div>' +
									'</div>' +
									'<div class="mega_link">' +
										'<input class="mega_link_input mousetrap" id="link_' + c_id + '_' + d_id + '_' + iid + '" name="feld[' + c_id + '][' + iid + '][link]" placeholder="' + mega_link + '" />' +
										'&nbsp;' +
										'<a class="btn greyishBtn" onclick="openFileWindow(\'link_' + c_id + '_' + d_id + '_' + iid + '\',\'link_' + c_id + '_' + d_id + '_' + iid + '\',\'link_' + c_id + '_' + d_id + '_' + iid + '\');">' + mega_from_file + '</a>' +
										'&nbsp;' +
										'<a class="btn greyishBtn" onclick="openLinkWin(\'link_' + c_id + '_' + d_id + '_' + iid + '\', \'link_' + c_id + '_' + d_id + '_' + iid + '\');">' + mega_from_docs + '</a>' +
									'</div>' +
								'</div>'
							);

							$.alerts._overlay('hide');

							Mega.mega_update();
						}
					}
					$.jGrowl(data['message'], {
						header: data['header'],
						theme: data['theme']
					});

					mega_input.replaceWith(mega_input = mega_input.clone(true));
					mega_input.val();
				}
			});
			return false;
		});
	},

	mega_click_upload: function() {
		$('.mega_upload_local').on('click', function(event) {
			event.preventDefault();
			var c_id = $(this).parent().parent().parent('.mega').attr("data-id");
			var d_id = $(this).parent().parent().parent('.mega').attr("data-doc");
			$('.mega_upload_field_' + c_id + '_' + d_id).trigger('click');
		});
	},

	mega_add_single: function() {
		$('.mega_add_single').on('click', function(event) {
			event.preventDefault();

			var c_id = $(this).parent().parent().parent('.mega').attr("data-id");
			var d_id = $(this).parent().parent().parent('.mega').attr("data-doc");
			var iid = Mega.mega_maxid(c_id, d_id);

			$('#mega_' + d_id + '_' + c_id + ' > .mega_sortable:last').prepend(
				'<div class="mega_item ui-state-default" id="mega_image_' + c_id + '_' + d_id + '_' + iid + '" data-id="' + iid + '" data-doc="' + d_id + '">' +
					'<div class="header grey_bg"></div>' +
					'<a class="topDir icon_sprite ico_photo view fancy preview__' + c_id + '_' + d_id + '_' + iid + '" href="" title="' + mega_look + '"></a>' +
					'<a class="topDir icon_sprite ico_delete delete" href="javascript:void(0);" title="' + mega_del + '" data-id="' + c_id + '_' + d_id + '_' + iid + '"></a>' +
					'<div class="mega_block">' +
						'<div class="mega_left_block">' +
							'<input type="hidden" value="" name="feld[' + c_id + '][' + iid + '][url]" id="image__' + c_id + '_' + d_id + '_' + iid + '">' +
							'<img id="preview__' + c_id + '_' + d_id + '_' + iid + '" src="' + mega_blank + '" onclick="browse_uploads(\'image__' + c_id + '_' + d_id + '_' + iid + '\');" class="image" alt="" width="128" height="128" />' +
						'</div>' +
						'<div class="mega_left_block">' +
							'<textarea class="mousetrap" name="feld[' + c_id + '][' + iid + '][title]" placeholder="' + mega_title + '"></textarea>' +
							'<textarea class="mousetrap" name="feld[' + c_id + '][' + iid + '][description]" placeholder="' + mega_description + '"></textarea>' +
						'</div>' +
					'</div>' +
					'<div class="mega_link">' +
						'<input class="mega_link_input mousetrap" id="link_' + c_id + '_' + d_id + '_' + iid + '" name="feld[' + c_id + '][' + iid + '][link]" placeholder="' + mega_link + '" />' +
						'&nbsp;' +
						'<a class="btn greyishBtn" onclick="openFileWindow(\'link_' + c_id + '_' + d_id + '_' + iid + '\',\'link_' + c_id + '_' + d_id + '_' + iid + '\',\'link_' + c_id + '_' + d_id + '_' + iid + '\');">' + mega_from_file + '</a>' +
						'&nbsp;' +
						'<a class="btn greyishBtn" onclick="openLinkWin(\'link_' + c_id + '_' + d_id + '_' + iid + '\', \'link_' + c_id + '_' + d_id + '_' + iid + '\');">' + mega_from_docs + '</a>' +
					'</div>' +
				'</div>'
			);

			browse_uploads('image__' + c_id + '_' + d_id + '_' + iid + '');

			Mega.mega_update();
		});
	},

	mega_sortable: function() {
		$('.mega_sortable').sortable({
			handle: ".header",
			placeholder: "ui-state-highlight grey_bg"
		});
		//$(".mega").disableSelection();
	},

	mega_add_folder: function() {
		$('.mega_add_folder').on('click', function(event) {
			event.preventDefault();

			var c_id = $(this).parent().parent().parent('.mega').attr("data-id");
			var d_id = $(this).parent().parent().parent('.mega').attr("data-doc");

			browse_dirs("cascad__" + c_id + '_' + d_id);
		});
	}
};

$(document).ready(function() {
	Mega.init();

	$.fn.myPlugin = function mega_add_items(dir, c_id, d_id) {

		$.ajax({
			url: ave_path + 'admin/index.php?do=docs&action=image_import&ajax=run',
			data: {
				"path": dir
			},
			dataType: "JSON",
			success: function(data) {
				$.alerts._overlay('hide');
				for (var p = 0, max = data.respons.length; p < max; p++) {
					var iid = Mega.mega_maxid(c_id, d_id);
					var field_value = dir + data.respons[p];
					var img_path = '../index.php?thumb=' + field_value + '&mode=f&width=128&height=128';

					$('#mega_' + d_id + '_' + c_id + ' > .mega_sortable:last').prepend(
						'<div class="mega_item ui-state-default" id="mega_image_' + c_id + '_' + d_id + '_' + iid + '" data-id="' + iid + '" data-doc="' + d_id + '">' +
							'<div class="header grey_bg"></div>' +
							'<a class="topDir icon_sprite ico_photo view fancy preview__' + c_id + '_' + d_id + '_' + iid + '" href="' + field_value + '" title="' + mega_look + '"></a>' +
							'<a class="topDir icon_sprite ico_delete delete" href="javascript:void(0);" title="' + mega_del + '" data-id="' + c_id + '_' + d_id + '_' + iid + '"></a>' +
							'<div class="mega_block">' +
								'<div class="mega_left_block">' +
									'<input type="hidden" value="' + field_value + '" name="feld[' + c_id + '][' + iid + '][url]" id="image__' + c_id + '_' + d_id + '_' + iid + '">' +
									'<img id="preview__' + c_id + '_' + d_id + '_' + iid + '" src="' + img_path + '" onclick="browse_uploads(\'image__' + c_id + '_' + d_id + '_' + iid + '\');" class="image" alt="" width="128" height="128" />' +
									'<span class="topDir icon_sprite ico_info info" title="' + field_value + '"></span>' +
								'</div>' +
								'<div class="mega_left_block">' +
									'<textarea class="mousetrap" name="feld[' + c_id + '][' + iid + '][title]" placeholder="' + mega_title + '"></textarea>' +
									'<textarea class="mousetrap" name="feld[' + c_id + '][' + iid + '][description]" placeholder="' + mega_description + '"></textarea>' +
								'</div>' +
							'</div>' +
							'<div class="mega_link">' +
								'<input class="mega_link_input mousetrap" id="link_' + c_id + '_' + d_id + '_' + iid + '" name="feld[' + c_id + '][' + iid + '][link]" placeholder="' + mega_link + '" />' +
								'&nbsp;' +
								'<a class="btn greyishBtn" onclick="openFileWindow(\'link_' + c_id + '_' + d_id + '_' + iid + '\',\'link_' + c_id + '_' + d_id + '_' + iid + '\',\'link_' + c_id + '_' + d_id + '_' + iid + '\');">' + mega_from_file + '</a>' +
								'&nbsp;' +
								'<a class="btn greyishBtn" onclick="openLinkWin(\'link_' + c_id + '_' + d_id + '_' + iid + '\', \'link_' + c_id + '_' + d_id + '_' + iid + '\');">' + mega_from_docs + '</a>' +
							'</div>' +
						'</div>'
					);

					Mega.mega_update();
				}
			}
		});
	}

});