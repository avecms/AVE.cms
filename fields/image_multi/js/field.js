var Cascad = {

	init: false,

	init: function() {
		if (this.initialized) return;
		this.initialized = true;

		this.cascad();
	},

	cascad: function() {
		this.cascad_sortable();
		this.cascad_del_item();
		this.cascad_del_all_item();
		this.cascad_add_single();
		this.cascad_add_folder();
		this.cascade_upload_files();
		this.cascad_click_upload();
	},

	cascad_update: function() {
		this.cascad_maxid();
		this.cascad_del_item();
		AveAdmin.fancy_box();
		AveAdmin.tooltip();
	},

	cascad_maxid: function(id, doc) {
		var maxid = 1;
		$('#cascad_' + doc + '_' + id).children('.cascad_sortable').children('.cascad_item').each(function() {
			maxid = Math.max(maxid, parseInt($(this).attr("data-id")) + 1);
		});
		return maxid;
	},

	cascad_del_item: function() {
		$('.cascad_item .delete').on('click', function(event) {
			event.preventDefault();
			var id = $(this).attr('data-id');
			jConfirm(
				del_conf,
				del_head,
				function(b) {
					if (b) {
						$('#cascad_image_' + id).remove();
					}
				}
			);
		});
	},

	cascad_del_all_item: function() {
		$('.del_all').on('click', function(event) {
			event.preventDefault();
			var c_id = $(this).parent().parent().parent('.cascad').attr("data-id");
			var d_id = $(this).parent().parent().parent('.cascad').attr("data-doc");
			jConfirm(
				del_all_c,
				del_all_h,
				function(b) {
					if (b) {
						$('#cascad_' + d_id + '_' + c_id).children('.cascad_sortable').children('.cascad_item').each(function() {
							$(this).remove();
						});
					}
				}
			);
		});
	},

	cascade_upload_files: function() {
		$('.cascade_upload').on('change', function(event) {

			var cascade_input = $(this);

			event.preventDefault();

			if (cascade_input.val() == '') {
				return false;
			}

			var files_input = this.files.length;
			var max_files = cascade_input.attr("data-max-files");

			if (files_input > max_files) {
				$.jGrowl(max_f_t, {
					header: max_f_h,
					theme: 'error'
				});

				cascade_input.replaceWith(cascade_input.val('').clone(true));

				return false;
			}

			var cid = $(this).parent('.cascad').attr("data-id");
			var did = $(this).parent('.cascad').attr("data-doc");
			var rid = $(this).parent('.cascad').attr("data-rubric");

			$('#formDoc').ajaxSubmit({
				url: 'index.php?do=fields',
				data: {
					"field_id": cid,
					"rubric_id": rid,
					"doc_id": did,
					"field": 'image_multi',
					"type": 'upload'
				},
				beforeSend: function() {
					$.alerts._overlay('show');
				},
				dataType: "json",
				success: function(data) {
					if (data['respons'] == 'succes') {
						for (var p = 0, max = data.files.length; p < max; p++) {
							iid = Cascad.cascad_maxid(cid, did);
							var field_value = data['dir'] + data.files[p];
							var img_path = '../index.php?thumb=' + field_value + '&mode=f&width=128&height=128';
							$('#cascad_' + did + '_' + cid + ' > .cascad_sortable:last').prepend(
								'<div class="cascad_item ui-state-default" id="cascad_image_' + cid + '_' + did + '_' + iid + '" data-id="' + iid + '" doc=id="' + did + '">' +
								'<div class="header grey_bg"></div>' +
								'<a class="topDir icon_sprite ico_photo view fancy preview__' + cid + '_' + did + '_' + iid + '" href="' + field_value + '" title="' + look + '"></a>' +
								'<a class="topDir icon_sprite ico_delete delete" href="javascript:void(0);" title="' + del + '" data-id="' + cid + '_' + did + '_' + iid + '"></a>' +
								'<span class="topDir icon_sprite ico_info info" title="' + field_value + '"></span>' +
								'<input type="hidden" value="' + field_value + '" name="feld[' + cid + '][' + iid + '][url]" id="image__' + cid + '_' + did + '_' + iid + '">' +
								'<img id="preview__' + cid + '_' + did + '_' + iid + '" src="' + img_path + '" onclick="browse_uploads(\'image__' + cid + '_' + did + '_' + iid + '\');" class="image" alt="" width="100" height="100" />' +
								'<textarea class="mousetrap" name="feld[' + cid + '][' + iid + '][descr]" placeholder="' + place + '"></textarea>' +
								'</div>'
							);
							$.alerts._overlay('hide');
							Cascad.cascad_update();
						}
					}
					$.jGrowl(data['message'], {
						header: data['header'],
						theme: data['theme']
					});
					cascade_input.replaceWith(cascade_input = cascade_input.clone(true));
					cascade_input.val();
				}
			});
			return false;
		});
	},

	cascad_click_upload: function() {
		$('.upload_local').on('click', function(event) {
			event.preventDefault();
			var c_id = $(this).parent().parent().parent('.cascad').attr("data-id");
			var d_id = $(this).parent().parent().parent('.cascad').attr("data-doc");
			$('.cascade_upload_field_' + c_id + '_' + d_id).trigger('click');
		});
	},

	cascad_add_single: function() {
		$('.add_single').on('click', function(event) {
			event.preventDefault();
			var c_id = $(this).parent().parent().parent('.cascad').attr("data-id");
			var d_id = $(this).parent().parent().parent('.cascad').attr("data-doc");
			var iid = Cascad.cascad_maxid(c_id, d_id);
			$('#cascad_' + d_id + '_' + c_id + ' > .cascad_sortable:last').prepend(
				'<div class="cascad_item ui-state-default" id="cascad_image_' + c_id + '_' + d_id + '_' + iid + '" data-id="' + iid + '" data-doc="' + d_id + '">' +
				'<div class="header grey_bg"></div>' +
				'<a class="topDir icon_sprite ico_photo view fancy preview__' + c_id + '_' + d_id + '_' + iid + '" href="" title="' + look + '"></a>' +
				'<a class="topDir icon_sprite ico_delete delete" href="javascript:void(0);" title="' + del + '" data-id="' + c_id + '_' + d_id + '_' + iid + '"></a>' +
				'<input type="hidden" value="" name="feld[' + c_id + '][' + iid + '][url]" id="image__' + c_id + '_' + d_id + '_' + iid + '">' +
				'<img id="preview__' + c_id + '_' + d_id + '_' + iid + '" src="' + blank + '" onclick="browse_uploads(\'image__' + c_id + '_' + d_id + '_' + iid + '\');" class="image" alt="" width="100" height="100" />' +
				'<textarea class="mousetrap" name="feld[' + c_id + '][' + iid + '][descr]" placeholder="' + place + '"></textarea>' +
				'</div>'
			);
			browse_uploads('image__' + c_id + '_' + d_id + '_' + iid + '');
			Cascad.cascad_update();
		});
	},

	cascad_sortable: function() {
		$('.cascad_sortable').sortable({
			handle: ".header",
			placeholder: "ui-state-highlight grey_bg"
		});
		//$(".cascad").disableSelection();
	},

	cascad_add_folder: function() {
		$('.add_folder').on('click', function(event) {
			event.preventDefault();
			var c_id = $(this).parent().parent().parent('.cascad').attr("data-id");
			var d_id = $(this).parent().parent().parent('.cascad').attr("data-doc");
			browse_dirs("cascad__" + c_id + '_' + d_id);
		});
	}
};

