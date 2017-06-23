<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">

<head>

	<meta http-equiv="content-type" content="text/html; charset=UTF-8" />

	<title>{#MAIN_PAGE_TITLE#} - {*#SUB_TITLE#*} ({$smarty.session.user_name|escape})</title>

	<meta name="robots" content="noindex, nofollow">
	<meta http-equiv="pragma" content="no-cache">
	<meta name="Expires" content="Mon, 06 Jan 1990 00:00:01 GMT">

	<!-- CSS Files -->
	<link href="{$tpl_dir}/css/reset.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="{$tpl_dir}/css/main.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="{$tpl_dir}/css/data_table.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="{$tpl_dir}/css/jquery-ui_custom.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="{$tpl_dir}/css/color_{$smarty.const.DEFAULT_THEME_FOLDER_COLOR}.css" rel="stylesheet" type="text/css" media="screen" />
	<link href="{$tpl_dir}/css/browser.css" rel="stylesheet" type="text/css" media="screen" />

	<!-- JS files -->
	{include file="../scripts.tpl"}

	<script type="text/javascript" src="http://bp.yahooapis.com/2.4.21/browserplus-min.js"></script>
	<script type="text/javascript" src="{$ABS_PATH}lib/scripts/uploader/plupload.full.min.js"></script>
	<script type="text/javascript" src="{$ABS_PATH}lib/scripts/uploader/jquery.plupload.queue.js"></script>
	<script type="text/javascript" src="{$ABS_PATH}lib/scripts/uploader/i18n/{$smarty.session.admin_language}.js"></script>

	<script src="{$tpl_dir}/js/main.js" type="text/javascript"></script>

	<!-- JS Scripts -->
	<script type="text/javascript">
		var ave_path = "{$ABS_PATH}";
		var ave_theme = "{$smarty.const.DEFAULT_THEME_FOLDER}";
		var ave_admintpl = "{$tpl_dir}";

		$(document).ready(function(){ldelim}

			$('.openDialog').prop(
			{ldelim}
				href: 'index.php?do=browser&type={$smarty.request.type|escape}&target={$smarty.request.target|escape}&action=upload&directory=' + $('#DirName').val()
			{rdelim}
			);

			{literal}
			var mainframe = $('#mainframe');
			var height = $("body").height();
			mainframe.css({"height": height-280});

			$(window).bind(
					'resize',
					function()
					{
						$(window).resize(function() {
							var mainframe = $('#mainframe');
							var height = $("body").height();
							mainframe.css({"height": height-280});
						});
					}
			);
			{/literal}

		{rdelim});

	</script>

</head>

<body>
<!-- Wrapper -->
<div class="wrapper">
	<!-- Content -->
	<div class="content" id="contentPage" style="padding: 0px;">

	<div class="first"></div>
	<div class="title"><h5>{#MAIN_FILE_MANAGER_TITLE#}</h5></div>
	<div class="widget" style="margin-top: 0px;">
		<div class="body">
			{#MAIN_FILE_MANAGER_TIP#}
		</div>
	</div>

<div class="widget first">
<form style="display:inline;" name="bForm" onSubmit="return false;" class="mainForm">
	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<tr>
			<td>
				<div class="pr12"><input type="text" name="bDirName" id="DirName" size="20" style="width:100%;" readonly="readonly" /></div>
			</td>
			<td width="5%" nowrap="nowrap">
				<input type="button" class="basicBtn" onClick="NewFolder();" value="{#MAIN_MP_CREATE_FOLDER#}" />&nbsp;
			</td>
			{if check_permission('mediapool_add')}
			<td width="5%" nowrap="nowrap">
				<a class="button basicBtn openDialog" data-modal="true" data-height="455" href="index.php?do=browser&type={$smarty.request.type|escape}&target={$smarty.request.target|escape}&action=upload&dir=" data-title="{#MAIN_MP_UPLOAD_FILE#}">{#MAIN_MP_UPLOAD_FILE#}</a>
			</td>
			{/if}
		</tr>

		<tr valign="top">
			<td{if check_permission('mediapool_add')} colspan="3"{else} colspan="2"{/if}>
				<div style="border:1px solid #d4d4d4; overflow:hidden; height:100%; width:100%">
					<iframe id="mainframe" frameborder="0" name="zf" id="zf" width="100%" height="100%" scrolling="Yes" src="index.php?onlycontent=1&do=browser&type={$smarty.request.type|escape}&action=list&dir={$dir}&target={$smarty.request.target|escape}"></iframe>
				</div>
			</td>
		</tr>

		{if $smarty.request.type!=''}
			<tr>
				<td{if check_permission('mediapool_add')} colspan="2"{/if}>
					<div class="pr12"><input type="text" name="bFileName" size="20" style="width:100%;" readonly="readonly" /></div>
				</td>
				<td>
					<input type="button" class="basicBtn" onClick="submitTheForm();" value="{if $smarty.request.type != 'directory'}{#MAIN_MP_FILE_INSERT#}{else}{#MAIN_MP_DIR_INSERT#}{/if}" />
				</td>
			</tr>
		{/if}
	</table>
</form>
</div>

	</div>
	<div class="fix"></div>
</div>

{if $smarty.session.use_editor == 0}

<script type="text/javascript">

function getUrlParam(paramName)
{ldelim}
	var reParam = new RegExp('(?:[\?&]|&amp;)' + paramName + '=([^&]+)', 'i') ;
	var match = window.location.search.match(reParam) ;

	return (match && match.length > 1) ? match[1] : '' ;
{rdelim}

function submitTheForm() {ldelim}
	if (document.bForm.bFileName.value == '' && ('{$target}' != 'dir' && '{$target}' != 'directory' && '{$target}' != 'cascad')) {ldelim}
		alert('{#MAIN_MP_PLEASE_SELECT#}');
	{rdelim}
	else {ldelim}

{if	$target=='link'}
	var funcNum = getUrlParam('CKEditorFuncNum');
	var fileUrl = '{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;
	window.opener.CKEDITOR.tools.callFunction(funcNum, fileUrl);

{elseif $target=='link_image'}
	window.opener.document.getElementById('txtLnkUrl').value = '/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;

{elseif $target=='txtUrl'}
	var funcNum = getUrlParam('CKEditorFuncNum');
	var fileUrl = '/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value
	window.opener.CKEDITOR.tools.callFunction(funcNum, fileUrl);

{elseif $target=='navi'}
	window.opener.document.getElementById('{$smarty.request.id|escape}').value = '/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;

{elseif $target=='img_feld' || $target_img=='img_feld'}
	window.opener.document.getElementById('img_feld__{$target_id}').value = '/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;
	window.opener.document.getElementById('_img_feld__{$target_id}').src = '../index.php?mode=f&width=128&height=128&thumb=/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;
	window.opener.$('.preview__{$target_id}').attr("href", '../index.php?mode=f&width=128&height=128&thumb=/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value);

{elseif $target!='' && $target_id!='' && $target_id!=null}

{if $target=='image'}
	window.opener.$('#preview__{$target_id}').attr('src', '../index.php?mode=f&width=128&height=128&thumb=/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value);
	window.opener.$('.preview__{$target_id}').attr('href', '/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value);
	window.opener.$('#{$target}__{$target_id}').val('/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value);
{/if}

{if $target=='dir'}
	var bdn = document.bForm.bDirName.value.split('/').reverse();
	window.opener.document.getElementById('{$target}__{$target_id}').value = bdn[1];
{/if}

{if $target=='directory'}
	window.opener.$.fn.myPlugin('/{$mediapath}' + document.bForm.bDirName.value, {$target_id});
{/if}

{if $target=='cascad'}
	{assign var=data value="_"|explode:$target_id}
	window.opener.$.fn.myPlugin('/{$mediapath}' + document.bForm.bDirName.value, {$data[0]}, {$data[1]});
{/if}

{elseif $target!='all'}
	{if $smarty.request.fillout=='dl'}
			window.opener.document.getElementById('{$smarty.request.target|escape}').value = '/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;
	{else}
			window.opener.updatePreview();
	{/if}
{/if}
	setTimeout("self.close();", 100);
	{rdelim}
{rdelim}

function NewFolder() {ldelim}
	var title = '{#MAIN_MP_CREATE_FOLDER#}';
	var text = '{#MAIN_ADD_FOLDER#}';
	jPrompt(text, '', title, function(b){ldelim}
				if (b){ldelim}
						$.alerts._overlay('hide');
						$.alerts._overlay('show');
						parent.frames['zf'].location.href='index.php?do=browser&type={$smarty.request.type|escape}&target={$smarty.request.target|escape}&action=list&dir=' + document.bForm.bDirName.value + '&newdir=' + b;
						$.alerts._overlay('hide');
				{rdelim}
				else
				{ldelim}
					$.alerts._overlay('hide');
					$.jGrowl('{#MAIN_NO_ADD_FOLDER#}');
				{rdelim}
			{rdelim}
		);
{rdelim}

</script>

{else}

<script type="text/javascript">
function submitTheForm() {ldelim}

	if (document.bForm.bFileName.value == '' && ('{$target}' != 'dir' && '{$target}' != 'img_importfeld' && '{$target}' != 'directory')) {ldelim}
		alert('{#MAIN_MP_PLEASE_SELECT#}');
	{rdelim}
	else
	{ldelim}

{if		$target=='link'}
		window.opener.document.getElementById('txtUrl').value = '/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;

{elseif $target=='link_image'}
		window.opener.document.getElementById('txtLnkUrl').value = '/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;
		window.opener.UpdatePreview();

{elseif $target=='txtUrl'}
		window.opener.document.getElementById('txtUrl').value = '/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;
		window.opener.UpdatePreview();

{elseif $target=='navi'}
		/*window.opener.document.getElementById('Link_{$smarty.request.id|escape}').value = '{$cppath}/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;*/
		window.opener.document.getElementById('{$smarty.request.id|escape}').value = '/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;
		/*window.opener.document.getElementById('Titel_{$smarty.request.id|escape}').value = document.bForm.bFileName.value;*/

{elseif $target=='img_feld' || $target_img=='img_feld'}
/*
		window.opener.document.getElementById('img_feld__{$target_id}').value = '{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;
		window.opener.document.getElementById('span_feld__{$target_id}').style.display = '';
		window.opener.document.getElementById('_img_feld__{$target_id}').src = '../index.php?mode=f&width=128&height=128&thumb=/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;
		window.opener.document.getElementById('preview__{$target_id}').href = '../index.php?mode=f&width=128&height=128&thumb=/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;
*/
	window.opener.document.getElementById('img_feld__{$target_id}').value = '/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;
	window.opener.document.getElementById('_img_feld__{$target_id}').src = '../index.php?mode=f&width=128&height=128&thumb=/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;
	window.opener.$('.preview__{$target_id}').attr("href", '../index.php?mode=f&width=128&height=128&thumb=/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value);

{elseif $target!='' && $target_id!='' && $target_id!=null}
{if $target=='image'}
		window.opener.$('#preview__{$target_id}').attr('src', '../index.php?mode=f&width=128&height=128&thumb=/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value);
		window.opener.$('.preview__{$target_id}').attr('href', '/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value);
		window.opener.document.getElementById('{$target}__{$target_id}').value = '/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;
	{/if}

{if $target=='dir'}
		//1
		var bdn = document.bForm.bDirName.value.split('/').reverse();
		window.opener.document.getElementById('{$target}__{$target_id}').value = bdn[1];
{elseif $target=='img_importfeld'}
		//2
		var bdn = document.bForm.bDirName.value.split('/').reverse();
		window.opener.document.getElementById('{$target}__{$target_id}').value = '/{$mediapath}/'+bdn[1]+'/';
{else}
		//3
		window.opener.document.getElementById('{$target}__{$target_id}').value = '/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;
{/if}

{if $target=='directory'}
	window.opener.$.fn.myPlugin('/{$mediapath}' + document.bForm.bDirName.value, {$target_id});
{/if}

{elseif $target!='all'}
{if $smarty.request.fillout=='dl'}
		window.opener.document.getElementById('{$smarty.request.target|escape}').value = '/{$mediapath}' + document.bForm.bDirName.value + document.bForm.bFileName.value;
{else}
		window.opener.updatePreview();
{/if}
{/if}
		setTimeout("self.close();", 100);
	{rdelim}
{rdelim}

function NewFolder() {ldelim}
	var title = '{#MAIN_MP_CREATE_FOLDER#}';
	var text = '{#MAIN_ADD_FOLDER#}';
	jPrompt(text, '', title, function(b){ldelim}
				if (b){ldelim}
					$.alerts._overlay('hide');
					$.alerts._overlay('show');
					parent.frames['zf'].location.href='index.php?do=browser&type={$smarty.request.type|escape}&target={$smarty.request.target|escape}&action=list&dir=' + document.bForm.bDirName.value + '&newdir=' + b;
					$.alerts._overlay('hide');
				{rdelim}
				else
				{ldelim}
					$.alerts._overlay('hide');
					$.jGrowl('{#MAIN_NO_ADD_FOLDER#}');
				{rdelim}
			{rdelim}
		);
{rdelim}

</script>
{/if}

<!-- Footer -->
<div id="footer">
	<div class="wrapper">
		<span class="floatleft">{#oficial_site#}: {$smarty.const.APP_INFO}</span>
		<span class="floatleft ml20">{#support#}: <a href="mailto:support@ave-cms.ru">support@ave-cms.ru</a></span>
		<span class="floatright">{$smarty.const.APP_NAME} v{$smarty.const.APP_VERSION}</span>
	</div>
</div>

</body>
</html>