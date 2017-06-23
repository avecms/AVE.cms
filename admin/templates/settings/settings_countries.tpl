<div class="title"><h5>{#SETTINGS_COUNTRIES#}</h5></div>

<div class="widget" style="margin-top: 0px;">
    <div class="body">
		{#SETTINGS_COUNTRY_TIP#}
    </div>
</div>


<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
	    <ul>
	        <li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
	        <li><a href="index.php?do=settings&cp={$sess}">{#SETTINGS_MAIN_TITLE#}</a></li>
			<li>{#SETTINGS_COUNTRIES#}</li>
	    </ul>
	</div>
</div>

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

<form id="settings" name="settings" method="post" action="index.php?do=settings&sub=countries&cp={$sess}&save=1" class="mainForm">
<fieldset>
<div class="widget first">
	<ul class="inact_tabs">
		{if check_permission('gen_settings')}<li><a href="index.php?do=settings&cp={$sess}">{#SETTINGS_MAIN_SETTINGS#}</a></li>{/if}
		{if check_permission('gen_settings_more')}<li><a href="index.php?do=settings&sub=case&cp={$sess}">{#SETTINGS_CASE_TITLE#}</a></li>{/if}
		{if check_permission('gen_settings_countries')}<li class="activeTab"><a href="index.php?do=settings&sub=countries&cp={$sess}">{#MAIN_COUNTRY_EDIT#}</a></li>{/if}
		{if check_permission('gen_settings_languages')}<li><a href="index.php?do=settings&sub=language&cp={$sess}">{#SETTINGS_LANG_EDIT#}</a></li>{/if}
		<li><a href="index.php?do=settings&action=paginations&cp={$sess}">{#SETTINGS_PAGINATION#}</a></li>
	</ul>
			<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
            	<thead>
                	<tr>
                        <td width="20%">{#SETTINGS_COUNTRY_NAME#}</td>
                        <td width="20%">{#SETTINGS_ACTIVE#}</td>
                        <td width="20%">{#SETTINGS_IN_EC#}</td>
                    </tr>
                </thead>
                <tbody>
			{foreach from=$countries item=land name=l}
			<tr {if $land.country_status==1}class="green"{/if}>
				<td>
					<input name="country_name[{$land.Id}]" type="text" id="country_name[{$land.Id}]" value="{$land.country_name}" class="mousetrap" style="width: 95%;"/>
				</td>

				<td>
					<input type="radio" name="country_status[{$land.Id}]" value="1"{if $land.country_status==1} checked{/if} /><label style="cursor: pointer;">{#SETTINGS_YES#}</label>
					<input type="radio" name="country_status[{$land.Id}]" value="2"{if $land.country_status==2} checked{/if} /><label style="cursor: pointer;">{#SETTINGS_NO#}</label>
				</td>

				<td>
					<input type="radio" name="country_eu[{$land.Id}]" value="1"{if $land.country_eu==1} checked{/if} /><label style="cursor: pointer;">{#SETTINGS_YES#}</label>
					<input type="radio" name="country_eu[{$land.Id}]" value="2"{if $land.country_eu==2} checked{/if} /><label style="cursor: pointer;">{#SETTINGS_NO#}</label>
				</td>
			</tr>
			{/foreach}
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


{if $page_nav}
	<div class="pagination">
	<ul class="pages">
		{$page_nav}
	</ul>
	</div>
{/if}

<br />

<script language="javascript">

$(document).ready(function(){ldelim}

    var sett_options = {ldelim}
		url: 'index.php?do=settings&sub=countries&cp={$sess}&save=1',
		beforeSubmit: Request,
        success: Response
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

function Request(){ldelim}
	$.alerts._overlay('show');
	$('html, body').animate({ldelim}scrollTop:0{rdelim});
{rdelim}

function Response(){ldelim}
	$.alerts._overlay('hide');
	$.jGrowl('{#SETTINGS_SAVED#}',{ldelim}theme: 'accept'{rdelim});
{rdelim}

</script>