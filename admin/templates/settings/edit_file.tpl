<div class="title">
	<h5>{#SETTINGS_FILE_EDIT_H#} {$file_name}</h5>
</div>

<div class="widget">
	<div class="title">
		<h5>{#SETTINGS_FILE_CONTENT#} {$file_name}</h5>
	</div>

	<form id="code_form" method="post" action="{$formaction}" class="mainForm">
		<div class="rowElem" style="padding: 0">
			<textarea id="code_text" name="code_text">{$template|escape}</textarea>
			<div class="fix"></div>
		</div>

		<div class="rowElem">
			<button type="submit" class="basicBtn">{#SETTINGS_BUTTON_SAVE#}</button>
			&nbsp;{#SETTINGS_OR#}&nbsp;
			<button class="blackBtn SaveEditFile">{#SETTINGS_BUTTON_SAVE_AJAX#}</button>
			<div class="fix"></div>
		</div>
	</form>

</div>

<script language="Javascript" type="text/javascript">
	var sett_options = {ldelim}
		url: '{$formaction}',
		dataType: 'json',
		data: {ldelim} ajax: '1' {rdelim},
		beforeSubmit: Request,
		success: Response
	{rdelim}

	function Request(){ldelim}
		$.alerts._overlay('show');
	{rdelim}

	function Response(data){ldelim}
		$.alerts._overlay('hide');
		$.jGrowl(data['message'], {ldelim}
			header: data['header'],
			theme: data['theme']
		{rdelim});
	{rdelim}

	$(document).ready(function(){ldelim}

		Mousetrap.bind(['ctrl+s', 'command+s'], function(e) {ldelim}
			if (e.preventDefault) {ldelim}
				e.preventDefault();
			{rdelim} else {ldelim}
				// internet explorer
				e.returnValue = false;
			{rdelim}
			$("#code_form").ajaxSubmit(sett_options);
			return false;
		{rdelim});

		 $(".SaveEditFile").click(function(e){ldelim}
			if (e.preventDefault) {ldelim}
				e.preventDefault();
			{rdelim} else {ldelim}
				// internet explorer
				e.returnValue = false;
			{rdelim}
			$("#code_form").ajaxSubmit(sett_options);
			return false;
		{rdelim});

		{literal}
		setTimeout(function(){editorfile.refresh();}, 20);
		{/literal}

	{rdelim});
	</script>

{include file="$codemirror_editor" conn_id="file" textarea_id='code_text' ctrls='$("#code_form").ajaxSubmit(sett_options);' height=450}