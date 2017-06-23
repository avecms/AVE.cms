var SingleImage = {

	init: false,

	init: function() {
		if (this.initialized) return;
		this.initialized = true;
		this.single_image();
	},

	single_image: function() {
		$('.single_image').each(function(index, element) {

			var image_id = $(element).attr('data-id');
			var doc_id = $(element).attr('data-doc');
			var link = $(element).find('a.lnk');
			var look = $(element).find('a.look');
			var input = $(element).find('input');

			link.on('click', function(event) {
				event.preventDefault();
				$('#image__' + image_id + '_' + doc_id).toggle('show');
			});

			input.on('input', function(event) {
				event.preventDefault();

				src = $(this).val();
				dir = src.substring(0, src.lastIndexOf("/"));
				file_full = src.match(/(\w*)\.\w{3,4}$/)[0];

				$('.preview__' + image_id + '_' + doc_id).attr({
					'href': '/' + $(this).val()
				});

				$('#preview__' + image_id + '_' + doc_id).attr({
					'src': '../index.php?mode=f&width=128&height=128&thumb=/' + dir + '/' + file_full
				});
			});

			if (input.val() == '') {
				$('#preview__' + image_id + '_' + doc_id).attr({
					'src': '/uploads/images/' + thumbdir + '/noimage-f128x128.gif'
				});
				$('.preview__' + image_id + '_' + doc_id).attr({
					'href': '/uploads/images/noimage.gif'
				});
			}
		});
	},
}

$(document).ready(function() {
	SingleImage.init();
});