var Cascad3 = {

	init: false,

	init: function() {
		if (this.initialized) return;
		this.initialized = true;

		this.cascad3();
	},

	cascad3: function() {
		this.cascad3_sortable();
		this.cascad3_del_item();
		this.cascad3_del_all_item();
		this.cascad3_add_single();
		this.cascad3_add_folder();
		this.cascad3_upload_files();
		this.cascad3_click_upload();
	},

	cascad3_update: function() {
		this.cascad3_maxid();
		this.cascad3_del_item();
		AveAdmin.fancy_box();
		AveAdmin.tooltip();
	},

	cascad3_maxid: function(id, doc) {
		var maxid = 1;
		$('#cascad3_' + doc + '_' + id).children('.cascad3_sortable').children('.cascad3_item').each(function() {
			maxid = Math.max(maxid, parseInt($(this).attr("data-id")) + 1);
		});
		return maxid;
	},

	cascad3_del_item: function() {
		$('.cascad3_item .delete').on('click', function(event) {
			event.preventDefault();
			var id = $(this).attr('data-id');
			jConfirm(
				del_conf,
				del_head,
				function(b) {
					if (b) {
						$('#cascad3_image_' + id).remove();
					}
				}
			);
		});
	},

	cascad3_del_all_item: function() {
		$('.cascad3_del_all').on('click', function(event) {
			event.preventDefault();
			var c_id = $(this).parent().parent().parent('.cascad3').attr("data-id");
			var d_id = $(this).parent().parent().parent('.cascad3').attr("data-doc");
			jConfirm(
				del_all_c,
				del_all_h,
				function(b) {
					if (b) {
						$('#cascad3_' + d_id + '_' + c_id).children('.cascad3_sortable').children('.cascad3_item').each(function() {
							$(this).remove();
						});
					}
				}
			);
		});
	},

	cascad3_upload_files: function() {
		$('.cascad3_upload').on('change', function(event) {

			event.preventDefault();

			var cascad3_input = $(this);

			event.preventDefault();

			if (cascad3_input.val() == '') {
				return false;
			}

			var files_input = this.files.length;
			var max_files = cascad3_input.attr("data-max-files");

			if (files_input > max_files) {
				$.jGrowl(max_f_t, {
					header: max_f_h,
					theme: 'error'
				});

				cascad3_input.replaceWith(cascad3_input.val('').clone(true));

				return false;
			}

			var cid = $(this).parent('.cascad3').attr("data-id");
			var did = $(this).parent('.cascad3').attr("data-doc");
			var rid = $(this).parent('.cascad3').attr("data-rubric");

			$('#docmanager_edit').ajaxSubmit({
				url: 'index.php?do=fields',
				data: {
					"field_id": cid,
					"rubric_id": rid,
					"doc_id": did,
					"field": 'image_multi_new_3inputs',
					"type": 'upload'
				},
				beforeSend: function() {
					$.alerts._overlay('show');
				},
				dataType: "json",
				success: function(data) {
					if (data['respons'] == 'succes') {
						for (var p = 0, max = data.files.length; p < max; p++) {
							iid = Cascad3.cascad3_maxid(cid, did);
							var field_value = data['dir'] + data.files[p];
							var img_path = '../index.php?thumb=' + field_value + '&mode=f&width=128&height=128';
							$('#cascad3_' + did + '_' + cid + ' > .cascad3_sortable:last').prepend(
								'<div class="cascad3_item ui-state-default" id="cascad3_image_' + cid + '_' + did + '_' + iid + '" data-id="' + iid + '" doc=id="' + did + '">' +
								'<div class="header grey_bg"></div>' +
								'<a class="topDir icon_sprite ico_photo view fancy preview__' + cid + '_' + did + '_' + iid + '" href="' + field_value + '" title="' + look + '"></a>' +
								'<a class="topDir icon_sprite ico_delete delete" href="javascript:void(0);" title="' + del + '" data-id="' + cid + '_' + did + '_' + iid + '"></a>' +
								'<span class="topDir icon_sprite ico_info info" title="' + field_value + '"></span>' +
								'<input type="hidden" value="' + field_value + '" name="data[' + did + '][feld][' + cid + '][' + iid + '][url]" id="image__' + cid + '_' + did + '_' + iid + '">' +
								'<img id="preview__' + cid + '_' + did + '_' + iid + '" src="' + img_path + '" onclick="browse_uploads(\'image__' + cid + '_' + did + '_' + iid + '\');" class="image" alt="" width="100" height="100" />' +
								'<textarea class="mousetrap" name="data[' + did + '][feld][' + cid + '][' + iid + '][descr]" placeholder="' + place + '"></textarea>' +
								'</div>'
							);
							$.alerts._overlay('hide');
							Cascad3.cascad3_update();
						}
					}
					$.jGrowl(data['message'], {
						header: data['header'],
						theme: data['theme']
					});
					cascad3_input.replaceWith(cascad3_input = cascad3_input.clone(true));
					cascad3_input.val();
				}
			});
			return false;
		});
	},

	cascad3_click_upload: function() {
		$('.cascad3_upload_local').on('click', function(event) {
			event.preventDefault();
			var c_id = $(this).parent().parent().parent('.cascad3').attr("data-id");
			var d_id = $(this).parent().parent().parent('.cascad3').attr("data-doc");
			$('.cascad3_upload_field_' + c_id + '_' + d_id).trigger('click');
		});
	},

	cascad3_add_single: function() {
		$('.cascad3_add_single').on('click', function(event) {
			event.preventDefault();
			var c_id = $(this).parent().parent().parent('.cascad3').attr("data-id");
			var d_id = $(this).parent().parent().parent('.cascad3').attr("data-doc");
			var iid = Cascad3.cascad3_maxid(c_id, d_id);
			$('#cascad3_' + d_id + '_' + c_id + ' > .cascad3_sortable:last').prepend(
				'<div class="cascad3_item ui-state-default" id="cascad3_image_' + c_id + '_' + d_id + '_' + iid + '" data-id="' + iid + '" data-doc="' + d_id + '">' +
				'<div class="header grey_bg"></div>' +
				'<a class="topDir icon_sprite ico_photo view fancy preview__' + c_id + '_' + d_id + '_' + iid + '" href="" title="' + look + '"></a>' +
				'<a class="topDir icon_sprite ico_delete delete" href="javascript:void(0);" title="' + del + '" data-id="' + c_id + '_' + d_id + '_' + iid + '"></a>' +
				'<input type="hidden" value="" name="data[' + d_id + '][feld][' + c_id + '][' + iid + '][url]" id="image__' + c_id + '_' + d_id + '_' + iid + '">' +
				'<img id="preview__' + c_id + '_' + d_id + '_' + iid + '" src="' + blank + '" onclick="browse_uploads(\'image__' + c_id + '_' + d_id + '_' + iid + '\');" class="image" alt="" width="100" height="100" />' +
				'<textarea class="mousetrap" name="data[' + d_id + '][feld][' + c_id + '][' + iid + '][descr]" placeholder="' + place + '"></textarea>' +
				'</div>'
			);
			browse_uploads('image__' + c_id + '_' + d_id + '_' + iid + '');
			Cascad3.cascad3_update();
		});
	},

	cascad3_sortable: function() {
		$('.cascad3_sortable').sortable({
			handle: ".header",
			placeholder: "ui-state-highlight grey_bg"
		});
		//$(".cascad3").disableSelection();
	},

	cascad3_add_folder: function() {
		$('.cascad3_add_folder').on('click', function(event) {
			event.preventDefault();
			var c_id = $(this).parent().parent().parent('.cascad3').attr("data-id");
			var d_id = $(this).parent().parent().parent('.cascad3').attr("data-doc");
			browse_dirs("cascad3__" + c_id + '_' + d_id);
		});
	}
}

