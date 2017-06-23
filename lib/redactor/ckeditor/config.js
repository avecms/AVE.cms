CKEDITOR.editorConfig = function(config) {

	CKEDITOR.dtd.$removeEmpty.span = 0;
	CKEDITOR.dtd.$removeEmpty.i = 0;
	CKEDITOR.dtd.$removeEmpty.div = 0;
	CKEDITOR.dtd.$removeEmpty.em = 0;
	CKEDITOR.dtd.$removeEmpty.b = 0;

	config.removePlugins = 'scayt';

	config.extraPlugins = 'codemirror,placeholder,savedocs';

	config.protectedSource.push(/<\?[\s\S]*?\?>/g); // PHP code
	config.protectedSource.push(/<%[\s\S]*?%>/g); // ASP code
	config.protectedSource.push(/(]+>[\s|\S]*?<\/asp:[^\>]+>)|(]+\/>)/gi); // ASP.Net code

	config.language = 'ru';

	config.emailProtection = 'mt(NAME,DOMAIN,SUBJECT,BODY)';

	config.toolbarCanCollapse = true;
	config.disableNativeSpellChecker = false;
	config.scayt_autoStartup = false;

	config.autoParagraph = false;
	config.autoUpdateElement = true;

	config.enterMode = CKEDITOR.ENTER_P;
	config.shiftEnterMode = CKEDITOR.ENTER_BR;

	config.allowedContent = true;

	config.autoGrow_minHeight = 300;

	config.toolbar_Big = [{
			name: 'document',
			groups: ['mode', 'document', 'doctools'],
			items: ['Source', '-', 'searchCode','autoFormat','CommentSelectedRange','UncommentSelectedRange','AutoComplete', 'Save']
		}, {
			name: 'clipboard',
			groups: ['clipboard', 'undo'],
			items: ['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Undo', 'Redo']
		}, {
			name: 'editing',
			groups: ['find', 'selection', 'spellchecker'],
			items: ['Find', 'Replace', '-', 'SelectAll']
		}, {
			name: 'forms',
			items: ['Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton', 'HiddenField']
		},
		'/', {
			name: 'basicstyles',
			groups: ['basicstyles', 'cleanup'],
			items: ['Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat']
		}, {
			name: 'paragraph',
			groups: ['list', 'indent', 'blocks', 'align', 'bidi'],
			items: ['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote', 'CreateDiv', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock', '-', 'BidiLtr', 'BidiRtl']
		}, {
			name: 'links',
			items: ['Link', 'Unlink', 'Anchor']
		}, {
			name: 'insert',
			items: ['Image', 'Flash', 'Table', 'HorizontalRule', 'Smiley', 'SpecialChar', 'PageBreak', 'Iframe']
		},
		'/', {
			name: 'styles',
			items: ['Styles', 'Format', 'Font', 'FontSize']
		}, {
			name: 'colors',
			items: ['TextColor', 'BGColor']
		}, {
			name: 'tools',
			items: ['Maximize', 'ShowBlocks']
		}, {
			name: 'texttransform',
			items: ['TransformTextToUppercase', 'TransformTextToLowercase', 'TransformTextCapitalize', 'TransformTextSwitcher']
		}, {
			name: 'about',
			items: ['About']
		}
	];

	config.toolbar_Small = [
		['Source', '-', 'Save'],
		['Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord'],
		['Undo', 'Redo'],
		['Bold', 'Italic', 'Underline', 'Strike'],
		['NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', 'Blockquote', 'CreateDiv', 'Table'],
		'/', ['Format', 'FontSize'],

		['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
		['TextColor', 'BGColor', 'RemoveFormat'],

		['Link', 'Unlink', 'Anchor'],
		['Image', 'Flash', 'jwplayer'],

		['Spoiler', 'PageBreak', 'SpecialChar'],
		['ShowBlocks', 'Maximize']
	];

	config.toolbar_Verysmall = [
		['Source', '-', 'Save'],
		['Cut', 'Copy', 'Paste', 'PasteText', 'PasteWord'],
		['Undo', 'Redo'],
		['Bold', 'Italic', 'Underline', 'StrikeThrough'],
		['JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock'],
		['Link', 'Unlink'],
		['Image', 'ShowBlocks', 'Maximize']
	];

	config.filebrowserBrowseUrl = '../../../../admin/index.php?do=browser&type=link&mode=fck&target=txtUrl';
	config.filebrowserImageBrowseUrl = '../../../../admin/index.php?do=browser&type=image&mode=fck&target=txtUrl';
	config.filebrowserLinkBrowseUrl = '../../../../admin/index.php?do=docs&action=showsimple&selecturl=1&target=txtUrl&pop=1';

	config.removeDialogTabs = 'link:upload;image:Upload';

	config.keystrokes =
		[
		[CKEDITOR.ALT + 121 /*F10*/ , 'toolbarFocus'],
		[CKEDITOR.ALT + 122 /*F11*/ , 'elementsPathFocus'],

		[CKEDITOR.SHIFT + 121 /*F10*/ , 'contextMenu'],

		[CKEDITOR.CTRL + 90 /*Z*/ , 'undo'],
		[CKEDITOR.CTRL + 89 /*Y*/ , 'redo'],
		[CKEDITOR.CTRL + CKEDITOR.SHIFT + 90 /*Z*/ , 'redo'],

		[CKEDITOR.CTRL + 76 /*L*/ , 'link'],

		[CKEDITOR.CTRL + 66 /*B*/ , 'bold'],
		[CKEDITOR.CTRL + 73 /*I*/ , 'italic'],
		[CKEDITOR.CTRL + 85 /*U*/ , 'underline'],

		[CKEDITOR.ALT + 109 /*-*/ , 'toolbarCollapse']
	];

	config.forcePasteAsPlainText = true;

	config.codemirror = {

		// Set this to the theme you wish to use (codemirror themes)
		theme: 'default',

		// Whether or not you want to show line numbers
		lineNumbers: true,

		// Whether or not you want to use line wrapping
		lineWrapping: true,

		// Whether or not you want to highlight matching braces
		matchBrackets: true,

		// Whether or not you want to highlight matching tags
		matchTags: true,

		// Whether or not you want tags to automatically close themselves
		autoCloseTags: true,

		// Whether or not you want Brackets to automatically close themselves
		autoCloseBrackets: true,

		// Whether or not to enable search tools, CTRL+F (Find), CTRL+SHIFT+F (Replace), CTRL+SHIFT+R (Replace All), CTRL+G (Find Next), CTRL+SHIFT+G (Find Previous)
		enableSearchTools: true,

		// Whether or not you wish to enable code folding (requires 'lineNumbers' to be set to 'true')
		enableCodeFolding: true,

		// Whether or not to enable code formatting
		enableCodeFormatting: true,

		// Whether or not to automatically format code should be done when the editor is loaded
		autoFormatOnStart: false,

		autoFormatOnModeChange: false,

		// Whether or not to automatically format code which has just been uncommented
		autoFormatOnUncomment: true,

		// Whether or not to highlight the currently active line
		highlightActiveLine: true,

		// Whether or not to highlight all matches of current word/selection
		highlightMatches: true,

		// Define the language specific mode 'htmlmixed' for html  including (css, xml, javascript), 'application/x-httpd-php' for php mode including html, or 'text/javascript' for using java script only
		mode: 'application/x-httpd-php',

		// Whether or not to show the search Code button on the toolbar
		showSearchButton: true,

		// Whether or not to show Trailing Spaces
		showTrailingSpace: true,

		// Whether or not to show the format button on the toolbar
		showFormatButton: true,

		// Whether or not to show the comment button on the toolbar
		showCommentButton: true,

		// Whether or not to show the uncomment button on the toolbar
		showUncommentButton: true,

		// Whether or not to show the showAutoCompleteButton button on the toolbar
		showAutoCompleteButton: true
	};

};

CKEDITOR.on('instanceReady', function(ev) {
	ev.editor.on('paste', function(evt) {
		evt.data.dataValue = evt.data.dataValue.replace(/&nbsp;/g,' ');
		evt.data.dataValue = evt.data.dataValue.replace(/<p><\/p>/g,' ');
	}, null, null, 9);
});