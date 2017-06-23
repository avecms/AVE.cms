<div class="title">
	{if $smarty.request.action !='tmpls_edit'}
	<h5>{#RUBRIK_TEMPLATE_NEW#}</h5>
	{else}
	<h5>{#RUBRIK_TEMPLATE_EDIT#}</h5>
	{/if}
	<div class="num">
		<a class="basicNum" href="index.php?do=rubs&action=tmpls&Id={$smarty.request.rubric_id|escape}&cp={$sess}">{#RUBRIC_TMPLS_BUTTON#}</a>
		&nbsp;
		<a class="basicNum" href="index.php?do=rubs&action=edit&Id={$smarty.request.rubric_id|escape}&cp={$sess}">{#RUBRIK_EDIT#}</a>
		&nbsp;
		{if check_permission('rubric_code')}
		<a class="basicNum" href="index.php?do=rubs&action=code&Id={$smarty.request.rubric_id|escape}&cp={$sess}">{#RUBRIK_EDIT_CODE#}</a>
		{/if}
	</div>
</div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">{#RUBRIK_TEMPLATE_TIP#}</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=rubs&cp={$sess}">{#RUBRIK_SUB_TITLE#}</a></li>
			<li><strong class="code">{$rubric->rubric_title|escape}</strong></li>
			<li>{#RUBRIC_TMPLS_BUTTON#}</li>
			{if $smarty.request.action != 'tmpls_edit'}
			<li>{#RUBRIK_TEMPLATE_NEW#}</li>
			{else}
			<li><strong class="code">{$template->title|escape:html}</strong></li>
			{/if}
		</ul>
	</div>
</div>

{if $php_forbidden == 1}
	<ul class="messages first">
		<li class="highlight red">{#RUBRIK_PHP_DENIDED#}</li>
	</ul>
{/if}

{if $errors}
{foreach from=$errors item=e}
{assign var=message value=$e}
	<ul class="messages first">
		<li class="highlight red">{$message}</li>
	</ul>
{/foreach}
{/if}

	<form name="RubricTpl" id="RubricTpl" method="post" action="{$formaction}" class="mainForm">

		<div class="widget first">
			<div class="head">
				<h5 class="iFrames">{#RUBRIC_TMPLS_NAME_FULL#}</h5>
			</div>

			<div class="rowElem noborder">
				<label>{#RUBRIC_TMPLS_NAME#}</label>
				<div class="formRight">
					<input name="template_title" type="text" value="{if $smarty.request.tmpls_name}{$smarty.request.tmpls_name|escape:html}{else}{$template->title|escape:html}{/if}" maxlength="50" style="width: 400px;" class="mousetrap" />
				</div>
				<div class="fix"></div>
			</div>
		</div>

		<div class="widget first">
			<div class="head">
				<h5 class="iFrames">{#RUBRIK_HTML#}</h5>
			</div>

			{if !check_permission('rubric_php')}
			<div class="rowElem">
				<ul class="messages">
					<li class="highlight red aligncenter">
						{#RUBRIK_PHP_MESSAGE#}
					</li>
				</ul>
				<div class="fix"></div>
			</div>
			{/if}

			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<col width="18%" />
				<col width="82%" />
				<thead>
					<tr class="noborder">
						<td>{#RUBRIK_TAGS#}</td>
						<td>{#RUBRIK_HTML_T#}</td>
					</tr>
				</thead>
				<tbody>
					<tr>
						<td>
							{#RUBRIK_IFELSE#}
						</td>
						<td>
							|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('[tag:lang:', ']');"><strong>[tag:lang:XX]</strong></a>
							&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('[tag:/lang]', '');"><strong>[tag:/lang]</strong></a>
							&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('[tag:if_notempty:fld:', ']');"><strong>[tag:if_notempty:fld:XXX]</strong></a>
							&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('[tag:if_empty:fld:', ']');"><strong>[tag:if_empty:fld:XXX]</strong></a>
							&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('[tag:if:else]', '');"><strong>[tag:if:else]</strong></a>
							&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('[tag:/if]', '');"><strong>[tag:/if]</strong></a>
							&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('[tag:if_notempty:fld:XXX]\r\n{#RUBRIK_IFELSE_1#}\r\n[tag:if:else]\r\n{#RUBRIK_IFELSE_2#}\r\n[tag:/if]', '');"><strong>{#RUBRIK_SAMPLE#}</strong></a>
							&nbsp;|
						</td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_DOCID_INFO#}" href="javascript:void(0);" onclick="textSelection('[tag:docid]', '');"><strong>[tag:docid]</strong></a>
						</td>
						<td rowspan="20">
							<textarea {$read_only} class="{if $php_forbidden == 1}tpl_code_readonly{else}{/if}" style="width:100%; height:350px" name="rubric_template" id="rubric_template">{$template->template|default:$prefab|escape:html}</textarea>
							<ul class="messages" style="margin-top: 10px;">
								<li class="highlight grey">
									{#MAIN_CODEMIRROR_HELP#}
								</li>
							</ul>
						</td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_TAG_DOCDB#}" href="javascript:void(0);" onclick="textSelection('[tag:doc:', ']');"><strong>[tag:doc:XXX]</strong></a>
						</td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_TAG_ALIAS#}" href="javascript:void(0);" onclick="textSelection('[tag:alias]', '');"><strong>[tag:alias]</strong></a>
						</td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_DOCDATE_INFO#}" href="javascript:void(0);" onclick="textSelection('[tag:docdate]', '');"><strong>[tag:docdate]</strong></a>
						</td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_DOCTIME_INFO#}" href="javascript:void(0);" onclick="textSelection('[tag:doctime]', '');"><strong>[tag:doctime]</strong></a>
						</td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_DATE_INFO#}" href="javascript:void(0);" onclick="textSelection('[tag:date:', ']');"><strong>[tag:date:XXX]</strong></a>
						</td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_DOCAUTHOR_INFO#}" href="javascript:void(0);" onclick="textSelection('[tag:docauthor]', '');"><strong>[tag:docauthor]</strong></a>
						</td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_DOCAUTHOR_AVATAR#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:docauthoravatar:]', '');">[tag:docauthoravatar:XXX]</a></strong></td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_VIEWS_INFO#}" href="javascript:void(0);" onclick="textSelection('[tag:docviews]', '');"><strong>[tag:docviews]</strong></a>
						</td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_TITLE_INFO#}" href="javascript:void(0);" onclick="textSelection('[tag:title]', '');"><strong>[tag:title]</strong></a>
						</td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_PATH_INFO#}" href="javascript:void(0);" onclick="textSelection('[tag:path]', '');"><strong>[tag:path]</strong></a>
						</td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_LINK_HOME#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:home]', '');">[tag:home]</a></strong></td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_MEDIAPATH_INFO#}" href="javascript:void(0);" onclick="textSelection('[tag:mediapath]', '');"><strong>[tag:mediapath]</strong></a>
						</td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_TAG_REQUEST#}" href="javascript:void(0);" onclick="textSelection('[tag:request:]', '');"><strong>[tag:request:XXX]</strong></a>
						</td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_TAG_SYSBLOCK#}" href="javascript:void(0);" onclick="textSelection('[tag:sysblock:]', '');"><strong>[tag:sysblock:XXX]</strong></a>
						</td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_TAG_TEASER#}" href="javascript:void(0);" onclick="textSelection('[tag:teaser:]', '');"><strong>[tag:teaser:XXX]</strong></a>
						</td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_BREADCRUMB#}" href="javascript:void(0);" onclick="textSelection('[tag:breadcrumb]', '');"><strong>[tag:breadcrumb]</strong></a>
						</td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_HIDE_INFO#}" href="javascript:void(0);" onclick="textSelection('[tag:hide:', ']\n\n[/tag:hide]');"><strong>[tag:hide:X,X][/tag:hide]</strong></a>
						</td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_THUMBNAIL#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:X000x000:[tag:fld:]]', '');">[tag:X000x000:[tag:fld:YYY]]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_TAG_LANGFILE#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:langfile:', ']');">[tag:langfile:XXX]</a></strong></td>
					</tr>
					<tr>
						<td><strong>HTML Tags</strong></td>
						<td>
							|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<a href=&quot;&quot; title=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection('\t', '');"><strong>TAB</strong></a>&nbsp;|
						</td>
					</tr>
				</tbody>
			</table>

			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<col width="3%" />
				<col width="25%" />
				<col width="10%" />
				<col width="10%" />
				<col width="15%" />
				<col width="48%" />
				<thead>
					<tr>
						<td align="center"><strong>{#RUBRIK_ID#}</strong></td>
						<td align="center"><strong>{#RUBRIK_FIELD_NAME#}</strong></td>
						<td align="center"><strong>{#RUBRIK_TAGS_ID#}</strong></td>
						<td align="center"><strong>{#RUBRIK_TAGS_ALIAS#}</strong></td>
						<td align="center"><strong>{#RUBRIK_FIELD_ALIAS#}</strong></td>
						<td align="center"><strong>{#RUBRIK_FIELD_TYPE#}</strong></td>
					</tr>
				</thead>
				<tbody>

					{foreach from=$fields_list item=field_group}

					{if $groups_count > 1}
					<tr class="grey">
						<td colspan="6"><h5>{if $field_group.group_title}{$field_group.group_title}{else}{#RUBRIK_FIELD_G_UNKNOW#}{/if}</h5></td>
					</tr>
					{/if}

					{foreach  from=$field_group.fields item=field}
					<tr>
						<td align="center">
							<strong class="code">{$field.Id}</strong>
						</td>
						<td>
							<strong>{$field.rubric_field_title}</strong>
						</td>
						<td>
							<a class="rightDir" title="{#RUBRIK_INSERT_HELP#}" href="javascript:void(0);" onclick="textSelection('[tag:fld:{$field.Id}]', '');"><strong>[tag:fld:{$field.Id}]</strong></a>
						</td>
						<td>
							{if $field.rubric_field_alias}
							<a class="rightDir" title="{#RUBRIK_INSERT_HELP#}" href="javascript:void(0);" onclick="textSelection('[tag:fld:{$field.rubric_field_alias}]', '');"><strong>[tag:fld:{$field.rubric_field_alias}]</strong></a>
							{/if}
						</td>
						<td align="center">
							{if $field.rubric_field_alias}<strong class="code">{$field.rubric_field_alias}</strong>{/if}
						</td>
						<td>
							{section name=field_name loop=$field_array}
								{if $field.rubric_field_type == $field_array[field_name].id}{$field_array[field_name].name}{/if}
							{/section}
						</td>
					</tr>
					{/foreach}

					{/foreach}

				</tbody>
			</table>

			<div class="fix"></div>

			<div class="rowElem" id="saveBtn">
				<div class="saveBtn">
					<input type="hidden" name="id" value="{$smarty.request.id|escape}" />
					<input class="basicBtn" type="submit" value="{#RUBRIK_BUTTON_TPL#}" />
					{if $smarty.request.action == 'tmpls_edit'}
					{#RUBRIK_OR#}
					<input type="submit" class="blackBtn SaveEdit" name="next_edit" value="{#RUBRIK_BUTTON_TPL_NEXT#}" />
					{/if}
				</div>
			</div>

		</div>

</form>

<div class="fix"></div>

{include file="$codemirror_connect"}
{include file="$codemirror_editor" conn_id="" textarea_id='rubric_template' ctrls='$("#RubricTpl").ajaxSubmit(sett_options);' height=500}

{if $smarty.request.action =='tmpls_edit'}
<script language="Javascript" type="text/javascript">

	var sett_options = {ldelim}
		url: '{$formaction}',
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

		Mousetrap.bind(['ctrl+s', 'command+s'], function(event) {ldelim}
			if (event.preventDefault) {ldelim}
				event.preventDefault();
			{rdelim} else {ldelim}
				// internet explorer
				e.returnValue = false;
			{rdelim}
			$("#RubricTpl").ajaxSubmit(sett_options);
			return false;
		{rdelim});

		$(".SaveEdit").click(function(event){ldelim}
			if (event.preventDefault) {ldelim}
				event.preventDefault();
			{rdelim} else {ldelim}
				// internet explorer
				event.returnValue = false;
			{rdelim}
			$("#RubricTpl").ajaxSubmit(sett_options);
			return false;
		{rdelim});

	{rdelim});
</script>
{/if}