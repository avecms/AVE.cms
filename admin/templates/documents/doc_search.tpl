<form method="get" id="doc_search" action="index.php" class="mainForm">
	<input type="hidden" name="do" value="docs" />
	{if $smarty.request.action}<input type="hidden" name="action" value="{$smarty.request.action}" />
	{/if}{if $smarty.request.target_title}<input type="hidden" name="target_title" value="{$smarty.request.target_title}" />
	{/if}{if $smarty.request.target}<input type="hidden" name="target" value="{$smarty.request.target}" />
	{/if}{if $smarty.request.doc}<input type="hidden" name="doc" value="{$smarty.request.doc}" />
	{/if}{if $smarty.request.document_alias}<input type="hidden" name="document_alias" value="{$smarty.request.document_alias}" />
	{/if}{if $smarty.request.idtitle}<input type="hidden" name="idtitle" value="{$smarty.request.idtitle}" />
	{/if}{if $smarty.request.selurl}<input type="hidden" name="selurl" value="{$smarty.request.selurl}" />
	{/if}{if $smarty.request.selecturl}<input type="hidden" name="selecturl" value="{$smarty.request.selecturl}" />
	{/if}{if $smarty.request.idonly}<input type="hidden" name="idonly" value="{$smarty.request.idonly}" />
	{/if}{if $smarty.request.sort}<input type="hidden" name="sort" value="{$smarty.request.sort}" />
	{/if}{if $smarty.request.pop}<input type="hidden" name="pop" value="{$smarty.request.pop}" />
	{/if}{if $smarty.request.CKEditor}<input type="hidden" name="CKEditor" value="{$smarty.request.CKEditor}" />
	{/if}{if $smarty.request.CKEditorFuncNum}<input type="hidden" name="CKEditorFuncNum" value="{$smarty.request.CKEditorFuncNum}" />
	{/if}{if $smarty.request.langCode}<input type="hidden" name="langCode" value="{$smarty.request.langCode}" />
	{/if}{if $smarty.request.function}<input type="hidden" name="function" value="{$smarty.request.function}" />
	{/if}<input type="hidden" name="TimeSelect" value="1" />