$(document).ready(function() {
	Cascad.init();

	$.fn.myPlugin = function cascad_add_items(dir, cid, did) {

		$.ajax({
			url: ave_path + 'admin/index.php?do=docs&action=image_import&ajax=run',
			data: {
				"path": dir
			},
			dataType: "json",
			success: function(data) {
				$.alerts._overlay('hide');
				for (var p = 0, max = data.respons.length; p < max; p++) {
					var iid = Cascad.cascad_maxid(cid, did);
					var field_value = dir + data.respons[p];
					var img_path = '../index.php?thumb=' + field_value + '&mode=f&width=128&height=128';
					$('#cascad_' + did + '_' + cid + ' > .cascad_sortable:last').prepend(
						'<div class="cascad_item ui-state-default" id="cascad_image_' + cid + '_' + did + '_' + iid + '" data-id="' + iid + '" doc=id="' + did + '">' +
						'<div class="header grey_bg"></div>' +
						'<a class="topDir icon_sprite ico_photo view fancy preview__' + cid + '_' + did + '_' + iid + '" href="' + field_value + '" title="' + look + '"></a>' +
						'<a class="topDir icon_sprite ico_delete delete" href="javascript:void(0);" title="' + del + '" data-id="' + cid + '_' + did + '_' + iid + '"></a>' +
						'<span class="topDir icon_sprite ico_info info" title="' + field_value + '"></span>' +
						'<input type="hidden" value="' + field_value + '" name="feld[' + cid + '][' + iid + '][url]" id="image__' + cid + '_' + did + '_' + iid + '">' +
						'<img id="preview__' + cid + '_' + did + '_' + iid + '" src="' + img_path + '" onclick="browse_uploads(\'image__' + cid + '_' + did + '_' + iid + '\');" class="image" alt="" width="100" height="100" />' +
						'<textarea class="mousetrap" name="feld[' + cid + '][' + iid + '][descr]" placeholder="' + place + '"></textarea>' +
						'</div>'
					);
					Cascad.cascad_update();
				}
			}
		});
	}

});