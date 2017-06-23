<script type="text/javascript">
	$sess = '{$sess}';
</script>

<div class="title">
	<h5>{#SETTINGS_PAGINATION#}</h5>
</div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#SETTINGS_SAVE_INFO#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=settings&action=paginations&cp={$sess}">{#SETTINGS_PAGINATION#}</a></li>
			<li><strong class="code">{$pagination->pagination_name}</strong></li>
		</ul>
	</div>
</div>

<div class="widget first">
	<div class="body">
		{if check_permission('cache_clear')}<a class="button basicBtn clearCacheSess" href="javascript:void(0);">{#MAIN_STAT_CLEAR_CACHE_FULL#}</a>{/if}
		&nbsp;
		{if check_permission('cache_thumb')}<a class="button basicBtn clearThumb" href="javascript:void(0);">{#MAIN_STAT_CLEAR_THUMB#}</a>{/if}
		&nbsp;
		{if check_permission('document_revisions')}<a class="button basicBtn clearRev" href="javascript:void(0);">{#MAIN_STAT_CLEAR_REV#}</a>{/if}
		&nbsp;
		{if check_permission('gen_settings')}<a class="button basicBtn clearCount" href="javascript:void(0);">{#MAIN_STAT_CLEAR_COUNT#}</a>{/if}
	</div>
</div>

{if $smarty.const.SYSTEM_CACHE_LIFETIME > 0}
	<ul class="messages first">
		<li class="highlight red"><strong>{#SETTINGS_CACHE_LIFETIME#}</strong></li>
	</ul>
{/if}

<div class="widget first">
	<ul class="inact_tabs">
		{if check_permission('gen_settings')}<li><a href="index.php?do=settings&cp={$sess}">{#SETTINGS_MAIN_SETTINGS#}</a></li>{/if}
		{if check_permission('gen_settings_more')}<li><a href="index.php?do=settings&sub=case&cp={$sess}">{#SETTINGS_CASE_TITLE#}</a></li>{/if}
		{if check_permission('gen_settings_countries')}<li><a href="index.php?do=settings&sub=countries&cp={$sess}">{#MAIN_COUNTRY_EDIT#}</a></li>{/if}
		{if check_permission('gen_settings_languages')}<li><a href="index.php?do=settings&sub=language&cp={$sess}">{#SETTINGS_LANG_EDIT#}</a></li>{/if}
		<li class="activeTab"><a href="index.php?do=settings&action=paginations&cp={$sess}">{#SETTINGS_PAGINATION#}</a></li>
	</ul>

	<form id="paginations" action="index.php?do=settings&action=save_paginations&cp={$sess}" method="post" class="mainForm">

	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">
		<col width="300" />
		<col />
		<col width="300" />
		<col />
		<thead>
			<tr>
				<td>{#SETTINGS_NAME#}</td>
				<td>{#SETTINGS_VALUE#}</td>
				<td>{#SETTINGS_NAME#}</td>
				<td>{#SETTINGS_VALUE#}</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					{#pagination_name#}
				</td>
				<td colspan="3">
					<div class="pr12">
						<input type="text" name="pagination_name" id="pagination_name" value="{if isset($pagination->pagination_name)}{$pagination->pagination_name|escape|stripslashes}{/if}" class="mousetrap">
					</div>
				</td>
			</tr>
			<tr>
				<td>
					{#pagination_navigation_box#}
				</td>
				<td colspan="3">
					<div class="pr12">
						<textarea class="mousetrap" id="pagination_box" name="pagination_box" style="width: 100%; height: 60px;">{if isset($pagination->pagination_box)}{$pagination->pagination_box|escape|stripslashes}{/if}</textarea>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					{#pagination_link_box#}
				</td>
				<td>
					<div class="pr12">
						<input type="text" name="pagination_link_box" id="pagination_link_box" value="{if isset($pagination->pagination_link_box)}{$pagination->pagination_link_box|escape|stripslashes}{/if}" class="mousetrap">
					</div>
				</td>
				<td>
					{#pagination_active_link_box#}
				</td>
				<td>
					<div class="pr12">
						<input type="text" name="pagination_active_link_box" id="pagination_active_link_box" value="{if isset($pagination->pagination_link_box)}{$pagination->pagination_active_link_box|escape|stripslashes}{/if}" class="mousetrap">
					</div>
				</td>
			</tr>
			<tr>
				<td>
					{#pagination_link_template#}
				</td>
				<td colspan="3">
					<div class="pr12">
						<input type="text" name="pagination_link_template" id="pagination_link_template" value="{if isset($pagination->pagination_link_template)}{$pagination->pagination_link_template|escape|stripslashes}{/if}" class="mousetrap">
					</div>
				</td>
			</tr>
			<tr>
				<td>
					{#pagination_link_active_template#}
				</td>
				<td colspan="3">
					<div class="pr12">
						<input type="text" name="pagination_link_active_template" id="pagination_link_active_template" value="{if isset($pagination->pagination_link_active_template)}{$pagination->pagination_link_active_template|escape|stripslashes}{/if}" class="mousetrap">
					</div>
				</td>
			</tr>
			<tr>
				<td>
					{#pagination_separator_box#}
				</td>
				<td>
					<div class="pr12">
						<input type="text" name="pagination_separator_box" id="pagination_separator_box" value="{if isset($pagination->pagination_separator_box)}{$pagination->pagination_separator_box|escape|stripslashes}{/if}" class="mousetrap">
					</div>
				</td>
				<td>
					{#pagination_separator_label#}
				</td>
				<td>
					<div class="pr12">
						<input type="text" name="pagination_separator_label" id="pagination_separator_label" value="{if isset($pagination->pagination_separator_label)}{$pagination->pagination_separator_label|escape|stripslashes}{/if}" class="mousetrap">
					</div>
				</td>
			</tr>
			<tr>
				<td>
					{#pagination_start_label#}
				</td>
				<td>
					<div class="pr12">
						<input type="text" name="pagination_start_label" id="pagination_start_label" value="{if isset($pagination->pagination_start_label)}{$pagination->pagination_start_label|escape|stripslashes}{/if}" class="mousetrap">
					</div>
				</td>
				<td>
					{#pagination_end_label#}
				</td>
				<td>
					<div class="pr12">
						<input type="text" name="pagination_end_label" id="pagination_end_label" value="{if isset($pagination->pagination_end_label)}{$pagination->pagination_end_label|escape|stripslashes}{/if}" class="mousetrap">
					</div>
				</td>
			</tr>
			<tr>
				<td>
					{#pagination_next_label#}
				</td>
				<td>
					<div class="pr12">
						<input type="text" name="pagination_next_label" id="pagination_next_label" value="{if isset($pagination->pagination_next_label)}{$pagination->pagination_next_label|escape|stripslashes}{/if}" class="mousetrap">
					</div>
				</td>
				<td>
					{#pagination_prev_label#}
				</td>
				<td>
					<div class="pr12">
						<input type="text" name="pagination_prev_label" id="pagination_prev_label" value="{if isset($pagination->pagination_prev_label)}{$pagination->pagination_prev_label|escape|stripslashes}{/if}" class="mousetrap">
					</div>
				</td>
			</tr>
		</tbody>
	</table>
	<div class="rowElem" id="saveBtn">
		<div class="saveBtn">
			{if isset($smarty.request.id) && $smarty.request.id > 0}
			<input type="hidden" name="id" value="{$smarty.request.id}">
			{/if}
			<input name="submit" type="submit" class="basicBtn" value="Сохранить" />

			{if $smarty.request.action == 'edit_paginations'}
			<input type="submit" class="blackBtn SaveEdit" name="next_edit" value="Применить (CTRL + S)" />
			{/if}
		</div>
	</div>
	</form>
</div>

{if $smarty.request.action != 'new_paginations'}
<script language="javascript">
var sett_options = {ldelim}
	url: 'index.php?do=settings&action=save_paginations&cp={$sess}',
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

	Mousetrap.bind(['ctrl+s', 'command+s'], function(e) {ldelim}
		if (e.preventDefault) {ldelim}
			e.preventDefault();
		{rdelim} else {ldelim}
			e.returnValue = false;
		{rdelim}
		$("#paginations").ajaxSubmit(sett_options);
		return false;
	{rdelim});

	$(".SaveEdit").click(function(e){ldelim}
		if (e.preventDefault) {ldelim}
			e.preventDefault();
		{rdelim} else {ldelim}
			e.returnValue = false;
		{rdelim}
		$("#paginations").ajaxSubmit(sett_options);
		return false;
	{rdelim});

{rdelim});
</script>
{/if}

{include file="$codemirror_connect"}
{include file="$codemirror_editor" conn_id="1" textarea_id='pagination_box' ctrls='$("#paginations").ajaxSubmit(sett_options);' height='60'}
{include file="$codemirror_editor" conn_id="2" textarea_id='pagination_link_box' ctrls='$("#paginations").ajaxSubmit(sett_options);' height='40'}
{include file="$codemirror_editor" conn_id="3" textarea_id='pagination_active_link_box' ctrls='$("#paginations").ajaxSubmit(sett_options);' height='40'}
{include file="$codemirror_editor" conn_id="4" textarea_id='pagination_link_template' ctrls='$("#paginations").ajaxSubmit(sett_options);' height='60'}
{include file="$codemirror_editor" conn_id="5" textarea_id='pagination_link_active_template' ctrls='$("#paginations").ajaxSubmit(sett_options);' height='60'}