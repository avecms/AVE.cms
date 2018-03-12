<script language="Javascript" type="text/javascript">
var sess = '{$sess}';
</script>

<div class="title">
	<h5>{#RUBRIK_EDIT_RULES#}</h5>
</div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">{#RUBRIC_WARNING_TIP#}</div>
</div>

<table class="first tableButtons" cellpadding="0" cellspacing="0" width="100%" id="rubricButtons">
	<col width="20%">
	<col width="20%">
	<col width="20%">
	<col width="20%">
	<col width="20%">
	<tr>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=edit&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_FIELDS#}</a>
		</td>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=ftlist&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_FTEMPLATES#}</a>
		</td>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=fieldsgroups&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_FGROUPS#}</a>
		</td>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=template&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_TEMPLATES#}</a>
		</td>
		<td>
			{if check_permission('rubric_code')}
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=code&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_CODE#}</a>
			{/if}
		</td>
	</tr>
</table>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB">
				<a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a>
			</li>
			<li>
				<a href="index.php?do=rubs&cp={$sess}">{#RUBRIK_SUB_TITLE#}</a>
			</li>
			<li>{#RUBRIK_EDIT_FIELDS#}</li>
			<li><strong class="code">{$rubric->rubric_title}</strong></li>
		</ul>
	</div>
</div>

{if check_permission('rubric_edit') && check_permission('rubric_perms')}
<div class="widget first">
	<div class="head">
		<h5>{#RUBRIK_SET_PERMISSION#}</h5>
	</div>
	<div style="display: block;">
		<form id="rubperm" action="index.php?do=rubs&action=rules&Id={$smarty.request.Id|escape}&submit=saveperms&cp={$sess}" method="post" class="mainForm">
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<col width="16%">
				<col width="12%">
				<col width="12%">
				<col width="12%">
				<col width="12%">
				<col width="12%">
				<col width="12%">
				<col width="12%">
				<thead>
					<tr>
						<td>{#RUBRIK_USER_GROUP#}</td>
						<td align="center">
							{#RUBRIK_DOC_READ#}&nbsp;<a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_VIEW_TIP#}">[?]</a>
						</td>
						<td align="center">
							{#RUBRIK_ALL_PERMISSION#}&nbsp;<a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_ALL_TIP#}">[?]</a>
						</td>
						<td align="center">
							{#RUBRIK_CREATE_DOC#}&nbsp;<a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_DOC_TIP#}">[?]</a>
						</td>
						<td align="center">
							{#RUBRIK_CREATE_DOC_NOW#}&nbsp;<a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_DOC_NOW_TIP#}">[?]</a>
						</td>
						<td align="center">
							{#RUBRIK_EDIT_OWN#}&nbsp;<a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_OWN_TIP#}">[?]</a>
						</td>
						<td align="center">
							{#RUBRIK_EDIT_OTHER#}&nbsp;<a href="javascript:void(0);" class="topleftDir link" style="cursor: help;" title="{#RUBRIK_OTHER_TIP#}">[?]</a>
						</td>
						<td align="center">
							{#RUBRIK_EDIT_DELREV#}&nbsp;<a href="javascript:void(0);" class="topleftDir link" style="cursor: help;" title="{#RUBRIK_DELREV_TIP#}">[?]</a>
						</td>
					</tr>
				</thead>
				<tbody>
					{foreach from=$groups item=group}
					{assign var=doall value=$group->doall}
					<tr>
						<td>{$group->user_group_name|escape:html} </td>
						<td align="center" {if in_array('docread', $group->permissions) || in_array('alles', $group->permissions)} class="yellow"{/if}>
							{if $group->doall_h==1}
							<input type="hidden" name="perm[{$group->user_group}][]" value="docread" />
							<input class="check_perm" name="perm[{$group->user_group}][]" type="checkbox" value="docread" checked="checked" disabled="disabled" />
							{else}
							<input class="check_perm" name="perm[{$group->user_group}][]" type="checkbox" value="docread"{if in_array('docread', $group->permissions) || in_array('alles', $group->permissions)} checked="checked"{/if} />
							{/if}
						</td>
						<td align="center" {if in_array('alles', $group->permissions)} class="yellow"{/if}>
							{if $group->doall_h==1}
							<input type="hidden" name="perm[{$group->user_group}][]" value="alles" />
							<input class="check_perm" name="perm[{$group->user_group}][]" type="checkbox" value="alles" checked="checked" disabled="disabled" />
							{else}
							<input class="check_perm" name="perm[{$group->user_group}][]" type="checkbox" value="alles"{if in_array('alles', $group->permissions)} checked="checked"{/if}{if $group->user_group==2} disabled="disabled"{/if} />
							{/if}
						</td>
						<td align="center" {if in_array('new', $group->permissions) || in_array('alles', $group->permissions)} class="yellow"{/if}>
							<input type="hidden" name="user_group[{$group->user_group}]" value="{$group->user_group}" />
							{if $group->doall_h==1}
							<input class="check_perm" name="{$group->user_group}" type="checkbox" value="1"{$doall} />
							<input type="hidden" name="perm[{$group->user_group}][]" value="new" />
							{else}
							<input class="check_perm new" id="new_{$group->user_group}" name="perm[{$group->user_group}][]" type="checkbox" value="new"{if in_array('new', $group->permissions) || in_array('alles', $group->permissions)} checked="checked"{/if}{if $group->user_group==2} disabled="disabled"{/if} />
							{/if}
						</td>
						<td align="center" {if in_array('newnow', $group->permissions) || in_array('alles', $group->permissions)} class="yellow"{/if}>
							<input type="hidden" name="user_group[{$group->user_group}]" value="{$group->user_group}" />
							{if $group->doall_h==1}
							<input class="check_perm" name="{$group->user_group}" type="checkbox" value="1"{$doall} />
							<input type="hidden" name="perm[{$group->user_group}][]" value="newnow" />
							{else}
							<input class="check_perm newnow" id="newnow_{$group->user_group}" name="perm[{$group->user_group}][]" type="checkbox" value="newnow"{if in_array('newnow', $group->permissions) || in_array('alles', $group->permissions)} checked="checked"{/if}{if $group->user_group==2} disabled="disabled"{/if} />
							{/if}
						</td>
						<td align="center" {if in_array('editown', $group->permissions) || in_array('alles', $group->permissions)} class="yellow"{/if}>
							{if $group->doall_h==1}
							<input class="check_perm" name="{$group->user_group}" type="checkbox" value="1"{$doall} />
							<input type="hidden" name="perm[{$group->user_group}][]" value="editown" />
							{else}
							<input class="check_perm editown" id="editown_{$group->user_group}" data-id="{$group->user_group}" name="perm[{$group->user_group}][]" type="checkbox" value="editown"{if in_array('editown', $group->permissions) || in_array('alles', $group->permissions)} checked="checked"{/if}{if $group->user_group==2} disabled="disabled"{/if} />
							{/if}
						</td>
						<td align="center" {if in_array('editall', $group->permissions) || in_array('alles', $group->permissions)} class="yellow"{/if}>
							{if $group->doall_h==1}
								<input class="check_perm" name="{$group->user_group}" type="checkbox" value="1"{$doall} />
							{else}
								<input class="check_perm editall" id="editall_{$group->user_group}" name="perm[{$group->user_group}][]" data-id="{$group->user_group}" type="checkbox" value="editall"{if in_array('editall', $group->permissions) || in_array('alles', $group->permissions)} checked="checked"{/if}{if $group->user_group==2} disabled="disabled"{/if} />
							{/if}
						</td>
						<td align="center" {if in_array('delrev', $group->permissions) || in_array('alles', $group->permissions)} class="yellow"{/if}>
							{if $group->doall_h==1}
								<input class="check_perm" name="{$group->user_group}" type="checkbox" value="1"{$doall} />
								<input type="hidden" name="perm[{$group->user_group}][]" value="delrev" />
							{else}
								<input class="check_perm" name="perm[{$group->user_group}][]" type="checkbox" value="delrev"{if in_array('delrev', $group->permissions) || in_array('alles', $group->permissions)} checked="checked"{/if}{if $group->user_group==2} disabled="disabled"{/if} />
							{/if}
						</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
			<div class="rowElem">
				<input type="submit" class="basicBtn" value="{#RUBRIK_BUTTON_PERM#}" />
				&nbsp;
				<input type="submit" class="blackBtn SaveEditPerms" value="{#RUBRIK_BUTTON_TPL_NEXT#}" />
			</div>
		</form>
	</div>
</div>
{/if}

<script language="javascript">
$(document).ready(function(){ldelim}

	{literal}

	$(document).on('change', '#selall', function(event) {
		event.preventDefault();
		if ($('#selall').is(':checked')) {
			$('#FieldsList .checkbox').attr('checked','checked');
			$('#FieldsList .checkbox').addClass('jqTransformChecked');
		} else {
			$('#FieldsList .checkbox').removeClass('jqTransformChecked');
			$('#FieldsList .checkbox').removeAttr('checked');
		}
	});

	$('.check_perm').on('change', function(event) {
		event.preventDefault();
		if	($(this).is(':checked')) {
			$(this).parent().parent('td').addClass('yellow');
		} else {
			$(this).parent().parent('td').removeClass('yellow');
		}
	});

	{/literal}

	Mousetrap.bind(['ctrl+s', 'command+s'], function(event) {ldelim}
		event.preventDefault();
		$("#rubperm").ajaxSubmit({ldelim}
			url: 'index.php?do=rubs&action=rules&Id={$smarty.request.Id|escape}&submit=saveperms&cp={$sess}&ajax=1',
			dataType: 'json',
			beforeSubmit: function(){ldelim}
				$.alerts._overlay('show');
			{rdelim},
			success: function(data){ldelim}
				$.jGrowl(data['message'], {ldelim}
					header: data['header'],
					theme: data['theme']
				{rdelim});
					$.alerts._overlay('hide');
			{rdelim}
		{rdelim});
		return false;
	{rdelim});

	$(".SaveEditPerms").click(function(event){ldelim}
		event.preventDefault();
		$("#rubperm").ajaxSubmit({ldelim}
			url: 'index.php?do=rubs&action=rules&Id={$smarty.request.Id|escape}&submit=saveperms&cp={$sess}&ajax=1',
			dataType: 'json',
			beforeSubmit: function(){ldelim}
				$.alerts._overlay('show');
			{rdelim},
			success: function(data){ldelim}
				$.jGrowl(data['message'], {ldelim}
					header: data['header'],
					theme: data['theme']
				{rdelim});
					$.alerts._overlay('hide');
			{rdelim}
		{rdelim});
		return false;
	{rdelim});


{rdelim});
</script>
