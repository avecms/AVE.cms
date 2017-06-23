<script type="text/javascript" language="JavaScript">
$(document).ready(function(){ldelim}

	$(".AddTmpl").click( function(event) {ldelim}
		event.preventDefault();
		var tmpls_name = $('#add_tmpls #tmpls_name').fieldValue();
		var title = '{#RUBRIC_TMPLS_ADD#}';
		var text = '{#RUBRIC_TMPLS_INNAME#}';
		if (tmpls_name == ""){ldelim}
			jAlert(text,title);
		{rdelim}else{ldelim}
			$.alerts._overlay('show');
			$("#add_tmpls").submit();
		{rdelim}
	{rdelim});

	$(".CopyTmpl").click( function(event) {ldelim}
		event.preventDefault();
		var href = $(this).attr('href');
		var title = '{#RUBRIC_TMPLS_COPY#}';
		var text = '{#RUBRIC_TMPLS_COPY_TIP#}';
		jPrompt(text, '', title, function(b){ldelim}
					if (b){ldelim}
						$.alerts._overlay('show');
						window.location = href + '&tmpls_name=' + b;
						{rdelim}else{ldelim}
							$.jGrowl("{#MAIN_NO_ADD_BLOCK#}", {ldelim}theme: 'error'{rdelim});
						{rdelim}
				{rdelim}
			);
	{rdelim});

{rdelim});
</script>

<div class="title">
	<h5>{#RUBRIC_TMPLS_HEAD#}</h5>
	<div class="num">
		<a class="basicNum" href="index.php?do=rubs&action=template&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIK_EDIT_TEMPLATE#}</a>
		&nbsp;
		<a class="basicNum" href="index.php?do=rubs&action=edit&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIK_EDIT#}</a>
		&nbsp;
		{if check_permission('rubric_code')}
		<a class="basicNum" href="index.php?do=rubs&action=code&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIK_EDIT_CODE#}</a>
		{/if}
	</div>
</div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#RUBRIC_TMPLS_TIP#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=rubs&cp={$sess}">{#RUBRIK_SUB_TITLE#}</a></li>
			<li><strong class="code">{$rubric->rubric_title|escape}</strong></li>
			<li>{#RUBRIC_TMPLS_HEAD#}</li>
		</ul>
	</div>
</div>

<div class="widget first">
	<ul class="tabs">
		<li class="activeTab"><a href="#tab1">{#RUBRIC_TMPLS_HEAD#}</a></li>
		{if check_permission('rubric_edit')}<li class=""><a href="#tab2">{#RUBRIC_TMPLS_ADD#}</a></li>{/if}
		<div class="num">
			<a class="basicNum" href="index.php?do=rubs&action=tmpls_from&rubric_id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TMPLS_FROM#}</a>
		</div>
	</ul>
	<div class="tab_container">
		<div id="tab1" class="tab_content" style="display: block;">
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">

					<col width="20">
					<col>
					<col width="200">
					<col width="180">
					<col width="20">
					<col width="20">
					<col width="20">
					<col width="20">

					{if $templates}
					<thead>
					<tr>
						<td>{#RUBRIC_TMPLS_ID#}</td>
						<td>{#RUBRIC_TMPLS_NAME#}</td>
						<td>{#RUBRIC_TMPLS_AUTHOR#}</td>
						<td>{#RUBRIC_TMPLS_DATE#}</td>
						<td align="center"><a href="javascript:void(0);" class="topDir icon_sprite ico_list float" style="cursor: help; display: inline-block" title="{#RUBRIC_TMPLS_COUNT_DOCS#}"></a></td>
						{if check_permission('rubric_edit')}<td colspan="3">{#RUBRIC_TMPLS_ACTIONS#}</td>{/if}
					</tr>
					</thead>
					<tbody>

					{foreach from=$templates item=template}
						<tr id="tr{$template->id}">
							<td align="center">{$template->id}</td>

							<td>
								{if check_permission('rubric_edit')}
								<a class="topDir link" title="{#RUBRIC_TMPLS_EDIT#}" href="index.php?do=rubs&action=tmpls_edit&id={$template->id}&rubric_id={$smarty.request.Id|escape}&cp={$sess}">
									<strong>{$template->title|escape}</strong>
								</a>
								{else}
									<strong>{$template->title|escape}</strong>
								{/if}
							</td>

							<td align="center">{$template->author_id|escape}</td>

							<td align="center">
								<span class="date_text dgrey">{$template->created|date_format:$TIME_FORMAT|pretty_date}</span>
							</td>

							<td>
								<strong class="code">{$template->doc_count}</strong>
							</td>

							{if check_permission('rubric_edit')}
							<td nowrap="nowrap" width="1%" align="center">
								<a class="topleftDir CopyTmpl icon_sprite ico_copy" title="{#RUBRIC_TMPLS_COPY#}" href="index.php?do=rubs&action=tmpls_copy&tmpls_id={$template->id}&rubric_id={$smarty.request.Id|escape}&cp={$sess}"></a>
							</td>

							<td align="center">
								<a class="topleftDir icon_sprite ico_edit" title="{#RUBRIC_TMPLS_EDIT#}" href="index.php?do=rubs&action=tmpls_edit&id={$template->id}&rubric_id={$smarty.request.Id|escape}&cp={$sess}"></a>
							</td>

							<td align="center">
							{if check_permission('rubric_edit')}
								{if $template->doc_count==0}
									<a class="topleftDir ConfirmDelete icon_sprite ico_delete" title="{#RUBRIC_TMPLS_DELETE#}" dir="{#RUBRIC_TMPLS_DELETE#}" name="{#RUBRIC_TMPLS_DELETE_C#}" href="index.php?do=rubs&action=tmpls_del&tmpls_id={$template->id}&rubric_id={$smarty.request.Id|escape}&cp={$sess}" id="{$template->id}"></a>
								{else}
									<span title="{#RUBRIK_USE_DOCUMENTS#}" class="topleftDir icon_sprite ico_delete_no"></span>
								{/if}
							{else}
								<span title="{#RUBRIK_NO_PERMISSION#}" class="topleftDir icon_sprite ico_delete_no"></span>
							{/if}
							</td>
							{/if}
						</tr>
					{/foreach}
					{else}
						<tr class="noborder">
							<td colspan="10">
								<ul class="messages">
									<li class="highlight yellow">{#RUBRIC_TMPLS_NO_ITEMS#}</li>
								</ul>
							</td>
						</tr>
					{/if}
				</tbody>
			</table>
		</div>
		{if check_permission('rubric_edit')}
		<div id="tab2" class="tab_content" style="display: none;">
			<form id="add_tmpls" method="post" action="index.php?do=rubs&action=tmpls_new&rubric_id={$smarty.request.Id|escape}&cp={$sess}" class="mainForm">
				<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic mainForm">
				<col width="300">
				<col>
				<tr>
					<td>{#RUBRIC_TMPLS_NAME#}</td>
					<td><input name="tmpls_name" type="text" id="tmpls_name" value="" placeholder="{#RUBRIC_TMPLS_NAME#}" style="width: 400px"></td>
				</tr>
				<tr>
					<td colspan="2"><input type="button" class="basicBtn AddTmpl" value="{#RUBRIC_TMPLS_ADD#}" /></td>
				</tr>
				</table>
			</form>
		</div>
		{/if}
	</div>
	<div class="fix"></div>
</div>