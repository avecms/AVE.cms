<script type="text/javascript">
	$nid = parseInt('{$nid}');
	$sess = '{$sess}';

	var clipboard = new Clipboard('.copyBtn');
</script>

{if $smarty.request.action == 'new'}
<div class="title">
	<h5>{#NAVI_SUB_TITLE4#}</h5>
</div>
{else}
<div class="title">
	<h5>{#NAVI_SUB_TITLE3#}</h5>
</div>
{/if}

<div class="widget" style="margin-top: 0px;">
	<div class="body">{#NAVI_TIP_TEMPLATE2#}</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=navigation&cp={$sess}" title="">{#NAVI_SUB_TITLE#}</a></li>
			{if $smarty.request.action == 'new'}
			<li>{#NAVI_SUB_TITLE4#}</li>
			{else}
			<li>{#NAVI_SUB_TITLE3#}</li>
			<li><strong class="code">{$navigation->title|escape}</strong></li>
			{/if}
		</ul>
	</div>
</div>

<form name="navigation_template" id="navigation_template" method="post" action="{$form_action}" class="mainForm">

<div class="widget first">
	<div class="head">
		<h5 class="iFrames">{#NAVI_SUB_TITLE3#}</h5>
		<div class="num">
			<a class="basicNum topDir" href="index.php?do=navigation&cp={$sess}">{#NAVI_RETURN_TO_LIST#}</a>
		</div>
		<div class="num">
			<a class="greenNum" href="index.php?do=navigation&action=entries&navigation_id={$smarty.request.navigation_id}&cp={$sess}">{#NAVI_EDIT_ITEMS#}</a>
		</div>
	</div>

	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<tr class="noborder">
			<td width="200">
				<strong>{#NAVI_TITLE#}</strong>
			</td>
			<td>
				<input class="mousetrap" style="width:400px" name="title" type="text" id="title" value="{$navigation->title|default:$smarty.request.navigation_title_new|escape}">
			</td>
		</tr>
		<tr>
			<td>
				<div class="nowrap">
					<strong><a class="toprightDir" title="{#NAVI_I#}">[?]</a></strong> {#NAVI_ALIAS#}:
				</div>
			</td>
			<td colspan="3">
				<div class="pr12">
					<input type="text" name="alias" id="alias" value="{if $smarty.request.navigation_id != ''}{$navigation->alias}{else}{$smarty.request.alias}{/if}" class="mousetrap" data-accept="{#NAVI_ACCEPT#}" data-error-syn="{#NAVI_ER_SYN#}" data-error-exists="{#NAVI_ER_EXISTS#}" placeholder="{#NAVI_ALIAS#}" maxlength="20" style="width: 200px;" autocomplete="off" />&nbsp;
					<input type="text" id="alias_tag" value="[tag:navigation:{if $smarty.request.navigation_id != ''}{if $navigation->alias != ''}{$navigation->alias}{else}{$navigation->navigation_id}{/if}{else}{$smarty.request.alias}{/if}]" readonly size="40" class="mousetrap" style="width: 200px;" />
					<a style="text-align: center; padding: 5px 3px 4px 3px;" class="whiteBtn copyBtn" href="javascript:void(0);" data-clipboard-action="copy" data-clipboard-target="#alias_tag">
						<img style="margin-top: -3px; position: relative; top: 4px; padding: 0 3px;" class="clippy" src="{$ABS_PATH}admin/templates/images/clippy.svg" width="13">
					</a>
				</div>
			</td>
		</tr>
		<tr>
			<td width="200"><strong>{#NAVI_PRINT_TYPE#}</strong></td>
			<td>
				<select name="expand_ext" style="width: 300px;">
					<option value="1"{if $navigation->expand_ext == 1} selected{/if}/>{#NAVI_EXPAND_ALL#}</option>
					<option value="0"{if $navigation->expand_ext == 0} selected{/if}/>{#NAVI_EXPAND_WAY#}</option>
					<option value="2"{if $navigation->expand_ext == 2} selected{/if}/>{#NAVI_EXPAND_LEVEL#}</option>
				</select>
			</td>
		</tr>

		<tr>
			<td width="200"><strong>{#NAVI_GROUPS#}</strong></td>
			<td>
				<select class="mousetrap select" name="user_group[]" multiple="multiple" size="5" style="width:300px">
					{if $smarty.request.action=='new'}
						{foreach from=$groups item=group}
							<option value="{$group->user_group}" selected="selected">{$group->user_group_name|escape}</option>
						{/foreach}
					{else}
						{foreach from=$groups item=group}
							{assign var='select' value=''}
							{if $group->user_group}
								{if (in_array($group->user_group, $navigation->user_group))}
									{assign var='select' value=' selected="selected"'}
								{/if}
							{/if}
							<option value="{$group->user_group}"{$select}>{$group->user_group_name|escape}</option>
						{/foreach}
					{/if}
				</select>
			</td>
		</tr>
	</table>
</div>

<div class="widget first">

	<div class="head">
		<h5 class="iFrames">{#NAVI_LEVEL1#}</h5>
	</div>

	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<tr class="noborder">
			<td><strong>{#NAVI_LAVEL_TEMPL#}</strong><br />
				<strong><a class="rightDir" style="cursor: pointer;" title="{#NAVI_TAG#}" onclick="textSelection_1_1('[tag:content]','');">[tag:content]</a></strong>
			</td>
			<td>
				<div class="pr12">
					<textarea style="width:100%" name="level1_begin" rows="12" id="level1_tpl">{$navigation->level1_begin|escape}</textarea>
				</div>
			</td>
		</tr>

		<tr>
			<td>HTML Tags</td>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<a href=&quot;&quot; title=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_1('\t', '');"><strong>TAB</strong></a>&nbsp;|
			</td>
		</tr>

		<tr>
			<td width="200">
				<strong>{#NAVI_LINK_INACTIVE#}</strong><br />
				<strong><a class="rightDir" style="cursor: pointer;" title="{#NAVI_LINK_ID#}" onclick="textSelection_1_2('[tag:linkid]','');">[tag:linkid]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_NAME#}" onclick="textSelection_1_2('[tag:linkname]','');">[tag:linkname]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_URL#}" onclick="textSelection_1_2('[tag:link]','');">[tag:link]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_TARGET#}" onclick="textSelection_1_2('[tag:target]','');">[tag:target]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_DESCR#}" onclick="textSelection_1_2('[tag:desc]','');">[tag:desc]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE#}" onclick="textSelection_1_2('[tag:img]','');">[tag:img]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE_DESCR#}" onclick="textSelection_1_2('[tag:linkid]','');">[tag:img_act]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE_ID#}" onclick="textSelection_1_2('[tag:img_id]','');">[tag:img_id]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS Style" onclick="textSelection_1_2('[tag:css_style]','');">[tag:css_style]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS ID" onclick="textSelection_1_2('[tag:css_id]','');">[tag:css_id]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS Class" onclick="textSelection_1_2('[tag:css_class]','');">[tag:css_class]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_PLACE_INSERT#}" onclick="textSelection_1_2('[tag:level:2]','');">[tag:level:2]</a></strong>
			</td>
			<td>
				<div class="pr12">
					<textarea style="width:100%" name="level1" rows="12" id="level1">{$navigation->level1|escape}</textarea>
				</div>
			</td>
		</tr>

		<tr>
			<td>HTML Tags</td>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<a href=&quot;&quot; title=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('\t', '');"><strong>TAB</strong></a>&nbsp;|
			</td>
		</tr>
		<tr>
			<td>{#NAVI_CONDITIONS#}</td>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('[tag:if_first]', '[tag:/if]');"><strong>[tag:if_first]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('[tag:if_not_first]', '[tag:/if]');"><strong>[tag:if_not_first]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('[tag:if_last]', '[tag:/if]');"><strong>[tag:if_last]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('[tag:if_not_last]', '[tag:/if]');"><strong>[tag:if_not_last]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('[tag:if_every:]', '[tag:/if]');"><strong>[tag:if_every:XXX]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('[tag:if_not_every:]', '[tag:/if]');"><strong>[tag:if_not_every:XXX]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('[tag:if_sub_level]', '[tag:/if]');"><strong>[tag:if_sub_level]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('[tag:if_no_sub_level]', '[tag:/if]');"><strong>[tag:if_no_sub_level]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_2('[tag:if_every:2]{#NAVI_ITEM_EVEN#}[tag:if_else]{#NAVI_ITEM_ODD#}[tag:/if]', '');"><strong>{#NAVI_EXAMPLE#}</strong></a>
				&nbsp;|
			</td>
		</tr>

		<tr>
			<td width="200">
				<strong>{#NAVI_LINK_ACTIVE#}</strong><br />
				<strong><a class="rightDir" style="cursor: pointer;" title="{#NAVI_LINK_ID#}" onclick="textSelection_1_3('[tag:linkid]','');">[tag:linkid]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_NAME#}" onclick="textSelection_1_3('[tag:linkname]','');">[tag:linkname]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_URL#}" onclick="textSelection_1_3('[tag:link]','');">[tag:link]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_TARGET#}" onclick="textSelection_1_3('[tag:target]','');">[tag:target]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_DESCR#}" onclick="textSelection_1_3('[tag:desc]','');">[tag:desc]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE#}" onclick="textSelection_1_3('[tag:img]','');">[tag:img]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE_DESCR#}" onclick="textSelection_1_3('[tag:linkid]','');">[tag:img_act]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE_ID#}" onclick="textSelection_1_3('[tag:img_id]','');">[tag:img_id]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS Style" onclick="textSelection_1_3('[tag:css_style]','');">[tag:css_style]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS ID" onclick="textSelection_1_3('[tag:css_id]','');">[tag:css_id]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS Class" onclick="textSelection_1_3('[tag:css_class]','');">[tag:css_class]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_PLACE_INSERT#}" onclick="textSelection_1_3('[tag:level:2]','');">[tag:level:2]</a></strong>
			</td>
			<td><div class="pr12"><textarea style="width:100%" name="level1_active" rows="12" id="level1_active">{$navigation->level1_active|escape}</textarea></div></td>
		</tr>

		<tr>
			<td>HTML Tags</td>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<a href=&quot;&quot; title=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('\t', '');"><strong>TAB</strong></a>&nbsp;|
			</td>
		</tr>
		<tr>
			<td>{#NAVI_CONDITIONS#}</td>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('[tag:if_first]', '[tag:/if]');"><strong>[tag:if_first]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('[tag:if_not_first]', '[tag:/if]');"><strong>[tag:if_not_first]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('[tag:if_last]', '[tag:/if]');"><strong>[tag:if_last]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('[tag:if_not_last]', '[tag:/if]');"><strong>[tag:if_not_last]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('[tag:if_every:]', '[tag:/if]');"><strong>[tag:if_every:XXX]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('[tag:if_not_every:]', '[tag:/if]');"><strong>[tag:if_not_every:XXX]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('[tag:if_sub_level]', '[tag:/if]');"><strong>[tag:if_sub_level]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('[tag:if_no_sub_level]', '[tag:/if]');"><strong>[tag:if_no_sub_level]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_1_3('[tag:if_every:2]{#NAVI_ITEM_EVEN#}[tag:if_else]{#NAVI_ITEM_ODD#}[tag:/if]', '');"><strong>{#NAVI_EXAMPLE#}</strong></a>
				&nbsp;|
			</td>
		</tr>
	</table>
</div>


<div class="widget first">

	<div class="head{if $navigation->level2_begin == ''} closed{/if}">
		<h5 class="iFrames">{#NAVI_LEVEL2#}</h5>
	</div>

	<div style="display: block;">
	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<tr class="noborder">
			<td>
				<strong>{#NAVI_LAVEL_TEMPL#}</strong><br />
				<strong><a class="rightDir" style="cursor: pointer;" title="{#NAVI_TAG#}" onclick="textSelection_2_1('[tag:content]','');">[tag:content]</a></strong>
			</td>
			<td>
				<div class="pr12">
					<textarea style="width:100%" name="level2_begin" rows="12" id="level2_tpl">{$navigation->level2_begin|escape}</textarea>
				</div>
			</td>
		</tr>
		<tr>
			<td>HTML Tags</td>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<a href=&quot;&quot; title=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_1('\t', '');"><strong>TAB</strong></a>&nbsp;|
			</td>
		</tr>

		<tr>
			<td width="200">
				<strong>{#NAVI_LINK_INACTIVE#}</strong><br />
				<strong><a class="rightDir" style="cursor: pointer;" title="{#NAVI_LINK_ID#}" onclick="textSelection_2_2('[tag:linkid]','');">[tag:linkid]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_NAME#}" onclick="textSelection_2_2('[tag:linkname]','');">[tag:linkname]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_URL#}" onclick="textSelection_2_2('[tag:link]','');">[tag:link]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_TARGET#}" onclick="textSelection_2_2('[tag:target]','');">[tag:target]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_DESCR#}" onclick="textSelection_2_2('[tag:desc]','');">[tag:desc]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE#}" onclick="textSelection_2_2('[tag:img]','');">[tag:img]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE_DESCR#}" onclick="textSelection_2_2('[tag:linkid]','');">[tag:img_act]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE_ID#}" onclick="textSelection_2_2('[tag:img_id]','');">[tag:img_id]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS Style" onclick="textSelection_2_2('[tag:css_style]','');">[tag:css_style]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS ID" onclick="textSelection_2_2('[tag:css_id]','');">[tag:css_id]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS Class" onclick="textSelection_2_2('[tag:css_class]','');">[tag:css_class]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_PLACE_INSERT#}" onclick="textSelection_2_2('[tag:level:3]','');">[tag:level:3]</a></strong>
			</td>
			<td>
				<div class="pr12"><textarea style="width:100%" name="level2" rows="12" id="level2">{$navigation->level2|escape}</textarea></div>
			</td>
		</tr>
		<tr>
			<td>HTML Tags</td>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<a href=&quot;&quot; title=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('\t', '');"><strong>TAB</strong></a>&nbsp;|
			</td>
		</tr>
		<tr>
			<td>{#NAVI_CONDITIONS#}</td>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('[tag:if_first]', '[tag:/if]');"><strong>[tag:if_first]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('[tag:if_not_first]', '[tag:/if]');"><strong>[tag:if_not_first]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('[tag:if_last]', '[tag:/if]');"><strong>[tag:if_last]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('[tag:if_not_last]', '[tag:/if]');"><strong>[tag:if_not_last]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('[tag:if_every:]', '[tag:/if]');"><strong>[tag:if_every:XXX]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('[tag:if_not_every:]', '[tag:/if]');"><strong>[tag:if_not_every:XXX]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('[tag:if_sub_level]', '[tag:/if]');"><strong>[tag:if_sub_level]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('[tag:if_no_sub_level]', '[tag:/if]');"><strong>[tag:if_no_sub_level]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_2('[tag:if_every:2]{#NAVI_ITEM_EVEN#}[tag:if_else]{#NAVI_ITEM_ODD#}[tag:/if]', '');"><strong>{#NAVI_EXAMPLE#}</strong></a>
				&nbsp;|
			</td>
		</tr>
		<tr>
			<td width="200">
				<strong>{#NAVI_LINK_ACTIVE#}</strong><br />
				<strong><a class="rightDir" style="cursor: pointer;" title="{#NAVI_LINK_ID#}" onclick="textSelection_2_3('[tag:linkid]','');">[tag:linkid]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_NAME#}" onclick="textSelection_2_3('[tag:linkname]','');">[tag:linkname]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_URL#}" onclick="textSelection_2_3('[tag:link]','');">[tag:link]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_TARGET#}" onclick="textSelection_2_3('[tag:target]','');">[tag:target]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_DESCR#}" onclick="textSelection_2_3('[tag:desc]','');">[tag:desc]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE#}" onclick="textSelection_2_3('[tag:img]','');">[tag:img]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE_DESCR#}" onclick="textSelection_2_3('[tag:linkid]','');">[tag:img_act]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE_ID#}" onclick="textSelection_2_3('[tag:img_id]','');">[tag:img_id]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS Style" onclick="textSelection_2_3('[tag:css_style]','');">[tag:css_style]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS ID" onclick="textSelection_2_3('[tag:css_id]','');">[tag:css_id]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS Class" onclick="textSelection_2_3('[tag:css_class]','');">[tag:css_class]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_PLACE_INSERT#}" onclick="textSelection_2_3('[tag:level:3]','');">[tag:level:3]</a></strong>
			</td>
			<td><div class="pr12"><textarea style="width:100%" name="level2_active" rows="12" id="level2_active">{$navigation->level2_active|escape}</textarea></div></td>
		</tr>
		<tr>
			<td>HTML Tags</td>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<a href=&quot;&quot; title=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('\t', '');"><strong>TAB</strong></a>&nbsp;|
			</td>
		</tr>
		<tr>
			<td>{#NAVI_CONDITIONS#}</td>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('[tag:if_first]', '[tag:/if]');"><strong>[tag:if_first]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('[tag:if_not_first]', '[tag:/if]');"><strong>[tag:if_not_first]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('[tag:if_last]', '[tag:/if]');"><strong>[tag:if_last]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('[tag:if_not_last]', '[tag:/if]');"><strong>[tag:if_not_last]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('[tag:if_every:]', '[tag:/if]');"><strong>[tag:if_every:XXX]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('[tag:if_not_every:]', '[tag:/if]');"><strong>[tag:if_not_every:XXX]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('[tag:if_sub_level]', '[tag:/if]');"><strong>[tag:if_sub_level]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('[tag:if_no_sub_level]', '[tag:/if]');"><strong>[tag:if_no_sub_level]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_2_3('[tag:if_every:2]{#NAVI_ITEM_EVEN#}[tag:if_else]{#NAVI_ITEM_ODD#}[tag:/if]', '');"><strong>{#NAVI_EXAMPLE#}</strong></a>
				&nbsp;|
			</td>
		</tr>
	</table>
	</div>
</div>


<div class="widget first">
	<div class="head{if $navigation->level3_begin == ''} closed{/if}">
		<h5 class="iFrames">{#NAVI_LEVEL3#}</h5>
	</div>

	<div style="display: block">
	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<tr class="noborder">
			<td><strong>{#NAVI_LAVEL_TEMPL#}</strong><br />
				<strong><a class="rightDir" style="cursor: pointer;" title="Тег для вставки пунктов" onclick="textSelection_3_1('[tag:content]','');">[tag:content]</a></strong>
			</td>
			<td>
				<div class="pr12">
					<textarea style="width:100%" name="level3_begin" rows="12" id="level3_tpl">{$navigation->level3_begin|escape}</textarea>
				</div>
			</td>
		</tr>
		<tr>
			<td>HTML Tags</td>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<a href=&quot;&quot; title=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_1('\t', '');"><strong>TAB</strong></a>&nbsp;|
			</td>
		</tr>

		<tr>
			<td width="200">
				<strong>{#NAVI_LINK_INACTIVE#}</strong><br />
				<strong><a class="rightDir" style="cursor: pointer;" title="{#NAVI_LINK_ID#}" onclick="textSelection_3_2('[tag:linkid]','');">[tag:linkid]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_NAME#}" onclick="textSelection_3_2('[tag:linkname]','');">[tag:linkname]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_URL#}" onclick="textSelection_3_2('[tag:link]','');">[tag:link]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_TARGET#}" onclick="textSelection_3_2('[tag:target]','');">[tag:target]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_DESCR#}" onclick="textSelection_3_2('[tag:desc]','');">[tag:desc]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE#}" onclick="textSelection_3_2('[tag:img]','');">[tag:img]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE_DESCR#}" onclick="textSelection_3_2('[tag:linkid]','');">[tag:img_act]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE_ID#}" onclick="textSelection_3_2('[tag:img_id]','');">[tag:img_id]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS Style" onclick="textSelection_3_2('[tag:css_style]','');">[tag:css_style]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS ID" onclick="textSelection_3_2('[tag:css_id]','');">[tag:css_id]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS Class" onclick="textSelection_3_2('[tag:css_class]','');">[tag:css_class]</a></strong>
			</td>
			<td><div class="pr12"><textarea style="width:100%" name="level3" rows="12" id="level3">{$navigation->level3|escape}</textarea></div></td>
		</tr>
		<tr>
			<td>HTML Tags</td>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<a href=&quot;&quot; title=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('\t', '');"><strong>TAB</strong></a>&nbsp;|
			</td>
		</tr>
		<tr>
			<td>{#NAVI_CONDITIONS#}</td>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('[tag:if_first]', '[tag:/if]');"><strong>[tag:if_first]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('[tag:if_not_first]', '[tag:/if]');"><strong>[tag:if_not_first]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('[tag:if_last]', '[tag:/if]');"><strong>[tag:if_last]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('[tag:if_not_last]', '[tag:/if]');"><strong>[tag:if_not_last]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('[tag:if_every:]', '[tag:/if]');"><strong>[tag:if_every:XXX]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('[tag:if_not_every:]', '[tag:/if]');"><strong>[tag:if_not_every:XXX]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_2('[tag:if_every:2]{#NAVI_ITEM_EVEN#}[tag:if_else]{#NAVI_ITEM_ODD#}[tag:/if]', '');"><strong>{#NAVI_EXAMPLE#}</strong></a>
				&nbsp;|
			</td>
		</tr>
		<tr>
			<td width="200">
				<strong>{#NAVI_LINK_ACTIVE#}</strong><br />
				<strong><a class="rightDir" style="cursor: pointer;" title="{#NAVI_LINK_ID#}" onclick="textSelection_3_3('[tag:linkid]','');">[tag:linkid]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_NAME#}" onclick="textSelection_3_3('[tag:linkname]','');">[tag:linkname]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_URL#}" onclick="textSelection_3_3('[tag:link]','');">[tag:link]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_LINK_TARGET#}" onclick="textSelection_3_3('[tag:target]','');">[tag:target]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_DESCR#}" onclick="textSelection_3_3('[tag:desc]','');">[tag:desc]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE#}" onclick="textSelection_3_3('[tag:img]','');">[tag:img]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE_DESCR#}" onclick="textSelection_3_3('[tag:linkid]','');">[tag:img_act]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="{#NAVI_ITEM_IMAGE_ID#}" onclick="textSelection_3_3('[tag:img_id]','');">[tag:img_id]</a></strong><br/>
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS Style" onclick="textSelection_3_3('[tag:css_style]','');">[tag:css_style]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS ID" onclick="textSelection_3_3('[tag:css_id]','');">[tag:css_id]</a></strong><br />
				<strong><a class="rightDir"  style="cursor: pointer;" title="CSS Class" onclick="textSelection_3_3('[tag:css_class]','');">[tag:css_class]</a></strong>
			</td>
			<td><div class="pr12"><textarea style="width:100%" name="level3_active" rows="12" id="level3_active">{$navigation->level3_active|escape}</textarea></div></td>
		</tr>
		<tr>
			<td>HTML Tags</td>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<a href=&quot;&quot; title=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('\t', '');"><strong>TAB</strong></a>&nbsp;|
			</td>
		</tr>
		<tr>
			<td>{#NAVI_CONDITIONS#}</td>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('[tag:if_first]', '[tag:/if]');"><strong>[tag:if_first]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('[tag:if_not_first]', '[tag:/if]');"><strong>[tag:if_not_first]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('[tag:if_last]', '[tag:/if]');"><strong>[tag:if_last]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('[tag:if_not_last]', '[tag:/if]');"><strong>[tag:if_not_last]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('[tag:if_every:]', '[tag:/if]');"><strong>[tag:if_every:XXX]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('[tag:if_not_every:]', '[tag:/if]');"><strong>[tag:if_not_every:XXX]</strong></a>
				&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelection_3_3('[tag:if_every:2]{#NAVI_ITEM_EVEN#}[tag:if_else]{#NAVI_ITEM_ODD#}[tag:/if]', '');"><strong>{#NAVI_EXAMPLE#}</strong></a>
				&nbsp;|
			</td>
		</tr>
	</table>
	</div>

	<div class="fix"></div>

</div>

<div class="widget first">
	<div class="rowElem" id="saveBtn">
		<div class="saveBtn">
			<input type="submit" class="basicBtn" value="{#NAVI_BUTTON_SAVE#}" />
			{if $smarty.request.action != 'new'}
			&nbsp;{#NAVI_OR_BUTTON#}&nbsp;
			<input type="submit" class="button blackBtn SaveEdit" value="{#NAVI_BUTTON_SAVE_NEXT#}" />
			{/if}
		</div>
	</div>
</div>

</form>
{literal}
<script>
	$(document).on('change', '#alias', function (event) {

		var input = $(this);
		var alias = input.val();

		if (alias > '') {
			$.ajax({
				url: 'index.php?do=navigation&action=alias&cp=' + $sess,
				data: {
					alias: alias
				},
				success: function (data) {
					if (data === '1') {
						$.jGrowl(input.attr('data-accept'), {theme: 'accept'});
					}
					else if (data === 'syn') {
						$.jGrowl(input.attr('data-error-syn'), {theme: 'error'});
						alias = $nid ? $nid : '';
					}
					else {
						$.jGrowl(input.attr('data-error-exists'), {theme: 'error'});
						alias = $nid ? $nid : '';
					}
					$('#alias_tag').val('[tag:navigation:' + alias + ']');
				}
			});
		}
		else {
			alias = $nid ? $nid : '';
			$('#alias_tag').val('[tag:navigation:' + alias + ']');
		}

		return false;
	});
</script>
{/literal}

{if $smarty.request.action != 'new'}
<script language="javascript">
	var sett_options = {ldelim}
		url: '{$form_action}',
		data: {ldelim} ajax: '1', sub: 'save' {rdelim},
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
			$("#navigation_template").ajaxSubmit(sett_options);
			return false;
		{rdelim});

		$(".SaveEdit").click(function(e){ldelim}
			if (e.preventDefault) {ldelim}
				e.preventDefault();
			{rdelim} else {ldelim}
				e.returnValue = false;
			{rdelim}
			$("#navigation_template").ajaxSubmit(sett_options);
			return false;
		{rdelim});

	{rdelim});
</script>
{/if}

{include file="$codemirror_connect"}

{include file="$codemirror_editor" conn_id="_1_1" textarea_id='level1_tpl' ctrls='$("#navigation_template").ajaxSubmit(sett_options);' height=200}
{include file="$codemirror_editor" conn_id="_1_2" textarea_id='level1' ctrls='$("#navigation_template").ajaxSubmit(sett_options);' height=200}
{include file="$codemirror_editor" conn_id="_1_3" textarea_id='level1_active' ctrls='$("#navigation_template").ajaxSubmit(sett_options);' height=200}

{include file="$codemirror_editor" conn_id="_2_1" textarea_id='level2_tpl' ctrls='$("#navigation_template").ajaxSubmit(sett_options);' height=200}
{include file="$codemirror_editor" conn_id="_2_2" textarea_id='level2' ctrls='$("#navigation_template").ajaxSubmit(sett_options);' height=200}
{include file="$codemirror_editor" conn_id="_2_3" textarea_id='level2_active' ctrls='$("#navigation_template").ajaxSubmit(sett_options);' height=200}

{include file="$codemirror_editor" conn_id="_3_1" textarea_id='level3_tpl' ctrls='$("#navigation_template").ajaxSubmit(sett_options);' height=200}
{include file="$codemirror_editor" conn_id="_3_2" textarea_id='level3' ctrls='$("#navigation_template").ajaxSubmit(sett_options);' height=200}
{include file="$codemirror_editor" conn_id="_3_3" textarea_id='level3_active' ctrls='$("#navigation_template").ajaxSubmit(sett_options);' height=200}
