<div class="title"><h5>{#RUBRIK_NEW#}</h5></div>
<div class="widget" style="margin-top: 0px;"><div class="body">{#RUBRIK_NEW_TIP#}</div></div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
		<ul>
			<li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
			<li><a href="index.php?do=rubs&cp={$sess}">{#RUBRIK_SUB_TITLE#}</a></li>
			<li>{#RUBRIK_NEW#}</li>
			<li>{$smarty.request.rubric_title|escape|stripslashes}</li>
		</ul>
	</div>
</div>


{if $errors}
	<ul class="messages first">
		{foreach from=$errors item=error}<li class="highlight red"><strong>Ошибка:</strong> {$error}</li>{/foreach}
	</ul>
{/if}

<form name="form1" method="post" action="index.php?do=rubs&action=new&sub=save&cp={$sess}" class="mainForm">

<div class="widget first">
<div class="head"><h5 class="iFrames">{#RUBRIK_NEW#}</h5></div>

<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<col width="250" />
		<tr>
			<td><strong>{#RUBRIK_NAME2#}</strong></td>
			<td><input style="width:250px" type="text" name="rubric_title" value="{$smarty.request.rubric_title|escape|stripslashes}"></td>
		</tr>

		<tr>
			<td><strong>{#RUBRIK_URL_PREFIX2#}</strong></td>
			<td><input style="width:250px" type="text" name="rubric_alias" value="{$smarty.request.rubric_alias|escape|stripslashes}"></td>
		</tr>

		<tr>
			<td><strong>{#RUBRIK_TEMPLATE_OUT2#}</strong></td>
			<td>
				<select style="width:250px" name="rubric_template_id">
					{foreach from=$templates item=template}
						<option value="{$template->Id}" />{$template->template_title|escape}</option>
					{/foreach}
				</select>
			</td>
		</tr>
		<tr>
			<td colspan="2"><input type="submit" class="basicBtn" value="{#RUBRIK_BUTTON_NEW#}"></td>
		</tr>
	</table>

</div>
</form>