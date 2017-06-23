	<form action="index.php?do=docs&action=aliases_save&cp={$sess}" method="post" class="mainForm" id="Aliases">
		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
			<col width="20">
			<col>
			<col width="180">
			<col width="120">
			<col width="20">
			<col width="20">
			<thead>
				<tr class="noborder">
					<td><div align="center"><input type="checkbox" id="selall" value="1"></div></td>
					<td>{#DOC_ALIASES_TABL_H_URL#}</td>
					<td>{#DOC_ALIASES_TABL_H_ADD#}</td>
					<td>{#DOC_ALIASES_TABL_H_AUT#}</td>
					<td colspan="2">{#DOC_ACTIONS#}</td>
				</tr>
			</thead>
			<tbody>
			{if $aliases}
				{foreach from=$aliases item=alias}
				<tr>
					<td align="center">
						<input type="checkbox" class="checkbox topDir" name="alias_del[{$alias->id}]" value="1" title="{#DOC_ALIASES_TABL_CHECK#}">
					</td>
					<td>
						<div class="pr12">
							<a href="javascript:void(0);" class="link editable" id="document_alias_{$alias->id}" data-alias-id="{$alias->id}">{$alias->document_alias}</a>
						</div>
					</td>
					<td align="center">
						<span class="date_text dgrey">{$alias->document_alias_changed|date_format:$DATE_FORMAT|pretty_date}</span>
					</td>
					<td align="center">
						{$alias->document_alias_author_name}
					</td>
					<td align="center">
						<a href="/{$alias->document_alias}" class="icon_sprite ico_globus topleftDir" target="_blank" title="{#DOC_ALIASES_GO#}"></a>
					</td>
					<td align="center">
						<a href="javascript:void(0);" class="icon_sprite ico_delete topleftDir delAlias" data-title="{#DOC_ALIASES_DEL_T#}" data-confirm="{#DOC_ALIASES_DEL_C#}" data-alias-id="{$alias->id}" title="{#DOC_ALIASES_BUTT_DEL#}"></a>
					</td>
				</tr>
				{/foreach}
				<tr>
					<td colspan="6">
						<input type="submit" class="basicBtn Save" value="{#DOC_ALIASES_BUTT_SAV#}"/>
						&nbsp;
						<a href="javascript:void(0);" class="button redBtn Close">{#DOC_ALIASES_BUTT_CLO#}</a>
					</td>
				</tr>
			{else}
				<tr>
					<td colspan="6">
						<ul class="messages">
							<li class="highlight yellow">{#DOC_ALIASES_LIST_EMPT#}</li>
						</ul>
					</td>
				</tr>
				<tr>
					<td colspan="6">
						<a href="javascript:void(0);" class="button redBtn Close">{#DOC_ALIASES_BUTT_CLO#}</a>
					</td>
				</tr>
			{/if}
			</tbody>
		</table>
	</form>

<script language="javascript">
	$(function(){ldelim}

		AveAdmin.ajax();

	{rdelim}); // End
</script>