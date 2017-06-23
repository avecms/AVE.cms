var Analoque = {

	init: false,

	init: function() {
		if (this.initialized) return;
		this.initialized = true;

		this.Analoque_items();
	},

	Analoque_items: function() {
		this.Analoque_sortable();
		this.Analoque_del_item();
		this.Analoque_add();
		this.Analoque_search();
	},

	Analoque_update: function() {
		this.Analoque_maxid();
		this.Analoque_del_item();
		this.Analoque_search();
		AveAdmin.tooltip();
	},

	Analoque_maxid: function(id) {
		var maxid = 1;
		$('#analoque_lists_' + id).children('.analoque_list').each(function() {
			maxid = Math.max(maxid, parseInt($(this).attr("data-id")) + 1);
		});
		return maxid;
	},

	Analoque_del_item: function() {
		$('.analoque_list .DelButton').on('click', function(event) {
			event.preventDefault();
			var id = $(this).attr('data-id');
			jConfirm(
				analoque_del_conf,
				analoque_del_head,
				function(b) {
					if (b) {
						$('#analoque_list_' + id).remove();
					}
				}
			);
		});
	},

	Analoque_add: function() {
		$('.AddButton').on('click', function() {
			c_id = $(this).parent().parent('.analoque_lists').attr("data-id");
			d_id = $(this).parent().parent('.analoque_lists').attr("data-docid");
			i_id = Analoque.Analoque_maxid(d_id + '_' + c_id);
			$('#analoque_lists_' + d_id + '_' + c_id + ':last').append(
				'<div class="analoque_list fix mb10" id="analoque_list_' + d_id + '_' + c_id + '_' + i_id + '" data-id="' + i_id + '">' +
				'<input class="mousetrap search_analoque" name="data[' + d_id + '][feld][' + c_id + '][' + i_id + '][param]" type="text" value="" placeholder="' + analoque_param + '" data-docid="' + d_id + '" data-fieldid="' + c_id + '" data-id="' + i_id + '" style="width: 450px;"/>&nbsp;&nbsp;Id:&nbsp;<input type="text" class="mousetrap field_' + d_id + '_' + c_id + '_' + i_id + '" value="" name="data[' + d_id + '][feld][' + c_id + '][' + i_id + '][value]" placeholder="' + analoque_value + '" style="width: 50px;" readonly />&nbsp;&nbsp;<a href="javascript:void(0);" data-id="' + d_id + '_' + c_id + '_' + i_id + '" class="button redBtn topDir DelButton" title="' + analoque_del + '">&times;</a>' +
				'<div class="handle" style="float: left; display: inline-block; margin: 4px 7px; cursor: move;"><span class="icon_sprite ico_move"></span></div>' +
				'</div>'
			);

			Analoque.Analoque_update();
		});
	},

	Analoque_sortable: function() {
		$('.analoque_lists').sortable({
			handle: ".handle",
			placeholder: "ui-state-highlight grey_bg"
		});
	},

	Analoque_search: function() {

		$('.search_analoque').on('input', function(event) {

			var query = $(this);

			var did = query.attr('data-docid');
			var fid = query.attr('data-fieldid');
			var kid = query.attr('data-id');
			var field_id_input = $('.field_' + did + '_' + fid + '_' + kid);

			query.autocomplete("index.php?do=fields&field=analoque&type=search&doc_id=" + did + "&field_id=" + fid, {
				width: query.outerWidth(),
				max: 10,
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
					return '<div style="padding: 3px 0;"><span style="font-weight: 700;">' + item.doc_article + '</span> ' + item.doc_name + '</div>';
				}
			}).result(function(e, item) {
				query.val(item.doc_name);
				field_id_input.val(item.doc_id);
			});

		});

	}





}

$(document).ready(function() {
	Analoque.init();
});