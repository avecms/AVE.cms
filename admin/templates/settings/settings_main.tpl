<div class="title"><h5>{#SETTINGS_MAIN_TITLE#}</h5></div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">
		{#SETTINGS_SAVE_INFO#}
	</div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li>{#SETTINGS_MAIN_TITLE#}</li>
		</ul>
	</div>
</div>

{$message}

<div class="widget first">
	<div class="body">
		{if check_permission('cache_clear')}<a class="button basicBtn clearCacheSess" href="javascript:void(0);">{#MAIN_STAT_CLEAR_CACHE_FULL#}</a>{/if}
		&nbsp;
		{if check_permission('cache_thumb')}<a class="button basicBtn clearThumb" href="javascript:void(0);">{#MAIN_STAT_CLEAR_THUMB#}</a>{/if}
		&nbsp;
		{if check_permission('document_revisions')}<a class="button basicBtn clearRev" href="javascript:void(0);">{#MAIN_STAT_CLEAR_REV#}</a>{/if}
		&nbsp;
		{if check_permission('gen_settings')}<a class="button basicBtn clearCount" href="javascript:void(0);">{#MAIN_STAT_CLEAR_COUNT#}</a>{/if}
	</div>
</div>

{if $smarty.const.SYSTEM_CACHE_LIFETIME > 0}
	<ul class="messages first">
		<li class="highlight red"><strong>{#SETTINGS_CACHE_LIFETIME#}</strong></li>
	</ul>
{/if}

<form id="settings" name="settings" method="post" action="index.php?do=settings&cp={$sess}&sub=save" class="mainForm">
<fieldset>

<div class="widget first">

	<ul class="inact_tabs">
		{if check_permission('gen_settings')}<li class="activeTab"><a href="index.php?do=settings&cp={$sess}">{#SETTINGS_MAIN_SETTINGS#}</a></li>{/if}
		{if check_permission('gen_settings_more')}<li><a href="index.php?do=settings&sub=case&cp={$sess}">{#SETTINGS_CASE_TITLE#}</a></li>{/if}
		{if check_permission('gen_settings_countries')}<li><a href="index.php?do=settings&sub=countries&cp={$sess}">{#MAIN_COUNTRY_EDIT#}</a></li>{/if}
		{if check_permission('gen_settings_languages')}<li><a href="index.php?do=settings&sub=language&cp={$sess}">{#SETTINGS_LANG_EDIT#}</a></li>{/if}
		<li><a href="index.php?do=settings&action=paginations&cp={$sess}">{#SETTINGS_PAGINATION#}</a></li>
	</ul>

<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
<col width="300" />
<col />

<thead>
<tr>
	<td>{#SETTINGS_NAME#}</td>
	<td><div class="pr12">{#SETTINGS_VALUE#}</div></td>
</tr>
</thead>

<tbody>
<tr>
	<td>{#SETTINGS_SITE_NAME#}</td>
	<td><div class="pr12"><input type="text" name="site_name" id="site_name" value="{$row.site_name}" maxlength="200" class="mousetrap"></div></td>
</tr>

<tr>
	<td>{#SETTINGS_SITE_COUNTRY#}</td>
	<td>
		<div class="pr12">
	<select name="default_country" style="width: 300px;">
	{foreach from=$available_countries item=land}
		<option value="{$land->country_code}"{if $row.default_country==$land->country_code} selected{/if}>{$land->country_name}</option>
	{/foreach}
	</select>
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_DATE_FORMAT#}</td>
	<td>
		<div class="pr12">
	<select name="date_format" style="width: 300px;">
	{foreach from=$date_formats item=date_format}
		<option value="{$date_format}"{if $row.date_format==$date_format} selected{/if}>{$smarty.now|date_format:$date_format|pretty_date}</option>
	{/foreach}
	</select>
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_TIME_FORMAT#}</td>
	<td>
		<div class="pr12">
	<select name="time_format" style="width: 300px;">
	{foreach from=$time_formats item=time_format}
		<option value="{$time_format}"{if $row.time_format==$time_format} selected{/if}>{$smarty.now|date_format:$time_format|pretty_date}</option>
	{/foreach}
	</select>
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_USE_DOCTIME#}</td>
	<td>
		<div class="pr12">
			<input type="radio" name="use_doctime" value="1"{if $row.use_doctime==1} checked{/if} /><label style="cursor: pointer;">{#SETTINGS_YES#}</label>&nbsp;
			<input type="radio" name="use_doctime" value="0"{if $row.use_doctime==0} checked{/if} /><label style="cursor: pointer;">{#SETTINGS_NO#}</label>
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_USE_EDITOR#}</td>
	<td>
		<div class="pr12">
			<input type="radio" name="use_editor" value="0"{if $row.use_editor==0} checked{/if} /><label style="cursor: pointer;">{#SETTINGS_EDITOR_CKEDITOR#}</label>
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_ERROR_PAGE#}</td>
	<td>
		<div class="pr12">
			<input name="page_not_found_id" type="text" id="page_not_found_id" value="{$row.page_not_found_id}" size="4" maxlength="10" readonly style="width: 200px" class="mousetrap" />&nbsp;<input onClick="openLinkWindowSelect('page_not_found_id','page_not_found_id');" type="button" class="basicBtn" value="... " />&nbsp;&nbsp;&nbsp;{#SETTINGS_PAGE_DEFAULT#}
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_TEXT_PERM#}</td>
	<td>
		<div class="pr12">
			<textarea name="message_forbidden" id="message_forbidden" rows="8" cols class="mousetrap">{$row.message_forbidden|stripslashes}</textarea>
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_HIDDEN_TEXT#}</td>
	<td>
		<div class="pr12">
			<textarea name="hidden_text" id="hidden_text" rows="8" cols class="mousetrap">{$row.hidden_text|stripslashes}</textarea>
		</div>
	</td>
</tr>
</tbody>
</table>
</div>

<div class="widget first">
<div class="head"><h5 class="iFrames">{#SETTINGS_MAIN_MAIL#}</h5></div>
<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
<col width="300" />
<col />

<thead>
<tr>
	<td>{#SETTINGS_NAME#}</td>
	<td><div class="pr12">{#SETTINGS_VALUE#}</div></td>
</tr>
</thead>
<tbody>
<tr>
	<td>{#SETTINGS_EMAIL_NAME#}</td>
	<td>
		<div class="pr12">
		  <input type="text" name="mail_from_name" id="mail_from_name" value="{$row.mail_from_name}" style="width: 250px;" class="mousetrap">
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_EMAIL_SENDER#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="mail_from" id="mail_from" value="{$row.mail_from}" style="width: 250px;" class="mousetrap">
			<input type="hidden" name="mail_content_type" id="mail_content_type" value="text/plain" />
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_TEXT_EMAIL#}<br /><small>{#SETTINGS_TEXT_INFO#}</small></td>
	<td>
		<div class="pr12">
			<textarea name="mail_new_user" id="mail_new_user" rows="12" cols class="mousetrap">{$row.mail_new_user|stripslashes}</textarea>
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_EMAIL_FOOTER#}</td>
	<td>
		<div class="pr12">
			<textarea name="mail_signature" id="mail_signature" rows="8" cols class="mousetrap">{$row.mail_signature|stripslashes}</textarea>
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_SYMBOL_BREAK#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="mail_word_wrap" id="mail_word_wrap" value="{$row.mail_word_wrap}" max="1000" style="width: 50px;float:left;" class="mousetrap">
            <label>{#SETTINGS_SYMBOL_BREAK_INFO#}</label>
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_MAIL_TRANSPORT#}</td>
	<td>
		<div class="pr12">
			<select name="mail_type" id="mail_type" style="width: 250px;">
				<option value="mail"{if $row.mail_type=='mail'} selected{/if}>{#SETTINGS_MAIL#}</option>
				<option id="smtp" value="smtp"{if $row.mail_type=='smtp'} selected{/if}>{#SETTINGS_SMTP#}</option>
				<option value="sendmail"{if $row.mail_type=='sendmail'} selected{/if}>{#SETTINGS_SENDMAIL#}</option>
			</select>
		</div><div id="div_select"></div>
	</td>
</tr>

<tr class="smtp_group">
	<td>{#SETTINGS_SMTP_SERVER#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="mail_host" value="{$row.mail_host}" style="width: 250px;" class="mousetrap">
		</div>
	</td>
</tr>

<tr class="smtp_group">
	<td>{#SETTINGS_MAIL_PORT#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="mail_port" value="{$row.mail_port}" maxlength="5" style="width: 250px;" class="mousetrap">
		</div>
	</td>
</tr>

<tr class="smtp_group">
	<td>{#SETTINGS_SMTP_NAME#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="mail_smtp_login" value="{$row.mail_smtp_login}" style="width: 250px;" class="mousetrap">
		</div>
	</td>
</tr>

<tr class="smtp_group">
	<td>{#SETTINGS_SMTP_PASS#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="mail_smtp_pass" value="{$row.mail_smtp_pass}" style="width: 250px;" class="mousetrap">
		</div>
	</td>
</tr>

<tr class="smtp_group">
	<td>{#SETTINGS_SMTP_ENCRYPT#}</td>
	<td>
		<div class="pr12">
			<select name="mail_smtp_encrypt" style="width: 250px;" class="mousetrap">
              <option value="">{#SETTINGS_SMTP_NOENCRYPT#}</option>
              <option value="tls"{if $row.mail_smtp_encrypt=='tls'} selected="selected"{/if}>TLS</option>
              <option value="ssl"{if $row.mail_smtp_encrypt=='ssl'} selected="selected"{/if}>SSL</option>
            </select>
		</div>
	</td>
</tr>

<tr class="sendmail_group">
	<td>{#SETTINGS_MAIL_PATH#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="mail_sendmail_path" id="mail_sendmail_path" value="{$row.mail_sendmail_path}" style="width: 250px;" class="mousetrap">
		</div>
	</td>
</tr>
</tbody>
</table>
</div>

<div class="widget first">
<div class="head"><h5 class="iFrames">{#SETTINGS_MAIN_PAGENAVI#}</h5></div>
<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
<col width="300" />
<col />
<col width="300" />
<col />
<thead>
<tr>
	<td>{#SETTINGS_NAME#}</td>
	<td>{#SETTINGS_VALUE#}</td>
	<td>{#SETTINGS_NAME#}</td>
	<td>{#SETTINGS_VALUE#}</td>
</tr>
</thead>
<tbody>
<tr>
	<td>{#SETTINGS_NAVI_BOX#}</td>
	<td colspan="3">
		<div class="pr12">
			<input type="text" name="navi_box" id="navi_box" value="{$row.navi_box|escape|stripslashes}" class="mousetrap">
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_LINK_BOX#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="link_box" id="link_box" value="{$row.link_box|escape|stripslashes}" class="mousetrap">
		</div>
	</td>

	<td>{#SETTINGS_ACTIVE_LINK_BOX#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="active_box" id="active_box" value="{$row.active_box|escape|stripslashes}" class="mousetrap">
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_TOTAL_BOX#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="total_box" id="total_box" value="{$row.total_box|escape|stripslashes}" class="mousetrap">
		</div>
	</td>

	<td>{#SETTINGS_PAGE_BEFORE#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="total_label" id="total_label" value="{$row.total_label|escape|stripslashes}" class="mousetrap">
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_PAGE_SEPARATOR#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="separator_label" id="separator_label" value="{$row.separator_label|escape|stripslashes}" class="mousetrap">
		</div>
	</td>

	<td>{#SETTINGS_PAGE_SEPAR#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="separator_box" id="separator_box" value="{$row.separator_box|escape|stripslashes}" class="mousetrap">
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_PAGE_START#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="start_label" id="start_label" value="{$row.start_label|escape|stripslashes}" class="mousetrap">
		</div>
	</td>

	<td>{#SETTINGS_PAGE_END#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="end_label" id="end_label" value="{$row.end_label|escape|stripslashes}" class="mousetrap">
		</div>
	</td>
</tr>

<tr>
	<td>{#SETTINGS_PAGE_NEXT#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="next_label" id="next_label" value="{$row.next_label|escape|stripslashes}" class="mousetrap">
		</div>
	</td>

	<td>{#SETTINGS_PAGE_PREV#}</td>
	<td>
		<div class="pr12">
			<input type="text" name="prev_label" id="prev_label" value="{$row.prev_label|escape|stripslashes}" class="mousetrap">
		</div>
	</td>
</tr>

</tbody>

</table>
</div>

<div class="widget first">
	<div class="head"><h5 class="iFrames">{#SETTINGS_MAIN_BREADCRUMBS#}</h5></div>
	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<col width="300" />
		<col />
		<col width="300" />
		<col />
		<thead>
			<tr>
				<td>{#SETTINGS_NAME#}</td>
				<td><div class="pr12">{#SETTINGS_VALUE#}</div></td>
				<td>{#SETTINGS_NAME#}</td>
				<td><div class="pr12">{#SETTINGS_VALUE#}</div></td>
			</tr>
		</thead>
		<tbody>
			<tr>
				<td>{#SETTINGS_BREAD_MAIN#}</td>
				<td>
					<div class="pr12">
						<input type="radio" name="bread_show_main" value="1"{if $row.bread_show_main==1} checked{/if} /><label style="cursor: pointer;">{#SETTINGS_YES#}</label>&nbsp;
						<input type="radio" name="bread_show_main" value="0"{if $row.bread_show_main==0} checked{/if} /><label style="cursor: pointer;">{#SETTINGS_NO#}</label>
					</div>
				</td>
				<td>{#SETTINGS_BREAD_HOST#}</td>
				<td>
					<div class="pr12">
						<input type="radio" name="bread_show_host" value="1"{if $row.bread_show_host==1} checked{/if} /><label style="cursor: pointer;">{#SETTINGS_YES#}</label>&nbsp;
						<input type="radio" name="bread_show_host" value="0"{if $row.bread_show_host==0} checked{/if} /><label style="cursor: pointer;">{#SETTINGS_NO#}</label>
					</div>
				</td>
			</tr>
			<tr>
				<td>{#SETTINGS_BREAD_BOX#}</td>
				<td colspan="3">
					<div class="pr12">
						<input type="text" name="bread_box" id="bread_box" value="{$row.bread_box|escape|stripslashes}" class="mousetrap">
					</div>
				</td>
			</tr>
			<tr>
				<td>{#SETTINGS_BREAD_BOX_LINK#}</td>
				<td>
					<div class="pr12">
						<input type="text" name="bread_link_box" id="bread_link_box" value="{$row.bread_link_box|escape|stripslashes}" class="mousetrap">
					</div>
				</td>

				<td>{#SETTINGS_BREAD_LINK_TPL#}</td>
				<td>
					<div class="pr12">
						<input type="text" name="bread_link_template" id="bread_link_template" value="{$row.bread_link_template|escape|stripslashes}" class="mousetrap">
					</div>
				</td>
			</tr>

			<tr>
				<td>{#SETTINGS_BREAD_SELF_BOX#}</td>
				<td>
					<div class="pr12">
						<input type="text" name="bread_self_box" id="bread_self_box" value="{$row.bread_self_box|escape|stripslashes}" class="mousetrap">
					</div>
				</td>
				<td>{#SETTINGS_BREAD_BOX_LASTLINK#}</td>
				<td>
					<div class="pr12">
						<input type="radio" name="bread_link_box_last" value="1"{if $row.bread_link_box_last==1} checked{/if} /><label style="cursor: pointer;">{#SETTINGS_YES#}</label>&nbsp;
						<input type="radio" name="bread_link_box_last" value="0"{if $row.bread_link_box_last==0} checked{/if} /><label style="cursor: pointer;">{#SETTINGS_NO#}</label>
					</div>
				</td>
			</tr>

			<tr>
				<td>{#SETTINGS_BREAD_SEPPARATOR#}</td>
				<td>
					<div class="pr12">
						<input type="text" name="bread_sepparator" id="bread_sepparator" value="{$row.bread_sepparator|escape|stripslashes}" class="mousetrap">
					</div>
				</td>
				<td>{#SETTINGS_BREAD_SEPP_USE#}</td>
				<td>
					<div class="pr12">
						<input type="radio" name="bread_sepparator_use" value="1"{if $row.bread_sepparator_use==1} checked{/if} /><label style="cursor: pointer;">{#SETTINGS_YES#}</label>&nbsp;
						<input type="radio" name="bread_sepparator_use" value="0"{if $row.bread_sepparator_use==0} checked{/if} /><label style="cursor: pointer;">{#SETTINGS_NO#}</label>
					</div>
				</td>
			</tr>

		</tbody>

	</table>

	<div class="rowElem" id="saveBtn">
		<div class="saveBtn">
			<input type="submit" class="basicBtn" value="{#SETTINGS_BUTTON_SAVE#}" />&nbsp;{#SETTINGS_OR#}&nbsp;<input type="submit" class="button blackBtn SaveSettings" value="{#SETTINGS_BUTTON_SAVE_AJAX#}" />
		</div>
	</div>

</div>
</fieldset>
</form>

<script language="javascript">
$("#mail_type").change(function () {ldelim}
	if ($("#mail_type option:selected").val() == "mail") {ldelim}
		$(".smtp_group").hide();
		$(".sendmail_group").hide();
	{rdelim}
	else if ($("#mail_type option:selected").val() == "smtp") {ldelim}
		$(".smtp_group").show();
		$(".sendmail_group").hide();
	{rdelim}
	else if ($("#mail_type option:selected").val() == "sendmail") {ldelim}
		$(".smtp_group").hide();
		$(".sendmail_group").show();
	{rdelim}
{rdelim}).trigger('change');

	var sett_options = {ldelim}
		url: 'index.php?do=settings&sub=save&ajax=1&cp={$sess}',
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

	if ($("#mail_type option:selected").val() != "smtp") {ldelim}
		$(".smtp_group").hide();
	{rdelim}
	if ($("#mail_type option:selected").val() != "sendmail") {ldelim}
		$(".sendmail_group").hide();
	{rdelim}

	$(".SaveSettings").click(function(e){ldelim}
		e.preventDefault();
		var title = '{#SETTINGS_BUTTON_SAVE#}';
		var confirm = '{#SETTINGS_SAVE_CONFIRM#}';
		jConfirm(
				confirm,
				title,
				function(b){ldelim}
					if (b){ldelim}
						$("#settings").ajaxSubmit(sett_options);
					{rdelim}
				{rdelim}
			);
	{rdelim});

	Mousetrap.bind(['ctrl+s', 'command+s'], function(e) {ldelim}
		if (e.preventDefault) {ldelim}
			e.preventDefault();
		{rdelim} else {ldelim}
			// internet explorer
			e.returnValue = false;
		{rdelim}
		$("#settings").ajaxSubmit(sett_options);
		return false;
	{rdelim});

{rdelim});

function openLinkWindowSelect(target,doc) {ldelim}
	if (typeof width=='undefined' || width=='') var width = screen.width * 0.8;
	if (typeof height=='undefined' || height=='') var height = screen.height * 0.6;
	if (typeof doc=='undefined') var doc = 'title';
	if (typeof scrollbar=='undefined') var scrollbar=1;
	var left = ( screen.width - width ) / 2;
	var top = ( screen.height - height ) / 2;
	window.open('index.php?idonly=1&doc='+doc+'&target='+target+'&do=docs&action=showsimple&cp={$sess}&pop=1','pop','left='+left+',top='+top+',width='+width+',height='+height+',scrollbars='+scrollbar+',resizable=1');
{rdelim}
</script>

{include file="$codemirror_connect"}
{include file="$codemirror_editor" textarea_id='message_forbidden' ctrls='$("#settings").ajaxSubmit(sett_options);' height='150'}
{include file="$codemirror_editor" textarea_id='hidden_text' ctrls='$("#settings").ajaxSubmit(sett_options);' height='150'}