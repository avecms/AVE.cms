<div class="title"><h5>{#UGROUP_TITLE2#}</h5></div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#UGROUP_WARNING_TIP#}
	</div>
</div>

{if $own_group}
<ul class="messages first">
	<li class="highlight red">&bull; {#UGROUP_YOUR_NOT_CHANGE#}</li>
</ul>
{elseif $no_group}
<ul class="messages first">
	<li class="highlight red">&bull; {#UGROUP_NOT_EXIST#}</li>
</ul>
{/if}

{if !$no_group && !$own_group}

<form method="post" action="index.php?do=groups&action=grouprights&cp={$sess}&Id={$smarty.request.Id|escape}&sub=save" class="mainForm" id="groups">
<fieldset>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php">Главное меню</a> </li>
			<li><a href="index.php?do=groups&cp={$sess}">{#UGROUP_TITLE#}</a></li>
			<li><strong class="code">{$g_name|escape}</strong></li>
		</ul>
	</div>
</div>

<div class="widget first">
<div class="head"><h5 class="iFrames">{$g_name|escape}</h5></div>

<div class="rowElem noborder">
	<label>{#UGROUP_NAME#}</label>
	<div class="formRight"><input class="mousetrap" name="user_group_name" type="text" value="{$g_name|escape}" size="40" maxlength="40" /></div>
	<div class="fix"></div>
</div>

<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
	<tbody>

		<tr class="header">
			<td colspan="2">Модули</td>
		</tr>
		<tr>
			<td colspan="2">{#UGROUP_MODULES_RIGHT#}</td>
		</tr>
		{if !$modules}
		<tr>
			<td colspan="2">
				<ul class="messages">
					<li class="highlight yellow">&bull; {#UGROUP_NO_MODULES#}</li>
				</ul>
			</td>
		</tr>
		{else}
		<tr>
			<td></td>
			<td>
			{foreach from=$modules item=module}
				<input type="checkbox" name="perms[]" class="float" value="mod_{$module->ModuleSysName}"{if in_array($module->ModuleFunction, $g_group_permissions) || in_array('alles', $g_group_permissions)} checked="checked"{/if}{if $smarty.request.Id == 1 || $smarty.request.Id == $PAGE_NOT_FOUND_ID || in_array('alles', $g_group_permissions)} disabled="disabled"{/if}><label>{$module->ModuleName}</label>
				<div class="clear"></div>
			{/foreach}
			</td>
		</tr>
		{/if}
		<tbody id="perms_list">
			{foreach from=$g_all_permissions item=perm}
			{assign var="header" value="_"|explode:$perm}

			{if $header.0!="$headers"}
			{assign var="headers" value=$header.0}
			<tr class="header">
				<td colspan="2">{$smarty.config.$headers}</td>
			</tr>
			{/if}

			<tr>
				<td width="20" align="center">
					<input type="checkbox" class="checkbox" id="{$perm}" name="perms[]" value="{$perm}"{if in_array($perm, $g_group_permissions) || in_array('alles', $g_group_permissions)} checked="checked"{/if}{if $smarty.request.Id == 1 || $smarty.request.Id == $PAGE_NOT_FOUND_ID || (in_array('alles', $g_group_permissions) && $smarty.request.Id|escape == 1)} disabled="disabled"{/if} />
				</td>

				<td>
					{$smarty.config.$perm}
				</td>
			</tr>
			{/foreach}
		</tbody>
	</tbody>
</table>

<div class="rowElem" id="saveBtn">
	<div class="saveBtn">
	<input type="submit" class="basicBtn" value="{#UGROUP_BUTTON_SAVE#}" />&nbsp;{#UGROUP_OR#}&nbsp;<input type="submit" class="button blackBtn SaveSettings" value="{#UGROUP_BUTTON_SAVE_AJAX#}" />
	</div>
</div>


</div>
</fieldset>
</form>

{/if}


<script language="javascript">

$(document).ready(function(){ldelim}

	var sett_options = {ldelim}
		url: 'index.php?do=groups&action=grouprights&Id={$smarty.request.Id|escape}&cp={$sess}',
		data: {ldelim} ajax: '1', sub: 'save' {rdelim},
		dataType: 'json',
		beforeSubmit: Request,
		success: Response
	{rdelim}

	$(".SaveSettings").click(function(event){ldelim}
		event.preventDefault();
		var title = '{#UGROUP_BUTTON_SAVE#}';
		var confirm = '{#UGROUP_SAVE_CONFIRM#}';
		jConfirm(
				confirm,
				title,
				function(b){ldelim}
					if (b){ldelim}
						$("#groups").ajaxSubmit(sett_options);
					{rdelim}
				{rdelim}
			);
	{rdelim});

	Mousetrap.bind(['ctrl+s', 'command+s'], function(event) {ldelim}
		event.preventDefault();
		$("#groups").ajaxSubmit(sett_options);
		return false;
	{rdelim});

	$('#alles').on('change', function(event) {ldelim}
		event.preventDefault();
		if ($('#alles').is(':checked')) {ldelim}
			$('#perms_list .jqTransformCheckbox').removeClass('jqTransformChecked');
			$('#perms_list .checkbox').removeAttr('checked');
		{rdelim} else {ldelim}
			$('#perms_list .checkbox').attr('checked','checked');
			$('#perms_list .jqTransformCheckbox').addClass('jqTransformChecked');
		{rdelim}
	{rdelim});

{rdelim});

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

</script>