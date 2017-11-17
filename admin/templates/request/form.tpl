<script language="JavaScript" type="text/javascript">
/*<![CDATA[*/

$rid = parseInt('{$rid}');
$sess = '{$sess}';

var clipboard = new Clipboard('.copyBtn');

function changeRub(select) {ldelim}
	if(select.options[select.selectedIndex].value!='#') {ldelim}

			if(select.options[select.selectedIndex].value!='#') {ldelim}
			{if $smarty.request.action=='new'}
				location.href='index.php?do=request&action=new&rubric_id=' + select.options[select.selectedIndex].value + '{if $smarty.request.request_title_new!=''}&request_title_new={$smarty.request.request_title_new|escape|stripslashes}{/if}';
			{else}
				location.href='index.php?do=request&action=edit&Id={$smarty.request.Id|escape}&rubric_id=' + select.options[select.selectedIndex].value;
			{/if}
			{rdelim}

		else {ldelim}
			document.getElementById('RubrikId_{$smarty.request.rubric_id|escape}').selected = 'selected';
		{rdelim}
	{rdelim}
{rdelim}
/*]]>*/
</script>

{if $smarty.request.action=='edit'}
	<div class="title">
		<h5>{#REQUEST_EDIT2#}</h5>
		<div class="num">
			<a class="basicNum" href="index.php?do=request&action=conditions&Id={$smarty.request.Id|escape}&rubric_id={$smarty.request.rubric_id|escape}&cp={$sess}">{#REQUEST_CONDITION_EDIT#}</a>
		</div>
	</div>
	<div class="widget" style="margin-top: 0px;">
		<div class="body">{#REQUEST_EDIT_TIP#}</div>
	</div>
{else}
	<div class="title">
		<h5>{#REQUEST_NEW#}</h5>
	</div>
	<div class="widget" style="margin-top: 0px;">
		<div class="body">{#REQUEST_NEW_TIP#}</div>
	</div>
{/if}


<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=request&amp;cp={$sess}">{#REQUEST_ALL#}</a></li>
				{if $smarty.request.action=='edit'}
					<li>{#REQUEST_EDIT2#}</li>
				{else}
					<li>{#REQUEST_NEW#}</li>
				{/if}
			<li><strong class="code">{$smarty.request.request_title_new|stripslashes|default:$row->request_title|escape}</strong></li>
		</ul>
	</div>
</div>

{if $errors}
	<ul class="messages first">
	{foreach from=$errors item=e}
		<li class="highlight red mb10">
		{assign var=message value=$e}
		&bull;&nbsp;{$message}<br />
		</li>
	{/foreach}
	</ul>
{/if}

{if !check_permission('request_php')}
	<ul class="messages first">
		<li class="highlight red aligncenter">
		{#REQUEST_REPORT_ERR_PHP#}
		</li>
	</ul>
{/if}


{if $smarty.request.Id == ''}
	{assign var=iframe value='no'}
{/if}

{if $smarty.request.action == 'new' && $smarty.request.rubric_id == ''}
	{assign var=dis value='disabled'}
{/if}

{if $smarty.request.action=='new' && $smarty.request.rubric_id==''}
<ul class="messages first">
	<li class="highlight red">
		<strong>{#REQUEST_PLEASE_SELECT#}</strong>
	</li>
</ul>
{/if}

<div class="widget first">

	<ul class="tabs">
		<li class="activeTab">
			<a href="#tab1">{#REQUEST_SETTINGS#}</a>
		</li>
		<li>
			<a href="#tab2">{#REQUEST_TEMPLATE_QUERY#}</a>
		</li>
		<li>
			<a href="#tab3">{#REQUEST_TEMPLATE_ITEMS#}</a>
		</li>
	</ul>

	<form name="RequestTpl" id="RequestTpl" method="post" action="{$formaction}" class="mainForm">

	<div class="tab_container">

		<div id="tab1" class="tab_content" style="display: block;">

			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<col width="250">
				<col>
				<col width="250">
				<col>
				<thead>
				<tr>
					<td colspan="4">{#REQUEST_HEADER_SELF#}</td>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>{#REQUEST_NAME2#}</td>
					<td colspan="3"><input {$dis} class="mousetrap" style="width: 100%" name="request_title" type="text" id="l_Titel" value="{$smarty.request.request_title_new|stripslashes|default:$row->request_title|escape}"></td>
				</tr>

				<tr>
					<td>
						<div class="nowrap">
							<strong><a class="toprightDir" title="{#REQUEST_I#}">[?]</a></strong> {#REQUEST_ALIAS#}:
						</div>
					</td>
					<td colspan="3">
						<div class="pr12">
							<input type="text" name="request_alias" value="{if $smarty.request.Id != ''}{$row->request_alias}{else}{$smarty.request.request_alias}{/if}" id="request_alias" value="" class="mousetrap" data-accept="{#REQUEST_ACCEPT#}" data-error-syn="{#REQUEST_ER_SYN#}" data-error-exists="{#REQUEST_ER_EXISTS#}" placeholder="{#REQUEST_ALIAS#}" maxlength="20" style="width: 200px;" autocomplete="off" />&nbsp;
							<input type="text" id="request_alias_tag" value="[tag:request:{if $smarty.request.Id != ''}{if $row->request_alias != ''}{$row->request_alias}{else}{$smarty.request.Id}{/if}{else}{$smarty.request.request_alias}{/if}]" readonly size="40" class="mousetrap" style="width: 200px;" />
							<a style="text-align: center; padding: 5px 3px 4px 3px;" class="whiteBtn copyBtn" href="javascript:void(0);" data-clipboard-action="copy" data-clipboard-target="#sysblock_alias_tag">
								<img style="margin-top: -3px; position: relative; top: 4px; padding: 0 3px;" class="clippy" src="{$ABS_PATH}admin/templates/images/clippy.svg" width="13">
							</a>
						</div>
					</td>
				</tr>

				<tr>
					<td>{#REQUEST_CACHE#}</td>
					<td><input {$dis} class="mousetrap" style="width:100px" name="request_cache_lifetime" type="text" id="request_cache_lifetime" value="{$smarty.request.request_cache_lifetime|stripslashes|default:$row->request_cache_lifetime|escape}"></td>
					<td>{#REQUEST_CACHE_ELEMENTS#}</td>
					<td><input class="mousetrap float" name="request_cache_elements" type="checkbox" value="1" {if $row->request_cache_elements}checked="checked"{/if}/><label>&nbsp;</label></td>
				</tr>

				<tr>
					<td>{#REQUEST_SELECT_RUBRIK#}</td>
					<td colspan="3">
						<select onChange="changeRub(this)" style="width:350px" id="rubric_id" class="mousetrap">
							{if $smarty.request.action=='new' && $smarty.request.rubric_id==''}
								<option value="">{#REQUEST_PLEASE_SELECT#}</option>
							{/if}
							{foreach from=$rubrics item=rubric}
								<option id="RubrikId_{$rubric->Id}" value="{$rubric->Id}"{if $smarty.request.rubric_id==$rubric->Id} selected="selected"{/if}>{$rubric->rubric_title|escape}</option>
							{/foreach}
						</select>
						<input type="hidden" name="rubric_id" value="{$smarty.request.rubric_id}" />
					</td>
				</tr>

				<tr>
					<td>{#REQUEST_DESCRIPTION#}<br /><small>{#REQUEST_INTERNAL_INFO#}</small></td>
					<td colspan="3"><textarea class="mousetrap" {$dis} style="width:350px; height:60px" name="request_description" id="request_description">{if $smarty.request.action=='new' && $smarty.request.request_description !=''}{$smarty.request.request_description|escape}{else}{$row->request_description|escape}{/if}</textarea></td>
				</tr>

				<tr class="grey">
					<td>{#REQUEST_CONDITION#}</td>
					<td colspan="3">
						{if $iframe == 'no'}
							<input type="checkbox" name="reedit" value="1" checked="checked" class="float mousetrap" /> <label>{#REQUEST_ACTION_AFTER#}</label>
						{/if}
						{if $iframe != 'no'}
							<a href="index.php?do=request&action=conditions&Id={$smarty.request.Id|escape}&rubric_id={$smarty.request.rubric_id|escape}&cp={$sess}&pop=1" data-modal="true" data-dialog="conditions-{$smarty.request.Id}" data-title="{#REQUEST_CONDITION#}" class="openDialog button basicBtn">{#REQUEST_BUTTON_COND#}</a>
						{/if}
					</td>
				</tr>

				</tbody>
				<thead>
				<tr>
					<td>{#REQUEST_HEADER_NAME#}</td>
					<td>{#REQUEST_HEADER_PARAMETR#}</td>
					<td>{#REQUEST_HEADER_NAME#}</td>
					<td>{#REQUEST_HEADER_PARAMETR#}</td>
				</tr>
				</thead>

				<tbody>
				<tr>
					<td>{#REQUEST_HIDE_CURRENT#}</td>
					<td><input class="mousetrap float" name="request_hide_current" type="checkbox" value="1" {if $row->request_hide_current}checked="checked"{/if}/><label>&nbsp;</label></td>

					<td>{#REQUEST_ONLY_OWNER#}</td>
					<td><input class="mousetrap float" name="request_only_owner" type="checkbox" value="1" {if $row->request_only_owner}checked="checked"{/if}/><label>&nbsp;</label></td>
				</tr>

				<tr>
					<td>{#REQUEST_SORT_BY_NAT#}</td>
					<td>
						<select {$dis} style="width: 250px" name="request_order_by_nat" id="request_order_by_nat" class="mousetrap">
							<option>&nbsp;</option>
						{foreach from=$fields_list item=field_group}

							{if $groups_count > 1}
							<optgroup label="{if $field_group.group_title}{$field_group.group_title}{else}{#REQUEST_FIELD_G_UNKNOW#}{/if}">
							{/if}

							{foreach  from=$field_group.fields item=field}
								<option value="{$field.Id|escape}" {if $row->request_order_by_nat == $field.Id}selected{/if}>{$field.rubric_field_title|escape}</option>
							{/foreach}

							{if $groups_count > 1}
							</optgroup>
							{/if}

						{/foreach}
						</select>
					</td>

					<td>{#REQUEST_SORT_BY#}</td>
					<td>
						<select {$dis} style="width:250px" name="request_order_by" id="request_order_by" class="mousetrap">
							<option value="">&nbsp;</option>
							<option value="Id"{if $row->request_order_by=='Id'} selected="selected"{/if}>Id</option>
							<option value="document_published"{if $row->request_order_by=='document_published'} selected="selected"{/if}>{#REQUEST_BY_DATE#}</option>
							<option value="document_changed"{if $row->request_order_by=='document_changed'} selected="selected"{/if}>{#REQUEST_BY_DATECHANGE#}</option>
							<option value="document_title"{if $row->request_order_by=='document_title'} selected="selected"{/if}>{#REQUEST_BY_NAME#}</option>
							<option value="document_author_id"{if $row->request_order_by=='document_author_id'} selected="selected"{/if}>{#REQUEST_BY_EDIT#}</option>
							<option value="document_count_print"{if $row->request_order_by=='document_count_print'} selected="selected"{/if}>{#REQUEST_BY_PRINTED#}</option>
							<option value="document_count_view"{if $row->request_order_by=='document_count_view'} selected="selected"{/if}>{#REQUEST_BY_VIEWS#}</option>
							<option value="document_parent"{if $row->request_order_by=='document_parent'} selected="selected"{/if}>{#REQUEST_BY_PARENT#}</option>
							<option value="RAND()"{if $row->request_order_by=='RAND()'} selected="selected"{/if}>{#REQUEST_BY_RAND#}</option>
						</select>
					</td>
				</tr>

				<tr>
					<td>{#REQUEST_ASC_DESC#}</td>
					<td>
						<select {$dis} style="width:150px" name="request_asc_desc" id="request_asc_desc" class="mousetrap">
							<option value="DESC"{if $row->request_asc_desc=='DESC'} selected="selected"{/if}>{#REQUEST_DESC#}</option>
							<option value="ASC"{if $row->request_asc_desc=='ASC'} selected="selected"{/if}>{#REQUEST_ASC#}</option>
						</select>
					</td>

					<td>{#REQUEST_DOC_PER_PAGE#}</td>
					<td>
						<select {$dis} style="width:150px" name="request_items_per_page" id="request_items_per_page" class="mousetrap">
								<option value="0" {if $row->request_items_per_page==all} selected="selected"{/if}>{#REQUEST_DOC_PER_PAGE_ALL#}</option>
							{section name=items loop=300 step=1 start=0}
								<option value="{$smarty.section.items.index+1}"{if $row->request_items_per_page==$smarty.section.items.index+1} selected="selected"{/if}>{$smarty.section.items.index+1}</option>
							{/section}
						</select>
					</td>
				</tr>
				</tbody>

				<thead>
				<tr>
					<td colspan="4">{#REQUEST_PAGINATION#}</td>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>{#REQUEST_SHOW_NAVI#}</td>
					<td><input class="mousetrap float" name="request_show_pagination" type="checkbox" id="request_show_pagination" value="1"{if $row->request_show_pagination=='1'} checked="checked"{/if} /><label>&nbsp;</label></td>

					<td>{#REQUEST_NAVI_TPL#}</td>
					<td>
						<select style="width:350px" id="request_pagination" name="request_pagination" class="mousetrap">
							{foreach from=$paginations item=pagination}
								<option value="{$pagination->id}"{if $row->request_pagination == $pagination->id} selected="selected"{/if}>{$pagination->pagination_name|escape}</option>
							{/foreach}
						</select>
					</td>
				</tr>
				<tr>
					<td>{#REQUEST_USE_QUERY#}</td>
					<td colspan="3"><input class="mousetrap float" name="request_use_query" type="checkbox" id="request_use_query" value="1"{if $row->request_use_query == '1'} checked="checked"{/if} /><label>&nbsp;</label></td>
				</tr>
				</tbody>

				<thead>
				<tr>
					<td colspan="4">{#REQUEST_OTHER#}</td>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>{#REQUEST_USE_LANG#}</td>
					<td colspan="3"><input class="mousetrap float" name="request_lang" type="checkbox" id="request_lang" value="1"{if $row->request_lang == '1'} checked="checked"{/if} /><label>&nbsp;</label></td>
				</tr>
				<tr>
					<td>{#REQUEST_SHOW_STAT#}</td>
					<td><input class="mousetrap float" name="request_show_statistic" type="checkbox" id="request_show_statistic" value="1"{if $row->request_show_statistic == '1'} checked="checked"{/if} /><label>&nbsp;</label></td>
					<td>{#REQUEST_SHOW_SQL#}</td>
					<td><input class="mousetrap float" name="request_show_sql" type="checkbox" id="request_show_sql" value="1"{if $row->request_show_sql == '1'} checked="checked"{/if} /><label>&nbsp;</label></td>
				</tr>
				</tbody>

				<thead>
				<tr>
					<td colspan="4">{#REQUEST_HEADER_EXTERNAL#}</td>
				</tr>
				</thead>
				<tbody>
				<tr>
					<td>{#REQUEST_EXTERNAL#}</td>
					<td><input class="mousetrap float" name="request_external" type="checkbox" id="request_external" value="1"{if $row->request_external == '1'} checked="checked"{/if} /><label>&nbsp;</label></td>
					<td>{#REQUEST_ONLY_AJAX#}</td>
					<td><input class="mousetrap float" name="request_ajax" type="checkbox" id="request_ajax" value="1"{if $row->request_ajax == '1'} checked="checked"{/if} /><label>&nbsp;</label></td>
				</tr>
				</tbody>

			</table>
			<div class="fix"></div>

		</div>

		<div id="tab2" class="tab_content" style="display: none;">

			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<col width="230">
				<tr>
					<td><strong><a title="{#REQUEST_MAIN_CONTENT#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:content]', '');">[tag:content]</a></strong></td>
					<td rowspan="19">
						<textarea {$dis} name="request_template_main" id="request_template_main" wrap="off" style="width:100%; height:500px">{$row->request_template_main|escape|default:''}</textarea>
						<ul class="messages" style="margin-top: 10px;">
							<li class="highlight grey">
								{#MAIN_CODEMIRROR_HELP#}
							</li>
						</ul>
					</td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_MAIN_NAVI#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:pages]', '');">[tag:pages]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_PAGES_CURENT#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:pages:curent]', '');">[tag:pages:curent]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_PAGES_TOTAL#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:pages:total]', '');">[tag:pages:total]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_CDOCID_TITLE#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:pagetitle]', '');">[tag:pagetitle]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_DOC_COUNT#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:doctotal]', '');">[tag:doctotal]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_DOC_ON_PAGE#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:doconpage]', '');">[tag:doconpage]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a class="rightDir" title="{#REQUEST_DOCDB#}" href="javascript:void(0);" onclick="textSelection('[tag:doc:', ']');">[tag:doc:XXX]</a></td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_CDOCID_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:docid]', '');">[tag:docid]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_CDOCDATE_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:docdate]', '');">[tag:docdate]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_CDOCTIME_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:doctime]', '');">[tag:doctime]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_CDATE_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:date:', ']');">[tag:date:X]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_CDOCAUTHOR_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:docauthor]', '');">[tag:docauthor]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_LANGFILE#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:langfile:', ']');">[tag:langfile:XXX]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_PATH#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:path]', '');">[tag:path]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_MEDIAPATH#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:mediapath]', '');">[tag:mediapath]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_IF_EMPTY#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:if_empty]\n', '\n[/tag:if_empty]');">[tag:if_empty][/tag:if_empty]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a title="{#REQUEST_NOT_EMPTY#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:if_notempty]\n', '\n[/tag:if_notempty]');">[tag:if_notempty][/tag:if_notempty]</a></strong></td>
				</tr>
				<tr>
					<td><strong><a class="rightDir" title="{#REQUEST_LANG#}" href="javascript:void(0);" onclick="textSelection('[tag:lang:]\n','\n[tag:/lang]');">[tag:lang:XX][/tag:lang]</a></strong></td>
				</tr>
				<tr>
					<td>HTML Tags</td>
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
			</table>
			<div class="fix"></div>

		</div>

		<div id="tab3" class="tab_content" style="display: none;">

			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
			<col width="230">
			<col>
			<tr>
				<td>{#REQUEST_CONDITION_IF#}</td>
				<td>
					|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('[tag:if_first]', '[tag:/if]');"><strong>[tag:if_first]</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('[tag:if_not_first]', '[tag:/if]');"><strong>[tag:if_not_first]</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('[tag:if_last]', '[tag:/if]');"><strong>[tag:if_last]</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('[tag:if_not_last]', '[tag:/if]');"><strong>[tag:if_not_last]</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('[tag:if_every:]', '[tag:/if]');"><strong>[tag:if_every:XXX]</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('[tag:if_not_every:]', '[tag:/if]');"><strong>[tag:if_not_every:XXX]</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('[tag:if_every:2]четный[tag:if_else]нечетный[tag:/if]', '');"><strong>{#REQUEST_SAMPLE#}</strong></a>
					&nbsp;|
				</td>
			</tr>
			<tr class="noborder">
				<td><strong><a title="{#REQUEST_RUB_INFO#}" class="rightDir" href="javascript:void(0);" onclick="jAlert('{#REQUEST_SELECT_IN_LIST#}','{#REQUEST_TEMPLATE_ITEMS#}');">[tag:rfld:ID][XXX]</a></strong></td>
				<td rowspan="18">
					<ul class="messages" style="margin-bottom: 10px; font-size: 10px;">
						<li class="highlight grey">
							|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('[tag:if_notempty:rfld:', '][]');"><strong>[tag:if_notempty:rfld:XXX][XXX]</strong></a>
							&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('[tag:if_empty:rfld:', '][]');"><strong>[tag:if_empty:rfld:XXX][XXX]</strong></a>
							&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('[tag:if:else]', '');"><strong>[tag:if:else]</strong></a>
							&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('[tag:/if]', '');"><strong>[tag:/if]</strong></a>
							&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('[tag:if_notempty:rfld:XXX][XXX]\r\n\r\n[tag:if:else]\r\n\r\n[tag:/if]', '');"><strong>{#REQUEST_SAMPLE#}</strong></a>
							&nbsp;|
						</li>
					</ul>
					<textarea {$dis} name="request_template_item" id="request_template_item" wrap="off" style="width:100%; height:340px">{$row->request_template_item|escape|default:''}</textarea>
					<ul class="messages" style="margin-top: 10px;">
						<li class="highlight grey">
							{#MAIN_CODEMIRROR_HELP#}
						</li>
					</ul>
				</td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_DOCID_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:docid]', '');">[tag:docid]</a></strong></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_DOCITEMNUM_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:docitemnum]', '');">[tag:docitemnum]</a></strong></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_DOCTITLE_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:doctitle]', '');">[tag:doctitle]</a></strong></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_DOCDB#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:doc:', ']');">[tag:doc:XXX]</a></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_LINK_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:link]', '');">[tag:link]</a></strong></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_DOCDATE_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:docdate]', '');">[tag:docdate]</a></strong></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_DOCTIME_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:doctime]', '');">[tag:doctime]</a></strong></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_DATE_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:date:', ']');">[tag:date:X]</a></strong></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_DOCAUTHOR_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:docauthor]', '');">[tag:docauthor]</a></strong></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_DOCAUTHOR_AVATAR#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:docauthoravatar:]', '');">[tag:docauthoravatar:XXX]</a></strong></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_VIEWS_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:docviews]', '');">[tag:docviews]</a></strong></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_COMMENTS_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:doccomments]', '');">[tag:doccomments]</a></strong></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_PATH#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:path]', '');">[tag:path]</a></strong></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_MEDIAPATH#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:mediapath]', '');">[tag:mediapath]</a></strong></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_THUMBNAIL#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:X000x000:[tag:rfld:', '][150]]');">[tag:X000x000:[tag:rfld:XXX][XXX]]</a></strong></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_LANGFILE#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:langfile:', ']');">[tag:langfile:XXX]</a></strong></td>
			</tr>
			<tr>
				<td><strong><a title="{#REQUEST_LANG#}" class="rightDir" href="javascript:void(0);" onclick="textSelection2('[tag:lang:]\n','\n[tag:/lang]');">[tag:lang:XX][/tag:lang]</a></strong></td>
			</tr>
			<tr>
				<td>HTML Tags</td>
				<td>
					|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<ol>', '</ol>');"><strong>OL</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<ul>', '</ul>');"><strong>UL</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<li>', '</li>');"><strong>LI</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<strong>', '</strong>');"><strong>B</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<em>', '</em>');"><strong>I</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<h1>', '</h1>');"><strong>H1</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<h2>', '</h2>');"><strong>H2</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<h3>', '</h3>');"><strong>H3</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<h4>', '</h4>');"><strong>H4</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<h5>', '</h5>');"><strong>H5</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<a href=&quot;&quot; title=&quot;&quot;>', '</a>');"><strong>A</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<span>', '</span>');"><strong>SPAN</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<pre>', '</pre>');"><strong>PRE</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2('<br &#047;>', '');"><strong>BR</strong></a>
					&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelection2s('\t', '');"><strong>TAB</strong></a>
					&nbsp;|
				</td>
			</tr>
			</table>
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<col width="3%" />
				<col width="25%" />
				<col width="10%" />
				<col width="15%" />
				<col width="15%" />
				<col width="43%" />
				<thead>
					<tr>
						<td align="center"><strong>{#REQUEST_ID#}</strong></td>
						<td align="center"><strong>{#REQUEST_FIELD_NAME#}</strong></td>
						<td align="center"><strong>{#REQUEST_RUBRIK_FIELD#}</strong></td>
						<td align="center"><strong>{#REQUEST_RUBRIK_FIELD#}</strong></td>
						<td align="center"><strong>{#RUBRIK_FIELD_ALIAS#}</strong></td>
						<td align="center"><strong>{#REQUEST_FIELD_TYPE#}</strong></td>
					</tr>
				</thead>
				<tbody>

					{foreach from=$fields_list item=field_group}

					{if $groups_count > 1}
					<tr class="grey">
						<td colspan="6"><h5>{if $field_group.group_title}{$field_group.group_title}{else}{#REQUEST_FIELD_G_UNKNOW#}{/if}</h5></td>
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
							<a class="rightDir" title="{#REQUEST_INSERT_INFO#}" href="javascript:void(0);" onclick="textSelection2('[tag:rfld:{$field.Id}][', '0]');"><strong>[tag:rfld:{$field.Id}][0]</strong></a>
						</td>
						<td>
							{if $field.rubric_field_alias}
							<a class="rightDir" title="{#REQUEST_INSERT_INFO#}" href="javascript:void(0);" onclick="textSelection2('[tag:rfld:{$field.rubric_field_alias}][', '0]');"><strong>[tag:rfld:{$field.rubric_field_alias}][0]</strong></a>
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

		</div>

	</div>

	<div class="fix"></div>

	<div class="rowElem" id="saveBtn">
		<div class="saveBtn">
		{if $smarty.request.action=='edit'}
			<input {$dis} type="submit" class="basicBtn" value="{#REQUEST_BUTTON_SAVE#}" />
		{else}
			<input {$dis} type="submit" class="basicBtn" value="{#REQUEST_BUTTON_ADD#}" />
		{/if}
		{#REQUEST_OR#}
		{if $smarty.request.action=='edit'}
			<input {$dis} type="submit" class="blackBtn SaveEdit" value="{#REQUEST_BUTTON_SAVE_NEXT#}" />
		{else}
			<input {$dis} type="submit" class="blackBtn" value="{#REQUEST_BUTTON_ADD_NEXT#}" />
		{/if}
			<a style="float:right; height: 20px; padding: 0 10px;" type="submit" class="button redBtn" href="index.php?do=request&cp={$sess}">{#REQUEST_CANCEL#}</a>
		</div>
	</div>

	<div class="fix"></div>

	</form>

</div>

{include file="$codemirror_connect"}
{include file="$codemirror_editor" conn_id="" textarea_id='request_template_main' ctrls='$("#RequestTpl").ajaxSubmit(sett_options);' height=480}
{include file="$codemirror_editor" conn_id="2" textarea_id='request_template_item' ctrls='$("#RequestTpl").ajaxSubmit(sett_options);' height=440}

{literal}
<script>
	$(document).on('change', '#request_alias', function (event) {

		var input = $(this);
		var alias = input.val();

		if (alias > '') {
			$.ajax({
				url: 'index.php?do=request&action=alias&cp=' + $sess,
				data: {
					alias: alias,
					id: $rid
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

{if $smarty.request.action !='new' && $smarty.request.rubric_id !=''}
<script language="Javascript" type="text/javascript">
	var sett_options = {ldelim}
		url: "{$formaction}",
		beforeSubmit: Request,
		success: Response,
		dataType:  'json'
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
			event.preventDefault();
			$("#RequestTpl").ajaxSubmit(sett_options);
			return false;
		{rdelim});

		$(".SaveEdit").click(function(event){ldelim}
			event.preventDefault();
			$("#RequestTpl").ajaxSubmit(sett_options);
			return false;
		{rdelim});

	{rdelim});
</script>
{/if}
