{if $fields_list}
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
					<a class="icon_sprite ico_move{if $field_group.fields|@count < 2}_no{/if}" style="cursor:move"></a>
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

	<script language="javascript">
		$(document).ready(function(){ldelim}
		AveAdmin.ajax();
		AveAdmin.modalDialog();

		$('#FieldsList tbody').tableSortable({ldelim}
		items: '.field_tbody',
		url: 'index.php?do=rubs&action=fieldssort&cp={$sess}',
		success: true
		{rdelim});

		{rdelim});
	</script>
</div>

{else}

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

{/if}
