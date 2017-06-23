{if !$no_edit}

{literal}
<script language="javascript">
function pass_show() {
	if ($("#password_show:checked").val()) {
		type_hide = "pass";
		type_show = "text";
	}
	else {
		type_hide = "text";
		type_show = "pass";
	}
	$("#password_"+type_hide).hide();
	$("#password_"+type_hide).removeAttr("name");
	$("#password_"+type_show).show();
	$("#password_"+type_show).attr("name","password");
	$("#password_"+type_show).val($("#password_"+type_hide).val());
};

function mail_pass(input_id){
	var val = $("#"+input_id).val();
	if (val) {
		$("#mail_pass").show();
		$("#mail_pass input").attr("checked","checked");
		$("#mail_pass a.jqTransformCheckbox").addClass("jqTransformChecked");
	}
	else {
		$("#mail_pass input").removeAttr("checked");
		$("#mail_pass a.jqTransformCheckbox").removeClass("jqTransformChecked");
	}
}

function mail_status(){
	var val = $("#status").val();
	if (val == 1) {
		$("#mail_status").show();
		$("#mail_status input").attr("checked","checked");
		$("#mail_status a.jqTransformCheckbox").addClass("jqTransformChecked");
	}
	else {
		$("#mail_status input").removeAttr("checked");
		$("#mail_status a.jqTransformCheckbox").removeClass("jqTransformChecked");
	}
}
</script>
{/literal}

