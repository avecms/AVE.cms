<script type="text/javascript" language="JavaScript">
$(document).ready(function(){ldelim}

	$rid = parseInt('{$rid}');
	$sess = '{$sess}';

	$('.tabs li > a').on('click', function(){ldelim}
		setTimeout(
			function(){ldelim}
				$('.mainForm select').trigger('refresh');
			{rdelim}
		, 100);

		console.log('Refresh');
	{rdelim});

	{if check_permission('request_edit')}
		$(".AddRequest").click( function(event) {ldelim}
			event.preventDefault();
			var request_title_new = $('#add_request #request_title_new').fieldValue();
			var title = '{#REQUEST_NEW#}';
			var text = '{#REQUEST_ENTER_NAME#}';
			if (request_title_new == ""){ldelim}
				jAlert(text,title);
			{rdelim}else{ldelim}
				$.alerts._overlay('show');
				$("#add_request").submit();
			{rdelim}
		{rdelim});

		$(".CopyRequest").click( function(event) {ldelim}
			event.preventDefault();
			var href = $(this).attr('href');
			var title = '{#REQUEST_COPY#}';
			var text = '{#REQUEST_PLEASE_NAME#}';
			jPrompt(text, '', title, function(b){ldelim}
						if (b){ldelim}
							$.alerts._overlay('show');
							window.location = href + '&cname=' + b;
						{rdelim}
					{rdelim}
				);
		{rdelim});
	{/if}

	var clipboard = new Clipboard('.copyBtn');

{rdelim});
</script>

