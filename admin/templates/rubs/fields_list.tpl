<script language="Javascript" type="text/javascript">

var sess = '{$sess}';

function openAliasWindow(fieldId, rubId, width, height, target) {ldelim}
	if (typeof width=='undefined' || width=='') var width = screen.width * 0.8;
	if (typeof height=='undefined' || height=='') var height = screen.height * 0.8;
	if (typeof scrollbar=='undefined') var scrollbar=1;
	var left = ( screen.width - width ) / 2;
	var top = ( screen.height - height ) / 2;
	window.open('index.php?field_id='+fieldId+'&rubric_id='+rubId+'&target='+target+'&do=rubs&action=alias_add&cp={$sess}&pop=1','pop','left='+left+',top='+top+',width='+width+',height='+height+',scrollbars='+scrollbar+',resizable=1').focus();
{rdelim}

</script>
<div class="title">
	<h5>{#RUBRIK_EDIT_FIELDS#}</h5>
</div>
{if !$rub_fields}
<div class="widget" style="margin-top: 0px;">
	<div class="body">{#RUBRIK_NO_FIELDS#}</div>
</div>
{else}
<div class="widget" style="margin-top: 0px;">
	<div class="body">{#RUBRIK_FIELDS_INFO#}</div>
</div>
{/if}
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

<form action="index.php?do=rubs&action=edit&Id={$smarty.request.Id|escape}&cp={$sess}" method="post" class="mainForm" id="RubricDescription">
	<div class="widget first">
		<div class="head">
			<h5 class="iFrames">{#RUBRIK_DESCRIPTION#}</h5>
		</div>
		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
			<tr>
				<td>
					<div class="pr12">
						<textarea wrap="off" placeholder="{#RUBRIK_DESCRIPTION#}" style="width:100%; height:40px" name="rubric_description">{$rubric->rubric_description|escape}</textarea>
					</div>
				</td>
			</tr>
		</table>
		<div class="rowElem">
			<input type="hidden" name="submit" value="" id="nd_sub" />
			<input type="submit" class="basicBtn" value="{#RUBRIK_BUTTON_SAVE#}" onclick="document.getElementById('nd_sub').value='description'" />
		</div>
	</div>
</form>

{if $fields_list}

<form action="index.php?do=rubs&action=edit&Id={$smarty.request.Id|escape}&cp={$sess}" method="post" class="mainForm" id="Rubric">
	<div class="widget first">

		<div class="head">
			<h5 class="iFrames">{#RUBRIK_FIELDS_TITLE#}</h5>
			<div class="num">
				<a class="basicNum" href="index.php?do=rubs&action=template&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIK_EDIT_TEMPLATE#}</a>
				&nbsp;
				<a class="basicNum" href="index.php?do=rubs&action=fieldsgroups&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIK_EDIT_FIELDS_GROUPS#}</a>
				&nbsp;
				{if check_permission('rubric_code')}
					<a class="basicNum" href="index.php?do=rubs&action=code&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIK_EDIT_CODE#}</a>
				{/if}

			</div>
		</div>

		<div id="fields_list">

			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic" id="FieldsList">
				<col width="10">
				<col width="10">
				<col width="10">
				<col width="10">
				<col width="10">
				<col width="100">
				<col>
				<col width="280">
				<col width="280">
				<col width="10">
				<thead>
					<tr>
						<td align="center" title="{#RUBRIK_MARK_DEL_ALL#}" class="topDir">
							<div align="center">
								<input type="checkbox" id="selall" value="1" />
							</div>
						</td>
						<td align="center"><a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_F_SORT_TIP#}">[?]</a></td>
						<td align="center"><a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_NUMERIC_TIP#}">[?]</a></td>
						<td align="center"><a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_SEARCH_TIP#}">[?]</a></td>
						<td>{#RUBRIK_ID#}</td>
						<td>{#RUBRIK_FIELD_ALIAS#}</td>
						<td>{#RUBRIK_FIELD_NAME#}</td>
						<td>{#RUBRIK_FIELD_TYPE#}</td>
						<td>{#RUBRIK_FIELD_GROUP#}</td>
						<td align="center"></td>
					</tr>
				</thead>

				{foreach from=$fields_list item=field_group}
				<tbody>
					{if $groups_count > 1}
					<tr class="grey">
						<td colspan="10" class="aligncenter"><h5>{if $field_group.group_title}{$field_group.group_title}{else}{#RUBRIK_FIELD_G_UNKNOW#}{/if}</h5></td>
					</tr>
					{/if}

					{foreach  from=$field_group.fields item=field}

						<tr data-id="field_{$field.Id}" class="field_tbody">
							<td align="center">
								<input title="{#RUBRIK_MARK_DELETE#}" name="del[{$field.Id}]" type="checkbox" id="del[{$field.Id}]" value="1" class="checkbox topDir mousetrap" />
							</td>
							<td align="center">
								<a class="icon_sprite ico_move{if $field_group.fields|@count<2}_no{/if}" style="cursor:move"></a>
							</td>
							<td align="center">
								<input class="mousetrap" name="rubric_field_numeric[{$field.Id}]" type="hidden" value="0" />
								<input class="mousetrap" title="{#RUBRIK_CHECK_NUMERIC#}" name="rubric_field_numeric[{$field.Id}]" {if $field.rubric_field_numeric}checked{/if} type="checkbox" value="1" class="topDir mousetrap" />
							</td>
							<td align="center">
								<input name="rubric_field_search[{$field.Id}]" type="hidden" value="0" />
								<input title="{#RUBRIK_CHECK_SEARCH#}" name="rubric_field_search[{$field.Id}]" {if $field.rubric_field_search}checked{/if} type="checkbox" value="1" class="topDir mousetrap" />
							</td>
							<td align="center"><strong class="code">{$field.Id}</strong></td>
							<td nowrap>
								<input class="mousetrap" name="alias[{$field.Id}]" type="text" id="alias_{$field.Id}" value="{$field.rubric_field_alias|escape}" style="width: 100px;" readonly />
								&nbsp;
								<a data-dialog="rft-alias-{$field.Id}" href="index.php?do=rubs&action=alias_add&field_id={$field.Id}&rubric_id={$smarty.request.Id|escape}&cp={$sess}&pop=1&onlycontent=1" data-width="650" data-height="350" data-modal="true" data-title="{#RUBRIK_ALIAS_HEAD#}" class="changeAlias button blackBtn openDialog">...</a>
							</td>
							<td>
								<div class="pr12">
									<input class="mousetrap" name="title[{$field.Id}]" type="text" id="title[{$field.Id}]" value="{$field.rubric_field_title|escape}" style="width:100%;" />
								</div>
							</td>
							<td class="aligncenter">
								<div id="rub_field_{$field.rubric_id}_{$field.Id}">
								{assign var="unknow" value="true"}

								{section name=field loop=$fields}
									{if $field.rubric_field_type == $fields[field].id}
										{assign var="unknow" value=""}
										<a href="javascript:void(0);" class="link change_type" data-rubric="{$field.rubric_id}" data-id="{$field.Id}">{$fields[field].name}</a>
									{/if}
								{/section}

								{if $unknow}
								<a href="javascript:void(0);" class="link change_type" data-rubric="{$field.rubric_id}" data-id="{$field.Id}">{#RUBRIK_FIELD_UNKNOW#}</a>
								{/if}

								{assign var="unknow" value=""}
								</div>
							</td>
							<td align="center">
								<div id="rub_field_group_{$field.rubric_id}_{$field.Id}">
									{assign var="unknow_group" value="true"}
									{foreach from=$fields_groups item=group}
										{if $field.rubric_field_group == $group->Id}
										{assign var="unknow_group" value=""}
										<a href="javascript:void(0);" class="link change_group" data-rubric="{$field.rubric_id}" data-id="{$field.Id}">{$group->group_title}</a>
										{/if}
									{/foreach}

									{if $unknow_group}
										<a href="javascript:void(0);" class="link change_group" data-rubric="{$field.rubric_id}" data-id="{$field.Id}">{#RUBRIK_FIELD_G_UNKNOW#}</a>
									{/if}
								</div>
							</td>
							<td align="center">
								<a data-dialog="rft-{$field.Id}" href="index.php?do=rubs&action=field_template&field_id={$field.Id}&rubric_id={$smarty.request.Id|escape}&cp={$sess}&pop=1&onlycontent=1" data-height="700" data-modal="true" data-title="{#RUBRIK_ALIAS_HEAD#}" class="openDialog icon_sprite ico_template"></a>
							</td>
						</tr>
						{/foreach}
					</tbody>
				{/foreach}
		</table>


		<div class="rowElem" id="saveBtn">
			<div class="saveBtn">
				<input type="hidden" name="submit" value="save" id="nf_save_next" />
				<input type="submit" class="basicBtn" value="{#RUBRIK_BUTTON_SAVE#}" />
				&nbsp;
				<input type="submit" class="blackBtn SaveEditFields" value="{#RUBRIK_BUTTON_TPL_NEXT#}" />
				&nbsp;
				<a class="button redBtn" href="index.php?do=rubs&action=template&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIK_BUTTON_TEMPL#}</a>
			</div>
		</div>
		</div>
	</div>
</form>

{else}

<form action="index.php?do=rubs&action=edit&Id={$smarty.request.Id|escape}&cp={$sess}" method="post" class="mainForm" id="Rubric">
	<div class="widget first">
		<div class="head">
			<h5 class="iFrames">{#RUBRIK_FIELDS_TITLE#}</h5>
			<div class="num">
				<a class="basicNum" href="index.php?do=rubs&action=template&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIK_EDIT_TEMPLATE#}</a>
			</div>
		</div>
		<div id="fields_list">
		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic" id="FieldsList">
			<col width="10">
			<col width="10">
			<col width="10">
			<col width="10">
			<col width="10">
			<col width="100">
			<col>
			<col width="280">
			<col width="280">
			<col width="10">
			<thead>
				<tr>
					<td align="center" title="{#RUBRIK_MARK_DEL_ALL#}" class="topDir">
						<div align="center">
							<input type="checkbox" id="selall" value="1" />
						</div>
					</td>
					<td align="center"><a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_F_SORT_TIP#}">[?]</a></td>
					<td align="center"><a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_NUMERIC_TIP#}">[?]</a></td>
					<td align="center"><a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_SEARCH_TIP#}">[?]</a></td>
					<td>{#RUBRIK_ID#}</td>
					<td>{#RUBRIK_FIELD_ALIAS#}</td>
					<td>{#RUBRIK_FIELD_NAME#}</td>
					<td>{#RUBRIK_FIELD_TYPE#}</td>
					<td>{#RUBRIK_FIELD_GROUP#}</td>
					<td align="center"></td>
				</tr>
			</thead>

			<tbody class="field_tbody">
				<tr>
					<td align="center" colspan="10">
						<ul class="messages">
							<li class="highlight red">{#RUBRIK_NO_FIELDS#}</li>
						</ul>
					</td>
				</tr>
			</tbody>

		</table>
		</div>
	</div>
</form>
{/if}

{* Новое поле *}
<div class="widget first">
	<div class="head collapsible" id="opened">
		<h5>{#RUBRIK_NEW_FIELD#}</h5>
	</div>
	<div style="display: block;">
		<div class="body">{#RUBRIK_NEW_FIEL_TITLE#}</div>
		<form id="newfld" action="index.php?do=rubs&action=newfield&Id={$smarty.request.Id|escape}&cp={$sess}" method="post" class="mainForm">
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<col width="10">
				<col width="10">
				<col width="350">
				<col width="220">
				<col width="100">
				<col>
				<thead>
					<tr>
						<td align="center"><a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_NUMERIC_TIP#}">[?]</a></td>
						<td align="center"><a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_SEARCH_TIP#}">[?]</a></td>
						<td>{#RUBRIK_FIELD_NAME#}</td>
						<td>{#RUBRIK_FIELD_TYPE#}</td>
						<td>{#RUBRIK_FIELD_GROUP#}</td>
						<td>{#RUBRIK_FIELD_DEFAULT#}</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td><input title="{#RUBRIK_CHECK_NUMERIC#}" name="rubric_field_numeric" type="checkbox" value="1" class="topDir mousetrap" /></td>
						<td><input title="{#RUBRIK_CHECK_SEARCH#}" name="rubric_field_search" type="checkbox" value="1" class="topDir mousetrap" /></td>
						<td>
							<div class="pr12">
								<input name="title_new" type="text" id="title_new" value="" style="width:100%;" />
							</div>
						</td>
						<td>
							<select name="rub_type_new" id="rub_type_new" style="width: 250px;">
								{section name=field loop=$fields}
								<option value="{$fields[field].id}" {if $fields[field].id == 'single_line'}selected{/if}>{$fields[field].name}</option>
								{/section}
							</select>
						</td>
						<td>
							<select name="group_new" id="group_new" style="width: 300px;">
								<option value="">{#RUBRIK_FIELD_GROUP_SEL#}</option>
								{foreach from=$fields_groups item=f_group}
								<option value="{$f_group->Id}">{$f_group->group_title|escape}</option>
								{/foreach}
							</select>
						</td>
						<td>
							<div class="pr12">
								<input name="default_value" type="text" id="default_value" value="" style="width:100%;" />
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="rowElem">
				<input class="basicBtn AddNewField" type="submit" value="{#RUBRIK_BUTTON_ADD#}" />
			</div>
		</form>
	</div>
</div>


{*  Связать рубрику *}
<div class="widget first">
	<div class="head closed active">
		<h5>{#RUBRIK_LINK#}</h5>
	</div>
	<div style="display: block;">
		<form action="index.php?do=rubs&action=edit&Id={$smarty.request.Id|escape}&cp={$sess}" method="post" class="mainForm">
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<col width="50%">
				<col width="50%">
				<tbody>
					<tr>
						<td>
							<div class="pr12"> {#RUBRIK_LINK_DESC#} </div>
						</td>
						<td>
							<div class="pr12">
								{foreach from=$rubs item=rub}
									{if $rub->Id != $smarty.request.Id}
									<div class="fix">
										<input type="checkbox" class="float" {if in_array($rub->Id, $rubric->rubric_linked_rubric)}checked="checked"{/if} name="rubric_linked[]" value="{$rub->Id}" />
										<label>{$rub->rubric_title}</label>
									</div>
									{/if}
								{/foreach}
							</div>
						</td>
					</tr>
				</tbody>
			</table>
			<div class="rowElem">
				<input type="hidden" name="submit" value="linked_rubric" id="linked_rubric" />
				<input class="basicBtn" type="submit" value="{#RUBRIK_BUTTON_SAVE#}" />
			</div>
		</form>
	</div>
</div>

{if check_permission('rubric_edit') && check_permission('rubric_perms')}
<div class="widget first">
	<div class="head closed active">
		<h5>{#RUBRIK_SET_PERMISSION#}</h5>
	</div>
	<div style="display: block;">
		<form id="rubperm" action="index.php?do=rubs&action=edit&Id={$smarty.request.Id|escape}&submit=saveperms&cp={$sess}" method="post" class="mainForm">
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

{include file="$codemirror_connect"}

<script language="javascript">

	var sett_options = {ldelim}
		url: 'index.php?do=rubs&action=edit&Id={$smarty.request.Id|escape}&cp={$sess}&ajax=1',
		dataType: 'json',
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

	// сортировка полей
	$('#FieldsList tbody').tableSortable({ldelim}
		items: '.field_tbody',
		url: 'index.php?do=rubs&action=fieldssort&cp={$sess}',
		success: true
	{rdelim});

	{literal}

	$(document).on('click', '.change_group', function(event)
	{
		event.preventDefault();

		var Id = $(this).attr('data-id');
		var RubId = $(this).attr('data-rubric');

		$.ajax({
			url: 'index.php?do=rubs&action=changegroup&field_id=' + Id + '&rubric_id=' + RubId + '&cp=' + sess + '&onlycontent=1',
				type: 'POST',
				beforeSend: function () {
			},
			success: function (data)
			{
				$("#rub_field_group_" + RubId + "_" + Id).before(data).remove();
				$.alerts._overlay('hide');
			}
		});

		return false;
	});

	$(document).on('click', '.change_type', function(event)
	{
		event.preventDefault();

		var Id = $(this).attr('data-id');
		var RubId = $(this).attr('data-rubric');

		$.ajax({
			url: 'index.php?do=rubs&action=change&field_id=' + Id + '&rubric_id=' + RubId + '&cp=' + sess + '&onlycontent=1',
				type: 'POST',
				beforeSend: function () {
			},
			success: function (data)
			{
				$("#rub_field_" + RubId + "_" + Id).before(data).remove();
				$.alerts._overlay('hide');
			}
		});

		return false;
	});

	$(document).on('click', '.SaveChangeField', function(event)
	{
		event.preventDefault();

		var form = $(this).parent('form');

		var Id = $(this).attr('data-id');
		var RubId = $(this).attr('data-rubric');

		form.ajaxSubmit({
			url: form.attr('action'),
			type: 'POST',
			beforeSubmit: function()
			{
				$.alerts._overlay('show');
			},
			success: function(data)
			{
				$("#rub_field_" + RubId + "_" + Id).before(data).remove();
				$.alerts._overlay('hide');
			}
		});

		return false;
	});

	$(document).on('click', '.SaveChangeFieldGroup', function(event)
	{
		event.preventDefault();

		var form = $(this).parent('form');

		var Id = $(this).attr('data-id');
		var RubId = $(this).attr('data-rubric');

		form.ajaxSubmit({
			url: form.attr('action'),
			type: 'POST',
			beforeSubmit: function()
			{
				$.alerts._overlay('show');
			},
			success: function(data)
			{
				$("#rub_field_group_" + RubId + "_" + Id).before(data).remove();
			}
		});

		return false;
	});


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

	$('.collapsible').collapsible({
		defaultOpen: 'opened',
		cssOpen: 'inactive',
		cssClose: 'normal',
		cookieName: 'collaps_rub',
		cookieOptions: {
			expires: 7,
			domain: ''
		},
		speed: 200
	});
	{/literal}

	Mousetrap.bind(['ctrl+s', 'command+s'], function(event) {ldelim}
		if (event.preventDefault) {ldelim}
			event.preventDefault();
		{rdelim} else {ldelim}
			event.returnValue = false;
		{rdelim}
			$("#Rubric").ajaxSubmit({ldelim}
				url: 'index.php?do=rubs&action=edit&submit=save&Id={$smarty.request.Id|escape}&cp={$sess}&ajax=1',
				dataType: 'json',
				beforeSubmit: function(){ldelim}
					$.alerts._overlay('show');
				{rdelim},
				success: function(data){ldelim}
					$.jGrowl(data['message'], {ldelim}
						header: data['header'],
						theme: data['theme']
					{rdelim});
					ajaxFields();
				{rdelim}
			{rdelim});
		return false;
	{rdelim});


	function SaveEditFields(){ldelim}
			$(".SaveEditFields").on('click', function(event){ldelim}
				event.preventDefault();
				$("#Rubric").ajaxSubmit({ldelim}
					url: 'index.php?do=rubs&action=edit&submit=save&Id={$smarty.request.Id|escape}&cp={$sess}&ajax=1',
					dataType: 'json',
					beforeSubmit: function(){ldelim}
						$.alerts._overlay('show');
					{rdelim},
					success: function(data){ldelim}
						$.jGrowl(data['message'], {ldelim}
							header: data['header'],
							theme: data['theme']
						{rdelim});
						ajaxFields();
					{rdelim}
				{rdelim});
			return false;
		{rdelim});
	{rdelim};

	$(".AddNewField").click(function(event){ldelim}
		event.preventDefault();
		$("#newfld").ajaxSubmit({ldelim}
			url: 'index.php?do=rubs&action=newfield&Id={$smarty.request.Id|escape}&cp={$sess}&ajax=1',
			dataType: 'json',
			beforeSubmit: function(){ldelim}
				$.alerts._overlay('show');
			{rdelim},
			success: function(data){ldelim}
				$.jGrowl(data['message'], {ldelim}
					header: data['header'],
					theme: data['theme']
				{rdelim});
				if (data['theme'] != 'error') {ldelim}
					resetForms();
					ajaxFields();
				{rdelim} else {ldelim}
					$.alerts._overlay('hide');
				{rdelim}
			{rdelim}
		{rdelim});
		return false;
	{rdelim});

	$(".SaveEditPerms").click(function(event){ldelim}
		event.preventDefault();
		$("#rubperm").ajaxSubmit({ldelim}
			url: 'index.php?do=rubs&action=edit&Id={$smarty.request.Id|escape}&submit=saveperms&cp={$sess}&ajax=1',
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

	function ajaxFields(){ldelim}
		$.ajax({ldelim}
			url: 'index.php?do=rubs&action=fields&Id={$smarty.request.Id|escape}&cp={$sess}&ajax=1&onlycontent=1',
			type: 'POST',
			beforeSend: function () {ldelim}
				//$("#fields_list").css('opacity', 0.5);
			{rdelim},
			success: function (data) {ldelim}
				$("#fields_list").before(data).remove();
				//$("#fields_list").css('opacity', 1.0);
				SaveEditFields();
				$.alerts._overlay('hide');
			{rdelim}
		{rdelim});
	{rdelim}

	function resetForms(){ldelim}
		$('#newfld').find('a.jqTransformCheckbox').removeClass('jqTransformChecked');
		$('#newfld').find('select').prop('selectedIndex',0);
		$('#newfld').find('select').trigger('refresh');
		$('#newfld input[type=text]').val('');
		$('#newfld').trigger('refresh');
	{rdelim}

SaveEditFields();

AveAdmin.ajaxSave = function() {ldelim}
	$.ajax({ldelim}
		url: 'index.php?do=rubs&action=fields&Id={$smarty.request.Id|escape}&cp={$sess}&onlycontent=1',
		type: 'POST',
		beforeSend: function () {ldelim}
			//$("#fields_list").css('opacity', 0.5);
		{rdelim},
		success: function (data) {ldelim}
			$("#fields_list").before(data).remove();
			//$("#fields_list").css('opacity', 1.0);
			SaveEditFields();
			$.alerts._overlay('hide');
		{rdelim}
	{rdelim});
{rdelim}

{rdelim});
</script>
