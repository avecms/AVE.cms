<script type="text/javascript" language="JavaScript">
	$sid = parseInt('{$sid}');
	$sess = '{$sess}';

	$(document).ready(function(){ldelim}

		$(".AddBlock").click( function(event) {ldelim}
			event.preventDefault();
			var user_group = $('#add_block #block_name').fieldValue();
			var title = '{#BLOCK_ADD#}';
			var text = '{#BLOCK_INNAME#}';
			if (user_group == ""){ldelim}
				jAlert(text,title);
			{rdelim}else{ldelim}
				$.alerts._overlay('show');
				$("#add_block").submit();
			{rdelim}
		{rdelim});

		$(".CopyBlock").click( function(event) {ldelim}
			event.preventDefault();
			var href = $(this).attr('href');
			var title = '{#BLOCK_COPY#}';
			var text = '{#BLOCK_COPY_TIP#}';
			jPrompt(text, '', title, function(b){ldelim}
						if (b){ldelim}
							$.alerts._overlay('show');
							window.location = href + '&block_name=' + b;
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
	<h5>{#BLOCK_EDIT#}</h5>
</div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#BLOCK_EDIT_TIP#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li>{#BLOCK_EDIT#}</li>
		</ul>
	</div>
</div>


<div class="widget first">
	<ul class="tabs">
		<li class="activeTab"><a href="#tab1">{#BLOCK_HEAD#}</a></li>
		{if check_permission('blocks_edit')}<li class=""><a href="#tab2">{#BLOCK_ADD#}</a></li>{/if}
	</ul>

		<div class="tab_container">
			<div id="tab1" class="tab_content" style="display: block;">

				<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">

					<col width="20">
					<col>
					<col width="200">
					<col width="180">
					<col width="200">
					<col width="20">
					<col width="20">
					<col width="20">

					{if $vis_blocks}
					<thead>
					<tr>
						<td>{#BLOCK_ID#}</td>
						<td>{#BLOCK_NAME#}</td>
						<td>{#BLOCK_AUTHOR#}</td>
						<td>{#BLOCK_DATE#}</td>
						<td>{#BLOCK_TAG#}</td>
						{if check_permission('blocks_edit')}<td colspan="3">{#BLOCK_ACTIONS#}</td>{/if}
					</tr>
					</thead>
					<tbody>

					{foreach from=$vis_blocks item=block}
						<tr id="tr{$block->id}">
							<td align="center">{$block->id}</td>

							<td>
								{if check_permission('blocks_edit')}
								<a class="topDir link" title="{#BLOCK_EDIT_HINT#}" href="index.php?do=blocks&action=edit&cp={$sess}&id={$block->id}">
									<strong>{$block->block_name|escape}</strong>
								</a>
								{if $block->block_description}
									<br>{$block->block_description|escape}
								{/if}
								{else}
									<strong>{$block->block_name|escape}</strong>
								{/if}
							</td>

							<td align="center">{$block->block_author_id|escape}</td>

							<td align="center">
								<span class="date_text dgrey">{$block->block_created|date_format:$TIME_FORMAT|pretty_date}</span>
							</td>

							<td>
								<div class="pr12" style="display: table">
									<input style="display: table-cell" readonly type="text" id="shot_{$block->id}" value="[tag:block:{if $block->block_alias}{$block->block_alias}{else}{$block->id}{/if}]">
									<a style="display: table-cell; text-align: center" class="whiteBtn copyBtn topDir" href="javascript:void(0);" data-clipboard-action="copy" data-clipboard-target="#shot_{$block->id}" title="Copy to clipboard">
										<img style="margin-top: -3px; position: relative; top: 4px; padding: 0 3px;" class="clippy" src="{$ABS_PATH}admin/templates/images/clippy.svg" width="13">
									</a>
								</div>
							</td>

							{if check_permission('blocks_edit')}
							<td nowrap="nowrap" width="1%" align="center">
								<a class="topleftDir CopyBlock icon_sprite ico_copy" title="{#BLOCK_COPY#}" href="index.php?do=blocks&action=multi&sub=save&id={$block->id}&cp={$sess}"></a>
							</td>

							<td align="center">
								<a class="topleftDir icon_sprite ico_edit" title="{#BLOCK_EDIT_HINT#}" href="index.php?do=blocks&action=edit&cp={$sess}&id={$block->id}"></a>
							</td>

							<td align="center">
								<a class="topleftDir ConfirmDelete icon_sprite ico_delete" title="{#BLOCK_DELETE_HINT#}" dir="{#BLOCK_DELETE_HINT#}" name="{#BLOCK_DEL_HINT#}" href="index.php?do=blocks&action=del&cp={$sess}&id={$block->id}" id="{$block->id}"></a>
							</td>
							{/if}
						</tr>
					{/foreach}
					{else}
						<tr class="noborder">
							<td colspan="10">
								<ul class="messages">
									<li class="highlight yellow">{#BLOCK_NO_ITEMS#}</li>
								</ul>
							</td>
						</tr>
					{/if}
				</tbody>
			</table>
		</div>
			{if check_permission('blocks_edit')}
			<div id="tab2" class="tab_content" style="display: none;">
				<form id="add_block" method="post" action="index.php?do=blocks&action=new&cp={$sess}" class="mainForm">
					<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">
						<col width="300">
						<col>
						<tr>
							<td>{#BLOCK_NAME#}</td>
							<td><input name="block_name" type="text" id="block_name" value="" placeholder="{#BLOCK_NAME#}" style="width: 400px"></td>
						</tr>
						<tr>
							<td>{#BLOCK_DESCRIPTION#}</td>
							<td><input name="block_description" type="text" id="block_description" value="" placeholder="{#BLOCK_DESCRIPTION#}"></td>
						</tr>
						<tr>
							<td>
								<div class="nowrap">
									<strong><a class="toprightDir" title="{#BLOCK_I#}">[?]</a></strong> {#BLOCK_ALIAS#}:
								</div>
							</td>
							<td>
								<div class="pr12">
									<input type="text" name="block_alias" id="block_alias" value="" class="mousetrap" data-accept="{#BLOCK_ACCEPT#}" data-error-syn="{#BLOCK_ER_SYN#}" data-error-exists="{#BLOCK_ER_EXISTS#}" placeholder="{#BLOCK_ALIAS#}" maxlength="20" style="width: 200px;" />&nbsp;
									<input type="text" id="block_alias_tag" value="[tag:block:]" readonly size="40" class="mousetrap" style="width: 200px;" />
									<a style="text-align: center; padding: 5px 3px 4px 3px;" class="whiteBtn copyBtn" href="javascript:void(0);" data-clipboard-action="copy" data-clipboard-target="#block_alias_tag">
										<img style="margin-top: -3px; position: relative; top: 4px; padding: 0 3px;" class="clippy" src="{$ABS_PATH}admin/templates/images/clippy.svg" width="13">
									</a>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2"><input type="button" class="basicBtn AddBlock" value="{#BLOCK_ADD_BUTTON#}" /></td>
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
	$(document).on('change', '#block_alias', function (event) {

		var input = $(this);
		var alias = input.val();

		if (alias > '') {
			$.ajax({
				url: 'index.php?do=blocks&action=alias&cp=' + $sess,
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
					$('#block_alias_tag').val('[tag:block:' + alias + ']');
				}
			});
		}
		else {
			alias = $sid ? $sid : '';
			$('#block_alias_tag').val('[tag:block:' + alias + ']');
		}

		return false;
	});
</script>
{/literal}