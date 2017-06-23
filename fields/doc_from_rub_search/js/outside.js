var DocSearch = {

	init: false,

	init: function() {
		if (this.initialized) return;
		this.initialized = true;

		this.DocSearch_items();
	},

	DocSearch_items: function() {
		this.DocSearch_sortable();
		this.DocSearch_del_item();
		this.DocSearch_add();
		this.DocSearch_search();
	},

	DocSearch_update: function() {
		this.DocSearch_maxid();
		this.DocSearch_del_item();
		this.DocSearch_search();
		AveAdmin.tooltip();
	},

	DocSearch_maxid: function(id) {
		var maxid = 1;
		$('#docsearch_lists_' + id).children('.docsearch_list').each(function() {
			maxid = Math.max(maxid, parseInt($(this).attr("data-id")) + 1);
		});
		return maxid;
	},

	DocSearch_del_item: function() {
		$('.docsearch_list .DelButton').on('click', function(event) {
			event.preventDefault();
			var id = $(this).attr('data-id');
			jConfirm(
				docsearch_del_conf,
				docsearch_del_head,
				function(b) {
					if (b) {
						$('#docsearch_list_' + id).remove();
					}
				}
			);
		});
	},

	DocSearch_add: function() {
		$('.AddButton').on('click', function() {
			c_id = $(this).parent().parent('.docsearch_lists').attr("data-id");
			d_id = $(this).parent().parent('.docsearch_lists').attr("data-docid");
			i_id = DocSearch.DocSearch_maxid(d_id + '_' + c_id);
			$('#docsearch_lists_' + d_id + '_' + c_id + ':last').append(
				'<div class="docsearch_list fix mb10" id="docsearch_list_' + d_id + '_' + c_id + '_' + i_id + '" data-id="' + i_id + '">' +
				'<input class="mousetrap search_docsearch" name="data[' + d_id + '][feld][' + c_id + '][' + i_id + '][param]" type="text" value="" placeholder="' + docsearch_param + '" data-docid="' + d_id + '" data-fieldid="' + c_id + '" data-id="' + i_id + '" style="width: 450px;"/>&nbsp;&nbsp;Id:&nbsp;<input type="text" class="mousetrap field_' + d_id + '_' + c_id + '_' + i_id + '" value="" name="data[' + d_id + '][feld][' + c_id + '][' + i_id + '][value]" placeholder="' + docsearch_value + '" style="width: 50px;" readonly />&nbsp;&nbsp;<a href="javascript:void(0);" data-id="' + d_id + '_' + c_id + '_' + i_id + '" class="button redBtn topDir DelButton" title="' + docsearch_del + '">&times;</a>' +
				'<div class="handle" style="float: left; display: inline-block; margin: 4px 7px; cursor: move;"><span class="icon_sprite ico_move"></span></div>' +
				'</div>'
			);

			DocSearch.DocSearch_update();
		});
	},

	DocSearch_sortable: function() {
		$('.docsearch_lists').sortable({
			handle: ".handle",
			placeholder: "ui-state-highlight grey_bg"
		});
	},

	/**
	 * @return {boolean}
	 */
	DocSearch_search: function() {

		$(document).on('input', '.search_docsearch', function(event)
		{
			event.preventDefault();

			var query = $(this);

			var did = query.attr('data-docid');
			var fid = query.attr('data-fieldid');
			var kid = query.attr('data-id');
			var field_id_input = $('.field_' + did + '_' + fid + '_' + kid);

			query.autocomplete("index.php?do=fields&field=doc_from_rub_search&type=search&doc_id=" + did + "&field_id=" + fid, {
				width: query.outerWidth(),
				max: 5,
				dataType: "json",
				matchContains: "word",
				scroll: true,
				scrollHeight: 200,
				parse: function(data) {
					return $.map(data, function(row) {
						return {
							data: row,
							value: row.doc_title,
							result: query.val()
						}
					});
				},
				formatItem: function(item) {
					return '<div style="padding: 3px 0;"><span style="font-weight: 700;">(' + item.doc_rubric + ')</span> ' + item.doc_title + '</div>';
				}
			}).result(function(event, item) {

				query.val(item.doc_title);

				field_id_input.val(item.doc_id);

				query.unautocomplete();
			});

			return false;
		});

		return false;
	}
}

$(document).ready(function()
{
	DocSearch.init();
});