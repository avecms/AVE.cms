{if $smarty.session.use_editor == 0}
<script>
	CKEDitor_loaded = false;
</script>
{/if}

<script type="text/javascript">

function openLinkWin(target, rtrn='', data='') {ldelim}
	if (typeof width == 'undefined' || width == '')
		var width = screen.width * 0.8;

	if (typeof height == 'undefined' || height == '')
		var height = screen.height * 0.7;

	var left = ( screen.width - width ) / 2;
	var top = ( screen.height - height ) / 2;

	data = data.length ? data : 'selurl';

	window.open('index.php?do=docs&action=showsimple&target='+target+'&'+data+'=1&pop=1','pop','left='+left+',top='+top+',width='+width+',height='+height+',scrollbars=1,resizable=1');
{rdelim}

function openLinkWinId(target, doc) {ldelim}
	if (typeof width == 'undefined' || width == '')
		var width = screen.width * 0.8;

	if (typeof height == 'undefined' || height == '')
		var height = screen.height * 0.7;

	if (typeof doc == 'undefined')
		var doc = 'document_title';

	if (typeof scrollbar == 'undefined')
		var scrollbar = 1;

	var left = ( screen.width - width ) / 2;
	var top = ( screen.height - height ) / 2;

	window.open('index.php?idonly=1&do=docs&action=showsimple&doc='+doc+'&target='+target+'&pop=1&cp={$sess}','pop','left='+left+',top='+top+',width='+width+',height='+height+',scrollbars='+scrollbar+',resizable=1');
{rdelim}

function openFileWin(target,id) {ldelim}
	if (typeof width == 'undefined' || width == '')
		var width = screen.width * 0.8;

	if (typeof height == 'undefined' || height == '')
		var height = screen.height * 0.7;

	if (typeof doc == 'undefined')
		var doc = 'document_title';

	if (typeof scrollbar == 'undefined')
		var scrollbar=1;

	var left = ( screen.width - width ) / 2;
	var top = ( screen.height - height ) / 2;

	window.open('index.php?do=browser&id='+id+'&typ=bild&mode=fck&target=navi&cp={$sess}','pop','left='+left+',top='+top+',width='+width+',height='+height+',scrollbars='+scrollbar+',resizable=1');
{rdelim}

