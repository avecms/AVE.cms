var DocFiles = {

	init: false,

	init: function() {
		if (this.initialized) return;
		this.initialized = true;

		this.lists();
	},

	lists: function() {
		this.lists_sortable();
		this.lists_del_item();
		this.lists_add();
	},

	lists_update: function() {
		this.lists_maxid();
		this.lists_del_item();
		AveAdmin.tooltip();
	},

	lists_maxid: function(id) {
		var maxid = 1;
		$('#doc_files_' + id).children('.doc_file').each(function() {
			maxid = Math.max(maxid, parseInt($(this).attr("data-id")) + 1);
		});
		return maxid;
	},

	lists_del_item: function() {
		$('.doc_file .DelButton').on('click', function(event) {
			event.preventDefault();
			var id = $(this).attr('data-id');
			jConfirm(
				links_del_conf,
				links_del_head,
				function(b) {
					if (b) {
						$('#link_' + id).remove();
					}
				}
			);
		});
	},

	lists_add: function() {
		$('.doc_files .AddButton').on('click', function(event) {
			event.preventDefault();

			c_id = $(this).parent().parent().parent('.doc_files').attr("data-id");
			iid = DocFiles.lists_maxid(c_id);

			$('#doc_files_' + c_id + ':last').append(
				'<div class="doc_file fix mb10" id="link_' + c_id + '_' + iid + '" data-id="' + iid + '">' +
					'<div class="handle">' +
						'<span class="icon_sprite ico_move"></span>' +
					'</div>' +
					'<div class="file_block">' +
						'<input type="text" class="mousetrap docs_name" value="" name="feld[' + c_id + '][' + iid + '][name]" placeholder="' + links_name + '"/>' +
						'<textarea class="mousetrap docs_desc" name="feld[' + c_id + '][' + iid + '][descr]" placeholder="' + links_desc + '"></textarea>' +
						'<input type="text" class="mousetrap docs_url" value="" name="feld[' + c_id + '][' + iid + '][url]" id="links_' + c_id + '_' + iid + '" placeholder="' + links_url + '" />&nbsp;' +
						'<a class="btn greyishBtn" onclick="openFileWindow(\'links_' + c_id + '_' + iid + '\',\'links_' + c_id + '_' + iid + '\',\'links_' + c_id + '_' + iid + '\');">FILE</a>&nbsp;&nbsp;<a href="javascript:void(0);" data-id="' + c_id + '_' + iid + '" class="button redBtn topDir DelButton" title="' + links_del + '">&times;</a>' +
					'</div>' +
				'</div>'
			);

			DocFiles.lists_update();
		});
	},

	lists_sortable: function() {
		$('.doc_files').sortable({
			handle: ".handle",
			placeholder: "ui-state-highlight grey_bg"
		});
	}
}

$(document).ready(function() {
	DocFiles.init();
});