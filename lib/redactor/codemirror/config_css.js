$(function () {

	var config = 	{
		height: "400px",
        stylesheet: ["codemirror/lib/codemirror.css", "codemirror/doc/docs.css", "codemirror/mode/css/csscolors.css"],
		path: "codemirror/lib/",
		lineNumbers: true,
		lineWrapping: true,
		matchBrackets: true,
			onCursorActivity: function() {
				editor.setLineClass(hlLine, null);
				hlLine = editor.setLineClass(editor.getCursor().line, "activeline");
  			}
	};
	var arrays = $(".code_text textarea");

	$.each(arrays, function() {
			editor($(this).attr('id'));
	});

	function editor(id)
	{
	  CodeMirror.fromTextArea(id, config);
	}

	var hlLine = editor.setLineClass(0, "activeline");

});