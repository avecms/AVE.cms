var MultiListTriple = {

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
		$('#multi_lists_triple_' + id).children('.multi_list_triple').each(function() {
			maxid = Math.max(maxid, parseInt($(this).attr("data-id")) + 1);
		});
		return maxid;
	},

	lists_del_item: function() {
		$('.multi_list_triple .DelButton').on('click', function(event) {
			event.preventDefault();
			var id = $(this).attr('data-id');
			jConfirm(
				list_del_conf,
				list_del_head,
				function(b) {
					if (b) {
						$('#list_' + id).remove();
					}
				}
			);
		});
	},

	lists_add: function() {
		$('.AddButton').on('click', function(event) {
			event.preventDefault();
			c_id = $(this).parent().parent('.multi_lists_triple').attr("data-id");
			iid = MultiListTriple.lists_maxid(c_id);
			$('#multi_lists_triple_' + c_id + ':last').append(
				'<div class="multi_list_triple fix mb10" id="list_' + c_id + '_' + iid + '" data-id="' + iid + '">' +
				'<input class="mousetrap" type="text" value="" name="feld[' + c_id + '][' + iid + '][param]" placeholder="' + list_param + '" style="width: 200px;"/>&nbsp;&nbsp;<input type="text" class="mousetrap" value="" name="feld[' + c_id + '][' + iid + '][value]" placeholder="' + list_value + '" style="width: 200px;" />&nbsp;&nbsp;<input type="text" class="mousetrap" value="" name="feld[' + c_id + '][' + iid + '][value2]" placeholder="' + list_value2 + '" style="width: 200px;" />&nbsp;&nbsp;<a href="javascript:void(0);" data-id="' + c_id + '_' + iid + '" class="button redBtn topDir DelButton" title="' + list_del + '">&times;</a>' +
				'<div class="handle" style="float: left; display: inline-block; margin: 4px 7px; cursor: move;"><span class="icon_sprite ico_move"></span></div>' +
				'</div>'
			);

			MultiListTriple.lists_update();
		});
	},

	lists_sortable: function() {
		$('.multi_lists_triple').sortable({
			handle: ".handle",
			placeholder: "ui-state-highlight grey_bg"
		});
		//$(".multi_lists_triple").disableSelection();
	}
}

$(document).ready(function() {
	MultiListTriple.init();
});