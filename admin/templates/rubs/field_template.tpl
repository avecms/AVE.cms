<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="javascript:void(0);">{#MAIN_PAGE#}</a></li>
			<li>{#RUBRIK_FILED_TEMPLATE_H#}</li>
			<li>{#RUBRIK_ALIAS_HEAD_R#} <strong class="code">{$rubric_title|escape}</strong></li>
			<li>{#RUBRIK_ALIAS_HEAD_F#} <strong class="code">{$rubric_field_title|escape}</strong></li>
		</ul>
	</div>
</div>

{if $errors}
	<ul class="messages">
		{foreach from=$errors item=error}<li class="highlight red"><strong>{#RUBRIK_ALIAS_ERROR#}</strong> {$error}</li>{/foreach}
	</ul>
{/if}

<form name="alias_check" id="field_tpl" method="post" action="index.php?do=rubs&action=field_template_save&onlycontent=1&cp={$sess}" class="mainForm">

<div class="widget first">
<div class="head"><h5 class="iFrames">{#RUBRIK_FIELD_DEFAULT#}</h5></div>
	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<tbody>
			<tr>
				<td>
					<div class="pr12">
						<input name="rubric_field_default" type="text" id="rubric_field_default" value="{$rubric_field_default|escape}" style="width:100%;" />
					</div>
				</td>
			</tr>
		</tbody>
	</table>
</div>

<div class="widget first">
	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<col>
		<thead>
			<tr>
				<td>{#RUBRIK_FILED_TEMPLATE_DESCR#}</td>
			</tr>
		</thead>
		<tr>
			<td>
				<div class="pr12">
					<textarea wrap="off" placeholder="" id="rubric_field_description" style="width:100%; height:40px" name="rubric_field_description">{$rubric_field_description|escape}</textarea>
				</div>
			</td>
		</tr>
		<tr>
			<td>
				|&nbsp;
				<a href="javascript:void(0);" onclick="textSelectionrftd('<div>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelectionrftd('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelectionrftd('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelectionrftd('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelectionrftd('<p>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelectionrftd('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelectionrftd('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelectionrftd('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
				<a href="javascript:void(0);" onclick="textSelectionrftd('<br &#047;>', '');"><strong>BR</strong></a>
				&nbsp;|
			</td>
		</tr>
	</table>
</div>

<div class="widget first">
<div class="head"><h5 class="iFrames">{#RUBRIK_FILED_TEMPLATE_F#}</h5></div>
	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<thead>
			<tr>
				<td>
					{#RUBRIK_FIELDS_TPL#}
				</td>
				<td>
					{#RUBRIK_REQUEST_TPL#}
				</td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>
					<div class="pr12">
						<textarea class="mousetrap" name="rubric_field_template" id="rubric_field_template" wrap="off" style="width:100%; height:100px">{$rubric_field_template|escape}</textarea>
					</div>
				</td>
				<td>
					<div class="pr12">
						<textarea class="mousetrap" name="rubric_field_template_request" id="rubric_field_template_request" wrap="off" style="width:100%; height:100px">{$rubric_field_template_request|escape}</textarea>
					</div>
				</td>
			</tr>
			<tr>
				<td>
					|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('[tag:parametr:', ']');"><strong>[tag:parametr:XXX]</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('[tag:X000x000:[tag:parametr:', ']]');"><strong>[tag:X000x000:[tag:parametr:XXX]]</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('[tag:path]', '');"><strong>[tag:path]</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('[tag:docid]', '');"><strong>[tag:docid]</strong></a>&nbsp;|&nbsp;

					<br/>
					|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('[tag:if_empty]\r\n', '\r\n[/tag:if_empty]');"><strong>[tag:if_empty]&nbsp;[/tag:if_empty]</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('[tag:if_notempty]\r\n', '\r\n[/tag:if_notempty]');"><strong>[tag:if_notempty]&nbsp;[/tag:if_notempty]</strong></a>
					&nbsp;|
					&nbsp;|
					<br/>
					|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<a href=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrft('\t', '');"><strong>TAB</strong></a>
					&nbsp;|
				</td>
				<td>
					|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('[tag:parametr:', ']');"><strong>[tag:parametr:XXX]</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('[tag:X000x000:[tag:parametr:', ']]');"><strong>[tag:X000x000:[tag:parametr:XXX]]</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('[tag:path]', '');"><strong>[tag:path]</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('[tag:docid]', '');"><strong>[tag:docid]</strong></a>&nbsp;|&nbsp;

					<br/>
					|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('[tag:if_empty]\r\n', '\r\n[/tag:if_empty]');"><strong>[tag:if_empty]&nbsp;[/tag:if_empty]</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('[tag:if_notempty]\r\n', '\r\n[/tag:if_notempty]');"><strong>[tag:if_notempty]&nbsp;[/tag:if_notempty]</strong></a>
					&nbsp;|
					&nbsp;|
					<br/>
					|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<div class=&quot;&quot; id=&quot;&quot;>', '</div>');"><strong>DIV</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<ol>', '</ol>');"><strong>OL</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<ul>', '</ul>');"><strong>UL</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<li>', '</li>');"><strong>LI</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<p class=&quot;&quot;>', '</p>');"><strong>P</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<strong>', '</strong>');"><strong>B</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<em>', '</em>');"><strong>I</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<h1>', '</h1>');"><strong>H1</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<h2>', '</h2>');"><strong>H2</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<h3>', '</h3>');"><strong>H3</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<h4>', '</h4>');"><strong>H4</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<h5>', '</h5>');"><strong>H5</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<a href=&quot;&quot;>', '</a>');"><strong>A</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<img src=&quot;&quot; alt=&quot;&quot; &#047;>', '');"><strong>IMG</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<span>', '</span>');"><strong>SPAN</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<pre>', '</pre>');"><strong>PRE</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('<br &#047;>', '');"><strong>BR</strong></a>&nbsp;|&nbsp;
					<a href="javascript:void(0);" onclick="textSelectionrftr('\t', '');"><strong>TAB</strong></a>
					&nbsp;|
				</td>
			</tr>

			<tr>
				<td colspan="2">
					<input type="submit" class="basicBtn SaveEditFieldTemplateSave" value="{#RUBRIK_BUTTON_SAVE#}"/>
					&nbsp;
					<input type="submit" class="blackBtn SaveEditFieldTemplate" value="{#RUBRIK_BUTTON_TPL_NEXT#}" />
					&nbsp;
					<a href="javascript:void(0);" class="button redBtn Close">{#RUBRIK_BUTTON_TPL_CLOSE#}</a>
				</td>
			</tr>
		</tbody>

	</table>
	<input type="hidden" name="field_id" value="{$smarty.request.field_id|escape}" />
	<input type="hidden" name="rubric_id" value="{$smarty.request.rubric_id|escape}" />

</div>
</form>

<script language="javascript">
$(document).ready(function(){ldelim}

	Mousetrap.bind(['ctrl+s', 'command+s'], function(event) {ldelim}
		event.preventDefault();
		$("#field_tpl").ajaxSubmit({ldelim}
			url: 'index.php?do=rubs&action=field_template_save&onlycontent=1&cp={$sess}',
			dataType: 'json',
			beforeSubmit: function(){ldelim}
				$.alerts._overlay('show');
			{rdelim},
			success: function(data){ldelim}
				$.jGrowl(data['message'], {ldelim}
					header: data['header'],
					theme: data['theme']
				{rdelim});
					$.alerts._overlay('hide');
			{rdelim}
		{rdelim});
		return false;
	{rdelim});

	$(".SaveEditFieldTemplate").on('click', function(event){ldelim}
		event.preventDefault();
		$("#field_tpl").ajaxSubmit({ldelim}
			url: 'index.php?do=rubs&action=field_template_save&onlycontent=1&cp={$sess}',
			dataType: 'json',
			beforeSubmit: function(){ldelim}
				$.alerts._overlay('show');
			{rdelim},
			success: function(data){ldelim}
				$.jGrowl(data['message'], {ldelim}
					header: data['header'],
					theme: data['theme']
				{rdelim});
					$.alerts._overlay('hide');
			{rdelim}
		{rdelim});
		return false;
	{rdelim});

	$(".Close").on('click', function(event){ldelim}
		event.preventDefault();
		$('#ajax-dialog-rft-{$smarty.request.field_id|escape}').dialog('destroy').remove();
		return false;
	{rdelim});

	$(".SaveEditFieldTemplateSave").on('click', function(event){ldelim}
		event.preventDefault();
		$("#field_tpl").ajaxSubmit({ldelim}
			url: 'index.php?do=rubs&action=field_template_save&onlycontent=1&save=save&cp={$sess}',
			dataType: 'json',
			beforeSubmit: function(){ldelim}
				$.alerts._overlay('show');
			{rdelim},
			success: function(data){ldelim}
				$.jGrowl(data['message'], {ldelim}
					header: data['header'],
					theme: data['theme']
				{rdelim});
					$.alerts._overlay('hide');
					$('#ajax-dialog-rft-{$smarty.request.field_id|escape}').dialog('destroy').remove();
			{rdelim}
		{rdelim});
		return false;
	{rdelim});

{literal}
	setTimeout(function(){editorrft.refresh();}, 20);
	setTimeout(function(){editorrftr.refresh();}, 20);
	setTimeout(function(){editorrftd.refresh();}, 20);
{/literal}

{rdelim});
</script>

{include file="$codemirror_editor" conn_id="rftd" textarea_id='rubric_field_description' ctrls='$(".SaveEditFieldTemplate").trigger("click");' height=80}
{include file="$codemirror_editor" conn_id="rft" textarea_id='rubric_field_template' ctrls='$(".SaveEditFieldTemplate").trigger("click");' height=130}
{include file="$codemirror_editor" conn_id="rftr" textarea_id='rubric_field_template_request' ctrls='$(".SaveEditFieldTemplate").trigger("click");' height=130}