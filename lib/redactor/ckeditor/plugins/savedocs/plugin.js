CKEDITOR.plugins.add('savedocs', {
	init: function(a) {
		var cmd = a.addCommand('savedoc', {
			exec: saveAjax
		})
		a.ui.addButton('savedocs', {
			label: 'Save',
			command: 'savedoc',
			icon: this.path + "images/save.png"
		})
	}
})

function saveAjax(e) {
	var theForm = e.element.$.form;
	if (typeof(theForm.onsubmit) == 'function') {
		SaveAjax();
		return false;
	} else {
		SaveAjax();
	}
}