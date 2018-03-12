{if $smarty.request.action=='new'}
	<div class="title"><h5>{#TEMPLATES_TITLE_NEW#}</h5></div>
	<div class="widget" style="margin-top: 0px;"><div class="body">{#TEMPLATES_WARNING2#}</div></div>
{else}
	<div class="title"><h5>{#TEMPLATES_TITLE_EDIT#}</h5></div>
	<div class="widget" style="margin-top: 0px;"><div class="body">{#TEMPLATES_WARNING1#}</div></div>
{/if}

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		 <ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=templates&cp={$sess}" title="">{#TEMPLATES_SUB_TITLE#}</a></li>
			<li><strong class="code">{if $smarty.request.template_title}{$smarty.request.template_title|escape:html}{else}{$row->template_title|escape:html}{/if}</strong></li>
		 </ul>
	</div>
</div>

{if $errors}
	<div class="first">

		<ul class="messages">

			<li class="highlight red">
		{foreach from=$errors item=e}
		{assign var=message value=$e}
			&bull;&nbsp;{$message}<br />
		{/foreach}
			</li>

		</ul>

	</div>
{/if}

<form name="f_tpl" id="f_tpl" method="post" action="index.php?do=templates&action=save" class="mainForm">

	<div class="widget first">
		<div class="head">
			<h5 class="iFrames">{#TEMPLATES_TITLE_EDIT#}</h5>
		</div>

		<div class="rowElem noborder">
			<label>{#TEMPLATES_NAME#}</label>
			<div class="formRight">
				<input name="template_title" type="text" value="{if $smarty.request.template_title}{$smarty.request.template_title|escape:html}{else}{$row->template_title|escape:html}{/if}" maxlength="50" style="width: 250px;" class="mousetrap" />
			</div>
			<div class="fix"></div>
		</div>
	</div>

	{if $php_forbidden==1}
	<div class="first">
		<ul class="messages">
			<li class="highlight red">{#TEMPLATES_USE_PHP#}</li>
		</ul>
	</div>
	{/if}

	<div class="widget first">
		<div class="head">
			<h5 class="iFrames">{#TEMPLATES_HTML#}</h5>
		</div>
		{if !check_permission('template_php')}
		<div class="rowElem">
			<ul class="messages">
				<li class="highlight red aligncenter">
				{#TEMPLATES_REPORT_PHP_ERR#}
				</li>
			</ul>
			<div class="fix"></div>
		</div>
		{/if}
		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
			<tbody>
				<tr>
					<td style="width: 200px;">{#TEMPLATES_TAGS#}</td>
					<td>{#TEMPLATES_HTML#}</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_THEME_FOLDER#}" href="javascript:void(0);" onclick="textSelection('[tag:theme:',']');">[tag:theme:folder]</a></strong>
					</td>
					<td rowspan="28">
						<textarea {$read_only} class="{if $php_forbidden==1}tpl_code_readonly{else}{/if}" wrap="off" style="width:100%; height:100%;" name="template_text" id="template_text">{$row->template_text|default:$prefab|escape}</textarea>
						<ul class="messages" style="margin-top: 10px;">
							<li class="highlight grey">
								{#MAIN_CODEMIRROR_HELP#}
							</li>
						</ul>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_LANGUAGE#}" href="javascript:void(0);" onclick="textSelection('[tag:language]','');">[tag:language]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_PAGENAME#}" href="javascript:void(0);" onclick="textSelection('[tag:sitename]','');">[tag:sitename]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_RUBHEADER#}" href="javascript:void(0);" onclick="textSelection('[tag:rubheader]','');">[tag:rubheader]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_RUBFOOTER#}" href="javascript:void(0);" onclick="textSelection('[tag:rubfooter]','');">[tag:rubfooter]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_TITLE#}" href="javascript:void(0);" onclick="textSelection('[tag:title]','');">[tag:title]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_KEYWORDS#}" href="javascript:void(0);" onclick="textSelection('[tag:keywords]','');">[tag:keywords]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_DESCRIPTION#}" href="javascript:void(0);" onclick="textSelection('[tag:description]','');">[tag:description]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_INDEXFOLLOW#}" href="javascript:void(0);" onclick="textSelection('[tag:robots]','');">[tag:robots]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_CANONICAL#}" href="javascript:void(0);" onclick="textSelection('[tag:canonical]','');">[tag:canonical]</a></strong>
					</td>
				</tr>
				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_PATH#}" href="javascript:void(0);" onclick="textSelection('[tag:path]','');">[tag:path]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_MEDIAPATH#}" href="javascript:void(0);" onclick="textSelection('[tag:mediapath]','');">[tag:mediapath]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_CSS#}" href="javascript:void(0);" onclick="textSelection('[tag:css:',']');">[tag:css:FFF:P]</a></strong>,&nbsp;&nbsp;
						<strong><a class="rightDir" title="{#TEMPLATES_JS#}" href="javascript:void(0);" onclick="textSelection('[tag:js:',']');">[tag:js:FFF:P]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a title="{#TEMPLATES_DOCDB#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:doc:', ']');">[tag:doc:XXX]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a title="{#TEMPLATES_LANGFILE#}" class="rightDir" href="javascript:void(0);" onclick="textSelection('[tag:langfile:', ']');">[tag:langfile:XXX]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_MAINCONTENT#}" href="javascript:void(0);" onclick="textSelection('[tag:maincontent]','');">[tag:maincontent]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_ALIAS#}" href="javascript:void(0);" onclick="textSelection('[tag:alias]','');">[tag:alias]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_DOMAIN#}" href="javascript:void(0);" onclick="textSelection('[tag:domain]','');">[tag:domain]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_HOME#}" href="javascript:void(0);" onclick="textSelection('[tag:home]','');">[tag:home]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_PRINTLINK#}" href="javascript:void(0);" onclick="textSelection('[tag:printlink]','');">[tag:printlink]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_BREADCRUMB#}" href="javascript:void(0);" onclick="textSelection('[tag:breadcrumb]','');">[tag:breadcrumb]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_TEASER#}" href="javascript:void(0);" onclick="textSelection('[tag:teaser:',']');">[tag:teaser:XXX]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_SYSBLOCK#}" href="javascript:void(0);" onclick="textSelection('[tag:sysblock:',']');">[tag:sysblock:XXX]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_NAVIGATION#}" href="javascript:void(0);" onclick="textSelection('[tag:navigation:',']');">[tag:navigation:XXX]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_LANG#}" href="javascript:void(0);" onclick="textSelection('[tag:lang:]\n','\n[tag:/lang]');">[tag:lang:XX][/tag:lang]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_IF_PRINT#}" href="javascript:void(0);" onclick="textSelection('[tag:if_print]\n','\n[/tag:if_print]');">[tag:if_print][/tag:if_print]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_DONOT_PRINT#}" href="javascript:void(0);" onclick="textSelection('[tag:if_notprint]\n','\n[/tag:if_notprint]');">[tag:if_notprint][/tag:if_notprint]</a></strong>
					</td>
				</tr>

				<tr>
					<td>
						<strong><a class="rightDir" title="{#TEMPLATES_VERSION#}" href="javascript:void(0);" onclick="textSelection('[tag:version]','');">[tag:version]</a></strong>
					</td>
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
			</tbody>
		</table>
		<div class="rowElem" id="saveBtn">
			<div class="saveBtn">
				{if $smarty.request.action == 'new'}
				<input class="basicBtn" type="submit" value="{#TEMPLATES_BUTTON_ADD#}" />
				{else}
				<input class="basicBtn" type="submit" value="{#TEMPLATES_BUTTON_SAVE#}" />
				{/if}
				{#TEMPLATES_OR#}
				{if $smarty.request.action=='edit'}
				<input type="hidden" name="Id" value="{$smarty.request.Id}">
				<input type="submit" class="blackBtn SaveEdit" name="next_edit" value="{#TEMPLATES_BUTTON_SAVE_NEXT#}" />
				{else}
				<input type="submit" class="blackBtn" name="next_edit" value="{#TEMPLATES_BUTTON_ADD_NEXT#}" />
				{/if}
			</div>
		</div>
	</div>
</form>
{if $smarty.request.action != 'new'}
	<script language="Javascript" type="text/javascript">
	var sett_options = {ldelim}
		url: 'index.php?do=templates&action=save',
		dataType: 'json',
		data: {ldelim} ajax: '1' {rdelim},
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
			  $("#f_tpl").ajaxSubmit(sett_options);
			return false;
		{rdelim});

		$(".SaveEdit").click(function(e){ldelim}
			if (e.preventDefault) {ldelim}
				e.preventDefault();
			{rdelim} else {ldelim}
				// internet explorer
				e.returnValue = false;
			{rdelim}

			$("#f_tpl").ajaxSubmit(sett_options);
			return false;
		{rdelim});

	{rdelim});
</script>
{/if}

{include file="$codemirror_connect"}

{if $php_forbidden == 1}
	{include file="$codemirror_editor" textarea_id='template_text' ctrls='$("#f_tpl").ajaxSubmit(sett_options);' height='720' readonly='true'}
{else}
	{include file="$codemirror_editor" textarea_id='template_text' ctrls='$("#f_tpl").ajaxSubmit(sett_options);' height='720'}
{/if}
