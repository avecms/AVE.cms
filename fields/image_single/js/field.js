var SingleImage = {

	init: false,

	init: function() {
		if (this.initialized) return;
		this.initialized = true;
		this.single_image();
	},

	single_image: function() {
		$('.single_image').each(function(index, element) {

			let image_id = $(element).attr('data-id');
			let doc_id = $(element).attr('data-doc');
			let link = $(element).find('a.lnk');
			let look = $(element).find('a.look');
			let input = $(element).find('input');

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
					//'src': '../index.php?mode=t&width=128&height=128&thumb=/' + dir + '/' + file_full
				});
			});

			if (input.val() == '') {
				$('#preview__' + image_id + '_' + doc_id).attr({
					'src': '/uploads/images/' + thumbdir + '/noimage-t128x128.png'
				});
				$('.preview__' + image_id + '_' + doc_id).attr({
					'href': '/uploads/images/noimage.png'
				});
			}
		});
	},
}

$(document).ready(function() {
	SingleImage.init();
});