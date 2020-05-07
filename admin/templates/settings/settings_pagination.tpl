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
			<li>{#SETTINGS_PAGINATION#}</li>
		</ul>
	</div>
</div>

<div class="widget first">
	<div class="body">
		{if check_permission('cache_clear')}<a class="button redBtn clearCacheSess" href="javascript:void(0);">{#MAIN_STAT_CLEAR_CACHE_FULL#}</a>&nbsp;{/if}
		{if check_permission('cache_thumb')}<a class="button redBtn clearThumb" href="javascript:void(0);">{#MAIN_STAT_CLEAR_THUMB#}</a>&nbsp;{/if}
		{if check_permission('document_revisions')}<a class="button redBtn clearRev" href="javascript:void(0);">{#MAIN_STAT_CLEAR_REV#}</a>&nbsp;{/if}
		{if check_permission('gen_settings')}<a class="button redBtn clearCount" href="javascript:void(0);">{#MAIN_STAT_CLEAR_COUNT#}</a>&nbsp;{/if}
		{if check_permission('gen_settings_robots')}<a data-dialog="robots" data-title="{#SETTINGS_FILE_ROBOTS#}" data-height="650" data-modal="true" class="button greenBtn openDialog" href="index.php?do=settings&action=robots&cp={$sess}">{#SETTINGS_FILE_ROBOTS#}</a>&nbsp;{/if}
		{if check_permission('gen_settings_fcustom')}<a data-dialog="custom" data-title="{#SETTINGS_FILE_CUSTOM#}" data-height="650" data-modal="true" class="button greenBtn openDialog" href="index.php?do=settings&action=custom&cp={$sess}">{#SETTINGS_FILE_CUSTOM#}</a>{/if}
	</div>
</div>

<div class="widget first">
	<ul class="inact_tabs">
		{if check_permission('gen_settings')}<li><a href="index.php?do=settings&cp={$sess}">{#SETTINGS_MAIN_SETTINGS#}</a></li>{/if}
		{if check_permission('gen_settings_more')}<li><a href="index.php?do=settings&sub=case&cp={$sess}">{#SETTINGS_CASE_TITLE#}</a></li>{/if}
		{if check_permission('gen_settings_countries')}<li><a href="index.php?do=settings&sub=countries&cp={$sess}">{#MAIN_COUNTRY_EDIT#}</a></li>{/if}
		{if check_permission('gen_settings_languages')}<li><a href="index.php?do=settings&sub=language&cp={$sess}">{#SETTINGS_LANG_EDIT#}</a></li>{/if}
		<li class="activeTab"><a href="index.php?do=settings&action=paginations&cp={$sess}">{#SETTINGS_PAGINATION#}</a></li>
		<li><a href="index.php?do=settings&action=showcache&cp={$sess}">{#SETTINGS_SHOWCACHE#}</a></li>
		<div class="num">
			<a class="greenNum" href="index.php?do=settings&action=new_paginations&cp={$sess}">{#PAGINATION_ADD#}</a>
		</div>
	</ul>

	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">
		<col width="10" />
		<col />
		<col width="10" />
		<col width="10" />
		<thead>
			<tr>
				<td>ID</td>
				<td>{#PAGINATION_NAME#}</td>
				<td colspan="2">{#PAGINATION_ACTIONS#}</td>
			</tr>
		</thead>
		<tbody>
			{if $items}
			{foreach from=$items item=item}
			<tr>
				<td>
					{$item->id}
				</td>
				<td>
					<strong><a class="topDir" title="{#PAGINATION_EDIT_HINT#}" href="index.php?do=settings&action=edit_paginations&id={$item->id}&cp={$sess}" id="{$item->id}">{$item->pagination_name}</a></strong>
				</td>
				<td>
					<a class="topleftDir icon_sprite ico_edit" title="{#PAGINATION_EDIT_HINT#}" href="index.php?do=settings&action=edit_paginations&id={$item->id}&cp={$sess}" id="{$item->id}"></a>
				</td>
				<td>
					{if $item->id == 1}
					<span class="topleftDir icon_sprite ico_delete_no"></span>
					{else}
					<a class="topleftDir ConfirmDelete icon_sprite ico_delete" title="{#PAGINATION_DELETE_HINT#}" dir="{#PAGINATION_DELETE_HINT#}" name="{#PAGINATION_DEL_HINT#}" href="index.php?do=settings&action=del_paginations&id={$item->id}&cp={$sess}" id="{$item->id}"></a>
					{/if}
				</td>
			</tr>
			{/foreach}
			{/if}
		</tbody>
	</table>
</div>
{include file="$codemirror_connect"}