<div class="widget first">
	<div class="head collapsible" id="opened"><h5>{#MAIN_SEARCH_DOCUMENTS#}</h5></div>
	<div style="display: block;">

<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<col width="150">
		<col width="120">
		<col width="160">
		<col>
		<col width="120">
		<col>
		<tr class="noborder">
			<td rowspan="2"><strong>{#MAIN_TIME_PERIOD#}</strong></td>
			<td>
				<div class="pr12"><input id="document_published" name="document_published" type="text" value="{$smarty.request.document_published|date_format:"%d.%m.%Y"}" placeholder="{#MAIN_TIME_START#}" /></div>
			</td>
			<td><strong>{#MAIN_TITLE_SEARCH#}&nbsp;<a href="javascript:void(0);" style="cursor:help;"  class="topDir link" title="{#MAIN_SEARCH_HELP#}">[?]</a></strong></td>
			<td>
				<div class="pr12"><input type="text" name="QueryTitel" value="{$smarty.request.QueryTitel|escape|stripslashes}" placeholder="{#MAIN_TITLE_DOC_NAME#}" /></div>
			</td>
			<td><strong>{#MAIN_SELECT_RUBRIK#}</strong></td>
			<td>
				<select name="rubric_id" id="rubric_id">
					<option value="all">{#MAIN_ALL_RUBRUKS#}</option>
					{foreach from=$rubrics item=rubric}
						<option value="{$rubric->Id}" {if $smarty.request.rubric_id==$rubric->Id}selected{/if}>{$rubric->rubric_title|escape}</option>
					{/foreach}
				</select>
			</td>
		</tr>

		<tr>
			<td>
				<div class="pr12"><input id="document_expire" name="document_expire" type="text" value="{$smarty.request.document_expire|date_format:"%d.%m.%Y"}" placeholder="{#MAIN_TIME_END#}" /></div>
			</td>
			<td><strong>{#MAIN_ID_SEARCH#}</strong></td>
			<td><input style="width:80px" type="text" name="doc_id" value="{$smarty.request.doc_id|escape|stripslashes}" placeholder="{#MAIN_TITLE_DOC_ID#}" /></td>
			<td><strong>{#MAIN_DOCUMENT_STATUS#}</strong></td>
			<td>
				<select name="status">
					<option value="All">{#MAIN_ALL_DOCUMENTS#}</option>
					<option value="Opened" {if $smarty.request.status == 'Opened'}selected{/if}>{#MAIN_DOCUMENT_ACTIVE#}</option>
					<option value="Closed" {if $smarty.request.status == 'Closed'}selected{/if}>{#MAIN_DOCUMENT_INACTIVE#}</option>
					<option value="Deleted" {if $smarty.request.status == 'Deleted'}selected{/if}>{#MAIN_TEMP_DELETE_DOCS#}</option>
				</select>
			</td>
		</tr>

		{if $fields}
		<tr>
			<td>
				<strong>{#DOC_SEARCH_FIELD#}</strong>
			</td>
			<td colspan="2">
				<select name="field_id">
					<option value="">{#DOC_SEARCH_FIELD_SELECT#}</option>
					{foreach from=$fields item=field}
						<option value="{$field->Id}" {if $smarty.request.field_id == $field->Id}selected{/if}>{$field->rubric_field_title|escape}</option>
					{/foreach}
				</select>
			</td>
			<td>
				<select name="field_request">
					<option value="like" {if $smarty.request.field_request == 'like'}selected{/if}>{#DOC_SEARCH_FIELD_LIKE#}</option>
					<option value="eq" {if $smarty.request.field_request == 'eq'}selected{/if}>{#DOC_SEARCH_FIELD_EQ#}</option>
				</select>
			</td>
			<td colspan="2">
				<div class="pr12">
					<input id="" name="field_search" type="text" value="{$smarty.request.field_search|default:""}" placeholder="{#DOC_SEARCH_FIELD_TEXT#}" />
				</div>
			</td>
		</tr>
		{/if}

		<tr>
			<td>
				<strong>{#DOC_SEARCH_PARAM#}</strong>
			</td>
			<td colspan="2">
				<select name="param_id">
					<option value="">{#DOC_SEARCH_PARAM_SELECT#}</option>
					{foreach from=$params item=param}
						<option value="{$param}" {if $smarty.request.param_id == $param}selected{/if}>{$param}</option>
					{/foreach}
				</select>
			</td>
			<td>
				<select name="param_request">
					<option value="like" {if $smarty.request.param_request == 'like'}selected{/if}>{#DOC_SEARCH_PARAM_LIKE#}</option>
					<option value="eq" {if $smarty.request.param_request == 'eq'}selected{/if}>{#DOC_SEARCH_PARAM_EQ#}</option>
				</select>
			</td>
			<td colspan="2">
				<div class="pr12">
					<input id="" name="param_search" type="text" value="{$smarty.request.param_search|default:""}" placeholder="{#DOC_SEARCH_PARAM_TEXT#}" />
				</div>
			</td>
		</tr>


		<tr>
			<td>
				<strong>{#DOC_LANG_ID#}</strong>
			</td>
			<td>
				<select name="lang_id">
				<option value="" {if !$smarty.request.lang_id}selected{/if}>{#DOC_LANG_SELECT#}</option>
				{foreach from=$smarty.session.accept_langs key=lang_id item=lang}
				<option value="{$lang_id}" {if $smarty.request.lang_id == $lang_id}selected{/if}>{$lang_id}</option>
				{/foreach}
				</select>
			</td>
			<td colspan="2"></td>
			<td>
				<strong>{#MAIN_RESULTS_ON_PAGE#}</strong>
			</td>
			<td>
				<select name="limit">
					{section loop=500 name=dl step=50}
						<option value="{$smarty.section.dl.index+50}" {if $smarty.request.limit==$smarty.section.dl.index+50}selected{/if}>{$smarty.section.dl.index+50}</option>
					{/section}
				</select>
			</td>
		</tr>


		<tr>
			<td colspan="6">
				<input type="submit" class="basicBtn" value="{#MAIN_BUTTON_SEARCH#}" />
			</td>
		</tr>

		{if $smarty.request.rubric_id}

		{/if}

	</table>
	<input type="hidden" name="cp" value="{$sess}" />

	</div>
</div>
</form>

<script src="{$ABS_PATH}admin/templates/js/docs.js"></script>

<script type="text/javascript">
{literal}
	$(document).ready(function() {
		AveDocs.init();
		AveDocs.search();
	});
{/literal}
</script>