$(document).ready(function(){ldelim}

	$(".ConfirmRecover").click( function(e) {ldelim}
		e.preventDefault();
		var href = $(this).attr('href');
		var title = $(this).attr('dir');
		var confirm = $(this).attr('name');
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

	$(".ConfirmDeleteRev").click( function(e) {ldelim}
		e.preventDefault();
		var revission = $(this).attr('rev');
		var href = $(this).attr('href');
		var title = $(this).attr('dir');
		var confirm = $(this).attr('name');
		jConfirm(
				confirm,
				title,
				function(b){ldelim}
					if (b){ldelim}
						$.alerts._overlay('hide');
						$.alerts._overlay('show');
						$.ajax({ldelim}
							url: ave_path+'admin/'+href+'&ajax=run',
							type: 'POST',
							success: function (data) {ldelim}
								$.alerts._overlay('hide');
								$.jGrowl(revission,{ldelim}theme: 'accept'{rdelim});
								$("#"+revission).remove();
							{rdelim}
						{rdelim});
					{rdelim}
				{rdelim}
			);
	{rdelim});

	function check(){ldelim}
		$.ajax({ldelim}
			beforeSend: function(){ldelim}
				{rdelim},
			url: 'index.php',
			data: ({ldelim}
				'action': 'checkurl',
				'do': 'docs',
				'check': false,
				'cp': '{$sess}',
				'id': '{$document->Id}',
				'alias': $("#document_alias").val()
				{rdelim}),
			timeout:3000,
			dataType: "json",
			success:
				function(data){ldelim}
					$.jGrowl(data[0],{ldelim}theme: data[1]{rdelim});
				{rdelim}
		{rdelim});
	{rdelim};

	$("#translit").click(function(){ldelim}
		$.ajax({ldelim}
			beforeSend: function(){ldelim}
				$("#checkResult").html('');
				{rdelim},
			url:'index.php',
			data: ({ldelim}
				action: 'translit',
				'do': 'docs',
				cp: '{$sess}',
				alias: $("#document_alias").val(),
				title: $("#document_title").val(),
				prefix: '{$document->rubric_url_prefix}'
				{rdelim}),
			timeout:3000,
			success: function(data){ldelim}
				$("#document_alias").val(data);
				check();
				{rdelim}
		{rdelim});
	{rdelim});

	$("#document_alias").change(function(){ldelim}
		if ($("#document_alias").val()!='') check();
	{rdelim});

	$("#loading")
		.bind("ajaxSend", function(){ldelim}$.alerts._overlay('show'){rdelim})
		.bind("ajaxComplete", function(){ldelim}$.alerts._overlay('hide'){rdelim}
	);

	{if $smarty.request.feld != ''}
		$("#feld_{$smarty.request.feld|escape}").css({ldelim}
			'border' : '2px solid red',
			'font' : '120% verdana,arial',
			'background' : '#ffffff'
		{rdelim});
	{/if}

	$('#document_published').datetimepicker({ldelim}
		changeMonth: true,
		changeYear: true,
		stepHour: 1,
		stepMinute: 1,

		onClose: function(dateText, inst) {ldelim}
		var endDateTextBox = $('#document_expire');
		if (endDateTextBox.val() != '') {ldelim}
			var testStartDate = new Date(dateText);
			var testEndDate = new Date(endDateTextBox.val());
			if (testStartDate > testEndDate)
				endDateTextBox.val(dateText);
		{rdelim}
		else {ldelim}
			endDateTextBox.val(dateText);
		{rdelim}
		{rdelim}
	{rdelim});

	$('#document_expire').datetimepicker({ldelim}
		changeMonth: true,
		changeYear: true,

		stepHour: 1,
		stepMinute: 1,

		onClose: function(dateText, inst) {ldelim}
		var startDateTextBox = $('#document_published');
		if (startDateTextBox.val() != '') {ldelim}
			var testStartDate = new Date(startDateTextBox.val());
			var testEndDate = new Date(dateText);
			if (testStartDate > testEndDate)
				startDateTextBox.val(dateText);
		{rdelim}
		else {ldelim}
			startDateTextBox.val(dateText);
		{rdelim}
	{rdelim},
	onSelect: function (selectedDateTime){ldelim}
		var end = $(this).datetimepicker('getDate');
		$('#document_published').datetimepicker('option', 'maxDate', new Date(end.getTime()) );
	{rdelim}
	{rdelim});

	$(".linkSelect").change(function() {ldelim}
		var link = $(this).val();
		var parent = $(this).find(' option:selected').attr("data-id");
		{if $document->rubric_url_prefix == ""}
			$("#document_alias").val(link);
		{else}
			$("#document_alias").val(link+'/{$document->rubric_url_prefix}');
		{/if}
		$("#document_parent").val(parent);
		return false;
	{rdelim});

	$("#document_meta_keywords").autocomplete("index.php?do=docs&action=keywords&ajax=run&cp={$sess}", {ldelim}
		max: 20,
		width: 300,
		highlight: false,
		multiple: true,
		multipleSeparator: ", ",
		autoFill: true,
		scroll: true,
		scrollHeight: 180
	{rdelim});

	$('#document_lang').change(function() {ldelim}
		var defaultLang = '{$smarty.session.accept_langs[$smarty.const.DEFAULT_LANGUAGE]}';
		var lang = $('#document_lang option:selected').val();
		var alias = $('#document_alias').val().split('/');
		var languages = [];

		$('#document_lang option').each(function(){ldelim}
			languages.push($(this).attr('value'));
		{rdelim});

		if ($.inArray(alias[0], languages) > -1) {ldelim}
			alias.splice(0, 1);
		{rdelim}

		if ((lang == defaultLang)||(lang == "{#DOC_LANG_NONE#}")) {ldelim}
			$('#document_alias').val(alias.join('/'));
		{rdelim} else {ldelim}
			if (alias[0] != "") {ldelim}
				console.log(alias);
				$('#document_alias').val(lang + '/' + alias.join('/'));
			{rdelim} else {ldelim}
				$('#document_alias').val(lang);
			{rdelim}
		{rdelim}
	{rdelim});

	$('#lang_block').hide();
	$('#show_lang').click(function(event) {ldelim}
		event.preventDefault();
		$('#lang_block').show();
		$('#show_lang').hide();
	{rdelim});

{rdelim});

</script>

{if $smarty.request.action=='edit'}
	<div class="title">
		<h5>{#DOC_EDIT_DOCUMENT#} ID: {$smarty.request.Id}</h5>
		<div class="lang">
		{foreach from=$smarty.session.accept_langs key=lang_id item=lang}
			{if $document->lang_pack[$lang_id]>''}
				<a href="{$ABS_PATH}admin/index.php?do=docs&action=edit&Id={$document->lang_pack[$lang_id].Id}"><img src="{$ABS_PATH}lib/flags/{$lang_id}.png" alt="{$lang_id}" /></a>
			{else}
				<a class="icon_off" href="{$ABS_PATH}admin/index.php?do=docs&action=new&lang_pack={$document->Id}&rubric_id={$document->rubric_id}&lang={$lang_id}"><img src="{$ABS_PATH}lib/flags/{$lang_id}.png" alt="{$lang_id}" /></a>
			{/if}
		{/foreach}
		</div>
	</div>
{elseif $smarty.request.action=='copy'}
	<div class="title"><h5>{#DOC_COPY_DOCUMENT#}</h5></div>
{else}
	<div class="title"><h5>{#DOC_ADD_DOCUMENT#}</h5>
		<div class="lang">
		{foreach from=$smarty.session.accept_langs key=lang_id item=lang}
			{if $document->lang_pack[$lang_id]>''}
				<a href="{$ABS_PATH}admin/index.php?do=docs&action=edit&Id={$document->lang_pack[$lang_id].Id}"><img src="{$ABS_PATH}lib/flags/{$lang_id}.png" alt="{$lang_id}" /></a>
			{else}
				<a class="icon_off" href="{$ABS_PATH}admin/index.php?do=docs&action=new&rubric_id={$document->rubric_id}&lang={$lang_id}"><img src="{$ABS_PATH}lib/flags/{$lang_id}.png" alt="{$lang_id}" /></a>
			{/if}
		{/foreach}
		</div>
	</div>
{/if}

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=docs&cp={$sess}">{#DOC_SUB_TITLE#}</a></li>
			{if $smarty.request.action=='edit'}
				<li>{#DOC_EDIT_DOCUMENT#}</li>
				{if check_permission('rubric_edit')}
				<li><strong>{#DOC_IN_RUBRIK#}</strong> &gt; <a style="float: right" title="{#RUBRIK_EDIT_TEMPLATE#}" href="index.php?do=rubs&action=edit&Id={$document->rubric_id}&cp={$sess}">{$document->rubric_title|escape}</a></li>
				{else}
				<li><strong>{#DOC_IN_RUBRIK#}</strong> &gt; {$document->rubric_title|escape}</li>
				{/if}
				<li><strong class="code"><a href="{$document->document_alias_breadcrumb}" target="_blank">{if $document->document_title != ""}{$document->document_title}{else}{#DOC_SHOW3_TITLE#}{/if}</a></strong></li>
			{else}
				<li>{#DOC_ADD_DOCUMENT#}</li>
				<li><strong>{#DOC_IN_RUBRIK#}</strong> &gt; {$document->rubric_title|escape}</li>
				<li><strong class="code">{if $smarty.request.document_title != ""}{$smarty.request.document_title}{else}{#DOC_IN_NEW#}{/if}</strong></li>
			{/if}
		</ul>
	</div>
</div>


<form method="post" name="formDocOption" action="{$document->formaction}" enctype="multipart/form-data" class="mainForm" id="formDoc">

	<input class="mousetrap" name="closeafter" type="hidden" id="closeafter" value="{$smarty.request.closeafter}">

	{if ($smarty.request.Id == 1 || $smarty.request.Id == $PAGE_NOT_FOUND_ID) && $smarty.request.action != 'new' && $smarty.request.action != 'copy'}
		{assign var=dis value = 'disabled'}
	{/if}

	<div class="widget first">

		<ul class="tabs">
			<li class="activeTab"><a href="#tab1">{#DOC_TABS_META#}</a></li>
			<li><a href="#tab2">{#DOC_TABS_URL#}</a></li>
			<li><a href="#tab3">{#DOC_TABS_DATE#}</a></li>
			<li><a href="#tab4">{#DOC_TABS_OTHER#}</a></li>
		</ul>

		<div class="tab_container">

			<!-- Meta данные -->
			<div id="tab1" class="tab_content" style="display: block;">

				<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
					<col width="250">
					<col>
					<tbody>

						<tr>
							<td>{#DOC_NAME#}&nbsp;<a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#DOC_META_TITLE#}">[?]</a></td>
							<td colspan="3"><div class="pr12"><input class="mousetrap" name="document_title" type="text" id="document_title" size="40" value="{if $smarty.request.action == 'edit'}{$document->document_title|escape|stripslashes}{else}{$smarty.request.document_title|stripslashes}{/if}" /></div></td>
						</tr>

						<tr>
							<td>{#DOC_META_KEYWORDS#}&nbsp;<a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#DOC_META_KEYWORDS_INFO#}">[?]</a></td>
							<td colspan="3">
								<div class="pr12">
								<textarea class="mousetrap" style="width:100%; height:40px" name="document_meta_keywords" id="document_meta_keywords">{$document->document_meta_keywords|escape|stripslashes}</textarea>
								</div>
							</td>
						</tr>

						<tr>
							<td>{#DOC_META_DESCRIPTION#}&nbsp;<a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#DOC_META_DESCRIPTION_INFO#}">[?]</a></td>
							<td colspan="3">
								<div class="pr12">
								<textarea class="mousetrap" style="width:100%; height:40px" name="document_meta_description" id="document_meta_description" >{$document->document_meta_description|escape|stripslashes}</textarea>
								</div>
							</td>
						</tr>

						<tr>
							<td>{#DOC_INDEX_TYPE#}</td>
							<td colspan="3">
								<select style="width:300px" name="document_meta_robots" id="document_meta_robots">
									<option value="index,follow"{if $document->document_meta_robots=='index,follow'} selected="selected"{/if}>{#DOC_INDEX_FOLLOW#}</option>
									<option value="index,nofollow"{if $document->document_meta_robots=='index,nofollow'} selected="selected"{/if}>{#DOC_INDEX_NOFOLLOW#}</option>
									<option value="noindex,nofollow"{if $document->document_meta_robots=='noindex,nofollow'} selected="selected"{/if}>{#DOC_NOINDEX_NOFOLLOW#}</option>
								</select>
							</td>
						</tr>

						<tr>
							<td>{#DOC_SITEMAP_FREQ#} <a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#DOC_SITEMAP_FREQ_DOC#}">[?]</a></td>
							<td>
								<select name="document_sitemap_freq" id="document_sitemap_freq" style="width: 250px">
									<option value="0"{if $document->document_sitemap_freq=='0'} selected="selected"{/if}>{#DOC_SITEMAP_ALWAYS#}</option>
									<option value="1"{if $document->document_sitemap_freq=='1'} selected="selected"{/if}>{#DOC_SITEMAP_HOURLY#}</option>
									<option value="2"{if $document->document_sitemap_freq=='2'} selected="selected"{/if}>{#DOC_SITEMAP_DAILY#}</option>
									<option value="3"{if $document->document_sitemap_freq=='3' || $document->document_sitemap_freq == ''} selected="selected"{/if}>{#DOC_SITEMAP_WEEKLY#}</option>
									<option value="4"{if $document->document_sitemap_freq=='4'} selected="selected"{/if}>{#DOC_SITEMAP_MONTHLY#}</option>
									<option value="5"{if $document->document_sitemap_freq=='5'} selected="selected"{/if}>{#DOC_SITEMAP_YEARLY#}</option>
									<option value="6"{if $document->document_sitemap_freq=='6'} selected="selected"{/if}>{#DOC_SITEMAP_NEVER#}</option>
								</select>
							</td>
							<td>{#DOC_SITEMAP_PRIORITY#} <a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#DOC_SITEMAP_PRIORITY_DOC#}">[?]</a></td>
							<td>
								<select name="document_sitemap_pr" id="document_sitemap_pr" style="width: 250px">
									<option value="0"{if $document->document_sitemap_pr=='0'} selected="selected"{/if}>0 {#DOC_SITEMAP_PRIORITY_LOW#}</option>
									<option value="0.1"{if $document->document_sitemap_pr=='0.1'} selected="selected"{/if}>0.1</option>
									<option value="0.2"{if $document->document_sitemap_pr=='0.2'} selected="selected"{/if}>0.2</option>
									<option value="0.3"{if $document->document_sitemap_pr=='0.3'} selected="selected"{/if}>0.3</option>
									<option value="0.4"{if $document->document_sitemap_pr=='0.4'} selected="selected"{/if}>0.4</option>
									<option value="0.5"{if $document->document_sitemap_pr=='0.5' || $document->document_sitemap_pr==''} selected="selected"{/if}>0.5 {#DOC_SITEMAP_PRIORITY_MID#}</option>
									<option value="0.6"{if $document->document_sitemap_pr=='0.6'} selected="selected"{/if}>0.6</option>
									<option value="0.7"{if $document->document_sitemap_pr=='0.7'} selected="selected"{/if}>0.7</option>
									<option value="0.8"{if $document->document_sitemap_pr=='0.8'} selected="selected"{/if}>0.8</option>
									<option value="0.9"{if $document->document_sitemap_pr=='0.9'} selected="selected"{/if}>0.9</option>
									<option value="1"{if $document->document_sitemap_pr=='1'} selected="selected"{/if}>1 {#DOC_SITEMAP_PRIORITY_HIG#}</option>
								</select>
							</td>
						</tr>

					</tbody>
				</table>


			</div>
			<!-- Alias документа -->
			<div id="tab2" class="tab_content" style="display: none;">
				<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
					<col width="250">
					<col>
					<tbody>
						<tr>
							<td>{#DOC_CHOOSE_LANG#}</td>
							<td colspan="3">
								<select style="width: 100px" name="document_lang" id="document_lang">
										<option value="">&nbsp;</option>
									{foreach from=$smarty.session.accept_langs key=lang_id item=lang}
										{if ($smarty.request.lang == $lang_id)}
										<option value="{$lang_id}" selected="selected">{$lang}</option>
										{elseif ($document->document_lang == $lang_id)}
										<option value="{$lang_id}" selected="selected">{$lang}</option>
										{elseif (!$smarty.request.lang AND !$document->document_lang AND $document->document_lang == '' AND $smarty.const.DEFAULT_LANGUAGE == $lang_id)}
										<option value="{$lang_id}" selected="selected">{$lang}</option>
										{else}
										<option value="{$lang_id}">{$lang}</option>
										{/if}
									{/foreach}
								</select>
							</td>
						</tr>

						<tr>
							<td>{#DOC_URL#}&nbsp;<a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#DOC_URL_INFO#}">[?]</a></td>
							<td nowrap="nowrap" colspan="3">
								<div class="pr12">
									<input class="mousetrap" name="prefix" type="hidden" value="{$document->rubric_url_prefix}">
									<input class="mousetrap" autocomplete="off" name="document_alias" {$dis} type="text" id="document_alias" size="60" style="float: left; width: 100%;" value="{if $smarty.request.action=='edit' OR $document->document_alias!=''}{$document->document_alias}{else}{$document->rubric_url_prefix}{/if}" />
										<span class="span-form" style="padding-left: 10px;">
											{if $smarty.request.Id != 1 && $smarty.request.Id != $PAGE_NOT_FOUND_ID}
											<input type="button" class="basicBtn" id="translit" value="{#DOC_ALIAS_CREATE#}" />
											{/if}
											{if $smarty.request.Id && $smarty.request.Id != $PAGE_NOT_FOUND_ID}
											<a data-dialog="aliases-{$smarty.request.Id}" href="index.php?do=docs&action=aliases_doc&doc_id={$smarty.request.Id}&cp={$sess}&pop=1&onlycontent=1" data-height="650" data-modal="true" data-title="История алисов документа" class="openDialog button greenBtn">История</a>
											{/if}
										</span>
								</div>
								<span id="loading" style="display:none"></span>
							</td>
						</tr>

						<tr>
							<td>{#DOC_URL_LOG#}&nbsp;<a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#DOC_URL_LOG_T#}">[?]</a></td>
							<td nowrap="nowrap" colspan="3">
								<div class="pr12">
									<select style="width: 300px" name="document_alias_history" id="document_alias_history">
										<option value="0"{if $document->document_alias_history=='0'} selected="selected"{/if}>{#DOC_URL_LOG_RUBRIC#}</option>
										<option value="1"{if $document->document_alias_history=='1'} selected="selected"{/if}>{#DOC_URL_LOG_USE#}</option>
										<option value="2"{if $document->document_alias_history=='2'} selected="selected"{/if}>{#DOC_URL_LOG_NOTUSE#}</option>
									</select>
								</div>
							</td>
						</tr>

						{if $document_alias}
						<tr>
							<td>{#DOC_URL_LINK#}&nbsp;<a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#DOC_USE_RUB_ALIAS#}">[?]</a></td>
							<td nowrap="nowrap" colspan="3">
								<div class="alias">
									{foreach from=$document_alias key=k item=v}
										<div>&nbsp;{$k}:</div>
										<div style="margin:2px 0 3px;">
											<select class="linkSelect" style="width: 300px;">
												<option value="" data-id="" selected="selected">{#DOC_LINK_CHOOSE#}</option>
												{section name=nov loop=$v}
												<option value="{$v[nov].document_alias}" data-id="{$v[nov].Id}">{if $v[nov].document_breadcrum_title}{$v[nov].document_breadcrum_title}{else}{$v[nov].document_title}{/if}</option>
												{/section}
											</select>
											<div class="fix"></div>
										</div>
									{/foreach}
								</div>
							</td>
						</tr>
						{/if}
					</tbody>
				</table>
			</div>
			<!-- Дата публикации -->
			<div id="tab3" class="tab_content" style="display: none;">
				<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
					<col width="250">
					<col>
					<tbody>
						<tr>
							<td>{#DOC_START_PUBLICATION#}</td>
							<td>
								<input class="mousetrap" {$dis} id="document_published" name="document_published" type="text" value="{$document->document_published|date_format:"%d.%m.%Y %H:%M"}" style="width: 150px;" />
							</td>

							<td>{#DOC_END_PUBLICATION#}</td>
							<td>
								<input class="mousetrap" {$dis} id="document_expire" name="document_expire" type="text" value="{$document->document_expire|date_format:"%d.%m.%Y %H:%M"}" style="width: 150px;" />
							</td>
						</tr>
					</tbody>
				</table>
			</div>
			<!-- Прочие параметры -->
			<div id="tab4" class="tab_content" style="display: none;">
				<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
					<col width="250">
					<col>
					<tbody>
						<tr>
							<td>{#DOC_RUBRIC_TMPLS#}&nbsp;<a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#DOC_RUBRIC_TMPLS_HINT#}">[?]</a></td>
							<td nowrap="nowrap" colspan="3">
								<div style="margin:2px 0 3px;">
									<select style="width: 400px;" name="rubric_tmpl_id" id="rubric_tmpl_id">
										<option value="0" {if $smarty.request.action == 'new'}selected="selected"{/if}>{#DOC_TEMPLATE_DEFAULT#}</option>
										{foreach from=$rubric_tmpls item=tmpl}
										<option value="{$tmpl->id}"{if $document->rubric_tmpl_id == $tmpl->id}selected="selected"{/if}>{$tmpl->title}</option>
										{/foreach}
									</select>
									<div class="fix"></div>
								</div>
							</td>
						</tr>

						<tr>
							<td>{#DOC_CAN_SEARCH#}</td>
							<td colspan="3"><input name="document_in_search" type="checkbox" id="document_in_search" value="1" {if $document->document_in_search==1 || $smarty.request.action=='new'}checked{/if} /><label> </label></td>
						</tr>

						<tr>
							<td>{#DOC_STATUS#}</td>
							<td colspan="3">
								{if $smarty.request.action == 'new'}
									{if  $document->dontChangeStatus==1}
										{assign var=sel_1 value=''}
										{assign var=sel_2 value='selected="selected"'}
									{else}
										{assign var=sel_1 value='selected="selected"'}
										{assign var=sel_2 value=''}
									{/if}
								{else}
									{if $document->document_status==1}
										{assign var=sel_1 value='selected="selected"'}
										{assign var=sel_2 value=''}
									{else}
										{assign var=sel_1 value=''}
										{assign var=sel_2 value='selected="selected"'}
									{/if}
								{/if}
								<select style="width: 200px" name="document_status" id="document_status"{if $document->dontChangeStatus==1} disabled="disabled"{/if}>
									<option value="1" {$sel_1}>{#DOC_STATUS_ACTIVE#}</option>
									<option value="0" {$sel_2}>{#DOC_STATUS_INACTIVE#}</option>
								</select>
							</td>
						</tr>

						<tr>
							<td>{#DOC_USE_NAVIGATION#} <a href="javascript:void(0);" style="cursor:help;" class="rightDir link btext" title="{#DOC_NAVIGATION_INFO#}">[?]</a></td>
							<td colspan="3">
								{include file='navigation/tree.tpl'}
							</td>
						</tr>

						<tr>
							<td>{#DOC_BREADCRUMB_TITLE#}</td>
							<td colspan="3"><div class="pr12"><input class="mousetrap" name="document_breadcrum_title" type="text" id="document_breadcrum_title" size="40" value="{if $smarty.request.action == 'edit'}{$document->document_breadcrum_title|escape}{/if}" /></div></td>
						</tr>

						<tr>
							<td>{#DOC_USE_BREADCRUMB#}</td>
							<td colspan="3">
								<input class="mousetrap" name="document_parent" type="text" id="document_parent" value="{$document->document_parent}" size="4" maxlength="10" style="width: 50px;" />&nbsp;
									<span class="button basicBtn" onClick="openLinkWinId('document_parent','document_parent');">{#DOC_BREADCRUMB_BTN#}</span>
								&nbsp;{if $document->parent}{#DOC_BREADCRUMB_WITH#} « <a href="{$ABS_PATH}{$document->parent->document_alias}" target="_blank">{$document->parent->document_title|stripslashes}</a> »{/if}
							</td>
						</tr>

						<tr>
							<td>{#DOC_USE_LANG_PACK#}</td>
							<td colspan="3">
								<a id="show_lang" class="button basicBtn" href="#">{#DOC_SHOW_LANG#}</a>
								<div id="lang_block"><input name="document_lang_group" class="mousetrap" type="text" id="document_lang_group" value="{if $smarty.request.lang_pack}{$smarty.request.lang_pack}{else}{$document->document_lang_group}{/if}" size="4" maxlength="10" style="width: 50px;" /></div>
							</td>
						</tr>

						<tr>
							<td>{#DOC_PROPERTY#}</td>
							<td colspan="3">
								<input class="mousetrap" {$dis} id="document_property" name="document_property" type="text" value="{$document->document_property|escape}" readonly style="width: 100%;" />
							</td>
						</tr>
					</tbody>
				</table>
			</div>

		</div>

		<div class="fix"></div>
	</div>


	<div class="widget first">
		<div class="head">
			<h5>{#DOC_MAIN_CONTENT#}</h5>
		</div>
		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
			<col width="250">
			<col>
			<tbody>
			{if $document->fields}
			{foreach from=$document->fields item=document_field_group}

				{if $document->count_groups > 1}
				<tr class="group_row_{$document_field_group.group_id|default:0} group_row grey" id="group_row_{$document_field_group.group_id|default:0}">
					<td colspan="3" class="aligncenter"><h5>{if $document_field_group.group_title}{$document_field_group.group_title}{else}{#DOC_FIELD_G_UNKNOW#}{/if}</h5></td>
				</tr>
				{/if}

				{foreach from=$document_field_group.fields  item=field}
				<tr class="field_row_{$field.Id} field_row" id="field_row_{$field.Id}">
					<td>
						<strong>{$field.rubric_field_title|escape}</strong>
						{if $field.rubric_field_description}
						<br />
						<small>{$field.rubric_field_description}</small>
						{/if}
					</td>
					<td colspan="2">{$field.result}</td>
				</tr>
				{/foreach}

			{/foreach}
			{else}
				<tr class="field_row">
					<td colspan="2">
						<ul class="messages">
							<li class="highlight yellow">{#DOC_MAIN_NOCONTENT#}</li>
						</ul>
					</td>
				</tr>
			{/if}
			</tbody>
		</table>

		<div class="rowElem" id="saveBtn">
			<div class="saveBtn">
				{if $smarty.request.action == 'edit'}
					<div style="float:left">
					<input type="submit" class="basicBtn" name="doc_after" value="{#DOC_BUTTON_EDIT_DOCUMENT#}" />
					&nbsp;
					<input type="submit" class="blackBtn SaveEdit" value="{#DOC_BUTTON_EDIT_DOCUMENT_NEXT#}" />
					</div>
					<input style="float:right" type="submit" class="greenBtn" value="{#DOC_DISPLAY_NEW_WINDOW#} &raquo;" onClick="window.open('/{if $document_id!=1}index.php?id={$smarty.request.Id}{/if}','_blank');return false;" />
					<div class="clear"></div>
				{elseif $smarty.request.action == 'copy'}
					<input type="submit" class="basicBtn" name="doc_after" value="{#DOC_BUTTON_ADD_DOCUMENT#}" />
					&nbsp;
					<input type="submit" class="blackBtn" name="next_edit" value="{#DOC_BUTTON_ADD_DOCUMENT_NEXT#}" />
				{else}
					<input type="submit" class="basicBtn" name="doc_after" value="{#DOC_BUTTON_ADD_DOCUMENT#}" />
					&nbsp;
					<input type="submit" class="blackBtn SaveEdit" value="{#DOC_BUTTON_ADD_DOCUMENT_NEXT#}" />
				{/if}
			</div>
		</div>

		<div class="fix"></div>
	</div>

</form>

<div class="widget first">
	<div class="head">
		<h5>{#DOC_REVISSION#}</h5>
	</div>
	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<col>
		<col>
		{if $document->canDelRev == 1}
		<col width="20">
		<col width="20">
		<col width="20">
		{else}
		<col width="60">
		{/if}

		<thead>
		<tr>
			<td>{#DOC_REVISSION_DATA#}</td>
			<td>{#DOC_REVISSION_USER#}</td>
			<td colspan="3">{#DOC_ACTIONS#}</td>
		</tr>
		</thead>

		<tbody>
		{if $document_rev}
		{foreach from=$document_rev item=doc_rev}
			<tr id="{$doc_rev->doc_revision}">
				<td align="center"><span class="date_text dgrey">{$doc_rev->doc_revision|date_format:$TIME_FORMAT|pretty_date}</span></td>
				<td align="center">{$doc_rev->user_id}</td>
				<td align="center"><a class="topleftDir icon_sprite ico_look" title="{#DOC_REVISSION_VIEW#}" href="../?id={$doc_rev->doc_id}&revission={$doc_rev->doc_revision}" target="_blank"></a></td>
				{if $document->canDelRev == 1}
				<td><a class="topleftDir ConfirmRecover icon_sprite ico_copy" title="{#DOC_REVISSION_RECOVER#}" dir="{#DOC_REVISSION_RECOVER#}" name="{#DOC_REVISSION_RECOVER_T#}" href="index.php?do=docs&action=revision_recover&doc_id={$doc_rev->doc_id}&revission={$doc_rev->doc_revision}&rubric_id={$smarty.request.rubric_id}&cp={$sess}"></a></td>
				<td><a class="topleftDir ConfirmDeleteRev icon_sprite ico_delete" title="{#DOC_REVISSION_DELETE#}" dir="{#DOC_REVISSION_DELETE#}" rev="{$doc_rev->doc_revision}" name="{#DOC_REVISSION_DELETE_T#}" href="index.php?do=docs&action=revision_delete&doc_id={$doc_rev->doc_id}&revission={$doc_rev->doc_revision}&rubric_id={$smarty.request.rubric_id}&cp={$sess}"></a></td>
				{/if}
			</tr>
		{/foreach}
		{else}
			<tr>
				<td colspan="5">
					<ul class="messages">
						<li class="highlight yellow">{#DOC_REVISSION_NO_ITEMS#}</li>
					</ul>
				</td>
			</tr>
		{/if}
		</tbody>
	</table>
	<div class="fix"></div>
</div>

<script language="Javascript" type="text/javascript">

	var sett_options = {ldelim}
		url: '{$document->formaction}',
		beforeSubmit: Request,
		dataType: 'json',
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

	function SaveAjax () {ldelim}
		{if $smarty.session.use_editor == '0'}if (window.CKEDITOR) for(var instanceName in CKEDITOR.instances) CKEDITOR.instances[instanceName].updateElement();{/if}
		{if $smarty.request.action=='edit'}
		$('#formDoc').ajaxSubmit(sett_options);
		{else}
		$('#formDoc').submit();
		{/if}
	{rdelim}

	function docLook () {ldelim}
		{if $smarty.request.action=='edit'}
		window.open('/{if $smarty.request.Id!=1}index.php?id={$smarty.request.Id}{/if}','_blank');
		{else}
		jAlert('{#DOC_CTRLO_ALERT#}','{#DOC_CTRLO_TIT#}');
		{/if}
	{rdelim}

	$(document).ready(function(){ldelim}

		Mousetrap.bind(['ctrl+s', 'command+s'], function(event) {ldelim}
			event.preventDefault();
			{if $smarty.session.use_editor == '0'}if (window.CKEDITOR) for(var instanceName in CKEDITOR.instances) CKEDITOR.instances[instanceName].updateElement();{/if}
			SaveAjax();
			return false;
		{rdelim});

		$('.SaveEdit').click(function (event) {ldelim}
			event.preventDefault();
			{if $smarty.session.use_editor == '0'}if (window.CKEDITOR) for(var instanceName in CKEDITOR.instances) CKEDITOR.instances[instanceName].updateElement();{/if}
			SaveAjax();
			return false;
		{rdelim});

		Mousetrap.bind(['ctrl+o', 'meta+o'], function (event) {ldelim}
			event.preventDefault();
			docLook();
			return false;
		{rdelim});
	{if $smarty.session.use_editor == '0'}
		{literal}
			window.onload = function(){
				if (window.CKEDITOR) {
					CKEDITOR.on('instanceReady', function (event) {
						event.editor.setKeystroke(CKEDITOR.CTRL + 83 /*S*/, 'savedoc');
					});
				}
			}
		{/literal}
	{/if}
	{rdelim});
</script>