{if $smarty.request.action=='new'}
	<div class="title"><h5>{#USER_NEW_TITLE#}</h5></div>
	<div class="widget" style="margin-top: 0px;"><div class="body">{#USER_NEW_TIP#}</div></div>
{else}
	<div class="title"><h5>{#USER_EDIT_TITLE#}</h5></div>
	<div class="widget" style="margin-top: 0px;"><div class="body">{#USER_EDIT_TIP#}</div></div>
{/if}

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=user&cp={$sess}" title="">{#USER_SUB_TITLE#}</a></li>
			<li><strong class="code">{if $smarty.request.action=='new'}{$smarty.request.user_name}{else}{if $row->firstname|escape==""}{$row->user_name|escape}{else}{$row->firstname|escape} {$row->lastname|escape}{/if}{/if}</strong></li>
		</ul>
	</div>
</div>


{if $errors}
	<ul class="messages first">
		<li class="highlight red"><strong>{#USER_ERRORS#}</strong><br />{foreach from=$errors item=error}&bull; {$error}<br />{/foreach}</li>
	</ul>
{/if}

<form method="post" action="{$formaction}" enctype="multipart/form-data" class="mainForm">

{if $smarty.request.action=='edit'}
	<input name="Email_Old" type="hidden" value="{$smarty.request.email|stripslashes|default:$row->email|escape}" />
{/if}

<fieldset>

<div class="widget first">
<div class="head"><h5 class="iFrames">{#USER_EDIT_TITLE#}</h5></div>

<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
<tbody>

<tr class="noborder">
	<td width="250">{#USER_LOGIN#}</td>
	<td><div class="pr12"><input name="user_name" type="text" id="user_name" size="40" style="width:250px;" value="{$smarty.request.user_name|stripslashes|default:$row->user_name|escape}" /></div></td>
</tr>

<tr>
	<td>{#USER_FIRSTNAME#}</td>
	<td><div class="pr12"><input name="firstname" type="text" id="firstname" size="40" style="width:250px;" value="{$smarty.request.firstname|stripslashes|default:$row->firstname|escape}" /></div></td>
</tr>

<tr>
	<td>{#USER_LASTNAME#}</td>
	<td><div class="pr12"><input name="lastname" type="text" id="lastname" size="40" style="width:250px;" value="{$smarty.request.lastname|stripslashes|default:$row->lastname|escape}" /></div></td>
</tr>

<tr>
	<td>{#USER_EMAIL#}</td>
	<td><div class="pr12"><input name="email" type="text" id="email" size="40" style="width:250px;" value="{$smarty.request.email|stripslashes|default:$row->email|escape}" /></div></td>
</tr>

<tr>
	<td>{#USER_PASSWORD#}&nbsp;{if $smarty.request.action=='edit'} ({#USER_PASSWORD_CHANGE#}){/if}</td>
	<td>
		<div class="pr12">
		  <input onchange="mail_pass(this.id);" onkeydown="mail_pass(this.id);" onkeyup="mail_pass(this.id);" name="password" type="password" id="password_pass" size="40" style="width:250px;" maxlength="50" autocomplete="off" />
		  <input onchange="mail_pass(this.id);" onkeydown="mail_pass(this.id);" onkeyup="mail_pass(this.id);" type="text" id="password_text" size="40" style="width:250px;display:none;" maxlength="50" autocomplete="off" />
		</div>
		<div class="pr12 mt5">
			<input class="float" type="checkbox" id="password_show" value="1" onchange="pass_show();" /> <label>{#USER_PASSWORD_SHOW#}</label>
		</div>
		{if $smarty.request.action=='edit'}
		<div class="pr12" id="mail_pass" style="display:none; clear:left">
			<input name="PassChange" type="checkbox" value="1" class="float" /> <label style="cursor: pointer;">{#USER_SEND_INFO#}</label>
		</div>
		{/if}
	</td>
</tr>

{if $is_forum==1 && $smarty.request.action=='edit'}
<tr>
	<td>{#USER_NICK#}</td>
	<td><div class="pr12"><input name="BenutzerName_fp" type="text" size="40" style="width:250px;" value="{$row_fp->BenutzerName|escape}" /></div></td>
</tr>

<tr>
	<td>{#USER_SIGNATURE#}</td>
	<td><div class="pr12"><textarea name="Signatur_fp" style="width:400px; height:100px">{$row_fp->Signatur|escape}</textarea></div></td>
</tr>
{/if}

<tr>
	<td>{#USER_AVATAR#}</td>
	<td><div class="pr12">
		{if $row->avatar}<img src="{$row->avatar}">{/if}
		<div class="fix"></div>
		<input type="file" name="avatar" class="nicefileinput nice input_file" />
	</div></td>
</tr>

{if $is_shop==1}
<tr>
	<td>{#USER_TAX#}</td>
	<td><div class="pr12">
					<input type="radio" name="taxpay" value="1" {if $row->taxpay=='1'}checked="checked" {/if}/> <label style="cursor: pointer;">{#USER_YES#}</label>
					<input type="radio" name="taxpay" value="0" {if $row->taxpay=='0'}checked="checked" {/if}/> <label style="cursor: pointer;">{#USER_NO#}</label>
	</div></td>
</tr>
{/if}

<tr>
	<td>{#USER_COMPANY#}</td>
	<td><div class="pr12"><input name="company" type="text" size="40" style="width:250px;" value="{$smarty.request.company|stripslashes|default:$row->company|escape}" /></div></td>
</tr>

<tr>
	<td>{#USER_HOUSE_STREET#}</td>
	<td><div class="pr12">
		<input name="street" type="text" id="street" size="25" style="width:180px;" value="{$smarty.request.street|stripslashes|default:$row->street|escape}" />&nbsp;
		<input name="street_nr" type="text" id="street_nr" size="7" style="width:60px;" maxlength="10" value="{$smarty.request.street_nr|stripslashes|default:$row->street_nr|escape}" />
	</div></td>
</tr>

<tr>
	<td>{#USER_ZIP_CODE#}</td>
	<td><div class="pr12"><input name="zipcode" type="text" id="zipcode" size="40" style="width:250px;" maxlength="20" value="{$smarty.request.zipcode|stripslashes|default:$row->zipcode|escape}" /></div></td>
</tr>

<tr>
	<td>{#USER_CITY#}</td>
	<td><div class="pr12"><input name="city" type="text" id="city" size="40" style="width:250px;" value="{$smarty.request.city|stripslashes|default:$row->city|escape}" /></div></td>
</tr>

<tr>
	<td>{#USER_COUNTRY#}</td>
	<td><div class="pr12">
		<select name="country" style="width:250px;">
			{if $smarty.request.action=='new'}
				{assign var=sL value=$smarty.request.country|default:$smarty.session.user_language|lower|escape|stripslashes}
			{else}
				{assign var=sL value=$row->country|lower|escape|stripslashes}
			{/if}
			{assign var=sL value=$row->country|escape|stripslashes}

			{foreach from=$available_countries item=land}
				<option value="{$land->country_code}"{if $sL == $land->country_code} selected="selected"{/if}>{$land->country_name|escape}</option>
			{/foreach}
		</select>
	</div></td>
</tr>

<tr>
	<td>{#USER_PHONE#}</td>
	<td><div class="pr12"><input name="phone" type="text" id="phone" size="40" style="width:250px;" value="{$smarty.request.phone|stripslashes|default:$row->phone|escape}" /></div></td>
</tr>

<tr>
	<td>{#USER_FAX#}</td>
	<td><div class="pr12"><input name="telefax" type="text" id="telefax" size="40" style="width:250px;" value="{$smarty.request.telefax|stripslashes|default:$row->telefax|escape}" /></div></td>
</tr>

<tr>
	<td>{#USER_BIRTHDAY#} <small>{#USER_BIRTHDAY_FORMAT#}</small></td>
	<td><div class="pr12"><input name="birthday" type="text" id="birthday" size="25" style="width:250px;" maxlength="10" value="{$smarty.request.birthday|stripslashes|default:$row->birthday|escape}" /></div></td>
</tr>

<tr>
	<td>{#USER_NOTICE#}</td>
	<td><div class="pr12"><textarea name="description" style="width:400px; height:100px" id="description">{$smarty.request.description|stripslashes|default:$row->description|escape}</textarea></div></td>
</tr>

{assign var=u_group value=$row->user_group|lower|escape|stripslashes}
<tr>
	<td>{#USER_MAIN_GROUP#}</td>
	<td>
		<div class="pr12">
			{if check_permission('user_perms')}
				{if ($smarty.session.user_id == $row->Id) && $row->user_group != 1}
					<select name="user_group" style="width: 200px;" disabled="disabled">
						{foreach from=$ugroups item=groups}
							{if $groups->user_group!=2}
								<option value="{$groups->user_group}"{if $groups->user_group==$row->user_group} selected="selected"{/if}>{$groups->user_group_name}</option>
							{/if}
						{/foreach}
					</select>
				{else}
				<select name="user_group" style="width: 200px;" {if $row->Id==1 && $groups->user_group!=1} disabled="disabled"{/if}>
					{foreach from=$ugroups item=groups}
						{if $smarty.session.user_group != 1}
							{if $row->user_group == 1 && $groups->user_group != 1}
								<option value="{$groups->user_group}" disabled="disabled">{$groups->user_group_name|escape} {$row->user_group}</option>
							{else}
								{if $smarty.session.user_group != $groups->user_group}
								<option value="{$groups->user_group}"{if $groups->user_group == $row->user_group} selected="selected"{/if}{if $row->user_group != 1 && $groups->user_group == 1} disabled="disabled"{/if}>{$groups->user_group_name|escape}</option>
								{else}
								<option value="{$groups->user_group}" disabled="disabled">{$groups->user_group_name|escape}</option>
								{/if}
							{/if}
						{else}
							<option value="{$groups->user_group}" {if $groups->user_group==$row->user_group}selected{/if}>{$groups->user_group_name|escape}</option>
						{/if}
					{/foreach}
				</select>
				{/if}
			{else}
			<select name="user_group" style="width: 200px;" disabled="disabled">
				{foreach from=$ugroups item=groups}
					{if $groups->user_group!=2}
						<option value="{$groups->user_group}"{if $groups->user_group==$row->user_group} selected="selected"{/if}>{$groups->user_group_name}</option>
					{/if}
				{/foreach}
			</select>
			{/if}
		</div>
	</td>
</tr>

<tr>
	<td>{#USER_SECOND_GROUP#}<br /><small>{#USER_SECOND_INFO#}</small></td>
	<td>
	<div class="pr12">
	{if check_permission('user_perms')}
		{foreach from=$ugroups item=groups}
				<input type="checkbox" name="user_group_extra[]" class="float" value="{$groups->user_group}" {if ($groups->user_group == $row->user_group)} disabled="disabled"{/if} {if in_array($groups->user_group, $us_groups)} checked="checked"{/if}><label>{$groups->user_group_name|escape}</label>
				<div class="clear"></div>
		{/foreach}
	{/if}
	</div>
	</td>
</tr>

<tr>
	<td>{#USER_STATUS#}</td>
	<td>
		<div class="pr12">
		  <select style="width:250px;" name="status" id="status" onchange="mail_status();">
			  <option id="free" value="1"{if $row->status==1 || $smarty.request.action=='new'} selected="selected"{/if}>{#USER_ACTIVE#}</option>
			  <option id="notfree" value="0"{if $row->Id==1 && $g->user_group!=1} disabled="disabled"{else}{if $row->status==0 && $smarty.request.action!='new'} selected="selected"{/if}{if $ItsGroup=='1' && $smarty.session.user_group=='1'} disabled="disabled"{/if}{/if}>{#USER_INACTIVE#}</option>
		  </select>
		</div>
		{if $smarty.request.action=='edit'}
		  <div class="pr12" id="mail_status" style="display:none;clear:left;">
			  <input name="SendFreeMail" type="checkbox" value="1" class="float" /> <label style="cursor: pointer;">{#USER_SEND_INFO#}</label>
		  </div>
		{/if}
	</td>
</tr>

{if $smarty.request.action=='edit'}
<tr>
	<td>{#USER_MESSAGE_SUBJECT#}</td>
	<td><div class="pr12"><input name="SubjectMessage" type="text" id="SubjectMessage" value="{$smarty.request.SubjectMessage|stripslashes|escape}" size="40" style="width:400px;" /></div></td>
</tr>

<tr>
	<td>{#USER_MESSAGE_TEXT#}</td>
	<td><div class="pr12"><textarea style="width:400px; height:100px" name="SimpleMessage" id="SimpleMessage">{$smarty.request.SimpleMessage|stripslashes|escape}</textarea></div></td>
</tr>
{/if}

</tbody>
</table>

<div class="rowElem" id="saveBtn">
	<div class="saveBtn">
		<input type="submit" class="basicBtn" value="{if $smarty.request.action=='new'}{#USER_BUTTON_ADD#}{else}{#USER_BUTTON_SAVE#}{/if}" />
	</div>
</div>

</div>

</fieldset>
</form>

{else}

<div class="title"><h5>{#USER_SUB_TITLE#}</h5></div>

<div class="widget" style="margin-top: 0px;">
	<div class="body">

		{#USER_WARNING_TIP#}

	</div>
</div>

<ul class="messages first">
	<li class="highlight red">&bull; {#USER_YOUR_NOT_CHANGE#}</li>
</ul>
{/if}
