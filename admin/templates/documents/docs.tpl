<div class="title"><h5>{#DOC_SUB_TITLE#}</h5></div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#DOC_TIPS#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li>{#DOC_SUB_TITLE#}</li>
		</ul>
	</div>
</div>

{if check_permission('document_view')}

<div class="widget first">
<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
	<col width="50%">
	<col width="50%">
	<thead>
	<tr>
		<td>{#MAIN_ADD_IN_RUB#}</td>
		<td>{#MAIN_SORT_DOCUMENTS#}</td>
	</tr>
	</thead>
	<tbody>
	<tr>
		<td style="padding:8px;">
			<form action="index.php" method="get" id="add_docum" class="mainForm">
				<input type="hidden" name="cp" value="{$sess}" />
				<input type="hidden" name="do" value="docs" />
				<input type="hidden" name="action" value="new" />
				<select name="rubric_id" id="DocName" style="width: 250px;">
					<option value="">{#DOC_CHOSE_RUB#}</option>
					{foreach from=$rubrics item=rubric}
						{if $rubric->Show==1}
							<option value="{$rubric->Id}"{if $smarty.request.rubric_id==$rubric->Id} selected{/if}>{$rubric->rubric_title|escape}</option>
						{/if}
					{/foreach}
				</select>
				&nbsp;
				<input style="width:85px" type="submit" class="basicBtn AddDocs" value="{#MAIN_BUTTON_ADD#}" />
			</form>
		</td>

		<td style="padding:8px;">
			<form action="index.php" method="get" class="mainForm">
				<input type="hidden" name="cp" value="{$sess}" />
				<input type="hidden" name="do" value="docs" />
				<select name="rubric_id" id="RubrikSort" style="width: 250px;">
					<option value="all">{#MAIN_ALL_RUBRUKS#}</option>
					{foreach from=$rubrics item=rubric}
						{if $rubric->Show==1}
							<option value="{$rubric->Id}"{if $smarty.request.rubric_id==$rubric->Id} selected{/if}>{$rubric->rubric_title|escape}</option>
						{/if}
					{/foreach}
				</select>
				&nbsp;
				<input style="width:85px" type="submit" class="basicBtn" value="{#MAIN_BUTTON_SORT#}" />
			</form>
		</td>
	</tr>
	</tbody>
</table>
</div>
{/if}


{include file='documents/doc_search.tpl'}

<div class="widget first">
<div class="head">
	<h5 class="iFrames">{#MAIN_DOCUMENTS_ALL#}</h5>
	<div class="num">
		<a class="basicNum" href="index.php?do=docs&action=aliases&cp={$sess}">{#DOC_ALIASES#}</a>
	</div>
</div>
<form class="mainForm" method="post" action="index.php?do=docs&action=editstatus&cp={$sess}">
	<div class="body">
		<strong>{#DOC_SORT_TEXT#}</strong>

		<span class="mrl5">
		{if $smarty.request.sort=='id'}<span class="arrow">&uarr;</span>{elseif $smarty.request.sort=='id_desc'}<span class="arrow">&darr;</span>{/if}
		<a class="link" href="{$link}&sort=id{if $smarty.request.sort=='id'}_desc{/if}&page={$smarty.request.page|escape|default:'1'}&cp={$sess}">{#DOC_ID#}</a>
		</span>

		<span class="mrl5">
		{if $smarty.request.sort=='title'}<span class="arrow">&uarr;</span>{elseif $smarty.request.sort=='title_desc'}<span class="arrow">&darr;</span>{/if}
		<a class="link" href="{$link}&sort=title{if $smarty.request.sort=='title'}_desc{/if}&page={$smarty.request.page|escape|default:'1'}&cp={$sess}">{#DOC_TITLE#}</a>
		</span>

		<span class="mrl5">
		{if $smarty.request.sort=='alias'}<span class="arrow">&uarr;</span>{elseif $smarty.request.sort=='alias_desc'}<span class="arrow">&darr;</span>{/if}
		<a class="link" href="{$link}&sort=alias{if $smarty.request.sort=='alias'}_desc{/if}&page={$smarty.request.page|escape|default:'1'}&cp={$sess}">{#DOC_URL_RUB#}</a>
		</span>

		<span class="mrl5">
		{if $smarty.request.sort=='rubric'}<span class="arrow">&uarr;</span>{elseif $smarty.request.sort=='rubric_desc'}<span class="arrow">&darr;</span>{/if}
		<a class="link" href="{$link}&sort=rubric{if $smarty.request.sort=='rubric'}_desc{/if}&page={$smarty.request.page|escape|default:'1'}&cp={$sess}">{#DOC_IN_RUBRIK#}</a>
		</span>

		<span class="mrl5">
		{if $smarty.request.sort=='published'}<span class="arrow">&uarr;</span>{elseif $smarty.request.sort=='published_desc'}<span class="arrow">&darr;</span>{/if}
		<a class="link" href="{$link}&sort=published{if $smarty.request.sort=='published'}_desc{/if}&page={$smarty.request.page|escape|default:'1'}&cp={$sess}">{#DOC_CREATED#}</a>
		</span>

		<span class="mrl5">
		{if $smarty.request.sort=='changed'}<span class="arrow">&uarr;</span>{elseif $smarty.request.sort=='changed_desc'}<span class="arrow">&darr;</span>{/if}
		<a class="link" href="{$link}&sort=changed{if $smarty.request.sort=='changed' || !$smarty.request.sort}_desc{/if}&page={$smarty.request.page|escape|default:'1'}&cp={$sess}">{#DOC_EDIT#}</a>
		</span>

		<span class="mrl5">
		{if $smarty.request.sort=='author'}<span class="arrow">&uarr;</span>{elseif $smarty.request.sort=='author_desc'}<span class="arrow">&darr;</span>{/if}
		<a class="link" href="{$link}&sort=author{if $smarty.request.sort=='author'}_desc{/if}&page={$smarty.request.page|escape|default:'1'}&cp={$sess}">{#DOC_AUTHOR#}</a>
		</span>

		<span class="mrl5">
		{if $smarty.request.sort=='lang'}<span class="arrow">&uarr;</span>{elseif $smarty.request.sort=='lang_desc'}<span class="arrow">&darr;</span>{/if}
			<a class="link" href="{$link}&sort=lang{if $smarty.request.sort=='lang'}_desc{/if}&page={$smarty.request.page|escape|default:'1'}&cp={$sess}">{#DOC_LANG#}</a>
		</span>
	</div>

<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic" id="docs">
	<col width="10">
	<col width="10">
	<col>
	<col width="150">
	<col width="180">
	{if !$smarty.const.ADMIN_EDITMENU}<col width="141">{/if}

	{if $docs}
	<thead>
	<tr>
		<td><div align="center"><input type="checkbox" id="selall" value="1" /></div></td>
		<td>{#DOC_ID#}</td>
		<td nowrap="nowrap">
			{#DOC_TITLE#}&nbsp;|&nbsp;{#DOC_URL_RUB#}
		</td>
		<td>{#DOC_IN_RUBRIK#}</td>
		<td>{#DOC_CREATED#}&nbsp;|&nbsp;{#DOC_EDIT#}</td>
		{if !$smarty.const.ADMIN_EDITMENU}<td {if $smarty.const.ADMIN_EDITMENU}colspan="7"{else}colspan="14"{/if} align="center">{#DOC_ACTIONS#}</td>{/if}
	</tr>
	</thead>
	{/if}

	<tbody>
	{if $docs}
	{foreach from=$docs item=item}
		<tr {if $item->document_deleted==1}class="red"{/if}{if $item->document_status!=1}class="yellow"{/if}>
			<td nowrap="nowrap"><input name="document[{$item->Id}]" type="checkbox" value="1" {if ($item->cantEdit!=1 || $item->canOpenClose!=1 || $item->canEndDel!=1) && ($item->Id == 1 || $item->Id == $PAGE_NOT_FOUND_ID)}disabled{/if} class="checkbox" /></td>
			<td align="center" nowrap="nowrap"><strong><a class="toprightDir" title="{#DOC_SHOW_TITLE#}" href="../{if $item->Id!=1}index.php?id={$item->Id}&cp={$sess}{/if}" target="_blank">{$item->Id}</a></strong></td>

			<td>
				<div class="docaction">
				{if $item->cantEdit==1}

					{if $item->rubric_admin_teaser_template != ""}
						{$item->rubric_admin_teaser_template}
					{else}
					<strong>
						<a class="toprightDir docname" title="{#DOC_EDIT_TITLE#}" href="index.php?do=docs&action=edit&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}">
							{if $item->document_breadcrum_title != ""}
								{$item->document_breadcrum_title|stripslashes}{elseif $item->document_title != ""}{$item->document_title|stripslashes}{else}{#DOC_SHOW3_TITLE#}
							{/if}
						</a>
					</strong>
					<br />
					<!-- npop *** https://forum.ave-cms.ru/viewtopic.php?p=1857#p1857 -->
					<img src="{$ABS_PATH}lib/flags/{$item->document_lang}.png" width="16" alt="{$item->document_lang}">
					<span class="code">url:</span>&nbsp;
					<a class="toprightDir" title="{#DOC_SHOW2_TITLE#}" href="../{if $item->Id!=1}{$item->document_alias}{/if}" target="_blank">
						<span class="dgrey doclink">{$item->document_alias}</span>
					</a>
					&nbsp;|&nbsp;
					<span class="dgrey">{#DOC_CLICKS#}: </span> <strong class="code">{$item->document_count_view}</strong>
					{/if}

					<div class="actions" style="display: none;">

					{if $smarty.const.ADMIN_EDITMENU}

						<!-- Редактировать -->
						{if $item->cantEdit==1}
							<a class="topDir floatleft" title="{#DOC_EDIT_TITLE#}" href="index.php?do=docs&action=edit&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}">
								<span class="icon_sprite_doc icon_edit"></span>
							</a>
						{/if}

						<!-- Копировать -->
						{if $item->cantEdit==1 && $item->Id != 1 && $item->Id != $PAGE_NOT_FOUND_ID}
							<a class="topDir CopyDocs floatleft" title="{#DOC_COPY#}" href="index.php?do=docs&action=copy&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}">
								<span class="icon_sprite_doc icon_copy"></span>
							</a>
						{/if}

						<!-- Заметки -->
						{if check_permission("remark_view")}
							{if $item->ist_remark=='0'}
							<a class="topDir floatleft" title="{#DOC_CREATE_NOTICE_TITLE#}" href="javascript:void(0);" onclick="windowOpen('index.php?do=docs&action=remark&Id={$item->Id}&pop=1&cp={$sess}','800','700','1','docs');">
								<span class="icon_sprite_doc icon_comment"></span>
							</a>
							{else}
							<a class="topDir floatleft" title="{#DOC_CREATE_NOTICE_TITLE#}" href="javascript:void(0);" onclick="windowOpen('index.php?do=docs&action=remark_reply&Id={$item->Id}&pop=1&cp={$sess}','800','700','1','docs');">
								<span class="icon_sprite_doc icon_comment"></span>
							</a>
							{/if}
						{/if}

						<!-- Публикация -->
						{if $item->document_deleted!=1}
							{if $item->document_status==1}
								{if $item->canOpenClose==1 && $item->Id != 1 && $item->Id != $PAGE_NOT_FOUND_ID}
									<a class="topDir floatleft" title="{#DOC_DISABLE_TITLE#}" href="index.php?do=docs&action=close&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}">
										<span class="icon_sprite_doc icon_public_on"></span>
									</a>
								{/if}
							{else}
								{if $item->canOpenClose==1}
									<a class="topDir floatleft public" title="{#DOC_ENABLE_TITLE#}" href="index.php?do=docs&action=open&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}">
										<span class="icon_sprite_doc icon_public"></span>
									</a>
								{/if}
							{/if}
						{/if}

						<!-- Корзина -->
						{if $item->document_deleted==1}
							<a class="topDir floatleft recylce" title="{#DOC_RESTORE_DELETE#}" href="index.php?do=docs&action=redelete&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}">
								<span class="icon_sprite_doc icon_recylce_on "></span>
							</a>
						{else}
							{if $item->canDelete==1}
							<a class="ConfirmRecycle topDir floatleft" title="{#DOC_TEMPORARY_DELETE#}"  href="index.php?do=docs&action=delete&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}">
								<span class="icon_sprite_doc icon_recylce"></span>
							</a>
							{/if}
						{/if}

						<!-- Удалить -->
						{if $item->canEndDel==1 && $item->Id != 1 && $item->Id != $PAGE_NOT_FOUND_ID}
							<a class="ConfirmDelete topDir" title="{#DOC_FINAL_DELETE#}" dir="{#DOC_FINAL_DELETE#}" name="{#DOC_FINAL_CONFIRM#}" href="index.php?do=docs&action=enddelete&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}"><span class="icon_sprite_doc icon_delete floatleft"></span></a>
						{/if}

					{/if}
					</div>

				{else}
					<strong>
					{if $item->document_breadcrum_title != ""}
						{$item->document_breadcrum_title|stripslashes}{elseif $item->document_title != ""}{$item->document_title|stripslashes}{else}{#DOC_SHOW3_TITLE#}
					{/if}
					</strong>
					<br />
					<span class="code">url:</span>&nbsp;
					<a class="toprightDir" title="{#DOC_SHOW2_TITLE#}" href="../{if $item->Id!=1}{$item->document_alias}{/if}" target="_blank">
						<span class="dgrey doclink">{$item->document_alias}</span>
					</a>
					&nbsp;|&nbsp;
					<span class="dgrey">{#DOC_CLICKS#}: </span> <strong class="code">{$item->document_count_view}</strong>
				{/if}
				</div>
			</td>

			<td nowrap="nowrap" align="center">
				{if $item->cantEdit==1}

					{foreach from=$rubrics item=rubric}
						{if $item->rubric_id == $rubric->Id}
							<a href="javascript:void(0);" title="{#DOC_CHANGE_RUBRIC#}" class="link topDir" onclick="windowOpen('index.php?do=docs&action=change&Id={$item->Id}&rubric_id={$item->rubric_id}&pop=1&cp={$sess}','650','550','1','docs');">
								{$rubric->rubric_title|escape}
							</a>
							<br />
							{if $smarty.const.UGROUP == 1}
							<strong>{#DOC_AUTHOR#}:</strong> <a class="link topDir" title="{#DOC_CHANGE_AUTOR#}" href="javascript:void(0);" id="doc_id_{$item->Id}" onclick="windowOpen('index.php?do=docs&action=change_user&Id={$item->Id}&pop=1&cp={$sess}','750','500','1','docs');">{$item->document_author|escape}</a>
							{else}
							<strong>{#DOC_AUTHOR#}:</strong> {$item->document_author|escape}
							{/if}
						{/if}
					{/foreach}

				{else}
					{foreach from=$rubrics item=rubric}
						{if $item->rubric_id == $rubric->Id}
							{$rubric->rubric_title|escape}
							<br />
							<strong>{#DOC_AUTHOR#}:</strong> {$item->document_author|escape}
						{/if}
					{/foreach}
				{/if}
			</td>

			<td align="center">

				<div class="docaction">
					<div class="doc_message">
						{if $item->ist_remark!='0'}
							<div class="remarks"><span title="{#DOC_ICON_COMMENT#}" class="icon_sprite_doc icon_comment topDir"></span></div>
						{/if}
					</div>
					<span class="date_text dgrey">
						{$item->document_published|date_format:$TIME_FORMAT|pretty_date}
						<br />
						{$item->document_changed|date_format:$TIME_FORMAT|pretty_date}
					</span>
				</div>
			</td>


			{if !$smarty.const.ADMIN_EDITMENU}
			<td align="center" nowrap="nowrap" class="actions">
				{if check_permission("remarks")}
					{if $item->ist_remark=='0'}
						<a class="topleftDir floatleft" title="{#DOC_CREATE_NOTICE_TITLE#}" href="javascript:void(0);" onclick="windowOpen('index.php?do=docs&action=remark&Id={$item->Id}&pop=1&cp={$sess}','800','700','1','docs');"><span class="icon_sprite ico_comment"></span></a>
					{else}
						<a class="topleftDir floatleft" title="{#DOC_REPLY_NOTICE_TITLE#}" href="javascript:void(0);" onclick="windowOpen('index.php?do=docs&action=remark_reply&Id={$item->Id}&pop=1&cp={$sess}','800','700','1','docs');"><span class="icon_sprite ico_comment"></span></a>
					{/if}
				{else}
					{*<span class="topleftDir icon_sprite ico_comment_no floatleft"></span>*}
				{/if}

				{if $item->cantEdit==1 && $item->Id != 1 && $item->Id != $PAGE_NOT_FOUND_ID}
					<a class="topleftDir CopyDocs floatleft" title="{#DOC_COPY#}" href="index.php?do=docs&action=copy&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}"><span class="icon_sprite ico_copy"></span></a>
	 				{else}
					{*<span class="icon_sprite ico_copy_no floatleft"></span>*}
				{/if}

				{if $item->cantEdit==1}
					<a class="topleftDir floatleft" title="{#DOC_EDIT_TITLE#}" href="index.php?do=docs&action=edit&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}"><span class="icon_sprite ico_edit"></span></a>
				{else}
					{*<span class="icon_sprite ico_edit_no floatleft"></span>*}
				{/if}

				{if $item->document_deleted==1}
					{*<span class="icon_sprite ico_blank floatleft"></span>*}
				{else}
					{if $item->document_status==1}
						{if $item->canOpenClose==1 && $item->Id != 1 && $item->Id != $PAGE_NOT_FOUND_ID}
							<a class="topleftDir lock floatleft" ajax="index.php?do=docs&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}" title="{#DOC_DISABLE_TITLE#}" href="index.php?do=docs&action=close&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}"><span class="icon_sprite ico_unlock"></span></a>
						{else}
							{if $item->cantEdit==1 && $item->Id != 1 && $item->Id != $PAGE_NOT_FOUND_ID}
				   			{*<span class="icon_sprite ico_unlock_no floatleft"></span>*}
							{else}
							{*<span class="icon_sprite ico_unlock_no floatleft"></span>*}
							{/if}
						{/if}
					{else}
						{if $item->canOpenClose==1}
							<a class="topleftDir floatleft" ajax="index.php?do=docs&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}" title="{#DOC_ENABLE_TITLE#}" href="index.php?do=docs&action=open&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}"><span class="icon_sprite ico_lock"></span></a>
						{else}
							{if $item->cantEdit==1 && $item->Id != 1 && $item->Id != $PAGE_NOT_FOUND_ID}
							{*<span class="icon_sprite ico_lock_no floatleft"></span>*}
							{else}
							{*<span class="icon_sprite ico_lock_no floatleft"></span>*}
							{/if}
						{/if}
					{/if}
				{/if}

				{if $item->document_deleted==1}
					<a class="topleftDir floatleft" title="{#DOC_RESTORE_DELETE#}" href="index.php?do=docs&action=redelete&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}"><span class="icon_sprite ico_recylce_on"></span></a>
				{else}
					{if $item->canDelete==1}
						<a class="ConfirmRecycle topleftDir floatleft" title="{#DOC_TEMPORARY_DELETE#}"  href="index.php?do=docs&action=delete&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}"><span class="icon_sprite ico_recylce"></span></a>
					{else}
						{*<span class="icon_sprite ico_recylce_no floatleft"></span>*}
					{/if}
				{/if}

				{if $item->canEndDel==1 && $item->Id != 1 && $item->Id != $PAGE_NOT_FOUND_ID}
					<a class="ConfirmDelete topleftDir floatleft" title="{#DOC_FINAL_DELETE#}" dir="{#DOC_FINAL_DELETE#}" name="{#DOC_FINAL_CONFIRM#}" href="index.php?do=docs&action=enddelete&rubric_id={$item->rubric_id}&Id={$item->Id}&cp={$sess}"><span class="icon_sprite ico_delete"></span></a>
				{else}
					{*<span class="icon_sprite ico_delete_no floatleft"></span>*}
				{/if}
			</td>
			{/if}
		</tr>
	{/foreach}
	{else}
			<tr>
				<td {if $smarty.const.ADMIN_EDITMENU}colspan="7"{else}colspan="14"{/if}>
					<ul class="messages">
						<li class="highlight yellow">{#DOC_NO_DOCS#}</li>
					</ul>
				</td>
			</tr>
	{/if}
	{if $docs}
	<thead>
	<tr>
		<td></td>
		<td>{#DOC_ID#}</td>
		<td nowrap="nowrap">
			{#DOC_TITLE#}&nbsp;|&nbsp;{#DOC_URL_RUB#}
		</td>
		<td>{#DOC_IN_RUBRIK#}</td>
		<td>{#DOC_CREATED#}&nbsp;|&nbsp;{#DOC_EDIT#}</td>
		{if !$smarty.const.ADMIN_EDITMENU}<td colspan="6" align="center">{#DOC_ACTIONS#}</td>{/if}
	</tr>
	</thead>
	{/if}
</tbody>
</table>

{if check_permission('alle')}
<div class="rowElem" id="saveBtn">
	<div class="saveBtn">
			<select name="moderation" class="action-in-moderation" style="width: 250px;">
				<option value="none" selected="selected">{#DOC_ACTION_SELECT#}</option>
				<option value="1">{#DOC_ACTION_SELECT_ACT#}</option>
				<option value="0">{#DOC_ACTION_SELECT_NACT#}</option>
				<option value="intrash">{#DOC_ACTION_SELECT_TRASH#}</option>
				<option value="outtrash">{#DOC_ACTION_SELECT_OUTTRASH#}</option>
				<option value="trash">{#DOC_ACTION_SELECT_DEL#}</option>
			</select>
			&nbsp;&nbsp;<input type="submit" class="basicBtn" value="{#DOC_ACTION_BUTTON#}" onclick="document.getElementById('nf_save_next').value='save'" />
	</div>
</div>
{/if}

</form>
</div>

{if $page_nav}
	<div class="pagination">
	<ul class="pages">
		{$page_nav}
	</ul>
	</div>
{/if}

<script language="Javascript" type="text/javascript">
$(document).ready(function(){ldelim}

	$(".AddDocs").click( function(e) {ldelim}
		e.preventDefault();
		var DocName = $('#add_docum #DocName').fieldValue();
		var title = '{#MAIN_ADD_IN_RUB#}';
		var text = '{#DOC_ENTER_NAME#}';
		if (DocName == ""){ldelim}
			jAlert(text,title);
		{rdelim}else{ldelim}
			$.alerts._overlay('show');
			$("#add_docum").submit();
		{rdelim}
	{rdelim});

	$('#selall').on('change', function(event) {ldelim}
		event.preventDefault();
		if ($('#selall').is(':checked')) {ldelim}
			$('#docs .checkbox').attr('checked','checked');
			$('#docs .checkbox').addClass('jqTransformChecked');
			$("#docs a.jqTransformCheckbox").addClass("jqTransformChecked");
		{rdelim} else {ldelim}
			$('#docs .checkbox').removeClass('jqTransformChecked');
			$('#docs .checkbox').removeAttr('checked');
			$("#docs a.jqTransformCheckbox").removeClass("jqTransformChecked");
		{rdelim}
	{rdelim});

	$(".ConfirmRecycle").click(function(e){ldelim}
		e.preventDefault();
		var href = $(this).attr('href');
		var title = '{#DOC_TEMPORARY_DELETE#}';
		var confirm = '{#DOC_TEMPORARY_CONFIRM#}';
		jConfirm(
				confirm,
				title,
				function(b){ldelim}
					if (b){ldelim}
						$.alerts._overlay('show');
						window.location = href;
					{rdelim}
				{rdelim}
			);
	{rdelim});

	$(".CopyDocs").click( function(e) {ldelim}
		e.preventDefault();
		var href = $(this).attr('href');
		var title = '{#DOC_COPY#}';
		var text = '{#DOC_COPY_TIP#}';
		jPrompt(text, '', title, function(b){ldelim}
					if (b){ldelim}
						$.alerts._overlay('show');
						window.location = href + '&document_title=' + b;
						{rdelim}else{ldelim}
							$.jGrowl("{#MAIN_NO_ADD_DOCS#}", {ldelim}theme: 'error'{rdelim});
						{rdelim}
				{rdelim}
			);
	{rdelim});

	 $(".docaction").hover(
		  function() {ldelim}$(this).children(".actions").show("fade", 10);{rdelim},
		  function() {ldelim}$(this).children(".actions").hide("fade", 10);{rdelim}
	 );


{literal}

	function action(href, actions){
		$.ajax({
				beforeSend: function(){
					$.alerts._overlay('show');
					},
				url: href,
				data: ({
					action: actions,
					ajax: '1',
					pop: '1'
					}),
				timeout:3000,
				dataType: "json",
				success: function(data){
					$.alerts._overlay('hide');
					$.jGrowl(data[0],{theme: data[1]});
				},
				error: function (xhr, ajaxOptions, thrownError) {
					$.alerts._overlay('hide');
					$.jGrowl(xhr.status + thrownError, {theme: 'error'});
				}
			});
		};

		$('.lock').on('click', function(e){
			e.preventDefault();
			if($(this).hasClass('ico_unlock')){
				action($(this).attr('ajax'),'close');
				$(this).removeClass("ico_unlock").addClass("ico_lock");
			} else if ($(this).hasClass('ico_lock')){
				action($(this).attr('ajax'),'open');
				$(this).removeClass("ico_lock").addClass("ico_unlock")
			}
		});

{/literal}




{rdelim});
</script>