$(document).ready(function() {
	Cascad3.init();

	$.fn.myPlugin = function cascad3_add_items(dir, cid, did) {

		$.ajax({
			url: ave_path + 'admin/index.php?do=docs&action=image_import&ajax=run',
			data: {
				"path": dir
			},
			dataType: "json",
			success: function(data) {
				$.alerts._overlay('hide');
				for (var p = 0, max = data.respons.length; p < max; p++) {
					var iid = Cascad3.cascad3_maxid(cid, did);
					var field_value = dir + data.respons[p];
					var img_path = '../index.php?thumb=' + field_value + '&mode=f&width=128&height=128';
					$('#cascad3_' + did + '_' + cid + ' > .cascad3_sortable:last').prepend(
						'<div class="cascad3_item ui-state-default" id="cascad3_image_' + cid + '_' + did + '_' + iid + '" data-id="' + iid + '" doc=id="' + did + '">' +
						'<div class="header grey_bg"></div>' +
						'<a class="topDir icon_sprite ico_photo view fancy preview__' + cid + '_' + did + '_' + iid + '" href="' + field_value + '" title="' + look + '"></a>' +
						'<a class="topDir icon_sprite ico_delete delete" href="javascript:void(0);" title="' + del + '" data-id="' + cid + '_' + did + '_' + iid + '"></a>' +
						'<span class="topDir icon_sprite ico_info info" title="' + field_value + '"></span>' +
						'<input type="hidden" value="' + field_value + '" name="data[' + did + '][feld][' + cid + '][' + iid + '][url]" id="image__' + cid + '_' + did + '_' + iid + '">' +
						'<img id="preview__' + cid + '_' + did + '_' + iid + '" src="' + img_path + '" onclick="browse_uploads(\'image__' + cid + '_' + did + '_' + iid + '\');" class="image" alt="" width="100" height="100" />' +
						'<textarea class="mousetrap" name="data[' + did + '][feld][' + cid + '][' + iid + '][descr]" placeholder="' + place + '"></textarea>' +
						'</div>'
					);
					Cascad3.cascad3_update();
				}
			}
		});
	}

});