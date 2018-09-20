<script type="text/javascript" language="JavaScript">
	$sid = parseInt('{$sid}');
	$sess = '{$sess}';

	$(document).ready(function(){ldelim}

		$(".AddSysBlock").click( function(event) {ldelim}
			event.preventDefault();
			var user_group = $('#add_sysblock #sysblock_name').fieldValue();
			var title = '{#SYSBLOCK_ADD#}';
			var text = '{#SYSBLOCK_INNAME#}';
			if (user_group == ""){ldelim}
				jAlert(text,title);
			{rdelim}else{ldelim}
				$.alerts._overlay('show');
				$("#add_sysblock").submit();
			{rdelim}
		{rdelim});

		$(".CopyBlock").click( function(event) {ldelim}
			event.preventDefault();
			var href = $(this).attr('href');
			var title = '{#SYSBLOCK_COPY#}';
			var text = '{#SYSBLOCK_COPY_TIP#}';
			jPrompt(text, '', title, function(b){ldelim}
						if (b){ldelim}
							$.alerts._overlay('show');
							window.location = href + '&sysblock_name=' + b;
							{rdelim}else{ldelim}
								$.jGrowl("{#MAIN_NO_ADD_BLOCK#}", {ldelim}theme: 'error'{rdelim});
							{rdelim}
					{rdelim}
				);
		{rdelim});

		var clipboard = new Clipboard('.copyBtn');

	{rdelim});
</script>

