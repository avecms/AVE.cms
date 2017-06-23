<div class="first"></div>

<div class="title"><h5>{#SETTINGS_LANG_EDIT#}</h5></div>

<div class="widget" style="margin-top: 0px;">
    <div class="body">
		{#SETTINGS_LANG_TITLE#}
    </div>
</div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
	    <ul>
	        <li class="firstB"><a href="index.php?pop=1" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
	        <li>{#SETTINGS_LANG_EDIT#}</li>
	    </ul>
	</div>
</div>

<form method="post" class="mainForm" action="index.php?do=settings&sub=language&func=save&cp={$sess}">

	<div class="widget first">
		<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
			<col width="50" />
			<col width="100" />
			<col width="200" />
			<thead>
			<tr>
				<td>{#SETTINGS_LANG_SYSTEM#}</td>
				<td>{#SETTINGS_LANG_PREFIX#}</td>
				<td>{#SETTINGS_LANG_NAME#}</td>
			</tr>
			</thead>
			<tr>
				<td><div class="pr12"><input {if $smarty.request.Id!=''}readonly{/if} type="text" name="lang_key" id="lang_key" value="{$items->lang_key}" /></div></td>
				<td><div class="pr12"><input type="text" name="lang_alias_pref" id="lang_alias_pref" value="{$items->lang_alias_pref}" /></div></td>
				<td><div class="pr12"><input type="text" name="lang_name" id="lang_name" value="{$items->lang_name}" /></div></td>
			</tr>
			<tr>
				<td colspan="3">
					<input type="hidden" name="Id" value="{$smarty.request.Id}" />
					{if $smarty.request.Id==''}
					<input type="submit" value="{#SETTINGS_LANG_ADD#}" class="basicBtn" />
					{else}
					<input type="submit" value="{#SETTINGS_LANG_SAVE#}" class="basicBtn" />
					{/if}
				</td>
			</tr>
		</table>
		<div class="fix"></div>
	</div>
</form>