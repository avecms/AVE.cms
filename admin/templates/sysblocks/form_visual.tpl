<script type="text/javascript">
	$sid = parseInt('{$sid}');
	$sess = '{$sess}';

	var clipboard = new Clipboard('.copyBtn');
</script>

<div class="title">
	<h5>{#SYSBLOCK_INSERT_H#}</h5>
</div>

<div class="widget" style="margin-top: 0px;">
	<div class="body"> {#SYSBLOCK_INSERT#} </div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB">
				<a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a>
			</li>
			<li>
				<a href="index.php?do=sysblocks&cp={$sess}" title="">{#SYSBLOCK_LIST_LINK#}</a>
			</li>
			<li>{if $smarty.request.id != ''}{#SYSBLOCK_EDIT_H#}{else}{#SYSBLOCK_INSERT_H#}{/if}</li>
			<li><strong class="code">{if $smarty.request.id != ''}{$sysblock_name|escape}{else}{$smarty.request.sysblock_name}{/if}</strong></li>
		</ul>
	</div>
</div>

<div class="widgets">
	<table class="first tableButtons" cellpadding="0" cellspacing="0" width="100%" id="sysblocksButtons">
		<colgroup>
			<col width="25%">
			<col width="25%">
			<col width="25%">
			<col width="25%">
		</colgroup>
		<tbody>
		<tr>
			<td>
				<a class="button greyishBtn topBtn" href="index.php?do=sysblocks&cp={$sess}">{#SYSBLOCK_LIST_LINK#}</a>
			</td>
			<td>
				<a class="button greenBtn topBtn" href="index.php?do=sysblocks&action=new&cp={$sess}">{#SYSBLOCK_BUTTON_ADD#}</a>
			</td>
			<td>
				<a class="button basicBtn topBtn" href="index.php?do=sysblocks&action=groups&cp={$sess}">{#SYS_GROUPS#}</a>
			</td>
		</tr>
		</tbody>
	</table>
</div>

<form id="sysblock" action="index.php?do=sysblocks&action=save&cp={$sess}" method="post" class="mainForm">
	<div class="widget first">
		<div class="head">
			<h5 class="iFrames">{if $smarty.request.id != ''}{#SYSBLOCK_EDIT_H#}{else}{#SYSBLOCK_INSERT_H#}{/if}</h5>
		</div>
		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
			<col width="350">
			<col width="300">
			<col width="300">
			<col>
			<tr class="noborder">
				<td><strong>{#SYSBLOCK_NAME#}</strong></td>
				<td colspan="4">
					<div class="pr12">
						<input name="sysblock_name" class="mousetrap" type="text" value="{if $smarty.request.id != ''}{$sysblock_name|escape}{else}{$smarty.request.sysblock_name}{/if}" />
					</div>
				</td>
			</tr>
			<tr>
				<td>{#SYSBLOCK_DESCRIPTION#}</td>
				<td colspan="4">
					<input class="mousetrap" name="sysblock_description" type="text" id="sysblock_description" value="{if $smarty.request.id != ''}{$sysblock_description|escape}{else}{$smarty.request.sysblock_description}{/if}" placeholder="{#SYSBLOCK_DESCRIPTION#}">
				</td>
			</tr>
			<tr>
				<td>
					<div class="nowrap">
						<strong><a class="toprightDir" title="{#SYSBLOCK_I#}">[?]</a></strong> {#SYSBLOCK_ALIAS#}:
					</div>
				</td>
				<td colspan="4">
					<div class="pr12">
						<input type="text" name="sysblock_alias" id="sysblock_alias" value="{if $smarty.request.id != ''}{$sysblock_alias|escape}{else}{$smarty.request.sysblock_alias}{/if}" class="mousetrap" data-accept="{#SYSBLOCK_ACCEPT#}" data-error-syn="{#SYSBLOCK_ER_SYN#}" data-error-exists="{#SYSBLOCK_ER_EXISTS#}" placeholder="{#SYSBLOCK_ALIAS#}" maxlength="20" style="width: 200px;" autocomplete="off" />&nbsp;
						<input type="text" id="sysblock_alias_tag" value="[tag:sysblock:{if $smarty.request.id != ''}{$sysblock_alias|escape}{else}{$smarty.request.sysblock_alias}{/if}]" readonly size="40" class="mousetrap" style="width: 200px;" />
						<a style="text-align: center; padding: 5px 3px 4px 3px;" class="whiteBtn copyBtn" href="javascript:void(0);" data-clipboard-action="copy" data-clipboard-target="#sysblock_alias_tag">
							<img style="margin-top: -3px; position: relative; top: 4px; padding: 0 3px;" class="clippy" src="{$ABS_PATH}admin/templates/images/clippy.svg" width="13">
						</a>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<div class="nowrap">
						Группа
					</div>
				</td>
				<td colspan="4">
					<div class="pr12">
						<select style="width:350px" name="sysblock_group_id" id="group_id" class="mousetrap">
							<option value="" {if $smarty.request.action == 'new' && $smarty.request.id == ''}selected{/if}>{#SYS_GROUP_PLEASE_SELECT#}</option>
							{foreach from=$groups item=group}
								<option value="{$group->id}"{if $sysblock_group_id == $group->id} selected="selected"{/if}>{$group->title|escape}</option>
							{/foreach}
						</select>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					<input type="checkbox" value="1" name="sysblock_eval" class="float mousetrap" {if $sysblock_eval}checked="checked"{/if} /><label>{#SYSBLOCK_EVAL#}</label>
				</td>
				<td>
					<input type="checkbox" value="1" name="sysblock_external" class="float mousetrap" {if $sysblock_external}checked="checked"{/if} /><label>{#SYSBLOCK_EXTERNAL#}</label>
				</td>
				<td>
					<input type="checkbox" value="1" name="sysblock_ajax" class="float mousetrap" {if $sysblock_ajax}checked="checked"{/if} /><label>{#SYSBLOCK_AJAX#}</label>
				</td>
				<td>
					<input type="checkbox" value="1" name="sysblock_visual" class="float mousetrap" {if $sysblock_visual}checked="checked"{/if} /><label>{#SYSBLOCK_VISUAL#}</label>
				</td>
			</tr>
			{if $sysblock_external}
			<tr>
				<td colspan="4">
					<ul class="messages">
						<li class="highlight grey">{#SYSBLOCK_LINK#} <a class="float" href="/?sysblock={$smarty.request.id}" target="_blank">http://{$smarty.server.HTTP_HOST}/?sysblock={$smarty.request.id}</a></li>
					</ul>
				</td>
			</tr>
			{/if}
		</table>
	</div>
	<div class="widget first">
		<div class="head">
			<h5 class="iFrames">{#SYSBLOCK_HTML#}</h5>
		</div>
		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
			<tbody>
				<tr>
					<td>
						{$sysblock_text}
					</td>
				</tr>
			</tbody>
		</table>

		<div class="rowElem" id="saveBtn">
			<div class="saveBtn">
				{if $smarty.request.id != ''}
				<input type="hidden" name="id" value="{$id}">
				<input name="submit" type="submit" class="basicBtn" value="{#SYSBLOCK_SAVEDIT#}" />
				{else}
				<input name="submit" type="submit" class="basicBtn" value="{#SYSBLOCK_SAVE#}" />
				{/if}
			</div>
		</div>

	</div>
</form>
{literal}
<script>
	$(document).on('change', '#sysblock_alias', function (event) {

		var input = $(this);
		var alias = input.val();

		if (alias > '') {
			$.ajax({
				url: 'index.php?do=sysblocks&action=alias&cp=' + $sess,
				data: {
					alias: alias,
					id: $sid
				},
				success: function (data) {
					if (data === '1') {
						$.jGrowl(input.attr('data-accept'), {theme: 'accept'});
					}
					else if (data === 'syn') {
						$.jGrowl(input.attr('data-error-syn'), {theme: 'error'});
						alias = $sid ? $sid : '';
					}
					else {
						$.jGrowl(input.attr('data-error-exists'), {theme: 'error'});
						alias = $sid ? $sid : '';
					}
					$('#sysblock_alias_tag').val('[tag:sysblock:' + alias + ']');
				}
			});
		}
		else {
			alias = $sid ? $sid : '';
			$('#sysblock_alias_tag').val('[tag:sysblock:' + alias + ']');
		}

		return false;
	});
</script>
{/literal}

{if $smarty.request.action != 'new'}
<script language="javascript">

	var sett_options = {ldelim}
		url: 'index.php?do=sysblocks&action=save&cp={$sess}',
		beforeSubmit: Request,
		dataType: 'json',
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

	function SaveAjax () {ldelim}
		if (window.CKEDITOR) for(var instanceName in CKEDITOR.instances) CKEDITOR.instances[instanceName].updateElement();
		{if $smarty.request.action=='edit'}
		$('#sysblock').ajaxSubmit(sett_options);
		{else}
		$('#sysblock').submit();
		{/if}
	{rdelim}

	$(document).ready(function(){ldelim}

		Mousetrap.bind(['ctrl+s', 'command+s'], function(event) {ldelim}
			event.preventDefault();
			if (window.CKEDITOR) for(var instanceName in CKEDITOR.instances) CKEDITOR.instances[instanceName].updateElement();
			SaveAjax();
			return false;
		{rdelim});

		$('.SaveEdit').click(function (event) {ldelim}
			event.preventDefault();
			if (window.CKEDITOR) for(var instanceName in CKEDITOR.instances) CKEDITOR.instances[instanceName].updateElement();
			SaveAjax();
			return false;
		{rdelim});

		{literal}
			window.onload = function(){
				if (window.CKEDITOR) {
					CKEDITOR.on('instanceReady', function (event) {
						event.editor.setKeystroke(CKEDITOR.CTRL + 83 /*S*/, 'savedoc');
					});
				}
			}
		{/literal}

	{rdelim});
</script>
{/if}