CKEDITOR.plugins.add('savedocs', {
	init: function(a) {
		var cmd = a.addCommand('savedoc', {
			exec: saveDocument
		})
		a.ui.addButton('savedocs', {
			label: 'Save',
			command: 'savedoc',
			icon: this.path + "images/save.png"
		})
	}
});

function saveDocument(event) {
	var theForm = event.element.$.form;
	if (typeof(theForm.onsubmit) == 'function') {
		AveDocs.saveDocument();
		return false;
	} else {
		AveDocs.saveDocument();
	}
}