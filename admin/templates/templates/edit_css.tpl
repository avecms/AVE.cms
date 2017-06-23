<div class="title"><h5>{#TEMPLATES_CSS_EDITOR#}</h5></div>

<div class="widget" style="margin-top: 0px;">
    <div class="body">
		{#TEMPLATES_CSS_TITLE#}
    </div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
	    <ul>
	        <li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
	        <li><a href="index.php?do=templates&cp={$sess}" title="">{#TEMPLATES_SUB_TITLE#}</a></li>
	        <li>{#TEMPLATES_CSS_EDITOR#}</li>
			<li><strong class="code">{$smarty.request.name_file|escape}</strong></li>
	    </ul>
	</div>
</div>

<form id="code_templ" method="post" action="{$formaction}" class="mainForm">

<div class="widget first">
<div class="head"><h5 class="iFrames">{$smarty.request.name_file|escape}</h5></div>

<div class="rowElem" style="padding: 0">
	<textarea id="code_text" name="code_text">{$code_text|escape}</textarea>
	<div class="fix"></div>
</div>

<div class="rowElem">
	<button class="basicBtn">{#TEMPLATES_BUTTON_SAVE#}</button>
	{#TEMPLATES_OR#}
	<input type="submit" class="blackBtn SaveEdit" value="{#TEMPLATES_BUTTON_SAVE_NEXT#}" />
	<div class="fix"></div>
</div>

</div>
</form>

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
		    $("#code_templ").ajaxSubmit(sett_options);
			return false;
		{rdelim});

	    $(".SaveEdit").click(function(e){ldelim}
		    if (e.preventDefault) {ldelim}
		        e.preventDefault();
		    {rdelim} else {ldelim}
		        // internet explorer
		        e.returnValue = false;
		    {rdelim}
		    $("#code_templ").ajaxSubmit(sett_options);
			return false;
		{rdelim});

	{rdelim});
	</script>

{include file="$codemirror_connect"}
{include file="$codemirror_editor" textarea_id='code_text' ctrls='$("#code_templ").ajaxSubmit(sett_options);' mode='text/css'}