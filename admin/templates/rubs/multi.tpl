<div class="first"></div>
<div class="title"><h5>{#RUBRIK_MULTIPLY2#}</h5></div>
<div class="widget" style="margin-top: 0px;"><div class="body">{#RUBRIK_MULTIPLY_TIP#}</div></div>

<div class="breadCrumbHolder module">
	<div class="breadCrumb module">
	    <ul>
	        <li class="firstB"><a href="index.php" title="{#MAIN_PAGE#}">{#MAIN_PAGE#}</a></li>
	        <li><a href="index.php?do=rubs&cp={$sess}">{#RUBRIK_SUB_TITLE#}</a></li>
	        <li>{#RUBRIK_MULTIPLY2#}</li>
	    </ul>
	</div>
</div>


{if $errors}
	<ul class="messages">
		{foreach from=$errors item=error}<li class="highlight red"><strong>Ошибка:</strong> {$error}</li>{/foreach}
	</ul>
{/if}


<form name="m" method="post" action="?do=rubs&action=multi&pop=1&sub=save&Id={$smarty.request.Id|escape}" class="mainForm">

<div class="widget first">
<div class="head"><h5 class="iFrames">{#RUBRIK_MULTIPLY2#}</h5></div>

	<table cellpadding="0" cellspacing="0" width="100%" class="tableStatic">
		<col width="200">
		<col>
		<tr class="noborder">
			<td>{#RUBRIK_NAME#}</td>
			<td><div class="pr12"><input type="text" name="rubric_title" value="{$smarty.request.rubric_title|escape|stripslashes}"></div></td>

		</tr>

		<tr>
			<td>{#RUBRIK_URL_PREFIX#}</td>
			<td><div class="pr12"><input type="text" name="rubric_alias" value="{$smarty.request.rubric_alias|escape|stripslashes}"></div></td>
		</tr>

		<tr>
			<td colspan="2">
				<input class="basicBtn" type="submit" value="{#RUBRIK_BUTTON_COPY#}" />
			</td>
		</tr>

	</table>


</div>
</form>