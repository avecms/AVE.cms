<div class="title"><h5>{#SETTINGS_CASE_TITLE#}</h5></div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#SETTINGS_SAVE_INFO#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=settings&cp={$sess}">{#SETTINGS_MAIN_TITLE#}</a></li>
			<li>{#SETTINGS_CASE_TITLE#}</li>
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

<form id="settings" name="settings" method="post" action="index.php?do=settings&cp={$sess}&sub=save&more=case" class="mainForm">
<fieldset>
	<div class="widget first">

	<ul class="inact_tabs">
		{if check_permission('gen_settings')}<li><a href="index.php?do=settings&cp={$sess}">{#SETTINGS_MAIN_SETTINGS#}</a></li>{/if}
		{if check_permission('gen_settings_more')}<li class="activeTab"><a href="index.php?do=settings&sub=case&cp={$sess}">{#SETTINGS_CASE_TITLE#}</a></li>{/if}
		{if check_permission('gen_settings_countries')}<li><a href="index.php?do=settings&sub=countries&cp={$sess}">{#MAIN_COUNTRY_EDIT#}</a></li>{/if}
		{if check_permission('gen_settings_languages')}<li><a href="index.php?do=settings&sub=language&cp={$sess}">{#SETTINGS_LANG_EDIT#}</a></li>{/if}
		<li><a href="index.php?do=settings&action=paginations&cp={$sess}">{#SETTINGS_PAGINATION#}</a></li>
	</ul>




</div>
</fieldset>

{foreach from=$CMS_CONFIG item=category key=type}
{assign var="header" value=$type|strtolower}
<div class="widget first">
	<div class="head">
		<h5 class="iFrames">{$smarty.config.$header}</h5>
	</div>
	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<col width="300" />
		<col />
		<thead>
			<tr>
				<td>{#SETTINGS_NAME#}</td>
				<td><div class="pr12">{#SETTINGS_VALUE#}</div></td>
			</tr>
		</thead>
		<tbody>
		{foreach from=$category item=def key=_var}
		<tr>
			<td>
				<strong><small>{$_var}</small></strong><br />
				{$def.DESCR}
			</td>
			<td>
				{if $def.TYPE=="dropdown"}
					<select class="mousetrap" name="GLOB[{$type}][{$_var}]">
						{foreach from=$def.VARIANT item=elem}
						<option value="{$elem}"
							{php}
								echo (constant($this->_tpl_vars['_var']) == $this->_tpl_vars['elem'] ? 'selected' :'' );
							{/php}>{$elem}
						</option>
						{/foreach}
					</select>
				{/if}
				{if $def.TYPE=="string"}
					<input class="mousetrap" name="GLOB[{$type}][{$_var}]" type="text" id="{$_var}" style="width:550px" value="{php} echo(constant($this->_tpl_vars['_var']));{/php}" size="100" autocomplete="off" />
				{/if}
				{if $def.TYPE=="integer"}
					<input class="mousetrap" name="GLOB[{$type}][{$_var}]" type="text" id="{$_var}" style="width:550px" value="{php} echo(constant($this->_tpl_vars['_var']));{/php}" size="100" autocomplete="off" />
				{/if}
				{if $def.TYPE=="bool"}
					<input type="radio" name="GLOB[{$type}][{$_var}]" value="1" {php} echo(constant($this->_tpl_vars['_var']) ? 'checked' : "");{/php} /><label style="cursor: pointer;">{#SETTINGS_YES#}</label>
					<input type="radio" name="GLOB[{$type}][{$_var}]" value="0" {php} echo(constant($this->_tpl_vars['_var']) ? '' : "checked");{/php} /><label style="cursor: pointer;">{#SETTINGS_NO#}</label>
				{/if}
			</td>
		</tr>
		{/foreach}
		</tbody>
	</table>
</div>
{/foreach}


	<div class="rowElem" id="saveBtn">
		<div class="saveBtn">
			<input type="submit" class="basicBtn" value="{#SETTINGS_BUTTON_SAVE#}" />&nbsp;{#SETTINGS_OR#}&nbsp;<input type="submit" class="button blackBtn SaveSettings" value="{#SETTINGS_BUTTON_SAVE_AJAX#}" />
		</div>
	</div>

</form>

<script language="javascript">

$(document).ready(function(){ldelim}

	var sett_options = {ldelim}
		url: 'index.php?do=settings&sub=save&more=case&ajax=1&cp={$sess}',
		dataType: 'json',
		beforeSubmit: Request,
		success: Response
	{rdelim}

	$(".SaveSettings").click(function(e){ldelim}
		e.preventDefault();
		var title = '{#SETTINGS_BUTTON_SAVE#}';
		var confirm = '{#SETTINGS_SAVE_CONFIRM#}';
		jConfirm(
				confirm,
				title,
				function(b){ldelim}
					if (b){ldelim}
						$("#settings").ajaxSubmit(sett_options);
					{rdelim}
				{rdelim}
			);
	{rdelim});

		Mousetrap.bind(['ctrl+s', 'command+s'], function(e) {ldelim}
			if (e.preventDefault) {ldelim}
				e.preventDefault();
			{rdelim} else {ldelim}
				// internet explorer
				e.returnValue = false;
			{rdelim}
			$("#settings").ajaxSubmit(sett_options);
			return false;
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
{include file="$codemirror_connect"}