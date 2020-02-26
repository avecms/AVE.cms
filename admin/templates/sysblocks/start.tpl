<script type="text/javascript" language="JavaScript">
	$sess = '{$sess}';

	$(document).ready(function() {ldelim}
		var clipboard = new Clipboard('.copyBtn');
	{rdelim});
</script>

<div class="title">
	<h5>{#SYSBLOCK_EDIT#}</h5>
</div>

<div class="widget" style="margin-top: 0;">
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


{foreach from=$groups item=group}
	{assign var="group_id" value=$group.id}

	{if $group_id == null}
		{assign var="group_id" value='0'}
	{/if}

	<div class="widget first">
		<div class="head closed active">
			<h5 class="iFrames">{if $group.title}{$group.title}{else}{#SYS_GROUP_NO_TITLE#}{/if} ({$group.count})</h5>
		</div>
		<div style="display: block;">

			<div class="body">
				{if $group.description}{$group.description}{else}{#SYS_GROUP_NO_DESCRIPTION#}{/if}
			</div>

			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">

				<col width="20">
				<col width="20">
				<col width="20">
				<col width="20">
				<col>
				<col width="200">
				<col width="180">
				<col width="200">
				<col width="200">
				<col width="20">
				<col width="20">
				<col width="20">

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

				{foreach from=$sysblocks.$group_id item=sysblock}
					<tr id="tr{$sysblock.id}">
						<td align="center">
							{$sysblock.id}
						</td>
						<td align="center">
							{if $sysblock.sysblock_external}<a class="icon_sprite ico_globus topDir" title="{#SYSBLOCK_EXTERNAL_GO#}" href="http://{$smarty.server.HTTP_HOST}/?sysblock={if $sysblock.sysblock_alias}{$sysblock.sysblock_alias}{else}{$sysblock.id}{/if}" target="_blank"></a>{else}<span class="icon_sprite ico_globus_no"></span>{/if}
						</td>
						<td>
							<span class="icon_sprite {if $sysblock.sysblock_ajax}ico_ok_green{else}ico_delete_no{/if}"></span>
						</td>
						<td>
							<span class="icon_sprite {if $sysblock.sysblock_visual}ico_ok_green{else}ico_delete_no{/if}"></span>
						</td>

						<td>
							{if check_permission('sysblocks_edit')}
								<a class="topDir link" title="{#SYSBLOCK_EDIT_HINT#}" href="index.php?do=sysblocks&action=edit&cp={$sess}&id={$sysblock.id}">
									<strong>{$sysblock.sysblock_name|escape}</strong>
								</a>
								{if $sysblock.sysblock_description}
									<br>{$sysblock.sysblock_description|escape}
								{/if}
							{else}
								<strong>{$sysblock.sysblock_name|escape}</strong>
							{/if}
						</td>

						<td align="center">
							{$sysblock.author|escape}
						</td>

						<td align="center">
							<span class="date_text dgrey">{$sysblock.sysblock_created|date_format:$TIME_FORMAT|pretty_date}</span>
						</td>

						<td>
							<div class="pr12" style="display: table">
								<input style="display: table-cell" readonly type="text" id="shot_{$sysblock.id}" value="[tag:sysblock:{if $sysblock.sysblock_alias}{$sysblock.sysblock_alias}{else}{$sysblock.id}{/if}]">
								<a style="display: table-cell; text-align: center" class="whiteBtn copyBtn topDir" href="javascript:void(0);" data-clipboard-action="copy" data-clipboard-target="#shot_{$sysblock.id}" title="Copy to clipboard">
									<img style="margin-top: -3px; position: relative; top: 4px; padding: 0 3px;" class="clippy" src="{$ABS_PATH}admin/templates/images/clippy.svg" width="13">
								</a>
							</div>
						</td>

						{if check_permission('sysblocks_edit')}
							<td nowrap="nowrap" width="1%" align="center">
								<a class="topleftDir CopyBlock icon_sprite ico_copy" title="{#SYSBLOCK_COPY#}" href="index.php?do=sysblocks&action=multi&sub=save&id={$sysblock.id}&cp={$sess}"></a>
							</td>

							<td align="center">
								<a class="topleftDir icon_sprite ico_edit" title="{#SYSBLOCK_EDIT_HINT#}" href="index.php?do=sysblocks&action=edit&cp={$sess}&id={$sysblock.id}"></a>
							</td>

							<td align="center">
								<a class="topleftDir ConfirmDelete icon_sprite ico_delete" title="{#SYSBLOCK_DELETE_HINT#}" dir="{#SYSBLOCK_DELETE_HINT#}" name="{#SYSBLOCK_DEL_HINT#}" href="index.php?do=sysblocks&action=del&cp={$sess}&id={$sysblock.id}" id="{$sysblock.id}"></a>
							</td>
						{/if}
					</tr>
					{foreachelse}
					<tr class="noborder">
						<td colspan="12">
							<ul class="messages">
								<li class="highlight yellow">{#SYSBLOCK_NO_ITEMS#}</li>
							</ul>
						</td>
					</tr>
				{/foreach}

				</tbody>
			</table>
			<div class="fix"></div>
		</div>
	</div>
{/foreach}


{literal}
<script>

</script>
{/literal}