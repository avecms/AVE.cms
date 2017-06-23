<div class="title"><h5>{#SETTINGS_LANG_EDIT#}</h5></div>

<div class="widget" style="margin-top: 0px;">
    <div class="body">
		{#SETTINGS_LANG_TITLE#}
    </div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
	    <ul>
	        <li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
	        <li><a href="index.php?do=settings&cp={$sess}">{#SETTINGS_MAIN_TITLE#}</a></li>
			<li>{#SETTINGS_LANG_EDIT#}</li>
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

<div class="widget first">

	<ul class="inact_tabs">
		{if check_permission('gen_settings')}<li><a href="index.php?do=settings&cp={$sess}">{#SETTINGS_MAIN_SETTINGS#}</a></li>{/if}
		{if check_permission('gen_settings_more')}<li><a href="index.php?do=settings&sub=case&cp={$sess}">{#SETTINGS_CASE_TITLE#}</a></li>{/if}
		{if check_permission('gen_settings_countries')}<li><a href="index.php?do=settings&sub=countries&cp={$sess}">{#MAIN_COUNTRY_EDIT#}</a></li>{/if}
		{if check_permission('gen_settings_languages')}<li class="activeTab"><a href="index.php?do=settings&sub=language&cp={$sess}">{#SETTINGS_LANG_EDIT#}</a></li>{/if}
		<li><a href="index.php?do=settings&action=paginations&cp={$sess}">{#SETTINGS_PAGINATION#}</a></li>
	</ul>

<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">
<col width="30" />
<col width="30" />
<col width="70" />
<col width="100" />
<col width="200" />
<col width="100" />
<col />
<col width="20" />
<col width="20" />
<col width="20" />
<thead>
<tr>
	<td>{#SETTINGS_LANG_ID#}</td>
	<td>{#SETTINGS_LANG_FLAG#}</td>
	<td>{#SETTINGS_LANG_SYSTEM#}</td>
	<td>{#SETTINGS_LANG_PREFIX#}</td>
	<td>{#SETTINGS_LANG_NAME#}</td>
	<td>{#SETTINGS_LANG_DEFAULT#}</td>
	<td>&nbsp;</td>
	<td colspan="3">{#SETTINGS_LANG_ACTION#}</td>
</tr>
</thead>
<tbody>
	{foreach from=$language item=lang name=l}
	<tr>
		<td align="center">{$lang.Id}</td>
		<td align="center"><img src="{$ABS_PATH}lib/flags/{$lang.lang_key}.png" /></td>
		<td align="center">{$lang.lang_key}</td>
		<td align="center">{$lang.lang_alias_pref}</td>
		<td>{$lang.lang_name}</td>
		<td align="center">{if $lang.lang_default==1}<span title="" class="topleftDir icon_sprite ico_ok"></span>{/if}</td>
		<td></td>
		<td align="center">
			<a class="topleftDir icon_sprite ico_edit" title="{#SETTINGS_LANG_AEDIT#}" href="javascript:void(0);" onclick="windowOpen('index.php?do=settings&sub=language&func=editlang&Id={$lang.Id}&pop=1&cp={$sess}','800','400','1','settings');"></a>
		</td>
		<td align="center">
		{if $lang.lang_default!=1}
			{if $lang.lang_status==1}
				<a class="topleftDir icon_sprite ico_delete" title="{#SETTINGS_LANG_AOFF#}" dir="{#SETTINGS_LANG_AOFF#}" name="{#SETTINGS_LANG_AOFF#}" href="index.php?do=settings&sub=language&func=off&Id={$lang.Id}&cp={$sess}"></a>
			{else}
				<a class="topleftDir icon_sprite ico_delete_no" title="{#SETTINGS_LANG_AON#}" dir="{#SETTINGS_LANG_AON#}" name="{#SETTINGS_LANG_AON#}" href="index.php?do=settings&sub=language&func=on&Id={$lang.Id}&cp={$sess}"></a>
			{/if}
		{else}
			<span class="icon_sprite ico_blanc"></span>
		{/if}
		</td>
		<td align="center">
			{if $lang.lang_default!=1 && $lang.lang_status==1}
				<a class="topleftDir icon_sprite ico_globus" title="{#SETTINGS_LANG_ADEFAULT#}" dir="{#SETTINGS_LANG_ADEFAULT#}" name="{#SETTINGS_LANG_ADEFAULT_HINT#}" href="index.php?do=settings&sub=language&func=default&Id={$lang.Id}&cp={$sess}" id="{$lang.Id}"></a>
			{else}
				<span class="icon_sprite ico_blanc"></span>
			{/if}
		</td>
	</tr>
	{/foreach}
</tbody>
</table>

<div class="rowElem">
	<input type="submit" class="basicBtn" value="{#SETTINGS_LANG_ADD#}" onclick="windowOpen('index.php?do=settings&sub=editlang&pop=1&cp={$sess}','800','400','1','settings');" />
</div>

</div>