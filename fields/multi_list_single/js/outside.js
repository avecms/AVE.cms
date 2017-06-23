var MultiListSingle = {

	init: false,

	init: function() {
		if (this.initialized) return;
		this.initialized = true;

		this.s_lists();
	},

	s_lists: function() {
		this.s_lists_sortable();
		this.s_lists_del_item();
		this.s_lists_add();
	},

	s_lists_update: function() {
		this.s_lists_maxid();
		this.s_lists_del_item();
		AveAdmin.tooltip();
	},

	s_lists_maxid: function(id) {
		var maxid = 1;
		$('#multi_lists_single_' + id).children('.multi_list_single').each(function() {
			maxid = Math.max(maxid, parseInt($(this).attr("data-id")) + 1);
		});
		return maxid;
	},

	s_lists_del_item: function() {
		$('.multi_list_single .DelSingleButton').on('click', function(event) {
			event.preventDefault();
			var id = $(this).attr('data-id');
			jConfirm(
				s_list_del_conf,
				s_list_del_head,
				function(b) {
					if (b) {
						$('#list_' + id).remove();
					}
				}
			);
		});
	},

	s_lists_add: function() {
		$('.AddSingleButton').on('click', function(event) {
			event.preventDefault();
			c_id = $(this).parent().parent('.multi_lists_single').attr("data-id");
			iid = MultiListSingle.s_lists_maxid(c_id);
			$('#multi_lists_single_' + c_id + ':last').append(
				'<div class="multi_list_single fix mb10" id="list_' + c_id + '_' + iid + '" data-id="' + iid + '">' +
				'<input type="text" class="mousetrap" value="" name="feld[' + c_id + '][' + iid + ']" placeholder="' + s_list_value + '" style="width: 400px;"/>&nbsp;&nbsp;<a href="javascript:void(0);" data-id="' + c_id + '_' + iid + '" class="button redBtn topDir DelSingleButton" title="' + s_list_del + '">&times;</a>' +
				'<div class="handle" style="float: left; display: inline-block; margin: 4px 7px; cursor: move;"><span class="icon_sprite ico_move"></span></div>' +
				'</div>'
			);

			MultiListSingle.s_lists_update();
		});
	},

	s_lists_sortable: function() {
		$('.multi_lists_single').sortable({
			handle: ".handle",
			placeholder: "ui-state-highlight grey_bg"
		});
		//$(".multi_lists_single").disableSelection();
	}
}

$(document).ready(function() {
	MultiListSingle.init();
});