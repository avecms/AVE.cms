$(function () {

	var config = 	{
		height: "400px",
        parserfile: ["parsexml.js", "parsecss.js", "tokenizejavascript.js", "parsejavascript.js",
                     "../contrib/php/js/tokenizephp.js", "../contrib/php/js/parsephp.js",
                     "../contrib/php/js/parsephphtmlmixed.js"],
        stylesheet: ["codemirror/css/xmlcolors.css", "codemirror/css/jscolors.css", "codemirror/css/csscolors.css", "codemirror/contrib/php/css/phpcolors.css"],
		path: "codemirror/js/",
		lineNumbers: true,
		lineWrapping: true,
		matchBrackets: true,
			onCursorActivity: function() {
				editor.setLineClass(hlLine, null);
				hlLine = editor.setLineClass(editor.getCursor().line, "activeline");
  			}
	};
	var arrays = $(".coder_in textarea");

	$.each(arrays, function() {
			editor($(this).attr('id'));
	});

	function editor(id)
	{
	  CodeMirror.fromTextArea(id, config);
	}

	var hlLine = editor.setLineClass(0, "activeline");

});