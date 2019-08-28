var MultiLinks = {

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
		$('#multi_links_' + id).children('.multi_link').each(function() {
			maxid = Math.max(maxid, parseInt($(this).attr("data-id")) + 1);
		});
		return maxid;
	},

	lists_del_item: function() {
		$('.multi_link .DelButton').on('click', function(event) {
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
		$('.multi_links .AddButton').on('click', function(event) {
			event.preventDefault();
			c_id = $(this).parent().parent('.multi_links').attr("data-id");
			iid = MultiLinks.lists_maxid(c_id);
			$('#multi_links_' + c_id + ':last').append(
				'<div class="multi_link fix mb10" id="link_' + c_id + '_' + iid + '" data-id="' + iid + '">' +
				'<input class="mousetrap" type="text" value="" name="feld[' + c_id + '][' + iid + '][param]" placeholder="' + links_name + '" style="width: 200px;"/>&nbsp;&nbsp;<input type="text" class="mousetrap" value="" name="feld[' + c_id + '][' + iid + '][value]" id="links_' + c_id + '_' + iid + '" placeholder="' + links_url + '" style="width: 300px;" />&nbsp;&nbsp;<a class="btn greyishBtn" onclick="openFileWindow(\'links_' + c_id + '_' + iid + '\',\'links_' + c_id + '_' + iid + '\',\'links_' + c_id + '_' + iid + '\');">PDF</a>&nbsp;&nbsp;<a href="javascript:void(0);" data-id="' + c_id + '_' + iid + '" class="button redBtn topDir DelButton" title="' + links_del + '">&times;</a>' +
				'<div class="handle" style="float: left; display: inline-block; margin: 4px 7px; cursor: move;"><span class="icon_sprite ico_move"></span></div>' +
				'</div>'
			);

			MultiLinks.lists_update();
		});
	},

	lists_sortable: function() {
		$('.multi_links').sortable({
			handle: ".handle",
			placeholder: "ui-state-highlight grey_bg"
		});
	}
}

$(document).ready(function() {
	MultiLinks.init();
});