<div class="title">
	<h5>{#SYSBLOCK_EDIT#}</h5>
</div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#SYSBLOCK_EDIT_TIP#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li>{#SYSBLOCK_EDIT#}</li>
		</ul>
	</div>
</div>


<div class="widget first">
	<ul class="tabs">
		<li class="activeTab"><a href="#tab1">{#SYSBLOCK_HEAD#}</a></li>
		{if check_permission('sysblocks_edit')}<li class=""><a href="#tab2">{#SYSBLOCK_ADD#}</a></li>{/if}
	</ul>

		<div class="tab_container">
			<div id="tab1" class="tab_content" style="display: block;">

				<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">

					<col width="20">
					<col width="20">
					<col width="20">
					<col width="20">
					<col>
					<col width="200">
					<col width="180">
					<col width="200">
					<col width="20">
					<col width="20">
					<col width="20">

					{if $sys_blocks}
					<thead>
					<tr>
						<td>{#SYSBLOCK_ID#}</td>
						<td><a href="javascript:void(0);" class="toprightDir link" style="cursor: help;" title="{#SYSBLOCK_EXTERNAL_H#}">[?]</a></td>
						<td><a href="javascript:void(0);" class="toprightDir link" style="cursor: help;" title="{#SYSBLOCK_AJAX_H#}">[?]</a></td>
						<td><a href="javascript:void(0);" class="toprightDir link" style="cursor: help;" title="{#SYSBLOCK_VISUAL_H#}">[?]</a></td>
						<td>{#SYSBLOCK_NAME#}</td>
						<td>{#SYSBLOCK_AUTHOR#}</td>
						<td>{#SYSBLOCK_DATE#}</td>
						<td>{#SYSBLOCK_TAG#}</td>
						{if check_permission('sysblocks_edit')}<td colspan="3">{#SYSBLOCK_ACTIONS#}</td>{/if}
					</tr>
					</thead>
					<tbody>

					{foreach from=$sys_blocks item=sysblock}
						<tr id="tr{$sysblock->id}">
							<td align="center">{$sysblock->id}</td>
							<td align="center">{if $sysblock->sysblock_external}<a class="icon_sprite ico_globus topDir" title="{#SYSBLOCK_EXTERNAL_GO#}" href="http://{$smarty.server.HTTP_HOST}/?sysblock={if $sysblock->sysblock_alias}{$sysblock->sysblock_alias}{else}{$sysblock->id}{/if}" target="_blank"></a>{else}<span class="icon_sprite ico_globus_no"></span>{/if}</td>
							<td><span class="icon_sprite {if $sysblock->sysblock_ajax}ico_ok_green{else}ico_delete_no{/if}"></span></td>
							<td><span class="icon_sprite {if $sysblock->sysblock_visual}ico_ok_green{else}ico_delete_no{/if}"></span></td>

							<td>
								{if check_permission('sysblocks_edit')}
								<a class="topDir link" title="{#SYSBLOCK_EDIT_HINT#}" href="index.php?do=sysblocks&action=edit&cp={$sess}&id={$sysblock->id}">
									<strong>{$sysblock->sysblock_name|escape}</strong>
								</a>
								{if $sysblock->sysblock_description}
									<br>{$sysblock->sysblock_description|escape}
								{/if}
								{else}
									<strong>{$sysblock->sysblock_name|escape}</strong>
								{/if}
							</td>

							<td align="center">{$sysblock->sysblock_author_id|escape}</td>

							<td align="center">
								<span class="date_text dgrey">{$sysblock->sysblock_created|date_format:$TIME_FORMAT|pretty_date}</span>
							</td>

							<td>
								<div class="pr12" style="display: table">
									<input style="display: table-cell" readonly type="text" id="shot_{$sysblock->id}" value="[tag:sysblock:{if $sysblock->sysblock_alias}{$sysblock->sysblock_alias}{else}{$sysblock->id}{/if}]">
									<a style="display: table-cell; text-align: center" class="whiteBtn copyBtn topDir" href="javascript:void(0);" data-clipboard-action="copy" data-clipboard-target="#shot_{$sysblock->id}" title="Copy to clipboard">
										<img style="margin-top: -3px; position: relative; top: 4px; padding: 0 3px;" class="clippy" src="{$ABS_PATH}admin/templates/images/clippy.svg" width="13">
									</a>
								</div>
							</td>

							{if check_permission('sysblocks_edit')}
							<td nowrap="nowrap" width="1%" align="center">
								<a class="topleftDir CopyBlock icon_sprite ico_copy" title="{#SYSBLOCK_COPY#}" href="index.php?do=sysblocks&action=multi&sub=save&id={$sysblock->id}&cp={$sess}"></a>
							</td>

							<td align="center">
								<a class="topleftDir icon_sprite ico_edit" title="{#SYSBLOCK_EDIT_HINT#}" href="index.php?do=sysblocks&action=edit&cp={$sess}&id={$sysblock->id}"></a>
							</td>

							<td align="center">
								<a class="topleftDir ConfirmDelete icon_sprite ico_delete" title="{#SYSBLOCK_DELETE_HINT#}" dir="{#SYSBLOCK_DELETE_HINT#}" name="{#SYSBLOCK_DEL_HINT#}" href="index.php?do=sysblocks&action=del&cp={$sess}&id={$sysblock->id}" id="{$sysblock->id}"></a>
							</td>
							{/if}
						</tr>
					{/foreach}
					{else}
						<tr class="noborder">
							<td colspan="10">
								<ul class="messages">
									<li class="highlight yellow">{#SYSBLOCK_NO_ITEMS#}</li>
								</ul>
							</td>
						</tr>
					{/if}
				</tbody>
			</table>
		</div>
			{if check_permission('sysblocks_edit')}
			<div id="tab2" class="tab_content" style="display: none;">
				<form id="add_sysblock" method="post" action="index.php?do=sysblocks&action=new&cp={$sess}" class="mainForm">
					<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">
						<col width="300">
						<col>
						<tr>
							<td>{#SYSBLOCK_NAME#}</td>
							<td><input name="sysblock_name" type="text" id="sysblock_name" value="" placeholder="{#SYSBLOCK_NAME#}" style="width: 400px"></td>
						</tr>
						<tr>
							<td>{#SYSBLOCK_DESCRIPTION#}</td>
							<td><input name="sysblock_description" type="text" id="sysblock_description" value="" placeholder="{#SYSBLOCK_DESCRIPTION#}"></td>
						</tr>
						<tr>
							<td>
								<div class="nowrap">
									<strong><a class="toprightDir" title="{#SYSBLOCK_I#}">[?]</a></strong> {#SYSBLOCK_ALIAS#}:
								</div>
							</td>
							<td>
								<div class="pr12">
									<input type="text" name="sysblock_alias" id="sysblock_alias" value="" class="mousetrap" data-accept="{#SYSBLOCK_ACCEPT#}" data-error-syn="{#SYSBLOCK_ER_SYN#}" data-error-exists="{#SYSBLOCK_ER_EXISTS#}" placeholder="{#SYSBLOCK_ALIAS#}" maxlength="20" style="width: 200px;" />&nbsp;
									<input type="text" id="sysblock_alias_tag" value="[tag:sysblock:]" readonly size="40" class="mousetrap" style="width: 200px;" />
									<a style="text-align: center; padding: 5px 3px 4px 3px;" class="whiteBtn copyBtn" href="javascript:void(0);" data-clipboard-action="copy" data-clipboard-target="#sysblock_alias_tag">
										<img style="margin-top: -3px; position: relative; top: 4px; padding: 0 3px;" class="clippy" src="{$ABS_PATH}admin/templates/images/clippy.svg" width="13">
									</a>
								</div>
							</td>
						</tr>
						<tr>
							<td>{#SYSBLOCK_VISUAL#}</td>
							<td><input type="checkbox" value="1" name="sysblock_visual" /></td>
						</tr>
						<tr>
							<td colspan="2"><input type="button" class="basicBtn AddSysBlock" value="{#SYSBLOCK_ADD_BUTTON#}" /></td>
						</tr>
					</table>
				</form>
			</div>
			{/if}
		</div>
	<div class="fix"></div>
</div>

{literal}
<script>
	$(document).on('change', '#sysblock_alias', function (event) {

		var input = $(this);
		var alias = input.val();

		if (alias > '') {
			$.ajax({
				url: 'index.php?do=sysblocks&action=alias&cp=' + $sess,
				data: {
					alias: alias
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