{if $smarty.request.action=='new'}
<div class="title">
	<h5>{#RUBRIK_TEMPLATE_NEW#}</h5>
</div>
{else}
<div class="title">
	<h5>{#RUBRIK_TEMPLATE_EDIT#}</h5>
	<div class="num">
		<a class="basicNum greenNum" href="index.php?do=rubs&action=tmpls&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TMPLS_BUTTON#}</a>
	</div>
</div>
{/if}

<div class="widget" style="margin-top: 0px;">
	<div class="body">{#RUBRIK_TEMPLATE_TIP#}</div>
</div>

<table class="first tableButtons" cellpadding="0" cellspacing="0" width="100%" id="rubricButtons">
	<col width="20%">
	<col width="20%">
	<col width="20%">
	<col width="20%">
	<col width="20%">
	<tr>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=edit&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_FIELDS#}</a>
		</td>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=ftlist&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_FTEMPLATES#}</a>
		</td>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=fieldsgroups&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_FGROUPS#}</a>
		</td>
		<td>
			{if check_permission('rubric_code')}
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=code&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_CODE#}</a>
			{/if}
		</td>
		<td>
			<a class="button basicBtn topBtn" href="index.php?do=rubs&action=rules&Id={$smarty.request.Id|escape}&cp={$sess}">{#RUBRIC_TABLE_BTN_RULES#}</a>
		</td>
	</tr>
</table>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=rubs&cp={$sess}">{#RUBRIK_SUB_TITLE#}</a></li>
			{if $smarty.request.action=='new'}
			<li>{#RUBRIK_TEMPLATE_NEW#}</li>
			<li><strong class="code">{$rubric->rubric_title|escape}</strong></li>
			{else}
			<li>{#RUBRIK_TEMPLATE_EDIT#}</li>
			<li><strong class="code">{$rubric->rubric_title|escape}</strong></li>
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

<form name="RubricTpl" id="RubricTpl" method="post" action="{$formaction}" class="mainFrom">

<div class="widget first">

	<ul class="tabs">
		<li class="activeTab">
			<a href="#tab1">{#RUBRIK_HTML#}</a>
		</li>
		<li>
			<a href="#tab2">{#RUBRIK_HTML_2#}</a>
		</li>
		<li>
			<a href="#tab2_1">{#RUBRIK_HTML_2_1#}</a>
		</li>
		<li>
			<a href="#tab3">{#RUBRIK_HTML_3#}</a>
		</li>
		<li>
			<a href="#tab4">{#RUBRIK_HTML_4#}</a>
		</li>
	</ul>

	<div class="tab_container">

		<div id="tab1" class="tab_content" style="display: block;">

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
							<textarea {$read_only} class="{if $php_forbidden==1}tpl_code_readonly{else}{/if}" style="width:100%; height:350px" name="rubric_template" id="rubric_template">{$rubric->rubric_template|default:$prefab|escape:html}</textarea>
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
							<a class="rightDir" title="{#RUBRIK_TAG_SYSBLOCK#}" href="javascript:void(0);" onclick="textSelection('[tag:sysblock:', ']');"><strong>[tag:sysblock:XXX]</strong></a>
						</td>
					</tr>
					<tr>
						<td>
							<a class="rightDir" title="{#RUBRIK_TAG_TEASER#}" href="javascript:void(0);" onclick="textSelection('[tag:teaser:', ']');"><strong>[tag:teaser:XXX]</strong></a>
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
						<td><strong><a title="{#RUBRIK_THUMBNAIL#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:X000x000:[tag:fld:', ']]');">[tag:X000x000:[tag:fld:YYY]]</a></strong></td>
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

		</div>


		<div id="tab2" class="tab_content" style="display: none;">

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
							<strong><a class="rightDir" title="{#RUBRIK_TEMPLATES_THEME_FOLDER#}" href="javascript:void(0);" onclick="textSelection2('[tag:theme:',']');">[tag:theme:folder]</a></strong>
						</td>
						<td rowspan="10" colspan="2"><textarea {$read_only} class="{if $php_forbidden==1}tpl_code_readonly{else}{/if}" style="width:100%; height:200px" name="rubric_header_template" id="rubric_header_template">{$rubric->rubric_header_template|default:$prefab|escape:html}</textarea></td>
					</tr>

					<tr>
						<td>
							<strong><a class="rightDir" title="{#RUBRIK_TEMPLATES_PAGENAME#}" href="javascript:void(0);" onclick="textSelection2('[tag:sitename]','');">[tag:sitename]</a></strong>
						</td>
					</tr>

					<tr>
						<td>
							<strong><a class="rightDir" title="{#RUBRIK_TEMPLATES_TITLE#}" href="javascript:void(0);" onclick="textSelection2('[tag:title]','');">[tag:title]</a></strong>
						</td>
					</tr>

					<tr>
						<td>
							<strong><a class="rightDir" title="{#RUBRIK_TEMPLATES_KEYWORDS#}" href="javascript:void(0);" onclick="textSelection2('[tag:keywords]','');">[tag:keywords]</a></strong>
						</td>
					</tr>

					<tr>
						<td>
							<strong><a class="rightDir" title="{#RUBRIK_TEMPLATES_DESCRIPTION#}" href="javascript:void(0);" onclick="textSelection2('[tag:description]','');">[tag:description]</a></strong>
						</td>
					</tr>

					<tr>
						<td>
							<strong><a class="rightDir" title="{#RUBRIK_TEMPLATES_INDEXFOLLOW#}" href="javascript:void(0);" onclick="textSelection2('[tag:robots]','');">[tag:robots]</a></strong>
						</td>
					</tr>

					<tr>
						<td>
							<strong><a class="rightDir" title="{#RUBRIK_TEMPLATES_CSS#}" href="javascript:void(0);" onclick="textSelection2('[tag:css:]','');">[tag:css:FFF:P]</a></strong>,&nbsp;&nbsp;
							<strong><a class="rightDir" title="{#RUBRIK_TEMPLATES_JS#}" href="javascript:void(0);" onclick="textSelection2('[tag:js:]','');">[tag:js:FFF:P]</a></strong>
						</td>
					</tr>

					<tr>
						<td>
							<strong><a class="rightDir" title="{#RUBRIK_TEMPLATES_PATH#}" href="javascript:void(0);" onclick="textSelection2('[tag:path]','');">[tag:path]</a></strong>
						</td>
					</tr>

					<tr>
						<td>
							<strong><a class="rightDir" title="{#RUBRIK_TEMPLATES_MEDIAPATH#}" href="javascript:void(0);" onclick="textSelection2('[tag:mediapath]','');">[tag:mediapath]</a></strong>
						</td>
					</tr>
					<tr>
						<td>

						</td>
					</tr>
					<tr>
						<td><strong>HTML Tags</strong></td>
						<td>
							|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<a href=&quot;&quot; title=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('\t', '');"><strong>TAB</strong></a>&nbsp;|
						</td>
					</tr>
				</tbody>
			</table>
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<col width="3%" />
				<col width="25%" />
				<col width="15%" />
				<col width="15%" />
				<col width="15%" />
				<col width="38%" />
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
							<a class="rightDir" title="{#RUBRIK_INSERT_HELP#}" href="javascript:void(0);" onclick="textSelection2('[tag:rfld:{$field.Id}][0]', '');"><strong>[tag:rfld:{$field.Id}][150]</strong></a>
						</td>
						<td>
							{if $field.rubric_field_alias}
							<a class="rightDir" title="{#RUBRIK_INSERT_HELP#}" href="javascript:void(0);" onclick="textSelection2('[tag:rfld:{$field.rubric_field_alias}][0]', '');"><strong>[tag:rfld:{$field.rubric_field_alias}][0]</strong></a>
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

		</div>



		<div id="tab2_1" class="tab_content" style="display: none;">

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
							<strong><a class="rightDir" title="{#RUBRIK_TEMPLATES_TITLE#}" href="javascript:void(0);" onclick="textSelection2_1('[tag:title]','');">[tag:title]</a></strong>
						</td>
						<td rowspan="5" colspan="2"><textarea {$read_only} class="{if $php_forbidden==1}tpl_code_readonly{else}{/if}" style="width:100%; height:200px" name="rubric_footer_template" id="rubric_footer_template">{$rubric->rubric_footer_template|default:$prefab|escape:html}</textarea></td>
					</tr>

					<tr>
						<td>
							<strong><a class="rightDir" title="{#RUBRIK_TEMPLATES_CSS#}" href="javascript:void(0);" onclick="textSelection2('[tag:css:]','');">[tag:css:FFF:P]</a></strong>,&nbsp;&nbsp;
							<strong><a class="rightDir" title="{#RUBRIK_TEMPLATES_JS#}" href="javascript:void(0);" onclick="textSelection2_1('[tag:js:]','');">[tag:js:FFF:P]</a></strong>
						</td>
					</tr>

					<tr>
						<td>
							<strong><a class="rightDir" title="{#RUBRIK_TEMPLATES_PATH#}" href="javascript:void(0);" onclick="textSelection2_1('[tag:path]','');">[tag:path]</a></strong>
						</td>
					</tr>

					<tr>
						<td>
							<strong><a class="rightDir" title="{#RUBRIK_TEMPLATES_MEDIAPATH#}" href="javascript:void(0);" onclick="textSelection2_1('[tag:mediapath]','');">[tag:mediapath]</a></strong>
						</td>
					</tr>
					<tr>
						<td>

						</td>
					</tr>
					<tr>
						<td><strong>HTML Tags</strong></td>
						<td>
							|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<a href=&quot;&quot; title=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection2_1('\t', '');"><strong>TAB</strong></a>&nbsp;|
						</td>
					</tr>
				</tbody>
			</table>
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
				<col width="3%" />
				<col width="25%" />
				<col width="15%" />
				<col width="15%" />
				<col width="15%" />
				<col width="38%" />
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
							<a class="rightDir" title="{#RUBRIK_INSERT_HELP#}" href="javascript:void(0);" onclick="textSelection2('[tag:rfld:{$field.Id}][0]', '');"><strong>[tag:rfld:{$field.Id}][150]</strong></a>
						</td>
						<td>
							{if $field.rubric_field_alias}
							<a class="rightDir" title="{#RUBRIK_INSERT_HELP#}" href="javascript:void(0);" onclick="textSelection2('[tag:rfld:{$field.rubric_field_alias}][0]', '');"><strong>[tag:rfld:{$field.rubric_field_alias}][0]</strong></a>
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

		</div>


		<div id="tab3" class="tab_content" style="display: none;">

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
						<td><strong><a title="{#RUBRIK_RUB_INFO#}" class="rightDir" href="javascript:void(0);" onclick="jAlert('{#RUBRIK_SELECT_IN_LIST#}','{#RUBRIK_TEMPLATE_ITEMS#}');">[tag:rfld:ID][XXX]</a></strong></td>
						<td rowspan="14"><textarea {$dis} name="rubric_teaser_template" id="rubric_teaser_template" wrap="off" style="width:100%; height:340px">{$rubric->rubric_teaser_template|escape|default:''}</textarea></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_DOCID_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection3('[tag:docid]', '');">[tag:docid]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_DOCTITLE_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection3('[tag:doctitle]', '');">[tag:doctitle]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_LINK_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection3('[tag:link]', '');">[tag:link]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_DOCDATE_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection3('[tag:docdate]', '');">[tag:docdate]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_DOCTIME_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection3('[tag:doctime]', '');">[tag:doctime]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_DATE_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection3('[tag:date:', ']');">[tag:date:X]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_DOCAUTHOR_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection3('[tag:docauthor]', '');">[tag:docauthor]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_DOCAUTHOR_AVATAR#}" class="rightDir" href="javascript:void(0);" onclick="textSelection3('[tag:docauthoravatar:]', '');">[tag:docauthoravatar:XXX]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_VIEWS_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection3('[tag:docviews]', '');">[tag:docviews]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_COMMENTS_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection3('[tag:doccomments]', '');">[tag:doccomments]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_PATH#}" class="rightDir" href="javascript:void(0);" onclick="textSelection3('[tag:path]', '');">[tag:path]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_MEDIAPATH#}" class="rightDir" href="javascript:void(0);" onclick="textSelection3('[tag:mediapath]', '');">[tag:mediapath]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_THUMBNAIL#}" class="rightDir" href="javascript:void(0);" onclick="textSelection3('[tag:X000x000:[tag:fld:', ']]');">[tag:X000x000:[tag:fld:YYY]]</a></strong></td>
					</tr>
					<tr>
						<td><strong>HTML Tags</strong></td>
						<td>
							|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<a href=&quot;&quot; title=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection3('\t', '');"><strong>TAB</strong></a>&nbsp;|
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
							<a class="rightDir" title="{#RUBRIK_INSERT_HELP#}" href="javascript:void(0);" onclick="textSelection3('[tag:rfld:{$field.Id}][0]', '');"><strong>[tag:rfld:{$field.Id}][150]</strong></a>
						</td>
						<td>
							{if $field.rubric_field_alias}
							<a class="rightDir" title="{#RUBRIK_INSERT_HELP#}" href="javascript:void(0);" onclick="textSelection3('[tag:rfld:{$field.rubric_field_alias}][0]', '');"><strong>[tag:rfld:{$field.rubric_field_alias}][150]</strong></a>
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

		</div>


		<div id="tab4" class="tab_content" style="display: none;">

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
						<td><strong><a title="{#RUBRIK_RUB_INFO#}" class="rightDir" href="javascript:void(0);" onclick="jAlert('{#RUBRIK_SELECT_IN_LIST#}','{#RUBRIK_TEMPLATE_ITEMS#}');">[tag:rfld:ID][XXX]</a></strong></td>
						<td rowspan="15"><textarea {$dis} name="rubric_admin_teaser_template" id="rubric_admin_teaser_template" wrap="off" style="width:100%; height:340px">{$rubric->rubric_admin_teaser_template|escape|default:''}</textarea></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_DOCID_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection4('[tag:docid]', '');">[tag:docid]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_DOCTITLE_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection4('[tag:doctitle]', '');">[tag:doctitle]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_LINK_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection4('[tag:link]', '');">[tag:link]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_LINK_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection4('[tag:adminlink]', '');">[tag:adminlink]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_DOCDATE_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection4('[tag:docdate]', '');">[tag:docdate]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_DOCTIME_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection4('[tag:doctime]', '');">[tag:doctime]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_DATE_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection4('[tag:date:', ']');">[tag:date:X]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_DOCAUTHOR_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection4('[tag:docauthor]', '');">[tag:docauthor]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_DOCAUTHOR_AVATAR#}" class="rightDir" href="javascript:void(0);" onclick="textSelection4('[tag:docauthoravatar:]', '');">[tag:docauthoravatar:XXX]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_VIEWS_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection4('[tag:docviews]', '');">[tag:docviews]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_COMMENTS_INFO#}" class="rightDir" href="javascript:void(0);" onclick="textSelection4('[tag:doccomments]', '');">[tag:doccomments]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_PATH#}" class="rightDir" href="javascript:void(0);" onclick="textSelection4('[tag:path]', '');">[tag:path]</a></strong></td>
					</tr>
					<tr>
						<td><strong><a title="{#RUBRIK_MEDIAPATH#}" class="rightDir" href="javascript:void(0);" onclick="textSelection4('[tag:mediapath]', '');">[tag:mediapath]</a></strong></td>
					</tr>
					<tr>
						<td>
							<strong><a title="{#RUBRIK_THUMBNAIL#}" class="rightDir" href="javascript:void(0);" onclick="textSelection4('[tag:X000x000:[tag:fld:', ']]');">[tag:X000x000:[tag:fld:YYY]]</a></strong>
						</td>
					</tr>
					<tr>
						<td><strong>HTML Tags</strong></td>
						<td>
							|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<a href=&quot;&quot; title=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('\t', '');"><strong>TAB</strong></a>&nbsp;|&nbsp;
							<a href="javascript:void(0);" onclick="textSelection4('<img src=&quot;[tag:c50x50:[tag:rfld:XXX][img]]&quot; style=&quot;float: left; margin-right: 15px;&quot; alt=&quot;&quot; class=&quot;rounded&quot;/>\r\n<h6>[tag:doctitle]</h6>\r\n[tag:rfld:XXX][-100]\r\n', '');"><strong>Default Teaser</strong></a>&nbsp;|
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
							<a class="rightDir" title="{#RUBRIK_INSERT_HELP#}" href="javascript:void(0);" onclick="textSelection4('[tag:rfld:{$field.Id}][0]', '');"><strong>[tag:rfld:{$field.Id}][150]</strong></a>
						</td>
						<td>
							{if $field.rubric_field_alias}
							<a class="rightDir" title="{#RUBRIK_INSERT_HELP#}" href="javascript:void(0);" onclick="textSelection4('[tag:rfld:{$field.rubric_field_alias}][0]', '');"><strong>[tag:rfld:{$field.rubric_field_alias}][150]</strong></a>
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

		</div>

	</div>

	<div class="fix"></div>

	<div class="rowElem" id="saveBtn">
		<div class="saveBtn">
			<input type="hidden" name="Id" value="{$smarty.request.Id|escape}" />
			<input class="basicBtn" type="submit" value="{#RUBRIK_BUTTON_TPL#}" />
			{#RUBRIK_OR#}
			<input type="submit" class="blackBtn SaveEdit" name="next_edit" value="{#RUBRIK_BUTTON_TPL_NEXT#}" />
		</div>
	</div>

</div>

</form>

<div class="fix"></div>

{include file="$codemirror_connect"}
{include file="$codemirror_editor" conn_id="" textarea_id='rubric_template' ctrls='$("#RubricTpl").ajaxSubmit(sett_options);' height=500}
{include file="$codemirror_editor" conn_id="2" textarea_id='rubric_header_template' ctrls='$("#RubricTpl").ajaxSubmit(sett_options);' height=420}
{include file="$codemirror_editor" conn_id="2_1" textarea_id='rubric_footer_template' ctrls='$("#RubricTpl").ajaxSubmit(sett_options);' height=420}
{include file="$codemirror_editor" conn_id="3" textarea_id='rubric_teaser_template' ctrls='$("#RubricTpl").ajaxSubmit(sett_options);' height=420}
{include file="$codemirror_editor" conn_id="4" textarea_id='rubric_admin_teaser_template' ctrls='$("#RubricTpl").ajaxSubmit(sett_options);' height=420}

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
