<script type="text/javascript" language="JavaScript">
$(function() {ldelim}
{if check_permission('rubric_edit')}

	// сортировка рубрик
	$('#rubsTbody').tableSortable({ldelim}
		url: 'index.php?do=rubs&action=rubssort&cp={$sess}',
		success: true
	{rdelim});

	$(".AddRub").click( function(e) {ldelim}
		e.preventDefault();
		var user_group = $('#add_rub #rubric_title').fieldValue();
		var title = '{#RUBRIK_NEW#}';
		var text = '{#RUBRIK_ENTER_NAME#}';
		if (user_group == ""){ldelim}
			jAlert(text,title);
		{rdelim}else{ldelim}
			$.alerts._overlay('show');
			$("#add_rub").submit();
		{rdelim}
	{rdelim});

	$(".CopyRub").click( function(e) {ldelim}
		e.preventDefault();
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
{rdelim});
</script>

<div class="title">
	<h5>{#RUBRIK_SUB_TITLE#}</h5>
</div>

<div class="widget" style="margin-top: 0px;">
	<div class="body"> {#RUBRIK_TIP#} </div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB">
				<a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a>
			</li>
			<li>{#RUBRIK_SUB_TITLE#}</li>
		</ul>
	</div>
</div>

<div class="widget first">
	<ul class="tabs">
		<li class="activeTab">
			<a href="#tab1">{#RUBRIK_ALL#}</a>
		</li>
		{if check_permission('rubric_edit')}
		<li class="">
			<a href="#tab2">{#RUBRIK_NEW#}</a>
		</li>
		{/if}
	</ul>
	<div class="tab_container">
		<div id="tab1" class="tab_content" style="display: block;">
			<div class="body">
				<strong>{#RUBRIK_FORMAT#}</strong><br />
				<strong>%d-%m-%Y</strong> - {#RUBRIK_FORMAT_TIME#}<br />
				<strong>%id</strong> - {#RUBRIK_FORMAT_ID#}
			</div>
			<form class="mainForm" id="quickSave" method="post" action="index.php?do=rubs&cp={$sess}&sub=quicksave{if $smarty.request.page!=''}&page={$smarty.request.page|escape}{/if}">
				<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
					<col width="20">
					<col width="20">
					<col width="20">
					<col width="20">
					<col>
					<col width="120">
					<col width="100">
					<col width="40">
					<col width="30">
					<col width="40">
					<col width="40">
					<col width="20">
					<col width="20">
					<col width="20">
					<col width="20">
					<col width="20">
					<thead>
						<tr>
							<td>{#RUBRIK_ID#}</td>
							<td><a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_R_SORT_TIP#}">[?]</a></td>
							<td><a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_META_GEN_TIP#}">[?]</a></td>
							<td><a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_ALIAS_HISTORY_TIP#}">[?]</a></td>
							<td>{#RUBRIK_NAME#}</td>
							<td>{#RUBRIK_URL_PREFIX#}</td>
							<td>{#RUBRIK_TEMPLATE_OUT#}</td>
							<td align="center"><a href="javascript:void(0);" class="topDir icon_sprite ico_list float" style="cursor: help; display: inline-block" title="{#RUBRIK_COUNT_DOCS#}"></a></td>
							<td align="center">
								<div align="center">
									<a href="javascript:void(0);" class="topDir link" style="cursor: help;" title="{#RUBRIK_DOCS_VI#}">[?]</a>
								</div>
							</td>
							<td align="center"><a href="javascript:void(0);" class="topDir icon_sprite ico_lines float" style="cursor: help; display: inline-block" title="{#RUBRIK_COUNT_FIELDS#}"></a></td>
							<td align="center"><a href="javascript:void(0);" class="topDir icon_sprite ico_template float" style="cursor: help; display: inline-block" title="{#RUBRIK_EDIT_TMPLS#}"></a></td>
							<td align="center" colspan="5">{#RUBRIK_ACTION#}</td>
						</tr>
					</thead>
					<tbody id="rubsTbody">
						{foreach from=$rubrics item=rubric}
						<tr data-id="rub_{$rubric->Id}">
							<td align="center">
								{if $rubric->rubric_description}
								<a href="javascript:void(0);" class="toprightDir link" style="cursor: help;" title="{$rubric->rubric_description|escape}"><strong>[{$rubric->Id}]</strong></a>
								{else}
								<strong class="toprightDir link" title="{#RUBRIK_NAME#}: {$rubric->rubric_title|escape}">{$rubric->Id}</strong>
								{/if}
							</td>
							<td align="center">
								{if check_permission('rubric_edit')}
									<span class="icon_sprite topDir ico_move{if $rubrics|@count<2}_no{/if}" title="{#RUBRIK_MOVE#}" style="cursor:move"></span>
								{else}
									<span class="icon_sprite topDir ico_move{if $rubrics|@count<2}_no{/if}" title="{#RUBRIK_MOVE#}"></span>
								{/if}
							</td>
							<td align="center">
								{if check_permission('rubric_edit')}
									<input type="checkbox" value="1" name="rubric_meta_gen[{$rubric->Id}]" {if $rubric->rubric_meta_gen}checked="checked"{/if} />
								{else}
									<input type="checkbox" {if $rubric->rubric_meta_gen}checked="checked"{/if} disabled="disabled" />
								{/if}
							</td>
							<td align="center">
								{if check_permission('rubric_edit')}
									<input type="checkbox" value="1" name="rubric_alias_history[{$rubric->Id}]" {if $rubric->rubric_alias_history}checked="checked"{/if} />
								{else}
									<input type="checkbox" {if $rubric->rubric_alias_history}checked="checked"{/if} disabled="disabled" />
								{/if}
							</td>
							<td>
								{if check_permission('rubric_edit')}
								<div class="pr12">
									<input style="width:100%" class="mousetrap" type="text" name="rubric_title[{$rubric->Id}]" value="{$rubric->rubric_title|escape}" />
								</div>
								{else}
								<strong>{$rubric->rubric_title|escape}</strong>
								{/if}
							</td>
							<td>
								{if check_permission('rubric_edit')}
									<div class="pr12">
										<input style="width:100%" class="mousetrap" type="text" name="rubric_alias[{$rubric->Id}]" value="{$rubric->rubric_alias|escape}" />
									</div>
								{else}
									<div class="pr12">
										<input style="width:100%" class="mousetrap" type="text" name="rubric_alias[{$rubric->Id}]" value="{$rubric->rubric_alias|escape}" disabled="disabled" />
									</div>
								{/if}
							</td>
							<td>
								{if check_permission('rubric_edit')}
									<select name="rubric_template_id[{$rubric->Id}]" style="width: 300px" class="mousetrap">
										{foreach from=$templates item=template}
											<option value="{$template->Id}" {if $template->Id==$rubric->rubric_template_id}selected="selected" {/if}/>{$template->template_title|escape}</option>
										{/foreach}
									</select>
								{else}
									<select name="rubric_template_id[{$rubric->Id}]" style="width: 300px" disabled="disabled">
										{foreach from=$templates item=template}
											{if $template->Id==$rubric->rubric_template_id}<option value="{$template->Id}" selected="selected" />{$template->template_title|escape}</option>{/if}
										{/foreach}
									</select>
								{/if}
							</td>
							<td align="center"><strong class="code">{$rubric->doc_count}</strong></td>
							<td align="center">
								{if check_permission('rubric_edit')}
									<input type="checkbox" name="rubric_docs_active[{$rubric->Id}]" value="1" {if $rubric->rubric_docs_active == 1}checked="checked"{/if}>
								{else}
									<input type="checkbox" name="rubric_docs_active[{$rubric->Id}]" value="1" {if $rubric->rubric_docs_active == 1}checked="checked"{/if} disabled="disabled">
								{/if}
							</td>
							<td align="center"><strong class="code">{$rubric->fld_count}</strong></td>
							<td align="center">
								{if check_permission('rubric_edit')}
									<strong class="code"><a class="topDir" title="{#RUBRIK_EDIT_TMPLS#}" href="index.php?do=rubs&action=tmpls&Id={$rubric->Id}&cp={$sess}">{$rubric->tmpls_count}</a></strong>
								{else}
									<strong class="code">{$rubric->tmpls_count}</strong>
								{/if}
							</td>
							<td align="center">
								{if check_permission('rubric_edit')}
									<a class="topleftDir icon_sprite ico_edit" title="{#RUBRIK_EDIT#}" href="index.php?do=rubs&action=edit&Id={$rubric->Id}&cp={$sess}"></a>
								{else}
									<span title="{#RUBRIK_NO_CHANGE1#}" class="topleftDir icon_sprite ico_edit_no"></span>
								{/if}
							</td>
							<td align="center">
								{if check_permission('rubric_edit')}
									<a class="topleftDir icon_sprite ico_template" title="{#RUBRIK_EDIT_TEMPLATE#}" href="index.php?do=rubs&action=template&Id={$rubric->Id}&cp={$sess}"></a>
								{else}
									<span title="{#RUBRIK_NO_CHANGE2#}" class="topleftDir icon_sprite ico_template_no"></span>
								{/if}
							</td>
							<td align="center">
								{if check_permission('rubric_edit') &&  check_permission('rubric_code')}
									<a class="topleftDir icon_sprite ico_attach" title="{#RUBRIK_EDIT_CODE#}" href="index.php?do=rubs&action=code&Id={$rubric->Id}&cp={$sess}"></a>
								{else}
									<span title="{#RUBRIK_EDIT_CODE_NO#}" class="topleftDir icon_sprite ico_attach"></span>
								{/if}
							</td>
							<td align="center">
								{if check_permission('rubric_edit')}
									<a class="topleftDir icon_sprite ico_copy" title="{#RUBRIK_MULTIPLY#}" href="javascript:void(0);" onclick="windowOpen('index.php?do=rubs&action=multi&Id={$rubric->Id}&pop=1&cp={$sess}','850','500','1','pop')"></a>
								{else}
									<span title="{#RUBRIK_NO_MULTIPLY#}" class="topleftDir icon_sprite ico_copy_no"></span>
								{/if}
							</td>
							<td align="center">
								{if $rubric->Id != 1}
									{if check_permission('rubric_edit')}
										{if $rubric->doc_count==0}
											<a class="topleftDir ConfirmDelete icon_sprite ico_delete" title="{#RUBRIK_DELETE#}" dir="{#RUBRIK_DELETE#}" name="{#RUBRIK_DELETE_CONFIRM#}" href="index.php?do=rubs&action=delete&Id={$rubric->Id}&cp={$sess}"></a>
										{else}
											<span title="{#RUBRIK_USE_DOCUMENTS#}" class="topleftDir icon_sprite ico_delete_no"></span>
										{/if}
									{else}
										<span title="{#RUBRIK_NO_PERMISSION#}" class="topleftDir icon_sprite ico_delete_no"></span>
									{/if}
								{else}
									<span class="topleftDir icon_sprite ico_delete_no"></span>
								{/if}
							</td>
						</tr>
						{/foreach}
					</tbody>
				</table>
				{if check_permission('rubric_edit')}
				<div class="rowElem">
					<input class="basicBtn" type="submit" value="{#RUBRIK_BUTTON_SAVE#}" />
					{#RUBRIK_OR#}
					<input type="submit" class="blackBtn SaveEdit" value="{#RUBRIK_BUTTON_TPL_NEXT#}" />
				</div>
				{/if}
			</form>
		</div>
		{if check_permission('rubric_edit')}
		<div id="tab2" class="tab_content" style="display: none;">
			<form id="add_rub" method="post" action="index.php?do=rubs&action=new&cp={$sess}" class="mainForm">
				<div class="rowElem">
					<label>{#RUBRIK_NAME2#}</label>
					<div class="formRight">
						<input placeholder="{#RUBRIK_NAME#}" name="rubric_title" type="text" id="rubric_title" value="" style="width: 400px">
						&nbsp;
						<input type="button" class="basicBtn AddRub" value="{#RUBRIK_BUTTON_NEW#}" />
					</div>
					<div class="fix"></div>
				</div>
			</form>
		</div>
		{/if}
	</div>
	<div class="fix"></div>
</div>


<script language="Javascript" type="text/javascript">
	var sett_options = {ldelim}
		url: 'index.php?do=rubs&sub=quicksave&ajax=run&cp={$sess}',
		dataType: 'json',
		beforeSubmit: Request,
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

	$(document).ready(function(){ldelim}

		Mousetrap.bind(['ctrl+s', 'command+s'], function(e) {ldelim}
			if (e.preventDefault) {ldelim}
				e.preventDefault();
			{rdelim} else {ldelim}
				// internet explorer
				e.returnValue = false;
			{rdelim}
			$("#quickSave").ajaxSubmit(sett_options);
			return false;
		{rdelim});

		$(".SaveEdit").click(function(e){ldelim}
			if (e.preventDefault) {ldelim}
				e.preventDefault();
			{rdelim} else {ldelim}
				// internet explorer
				e.returnValue = false;
			{rdelim}
			$("#quickSave").ajaxSubmit(sett_options);
			return false;
		{rdelim});

	{rdelim});
</script>


{if $page_nav}
<div class="pagination">
	<ul class="pages">
		{$page_nav}
	</ul>
</div>
{/if}
<br />
<br />
<br />