<div class="title"><h5>{#REQUEST_TITLE#}</h5></div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#REQUEST_TIP#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li>{#REQUEST_TITLE#}</li>
		</ul>
	</div>
</div>

<div class="widget first">
	<ul class="tabs">
		<li class="activeTab"><a href="#tab1">{#REQUEST_ALL#}</a></li>
		{if check_permission('request_edit')}
		<li class=""><a href="#tab2">{#REQUEST_NEW#}</a></li>
		{/if}
	</ul>

		<div class="tab_container">
			<div id="tab1" class="tab_content" style="display: block;">
			<form class="mainForm">
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				{if $items}
				<thead>
					<tr>
						<td width="40">{#REQUEST_ID#}</td>
						<td>{#REQUEST_NAME#}</td>
						<td width="200">{#REQUEST_AUTHOR#}</td>
						<td width="200">{#REQUEST_DATE_CREATE#}</td>
						<td width="200">{#REQUEST_SYSTEM_TAG#}</td>
						<td width="80" colspan="4">{#REQUEST_ACTIONS#}</td>
					</tr>
				</thead>
				<tbody>
					{foreach from=$items item=item}
					<tr>
						<td align="center">{$item->Id}</td>

						<td>
							{if check_permission('request_edit')}
							<a title="{#REQUEST_EDIT#}" href="index.php?do=request&action=edit&Id={$item->Id}&rubric_id={$item->rubric_id}&cp={$sess}" class="topDir link">
								<strong>{$item->request_title|escape}</strong>
							</a>
							{else}
							<strong>{$item->request_title|escape}</strong>
							{/if}
							{if $item->request_description != ''}
							<br>
							{$item->request_description|escape|default:#REQUEST_NO_DESCRIPTION#}
							{/if}
						</td>

						<td align="center">{$item->request_author|escape}</td>

						<td align="center">
							<span class="date_text dgrey">{$item->request_created|date_format:$TIME_FORMAT|pretty_date}</span>
						</td>

						<td>
							<div class="pr12" style="display: table">
								<input style="display: table-cell" readonly type="text" id="shot_{$item->Id}" value="[tag:request:{if $item->request_alias}{$item->request_alias}{else}{$item->Id}{/if}]">
								<a style="display: table-cell; text-align: center" class="whiteBtn copyBtn topDir" href="javascript:void(0);" data-clipboard-action="copy" data-clipboard-target="#shot_{$item->Id}" title="Copy to clipboard">
									<img style="margin-top: -3px; position: relative; top: 4px; padding: 0 3px;" class="clippy" src="{$ABS_PATH}admin/templates/images/clippy.svg" width="13">
								</a>
							</div>
						</td>

						<td width="1%" align="center">
							{if check_permission('request_edit')}
								<a title="{#REQUEST_EDIT#}" href="index.php?do=request&action=edit&Id={$item->Id}&cp={$sess}&rubric_id={$item->rubric_id}" class="topleftDir icon_sprite ico_edit"></a>
							{else}
								<span class="icon_sprite ico_edit_no"></span>
							{/if}
						</td>

						<td width="1%" align="center">
							{if check_permission('request_edit')}
								<a title="{#REQUEST_CONDITION_EDIT#}" data-dialog="conditions-{$item->Id}" data-modal="true" data-title="{#REQUEST_CONDITION#}" href="index.php?do=request&action=conditions&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}&pop=1" class="topleftDir icon_sprite ico_query openDialog"></a>
							{else}
								<span class="icon_sprite ico_query_no"></span>
							{/if}
						</td>

						<td width="1%" align="center">
							{if check_permission('request_edit')}
								<a title="{#REQUEST_COPY#}" href="index.php?do=request&action=copy&Id={$item->Id}&cp={$sess}&rubric_id={$item->rubric_id}" class="CopyRequest topleftDir icon_sprite ico_copy"></a>
							{else}
								<span class="icon_sprite ico_copy_no"></span>
							{/if}
						</td>

						<td width="1%" align="center">
							{if check_permission('request_edit')}
								<a title="{#REQUEST_DELETE#}" dir="{#REQUEST_DELETE#}" name="{#REQUEST_DELETE_CONFIRM#}" href="index.php?do=request&action=delete_query&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}" class="ConfirmDelete topleftDir icon_sprite ico_delete"></a>
							{else}
								<span class="icon_sprite ico_delete_no"></span>
							{/if}
						</td>
					</tr>
					{/foreach}
					{else}
					<tr class="noborder">
						<td colspan="6">
							<ul class="messages">
								<li class="highlight yellow">{#REQUEST_NO_REQUST#}</li>
							</ul>
						</td>
					</tr>
					{/if}
				</tbody>
			</table>
			</form>

			</div>
			{if check_permission('request_edit')}
			<div id="tab2" class="tab_content" style="display: none;">
				<form id="add_request" method="post" action="index.php?do=request&action=new&cp={$sess}" class="mainForm">
					<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">
						<col width="300">
						<col>
						<tr>
							<td>{#REQUEST_NAME3#}</td>
							<td><input name="request_title_new" type="text" id="request_title_new" value="" placeholder="{#REQUEST_NAME#}" style="width: 400px"></td>
						</tr>
						<tr>
							<td>{#REQUEST_DESCRIPTION#}</td>
							<td><input name="request_description" type="text" id="request_description" value="" placeholder="{#REQUEST_DESCRIPTION#}"></td>
						</tr>
						<tr>
							<td>{#REQUEST_SELECT_RUBRIK#}</td>
							<td>
								<select style="width:350px" id="rubric_id" name="rubric_id" class="mousetrap">
									<option value="">{#REQUEST_PLEASE_SELECT#}</option>
									{foreach from=$rubrics item=rubric}
									<option value="{$rubric->Id}">{$rubric->rubric_title|escape}</option>
									{/foreach}
								</select>
							</td>
						</tr>
						<tr>
							<td>
								<div class="nowrap">
									<strong><a class="toprightDir" title="{#REQUEST_I#}">[?]</a></strong> {#REQUEST_ALIAS#}:
								</div>
							</td>
							<td>
								<div class="pr12">
									<input type="text" name="request_alias" id="request_alias" value="" class="mousetrap" data-accept="{#REQUEST_ACCEPT#}" data-error-syn="{#REQUEST_ER_SYN#}" data-error-exists="{#REQUEST_ER_EXISTS#}" placeholder="{#REQUEST_ALIAS#}" maxlength="20" style="width: 200px;" autocomplete="off" />&nbsp;
									<input type="text" id="request_alias_tag" value="[tag:request:]" readonly size="40" class="mousetrap" style="width: 200px;" />
									<a style="text-align: center; padding: 5px 3px 4px 3px;" class="whiteBtn copyBtn" href="javascript:void(0);" data-clipboard-action="copy" data-clipboard-target="#sysblock_alias_tag">
										<img style="margin-top: -3px; position: relative; top: 4px; padding: 0 3px;" class="clippy" src="{$ABS_PATH}admin/templates/images/clippy.svg" width="13">
									</a>
								</div>
							</td>
						</tr>
						<tr>
							<td colspan="2"><input type="button" class="basicBtn AddRequest" value="{#REQUEST_BUTTON_ADD#}" /></td>
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
	$(document).on('change', '#request_alias', function (event) {

		var input = $(this);
		var alias = input.val();

		if (alias > '') {
			$.ajax({
				url: 'index.php?do=request&action=alias&cp=' + $sess,
				data: {
					alias: alias
				},
				success: function (data) {
					if (data === '1') {
						$.jGrowl(input.attr('data-accept'), {theme: 'accept'});
					}
					else if (data === 'syn') {
						$.jGrowl(input.attr('data-error-syn'), {theme: 'error'});
						alias = $rid ? $rid : '';
					}
					else {
						$.jGrowl(input.attr('data-error-exists'), {theme: 'error'});
						alias = $rid ? $rid : '';
					}
					$('#request_alias_tag').val('[tag:request:' + alias + ']');
				}
			});
		}
		else {
			alias = $rid ? $rid : '';
			$('#request_alias_tag').val('[tag:request:' + alias + ']');
		}

		return false;
	});
</script>
{/literal}

{if $page_nav}
	<div class="pagination">
		<ul class="pages">
			{$page_nav}
		</ul>
	</div>
{/if}