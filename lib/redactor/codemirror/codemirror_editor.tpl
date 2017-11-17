<script type="text/javascript">
var mixedMode = {ldelim}
	name: "htmlmixed",
	scriptTypes: [
		{ldelim}matches: /(text|application)\/(x-)?(html|httpd|php)/i, mode: "php"{rdelim}
	]
{rdelim};

editor{$conn_id} = CodeMirror.fromTextArea(document.getElementById('{$textarea_id}'), {ldelim}
	theme: '{$smarty.const.CODEMIRROR_THEME}',
	foldGutter: true,
	gutters: ["CodeMirror-linenumbers", "CodeMirror-foldgutter"],
	extraKeys: {ldelim}
		'Ctrl-S': function (cm) {ldelim}
			{$ctrls}
		{rdelim},
		'Cmd-S': function (cm) {ldelim}
			{$ctrls}
		{rdelim},
		'Ctrl-O': function (cm) {ldelim}
			{$ctrlo}
		{rdelim},
		'Cmd-O': function (cm) {ldelim}
			{$ctrlo}
		{rdelim},
		'Cmd-F11': function (cm) {ldelim}
			var codem = $(cm.getWrapperElement());
			if (codem.hasClass('CodeMirror-fullscreen')) $('body').css('overflow','auto');
			else $('body').css('overflow','hidden');
			codem.toggleClass('CodeMirror-fullscreen');
		{rdelim},
		'F11': function (cm) {ldelim}
			var codem = $(cm.getWrapperElement());
			if (codem.hasClass('CodeMirror-fullscreen')) $('body').css('overflow','auto');
			else $('body').css('overflow','hidden');
			codem.toggleClass('CodeMirror-fullscreen');
		{rdelim},
		'Esc': function (cm) {ldelim}
			$('body').css('overflow','auto');
			$(cm.getWrapperElement()).removeClass('CodeMirror-fullscreen');
		{rdelim}
	{rdelim},
	readOnly: {if $readonly} true {else} false {/if},
	lineNumbers: true,
	lineWrapping: true,
	matchBrackets: true,
	mode: '{$mode|default:'application/x-httpd-php'}',
	indentUnit: 4,
	indentWithTabs: true,
	enterMode: 'keep',
	tabMode: 'shift',
	autoCloseTags: true,
	styleActiveLine: true
{rdelim});

editor{$conn_id}.setSize('{$width|default:'100%'}', '{$height|default:'400px'}');

editor{$conn_id}.on('keypress', function(editor{$conn_id}, keypress) {ldelim}
	editor{$conn_id}.save();
{rdelim});

editor{$conn_id}.on('change', function(editor{$conn_id}, change) {ldelim}
	editor{$conn_id}.save();
{rdelim});

function getSelectedRange{$conn_id}() {ldelim}
	return {ldelim}
		from: editor{$conn_id}.getCursor(true),
		to: editor{$conn_id}.getCursor(false)
	{rdelim};
{rdelim}

function textSelection{$conn_id}(startTag, endTag) {ldelim}
	var range = getSelectedRange{$conn_id}();
	editor{$conn_id}.replaceRange(startTag + editor{$conn_id}.getRange(range.from, range.to) + endTag, range.from, range.to)
	editor{$conn_id}.setCursor(range.from.line, range.from.ch + startTag.length);
	editor{$conn_id}.save();
	editor{$conn_id}.focus();

	return false;
{rdelim}
</script>
